<?php
// verify.php
require_once 'db.php';

$cert = isset($_GET['cert']) ? trim($_GET['cert']) : '';
$found = false;
$saving = null;

if (! empty($cert)) {
    try {
        $stmt = $pdo->prepare('
            SELECT s.*, u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM savings s
            JOIN users u ON s.user_id = u.id
            WHERE s.cert_number = ?
        ');
        $stmt->execute([$cert]);
        $saving = $stmt->fetch();
        if ($saving) {
            $found = true;
        }
    } catch (PDOException $e) {
        // Silently catch database issues on verify page
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
  <title>Verifikasi Sertifikat Qurban - Tabungan Qurban Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link rel="stylesheet" href="app.css">
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#006b2c",
            "primary-container": "#00873a",
            "on-primary": "#ffffff",
            "on-primary-container": "#f7fff2",
            "secondary-container": "#6cf8bb",
            "on-secondary-container": "#00714d",
            "surface-container-lowest": "#ffffff",
            "surface-container-low": "#f2f4f6",
            "surface-container": "#eceef0",
            "on-surface": "#191c1e",
            "on-surface-variant": "#3e4a3d",
            "outline-variant": "#bdcaba",
            "background": "#f7f9fb",
            "on-background": "#191c1e"
          }
        }
      }
    }
  </script>
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
  </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col justify-center items-center px-6 py-12 relative overflow-x-hidden">
  
  <div class="absolute inset-0 islamic-pattern pointer-events-none -z-10"></div>
  <div class="absolute top-10 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
  <div class="absolute bottom-10 left-0 w-64 h-64 bg-secondary-container/10 rounded-full blur-3xl -z-10 -translate-x-1/2"></div>

  <!-- Main Container -->
  <div class="w-full max-w-md bg-white rounded-3xl border border-outline-variant/30 shadow-xl p-8 relative overflow-hidden">
    
    <!-- Decorative Border -->
    <div class="absolute inset-2 border border-amber-600/20 rounded-2xl pointer-events-none"></div>
    <div class="absolute inset-3.5 border-[2px] border-primary/10 rounded-xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col items-center">
      
      <!-- App Logo -->
      <a href="index.html" class="flex items-center gap-2 mb-6">
        <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center shadow-md">
          <span class="material-symbols-outlined text-white text-[18px]">payments</span>
        </div>
        <span class="font-bold text-md text-primary tracking-tight">Tabungan Qurban</span>
      </a>

      <?php if ($found) { ?>
        <!-- STATUS: VERIFIED -->
        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mb-4 shadow-sm border border-emerald-100 animate-bounce">
          <span class="material-symbols-outlined text-3xl font-bold" style="font-variation-settings: 'FILL' 1;">verified</span>
        </div>
        
        <div class="inline-flex items-center gap-1.5 bg-emerald-50 px-3.5 py-1 rounded-full text-emerald-800 text-[10px] font-bold uppercase tracking-wider mb-2 shadow-xs border border-emerald-100">
          Sertifikat Sah &amp; Terverifikasi
        </div>
        
        <h2 class="text-xs text-on-surface-variant font-semibold">Nomor Sertifikat: <span class="font-mono text-primary font-bold"><?= htmlspecialchars($saving['cert_number']) ?></span></h2>
        
        <div class="w-full h-[1px] bg-gradient-to-r from-transparent via-outline-variant/50 to-transparent my-6"></div>

        <!-- Details Grid -->
        <div class="w-full space-y-4 text-left">
          <div class="bg-surface-container-low p-4 rounded-2xl border border-outline-variant/10 flex flex-col gap-1">
            <span class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider">Nama Mudhohhi (Shohibul Qurban)</span>
            <p class="font-serif text-lg font-extrabold text-on-surface italic"><?= htmlspecialchars($saving['user_name']) ?></p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="bg-surface-container-low p-3.5 rounded-2xl border border-outline-variant/10">
              <span class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider block">Jenis Qurban</span>
              <span class="font-bold text-xs text-on-surface mt-1 block">🐄 <?= htmlspecialchars($saving['package_name']) ?></span>
            </div>
            <div class="bg-surface-container-low p-3.5 rounded-2xl border border-outline-variant/10">
              <span class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider block">Tanggal Pelaksanaan</span>
              <span class="font-bold text-xs text-on-surface mt-1 block">10 Dzulhijjah 1447H</span>
            </div>
          </div>

          <div class="bg-surface-container-low p-4 rounded-2xl border border-outline-variant/10 flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-lg mt-0.5" style="font-variation-settings: 'FILL' 1;">location_on</span>
            <div>
              <span class="text-[9px] text-on-surface-variant uppercase font-bold tracking-wider block">Wilayah Penyaluran</span>
              <p class="font-bold text-xs text-on-surface mt-0.5"><?= htmlspecialchars($saving['penyaluran_address'] ?: 'Daerah Minoritas/Darurat') ?></p>
              <span class="text-[9px] text-on-surface-variant mt-0.5 block">Metode: <?= htmlspecialchars($saving['penyaluran_method'] ?: 'Titip Qurban') ?></span>
            </div>
          </div>

          <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 flex items-center justify-between">
            <div class="flex items-center gap-2 text-emerald-800">
              <span class="material-symbols-outlined text-emerald-600 text-lg" style="font-variation-settings: 'FILL' 1;">check_circle</span>
              <span class="text-xs font-bold">Status Qurban</span>
            </div>
            <span class="text-xs font-bold bg-emerald-100 text-emerald-900 px-3 py-1 rounded-full border border-emerald-200"><?= htmlspecialchars($saving['penyaluran_status'] ?: 'Selesai') ?></span>
          </div>
        </div>

        <p class="text-[9px] text-on-surface-variant/80 text-center leading-relaxed mt-6 italic">
          "Daging-daging unta dan darahnya itu sekali-kali tidak dapat mencapai (keridhaan) Allah, tetapi ketakwaan darimulah yang dapat mencapainya." <br>
          <span class="font-bold font-sans not-italic text-[8px] text-amber-800 uppercase tracking-widest mt-1 block">(QS. Al-Hajj: 37)</span>
        </p>

      <?php } else { ?>
        <!-- STATUS: INVALID / NOT FOUND -->
        <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mb-4 shadow-sm border border-red-100">
          <span class="material-symbols-outlined text-3xl font-bold">error</span>
        </div>
        
        <div class="inline-flex items-center gap-1.5 bg-red-50 px-3.5 py-1 rounded-full text-red-850 text-[10px] font-bold uppercase tracking-wider mb-2 border border-red-100">
          Sertifikat Tidak Ditemukan
        </div>
        
        <h2 class="text-xs text-on-surface-variant font-medium mt-2 text-center max-w-[280px]">Maaf, nomor sertifikat qurban <span class="font-mono text-red-600 font-bold"><?= htmlspecialchars($cert) ?></span> tidak terdaftar dalam database kami.</h2>
        
        <div class="w-full h-[1px] bg-gradient-to-r from-transparent via-outline-variant/50 to-transparent my-6"></div>

        <p class="text-xs text-on-surface-variant text-center max-w-[280px]">
          Silakan hubungi tim layanan pelanggan kami jika Anda meyakini ini adalah kesalahan teknis.
        </p>
      <?php } ?>

      <a href="index.html" class="w-full bg-primary text-on-primary font-bold py-3.5 rounded-2xl shadow-md hover:bg-primary/95 transition-all text-xs flex items-center justify-center gap-1.5 mt-8">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
        Kembali ke Beranda
      </a>

    </div>
  </div>

</body>
</html>
