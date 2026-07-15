<?php
// db.php
require_once 'config.php';

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_DATABASE') ?: 'thankquu';

try {
    // 1. Hubungkan langsung ke database (rekomendasi untuk VPS / aaPanel)
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Fallback: Jika database belum dibuat, coba buat secara manual (untuk local dev / Laragon)
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");
    } catch (PDOException $e2) {
        throw new PDOException("Koneksi database gagal: " . $e2->getMessage());
    }
}
    
    // Buat tabel users jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) UNIQUE NOT NULL,
        `phone` VARCHAR(20) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `role` VARCHAR(10) DEFAULT 'user',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Buat tabel savings jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS `savings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `package_id` VARCHAR(50) NOT NULL,
        `package_name` VARCHAR(100) NOT NULL,
        `package_type` VARCHAR(50) NOT NULL,
        `target_amount` DECIMAL(15, 2) NOT NULL,
        `current_amount` DECIMAL(15, 2) NOT NULL,
        `remaining_amount` DECIMAL(15, 2) NOT NULL,
        `progress_percent` INT DEFAULT 0,
        `next_payment_deadline` VARCHAR(50) DEFAULT '25 June 2026',
        `status` VARCHAR(20) DEFAULT 'Menabung',
        `penyaluran_method` VARCHAR(50) NULL,
        `penyaluran_receiver` VARCHAR(100) NULL,
        `penyaluran_phone` VARCHAR(20) NULL,
        `penyaluran_address` TEXT NULL,
        `penyaluran_status` VARCHAR(50) NULL,
        `cert_number` VARCHAR(50) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Buat tabel transactions jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS `transactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `savings_id` INT NOT NULL,
        `order_id` VARCHAR(100) UNIQUE NOT NULL,
        `type` VARCHAR(50) NOT NULL,
        `amount` DECIMAL(15, 2) NOT NULL,
        `date` VARCHAR(50) NOT NULL,
        `time` VARCHAR(50) NOT NULL,
        `status` VARCHAR(20) DEFAULT 'Pending',
        `token` VARCHAR(255) NULL,
        `payment_method` VARCHAR(50) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`savings_id`) REFERENCES `savings`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Buat tabel livestock jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS `livestock` (
        `id` VARCHAR(50) PRIMARY KEY,
        `category` VARCHAR(50) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `price` DECIMAL(15, 2) NOT NULL,
        `desc` TEXT NOT NULL,
        `weight` VARCHAR(50) NOT NULL,
        `age` VARCHAR(50) NOT NULL,
        `fit` VARCHAR(50) NOT NULL,
        `image` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Upgrade image column to TEXT if it was created as VARCHAR(255) in a previous version
    $pdo->exec("ALTER TABLE `livestock` MODIFY COLUMN `image` TEXT NOT NULL");

    // Buat tabel locations jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS `locations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `region` VARCHAR(100) NOT NULL,
        `description` TEXT NOT NULL,
        `capacity` INT NOT NULL,
        `latitude` DECIMAL(10, 8) DEFAULT 0.00000000,
        `longitude` DECIMAL(11, 8) DEFAULT 0.00000000,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Seeding default users jika tabel users kosong
    $stmt = $pdo->query("SELECT COUNT(*) FROM `users`");
    if ($stmt->fetchColumn() == 0) {
        $hashedUserPass = password_hash('password', PASSWORD_BCRYPT);
        $hashedAdminPass = password_hash('admin', PASSWORD_BCRYPT);
        
        $pdo->exec("INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
            ('Ahmad Fauzan', 'ahmad@thankquu.com', '081234567890', '$hashedUserPass', 'user'),
            ('Super Admin', 'admin@thankquu.com', '089876543210', '$hashedAdminPass', 'admin')
        ");
    }

    // Seeding default livestock jika tabel livestock kosong
    $stmt = $pdo->query("SELECT COUNT(*) FROM `livestock`");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `livestock` (`id`, `category`, `name`, `price`, `desc`, `weight`, `age`, `fit`, `image`) VALUES
            ('Domba-A', 'Domba', 'Domba A', 2300000.00, 'Domba sehat dengan kualitas prima untuk ibadah qurban berkah.', '23-26 kg', '1.2 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD01xxU7e937WQxR4nYZpcjwo3jrOsDL0hZredv170fV4494Sf4nk9mK9lA9C5la2mN7465F5kGeB0lfun46gDzvLGzgc3r_YJEc-TmkqzjKsC1VBRyTzO-ox9KmtD3xD8cU3bhz-mebaqpf7gnkhDuTirVNvOQnawacxh1Ibe7Xn3txcLOnO6fNSn8pIlhEStA1M4DefT4WQxQ_G_IBqrPm0z8RLaQx2cb2bSc-mvnDyF56DNfk0P86tyTWqwOjFRWjeZ88x6bGAoE'),
            ('Domba-B', 'Domba', 'Domba B', 2700000.00, 'Domba gemuk dengan nutrisi terjamin, cocok untuk keluarga.', '28-32 kg', '1.5 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcSKoiNDvNcj7WRqA30TTDZ62jeDCtrrc51auT7bX9yL34Fh_OCm6WOVaYVJwyqMXDV_wBixrCvTKn8X9XtHY0n9zGfn4B-v-Hyg26rri-M52xuKRm7ZzVLBebahZV7Uvgyb4CFVDvS8jjUXvuS5k7CluJ_125V05rr_yWRX9ay0NoAzcs4vHiIyEPl_Qnnng7GtuIYZK6dYKw9iqNqFSlYvxrLktZEVrNW1nByzo1tcSZ1--Go092HFiwDmwKYQxtS9qoq6huwPwa'),
            ('Domba-C', 'Domba', 'Domba C (Super)', 3500000.00, 'Domba ukuran premium besar, daging melimpah, dirawat ahli.', '35-40 kg', '1.8 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBfBJ7wBa0ZumJYkwAP_2vKXYRW3p5izja6UrthrDxtCNP4-CmtWd3uT3Gi_H9OvaA6XGurQfR79O60dV7CZUi7RlhH7nACyjF5sDR37Pidun5WK_uPRonkN8wnAN_ynF09_A5OzZh7P0zefP5zlKu-vnxCqxv7ZY_FswmQu3Nk4l_U1vHZipy3imT74BJtfNJPSUr-EnoZJPwg6jfY8-017CgF8h3PTu4CfaJ29tTjXkiifDDMIZh-2g07sITg0Dgft4d9muCnm4Ed'),
            ('Sapi-Patungan', 'Sapi Patungan', 'Sapi Patungan (1/7)', 3500000.00, 'Patungan qurban sapi 1/7 kelompok. Pembagian kelompok diatur sistem otomatis syariah.', '300-350 kg (total Sapi)', '2.5 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_kA8C62RZ59pO-FPosUtY3y231uFZ-ZJ43nnjSnDRRcKH0Tdpt5W7chA1fcoIQVgP6q15dMZXWfSQjBOlxIUA-iYyb9iZGgLeAimE0OTUnANRw9O4UCgnbF4nRntL6WDlBQr80G6z3nH9j5x7zKpp28OouByRiLM5RXOjOQMx_pSoXZ4bqgDS4ryb2_LkFNZU27w6BFq5-e620LfwbVOUJxlt8RHkNBZpLvC7PADfsrCipWyajbmXmQfBwAZSehJKDT2AP2L_3TBM'),
            ('Sapi-Utuh', 'Sapi', 'Sapi 1 Ekor', 24000000.00, 'Sapi Limousin utuh premium untuk qurban atas nama keluarga besar Anda (7 orang).', '420-480 kg', '2.8 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDnGNcEb4t2Prwhfv2FLXutI-qb7nCCXFZkq-OXYSGIShiDll227LAvZfTcxENB1GUk1uyWzgklQ9Tfzcvk_lQYNR5TmSPIzw3lojNWIlq8qWMRG3qQqseg3JpIanIgRnZlDXPP5YZn-gy4eUkxtXqIMcNv1slowvpdQSNN4qKmGBKM92KX3HJ02YuoMGiDV7YuMqKJ3JYrzLRHi-opXIkTHO7I-_FhPYamarZw8nVwSHDDswfeq-Z1HLiioW5qFSOf41CIaFoev1ZO')
        ");
    }

    // Seeding default locations jika tabel locations kosong
    $stmt = $pdo->query("SELECT COUNT(*) FROM `locations`");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `locations` (`name`, `region`, `description`, `capacity`, `latitude`, `longitude`) VALUES
            ('Palestina (Lembaga Penyalur)', 'Palestina', 'Penyaluran khusus berupa paket daging qurban beku/tin untuk saudara-saudara kita di Jalur Gaza.', 500, 31.95220000, 35.23320000),
            ('NTT - Waikabubak', 'Nusa Tenggara Timur', 'Penyaluran qurban di wilayah pelosok Waikabubak dengan tingkat stunting tinggi dan minoritas muslim.', 200, -9.64160000, 119.41240000),
            ('Papua Barat - Sorong', 'Papua Barat', 'Penyaluran qurban kepada jamaah mualaf di pelosok Sorong dan pulau-pulau sekitarnya.', 150, -0.87530000, 131.25200000),
            ('Desa Binaan Istiqomah', 'Jawa Barat', 'Wilayah peternakan binaan utama di pelosok Sukabumi untuk masyarakat pra-sejahtera.', 300, -6.91810000, 106.92660000)
        ");
    }

    // Seeding default savings jika user ahmad@thankquu.com belum memiliki savings
    $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $userStmt->execute(['ahmad@thankquu.com']);
    $ahmadId = $userStmt->fetchColumn();
    
    if ($ahmadId) {
        $savingsStmt = $pdo->prepare("SELECT COUNT(*) FROM savings WHERE user_id = ?");
        $savingsStmt->execute([$ahmadId]);
        if ($savingsStmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO `savings` 
                (`user_id`, `package_id`, `package_name`, `package_type`, `target_amount`, `current_amount`, `remaining_amount`, `progress_percent`, `status`) 
                VALUES 
                ($ahmadId, 'Sapi-Patungan', 'Sapi Patungan (1/7)', 'Sapi Patungan', 3500000.00, 2300000.00, 1200000.00, 66, 'Menabung')
            ");
            
            $savingsId = $pdo->lastInsertId();
            
            $pdo->exec("INSERT INTO `transactions` (`savings_id`, `order_id`, `type`, `amount`, `date`, `time`, `status`) VALUES
                ($savingsId, 'TQ-TX-SEED-1', 'Top Up Saldo', 500000.00, '20 May 2026', '14:20 WIB', 'Success'),
                ($savingsId, 'TQ-TX-SEED-2', 'Top Up Saldo', 1000000.00, '25 April 2026', '08:00 WIB', 'Success'),
                ($savingsId, 'TQ-TX-SEED-3', 'Top Up Saldo', 800000.00, '12 April 2026', '10:45 WIB', 'Success')
            ");
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Koneksi database gagal: ' . $e->getMessage()
    ]);
    exit;
}
