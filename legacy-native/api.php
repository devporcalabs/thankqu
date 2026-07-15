<?php

// api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db.php'; // Hubungkan database dan auto-installer

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: [];

if ($action === 'login') {
    $email = isset($input['email']) ? trim(strtolower($input['email'])) : '';
    $password = isset($input['password']) ? $input['password'] : '';

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email dan password tidak boleh kosong.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            'status' => 'success',
            'user' => [
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role'],
            ],
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email atau password salah!']);
    }
    exit;
}

if ($action === 'register') {
    $name = isset($input['name']) ? trim($input['name']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $email = isset($input['email']) ? trim(strtolower($input['email'])) : '';
    $password = isset($input['password']) ? $input['password'] : '';

    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi.']);
        exit;
    }

    // Periksa apakah email sudah ada
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email sudah terdaftar!']);
        exit;
    }

    $hashedPass = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, 'user')");
    $stmt->execute([$name, $phone, $email, $hashedPass]);

    echo json_encode([
        'status' => 'success',
        'user' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'user',
        ],
    ]);
    exit;
}

if ($action === 'get_dashboard_data') {
    $email = isset($_GET['email']) ? trim(strtolower($_GET['email'])) : '';

    // Auto-update pending transactions older than 24 hours to 'Failed'
    try {
        $pdo->exec("UPDATE transactions SET status = 'Failed' WHERE status = 'Pending' AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    } catch (PDOException $e) {
        // Silently ignore
    }

    // Dapatkan user ID
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();

    if (! $userId) {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
        exit;
    }

    // Dapatkan semua tabungan untuk user ini
    $stmt = $pdo->prepare('SELECT * FROM savings WHERE user_id = ?');
    $stmt->execute([$userId]);
    $savings = $stmt->fetchAll();

    // Untuk setiap tabungan, dapatkan riwayat transaksinya
    foreach ($savings as &$saving) {
        $txStmt = $pdo->prepare('SELECT id, type, amount, date, time, status, token, payment_method FROM transactions WHERE savings_id = ? ORDER BY id DESC');
        $txStmt->execute([$saving['id']]);
        $saving['history'] = $txStmt->fetchAll();

        // Bentuk format penyaluran agar cocok dengan frontend
        if ($saving['penyaluran_method']) {
            $saving['penyaluran'] = [
                'method' => $saving['penyaluran_method'],
                'receiver' => $saving['penyaluran_receiver'],
                'phone' => $saving['penyaluran_phone'],
                'address' => $saving['penyaluran_address'],
                'status' => $saving['penyaluran_status'],
            ];
        } else {
            $saving['penyaluran'] = null;
        }
    }

    // Dapatkan daftar paket/livestock dari DB
    $stmt = $pdo->query('SELECT * FROM livestock');
    $packages = $stmt->fetchAll();

    // Dapatkan daftar lokasi qurban
    $stmt = $pdo->query('SELECT * FROM locations');
    $locations = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'savings' => $savings,
        'packages' => $packages,
        'locations' => $locations,
    ]);
    exit;
}

if ($action === 'create_savings') {
    $email = isset($input['email']) ? trim(strtolower($input['email'])) : '';
    $packageId = isset($input['packageId']) ? trim($input['packageId']) : '';
    $packageName = isset($input['packageName']) ? trim($input['packageName']) : '';
    $packageType = isset($input['packageType']) ? trim($input['packageType']) : '';
    $targetAmount = isset($input['targetAmount']) ? (float) $input['targetAmount'] : 0.0;
    $initialAmount = isset($input['initialAmount']) ? (float) $input['initialAmount'] : 0.0;

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();

    if (! $userId) {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
        exit;
    }

    $remainingAmount = $targetAmount;
    $progressPercent = 0;
    $status = 'Menabung';

    $stmt = $pdo->prepare('INSERT INTO savings (user_id, package_id, package_name, package_type, target_amount, current_amount, remaining_amount, progress_percent, status) VALUES (?, ?, ?, ?, ?, 0.0, ?, ?, ?)');
    $stmt->execute([$userId, $packageId, $packageName, $packageType, $targetAmount, $remainingAmount, $progressPercent, $status]);
    $savingsId = $pdo->lastInsertId();

    // Catat transaksi setoran awal sebagai Pending jika lebih dari 0
    if ($initialAmount > 0) {
        $orderId = 'TQ-TX-'.time().'-'.rand(100, 999);
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $day = date('j');
        $month = $months[date('n')];
        $year = date('Y');
        $txDate = "$day $month $year";
        $txTime = date('H:i').' WIB';

        $stmt = $pdo->prepare("INSERT INTO transactions (savings_id, order_id, type, amount, date, time, status) VALUES (?, ?, 'Setoran Awal', ?, ?, ?, 'Pending')");
        $stmt->execute([$savingsId, $orderId, $initialAmount, $txDate, $txTime]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Tabungan berhasil dibuat.',
        'savings_id' => $savingsId,
        'initial_amount' => $initialAmount,
    ]);
    exit;
}

