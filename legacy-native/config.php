<?php

/**
 * config.php
 * Helper untuk memuat variabel lingkungan dari file .env secara native.
 * Digunakan oleh get-snap-token.php dan midtrans-webhook.php.
 */
if (! function_exists('loadEnv')) {
    function loadEnv($path)
    {
        if (! file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Abaikan baris komentar
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Cari pembatas '='
            if (strpos($line, '=') !== false) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Hapus tanda kutip pembungkus jika ada
                $value = trim($value, '"\'');

                // Daftarkan ke environment variables jika belum terdaftar
                if (! array_key_exists($name, $_SERVER) && ! array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Load file .env di directory root
loadEnv(__DIR__.'/.env');

// Definisikan variable config global
$midtransServerKey = getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-PLACEHOLDER';
$midtransClientKey = getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-PLACEHOLDER';
