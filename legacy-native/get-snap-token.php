<?php

/**
 * get-snap-token.php
 * Endpoint untuk meminta Snap Token dari API Sandbox Midtrans.
 * Dipanggil oleh frontend (dashboard.html) via fetch POST request.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db.php';
$serverKey = $midtransServerKey;

// Membaca payload request POST dari frontend
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

$amount = isset($input['amount']) ? (int) $input['amount'] : 0;
$email = isset($input['email']) ? trim($input['email']) : '';
$name = isset($input['name']) ? trim($input['name']) : '';
$paymentMethod = isset($input['payment_method']) ? trim($input['payment_method']) : '';

// Validasi nominal pembayaran minimal
if ($amount < 10000) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nominal pembayaran minimal adalah Rp 10.000',
    ]);
    exit;
}

if (empty($email)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Alamat email pelanggan tidak boleh kosong.',
    ]);
    exit;
}

// Generate Order ID Unik (Format: TQ-TX-[WAKTU]-[RANDOM])
$orderId = 'TQ-TX-'.time().'-'.rand(100, 999);

// Menyusun detail transaksi untuk Midtrans
$transaction_details = [
    'order_id' => $orderId,
    'gross_amount' => $amount,
];

// Menyusun detail pelanggan
$customer_details = [
    'first_name' => $name ?: 'Jamaah',
    'email' => $email,
];

// Detail item (deskripsi setoran qurban)
$item_details = [
    [
        'id' => 'setoran-tabungan',
        'price' => $amount,
        'quantity' => 1,
        'name' => 'Setoran Cicilan Tabungan Qurban',
    ],
];

$savingsId = isset($input['savings_id']) ? trim($input['savings_id']) : '';

// Tentukan enabled_payments berdasarkan metode yang dipilih agar langsung lompat ke halaman detail pembayaran tersebut di Midtrans Snap
$enabledPayments = [];
if ($paymentMethod === 'Virtual Account') {
    $enabledPayments = ['bni_va'];
} elseif ($paymentMethod === 'E-Wallet') {
    $enabledPayments = ['gopay'];
}

// Payload JSON lengkap untuk Midtrans Snap API
$payload = [
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $item_details,
    'custom_field1' => $savingsId,
];

if (! empty($enabledPayments)) {
    $payload['enabled_payments'] = $enabledPayments;
}

// URL Endpoint Sandbox Snap Midtrans
$url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

// Header Otorisasi Basic (ServerKey di-encode Base64 ditambah titik dua ':')
$authHeader = 'Basic '.base64_encode($serverKey.':');

// Eksekusi HTTP POST menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: '.$authHeader,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Kembalikan response dari Midtrans ke frontend
if ($httpCode === 200 || $httpCode === 201) {
    $decodedResponse = json_decode($response, true);
    $token = isset($decodedResponse['token']) ? $decodedResponse['token'] : '';

    if (! empty($token) && ! empty($savingsId)) {
        // Simpan transaksi status Pending ke DB
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $day = date('j');
        $month = $months[date('n')];
        $year = date('Y');
        $txDate = "$day $month $year";
        $txTime = date('H:i').' WIB';

        try {
            $stmt = $pdo->prepare("INSERT INTO transactions (savings_id, order_id, type, amount, date, time, status, token, payment_method) VALUES (?, ?, 'Top Up Saldo', ?, ?, ?, 'Pending', ?, ?)");
            $stmt->execute([$savingsId, $orderId, $amount, $txDate, $txTime, $token, $paymentMethod]);
        } catch (PDOException $e) {
            file_put_contents('db_error_logs.txt', date('[Y-m-d H:i:s] ').$e->getMessage()."\n", FILE_APPEND);
        }
    }
    echo $response;
} else {
    http_response_code($httpCode);
    $decodedResponse = json_decode($response, true);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil snap token dari Sandbox Midtrans',
        'midtrans_error' => $decodedResponse ?: $response,
    ]);
}
