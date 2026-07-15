<?php

/**
 * get-client-key.php
 * Endpoint untuk membagikan Client Key Midtrans dari .env ke frontend.
 * Dipanggil secara dinamis oleh dashboard.html saat halaman dimuat.
 */

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'client_key' => $midtransClientKey,
]);
