<?php

namespace App\Http\Controllers;

use App\Models\AnimalPackage;
use App\Models\Certificate;
use App\Models\CowGroup;
use App\Models\DistributionLocation;
use App\Models\DistributionProgress;
use App\Models\Refund;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SavingsPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getAdminData()
    {
        $totalUsers = User::count();
        $totalSavings = SavingsPlan::sum('collected_amount') ?: 0;

        $users = User::orderBy('id', 'desc')->get();

        $transactions = Transaction::with(['savingPlan.user'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($tx) {
                return [
                    'txId' => $tx->id,
                    'savings_id' => $tx->savings_plan_id,
                    'order_id' => $tx->order_id,
                    'type' => $tx->type,
                    'amount' => $tx->amount,
                    'date' => $tx->date,
                    'time' => $tx->time,
                    'status' => $tx->status,
                    'packageName' => $tx->savingPlan ? $tx->savingPlan->package_name : 'N/A',
                    'userName' => ($tx->savingPlan && $tx->savingPlan->user) ? $tx->savingPlan->user->name : 'N/A',
                    'email' => ($tx->savingPlan && $tx->savingPlan->user) ? $tx->savingPlan->user->email : 'N/A',
                ];
            });

        $timelines = SavingsPlan::with(['user', 'location'])
            ->whereNotNull('distribution_location_id')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($saving) {
                return [
                    'id' => $saving->id,
                    'package_name' => $saving->package_name,
                    'packageName' => $saving->package_name,
                    'penyaluran_method' => 'Sentralisasi',
                    'penyaluran_address' => $saving->location ? ($saving->location->name . ' (' . $saving->location->region . ')') : 'N/A',
                    'penyaluran_status' => $saving->status,
                    'status' => $saving->status,
                    'userName' => $saving->user ? $saving->user->name : 'N/A',
                    'email' => $saving->user ? $saving->user->email : 'N/A',
                ];
            });

        $livestock = AnimalPackage::orderBy('created_at', 'desc')->get();
        $locations = DistributionLocation::orderBy('created_at', 'desc')->get();

        // 1. Fetch Cow Groups data
        $groups = CowGroup::with(['savingsPlans.user'])->orderBy('id', 'desc')->get()->map(function ($g) {
            return [
                'id' => $g->id,
                'code' => $g->code,
                'hijri_year' => $g->hijri_year,
                'filled_slots' => $g->filled_slots,
                'status' => $g->status,
                'plans' => $g->savingsPlans->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'shohibul_name' => $p->shohibul_name,
                        'user_name' => $p->user ? $p->user->name : 'N/A',
                        'collected_amount' => $p->collected_amount,
                        'target_amount' => $p->target_amount,
                        'status' => $p->status,
                        'is_institutional' => $p->is_institutional,
                    ];
                }),
            ];
        });

        // 2. Fetch Refunds data
        $refunds = Refund::with(['savingsPlan.user'])->orderBy('id', 'desc')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'savings_plan_id' => $r->savings_plan_id,
                'amount_collected' => $r->amount_collected,
                'fee_percent' => $r->fee_percent,
                'fee_amount' => $r->fee_amount,
                'net_amount' => $r->net_amount,
                'bank_account' => $r->bank_account,
                'proof_path' => $r->proof_path,
                'status' => $r->status,
                'userName' => ($r->savingsPlan && $r->savingsPlan->user) ? $r->savingsPlan->user->name : 'N/A',
                'userEmail' => ($r->savingsPlan && $r->savingsPlan->user) ? $r->savingsPlan->user->email : 'N/A',
                'packageName' => $r->savingsPlan ? $r->savingsPlan->package_name : 'N/A',
            ];
        });

        // 3. Fetch Settings
        $settings = [
            'qurban_cutoff_date' => Setting::getVal('qurban_cutoff_date', '2026-06-15'),
            'hijri_year' => Setting::getVal('hijri_year', '1447'),
            'cancellation_fee_percent' => Setting::getVal('cancellation_fee_percent', '2.5'),
            'institutional_shohibul_name' => Setting::getVal('institutional_shohibul_name', 'Infaq Qurban Lembaga'),
            'talangan_days_before_cutoff' => Setting::getVal('talangan_days_before_cutoff', '7'),
        ];

        // 4. Activity Logs
        $logs = DistributionProgress::with(['savingsPlan.user', 'cowGroup'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'userName' => ($log->savingsPlan && $log->savingsPlan->user) ? $log->savingsPlan->user->name : 'System',
                    'planCode' => $log->savingsPlan ? $log->savingsPlan->plan_code : 'N/A',
                    'groupCode' => $log->cowGroup ? $log->cowGroup->code : 'N/A',
                    'from' => $log->from_status,
                    'to' => $log->to_status,
                    'note' => $log->note,
                    'timestamp' => $log->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'stats' => [
                'total_users' => $totalUsers,
                'total_savings' => $totalSavings,
                'total_livestock' => count($livestock),
                'total_groups' => count($groups),
            ],
            'users' => $users,
            'transactions' => $transactions,
            'timelines' => $timelines,
            'livestock' => $livestock,
            'locations' => $locations,
            'groups' => $groups,
            'refunds' => $refunds,
            'settings' => $settings,
            'logs' => $logs,
        ]);
    }

    public function toggleUserRole(Request $request)
    {
        $email = trim(strtolower($request->input('email')));
        $user = User::where('email', $email)->first();

        if ($user) {
            $newRole = $user->role === 'admin' ? 'user' : 'admin';
            $user->update(['role' => $newRole]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil diubah.',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan.',
            ]);
        }
    }

    public function deleteUser(Request $request)
    {
        $email = trim(strtolower($request->input('email')));
        $user = User::where('email', $email)->where('role', '!=', 'admin')->first();

        if ($user) {
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil dihapus.',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan atau tidak dapat dihapus.',
            ]);
        }
    }

    public function addLivestock(Request $request)
    {
        $id = trim($request->input('id'));
        $category = trim($request->input('category', 'qurban'));
        $type = trim($request->input('type', 'domba'));
        $name = trim($request->input('name'));
        $price = (float) $request->input('price');
        $desc = trim($request->input('desc'));
        $weight = trim($request->input('weight'));
        $age = trim($request->input('age'));
        $fit = trim($request->input('fit'));
        $image = trim($request->input('image'));
        $totalSlots = (int) $request->input('total_slots', 1);
        $bundleQty = (int) $request->input('bundle_quantity', 1);

        if (empty($id) || empty($category) || empty($name)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID, Kategori, dan Nama wajib diisi.',
            ]);
        }

        AnimalPackage::create([
            'id' => $id,
            'category' => $category,
            'type' => $type,
            'name' => $name,
            'price' => $price,
            'desc' => $desc,
            'weight' => $weight,
            'age' => $age,
            'fit' => $fit,
            'image' => $image,
            'total_slots' => $totalSlots,
            'bundle_quantity' => $bundleQty,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Hewan qurban berhasil ditambahkan.',
        ]);
    }

    public function editLivestock(Request $request)
    {
        $id = trim($request->input('id'));
        $category = trim($request->input('category'));
        $type = trim($request->input('type'));
        $name = trim($request->input('name'));
        $price = (float) $request->input('price');
        $desc = trim($request->input('desc'));
        $weight = trim($request->input('weight'));
        $age = trim($request->input('age'));
        $fit = trim($request->input('fit'));
        $image = trim($request->input('image'));
        $totalSlots = (int) $request->input('total_slots', 1);
        $bundleQty = (int) $request->input('bundle_quantity', 1);

        if (empty($id) || empty($category) || empty($name)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID, Kategori, dan Nama wajib diisi.',
            ]);
        }

        $package = AnimalPackage::find($id);

        if (!$package) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hewan qurban tidak ditemukan.',
            ]);
        }

        $package->update([
            'category' => $category,
            'type' => $type,
            'name' => $name,
            'price' => $price,
            'desc' => $desc,
            'weight' => $weight,
            'age' => $age,
            'fit' => $fit,
            'image' => $image,
            'total_slots' => $totalSlots,
            'bundle_quantity' => $bundleQty,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Hewan qurban berhasil diperbarui.',
        ]);
    }

    public function deleteLivestock(Request $request)
    {
        $id = trim($request->input('id'));
        $package = AnimalPackage::find($id);

        if ($package) {
            $package->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Hewan qurban berhasil dihapus.',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Hewan qurban tidak ditemukan.',
            ]);
        }
    }

    public function addLocation(Request $request)
    {
        $code = trim($request->input('code'));
        $name = trim($request->input('name'));
        $category = trim($request->input('category', 'qurban'));
        $region = trim($request->input('region'));
        $description = trim($request->input('description'));
        $capacity = (int) $request->input('capacity');
        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');

        if (empty($name) || empty($region) || empty($code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode, Nama dan Wilayah lokasi wajib diisi.',
            ]);
        }

        DistributionLocation::create([
            'code' => $code,
            'name' => $name,
            'category' => $category,
            'region' => $region,
            'description' => $description,
            'capacity' => $capacity,
            'quota' => $capacity,
            'used_quota' => 0,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi qurban berhasil ditambahkan.',
        ]);
    }

    public function editLocation(Request $request)
    {
        $id = (int) $request->input('id');
        $code = trim($request->input('code'));
        $name = trim($request->input('name'));
        $category = trim($request->input('category'));
        $region = trim($request->input('region'));
        $description = trim($request->input('description'));
        $capacity = (int) $request->input('capacity');
        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');

        if (empty($id) || empty($name) || empty($region) || empty($code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID, Kode, Nama, dan Wilayah lokasi wajib diisi.',
            ]);
        }

        $location = DistributionLocation::find($id);

        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi tidak ditemukan.',
            ]);
        }

        $location->update([
            'code' => $code,
            'name' => $name,
            'category' => $category,
            'region' => $region,
            'description' => $description,
            'capacity' => $capacity,
            'quota' => $capacity,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi qurban berhasil diperbarui.',
        ]);
    }

    public function deleteLocation(Request $request)
    {
        $id = (int) $request->input('id');
        $location = DistributionLocation::find($id);

        if ($location) {
            $location->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Lokasi qurban berhasil dihapus.',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Lokasi tidak ditemukan.',
            ]);
        }
    }

    public function updateTimeline(Request $request)
    {
        $savingsId = (int) $request->input('savings_id');
        $newStatus = trim($request->input('status')); // status transitions
        $note = trim($request->input('note'));
        $evidence = trim($request->input('evidence'));
        $adminId = (int) $request->input('admin_id');

        $saving = SavingsPlan::find($savingsId);

        if (!$saving) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tabungan tidak ditemukan.',
            ]);
        }

        try {
            app(SavingsPlanService::class)->transitionTo($saving, $newStatus, $note, $evidence, $adminId);

            return response()->json([
                'status' => 'success',
                'message' => 'Status progres berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah status: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function saveCertificate(Request $request)
    {
        $savingsId = (int) $request->input('savings_id');
        $adminId = (int) $request->input('admin_id');

        $saving = SavingsPlan::find($savingsId);

        if (!$saving) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tabungan tidak ditemukan.',
            ]);
        }

        if ($saving->status !== 'report_done') {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat hanya bisa diterbitkan setelah status Laporan Selesai (report_done).',
            ], 400);
        }

        try {
            DB::transaction(function () use ($saving, $adminId) {
                // Generate sequential certificate number
                $hijriYear = $saving->hijri_year;
                
                $locCode = 'LOC-GEN';
                if ($saving->distribution_location_id) {
                    $loc = DistributionLocation::find($saving->distribution_location_id);
                    if ($loc && $loc->code) {
                        $locCode = $loc->code;
                    }
                }

                // Count certificates in this Hijri year and location code
                $seq = Certificate::whereHas('savingsPlan', function ($q) use ($hijriYear, $locCode) {
                    $q->where('hijri_year', $hijriYear)
                      ->where('distribution_location_id', function ($sub) use ($locCode) {
                          $sub->select('id')->from('distribution_locations')->where('code', $locCode)->limit(1);
                      });
                })->count() + 1;

                $seqStr = str_pad($seq, 4, '0', STR_PAD_LEFT);
                
                // Format: TQ/{HIJRIAH}/{KODE_LOKASI}/{SEQ} for qurban, prefix AQ/ for aqiqah
                $isAqiqah = ($saving->package && $saving->package->category === 'aqiqah');
                $prefix = $isAqiqah ? 'AQ' : 'TQ';
                $certNumber = "{$prefix}/{$hijriYear}/{$locCode}/{$seqStr}";

                $pdfPath = "certificates/cert-{$saving->id}-" . time() . ".pdf";

                Certificate::create([
                    'savings_plan_id' => $saving->id,
                    'certificate_number' => $certNumber,
                    'pdf_path' => $pdfPath,
                ]);

                // Transition status to completed
                app(SavingsPlanService::class)->transitionTo($saving, 'completed', "Sertifikat {$certNumber} telah diterbitkan.", $pdfPath, $adminId);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Sertifikat berhasil diterbitkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerbitkan sertifikat: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approveTransaction(Request $request)
    {
        $txId = (int) $request->input('txId');
        $orderId = trim($request->input('orderId'));
        $adminId = (int) $request->input('admin_id');

        if ($txId > 0) {
            $tx = Transaction::find($txId);
        } else {
            $tx = Transaction::where('order_id', $orderId)->first();
        }

        if ($tx && $tx->status === 'Pending') {
            try {
                DB::transaction(function () use ($tx, $adminId) {
                    $tx->update(['status' => 'Success']);

                    $saving = SavingsPlan::where('id', $tx->savings_plan_id)->lockForUpdate()->first();
                    if ($saving) {
                        $saving->collected_amount += $tx->amount;
                        $saving->recalculateProgress();

                        if ($saving->collected_amount >= $saving->target_amount) {
                            app(SavingsPlanService::class)->transitionTo($saving, 'paid_off', 'Rencana tabungan disetujui lunas manual oleh admin.', null, $adminId);
                        }
                    }
                });

                return response()->json([
                    'status' => 'success',
                    'message' => 'Transaksi berhasil disetujui.',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menyetujui transaksi: ' . $e->getMessage(),
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan, sudah diproses, atau statusnya bukan Pending.',
            ]);
        }
    }

    public function talangiSlot(Request $request)
    {
        $groupId = (int) $request->input('groupId');
        $adminId = (int) $request->input('admin_id');

        $group = CowGroup::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Grup tidak ditemukan.',
            ], 404);
        }

        try {
            app(SavingsPlanService::class)->talangiGroupSlots($group, $adminId);

            return response()->json([
                'status' => 'success',
                'message' => 'Grup berhasil ditalangi oleh lembaga.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function processBulkPipeline(Request $request)
    {
        $groupId = (int) $request->input('groupId');
        $newStatus = trim($request->input('status'));
        $note = trim($request->input('note'));
        $evidence = trim($request->input('evidence'));
        $adminId = (int) $request->input('admin_id');

        $group = CowGroup::find($groupId);
        if (!$group) {
            return response()->json([
                'status' => 'error',
                'message' => 'Grup tidak ditemukan.',
            ], 404);
        }

        try {
            app(SavingsPlanService::class)->transitionGroup($group, $newStatus, $note, $evidence, $adminId);

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk pipeline kelompok berhasil diproses.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getSettings(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'settings' => [
                'qurban_cutoff_date' => Setting::getVal('qurban_cutoff_date', '2026-06-15'),
                'hijri_year' => Setting::getVal('hijri_year', '1447'),
                'cancellation_fee_percent' => Setting::getVal('cancellation_fee_percent', '2.5'),
                'institutional_shohibul_name' => Setting::getVal('institutional_shohibul_name', 'Infaq Qurban Lembaga'),
                'talangan_days_before_cutoff' => Setting::getVal('talangan_days_before_cutoff', '7'),
            ]
        ]);
    }

    public function saveSettings(Request $request)
    {
        $settings = $request->input('settings', []);

        try {
            DB::transaction(function () use ($settings) {
                foreach ($settings as $key => $val) {
                    Setting::setVal($key, $val);
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pengaturan berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pengaturan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approveRefund(Request $request)
    {
        $refundId = (int) $request->input('refundId');
        $proofPath = trim($request->input('proof_path'));
        $adminId = (int) $request->input('admin_id');

        $refund = Refund::find($refundId);
        if (!$refund) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan refund tidak ditemukan.',
            ], 404);
        }

        try {
            DB::transaction(function () use ($refund, $proofPath, $adminId) {
                $refund->update([
                    'status' => 'approved',
                    'proof_path' => $proofPath ?: 'bukti-transfer-manual.jpg',
                ]);

                // Change savings plan status to cancelled
                $plan = SavingsPlan::lockForUpdate()->find($refund->savings_plan_id);
                if ($plan) {
                    app(SavingsPlanService::class)->transitionTo($plan, 'cancelled', 'Refund disetujui, bukti transfer manual terunggah.', $proofPath, $adminId);

                    // If part of group, open slot back up
                    if ($plan->cow_group_id) {
                        $group = CowGroup::lockForUpdate()->find($plan->cow_group_id);
                        if ($group) {
                            $group->filled_slots = max(0, $group->filled_slots - 1);
                            if ($group->status === 'full' || $group->status === 'ready') {
                                $group->status = 'open';
                            }
                            $group->save();
                        }
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Refund manual berhasil disetujui dan ditandai lunas.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyetujui refund: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getActivityLogs(Request $request)
    {
        $logs = DistributionProgress::with(['savingsPlan.user', 'cowGroup'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'status' => 'success',
            'logs' => $logs,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            $disk = config('filesystems.default');
            // Fallback to public locally if default is local (private)
            if ($disk === 'local') {
                $disk = 'public';
            }
            
            $path = $file->store('livestock', $disk);
            if ($path === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengunggah berkas ke penyimpanan.',
                ], 500);
            }
            $url = Storage::disk($disk)->url($path);

            return response()->json([
                'status' => 'success',
                'url' => $url,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'File tidak ditemukan atau tidak valid.',
        ], 400);
    }
}
