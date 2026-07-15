<?php

/**
 * midtrans-webhook.php
 * Endpoint Webhook / Callback untuk menerima notifikasi status pembayaran
 * dari server Midtrans secara server-to-server (sifatnya async).
 */
header('Content-Type: application/json');

require_once 'db.php';
$serverKey = $midtransServerKey;

// Membaca input JSON notifikasi dari Midtrans
$rawInput = file_get_contents('php://input');
$notification = json_decode($rawInput, true);

if (! $notification) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Payload kosong atau format salah.',
    ]);
    exit;
}

// Mengambil variabel penting untuk memvalidasi signature
$orderId = isset($notification['order_id']) ? $notification['order_id'] : '';
$statusCode = isset($notification['status_code']) ? $notification['status_code'] : '';
$grossAmount = isset($notification['gross_amount']) ? $notification['gross_amount'] : '';
$signatureKeyReceived = isset($notification['signature_key']) ? $notification['signature_key'] : '';

// Validasi Signature Key dari Midtrans untuk mengamankan endpoint dari fake requests
$localSignatureString = $orderId.$statusCode.$grossAmount.$serverKey;
$localSignatureKey = hash('sha512', $localSignatureString);

if ($localSignatureKey !== $signatureKeyReceived) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Signature Key tidak cocok! Request dicurigai palsu.',
    ]);
    exit;
}

// Ekstrak status transaksi
$transactionStatus = $notification['transaction_status'];
$paymentType = $notification['payment_type'];

/**
 * ==========================================
 * PROSES UPDATE STATUS TABUNGAN (PRODUKSI)
 * ==========================================
 * Di bawah ini adalah logika penanganan status transaksi.
 * Jika Anda menggunakan database (MySQL), lakukan query UPDATE di sini.
 */
if ($transactionStatus == 'capture') {
    // Untuk pembayaran kartu kredit (hanya sukses jika status fraud_status = 'accept')
    if (isset($notification['fraud_status']) && $notification['fraud_status'] == 'accept') {
        // Pembayaran berhasil diselesaikan
        updateStatusTransaksiPemberitahuan($orderId, 'Success', $grossAmount);
    }
} elseif ($transactionStatus == 'settlement') {
    // Pembayaran sukses diselesaikan (Transfer Bank VA, Gopay, QRIS, dll)
    updateStatusTransaksiPemberitahuan($orderId, 'Success', $grossAmount);
} elseif ($transactionStatus == 'pending') {
    // Pembayaran sedang menunggu pembayaran oleh user
    updateStatusTransaksiPemberitahuan($orderId, 'Pending', $grossAmount);
} elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
    // Pembayaran gagal, kedaluwarsa, atau dibatalkan
    updateStatusTransaksiPemberitahuan($orderId, 'Failed', $grossAmount);
}

// Kirim response status 200 OK ke Midtrans
echo json_encode([
    'status' => 'success',
    'message' => 'Webhook berhasil diproses.',
    'order_id' => $orderId,
    'status_transaksi' => $transactionStatus,
]);

/**
 * Fungsi pembantu simulasi untuk memperbarui database/file tabungan (jika ada)
 */
function updateStatusTransaksiPemberitahuan($orderId, $statusUpdate, $amount)
{
    global $pdo;

    // Logs for backup auditing
    $logMsg = date('[Y-m-d H:i:s]')." Order: {$orderId} | Status: {$statusUpdate} | Nominal: {$amount}\n";
    file_put_contents('midtrans_webhook_logs.txt', $logMsg, FILE_APPEND);

    try {
        // Query matching transaction from DB
        $stmt = $pdo->prepare('SELECT savings_id, amount, status FROM transactions WHERE order_id = ?');
        $stmt->execute([$orderId]);
        $tx = $stmt->fetch();

        if ($tx) {
            $savingsId = $tx['savings_id'];
            $txAmount = $tx['amount'];
            $oldStatus = $tx['status'];

            // Update transaction status
            $stmt = $pdo->prepare('UPDATE transactions SET status = ? WHERE order_id = ?');
            $stmt->execute([$statusUpdate, $orderId]);

            // If status changed to Success and was not Success before, credit savings account
            if ($statusUpdate === 'Success' && $oldStatus !== 'Success') {
                $stmt = $pdo->prepare('SELECT target_amount, current_amount FROM savings WHERE id = ?');
                $stmt->execute([$savingsId]);
                $savings = $stmt->fetch();

                if ($savings) {
                    $newCurrent = $savings['current_amount'] + $txAmount;
                    $newRemaining = max(0.0, $savings['target_amount'] - $newCurrent);
                    $newProgress = round(($newCurrent / $savings['target_amount']) * 100);
                    $newStatus = $newCurrent >= $savings['target_amount'] ? 'Dana Terkumpul' : 'Menabung';

                    $stmt = $pdo->prepare('UPDATE savings SET current_amount = ?, remaining_amount = ?, progress_percent = ?, status = ? WHERE id = ?');
                    $stmt->execute([$newCurrent, $newRemaining, $newProgress, $newStatus, $savingsId]);
                }
            }
        }
    } catch (PDOException $e) {
        file_put_contents('db_error_logs.txt', date('[Y-m-d H:i:s] webhook error: ').$e->getMessage()."\n", FILE_APPEND);
    }
}
