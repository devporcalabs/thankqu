<?php

namespace App\Http\Controllers;

use App\Models\PaymentNotification;
use App\Models\SavingsPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SavingsPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    private function getServerKey()
    {
        return config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-PLACEHOLDER');
    }

    public function getSnapToken(Request $request)
    {
        $serverKey = $this->getServerKey();
        $amount = (int) $request->input('amount');
        $email = trim($request->input('email'));
        $name = trim($request->input('name'));
        $paymentMethod = trim($request->input('payment_method'));
        $savingsId = trim($request->input('savings_id'));

        if ($amount < 10000) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nominal pembayaran minimal adalah Rp 10.000',
            ], 400);
        }

        if (empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat email pelanggan tidak boleh kosong.',
            ], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $plan = SavingsPlan::find($savingsId);
        if (!$plan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana tabungan tidak ditemukan.',
            ], 404);
        }

        // Validate plan belongs to user
        if ($plan->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana tabungan bukan milik Anda.',
            ], 403);
        }

        // Validate status is saving
        if ($plan->status !== 'saving') {
            return response()->json([
                'status' => 'error',
                'message' => 'Rencana tabungan tidak berstatus Menabung.',
            ], 400);
        }

        // Validate nominal <= remaining target
        $remaining = $plan->target_amount - $plan->collected_amount;
        if ($amount > $remaining) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nominal pembayaran melebihi sisa target tabungan (Rp ' . number_format($remaining, 0, ',', '.') . ').',
            ], 400);
        }

        // Unique order ID format: TQ-{plan_id}-{timestamp}-{random}
        $orderId = 'TQ-' . $plan->id . '-' . time() . '-' . rand(100, 999);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $name ?: 'Jamaah',
                'email' => $email,
            ],
            'item_details' => [
                [
                    'id' => 'setoran-tabungan',
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => 'Setoran Cicilan Tabungan ' . ucfirst($plan->package_type),
                ],
            ],
            'custom_field1' => $savingsId,
        ];

        $enabledPayments = [];
        if ($paymentMethod === 'Virtual Account') {
            $enabledPayments = ['bni_va', 'bri_va', 'mandiri_va', 'permata_va'];
        } elseif ($paymentMethod === 'E-Wallet') {
            $enabledPayments = ['gopay', 'shopeepay'];
        }

        if (!empty($enabledPayments)) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        $url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $authHeader,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            $decodedResponse = json_decode($response, true);
            $token = $decodedResponse['token'] ?? '';

            if (!empty($token)) {
                $months = [
                    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
                ];
                $day = date('j');
                $month = $months[date('n')];
                $year = date('Y');
                $txDate = "$day $month $year";
                $txTime = date('H:i') . ' WIB';

                try {
                    Transaction::create([
                        'savings_plan_id' => $plan->id,
                        'order_id' => $orderId,
                        'type' => 'Top Up Saldo',
                        'amount' => $amount,
                        'date' => $txDate,
                        'time' => $txTime,
                        'status' => 'Pending',
                        'snap_token' => $token,
                        'payment_method' => $paymentMethod,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal mencatat transaksi pending: ' . $e->getMessage());
                }
            }

            return response($response)->header('Content-Type', 'application/json');
        } else {
            $decodedResponse = json_decode($response, true);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil snap token dari Sandbox Midtrans',
                'midtrans_error' => $decodedResponse ?: $response,
            ], $httpCode);
        }
    }

    public function webhook(Request $request)
    {
        $serverKey = $this->getServerKey();
        $notification = $request->all();

        // 1. Log payload to payment_notifications first
        $dbNotification = PaymentNotification::create([
            'raw_payload' => $notification,
            'signature_valid' => false,
            'processed' => false,
        ]);

        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKeyReceived = $notification['signature_key'] ?? '';

        // 2. Validate signature_key
        $localSignatureString = $orderId . $statusCode . $grossAmount . $serverKey;
        $localSignatureKey = hash('sha512', $localSignatureString);

        if ($localSignatureKey !== $signatureKeyReceived) {
            return response()->json([
                'status' => 'error',
                'message' => 'Signature Key tidak cocok! Request dicurigai palsu.',
            ], 403);
        }

        $dbNotification->update(['signature_valid' => true]);

        // Find transaction
        $tx = Transaction::where('order_id', $orderId)->first();
        if (!$tx) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan secara lokal.',
            ], 404);
        }

        // 3. Gross amount verification
        if (abs(((float) $grossAmount) - ((float) $tx->amount)) > 0.01) {
            $tx->update(['status' => 'Failed']); // suspicious / failed
            return response()->json([
                'status' => 'error',
                'message' => 'Gross amount mismatch! Transaksi ditandai mencurigasi.',
            ], 400);
        }

        // 4. Idempotency: Ignore success notification if already success
        if ($tx->status === 'Success') {
            $dbNotification->update(['processed' => true]);
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi sudah success sebelumnya (idempotent).',
            ]);
        }

        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? '';

        $statusUpdate = 'Pending';
        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $statusUpdate = 'Success';
            }
        } elseif ($transactionStatus == 'settlement') {
            $statusUpdate = 'Success';
        } elseif ($transactionStatus == 'pending') {
            $statusUpdate = 'Pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $statusUpdate = 'Failed';
        }

        // 5. Update status using DB Transaction + lockForUpdate
        DB::transaction(function () use ($tx, $statusUpdate, $dbNotification) {
            // Lock the transaction row
            $lockedTx = Transaction::where('id', $tx->id)->lockForUpdate()->first();
            $lockedTx->update(['status' => $statusUpdate]);

            if ($statusUpdate === 'Success') {
                $plan = SavingsPlan::where('id', $lockedTx->savings_plan_id)->lockForUpdate()->first();
                if ($plan) {
                    $plan->collected_amount += $lockedTx->amount;
                    $plan->recalculateProgress();

                    // If collected >= target, update status to paid_off
                    if ($plan->collected_amount >= $plan->target_amount) {
                        app(SavingsPlanService::class)->transitionTo($plan, 'paid_off', 'Setoran lunas melalui Midtrans webhook.');
                    }
                }
            }

            $dbNotification->update(['processed' => true]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook berhasil diproses.',
            'order_id' => $orderId,
            'status_transaksi' => $transactionStatus,
        ]);
    }

    public function checkMidtransStatus(Request $request)
    {
        $txId = (int) $request->input('txId');
        $tx = Transaction::find($txId);

        if (!$tx) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan secara lokal.',
            ]);
        }

        $serverKey = $this->getServerKey();
        $orderId = $tx->order_id;

        $url = 'https://api.sandbox.midtrans.com/v2/' . $orderId . '/status';
        $authHeader = 'Basic ' . base64_encode($serverKey . ':');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $authHeader,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $transactionStatus = $data['transaction_status'] ?? '';
            $fraudStatus = $data['fraud_status'] ?? '';

            $statusUpdate = 'Pending';
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $statusUpdate = 'Success';
                }
            } elseif ($transactionStatus == 'settlement') {
                $statusUpdate = 'Success';
            } elseif ($transactionStatus == 'pending') {
                $statusUpdate = 'Pending';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $statusUpdate = 'Failed';
            }

            $this->updateStatusTransaksiPemberitahuan($orderId, $statusUpdate);

            return response()->json([
                'status' => 'success',
                'payment_status' => $statusUpdate,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil status dari Midtrans Sandbox (HTTP ' . $httpCode . ')',
            ]);
        }
    }

    public function simulatePayment(Request $request)
    {
        // Environment Check: only active when APP_ENV=local
        if (config('app.env') !== 'local' && env('APP_ENV') !== 'local') {
            return response()->json([
                'status' => 'error',
                'message' => 'Aksi simulasi pembayaran hanya aktif di lingkungan lokal (APP_ENV=local).',
            ], 403);
        }

        $savingsId = (int) $request->input('savings_id');
        $amount = (float) $request->input('amount');
        $paymentMethod = trim($request->input('payment_method', 'Simulasi'));

        if ($savingsId <= 0 || $amount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data input tidak valid.',
            ]);
        }

        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $day = date('j');
        $month = $months[date('n')];
        $year = date('Y');
        $txDate = "$day $month $year";
        $txTime = date('H:i') . ' WIB';

        $orderId = 'TQ-SIM-' . time() . '-' . rand(100, 999);

        try {
            DB::beginTransaction();

            $plan = SavingsPlan::lockForUpdate()->find($savingsId);
            if (!$plan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Rencana tabungan tidak ditemukan.',
                ]);
            }

            Transaction::create([
                'savings_plan_id' => $savingsId,
                'order_id' => $orderId,
                'type' => 'Top Up Saldo',
                'amount' => $amount,
                'date' => $txDate,
                'time' => $txTime,
                'status' => 'Success',
                'payment_method' => $paymentMethod,
                'channel' => 'Simulasi',
                'paid_at' => now(),
            ]);

            $plan->collected_amount += $amount;
            $plan->recalculateProgress();

            if ($plan->collected_amount >= $plan->target_amount) {
                app(SavingsPlanService::class)->transitionTo($plan, 'paid_off', 'Setoran lunas melalui simulasi pembayaran.');
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Simulasi setoran tabungan sebesar ' . $amount . ' sukses.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses simulasi: ' . $e->getMessage(),
            ]);
        }
    }

    private function updateStatusTransaksiPemberitahuan($orderId, $statusUpdate)
    {
        try {
            $tx = Transaction::where('order_id', $orderId)->first();

            if ($tx) {
                $oldStatus = $tx->status;
                if ($oldStatus === $statusUpdate) {
                    return;
                }

                DB::transaction(function () use ($tx, $statusUpdate) {
                    $lockedTx = Transaction::where('id', $tx->id)->lockForUpdate()->first();
                    $lockedTx->update(['status' => $statusUpdate]);

                    if ($statusUpdate === 'Success') {
                        $plan = SavingsPlan::where('id', $lockedTx->savings_plan_id)->lockForUpdate()->first();
                        if ($plan) {
                            $plan->collected_amount += $lockedTx->amount;
                            $plan->recalculateProgress();

                            if ($plan->collected_amount >= $plan->target_amount) {
                                app(SavingsPlanService::class)->transitionTo($plan, 'paid_off', 'Setoran lunas.');
                            }
                        }
                    }
                });
            }
        } catch (\Exception $e) {
            Log::error('Midtrans status sync error: ' . $e->getMessage());
        }
    }
}
