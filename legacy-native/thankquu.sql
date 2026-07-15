-- Database SQL Dump untuk ThankQuu
-- Dapat diimpor langsung ke phpMyAdmin atau aaPanel MySQL database

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Struktur Tabel `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(10) DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data default users (Password: password untuk ahmad, admin untuk admin)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`) VALUES
(1, 'Ahmad Fauzan', 'ahmad@thankquu.com', '081234567890', '$2y$10$fVp6GkEGBQdD8mB4t7eG6OBsEewmGzP0u5.3T56r.26yG3WJ1d21S', 'user'),
(2, 'Super Admin', 'admin@thankquu.com', '089876543210', '$2y$10$O0N8c0zF1.X5p7Ue6S4bGu1VwJgG5y28iFvTj5P5qQeM/a3.lG.y2', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------
-- Struktur Tabel `savings`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `savings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur Tabel `transactions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transactions` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Struktur Tabel `livestock`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `livestock` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default livestock packages
INSERT INTO `livestock` (`id`, `category`, `name`, `price`, `desc`, `weight`, `age`, `fit`, `image`) VALUES
('Domba-A', 'Domba', 'Domba A', 2300000.00, 'Domba sehat dengan kualitas prima untuk ibadah qurban berkah.', '23-26 kg', '1.2 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD01xxU7e937WQxR4nYZpcjwo3jrOsDL0hZredv170fV4494Sf4nk9mK9lA9C5la2mN7465F5kGeB0lfun46gDzvLGzgc3r_YJEc-TmkqzjKsC1VBRyTzO-ox9KmtD3xD8cU3bhz-mebaqpf7gnkhDuTirVNvOQnawacxh1Ibe7Xn3txcLOnO6fNSn8pIlhEStA1M4DefT4WQxQ_G_IBqrPm0z8RLaQx2cb2bSc-mvnDyF56DNfk0P86tyTWqwOjFRWjeZ88x6bGAoE'),
('Domba-B', 'Domba', 'Domba B', 2700000.00, 'Domba gemuk dengan nutrisi terjamin, cocok untuk keluarga.', '28-32 kg', '1.5 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAcSKoiNDvNcj7WRqA30TTDZ62jeDCtrrc51auT7bX9yL34Fh_OCm6WOVaYVJwyqMXDV_wBixrCvTKn8X9XtHY0n9zGfn4B-v-Hyg26rri-M52xuKRm7ZzVLBebahZV7Uvgyb4CFVDvS8jjUXvuS5k7CluJ_125V05rr_yWRX9ay0NoAzcs4vHiIyEPl_Qnnng7GtuIYZK6dYKw9iqNqFSlYvxrLktZEVrNW1nByzo1tcSZ1--Go092HFiwDmwKYQxtS9qoq6huwPwa'),
('Domba-C', 'Domba', 'Domba C (Super)', 3500000.00, 'Domba ukuran premium besar, daging melimpah, dirawat ahli.', '35-40 kg', '1.8 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBfBJ7wBa0ZumJYkwAP_2vKXYRW3p5izja6UrthrDxtCNP4-CmtWd3uT3Gi_H9OvaA6XGurQfR79O60dV7CZUi7RlhH7nACyjF5sDR37Pidun5WK_uPRonkN8wnAN_ynF09_A5OzZh7P0zefP5zlKu-vnxCqxv7ZY_FswmQu3Nk4l_U1vHZipy3imT74BJtfNJPSUr-EnoZJPwg6jfY8-017CgF8h3PTu4CfaJ29tTjXkiifDDMIZh-2g07sITg0Dgft4d9muCnm4Ed'),
('Sapi-Patungan', 'Sapi Patungan', 'Sapi Patungan (1/7)', 3500000.00, 'Patungan qurban sapi 1/7 kelompok. Pembagian kelompok diatur sistem otomatis syariah.', '300-350 kg (total Sapi)', '2.5 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_kA8C62RZ59pO-FPosUtY3y231uFZ-ZJ43nnjSnDRRcKH0Tdpt5W7chA1fcoIQVgP6q15dMZXWfSQjBOlxIUA-iYyb9iZGgLeAimE0OTUnANRw9O4UCgnbF4nRntL6WDlBQr80G6z3nH9j5x7zKpp28OouByRiLM5RXOjOQMx_pSoXZ4bqgDS4ryb2_LkFNZU27w6BFq5-e620LfwbVOUJxlt8RHkNBZpLvC7PADfsrCipWyajbmXmQfBwAZSehJKDT2AP2L_3TBM'),
('Sapi-Utuh', 'Sapi', 'Sapi 1 Ekor', 24000000.00, 'Sapi Limousin utuh premium untuk qurban atas nama keluarga besar Anda (7 orang).', '420-480 kg', '2.8 Tahun', 'Fit (Sehat)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDnGNcEb4t2Prwhfv2FLXutI-qb7nCCXFZkq-OXYSGIShiDll227LAvZfTcxENB1GUk1uyWzgklQ9Tfzcvk_lQYNR5TmSPIzw3lojNWIlq8qWMRG3qQqseg3JpIanIgRnZlDXPP5YZn-gy4eUkxtXqIMcNv1slowvpdQSNN4qKmGBKM92KX3HJ02YuoMGiDV7YuMqKJ3JYrzLRHi-opXIkTHO7I-_FhPYamarZw8nVwSHDDswfeq-Z1HLiioW5qFSOf41CIaFoev1ZO')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------
-- Struktur Tabel `locations`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `locations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `region` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `capacity` INT DEFAULT 100,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default locations
INSERT INTO `locations` (`id`, `name`, `region`, `description`, `capacity`, `latitude`, `longitude`) VALUES
(1, 'Palestina (Lembaga Penyalur)', 'Palestina', 'Penyaluran khusus berupa paket daging qurban beku/tin untuk saudara-saudara kita di Jalur Gaza.', 500, 31.95220000, 35.23320000),
(2, 'NTT - Waikabubak', 'Nusa Tenggara Timur', 'Penyaluran qurban di wilayah pelosok Waikabubak dengan tingkat stunting tinggi dan minoritas muslim.', 200, -9.64160000, 119.41240000),
(3, 'Papua Barat - Sorong', 'Papua Barat', 'Penyaluran qurban kepada jamaah mualaf di pelosok Sorong dan pulau-pulau sekitarnya.', 150, -0.87530000, 131.25200000),
(4, 'Desa Binaan Istiqomah', 'Jawa Barat', 'Wilayah peternakan binaan utama di pelosok Sukabumi untuk masyarakat pra-sejahtera.', 300, -6.91810000, 106.92660000)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seeding default savings and history for ahmad@thankquu.com
-- (Akan otomatis didaftarkan jika relasi id=1 dari users ada)
INSERT INTO `savings` (`id`, `user_id`, `package_id`, `package_name`, `package_type`, `target_amount`, `current_amount`, `remaining_amount`, `progress_percent`, `status`) VALUES
(1, 1, 'Sapi-Patungan', 'Sapi Patungan (1/7)', 'Sapi Patungan', 3500000.00, 2300000.00, 1200000.00, 66, 'Menabung')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `transactions` (`savings_id`, `order_id`, `type`, `amount`, `date`, `time`, `status`) VALUES
(1, 'TQ-TX-SEED-1', 'Top Up Saldo', 500000.00, '20 May 2026', '14:20 WIB', 'Success'),
(1, 'TQ-TX-SEED-2', 'Top Up Saldo', 1000000.00, '25 April 2026', '08:00 WIB', 'Success'),
(1, 'TQ-TX-SEED-3', 'Top Up Saldo', 800000.00, '12 April 2026', '10:45 WIB', 'Success')
ON DUPLICATE KEY UPDATE `order_id`=`order_id`;

SET FOREIGN_KEY_CHECKS=1;