if ($action === 'save_penyaluran') {
    $savingsId = isset($input['savings_id']) ? (int) $input['savings_id'] : 0;
    $method = isset($input['method']) ? trim($input['method']) : '';
    $receiver = isset($input['receiver']) ? trim($input['receiver']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $address = isset($input['address']) ? trim($input['address']) : '';

    $stmt = $pdo->prepare("UPDATE savings SET penyaluran_method = ?, penyaluran_receiver = ?, penyaluran_phone = ?, penyaluran_address = ?, penyaluran_status = 'Penyaluran Dipilih' WHERE id = ?");
    $stmt->execute([$method, $receiver, $phone, $address, $savingsId]);

    echo json_encode(['status' => 'success', 'message' => 'Metode penyaluran berhasil disimpan.']);
    exit;
}

if ($action === 'get_admin_data') {
    // 1. Hitung total user
    $totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

    // 2. Hitung total saldo tabungan terkumpul
    $totalSavings = $pdo->query('SELECT SUM(current_amount) FROM savings')->fetchColumn() ?: 0;

    // 3. Dapatkan daftar pengguna
    $users = $pdo->query('SELECT id, name, email, phone, role FROM users ORDER BY id DESC')->fetchAll();

    // 4. Dapatkan daftar transaksi ledger
    $txQuery = $pdo->query('SELECT t.id as txId, t.savings_id, t.order_id, t.type, t.amount, t.date, t.time, t.status, s.package_name as packageName, u.name as userName, u.email FROM transactions t JOIN savings s ON t.savings_id = s.id JOIN users u ON s.user_id = u.id ORDER BY t.id DESC');
    $transactions = $txQuery->fetchAll();

    // 5. Dapatkan daftar progres fisik qurban
    $timelineQuery = $pdo->query('SELECT s.id, s.package_name as packageName, s.penyaluran_method, s.penyaluran_address, s.penyaluran_status, u.name as userName, u.email FROM savings s JOIN users u ON s.user_id = u.id WHERE s.penyaluran_method IS NOT NULL ORDER BY s.id DESC');
    $timelines = $timelineQuery->fetchAll();

    // 6. Dapatkan daftar livestock
    $livestock = $pdo->query('SELECT * FROM livestock ORDER BY created_at DESC')->fetchAll();

    // 7. Dapatkan daftar lokasi qurban
    $locations = $pdo->query('SELECT * FROM locations ORDER BY created_at DESC')->fetchAll();

    echo json_encode([
        'status' => 'success',
        'stats' => [
            'total_users' => $totalUsers,
            'total_savings' => $totalSavings,
            'total_livestock' => count($livestock),
        ],
        'users' => $users,
        'transactions' => $transactions,
        'timelines' => $timelines,
        'livestock' => $livestock,
        'locations' => $locations,
    ]);
    exit;
}

if ($action === 'update_timeline') {
    $savingsId = isset($input['savings_id']) ? (int) $input['savings_id'] : 0;
    $newStatus = isset($input['status']) ? trim($input['status']) : '';

    $stmt = $pdo->prepare('UPDATE savings SET penyaluran_status = ? WHERE id = ?');
    $stmt->execute([$newStatus, $savingsId]);

    if ($newStatus === 'Laporan Selesai') {
        $stmt = $pdo->prepare("UPDATE savings SET status = 'Selesai' WHERE id = ?");
        $stmt->execute([$savingsId]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Status progres fisik berhasil diperbarui.']);
    exit;
}

if ($action === 'toggle_user_role') {
    $email = isset($input['email']) ? trim(strtolower($input['email'])) : '';

    $stmt = $pdo->prepare('SELECT role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $role = $stmt->fetchColumn();

    if ($role) {
        $newRole = $role === 'admin' ? 'user' : 'admin';
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE email = ?');
        $stmt->execute([$newRole, $email]);
        echo json_encode(['status' => 'success', 'message' => 'Role berhasil diubah.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
    }
    exit;
}

if ($action === 'delete_user') {
    $email = isset($input['email']) ? trim(strtolower($input['email'])) : '';

    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ? AND role != 'admin'");
    $stmt->execute([$email]);

    echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus.']);
    exit;
}

if ($action === 'add_livestock') {
    $id = isset($input['id']) ? trim($input['id']) : '';
    $category = isset($input['category']) ? trim($input['category']) : '';
    $name = isset($input['name']) ? trim($input['name']) : '';
    $price = isset($input['price']) ? (float) $input['price'] : 0.0;
    $desc = isset($input['desc']) ? trim($input['desc']) : '';
    $weight = isset($input['weight']) ? trim($input['weight']) : '';
    $age = isset($input['age']) ? trim($input['age']) : '';
    $fit = isset($input['fit']) ? trim($input['fit']) : '';
    $image = isset($input['image']) ? trim($input['image']) : '';

    $stmt = $pdo->prepare('INSERT INTO livestock (id, category, name, price, `desc`, weight, age, fit, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$id, $category, $name, $price, $desc, $weight, $age, $fit, $image]);

    echo json_encode(['status' => 'success', 'message' => 'Hewan qurban berhasil ditambahkan.']);
    exit;
}

if ($action === 'edit_livestock') {
    $id = isset($input['id']) ? trim($input['id']) : '';
    $category = isset($input['category']) ? trim($input['category']) : '';
    $name = isset($input['name']) ? trim($input['name']) : '';
    $price = isset($input['price']) ? (float) $input['price'] : 0.0;
    $desc = isset($input['desc']) ? trim($input['desc']) : '';
    $weight = isset($input['weight']) ? trim($input['weight']) : '';
    $age = isset($input['age']) ? trim($input['age']) : '';
    $fit = isset($input['fit']) ? trim($input['fit']) : '';
    $image = isset($input['image']) ? trim($input['image']) : '';

    if (empty($id) || empty($category) || empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'ID, Kategori, dan Nama wajib diisi.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE livestock SET category = ?, name = ?, price = ?, `desc` = ?, weight = ?, age = ?, fit = ?, image = ? WHERE id = ?');
    $stmt->execute([$category, $name, $price, $desc, $weight, $age, $fit, $image, $id]);

    echo json_encode(['status' => 'success', 'message' => 'Hewan qurban berhasil diperbarui.']);
    exit;
}

if ($action === 'delete_livestock') {
    $id = isset($input['id']) ? trim($input['id']) : '';

    $stmt = $pdo->prepare('DELETE FROM livestock WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success', 'message' => 'Hewan qurban berhasil dihapus.']);
    exit;
}

if ($action === 'simulate_payment') {
    $savingsId = isset($input['savings_id']) ? (int) $input['savings_id'] : 0;
    $amount = isset($input['amount']) ? (float) $input['amount'] : 0.0;
    $paymentMethod = isset($input['payment_method']) ? trim($input['payment_method']) : 'Simulasi';

    if ($savingsId <= 0 || $amount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Data input tidak valid.']);
        exit;
    }

    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $day = date('j');
    $month = $months[date('n')];
    $year = date('Y');
    $txDate = "$day $month $year";
    $txTime = date('H:i').' WIB';

    $orderId = 'TQ-SIM-'.time().'-'.rand(100, 999);

    try {
        $pdo->beginTransaction();

        // 1. Catat transaksi Success
        $stmt = $pdo->prepare("INSERT INTO transactions (savings_id, order_id, type, amount, date, time, status, payment_method) VALUES (?, ?, 'Top Up Saldo', ?, ?, ?, 'Success', ?)");
        $stmt->execute([$savingsId, $orderId, $amount, $txDate, $txTime, $paymentMethod]);

        // 2. Update status & nominal tabungan
        $stmt = $pdo->prepare('SELECT target_amount, current_amount FROM savings WHERE id = ?');
        $stmt->execute([$savingsId]);
        $savings = $stmt->fetch();

        if ($savings) {
            $newCurrent = $savings['current_amount'] + $amount;
            $newRemaining = max(0.0, $savings['target_amount'] - $newCurrent);
            $newProgress = round(($newCurrent / $savings['target_amount']) * 100);
            $newStatus = $newCurrent >= $savings['target_amount'] ? 'Dana Terkumpul' : 'Menabung';

            $stmt = $pdo->prepare('UPDATE savings SET current_amount = ?, remaining_amount = ?, progress_percent = ?, status = ? WHERE id = ?');
            $stmt->execute([$newCurrent, $newRemaining, $newProgress, $newStatus, $savingsId]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Simulasi setoran tabungan sebesar '.$amount.' sukses.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Gagal memproses simulasi: '.$e->getMessage()]);
    }
    exit;
}

if ($action === 'approve_transaction') {
    $txId = isset($input['txId']) ? (int) $input['txId'] : 0;
    $orderId = isset($input['orderId']) ? trim($input['orderId']) : '';

    // Ambil data transaksi
    if ($txId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM transactions WHERE id = ?');
        $stmt->execute([$txId]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM transactions WHERE order_id = ?');
        $stmt->execute([$orderId]);
    }
    $tx = $stmt->fetch();

    if ($tx && $tx['status'] === 'Pending') {
        // Update transaksi ke Success
        if ($txId > 0) {
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'Success' WHERE id = ?");
            $stmt->execute([$txId]);
        } else {
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'Success' WHERE order_id = ?");
            $stmt->execute([$orderId]);
        }

        // Update saldo savings
        $savingsId = $tx['savings_id'];
        $stmt = $pdo->prepare('SELECT target_amount, current_amount FROM savings WHERE id = ?');
        $stmt->execute([$savingsId]);
        $savings = $stmt->fetch();

        if ($savings) {
            $newCurrent = $savings['current_amount'] + $tx['amount'];
            $newRemaining = max(0.0, $savings['target_amount'] - $newCurrent);
            $newProgress = round(($newCurrent / $savings['target_amount']) * 100);
            $newStatus = $newCurrent >= $savings['target_amount'] ? 'Dana Terkumpul' : 'Menabung';

            $stmt = $pdo->prepare('UPDATE savings SET current_amount = ?, remaining_amount = ?, progress_percent = ?, status = ? WHERE id = ?');
            $stmt->execute([$newCurrent, $newRemaining, $newProgress, $newStatus, $savingsId]);
        }

        echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil disetujui.']);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Transaksi tidak ditemukan, sudah diproses, atau statusnya bukan Pending.',
            'debug' => ['txId' => $txId, 'orderId' => $orderId, 'found' => $tx ? true : false, 'status' => $tx ? $tx['status'] : null],
        ]);
    }
    exit;
}

if ($action === 'save_certificate') {
    $savingsId = isset($input['savings_id']) ? (int) $input['savings_id'] : 0;
    $certNumber = isset($input['cert_number']) ? trim($input['cert_number']) : '';

    $stmt = $pdo->prepare('UPDATE savings SET cert_number = ? WHERE id = ?');
    $stmt->execute([$certNumber, $savingsId]);

    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'get_livestock') {
    $stmt = $pdo->query('SELECT * FROM livestock ORDER BY created_at DESC');
    $livestock = $stmt->fetchAll();
    echo json_encode($livestock);
    exit;
}

if ($action === 'get_locations') {
    $stmt = $pdo->query('SELECT * FROM locations ORDER BY created_at DESC');
    $locations = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'locations' => $locations]);
    exit;
}

if ($action === 'add_location') {
    $name = isset($input['name']) ? trim($input['name']) : '';
    $region = isset($input['region']) ? trim($input['region']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $capacity = isset($input['capacity']) ? (int) $input['capacity'] : 0;
    $latitude = isset($input['latitude']) ? (float) $input['latitude'] : 0.0;
    $longitude = isset($input['longitude']) ? (float) $input['longitude'] : 0.0;

    if (empty($name) || empty($region)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan Wilayah lokasi wajib diisi.']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO locations (name, region, description, capacity, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $region, $description, $capacity, $latitude, $longitude]);

    echo json_encode(['status' => 'success', 'message' => 'Lokasi qurban berhasil ditambahkan.']);
    exit;
}

if ($action === 'edit_location') {
    $id = isset($input['id']) ? (int) $input['id'] : 0;
    $name = isset($input['name']) ? trim($input['name']) : '';
    $region = isset($input['region']) ? trim($input['region']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $capacity = isset($input['capacity']) ? (int) $input['capacity'] : 0;
    $latitude = isset($input['latitude']) ? (float) $input['latitude'] : 0.0;
    $longitude = isset($input['longitude']) ? (float) $input['longitude'] : 0.0;

    if (empty($id) || empty($name) || empty($region)) {
        echo json_encode(['status' => 'error', 'message' => 'ID, Nama, dan Wilayah lokasi wajib diisi.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE locations SET name = ?, region = ?, description = ?, capacity = ?, latitude = ?, longitude = ? WHERE id = ?');
    $stmt->execute([$name, $region, $description, $capacity, $latitude, $longitude, $id]);

    echo json_encode(['status' => 'success', 'message' => 'Lokasi qurban berhasil diperbarui.']);
    exit;
}

if ($action === 'delete_location') {
    $id = isset($input['id']) ? (int) $input['id'] : 0;

    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID lokasi tidak valid.']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM locations WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success', 'message' => 'Lokasi qurban berhasil dihapus.']);
    exit;
}

// Fungsi pembantu tanggal dan waktu
function getCurrentDateString()
{
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $day = date('j');
    $month = $months[date('n')];
    $year = date('Y');

    return "$day $month $year";
}

function getCurrentTimeString()
{
    return date('H:i').' WIB';
}
