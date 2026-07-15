<?php

namespace App\Http\Controllers;

use App\Models\AnimalPackage;
use App\Models\CowGroup;
use App\Models\DistributionLocation;
use App\Models\GroupLocationVote;
use App\Models\Refund;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SavingsPlanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $email = trim(strtolower($request->query('email')));

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak boleh kosong.',
            ]);
        }

        // Auto-update pending transactions older than 24 hours to 'Failed'
        try {
            Transaction::where('status', 'Pending')
                ->where('created_at', '<', Carbon::now()->subHours(24))
                ->update(['status' => 'Failed']);
        } catch (\Exception $e) {
            // Silently ignore
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan.',
            ]);
        }

        // Get savings for this user
        $savings = SavingsPlan::where('user_id', $user->id)->get()->map(function ($saving) {
            $savingArray = $saving->toArray();

            // Get history (transactions) sorted descending
            $history = Transaction::where('savings_plan_id', $saving->id)
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            $savingArray['history'] = $history;

            // Map penyaluran object shape for frontend
            if ($saving->distribution_location_id) {
                $loc = DistributionLocation::find($saving->distribution_location_id);
                $savingArray['penyaluran'] = [
                    'method' => 'Sentralisasi',
                    'receiver' => $saving->shohibul_name ?: $saving->user->name,
                    'phone' => $saving->user->phone,
                    'address' => $loc ? ($loc->name . ' (' . $loc->region . ')') : '',
                    'status' => $saving->penyaluran_status ?: 'Lokasi Dipilih',
                ];
            } else {
                $savingArray['penyaluran'] = null;
            }

            // Include group & voting info if patungan
            $savingArray['group'] = null;
            $savingArray['group_votes'] = [];
            $savingArray['user_voted'] = false;

            if ($saving->cow_group_id) {
                $group = CowGroup::with('savingsPlans.user')->find($saving->cow_group_id);
                if ($group) {
                    $groupArray = $group->toArray();
                    $groupArray['plans'] = $group->savingsPlans->map(function ($gp) {
                        return [
                            'id' => $gp->id,
                            'shohibul_name' => $gp->shohibul_name,
                            'is_institutional' => (bool) $gp->is_institutional,
                            'user_name' => $gp->user ? $gp->user->name : 'N/A',
                            'status' => $gp->status,
                        ];
                    })->toArray();
                    $savingArray['group'] = $groupArray;
                    
                    // Fetch group votes
                    $votes = GroupLocationVote::where('cow_group_id', $group->id)
                        ->get()
                        ->map(function ($v) {
                            $loc = DistributionLocation::find($v->distribution_location_id);
                            return [
                                'savings_plan_id' => $v->savings_plan_id,
                                'location_id' => $v->distribution_location_id,
                                'location_name' => $loc ? $loc->name : 'N/A',
                            ];
                        })->toArray();
                    
                    $savingArray['group_votes'] = $votes;
                    $savingArray['user_voted'] = GroupLocationVote::where('savings_plan_id', $saving->id)->exists();
                }
            }

            // Include refund request info if exists
            $refund = Refund::where('savings_plan_id', $saving->id)->first();
            $savingArray['refund'] = $refund ? $refund->toArray() : null;

            return $savingArray;
        });

        $packages = AnimalPackage::where('is_active', true)->get();
        $locations = DistributionLocation::all();

        // Get cut-off details
        $cutoffDate = Setting::getVal('qurban_cutoff_date', '2026-06-15');
        $hijriYear = (int) Setting::getVal('hijri_year', 1447);

        return response()->json([
            'status' => 'success',
            'savings' => $savings,
            'packages' => $packages,
            'locations' => $locations,
            'cutoff_date' => $cutoffDate,
            'hijri_year' => $hijriYear,
        ]);
    }

    public function createSavings(Request $request)
    {
        $email = trim(strtolower($request->input('email')));
        $packageId = trim($request->input('packageId'));
        $category = trim($request->input('category', 'qurban')); // qurban / aqiqah
        $shohibulName = trim($request->input('shohibulName'));
        
        // Aqiqah details
        $aqiqahChildName = trim($request->input('aqiqahChildName'));
        $aqiqahChildGender = trim($request->input('aqiqahChildGender')); // putra / putri
        $aqiqahChildBirthdate = $request->input('aqiqahChildBirthdate');

        // Payment scheme details
        $scheme = trim($request->input('scheme', 'bulanan')); // mingguan, bulanan, fleksibel
        $initialAmount = (float) $request->input('initialAmount', 0);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan.',
            ]);
        }

        $package = AnimalPackage::find($packageId);
        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paket hewan tidak ditemukan.',
            ]);
        }

        $hijriYear = (int) Setting::getVal('hijri_year', 1447);

        // Validation for Qurban cut-off
        if ($category === 'qurban') {
            $cutoffDateStr = Setting::getVal('qurban_cutoff_date', '2026-06-15');
            $cutoffDate = Carbon::parse($cutoffDateStr);
            if (now()->gt($cutoffDate)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pendaftaran tabungan Qurban tahun ini sudah ditutup (melewati cut-off ' . $cutoffDate->format('d M Y') . ').',
                ], 400);
            }
        }

        // Calculate target amount based on bundling rules (for Aqiqah Putra = 2x price)
        $targetAmount = $package->price;
        $bundleQuantity = 1;

        if ($category === 'aqiqah') {
            if ($package->type === 'sapi_utuh' || $package->type === 'sapi_patungan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aqiqah hanya diperbolehkan menggunakan Domba atau Kambing.',
                ], 400);
            }

            if (empty($aqiqahChildName) || empty($aqiqahChildGender)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Untuk rencana Aqiqah, nama dan jenis kelamin anak wajib diisi.',
                ], 400);
            }

            if ($aqiqahChildGender === 'putra') {
                $targetAmount = $package->price * 2;
                $bundleQuantity = 2;
            }
        }

        if (empty($shohibulName)) {
            $shohibulName = $user->name;
        }

        // Generate plan code: TQ-PLAN-{timestamp}-{rand}
        $planCode = 'TQ-PLAN-' . time() . '-' . rand(100, 999);

        // Estimate next deadline
        $nextDeadline = date('j F Y', strtotime('+1 month'));
        if ($scheme === 'mingguan') {
            $nextDeadline = date('j F Y', strtotime('+1 week'));
        }

        try {
            DB::beginTransaction();

            $saving = SavingsPlan::create([
                'plan_code' => $planCode,
                'user_id' => $user->id,
                'package_id' => $package->id,
                'package_name' => $package->name . ($bundleQuantity > 1 ? ' (Bundling 2 Ekor)' : ''),
                'package_type' => $package->type === 'sapi_patungan' ? 'Sapi Patungan' : ($package->type === 'sapi_utuh' ? 'Sapi Utuh' : ucfirst($package->type)),
                'locked_price' => $package->price,
                'target_amount' => $targetAmount,
                'collected_amount' => 0.0,
                'remaining_amount' => $targetAmount,
                'progress_percent' => 0,
                'next_payment_deadline' => $nextDeadline,
                'status' => 'saving',
                'hijri_year' => $hijriYear,
                'is_institutional' => false,
                'shohibul_name' => $shohibulName,
                'aqiqah_child_name' => $category === 'aqiqah' ? $aqiqahChildName : null,
                'aqiqah_child_gender' => $category === 'aqiqah' ? $aqiqahChildGender : null,
                'aqiqah_child_birthdate' => $category === 'aqiqah' ? $aqiqahChildBirthdate : null,
            ]);

            // Match group if it's Sapi Patungan
            if ($package->type === 'sapi_patungan') {
                app(SavingsPlanService::class)->matchToGroup($saving);
            }

            // Record initial payment pending if initialAmount > 0
            if ($initialAmount > 0) {
                $orderId = 'TQ-' . $saving->id . '-' . time() . '-' . rand(100, 999);
                
                $months = [
                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
                ];
                $day = date('j');
                $month = $months[date('n')];
                $year = date('Y');
                $txDate = "$day $month $year";
                $txTime = date('H:i') . ' WIB';

                Transaction::create([
                    'savings_plan_id' => $saving->id,
                    'order_id' => $orderId,
                    'type' => 'Setoran Awal',
                    'amount' => $initialAmount,
                    'date' => $txDate,
                    'time' => $txTime,
                    'status' => 'Pending',
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Rencana tabungan berhasil dibuat.',
                'savings_id' => $saving->id,
                'initial_amount' => $initialAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat tabungan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function savePenyaluran(Request $request)
    {
        $savingsId = (int) $request->input('savings_id');
        $locationId = (int) $request->input('location_id');

        $saving = SavingsPlan::find($savingsId);
        if (!$saving) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tabungan tidak ditemukan.',
            ]);
        }

        $loc = DistributionLocation::find($locationId);
        if (!$loc) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi penyaluran tidak ditemukan.',
            ]);
        }

        // Validation: cannot select location if part of a group patungan (must use voting instead)
        if ($saving->package_type === 'Sapi Patungan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana patungan harus menentukan lokasi melalui voting kelompok.',
            ], 400);
        }

        try {
            DB::transaction(function () use ($saving, $loc) {
                $saving->distribution_location_id = $loc->id;
                $saving->penyaluran_status = 'Lokasi Dipilih';
                $saving->save();

                app(SavingsPlanService::class)->transitionTo($saving, 'distribution_selected', "Lokasi disentralisasikan ke {$loc->name}");
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Lokasi penyaluran berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan lokasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitVote(Request $request)
    {
        $savingsId = (int) $request->input('savings_id');
        $locationId = (int) $request->input('location_id');

        $saving = SavingsPlan::find($savingsId);
        if (!$saving) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana tabungan tidak ditemukan.',
            ], 404);
        }

        try {
            app(SavingsPlanService::class)->submitVote($saving, $locationId);

            return response()->json([
                'status' => 'success',
                'message' => 'Suara voting berhasil dikirim.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getRefundSimulation(Request $request)
    {
        $savingsId = (int) $request->query('savings_id');
        $plan = SavingsPlan::find($savingsId);

        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana tabungan tidak ditemukan.',
            ], 404);
        }

        $collected = (float) $plan->collected_amount;
        $feePercent = (float) Setting::getVal('cancellation_fee_percent', 2.5);
        $fee = ($feePercent / 100) * $collected;
        $net = $collected - $fee;

        return response()->json([
            'status' => 'success',
            'collected_amount' => $collected,
            'fee_percent' => $feePercent,
            'fee_amount' => $fee,
            'net_amount' => $net,
        ]);
    }

    public function requestCancellation(Request $request)
    {
        $savingsId = (int) $request->input('savings_id');
        $bankAccount = trim($request->input('bank_account'));
        $reason = trim($request->input('reason'));

        if (empty($bankAccount)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor rekening bank tujuan transfer refund wajib diisi.',
            ], 400);
        }

        $plan = SavingsPlan::find($savingsId);
        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana tabungan tidak ditemukan.',
            ], 404);
        }

        // Cancellation only allowed during 'saving' or 'paid_off'
        if ($plan->status !== 'saving' && $plan->status !== 'paid_off') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembatalan terkunci. Hewan sudah diproses dalam pipeline.',
            ], 400);
        }

        try {
            DB::transaction(function () use ($plan, $bankAccount, $reason) {
                $collected = (float) $plan->collected_amount;
                $feePercent = (float) Setting::getVal('cancellation_fee_percent', 2.5);
                $fee = ($feePercent / 100) * $collected;
                $net = $collected - $fee;

                Refund::create([
                    'savings_plan_id' => $plan->id,
                    'amount_collected' => $collected,
                    'fee_percent' => $feePercent,
                    'fee_amount' => $fee,
                    'net_amount' => $net,
                    'bank_account' => $bankAccount,
                    'status' => 'pending',
                ]);

                // Transition status to cancellation_requested
                app(SavingsPlanService::class)->transitionTo($plan, 'cancellation_requested', 'Pengajuan pembatalan oleh user: ' . $reason);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan pembatalan rencana tabungan berhasil dikirim.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengajukan pembatalan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
