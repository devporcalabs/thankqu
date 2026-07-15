<?php

namespace App\Services;

use App\Models\AnimalPackage;
use App\Models\CowGroup;
use App\Models\DistributionLocation;
use App\Models\DistributionProgress;
use App\Models\GroupLocationVote;
use App\Models\PriceHistory;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SavingsPlanService
{
    /**
     * Match a savings plan to a group (for sapi_patungan).
     * Enforces DB transaction + lockForUpdate to prevent race conditions.
     */
    public function matchToGroup(SavingsPlan $plan)
    {
        if ($plan->package_type !== 'Sapi Patungan') {
            return;
        }

        DB::transaction(function () use ($plan) {
            // Find an open group in the current Hijri year, FIFO order (oldest first)
            $group = CowGroup::where('hijri_year', $plan->hijri_year)
                ->where('status', 'open')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$group) {
                // Generate a unique code
                $count = CowGroup::where('hijri_year', $plan->hijri_year)->count() + 1;
                $code = 'GRP-' . $plan->hijri_year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

                $group = CowGroup::create([
                    'code' => $code,
                    'hijri_year' => $plan->hijri_year,
                    'filled_slots' => 0,
                    'status' => 'open',
                ]);
            }

            // Double check slots to prevent race condition (max 7)
            if ($group->filled_slots >= 7) {
                throw new \Exception("Grup sapi patungan penuh. Silakan coba lagi.");
            }

            // Assign plan to group
            $group->filled_slots += 1;
            if ($group->filled_slots === 7) {
                $group->status = 'full';
            }
            $group->save();

            $plan->cow_group_id = $group->id;
            $plan->save();

            // Check if group can transition to ready
            $this->checkGroupReady($group);
        });
    }

    /**
     * Transition a plan status with strict validations and activity logs.
     */
    public function transitionTo(SavingsPlan $plan, string $newStatus, ?string $note = null, ?string $evidence = null, ?int $adminId = null)
    {
        $allowedTransitions = [
            'saving' => ['paid_off', 'cancellation_requested', 'cancelled', 'carried_over'],
            'paid_off' => ['distribution_selected', 'cancellation_requested', 'cancelled'],
            'cancellation_requested' => ['cancelled'],
            'distribution_selected' => ['animal_purchased'],
            'animal_purchased' => ['slaughtered'],
            'slaughtered' => ['report_done'],
            'report_done' => ['completed'],
            'completed' => [],
            'cancelled' => [],
            'carried_over' => ['saving'],
        ];

        $currentStatus = $plan->status;

        // Skip if same status
        if ($currentStatus === $newStatus) {
            return;
        }

        // Validate transition (allow super admin to override if marked, but enforce standard transitions generally)
        $isAllowed = in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);

        // Super Admin bypass override logic
        $isSuperAdminOverride = false;
        if ($adminId) {
            $admin = User::find($adminId);
            if ($admin && $admin->role === 'admin') { // Admin/Super Admin
                $isSuperAdminOverride = true;
            }
        }

        if (!$isAllowed && !$isSuperAdminOverride) {
            throw new \Exception("Transisi status ilegal: dari {$currentStatus} ke {$newStatus}");
        }

        DB::transaction(function () use ($plan, $currentStatus, $newStatus, $note, $evidence, $adminId) {
            $plan->status = $newStatus;
            $plan->save();

            // Log progress
            DistributionProgress::create([
                'savings_plan_id' => $plan->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'note' => $note ?: "Transisi status otomatis ke {$newStatus}",
                'evidence' => $evidence,
                'admin_id' => $adminId,
            ]);

            // If we are part of a cow group, check group lifecycle status changes
            if ($plan->cow_group_id) {
                $group = CowGroup::find($plan->cow_group_id);
                if ($group) {
                    $this->checkGroupReady($group);
                }
            }
        });
    }

    /**
     * Transition all plans in a CowGroup as a single unit (bulk pipeline).
     */
    public function transitionGroup(CowGroup $group, string $newStatus, ?string $note = null, ?string $evidence = null, ?int $adminId = null)
    {
        DB::transaction(function () use ($group, $newStatus, $note, $evidence, $adminId) {
            $plans = SavingsPlan::where('cow_group_id', $group->id)->get();

            foreach ($plans as $plan) {
                $this->transitionTo($plan, $newStatus, $note, $evidence, $adminId);
            }

            // Update group status if applicable
            if ($newStatus === 'animal_purchased') {
                $group->status = 'processed';
                $group->save();
            }

            // Log bulk progress to cow group logs
            DistributionProgress::create([
                'cow_group_id' => $group->id,
                'from_status' => $group->status,
                'to_status' => $newStatus,
                'note' => "Pemrosesan bulk grup: " . ($note ?: "Transisi status grup ke {$newStatus}"),
                'evidence' => $evidence,
                'admin_id' => $adminId,
            ]);
        });
    }

    /**
     * Checks if a group is ready (7 members, all paid off, all shohibul names filled).
     */
    public function checkGroupReady(CowGroup $group)
    {
        if ($group->filled_slots < 7) {
            return;
        }

        $plans = SavingsPlan::where('cow_group_id', $group->id)->get();

        if ($plans->count() < 7) {
            return;
        }

        $allPaidOff = true;
        $allShohibulFilled = true;

        foreach ($plans as $plan) {
            if ($plan->status !== 'paid_off' && $plan->status !== 'distribution_selected' && $plan->status !== 'animal_purchased' && $plan->status !== 'slaughtered' && $plan->status !== 'report_done' && $plan->status !== 'completed') {
                $allPaidOff = false;
            }
            if (empty($plan->shohibul_name)) {
                $allShohibulFilled = false;
            }
        }

        if ($allPaidOff && $allShohibulFilled && $group->status === 'full') {
            $group->status = 'ready';
            $group->save();

            // Trigger voting open automatically
            $this->openVoting($group);
        }
    }

    /**
     * Open voting automatically for a ready group.
     */
    private function openVoting(CowGroup $group)
    {
        // Set voting deadline
        $deadlineDays = (int) Setting::getVal('voting_deadline_days', 5);
        // We could log or queue a job. For this setup, we just mark group status as ready and open for votes.
    }

    /**
     * Submit a location vote for a savings plan.
     */
    public function submitVote(SavingsPlan $plan, int $locationId)
    {
        if (!$plan->cow_group_id) {
            throw new \Exception("Rencana tabungan tidak terasosiasi dengan kelompok.");
        }

        $group = CowGroup::find($plan->cow_group_id);
        if (!$group || $group->status !== 'ready') {
            throw new \Exception("Voting hanya bisa dilakukan ketika kelompok sudah berstatus Ready.");
        }

        // Check if institutional plan (abstain)
        if ($plan->is_institutional) {
            throw new \Exception("Slot talangan lembaga abstain dari voting.");
        }

        // Check if already voted
        $existingVote = GroupLocationVote::where('savings_plan_id', $plan->id)->first();
        if ($existingVote) {
            throw new \Exception("Anda sudah melakukan voting lokasi.");
        }

        DB::transaction(function () use ($plan, $locationId, $group) {
            GroupLocationVote::create([
                'cow_group_id' => $plan->cow_group_id,
                'savings_plan_id' => $plan->id,
                'distribution_location_id' => $locationId,
                'voted_at' => now(),
            ]);

            // Calculate votes
            $votes = GroupLocationVote::where('cow_group_id', $group->id)
                ->select('distribution_location_id', DB::raw('count(*) as count'))
                ->groupBy('distribution_location_id')
                ->get();

            foreach ($votes as $v) {
                // Absolute majority is >= 4/7 votes
                if ($v->count >= 4) {
                    $selectedLocationId = $v->distribution_location_id;
                    $location = DistributionLocation::find($selectedLocationId);

                    // Lock location for all members
                    $groupPlans = SavingsPlan::where('cow_group_id', $group->id)->get();
                    foreach ($groupPlans as $gp) {
                        $gp->update(['distribution_location_id' => $selectedLocationId]);
                        $this->transitionTo($gp, 'distribution_selected', "Lokasi disalurkan ke {$location->name} berdasarkan hasil voting mayoritas.");
                        $this->transitionTo($gp, 'animal_purchased', "Otomatis berstatus animal_purchased setelah voting lokasi dikunci.");
                    }
                    $group->update(['status' => 'processed']);
                    break;
                }
            }
        });
    }

    /**
     * Institutional slot coverage (Talangi Slot).
     */
    public function talangiGroupSlots(CowGroup $group, ?int $adminId = null)
    {
        if ($group->filled_slots >= 7) {
            throw new \Exception("Kelompok sudah penuh, tidak ada slot kosong.");
        }

        DB::transaction(function () use ($group, $adminId) {
            // Find default institution user or create a placeholder if none
            $adminUser = User::where('role', 'admin')->first();
            if (!$adminUser) {
                throw new \Exception("Akun lembaga (admin) tidak ditemukan.");
            }

            $slotsNeeded = 7 - $group->filled_slots;
            $institutionName = Setting::getVal('institutional_shohibul_name', 'Infaq Qurban Lembaga');

            // Find animal package to copy price
            $package = AnimalPackage::find('Sapi-Patungan');
            $price = $package ? $package->price : 3500000.00;

            for ($i = 0; $i < $slotsNeeded; $i++) {
                $code = 'TQ-INST-' . time() . '-' . rand(100, 999);

                $plan = SavingsPlan::create([
                    'plan_code' => $code,
                    'user_id' => $adminUser->id,
                    'package_id' => 'Sapi-Patungan',
                    'package_name' => $package ? $package->name : 'Sapi Patungan (1/7 Kelompok)',
                    'package_type' => 'Sapi Patungan',
                    'locked_price' => $price,
                    'target_amount' => $price,
                    'collected_amount' => $price,
                    'remaining_amount' => 0.0,
                    'progress_percent' => 100,
                    'status' => 'paid_off',
                    'hijri_year' => $group->hijri_year,
                    'is_institutional' => true,
                    'shohibul_name' => $institutionName,
                    'cow_group_id' => $group->id,
                ]);

                // Create a success manual transaction record
                $plan->transactions()->create([
                    'order_id' => 'TQ-INST-TX-' . time() . '-' . rand(1000, 9999),
                    'type' => 'Setoran Talangan Lembaga',
                    'amount' => $price,
                    'date' => date('j F Y'),
                    'time' => date('H:i') . ' WIB',
                    'status' => 'Success',
                    'channel' => 'Manual',
                    'is_manual' => true,
                    'manual_note' => 'Talangan dana lembaga otomatis lunas.',
                    'paid_at' => now(),
                ]);
            }

            $group->filled_slots = 7;
            $group->status = 'full';
            $group->save();

            // Re-check group status
            $this->checkGroupReady($group);
        });
    }

    /**
     * Process carry-over for unpaid savings plan at cut-off.
     */
    public function carryOverPlan(SavingsPlan $plan)
    {
        if ($plan->status !== 'saving') {
            return;
        }

        DB::transaction(function () use ($plan) {
            $oldGroup = null;
            if ($plan->cow_group_id) {
                $oldGroup = CowGroup::lockForUpdate()->find($plan->cow_group_id);
                if ($oldGroup) {
                    $oldGroup->filled_slots = max(0, $oldGroup->filled_slots - 1);
                    if ($oldGroup->status === 'full' || $oldGroup->status === 'ready') {
                        $oldGroup->status = 'open';
                    }
                    $oldGroup->save();
                }
            }

            // Adjust plan fields
            $plan->status = 'carried_over';
            $plan->cow_group_id = null; // Released from group
            // collected_amount remains UTUH
            $plan->hijri_year += 1;
            $plan->save();

            // Log carry-over progress
            DistributionProgress::create([
                'savings_plan_id' => $plan->id,
                'from_status' => 'saving',
                'to_status' => 'carried_over',
                'note' => 'Plan ditunda (carry-over) ke tahun depan karena belum lunas saat cut-off.',
            ]);

            // If old group was full/ready, it needs a replacement slot ditalangi oleh lembaga agar sapi tetap dieksekusi 7/7
            if ($oldGroup && ($oldGroup->filled_slots < 7)) {
                $this->talangiGroupSlots($oldGroup);
            }
        });
    }

    /**
     * Reactivate and reprice a carried-over plan.
     */
    public function reactivatePlan(SavingsPlan $plan)
    {
        if ($plan->status !== 'carried_over') {
            throw new \Exception("Hanya plan status carried_over yang dapat direaktivasi.");
        }

        // Fetch package to get the new season price
        $package = AnimalPackage::find($plan->package_id);
        if (!$package) {
            throw new \Exception("Paket hewan terkait tidak ditemukan.");
        }

        $oldPrice = $plan->locked_price;
        $newPrice = $package->price;

        DB::transaction(function () use ($plan, $oldPrice, $newPrice) {
            // Record price history
            PriceHistory::create([
                'animal_package_id' => $plan->package_id,
                'price' => $oldPrice,
                'hijri_year' => $plan->hijri_year - 1,
            ]);

            // Set new price details
            $plan->locked_price = $newPrice;
            // Target is price multiplied by bundle quantity (1 or 2 for aqiqah)
            $plan->target_amount = $newPrice * ($plan->package->bundle_quantity ?? 1);
            $plan->repriced_at = now();
            $plan->status = 'saving';
            $plan->save();

            $plan->recalculateProgress();

            // Log reactivation
            DistributionProgress::create([
                'savings_plan_id' => $plan->id,
                'from_status' => 'carried_over',
                'to_status' => 'saving',
                'note' => "Rencana diaktifkan kembali untuk Hijriah {$plan->hijri_year}. Harga diperbarui dari Rp " . number_format($oldPrice) . " menjadi Rp " . number_format($newPrice),
            ]);

            // Match back to group if patungan
            if ($plan->package_type === 'Sapi Patungan') {
                $this->matchToGroup($plan);
            }
        });
    }
}
