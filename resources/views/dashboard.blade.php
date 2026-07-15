<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
  <title>Dashboard - Tabungan Qurban Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet"/>
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
            "surface-container-high": "#e6e8ea",
            "surface-container-highest": "#e0e3e5",
            "on-surface": "#191c1e",
            "on-surface-variant": "#3e4a3d",
            "outline-variant": "#bdcaba",
            "background": "#f7f9fb",
            "on-background": "#191c1e",
            "tertiary": "#765700",
            "tertiary-container": "#956e00",
            "on-tertiary-container": "#fffbff",
            "tertiary-fixed": "#ffdfa0",
            "tertiary-fixed-dim": "#f6be39",
            "error-container": "#ffdad6",
            "on-error-container": "#93000a"
          },
          borderRadius: {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          spacing: {
            "xl": "64px",
            "base": "4px",
            "sm": "16px",
            "gutter": "24px",
            "lg": "40px",
            "xs": "8px",
            "margin-mobile": "16px",
            "container-max": "1280px",
            "md": "24px"
          }
        }
      }
    }
  </script>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col pb-safe relative">

  <!-- TOP APP BAR -->
  <header class="fixed top-0 w-full z-40 flex justify-between items-center px-6 h-16 glass-panel !bg-white/40 border-b border-white/20 !rounded-none shadow-sm max-w-lg mx-auto left-0 right-0">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-container bg-surface-container flex items-center justify-center font-bold text-primary">
        <span id="user-avatar-initial">A</span>
      </div>
      <div>
        <h1 class="font-bold text-primary tracking-tight leading-none">Tabungan Qurban</h1>
        <span class="text-[10px] text-on-surface-variant font-medium">Halal Fintech Partner</span>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="toggleNotification()" class="relative p-1 text-on-surface hover:opacity-80 transition-opacity active:scale-95">
        <span class="material-symbols-outlined">notifications</span>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-amber-500 rounded-full"></span>
      </button>
      <button onclick="logout()" class="text-red-600 hover:text-red-700 active:scale-95 transition-transform flex items-center justify-center p-1" title="Log Out">
        <span class="material-symbols-outlined">logout</span>
      </button>
    </div>
  </header>

  <!-- NOTIFICATION DROPDOWN MODAL -->
  <div id="notification-modal" class="hidden fixed top-16 right-6 left-6 max-w-sm ml-auto glass-panel rounded-2xl shadow-xl p-4 z-50 transition-all duration-300">
    <div class="flex justify-between items-center border-b border-outline-variant/30 pb-2 mb-3">
      <h4 class="font-bold text-sm text-on-surface">Notifikasi Terbaru</h4>
      <button onclick="toggleNotification()" class="text-xs text-primary font-semibold">Tutup</button>
    </div>
    <div class="space-y-3 max-h-60 overflow-y-auto" id="notification-list">
      <!-- Generated dynamically -->
      <div class="text-xs text-on-surface-variant py-4 text-center">Tidak ada notifikasi baru.</div>
    </div>
  </div>

  <!-- MAIN CANVAS CONTROLLER -->
  <main class="flex-grow pt-20 pb-24 px-6 max-w-md mx-auto w-full relative">
    
    <!-- BANNER NOTIF FOR SIMULATIONS -->
    <div id="alert-banner" class="hidden mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-xs flex justify-between items-center shadow-sm">
      <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-amber-700 text-[18px]">info</span>
        <span id="alert-banner-text">Mulai qurban Anda dengan memilih paket terlebih dahulu.</span>
      </div>
      <button onclick="switchTab('packages')" class="text-xs font-bold text-primary hover:underline">Pilih Paket</button>
    </div>

    <!-- VIEW 1: HOME & PACKAGES SELECTION -->
    <div id="tab-packages" class="tab-view space-y-6">
      <section>
        <p class="text-xs font-semibold text-primary mb-1">Assalamu'alaikum, <span id="user-display-name">Ahmad</span></p>
        
        <!-- Home Accounts Selector -->
        <div id="home-accounts-selector" class="hidden flex gap-2 overflow-x-auto pb-1 no-scrollbar mt-3 mb-2">
          <!-- Rendered dynamically -->
        </div>

        <!-- Dashboard Tabungan Qurban Widget -->
        <div id="home-savings-summary" class="hidden mt-3 mb-4">
          <!-- Rendered dynamically -->
        </div>

        <h2 class="text-2xl font-bold text-on-surface leading-tight mt-4">Pilih Rencana Qurban</h2>
        <p class="text-sm text-on-surface-variant mt-1">Pilih paket hewan qurban terbaik Anda. Dana tabungan amanah dan disalurkan sesuai syariat.</p>
      </section>

      <!-- Category Filter Tabs -->
      <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
        <button onclick="filterPackages('all')" class="package-filter-btn bg-primary text-on-primary px-4 py-1.5 rounded-full text-xs font-medium shadow-sm transition-all">Semua</button>
        <button onclick="filterPackages('Domba')" class="package-filter-btn bg-surface-container text-on-surface-variant px-4 py-1.5 rounded-full text-xs font-medium transition-all">Domba</button>
        <button onclick="filterPackages('Sapi Patungan')" class="package-filter-btn bg-surface-container text-on-surface-variant px-4 py-1.5 rounded-full text-xs font-medium transition-all">Sapi Patungan</button>
        <button onclick="filterPackages('Sapi')" class="package-filter-btn bg-surface-container text-on-surface-variant px-4 py-1.5 rounded-full text-xs font-medium transition-all">Sapi Utuh</button>
      </div>

      <!-- Packages Container -->
      <div class="grid grid-cols-2 gap-3" id="packages-list">
        <!-- Generated dynamically via JS -->
      </div>
    </div>

    <!-- VIEW 2: LIVESTOCK DETAIL (SUB-VIEW) -->
    <div id="tab-livestock-detail" class="tab-view hidden space-y-6">
      <!-- Back to packages button -->
      <button onclick="switchTab('packages')" class="inline-flex items-center gap-1.5 text-xs text-primary font-bold hover:underline mb-2">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Paket
      </button>

      <!-- Livestock details container generated dynamically -->
      <div id="livestock-detail-container" class="space-y-6">
        <!-- Rendered by js -->
      </div>
    </div>

    <!-- VIEW 3: SAVINGS DASHBOARD -->
    <div id="tab-savings" class="tab-view hidden space-y-6">
      <!-- Account selector tab container -->
      <div id="savings-accounts-selector" class="hidden flex gap-2 overflow-x-auto pb-1 no-scrollbar border-b border-outline-variant/20 pb-3 mb-4">
        <!-- Rendered dynamically by js -->
      </div>
      <!-- No active saving screen -->
      <div id="savings-empty-state" class="hidden text-center py-12 px-6 glass-panel rounded-2xl shadow-sm flex flex-col items-center">
        <span class="material-symbols-outlined text-primary text-6xl mb-4" style="font-variation-settings: 'FILL' 1;">savings</span>
        <h3 class="font-bold text-lg text-on-surface">Belum Ada Tabungan Aktif</h3>
        <p class="text-sm text-on-surface-variant mt-2 max-w-[280px]">Silakan pilih paket hewan qurban Anda untuk mulai menabung secara bertahap.</p>
        <button onclick="switchTab('packages')" class="mt-6 bg-primary text-on-primary font-bold px-6 py-3 rounded-xl shadow-sm hover:bg-primary/95 transition-all">Pilih Paket Sekarang</button>
      </div>

      <!-- Active saving screen -->
      <div id="savings-active-state" class="space-y-6">
        <!-- Circular Progress Card -->
        <section class="glass-card rounded-2xl p-6 shadow-sm relative overflow-hidden flex flex-col items-center gap-4">
          <div class="absolute -top-10 -right-10 w-28 h-28 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>
          <div class="relative w-44 h-44 flex items-center justify-center">
            <!-- Circular Progress SVG -->
            <div class="absolute inset-0 rounded-full border-[10px] border-surface-container"></div>
            <svg class="w-full h-full transform -rotate-90">
              <circle class="text-primary transition-all duration-1000 ease-out" cx="88" cy="88" fill="transparent" id="savings-progress-circle" r="76" stroke="currentColor" stroke-dasharray="477" stroke-dashoffset="477" stroke-linecap="round" stroke-width="10"></circle>
            </svg>
            <div class="absolute flex flex-col items-center">
              <span class="text-3xl font-extrabold text-primary" id="savings-progress-text">0%</span>
              <span class="text-[10px] text-on-surface-variant font-bold uppercase tracking-widest">Terkumpul</span>
            </div>
          </div>

          <div class="w-full grid grid-cols-2 gap-4 mt-2 border-t border-outline-variant/30 pt-4">
            <div>
              <span class="text-[10px] text-on-surface-variant block uppercase font-bold">Dana Terkumpul</span>
              <span class="font-bold text-primary text-lg" id="savings-current-display">Rp 0</span>
            </div>
            <div class="text-right">
              <span class="text-[10px] text-on-surface-variant block uppercase font-bold">Target Paket</span>
              <span class="font-bold text-on-surface text-lg" id="savings-target-display">Rp 0</span>
            </div>
          </div>

          <div class="w-full bg-surface-container-low p-3.5 rounded-xl flex justify-between items-center border border-outline-variant/30">
            <div class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-amber-600 text-[20px]">hourglass_bottom</span>
              <span class="text-xs font-semibold text-on-surface-variant">Sisa Pembayaran</span>
            </div>
            <span class="font-bold text-amber-700 text-sm" id="savings-remaining-display">Rp 0</span>
          </div>

          <!-- Cancellation Button -->
          <div class="w-full flex justify-center mt-1 border-t border-outline-variant/30 pt-3">
            <button onclick="openCancelSavingsModal()" class="text-[11px] text-red-600 hover:text-red-700 font-bold hover:underline flex items-center gap-1 active:scale-95 transition-transform">
              <span class="material-symbols-outlined text-[15px]">cancel</span>
              Batalkan Rencana Tabungan & Refund Dana
            </button>
          </div>
        </section>

        <!-- Up coming Payment Reminder -->
        <section id="savings-reminder-section" class="flex flex-col gap-3">
          <h3 class="font-bold text-sm text-on-surface flex items-center gap-1.5">
            <span class="material-symbols-outlined text-primary text-[18px]">calendar_today</span>
            Setoran Berikutnya
          </h3>
          <div class="bg-amber-50 border-l-4 border-amber-600 p-4 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex flex-col">
              <span class="text-[10px] text-amber-800 font-bold uppercase" id="savings-deadline">Batas: 25 June 2026</span>
              <span class="text-xs font-semibold text-on-surface mt-0.5">Setoran Rutin Bulanan</span>
            </div>
            <button onclick="openPaymentModal()" class="bg-primary text-on-primary text-xs font-bold px-4 py-2 rounded-full active:scale-95 transition-transform hover:opacity-90 shadow-sm">Bayar Sekarang</button>
          </div>
        </section>

        <!-- Penyaluran Selection Card (Unlocked at 100%) -->
        <section id="savings-penyaluran-selection" class="hidden bg-emerald-50 border border-primary/20 p-5 rounded-2xl flex flex-col gap-3 shadow-sm">
          <div class="flex items-center gap-2 text-primary">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">verified_user</span>
            <div>
              <h3 class="font-bold text-sm">Tabungan Qurban Selesai!</h3>
              <p class="text-xs text-on-surface-variant">Dana terkumpul 100%. Silakan pilih metode penyaluran qurban Anda.</p>
            </div>
          </div>
          <button onclick="openPenyaluranView()" class="w-full bg-primary text-on-primary font-bold py-2.5 rounded-xl hover:bg-primary/95 transition-colors text-xs flex items-center justify-center gap-2">
            Pilih Metode Penyaluran Qurban
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </button>
        </section>

        <!-- Group Progress Card (Unlocked at 100% for Patungan) -->
        <section id="savings-group-progress" class="hidden bg-amber-50/60 border border-amber-200 p-5 rounded-2xl flex flex-col gap-3 shadow-sm">
          <div class="flex items-center gap-2 text-amber-800">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">groups</span>
            <div class="text-left">
              <h3 class="font-bold text-xs text-amber-900">Menunggu Kelompok Patungan Lunas</h3>
              <p class="text-[10px] text-on-surface-variant leading-tight mt-0.5">Dana Anda telah terkumpul 100%. Kelompok patungan ini membutuhkan 7 slot terisi lunas agar fisik sapi qurban siap diproses.</p>
            </div>
          </div>

          <!-- Group Progress Bar -->
          <div class="space-y-1 mt-1 text-left">
            <div class="flex justify-between text-[10px] font-bold text-on-surface-variant">
              <span>Progres Kelompok:</span>
              <span id="group-progress-ratio">5/7 Anggota Lunas</span>
            </div>
            <div class="w-full bg-surface-container rounded-full h-1.5 overflow-hidden">
              <div id="group-progress-bar-fill" class="bg-amber-600 h-full rounded-full transition-all duration-500" style="width: 71.4%"></div>
            </div>
          </div>

          <!-- Group Members List -->
          <div class="border-t border-dashed border-outline-variant/30 pt-3 space-y-1.5 text-[10px] text-left" id="group-members-list">
            <!-- Rendered dynamically -->
          </div>

          <!-- Simulation Button (Dev/Lokal) -->
          <button onclick="simulateGroupFilled()" class="w-full mt-1 border border-dashed border-primary text-primary font-bold py-2.5 rounded-xl transition-all text-[11px] hover:bg-emerald-50 active:scale-95 flex items-center justify-center gap-1">
            <span class="material-symbols-outlined text-[14px]">auto_awesome</span>
            Simulasikan Kelompok Terpenuhi (7/7)
          </button>
        </section>

        <!-- Group Location Voting Card (Unlocked when group status is 'ready') -->
        <section id="savings-group-voting" class="hidden bg-emerald-50 border border-emerald-200 p-5 rounded-2xl flex flex-col gap-3 shadow-sm">
          <div class="flex items-center gap-2 text-primary">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">how_to_vote</span>
            <div class="text-left">
              <h3 class="font-bold text-xs text-primary">Voting Lokasi Penyaluran Kelompok</h3>
              <p class="text-[10px] text-on-surface-variant leading-tight mt-0.5">Kelompok Anda telah mencapai status Ready (7/7 lunas). Silakan berikan suara Anda untuk memilih lokasi penyaluran bersama.</p>
            </div>
          </div>

          <!-- Voting form / options -->
          <div id="voting-options-container" class="space-y-2 mt-1">
            <!-- Populated dynamically via JS -->
          </div>

          <!-- Vote status / results list -->
          <div class="border-t border-dashed border-outline-variant/30 pt-3 space-y-1.5 text-[10px] text-left" id="voting-results-list">
            <!-- Rendered dynamically -->
          </div>
        </section>

        <!-- Achievements -->
        <section class="flex flex-col gap-3">
          <h3 class="font-bold text-sm text-on-surface">Pencapaian</h3>
          <div class="flex gap-4 overflow-x-auto pb-2 no-scrollbar">
            <div class="flex-none w-20 flex flex-col items-center gap-1 group">
              <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center border-2 border-white shadow-md transition-transform duration-300">
                <span class="material-symbols-outlined text-amber-700 text-2xl" style="font-variation-settings: 'FILL' 1;">verified</span>
              </div>
              <span class="text-[10px] font-semibold text-center text-on-surface">Niat Qurban</span>
            </div>
            <div id="badge-istiqomah" class="flex-none w-20 flex flex-col items-center gap-1 opacity-30 transition-opacity">
              <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center border-2 border-white shadow-md">
                <span class="material-symbols-outlined text-emerald-700 text-2xl" style="font-variation-settings: 'FILL' 1;">volunteer_activism</span>
              </div>
              <span class="text-[10px] font-semibold text-center text-on-surface">Istiqomah</span>
            </div>
            <div id="badge-reached" class="flex-none w-20 flex flex-col items-center gap-1 opacity-30 transition-opacity">
              <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center border-2 border-white shadow-md">
                <span class="material-symbols-outlined text-amber-600 text-2xl" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
              </div>
              <span class="text-[10px] font-semibold text-center text-on-surface">Goal Reached</span>
            </div>
          </div>
        </section>

        <!-- History -->
        <section class="flex flex-col gap-3">
          <div class="flex justify-between items-center">
            <h3 class="font-bold text-sm text-on-surface">Riwayat Pembayaran</h3>
            <span class="text-xs text-primary font-semibold">Tampilkan Semua</span>
          </div>
          <div class="space-y-2" id="savings-history-list">
            <!-- Dynamic elements from JSON -->
          </div>
        </section>
      </div>
    </div>

    <!-- VIEW 4: PENYALURAN SELECTION (SUB-VIEW) -->
    <div id="tab-penyaluran" class="tab-view hidden space-y-6">
      <button onclick="switchTab('savings')" class="inline-flex items-center gap-1.5 text-xs text-primary font-bold hover:underline mb-2">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Tabungan
      </button>

      <section>
        <h2 class="text-xl font-bold text-on-surface">Metode Penyaluran Qurban</h2>
        <p class="text-xs text-on-surface-variant mt-1">Tentukan bagaimana pelaksanaan qurban Anda ingin dikelola setelah dana terkumpul penuh.</p>
      </section>

      <form id="penyaluran-form" class="space-y-4">
        <div class="grid grid-cols-1 gap-3">
          <!-- Option 1: Titip Qurban -->
          <label class="border-2 border-primary bg-emerald-50/50 p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors" id="label-method-titip">
            <input type="radio" name="penyaluran_method" value="Titip Qurban" checked onclick="selectPenyaluranMethod('titip')" class="text-primary focus:ring-primary mt-1 border-outline-variant">
            <div class="flex-grow">
              <span class="font-bold text-sm text-primary block">Titipkan Pelaksanaan (Rekomendasi)</span>
              <span class="text-xs text-on-surface-variant mt-1 block leading-tight">Qurban disembelih dan didistribusikan ke daerah pelosok/darurat kemanusiaan. Transparansi penuh dengan video penyembelihan dan digital certificate.</span>
            </div>
          </label>

          <!-- Option 2: Dikirim -->
          <label class="border border-outline-variant p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors" id="label-method-kirim">
            <input type="radio" name="penyaluran_method" value="Dikirim" onclick="selectPenyaluranMethod('kirim')" class="text-primary focus:ring-primary mt-1 border-outline-variant">
            <div class="flex-grow">
              <span class="font-bold text-sm text-on-surface block">Kirim Hewan ke Alamat</span>
              <span class="text-xs text-on-surface-variant mt-1 block leading-tight">Hewan qurban hidup akan dikirim secara langsung ke lokasi/alamat rumah Anda sebelum hari raya Idul Adha. (Biaya ongkir berlaku).</span>
            </div>
          </label>

          <!-- Option 3: Ambil Langsung -->
          <label class="border border-outline-variant p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors" id="label-method-ambil">
            <input type="radio" name="penyaluran_method" value="Ambil Langsung" onclick="selectPenyaluranMethod('ambil')" class="text-primary focus:ring-primary mt-1 border-outline-variant">
            <div class="flex-grow">
              <span class="font-bold text-sm text-on-surface block">Ambil Langsung di Peternakan</span>
              <span class="text-xs text-on-surface-variant mt-1 block leading-tight">Ambil hewan qurban secara langsung ke Malang Highland Farm/Sukabumi Ranch mitra kami.</span>
            </div>
          </label>
        </div>

        <!-- Dynamic Form: Titip Qurban -->
        <div id="form-penyaluran-titip" class="space-y-3 bg-white border border-outline-variant/30 p-4 rounded-xl">
          <h4 class="font-bold text-xs text-on-surface border-b pb-1">Lokasi Distribusi Qurban</h4>
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Target Wilayah Penyaluran</label>
            <select id="titip-wilayah" class="w-full bg-surface-container-low border border-outline-variant rounded-xl text-xs py-2 px-3 outline-none">
              <option value="Palestina (Melalui Penyalur Khusus)">Palestina (Melalui Lembaga Penyalur)</option>
              <option value="NTT (Waikabubak)">NTT - Waikabubak (Daerah Pelosok)</option>
              <option value="Papua Barat (Sorong)">Papua Barat - Sorong (Daerah Minoritas)</option>
              <option value="Desa Binaan (Jawa Barat)">Jawa Barat - Desa Binaan Istiqomah</option>
            </select>
          </div>
        </div>

        <!-- Dynamic Form: Dikirim -->
        <div id="form-penyaluran-kirim" class="hidden space-y-3 bg-white border border-outline-variant/30 p-4 rounded-xl">
          <h4 class="font-bold text-xs text-on-surface border-b pb-1">Detail Pengiriman</h4>
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Nama Penerima</label>
            <input type="text" id="kirim-nama" placeholder="Nama Lengkap" class="w-full bg-surface-container-low border border-outline-variant rounded-xl text-xs py-2 px-3 outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Nomor WhatsApp Penerima</label>
            <input type="tel" id="kirim-hp" placeholder="0812XXXXXXXX" class="w-full bg-surface-container-low border border-outline-variant rounded-xl text-xs py-2 px-3 outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Alamat Lengkap Pengiriman</label>
            <textarea id="kirim-alamat" rows="2" placeholder="Jalan, RT/RW, Kecamatan, Kota" class="w-full bg-surface-container-low border border-outline-variant rounded-xl text-xs py-2 px-3 outline-none"></textarea>
          </div>
        </div>

        <!-- Dynamic Form: Ambil Langsung -->
        <div id="form-penyaluran-ambil" class="hidden space-y-3 bg-white border border-outline-variant/30 p-4 rounded-xl">
          <h4 class="font-bold text-xs text-on-surface border-b pb-1">Lokasi Pengambilan</h4>
          <p class="text-xs text-on-surface-variant leading-relaxed">
            Silakan ambil hewan qurban Anda di peternakan utama kami: <br>
            <strong>Malang Highland Farm - Blok A-12</strong><br>
            Jl. Raya Highland No. 45, Malang, Jawa Timur.<br>
            Bawa invoice lunas dan KTP terdaftar untuk verifikasi pengambilan.
          </p>
        </div>

        <button type="submit" class="w-full bg-primary text-on-primary font-bold py-3.5 rounded-xl hover:bg-primary/95 transition-all text-sm flex items-center justify-center gap-2 shadow-md">
          Simpan Metode Penyaluran
          <span class="material-symbols-outlined text-[18px]">save</span>
        </button>
      </form>
    </div>

    <!-- VIEW 5: TRACKING STATUS TIMELINE -->
    <div id="tab-tracking" class="tab-view hidden space-y-6">
      <!-- Account selector tab container -->
      <div id="tracking-accounts-selector" class="hidden flex gap-2 overflow-x-auto pb-1 no-scrollbar border-b border-outline-variant/20 pb-3 mb-4">
        <!-- Rendered dynamically by js -->
      </div>
      <!-- Empty state if qurban not fully saved/ready -->
      <div id="tracking-empty-state" class="hidden text-center py-12 px-6 glass-panel rounded-2xl shadow-sm flex flex-col items-center">
        <span class="material-symbols-outlined text-primary text-6xl mb-4">local_shipping</span>
        <h3 class="font-bold text-lg text-on-surface">Belum Ada Hewan untuk Dilacak</h3>
        <p class="text-sm text-on-surface-variant mt-2 max-w-[280px]">Proses pelacakan fisik hewan qurban akan aktif setelah tabungan terkumpul 100% dan Anda mengonfirmasi metode penyaluran.</p>
        <button onclick="switchTab('savings')" class="mt-6 bg-primary text-on-primary font-bold px-6 py-3 rounded-xl shadow-sm hover:bg-primary/95 transition-all">Lihat Tabungan Saya</button>
      </div>

      <!-- Timeline state active -->
      <div id="tracking-active-state" class="space-y-6">
        <!-- Live Animal Card summary -->
        <section class="glass-card rounded-2xl p-6 shadow-sm flex gap-4 items-center">
          <img id="tracking-livestock-img" class="w-20 h-20 object-cover rounded-xl border border-outline-variant/30" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAd9hrZQ8UYHQSoAPPTkrc8MtUIs8Nq7a6LgG1irqWWYkL4m0I9A84Y-7xXjUZwV72A_UA5eJnJ_igw-sY05vIurhTxUBU6sspBeWl1J-jz0kLMcFs55u7Y27pGbw_AEx02Yl9Q7A3NxAX5YnG686AyH6fcrYzoDHIdQ2J5zyhq9VC1It1XVRJ4Z2EMSkwuYK0_M-klSwzDy8xV-X0irrVzwwYgAohQMcf_letaVOOWLi_2tUO1s4C-UAqJUDDo749C4ujwhYs93mdS" alt="Hewan Qurban">
          <div class="flex-grow">
            <h4 class="font-bold text-sm text-on-surface" id="tracking-livestock-name">Sapi Limousin A</h4>
            <p class="text-[10px] text-primary font-bold tracking-wider" id="tracking-livestock-id">ID: #SL-202401</p>
            <div class="flex gap-4 mt-2">
              <span class="text-xs text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">weight</span> <span id="tracking-livestock-weight">450 kg</span></span>
              <span class="text-xs text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">health_and_safety</span> Sehat</span>
            </div>
          </div>
        </section>

        <!-- Vertical Timeline progression -->
        <section class="relative pl-4">
          <div class="relative timeline-track timeline-track-active" id="timeline-track-element">
            <!-- Steps are rendered dynamically via js -->
          </div>
        </section>
      </div>
    </div>

    <!-- VIEW 6: IMPACT & STORIES -->
    <div id="tab-impact" class="tab-view hidden space-y-6">
      <section>
        <h2 class="text-2xl font-bold text-on-surface leading-tight">Dampak Kolektif Qurban</h2>
        <p class="text-sm text-on-surface-variant mt-1">Transparansi penuh penyaluran dan dampak nyata dari qurban Anda.</p>
      </section>

      <!-- Metrics Bento Grid -->
      <section class="grid grid-cols-2 gap-3">
        <div class="col-span-2 glass-card rounded-2xl p-5 flex flex-col items-center text-center">
          <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-on-secondary-container">groups</span>
          </div>
          <span class="text-3xl font-extrabold text-primary">12,450+</span>
          <span class="text-xs font-semibold text-on-surface-variant mt-1">Penerima Manfaat Penyaluran</span>
        </div>
        <div class="glass-card rounded-2xl p-4">
          <span class="material-symbols-outlined text-amber-700 mb-1">inventory_2</span>
          <div class="text-xl font-bold text-on-surface">3,820</div>
          <span class="text-xs text-on-surface-variant">Paket Daging Qurban</span>
        </div>
        <div class="glass-card rounded-2xl p-4">
          <span class="material-symbols-outlined text-teal-700 mb-1">public</span>
          <div class="text-xl font-bold text-on-surface">32</div>
          <span class="text-xs text-on-surface-variant">Kecamatan Pelosok</span>
        </div>
      </section>

      <!-- Digital Certificate banners list container -->
      <div id="impact-certificates-container" class="space-y-3"></div>

      <!-- Distribution Map -->
      <section class="space-y-2">
        <h3 class="font-bold text-sm text-on-surface">Jaringan Penyaluran</h3>
        <div class="relative w-full h-44 rounded-xl overflow-hidden glass-card border border-outline-variant/30">
          <img class="w-full h-full object-cover opacity-70 grayscale" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDUvyiGpPDTYbcnMGOdJTRAK406hpqruEoTp9spJM4iGTF_fmOXeA5qn10SuQMtC9gvIqZWe2xjTbrCsagXndUaiHgquvdsIsHZOq7NpB77Sy2yayVcULWrizAgfP7J8ECexsI5OlkrFG9LnAE6uCwbawC-dPFEs76dfHxxvdANsXB0EgKaTwp12W9pVnIbRg9r_b5orJpYjlAO1R71Xr98-OKqh3LZ3rl24RwJX4M-zHw5_wkmh8P4Tio_i2K4KnFF6JBs5E9IG-Kl" alt="Peta Indonesia">
          <div class="absolute inset-0 bg-gradient-to-t from-white/90 to-transparent"></div>
          <!-- Glowing Nodes -->
          <div class="absolute top-1/2 left-1/4 w-3.5 h-3.5 bg-primary rounded-full shadow-[0_0_10px_rgba(0,107,44,0.6)] animate-pulse"></div>
          <div class="absolute top-1/3 left-1/2 w-3.5 h-3.5 bg-primary rounded-full shadow-[0_0_10px_rgba(0,107,44,0.6)] animate-pulse"></div>
          <div class="absolute bottom-1/3 right-1/4 w-3.5 h-3.5 bg-primary rounded-full shadow-[0_0_10px_rgba(0,107,44,0.6)] animate-pulse"></div>
        </div>
      </section>

      <!-- Documentation Reports -->
      <section class="space-y-3">
        <h3 class="font-bold text-sm text-on-surface">Laporan Dokumentasi</h3>
        <div class="flex gap-4 overflow-x-auto pb-2 no-scrollbar">
          <div class="flex-shrink-0 w-56 glass-card rounded-xl overflow-hidden shadow-sm relative">
            <img class="w-full h-28 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDWm69B_Qdodp5ytO_P6HSP38052Qn1cSxSh0iNTNEkfn1c_bjVlfCGM1JP0036W9YPq9zvyzw4F1lZTf78L5KwKhGt12vn_uZwz5HrNCwlFi3EGQiapFCzGuhuUYr3NWclw5yQGAWUSnJIeiPJ9F7CL7BKcUhadD3DKgrnSRAC27nf3qf_K8Kbi6iFWRjBht0jid43oxeTLNiHaoSZwcFSH6O_b1MsvZtXzsd6sHcRSY15Q8jB7PTunf25WwY4keLZpeF6mg4CnKoa" alt="Report">
            <div class="absolute top-2 left-2 bg-black/40 text-white p-1 rounded-full backdrop-blur-sm"><span class="material-symbols-outlined text-[16px] block">play_circle</span></div>
            <div class="p-2.5">
              <p class="font-bold text-xs text-on-surface truncate">Persiapan Pelaksanaan 1447H</p>
              <span class="text-[10px] text-on-surface-variant">Jawa Timur • Video</span>
            </div>
          </div>
          <div class="flex-shrink-0 w-56 glass-card rounded-xl overflow-hidden shadow-sm">
            <img class="w-full h-28 object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBm4rPBhe6y2Y1VwsdLZ_DUO6VV835VPIKna1-T6sRl020TEGCbi7qyKntcnvrFYWqjNcoLBetUOpXtWeacXREIwgpPHHyH7JAT-vdocLuoYPSmZxM8X5wyeMSRVf7PXFGYE27lZFHrbnrbj_GUsiTq8q2Ie9w8ySOZ2vgveAG0EyhKdNPhXxtg7vPb8tnnbRGOfC7Jdo0k8E5L9z8NJb0nJSjWMoeRcgpGoEAR897SxqHZmD4vjkA0t7IiloJhL2UMu5DS3T5u-CWr" alt="Report">
            <div class="p-2.5">
              <p class="font-bold text-xs text-on-surface truncate">Distribusi Wilayah Pelosok</p>
              <span class="text-[10px] text-on-surface-variant">NTT - Waikabubak • 8 Foto</span>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- VIEW 7: DIGITAL CERTIFICATE DETAIL (SUB-VIEW) -->
    <div id="tab-certificate" class="tab-view hidden space-y-6">
      <button onclick="switchTab('impact')" class="inline-flex items-center gap-1.5 text-xs text-primary font-bold hover:underline mb-2">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali ke Dampak
      </button>

      <!-- PRINT-ONLY AREA FOR CLEAN CERTIFICATE PDF DOWNLOAD -->
      <div id="certificate-print-area" class="relative bg-white shadow-xl rounded-2xl p-6 border-2 border-primary/20 overflow-hidden text-center flex flex-col items-center">
        <!-- Inner islamic frame border -->
        <div class="absolute inset-2 border border-amber-600/30 rounded-xl pointer-events-none"></div>
        <div class="absolute inset-4 border-[2px] border-primary/20 rounded-lg pointer-events-none"></div>

        <div class="relative z-10 py-6 px-4 flex flex-col items-center">
          <div class="mb-4 opacity-30">
            <span class="material-symbols-outlined text-5xl text-primary">auto_awesome</span>
          </div>
          <span class="text-[10px] text-amber-800 font-bold uppercase tracking-[0.2em] mb-4">Sertifikat Resmi Partisipasi Qurban</span>
          <h2 class="font-serif-display text-3xl font-bold text-primary mb-6 italic" id="cert-user-name">Ahmad Fauzan</h2>
          <div class="w-16 h-[2px] bg-amber-500 mb-6"></div>
          
          <p class="text-xs text-on-surface-variant leading-relaxed max-w-[280px] mb-8">
            Telah terdaftar dan melaksanakan ibadah Qurban melalui platform Tabungan Qurban. Semoga ibadah qurban ini diterima di sisi Allah SWT dan membawa keberkahan bagi semua. Amin.
          </p>

          <!-- Dynamic meta stats -->
          <div class="grid grid-cols-2 gap-y-4 gap-x-3 text-left w-full border-t border-b border-surface-container py-5 mb-8">
            <div>
              <span class="text-[9px] text-on-surface-variant/70 uppercase font-semibold">Jenis Qurban</span>
              <p class="text-xs font-bold text-on-surface" id="cert-package">Sapi Limousin A</p>
            </div>
            <div>
              <span class="text-[9px] text-on-surface-variant/70 uppercase font-semibold">Lokasi Penyaluran</span>
              <p class="text-xs font-bold text-on-surface" id="cert-location">Palestina (Melalui Penyalur)</p>
            </div>
            <div>
              <span class="text-[9px] text-on-surface-variant/70 uppercase font-semibold">Tanggal Pelaksanaan</span>
              <p class="text-xs font-bold text-on-surface">10 Dzulhijjah 1447H</p>
            </div>
            <div>
              <span class="text-[9px] text-on-surface-variant/70 uppercase font-semibold">Nomor Sertifikat</span>
              <p class="text-xs font-bold text-on-surface" id="cert-number">#TQ-2026-8802</p>
            </div>
          </div>

          <!-- Bottom elements: QR and signature -->
          <div class="flex justify-between items-center w-full px-2">
            <div class="text-left flex flex-col items-center">
              <img id="cert-qr-img" alt="QR Code" class="w-16 h-16 border border-outline-variant p-1 rounded-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBjqyQ1I7-bpa9BSR-oh6XrHheb1l4jKqj7ixkek2iY1kaDdKKik5NSuoiV0N-0wTqN9hjzBrhib8g4akOSMSg6HzTqQQsMpa1adCCjBKJSGKyZ-tNotEvIigoPspZMbp3QmeX2L_qSveUmZ4eoHAFoIFFixGAuzj7bSRh0g96J7DZuXydipGuG756e_hN3rcSFPuN93s3Pg7ZRUL1Qh3Di15fyE3CB1CR5ujM2Z9KwhexmhK3hgWE2tdXZZOTx3BIeeCBgvZyPgCIc">
              <span class="text-[8px] text-on-surface-variant mt-1">Pindai Verifikasi</span>
            </div>
            <div class="text-right flex flex-col items-end">
              <span class="material-symbols-outlined text-primary text-3xl opacity-20 mb-1">verified</span>
              <span class="font-bold text-xs text-primary font-serif-display italic">Management Tabungan Qurban</span>
              <span class="text-[8px] text-on-surface-variant">Ditandatangani Digital</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Action actions -->
      <div class="flex flex-col gap-3 pt-4">
        <button onclick="downloadCertificatePDF()" class="w-full flex items-center justify-center gap-2 bg-primary text-on-primary font-bold py-3.5 rounded-xl shadow-sm active:scale-95 transition-transform hover:opacity-90">
          <span class="material-symbols-outlined">download</span> Unduh PDF Sertifikat
        </button>
        <button onclick="alert('Demo: Link sertifikat disalin ke papan klip!')" class="w-full flex items-center justify-center gap-2 bg-surface-container-high text-on-surface font-semibold py-3 rounded-xl hover:bg-surface-container-highest transition-colors text-xs">
          <span class="material-symbols-outlined text-[16px]">share</span> Bagikan Sertifikat
        </button>
      </div>
    </div>

    <!-- VIEW 8: USER PROFILE (SCREEN 20) -->
    <div id="tab-profile" class="tab-view hidden space-y-6">
      <section class="text-center py-6 glass-panel rounded-2xl shadow-sm flex flex-col items-center relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary/5 rounded-full blur-xl pointer-events-none"></div>
        <!-- Profile Avatar Initials -->
        <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center font-bold text-3xl shadow-md border-2 border-white mb-4">
          <span id="profile-initials">A</span>
        </div>
        <h2 class="font-bold text-lg text-on-surface" id="profile-name">Ahmad Fauzan</h2>
        <p class="text-xs text-on-surface-variant" id="profile-email">ahmad@thankquu.com</p>
        <span class="text-[10px] text-primary font-bold mt-2 bg-emerald-50 px-2.5 py-0.5 rounded-full" id="profile-phone">081234567890</span>
      </section>

      <!-- Profile Menu Actions -->
      <section class="glass-panel rounded-2xl shadow-sm overflow-hidden divide-y divide-white/20">
        <div onclick="alert('Demo: Fitur Edit Data Diri')" class="p-4 flex items-center justify-between cursor-pointer hover:bg-surface-container-low transition-colors">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">person</span>
            <span class="text-xs font-semibold text-on-surface">Data Diri</span>
          </div>
          <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_right</span>
        </div>
        <div onclick="switchTab('impact')" class="p-4 flex items-center justify-between cursor-pointer hover:bg-surface-container-low transition-colors">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">workspace_premium</span>
            <span class="text-xs font-semibold text-on-surface">Sertifikat Digital</span>
          </div>
          <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_right</span>
        </div>
        <div onclick="alert('Demo: Bantuan & FAQ')" class="p-4 flex items-center justify-between cursor-pointer hover:bg-surface-container-low transition-colors">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">help</span>
            <span class="text-xs font-semibold text-on-surface">Bantuan &amp; FAQ</span>
          </div>
          <span class="material-symbols-outlined text-on-surface-variant text-[18px]">chevron_right</span>
        </div>
        <div onclick="logout()" class="p-4 flex items-center justify-between cursor-pointer hover:bg-red-50 transition-colors text-red-600">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-red-600">logout</span>
            <span class="text-xs font-bold">Logout / Keluar</span>
          </div>
          <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </div>
      </section>
    </div>

  </main>

  <!-- BOTTOM NAVIGATION BAR -->
  <nav class="fixed bottom-0 w-full z-40 flex justify-around items-center px-2 py-2 glass-panel border-t border-white/25 !rounded-b-none !rounded-t-3xl shadow-[0_-8px_32px_rgba(0,107,44,0.04)] max-w-lg mx-auto left-0 right-0">
    <button onclick="switchTab('packages')" id="nav-packages" class="nav-tab-btn flex-1 flex flex-col items-center justify-center text-primary font-bold active:scale-95 transition-all text-xs py-1">
      <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
      <span class="text-[10px] mt-0.5">Paket</span>
    </button>
    <button onclick="switchTab('savings')" id="nav-savings" class="nav-tab-btn flex-1 flex flex-col items-center justify-center text-on-surface-variant hover:text-primary active:scale-95 transition-all text-xs py-1">
      <span class="material-symbols-outlined">account_balance_wallet</span>
      <span class="text-[10px] mt-0.5">Tabungan</span>
    </button>
    <button onclick="switchTab('tracking')" id="nav-tracking" class="nav-tab-btn flex-1 flex flex-col items-center justify-center text-on-surface-variant hover:text-primary active:scale-95 transition-all text-xs py-1">
      <span class="material-symbols-outlined">local_shipping</span>
      <span class="text-[10px] mt-0.5">Lacak</span>
    </button>
    <button onclick="switchTab('impact')" id="nav-impact" class="nav-tab-btn flex-1 flex flex-col items-center justify-center text-on-surface-variant hover:text-primary active:scale-95 transition-all text-xs py-1">
      <span class="material-symbols-outlined">auto_awesome</span>
      <span class="text-[10px] mt-0.5">Dampak</span>
    </button>
    <button onclick="switchTab('profile')" id="nav-profile" class="nav-tab-btn flex-1 flex flex-col items-center justify-center text-on-surface-variant hover:text-primary active:scale-95 transition-all text-xs py-1">
      <span class="material-symbols-outlined">account_circle</span>
      <span class="text-[10px] mt-0.5">Akun</span>
    </button>
  </nav>

  <!-- LOGOUT CONFIRMATION MODAL -->
  <div id="logout-confirm-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-6 backdrop-blur-xs">
    <div class="w-full max-w-sm bg-white rounded-2xl p-6 flex flex-col gap-4 shadow-xl border border-outline-variant/30 text-center">
      <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-2">
        <span class="material-symbols-outlined text-3xl">logout</span>
      </div>
      <h3 class="font-bold text-lg text-on-surface">Konfirmasi Keluar</h3>
      <p class="text-xs text-on-surface-variant leading-relaxed">Apakah Anda yakin ingin keluar dari akun Anda?</p>
      <div class="grid grid-cols-2 gap-3 mt-2">
        <button onclick="closeLogoutModal()" class="bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold py-3 rounded-xl transition-all text-xs">Batal</button>
        <button onclick="confirmLogoutSubmit()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-all text-xs shadow-sm shadow-red-200">Ya, Keluar</button>
      </div>
    </div>
  </div>

  <!-- CUSTOM BEAUTIFUL ALERT MODAL -->
  <div id="custom-alert-modal" class="hidden fixed inset-0 z-[100] bg-black/60 flex items-center justify-center p-6 backdrop-blur-xs">
    <div class="w-full max-w-sm bg-white rounded-2xl p-6 flex flex-col gap-4 shadow-xl border border-outline-variant/30 text-center">
      <div id="custom-alert-icon-container" class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
        <span class="material-symbols-outlined text-3xl">check_circle</span>
      </div>
      <h3 id="custom-alert-title" class="font-bold text-lg text-on-surface">Pemberitahuan</h3>
      <p id="custom-alert-message" class="text-xs text-on-surface-variant leading-relaxed px-2"></p>
      <div class="mt-2">
        <button id="custom-alert-btn" onclick="closeAlertModal()" class="w-full bg-primary text-on-primary font-bold py-3 rounded-xl transition-all text-xs hover:bg-primary/90 active:scale-95 shadow-sm animate-pulse-once">OK</button>
      </div>
    </div>
  </div>

  <!-- PENDING TRANSACTION DETAILS MODAL -->
  <div id="pending-tx-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-6 backdrop-blur-xs">
    <div class="w-full max-w-sm bg-white rounded-2xl p-6 flex flex-col gap-4 shadow-xl border border-outline-variant/30 text-center relative overflow-hidden">
      <!-- Decorative background -->
      <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary/5 rounded-full blur-xl pointer-events-none"></div>
      
      <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-2">
        <span class="material-symbols-outlined text-3xl">pending_actions</span>
      </div>
      <h3 class="font-bold text-lg text-on-surface">Transaksi Tertunda</h3>
      
      <div class="bg-surface-container-lowest rounded-xl p-4 text-left border border-outline-variant/20 space-y-2">
        <div class="flex justify-between text-xs text-on-surface-variant">
          <span>Order ID:</span>
          <span class="font-bold text-on-surface font-mono text-[11px]" id="pending-modal-order-id">TQ-TX-XXXX</span>
        </div>
        <div class="flex justify-between text-xs text-on-surface-variant">
          <span>Nominal:</span>
          <span class="font-bold text-primary text-[11px]" id="pending-modal-amount">Rp 0</span>
        </div>
        <div class="flex justify-between text-xs text-on-surface-variant">
          <span>Metode:</span>
          <span class="font-semibold text-on-surface text-[11px]" id="pending-modal-method">Virtual Account</span>
        </div>
        <div class="flex justify-between text-xs text-on-surface-variant">
          <span>Waktu:</span>
          <span class="text-on-surface text-right text-[11px]" id="pending-modal-datetime">27 Juni 2026 • 01:41 WIB</span>
        </div>
      </div>

      <div class="flex flex-col gap-2 mt-2">
        <button id="pending-modal-check-btn" class="w-full bg-secondary-container text-primary font-bold py-3 rounded-xl transition-all text-xs hover:bg-secondary-container/80 active:scale-95 shadow-sm flex items-center justify-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">sync</span>
          Cek Status Pembayaran
        </button>
        <button id="pending-modal-pay-btn" class="w-full bg-primary text-on-primary font-bold py-3 rounded-xl transition-all text-xs hover:bg-primary/95 active:scale-95 shadow-md flex items-center justify-center gap-1.5">
          <span class="material-symbols-outlined text-[16px]">credit_card</span>
          Bayar Sekarang (Midtrans)
        </button>
        <button id="pending-modal-bypass-btn" class="w-full border border-dashed border-red-300 text-red-600 font-bold py-2.5 rounded-xl transition-all text-[11px] hover:bg-red-50 active:scale-95 flex items-center justify-center gap-1">
          <span class="material-symbols-outlined text-[14px]">terminal</span>
          Bypass Sukses (Dev/Lokal)
        </button>
        <button onclick="closePendingTxModal()" class="w-full bg-surface-container hover:bg-surface-container-high text-on-surface font-semibold py-3 rounded-xl transition-all text-xs active:scale-95 mt-1">
          Tutup
        </button>
      </div>
    </div>
  </div>

  <!-- PAYMENT GATEWAY MODAL (SIMULATOR) -->
  <div id="payment-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-end justify-center p-0 md:p-6 backdrop-blur-xs">
    <div class="w-full max-w-md bg-white rounded-t-2xl md:rounded-2xl p-6 flex flex-col gap-4 max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center border-b pb-2">
        <h3 class="font-bold text-lg text-on-surface flex items-center gap-1.5"><span class="material-symbols-outlined text-primary">payments</span>Pembayaran Setoran</h3>
        <button onclick="closePaymentModal()" class="material-symbols-outlined text-on-surface-variant text-[24px]">close</button>
      </div>

      <!-- Step 1: Input Amount & Method -->
      <div id="payment-step-input" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-on-surface-variant mb-1">Pilih Nominal Setoran</label>
          <div class="grid grid-cols-3 gap-2">
            <button onclick="setPaymentAmount(100000)" class="bg-surface-container hover:bg-primary/10 py-2.5 rounded-lg text-xs font-bold">100 Rb</button>
            <button onclick="setPaymentAmount(250000)" class="bg-surface-container hover:bg-primary/10 py-2.5 rounded-lg text-xs font-bold">250 Rb</button>
            <button onclick="setPaymentAmount(500000)" class="bg-surface-container hover:bg-primary/10 py-2.5 rounded-lg text-xs font-bold">500 Rb</button>
          </div>
          <input type="number" id="payment-amount-input" placeholder="Masukkan Nominal Lainnya" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-sm font-semibold text-primary mt-2 outline-none">
        </div>

        <div>
          <label class="block text-xs font-semibold text-on-surface-variant mb-2">Pilih Metode Pembayaran</label>
          <div class="space-y-2">
            <label class="border-2 border-primary bg-emerald-50/10 p-3 rounded-xl flex items-center justify-between cursor-pointer select-none">
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                <span class="text-xs font-bold text-primary">Simulasi Instan (Lokal)</span>
              </div>
              <input type="radio" name="payment_method" value="Simulasi Instan" checked class="text-primary focus:ring-primary">
            </label>
            <label class="border border-outline-variant hover:bg-emerald-50/20 p-3 rounded-xl flex items-center justify-between cursor-pointer select-none">
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary">account_balance</span>
                <span class="text-xs font-semibold text-on-surface">Virtual Account BNI/Mandiri (Midtrans)</span>
              </div>
              <input type="radio" name="payment_method" value="Virtual Account" class="text-primary focus:ring-primary">
            </label>
            <label class="border border-outline-variant hover:bg-emerald-50/20 p-3 rounded-xl flex items-center justify-between cursor-pointer select-none">
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary">qr_code_2</span>
                <span class="text-xs font-semibold text-on-surface">QRIS (Midtrans)</span>
              </div>
              <input type="radio" name="payment_method" value="E-Wallet" class="text-primary focus:ring-primary">
            </label>
          </div>
        </div>

        <button onclick="processPaymentSubmit()" class="w-full bg-primary text-on-primary font-bold py-3.5 rounded-xl hover:bg-primary/95 transition-all text-sm flex items-center justify-center gap-2 shadow-md">
          Lanjutkan Pembayaran
          <span class="material-symbols-outlined text-[18px]">credit_card</span>
        </button>
      </div>
    </div>
  </div>

  <!-- SAVINGS SIMULATION / BUAT TABUNGAN MODAL -->
  <div id="simulation-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-6 backdrop-blur-xs">
    <div class="w-full max-w-sm bg-white rounded-2xl p-6 flex flex-col gap-4 shadow-xl">
      <div class="flex justify-between items-center border-b pb-2">
        <h3 class="font-bold text-md text-on-surface flex items-center gap-1.5"><span class="material-symbols-outlined text-primary">savings</span>Mulai Tabungan</h3>
        <button onclick="closeSimulationModal()" class="material-symbols-outlined text-on-surface-variant text-[24px]">close</button>
      </div>

      <div class="space-y-4">
        <div class="bg-emerald-50 p-3 rounded-xl">
          <span class="text-[10px] text-primary font-bold uppercase" id="sim-package-category">Kategori</span>
          <h4 class="font-bold text-sm text-on-surface" id="sim-package-name">Nama Paket</h4>
          <p class="text-xs text-primary font-bold mt-1" id="sim-package-price">Rp 0</p>
        </div>

        <!-- Aqiqah Wizard Fields -->
        <div id="aqiqah-wizard-fields" class="hidden space-y-3 border-t border-outline-variant/30 pt-3">
          <h5 class="text-xs font-bold text-on-surface uppercase tracking-wide">Data Anak Aqiqah</h5>
          <div>
            <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Nama Anak</label>
            <input type="text" id="sim-aqiqah-child-name" placeholder="Nama lengkap anak" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-2.5 text-xs font-semibold outline-none">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Jenis Kelamin</label>
              <select id="sim-aqiqah-child-gender" onchange="updateAqiqahTargetPrice()" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-3 py-2.5 text-xs font-semibold outline-none">
                <option value="putri">Putri (1 Ekor)</option>
                <option value="putra">Putra (2 Ekor - Bundling)</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Tanggal Lahir</label>
              <input type="date" id="sim-aqiqah-child-birthdate" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-3 py-2 text-xs font-semibold outline-none">
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-on-surface-variant mb-1">Setoran Awal (Nominal Awal)</label>
          <input type="number" id="sim-initial-amount" placeholder="Contoh: Rp 500.000" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-xs font-semibold text-primary outline-none">
        </div>

        <div class="bg-surface-container-low p-3.5 rounded-xl border border-outline-variant/30">
          <p class="text-[10px] text-on-surface-variant font-semibold mb-1">Simulasi Setoran Bulanan (Estimasi 10 Bulan)</p>
          <p class="text-sm font-bold text-on-surface" id="sim-monthly-estimate">Rp 0 / bln</p>
        </div>

        <button onclick="createTabungan()" class="w-full bg-primary text-on-primary font-bold py-3.5 rounded-xl hover:bg-primary/95 transition-all text-xs flex items-center justify-center gap-2 shadow-md">
          Buat Rencana Tabungan
          <span class="material-symbols-outlined text-[16px]">add_circle</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Cancellation Modal -->
  <div id="cancel-savings-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-6 backdrop-blur-xs">
    <div class="w-full max-w-sm bg-white rounded-2xl p-6 flex flex-col gap-4 shadow-xl text-left">
      <div class="flex justify-between items-center border-b pb-2">
        <h3 class="font-bold text-md text-red-600 flex items-center gap-1.5"><span class="material-symbols-outlined text-red-600">cancel</span>Batalkan Tabungan</h3>
        <button onclick="closeCancelSavingsModal()" class="material-symbols-outlined text-on-surface-variant text-[24px]">close</button>
      </div>

      <div class="space-y-4">
        <p class="text-xs text-on-surface-variant">Anda mengajukan pembatalan rencana tabungan. Berikut adalah rincian simulasi pengembalian dana (refund):</p>
        
        <div class="bg-red-50 p-4 rounded-xl space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Total Terkumpul:</span>
            <span class="font-bold text-on-surface" id="cancel-collected-amount">Rp 0</span>
          </div>
          <div class="flex justify-between text-red-700">
            <span class="font-semibold" id="cancel-fee-percent-label">Potongan Biaya (2.5%):</span>
            <span class="font-bold" id="cancel-fee-amount">Rp 0</span>
          </div>
          <div class="border-t border-red-200 pt-2 flex justify-between text-sm text-primary">
            <span class="font-bold">Dana Bersih Diterima:</span>
            <span class="font-extrabold" id="cancel-net-amount">Rp 0</span>
          </div>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Nomor Rekening & Nama Bank</label>
            <input type="text" id="cancel-bank-account" placeholder="Contoh: BCA 12345678 a/n Ahmad" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-xs font-semibold outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Alasan Pembatalan</label>
            <textarea id="cancel-reason" placeholder="Berikan alasan pembatalan Anda..." class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-2.5 text-xs font-semibold outline-none h-16 resize-none"></textarea>
          </div>
        </div>

        <button onclick="submitCancelSavings()" class="w-full bg-red-600 text-white font-bold py-3.5 rounded-xl hover:bg-red-700 transition-all text-xs flex items-center justify-center gap-2 shadow-md">
          Konfirmasi Pembatalan & Refund
          <span class="material-symbols-outlined text-[16px]">check_circle</span>
        </button>
      </div>
    </div>
  </div>

  <script>
    // Global User & App state
    let currentUser = null;
    let allUserSavings = []; // Array of all savings accounts for this user
    let userSavings = null;  // The currently selected active savings plan
    let activeLivestock = null;
    let selectedPackage = null;
    let activeSavingsId = null; // ID of the currently selected savings plan
    let locationsData = []; // Array of qurban distribution locations

    // Package List Data Source (Matches PRD - will be updated from DB)
    let packagesData = [
      { id: "Domba-A", category: "Domba", name: "Domba A", price: 2300000, desc: "Domba sehat dengan kualitas prima untuk ibadah qurban berkah.", weight: "23-26 kg", age: "1.2 Tahun", fit: "Fit (Sehat)", farmer: "Pak Ahmad Santoso", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuD01xxU7e937WQxR4nYZpcjwo3jrOsDL0hZredv170fV4494Sf4nk9mK9lA9C5la2mN7465F5kGeB0lfun46gDzvLGzgc3r_YJEc-TmkqzjKsC1VBRyTzO-ox9KmtD3xD8cU3bhz-mebaqpf7gnkhDuTirVNvOQnawacxh1Ibe7Xn3txcLOnO6fNSn8pIlhEStA1M4DefT4WQxQ_G_IBqrPm0z8RLaQx2cb2bSc-mvnDyF56DNfk0P86tyTWqwOjFRWjeZ88x6bGAoE" },
      { id: "Domba-B", category: "Domba", name: "Domba B", price: 2700000, desc: "Domba gemuk dengan nutrisi terjamin, cocok untuk keluarga.", weight: "28-32 kg", age: "1.5 Tahun", fit: "Fit (Sehat)", farmer: "Pak Ahmad Santoso", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAcSKoiNDvNcj7WRqA30TTDZ62jeDCtrrc51auT7bX9yL34Fh_OCm6WOVaYVJwyqMXDV_wBixrCvTKn8X9XtHY0n9zGfn4B-v-Hyg26rri-M52xuKRm7ZzVLBebahZV7Uvgyb4CFVDvS8jjUXvuS5k7CluJ_125V05rr_yWRX9ay0NoAzcs4vHiIyEPl_Qnnng7GtuIYZK6dYKw9iqNqFSlYvxrLktZEVrNW1nByzo1tcSZ1--Go092HFiwDmwKYQxtS9qoq6huwPwa" },
      { id: "Domba-C", category: "Domba", name: "Domba C (Super)", price: 3500000, desc: "Domba ukuran premium besar, daging melimpah, dirawat ahli.", weight: "35-40 kg", age: "1.8 Tahun", fit: "Fit (Sehat)", farmer: "Pak Ahmad Santoso", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBfBJ7wBa0ZumJYkwAP_2vKXYRW3p5izja6UrthrDxtCNP4-CmtWd3uT3Gi_H9OvaA6XGurQfR79O60dV7CZUi7RlhH7nACyjF5sDR37Pidun5WK_uPRonkN8wnAN_ynF09_A5OzZh7P0zefP5zlKu-vnxCqxv7ZY_FswmQu3Nk4l_U1vHZipy3imT74BJtfNJPSUr-EnoZJPwg6jfY8-017CgF8h3PTu4CfaJ29tTjXkiifDDMIZh-2g07sITg0Dgft4d9muCnm4Ed" },
      { id: "Sapi-Patungan", category: "Sapi Patungan", name: "Sapi Patungan (1/7)", price: 3500000, desc: "Patungan qurban sapi 1/7 kelompok. Pembagian kelompok diatur sistem otomatis syariah.", weight: "300-350 kg (total Sapi)", age: "2.5 Tahun", fit: "Fit (Sehat)", farmer: "Bapak Ahmad Santoso", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuD_kA8C62RZ59pO-FPosUtY3y231uFZ-ZJ43nnjSnDRRcKH0Tdpt5W7chA1fcoIQVgP6q15dMZXWfSQjBOlxIUA-iYyb9iZGgLeAimE0OTUnANRw9O4UCgnbF4nRntL6WDlBQr80G6z3nH9j5x7zKpp28OouByRiLM5RXOjOQMx_pSoXZ4bqgDS4ryb2_LkFNZU27w6BFq5-e620LfwbVOUJxlt8RHkNBZpLvC7PADfsrCipWyajbmXmQfBwAZSehJKDT2AP2L_3TBM" },
      { id: "Sapi-Utuh", category: "Sapi", name: "Sapi 1 Ekor", price: 24000000, desc: "Sapi Limousin utuh premium untuk qurban atas nama keluarga besar Anda (7 orang).", weight: "420-480 kg", age: "2.8 Tahun", fit: "Fit (Sehat)", farmer: "Bapak Ahmad Santoso", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDnGNcEb4t2Prwhfv2FLXutI-qb7nCCXFZkq-OXYSGIShiDll227LAvZfTcxENB1GUk1uyWzgklQ9Tfzcvk_lQYNR5TmSPIzw3lojNWIlq8qWMRG3qQqseg3JpIanIgRnZlDXPP5YZn-gy4eUkxtXqIMcNv1slowvpdQSNN4qKmGBKM92KX3HJ02YuoMGiDV7YuMqKJ3JYrzLRHi-opXIkTHO7I-_FhPYamarZw8nVwSHDDswfeq-Z1HLiioW5qFSOf41CIaFoev1ZO" }
    ];

    document.addEventListener('DOMContentLoaded', () => {
      // 1. Auth check
      const userStr = localStorage.getItem('currentUser');
      if (!userStr) {
        window.location.href = 'login.html';
        return;
      }
      
      try {
        currentUser = JSON.parse(userStr);
      } catch (e) {
        console.error("Gagal parse currentUser:", e);
        window.location.href = 'login.html';
        return;
      }

      if (!currentUser || !currentUser.email) {
        window.location.href = 'login.html';
        return;
      }

      const name = currentUser.name || 'User';
      const email = currentUser.email || '';
      const phone = currentUser.phone || '';
      const initial = name.charAt(0).toUpperCase();

      // Populate user info in header & details safely
      const avatarEl = document.getElementById('user-avatar-initial');
      if (avatarEl) avatarEl.textContent = initial;
      const displayEl = document.getElementById('user-display-name');
      if (displayEl) displayEl.textContent = name;

      // Populate profile safely
      const pInitialEl = document.getElementById('profile-initials');
      if (pInitialEl) pInitialEl.textContent = initial;
      const pNameEl = document.getElementById('profile-name');
      if (pNameEl) pNameEl.textContent = name;
      const pEmailEl = document.getElementById('profile-email');
      if (pEmailEl) pEmailEl.textContent = email;
      const pPhoneEl = document.getElementById('profile-phone');
      if (pPhoneEl) pPhoneEl.textContent = phone;

      // 2. Load User Savings State
      loadSavingsState();

      // 3. Initial layout
      switchTab('packages');

      // 4. Load Midtrans Snap JS SDK dynamically using client key from get-client-key.php
      fetch('get-client-key.php')
      .then(response => response.json())
      .then(data => {
        if (data.client_key && data.client_key !== 'SB-Mid-client-PLACEHOLDER') {
          const script = document.createElement('script');
          script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
          script.setAttribute('data-client-key', data.client_key);
          document.head.appendChild(script);
          console.log("Midtrans Snap JS SDK loaded successfully via env configuration.");
        }
      })
      .catch(error => {
        console.warn("Gagal memuat client key untuk Midtrans Snap SDK secara otomatis:", error);
      });
    });

    // Helper functions
    function renderPenyaluranLocations() {
      const select = document.getElementById('titip-wilayah');
      if (!select) return;
      select.innerHTML = '';
      locationsData.forEach(loc => {
        const opt = document.createElement('option');
        opt.value = loc.name;
        opt.textContent = `${loc.name} (${loc.region}) - Kap: ${loc.capacity}`;
        select.appendChild(opt);
      });
    }

    function loadDashboardDataFromServer() {
      return fetch(`api.php?action=get_dashboard_data&email=${encodeURIComponent(currentUser.email)}`)
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          // Update packagesData dynamically from DB
          if (data.packages && data.packages.length > 0) {
            packagesData.length = 0; // Clear array
            data.packages.forEach(pkg => {
              packagesData.push({
                id: pkg.id,
                category: pkg.category,
                type: pkg.type,
                name: pkg.name,
                price: parseFloat(pkg.price),
                desc: pkg.desc,
                weight: pkg.weight,
                age: pkg.age,
                fit: pkg.fit,
                image: pkg.image,
                farmer: "Pak Ahmad Santoso"
              });
            });
            renderPackagesList('all'); // Re-render package listings
          }

          // Update locationsData dynamically from DB
          if (data.locations && data.locations.length > 0) {
            locationsData = data.locations;
            renderPenyaluranLocations();
          }

          // Map database savings items to camelCase
          allUserSavings = (data.savings || []).map(dbSaving => {
            return {
              id: dbSaving.id,
              packageId: dbSaving.package_id,
              packageName: dbSaving.package_name,
              packageType: dbSaving.package_type,
              targetAmount: parseFloat(dbSaving.target_amount),
              currentAmount: parseFloat(dbSaving.collected_amount),
              remainingAmount: parseFloat(dbSaving.remaining_amount),
              progressPercent: parseInt(dbSaving.progress_percent) || 0,
              nextPaymentDeadline: dbSaving.next_payment_deadline || '25 June 2026',
              status: dbSaving.status,
              certNumber: dbSaving.cert_number,
              penyaluran: dbSaving.penyaluran ? {
                method: dbSaving.penyaluran.method,
                receiver: dbSaving.penyaluran.receiver,
                phone: dbSaving.penyaluran.phone,
                address: dbSaving.penyaluran.address,
                status: dbSaving.penyaluran.status
              } : null,
              history: (dbSaving.history || []).map(tx => {
                return {
                  id: tx.id,
                  type: tx.type,
                  amount: parseFloat(tx.amount),
                  date: tx.date,
                  time: tx.time,
                  status: tx.status,
                  token: tx.token,
                  paymentMethod: tx.payment_method
                };
              }),
              group: dbSaving.group ? {
                id: dbSaving.group.id,
                code: dbSaving.group.code,
                hijri_year: dbSaving.group.hijri_year,
                filled_slots: dbSaving.group.filled_slots,
                status: dbSaving.group.status,
                plans: dbSaving.group.plans || []
              } : null,
              group_votes: dbSaving.group_votes || [],
              user_voted: dbSaving.user_voted || false
            };
          });

          // Restore active savings ID or default to the first one
          if (allUserSavings.length > 0) {
            const matched = allUserSavings.find(s => s.id == activeSavingsId);
            if (matched) {
              userSavings = matched;
            } else {
              userSavings = allUserSavings[0];
            }
            activeSavingsId = userSavings.id;
          } else {
            userSavings = {
              id: "",
              packageId: "",
              packageName: "",
              packageType: "",
              targetAmount: 0,
              currentAmount: 0,
              remainingAmount: 0,
              progressPercent: 0,
              nextPaymentDeadline: "",
              status: "Niat",
              penyaluran: null,
              history: []
            };
            activeSavingsId = "";
          }

          updateUIStates();
          renderAccountSelectors();
        } else {
          console.error("Gagal memuat data dari server:", data.message);
        }
      })
      .catch(err => {
        console.error("Koneksi gagal saat memuat data dashboard:", err);
      });
    }

    function loadSavingsState() {
      loadDashboardDataFromServer();
    }

    function renderAccountSelectors() {
      // 1. Savings tab selector
      const savingsSelector = document.getElementById('savings-accounts-selector');
      if (savingsSelector) {
        if (!allUserSavings || allUserSavings.length <= 1) {
          savingsSelector.classList.add('hidden');
        } else {
          savingsSelector.classList.remove('hidden');
          savingsSelector.innerHTML = '';
          allUserSavings.forEach(saving => {
            const isSelected = saving.id === activeSavingsId;
            const icon = (saving.packageType || '').includes('Sapi') ? '🐄' : '🐏';
            const progress = saving.progressPercent || 0;
            
            const pill = document.createElement('button');
            pill.onclick = () => {
              activeSavingsId = saving.id;
              userSavings = saving;
              updateUIStates();
              renderAccountSelectors();
            };
            
            if (isSelected) {
              pill.className = "flex-none flex items-center gap-1.5 px-4 h-9 rounded-full text-xs font-bold bg-primary text-on-primary border border-primary shadow-xs transition-all active:scale-95";
            } else {
              pill.className = "flex-none flex items-center gap-1.5 px-4 h-9 rounded-full text-xs font-semibold bg-white text-on-surface-variant border border-outline-variant/30 hover:bg-surface-container-low transition-all active:scale-95";
            }
            
            pill.innerHTML = `
              <span>${icon}</span>
              <span>${saving.packageName}</span>
              <span class="opacity-80 font-mono">(${progress}%)</span>
            `;
            savingsSelector.appendChild(pill);
          });
        }
      }

      // 2. Tracking tab selector
      const trackingSelector = document.getElementById('tracking-accounts-selector');
      if (trackingSelector) {
        if (!allUserSavings || allUserSavings.length <= 1) {
          trackingSelector.classList.add('hidden');
        } else {
          trackingSelector.classList.remove('hidden');
          trackingSelector.innerHTML = '';
          allUserSavings.forEach(saving => {
            const isSelected = saving.id === activeSavingsId;
            const icon = (saving.packageType || '').includes('Sapi') ? '🐄' : '🐏';
            const status = saving.penyaluran ? saving.penyaluran.status : 'Belum Mulai';
            
            const pill = document.createElement('button');
            pill.onclick = () => {
              activeSavingsId = saving.id;
              userSavings = saving;
              updateUIStates();
              renderAccountSelectors();
            };
            
            if (isSelected) {
              pill.className = "flex-none flex items-center gap-1.5 px-4 h-9 rounded-full text-xs font-bold bg-primary text-on-primary border border-primary shadow-xs transition-all active:scale-95";
            } else {
              pill.className = "flex-none flex items-center gap-1.5 px-4 h-9 rounded-full text-xs font-semibold bg-white text-on-surface-variant border border-outline-variant/30 hover:bg-surface-container-low transition-all active:scale-95";
            }
            
            pill.innerHTML = `
              <span>${icon}</span>
              <span>${saving.packageName}</span>
              <span class="opacity-80 text-[9px] bg-black/10 px-1.5 py-0.5 rounded-md font-mono inline-block align-middle">${status}</span>
            `;
            trackingSelector.appendChild(pill);
          });
        }
      }

      // 3. Home tab selector
      const homeSelector = document.getElementById('home-accounts-selector');
      if (homeSelector) {
        if (!allUserSavings || allUserSavings.length <= 1) {
          homeSelector.classList.add('hidden');
        } else {
          homeSelector.classList.remove('hidden');
          homeSelector.innerHTML = '';
          allUserSavings.forEach(saving => {
            const isSelected = saving.id === activeSavingsId;
            const icon = (saving.packageType || '').includes('Sapi') ? '🐄' : '🐏';
            const progress = saving.progressPercent || 0;
            
            const pill = document.createElement('button');
            pill.onclick = () => {
              activeSavingsId = saving.id;
              userSavings = saving;
              updateUIStates();
              renderAccountSelectors();
            };
            
            if (isSelected) {
              pill.className = "flex-none flex items-center gap-1.5 px-4 h-9 rounded-full text-[11px] font-bold bg-primary text-on-primary border border-primary shadow-xs transition-all active:scale-95";
            } else {
              pill.className = "flex-none flex items-center gap-1.5 px-4 h-9 rounded-full text-[11px] font-semibold bg-white text-on-surface-variant border border-outline-variant/30 hover:bg-surface-container-low transition-all active:scale-95";
            }
            
            pill.innerHTML = `
              <span>${icon}</span>
              <span>${saving.packageName}</span>
              <span class="opacity-80 font-mono">(${progress}%)</span>
            `;
            homeSelector.appendChild(pill);
          });
        }
      }
    }

    function updateUIStates() {
      // 1. Alert banner logic
      const banner = document.getElementById('alert-banner');
      if (userSavings.packageId === "") {
        banner.classList.remove('hidden');
      } else {
        banner.classList.add('hidden');
      }

      // 2. Savings state check
      if (userSavings.packageId === "") {
        document.getElementById('savings-empty-state').classList.remove('hidden');
        document.getElementById('savings-active-state').classList.add('hidden');
        document.getElementById('tracking-empty-state').classList.remove('hidden');
        document.getElementById('tracking-active-state').classList.add('hidden');
        const certBanner = document.getElementById('impact-certificate-banner');
        if (certBanner) certBanner.classList.add('hidden');

        // Hide home summary elements
        const homeSummary = document.getElementById('home-savings-summary');
        if (homeSummary) homeSummary.classList.add('hidden');
        const homeSelector = document.getElementById('home-accounts-selector');
        if (homeSelector) homeSelector.classList.add('hidden');
      } else {
        document.getElementById('savings-empty-state').classList.add('hidden');
        document.getElementById('savings-active-state').classList.remove('hidden');

        // Render Home page savings summary
        const homeSummary = document.getElementById('home-savings-summary');
        if (homeSummary) {
          homeSummary.classList.remove('hidden');
          const percent = userSavings.progressPercent || 0;
          const icon = (userSavings.packageType || '').includes('Sapi') ? '🐄' : '🐏';
          
          homeSummary.innerHTML = `
            <div class="glass-panel rounded-2xl p-5 shadow-sm relative overflow-hidden">
              <!-- Decorative background -->
              <div class="absolute -top-10 -right-10 w-24 h-24 bg-primary/5 rounded-full blur-xl"></div>
              
              <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2">
                  <span class="text-2xl">${icon}</span>
                  <div>
                    <span class="text-[10px] text-on-surface-variant uppercase font-bold block leading-none">Tabungan Aktif</span>
                    <span class="font-extrabold text-sm text-on-surface">${userSavings.packageName}</span>
                  </div>
                </div>
                <span class="text-[11px] font-bold text-primary bg-emerald-50 px-2.5 py-1 rounded-full border border-primary/10">${percent}%</span>
              </div>
              
              <!-- Progress Bar -->
              <div class="w-full bg-surface-container rounded-full h-2 mb-4 overflow-hidden">
                <div class="bg-primary h-full rounded-full transition-all duration-1000 ease-out" style="width: ${percent}%"></div>
              </div>
              
              <!-- Detail Grid -->
              <div class="grid grid-cols-3 gap-2 border-t border-b border-outline-variant/20 py-3 mb-4">
                <div>
                  <span class="text-[9px] text-on-surface-variant block font-semibold uppercase leading-none">Total Tabungan</span>
                  <span class="font-extrabold text-primary text-xs mt-1 block">${formatCurrency(userSavings.currentAmount)}</span>
                </div>
                <div>
                  <span class="text-[9px] text-on-surface-variant block font-semibold uppercase leading-none">Target Dana</span>
                  <span class="font-bold text-on-surface text-xs mt-1 block">${formatCurrency(userSavings.targetAmount)}</span>
                </div>
                <div>
                  <span class="text-[9px] text-on-surface-variant block font-semibold uppercase leading-none">Sisa</span>
                  <span class="font-bold text-amber-700 text-xs mt-1 block">${formatCurrency(userSavings.remainingAmount)}</span>
                </div>
              </div>
              
              <!-- Buttons -->
              <div class="flex gap-2">
                <button onclick="switchTab('savings')" class="flex-grow bg-surface-container-low hover:bg-surface-container border border-outline-variant text-on-surface font-semibold py-2 rounded-xl text-xs transition-colors flex items-center justify-center gap-1 active:scale-95 transition-transform">
                  <span class="material-symbols-outlined text-[16px]">account_balance_wallet</span>
                  Detail
                </button>
                <button onclick="openPaymentModal()" class="flex-grow bg-primary text-on-primary font-bold py-2 rounded-xl text-xs hover:bg-primary/95 transition-all shadow-xs flex items-center justify-center gap-1 active:scale-95 transition-transform">
                  <span class="material-symbols-outlined text-[16px]">payments</span>
                  Setor Sekarang
                </button>
              </div>
            </div>
          `;
        }

        // Circular progress render
        const percent = userSavings.progressPercent || 0;
        document.getElementById('savings-progress-text').textContent = percent + '%';
        
        // Circular progress stroke-dashoffset
        // Circumference of r=76 is 2 * PI * 76 = ~477.5. Offset = 477 - (percent/100 * 477)
        const circumference = 477;
        const offset = circumference - (percent / 100) * circumference;
        document.getElementById('savings-progress-circle').style.strokeDashoffset = offset;

        // Display numbers
        document.getElementById('savings-current-display').textContent = formatCurrency(userSavings.currentAmount);
        document.getElementById('savings-target-display').textContent = formatCurrency(userSavings.targetAmount);
        document.getElementById('savings-remaining-display').textContent = formatCurrency(userSavings.remainingAmount);
        document.getElementById('savings-deadline').textContent = "Batas: " + (userSavings.nextPaymentDeadline || "25 June 2026");

        // Istiqomah badge unlock
        if (userSavings.currentAmount > 0) {
          document.getElementById('badge-istiqomah').classList.remove('opacity-30');
        } else {
          document.getElementById('badge-istiqomah').classList.add('opacity-30');
        }

        // Goal Reached badge and penyaluran box unlock
        const isGoalReached = userSavings.currentAmount >= userSavings.targetAmount;
        
        // Hide voting card by default
        document.getElementById('savings-group-voting').classList.add('hidden');

        if (isGoalReached) {
          document.getElementById('badge-reached').classList.remove('opacity-30');
          document.getElementById('savings-reminder-section').classList.add('hidden');
          
          const isPatungan = userSavings.packageType === 'Sapi Patungan';
          
          if (isPatungan && userSavings.group) {
            const groupStatus = userSavings.group.status; // open, full, ready, processed
            
            if (groupStatus === 'open' || groupStatus === 'full') {
              // Hide penyaluran selection & voting
              document.getElementById('savings-penyaluran-selection').classList.add('hidden');
              
              // Show group progress
              const groupSection = document.getElementById('savings-group-progress');
              if (groupSection) {
                groupSection.classList.remove('hidden');
                
                // Calculate lunas members count
                let lunasCount = 0;
                userSavings.group.plans.forEach(p => {
                  if (p.status !== 'saving') lunasCount++;
                });
                
                document.getElementById('group-progress-ratio').textContent = `${lunasCount}/7 Anggota Lunas`;
                document.getElementById('group-progress-bar-fill').style.width = `${(lunasCount/7)*100}%`;
                
                // Render group list
                const list = document.getElementById('group-members-list');
                list.innerHTML = '';
                userSavings.group.plans.forEach(p => {
                  const item = document.createElement('div');
                  item.className = "flex justify-between items-center py-0.5";
                  const name = p.shohibul_name || (p.is_institutional ? "Talangan Lembaga" : p.user_name);
                  const isMe = p.id === userSavings.id;
                  const statusText = p.status === 'saving' ? 'Menabung' : 'Lunas';
                  const statusColor = p.status === 'saving' ? 'text-on-surface-variant font-mono' : 'text-emerald-700 font-bold';
                  const emoji = p.status === 'saving' ? '⏳' : '🟢';
                  item.innerHTML = `
                    <span class="text-on-surface font-semibold flex items-center gap-1">${emoji} <span>${name}${isMe ? ' (Anda)' : ''}</span></span>
                    <span class="${statusColor}">${statusText}</span>
                  `;
                  list.appendChild(item);
                });
              }
            } else if (groupStatus === 'ready') {
              // Group is fully completed (7/7) but needs voting
              document.getElementById('savings-group-progress').classList.add('hidden');
              document.getElementById('savings-penyaluran-selection').classList.add('hidden');
              
              // Show voting card
              const votingCard = document.getElementById('savings-group-voting');
              votingCard.classList.remove('hidden');
              
              const optContainer = document.getElementById('voting-options-container');
              optContainer.innerHTML = '';
              
              if (!userSavings.user_voted) {
                // Populate voting options
                const qurbanLocations = locationsData.filter(loc => loc.category === 'qurban');
                if (qurbanLocations.length === 0) {
                  optContainer.innerHTML = '<p class="text-xs text-on-surface-variant italic">Belum ada lokasi qurban yang tersedia untuk dipilih.</p>';
                } else {
                  qurbanLocations.forEach(loc => {
                    const btn = document.createElement('button');
                    btn.className = "w-full bg-white border border-outline-variant hover:border-primary text-on-surface text-left p-3 rounded-xl text-xs flex justify-between items-center transition-all active:scale-98";
                    btn.onclick = () => submitVote(loc.id);
                    btn.innerHTML = `
                      <div>
                        <span class="font-bold block">${loc.name}</span>
                        <span class="text-[10px] text-on-surface-variant">${loc.region}</span>
                      </div>
                      <span class="material-symbols-outlined text-primary text-[18px]">how_to_vote</span>
                    `;
                    optContainer.appendChild(btn);
                  });
                }
              } else {
                optContainer.innerHTML = `
                  <div class="bg-emerald-50 text-emerald-800 text-xs p-3.5 rounded-xl border border-emerald-200 font-semibold flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span>Anda sudah memberikan suara. Menunggu hasil voting anggota kelompok lainnya.</span>
                  </div>
                `;
              }
              
              // Render vote counts
              const resultsList = document.getElementById('voting-results-list');
              resultsList.innerHTML = '';
              
              if (userSavings.group_votes && userSavings.group_votes.length > 0) {
                const voteCounts = {};
                userSavings.group_votes.forEach(v => {
                  voteCounts[v.location_name] = (voteCounts[v.location_name] || 0) + 1;
                });
                
                const header = document.createElement('h6');
                header.className = "font-bold text-[10px] text-on-surface mb-1";
                header.textContent = "Hasil Voting Sementara:";
                resultsList.appendChild(header);
                
                for (const [locName, count] of Object.entries(voteCounts)) {
                  const item = document.createElement('div');
                  item.className = "flex justify-between items-center py-0.5 text-on-surface-variant";
                  item.innerHTML = `
                    <span>🗳️ ${locName}</span>
                    <span class="font-bold text-primary">${count} / 7 Suara</span>
                  `;
                  resultsList.appendChild(item);
                }
              } else {
                resultsList.innerHTML = '<p class="text-[9px] text-on-surface-variant italic">Belum ada suara yang masuk.</p>';
              }
            } else {
              // status is processed, location is locked
              document.getElementById('savings-group-progress').classList.add('hidden');
              document.getElementById('savings-penyaluran-selection').classList.add('hidden');
            }
          } else {
            // Whole animal or Aqiqah savings
            document.getElementById('savings-group-progress').classList.add('hidden');
            if (!userSavings.penyaluran) {
              document.getElementById('savings-penyaluran-selection').classList.remove('hidden');
            } else {
              document.getElementById('savings-penyaluran-selection').classList.add('hidden');
            }
          }
        } else {
          document.getElementById('badge-reached').classList.add('opacity-30');
          document.getElementById('savings-reminder-section').classList.remove('hidden');
          document.getElementById('savings-penyaluran-selection').classList.add('hidden');
          document.getElementById('savings-group-progress').classList.add('hidden');
        }

        // Render savings transaction ledger
        renderSavingsHistory();

        // 3. Tracking View configurations
        if (userSavings.penyaluran) {
          document.getElementById('tracking-empty-state').classList.add('hidden');
          document.getElementById('tracking-active-state').classList.remove('hidden');

          // Match tracking info with livestock
          const pkg = packagesData.find(p => p.id === userSavings.packageId);
          if (pkg) {
            document.getElementById('tracking-livestock-name').textContent = pkg.name;
            document.getElementById('tracking-livestock-id').textContent = "ID: #" + pkg.id.toUpperCase();
            document.getElementById('tracking-livestock-weight').textContent = pkg.weight;
            document.getElementById('tracking-livestock-img').src = pkg.image;
          }

          // Populate timeline based on current status
          renderTimelineTrack();
        } else {
          document.getElementById('tracking-empty-state').classList.remove('hidden');
          document.getElementById('tracking-active-state').classList.add('hidden');
        }

        // 4. Impact digital certificate card logic
        const completedSavings = allUserSavings.filter(s => s.penyaluran && s.penyaluran.status === "Laporan Selesai");
        const certContainer = document.getElementById('impact-certificates-container');
        if (certContainer) {
          certContainer.innerHTML = '';
          if (completedSavings.length > 0) {
            completedSavings.forEach(saving => {
              const banner = document.createElement('div');
              banner.className = "bg-amber-50 border border-amber-200 p-5 rounded-2xl flex flex-col gap-3 shadow-sm mb-3";
              banner.innerHTML = `
                <div class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-amber-600 text-3xl" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                  <div>
                    <h3 class="font-bold text-sm text-on-surface">Sertifikat Digital Tersedia!</h3>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Terima kasih atas partisipasi qurban Anda untuk <strong>${saving.packageName}</strong>. Sertifikat digital resmi dapat diunduh.</p>
                  </div>
                </div>
                <button onclick="openCertificateView('${saving.id}')" class="w-full bg-primary text-on-primary font-bold py-3 rounded-xl hover:bg-primary/95 transition-all text-xs flex items-center justify-center gap-2 shadow-sm">
                  <span class="material-symbols-outlined text-[16px]">verified</span>
                  Lihat Sertifikat ${saving.packageName}
                </button>
              `;
              certContainer.appendChild(banner);
            });
          }
        }
      }
    }

    function renderSavingsHistory() {
      const listContainer = document.getElementById('savings-history-list');
      listContainer.innerHTML = '';

      if (!userSavings.history || userSavings.history.length === 0) {
        listContainer.innerHTML = '<p class="text-xs text-on-surface-variant text-center py-4">Belum ada riwayat transaksi.</p>';
        return;
      }

      userSavings.history.forEach(tx => {
        const item = document.createElement('div');
        
        let cursorStyle = "";
        if (tx.status === "Pending") {
          cursorStyle = " cursor-pointer hover:border-amber-400 transition-colors";
          item.onclick = () => showPendingPaymentDetails(tx.id);
          item.title = "Klik untuk melanjutkan pembayaran / melunasi";
        }
        
        item.className = "glass-card p-3 rounded-xl flex items-center gap-3 shadow-xs hover:scale-[1.01] hover:shadow-sm active:scale-99 transition-all duration-300" + cursorStyle;
        
        let icon = "add_circle";
        if (tx.type === "Autodebet") icon = "autorenew";

        const statusLabel = tx.status === 'Failed' ? 'Expired' : tx.status;
        const statusColor = tx.status === 'Success' 
          ? 'bg-emerald-100 text-emerald-800' 
          : (tx.status === 'Pending' ? 'bg-amber-100 text-amber-900' : 'bg-red-100 text-red-800');

        item.innerHTML = `
          <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-[20px]">${icon}</span>
          </div>
          <div class="flex-grow">
            <h5 class="text-xs font-bold text-on-surface">${tx.type}</h5>
            <span class="text-[10px] text-on-surface-variant">${tx.date} • ${tx.time}</span>
          </div>
          <div class="text-right">
            <span class="text-xs font-bold text-primary">+${formatCurrency(tx.amount)}</span>
            <span class="text-[8px] ${statusColor} px-1.5 py-0.5 rounded-full block mt-0.5 w-fit ml-auto">${statusLabel}</span>
          </div>
        `;
        listContainer.appendChild(item);
      });
    }

    function renderTimelineTrack() {
      const container = document.getElementById('timeline-track-element');
      container.innerHTML = '';

      // Total stages in physical execution
      let stages = [];
      const penyaluranMethod = userSavings.penyaluran ? userSavings.penyaluran.method : 'Titip Qurban';
      
      if (penyaluranMethod === 'Dikirim') {
        stages = [
          { key: "Tabungan Aktif", label: "Tabungan Aktif", desc: "Pembukaan tabungan qurban disetujui", icon: "check" },
          { key: "Dana Terkumpul", label: "Dana Terkumpul", desc: "Target dana terkumpul 100%", icon: "payments" },
          { key: "Penyaluran Dipilih", label: "Metode Penyaluran: Kirim", desc: `Kirim ke: ${userSavings.penyaluran.receiver || 'Rumah'} (${userSavings.penyaluran.phone || ''})`, icon: "assignment_turned_in" },
          { key: "Hewan Dipilih", label: "Hewan Qurban Dipilih", desc: "Hewan dipilah dan ditag atas nama Anda", icon: "pets" },
          { key: "Pemeriksaan Kesehatan", label: "Pemeriksaan Kesehatan", desc: "Lulus uji kelayakan & kesehatan hewan", icon: "medical_services" },
          { key: "Penjadwalan Kirim", label: "Penjadwalan Pengiriman", desc: "Penjadwalan tanggal kirim H-3 Idul Adha", icon: "calendar_today" },
          { key: "Dalam Perjalanan", label: "Dalam Perjalanan", desc: "Hewan sedang diantar oleh kurir peternakan", icon: "local_shipping" },
          { key: "Laporan Selesai", label: "Hewan Diterima", desc: "Hewan qurban hidup telah diterima di lokasi Anda", icon: "workspace_premium" }
        ];
      } else if (penyaluranMethod === 'Ambil Langsung') {
        stages = [
          { key: "Tabungan Aktif", label: "Tabungan Aktif", desc: "Pembukaan tabungan qurban disetujui", icon: "check" },
          { key: "Dana Terkumpul", label: "Dana Terkumpul", desc: "Target dana terkumpul 100%", icon: "payments" },
          { key: "Penyaluran Dipilih", label: "Metode Penyaluran: Ambil", desc: "Ambil langsung di Malang Highland Farm", icon: "assignment_turned_in" },
          { key: "Hewan Dipilih", label: "Hewan Qurban Dipilih", desc: "Hewan dipilah dan ditag atas nama Anda", icon: "pets" },
          { key: "Pemeriksaan Kesehatan", label: "Pemeriksaan Kesehatan", desc: "Lulus uji kelayakan & kesehatan hewan", icon: "medical_services" },
          { key: "Siap Diambil", label: "Siap Diambil", desc: "Hewan siap diambil dengan membawa invoice lunas", icon: "store" },
          { key: "Laporan Selesai", label: "Telah Diambil", desc: "Hewan qurban telah berhasil diambil oleh Anda", icon: "workspace_premium" }
        ];
      } else {
        stages = [
          { key: "Tabungan Aktif", label: "Tabungan Aktif", desc: "Pembukaan tabungan qurban disetujui", icon: "check" },
          { key: "Dana Terkumpul", label: "Dana Terkumpul", desc: "Target dana terkumpul 100%", icon: "payments" },
          { key: "Penyaluran Dipilih", label: "Metode Penyaluran: Titip", desc: `Lokasi: ${userSavings.penyaluran ? userSavings.penyaluran.address : 'Palestina'}`, icon: "assignment_turned_in" },
          { key: "Hewan Dipilih", label: "Hewan Qurban Dipilih", desc: "Hewan dipilah dan ditag atas nama Anda", icon: "pets" },
          { key: "Pemeriksaan Kesehatan", label: "Pemeriksaan Kesehatan", desc: "Lulus uji kelayakan &amp; kesehatan hewan", icon: "medical_services" },
          { key: "Pengiriman Lokasi", label: "Pengiriman ke Lokasi", desc: "Hewan didistribusikan ke lokasi penyembelihan", icon: "local_shipping" },
          { key: "Penyembelihan", label: "Penyembelihan Hewan", desc: "Hewan disembelih sesuai ketentuan syariat", icon: "content_cut" },
          { key: "Distribusi", label: "Distribusi Paket Daging", desc: "Daging disalurkan kepada penerima manfaat", icon: "volunteer_activism" },
          { key: "Laporan Selesai", label: "Laporan Selesai &amp; Sertifikat", desc: "Dokumentasi penyembelihan dan sertifikat dirilis", icon: "workspace_premium" }
        ];
      }

      // Determine current index from status
      const currentStatus = userSavings.penyaluran ? userSavings.penyaluran.status : 'Penyaluran Dipilih';
      let activeIndex = stages.findIndex(s => s.key === currentStatus);
      if (activeIndex === -1) activeIndex = 2; // Default to selection phase

      // Calculate vertical active line height
      // Percent based on activeIndex / (stages.length - 1)
      const trackProgress = (activeIndex / (stages.length - 1)) * 100;
      document.getElementById('timeline-track-element').style.setProperty('--timeline-active-height', `${trackProgress}%`);
      
      // Inject timeline style block
      const styleBlock = document.createElement('style');
      styleBlock.innerHTML = `
        .timeline-track-active::before {
          height: ${trackProgress}% !important;
        }
      `;
      document.head.appendChild(styleBlock);

      stages.forEach((stage, idx) => {
        const isCompleted = idx < activeIndex;
        const isActive = idx === activeIndex;
        const isLocked = idx > activeIndex;

        let nodeClass = "bg-surface-container border-2 border-outline-variant text-on-surface-variant";
        let textClass = "text-on-surface-variant opacity-60";
        let iconContent = stage.icon;

        if (isCompleted) {
          nodeClass = "bg-primary text-white border-primary shadow-xs";
          textClass = "text-on-surface";
          iconContent = "check";
        } else if (isActive) {
          nodeClass = "bg-white border-4 border-primary text-primary shadow-md scale-110";
          textClass = "text-primary font-bold";
        }

        let extraContent = '';
        if (stage.key === "Hewan Dipilih" && (isActive || isCompleted)) {
          const pkg = packagesData.find(p => p.id === userSavings.packageId);
          if (pkg) {
            extraContent = `
              <div class="glass-card p-4 rounded-xl w-full border-primary/20 mt-2 max-w-sm">
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-bold text-xs text-primary">${pkg.name}</h4>
                  <span class="text-[8px] bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full font-bold">Terpilih</span>
                </div>
                <img class="w-full h-24 object-cover rounded-lg mb-2" src="${pkg.image}" alt="${pkg.name}">
                <div class="flex gap-4">
                  <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant">monitor_weight</span>
                    <span class="text-[10px] text-on-surface-variant">${pkg.weight}</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-on-surface-variant">verified</span>
                    <span class="text-[10px] text-on-surface-variant">${pkg.fit}</span>
                  </div>
                </div>
              </div>
            `;
          }
        } else if (stage.key === "Laporan Selesai" && (isActive || isCompleted)) {
          extraContent = `
            <div class="mt-2">
              <button onclick="openCertificateView()" class="bg-primary text-on-primary text-[10px] font-bold px-3 py-1.5 rounded-lg active:scale-95 transition-transform flex items-center gap-1 shadow-sm">
                <span class="material-symbols-outlined text-[14px]">workspace_premium</span> Lihat Sertifikat Qurban
              </button>
            </div>
          `;
        }

        const step = document.createElement('div');
        step.className = `relative pl-12 mb-8 flex items-start transition-all duration-300`;
        
        step.innerHTML = `
          <div class="absolute left-0 w-9 h-9 rounded-full flex items-center justify-center z-10 ${nodeClass}">
            <span class="material-symbols-outlined text-[18px] font-bold">${iconContent}</span>
          </div>
          <div class="flex-grow">
            <h4 class="text-xs font-bold ${textClass}">${stage.label}</h4>
            <p class="text-[10px] text-on-surface-variant leading-tight mt-0.5">${stage.desc}</p>
            ${extraContent}
          </div>
        `;
        container.appendChild(step);
      });
    }

    function renderPackagesList(category) {
      const container = document.getElementById('packages-list');
      container.innerHTML = '';

      const filtered = category === 'all' 
        ? packagesData 
        : packagesData.filter(p => {
            if (category === 'qurban' || category === 'aqiqah') {
              return p.category === category;
            }
            if (category === 'Domba') return p.type === 'domba';
            if (category === 'Sapi Patungan') return p.type === 'sapi_patungan';
            if (category === 'Sapi') return p.type === 'sapi_utuh';
            return p.type === category || p.category === category;
          });

      filtered.forEach(p => {
        const card = document.createElement('div');
        card.className = "glass-card rounded-2xl overflow-hidden shadow-xs flex flex-col group hover:scale-[1.02] hover:shadow-md transition-all duration-300";
        
        card.innerHTML = `
          <div class="relative h-28 w-full overflow-hidden">
            <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="${p.image}" alt="${p.name}">
            <div class="absolute top-2 left-2 bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full text-[8px] font-extrabold flex items-center gap-0.5 shadow-sm">
              <span class="material-symbols-outlined text-[10px]" style="font-variation-settings: 'FILL' 1;">star</span>
              ${p.category.toUpperCase()}
            </div>
          </div>
          <div class="p-3 flex flex-col gap-2 flex-grow">
            <div class="flex flex-col gap-0.5">
              <h3 class="font-extrabold text-[13px] text-on-surface leading-tight truncate" title="${p.name}">${p.name}</h3>
              <span class="text-[9px] text-on-surface-variant font-medium leading-none">Bobot: ${p.weight}</span>
              <div class="mt-1 flex flex-col">
                <p class="font-extrabold text-[13px] text-primary leading-tight">${formatCurrency(p.price)}</p>
                <span class="text-[8px] text-on-surface-variant leading-none">Termasuk operasional</span>
              </div>
            </div>
            
            <p class="text-[10px] text-on-surface-variant leading-normal line-clamp-2 mt-0.5">${p.desc}</p>
            
            <div class="flex flex-col gap-1.5 mt-auto pt-1">
              <button onclick="viewLivestockDetail('${p.id}')" class="w-full border border-primary text-primary hover:bg-emerald-50/20 font-bold text-[10px] py-1.5 rounded-lg transition-all active:scale-95 transition-transform">Lihat Detail</button>
              <button onclick="openSimulationModal('${p.id}')" class="w-full bg-primary text-on-primary font-bold text-[10px] py-1.5 rounded-lg hover:bg-primary/95 transition-all shadow-xs active:scale-95 transition-transform">Mulai Menabung</button>
            </div>
          </div>
        `;
        container.appendChild(card);
      });
    }

    function filterPackages(category) {
      // Toggle button states
      const buttons = document.querySelectorAll('.package-filter-btn');
      buttons.forEach(btn => {
        btn.classList.remove('bg-primary', 'text-on-primary', 'shadow-sm');
        btn.classList.add('bg-surface-container', 'text-on-surface-variant');
      });

      const activeBtn = Array.from(buttons).find(b => {
        const text = b.textContent.toLowerCase();
        if (category === 'all') return text.includes('semua');
        if (category === 'qurban') return text.includes('qurban');
        if (category === 'aqiqah') return text.includes('aqiqah');
        if (category === 'Domba') return text.includes('domba');
        if (category === 'Sapi Patungan') return text.includes('sapi patungan');
        if (category === 'Sapi') return text.includes('sapi utuh');
        return false;
      });
      if (activeBtn) {
        activeBtn.classList.remove('bg-surface-container', 'text-on-surface-variant');
        activeBtn.classList.add('bg-primary', 'text-on-primary', 'shadow-sm');
      }

      renderPackagesList(category);
    }

    // Livestock Details Sub-view Router
    function viewLivestockDetail(packageId) {
      activeLivestock = packagesData.find(p => p.id === packageId);
      if (!activeLivestock) return;

      const detailContainer = document.getElementById('livestock-detail-container');
      detailContainer.innerHTML = `
        <section class="relative w-full h-56 rounded-xl overflow-hidden shadow-xs border border-outline-variant/30">
          <img class="w-full h-full object-cover" src="${activeLivestock.image}" alt="${activeLivestock.name}">
          <div class="absolute top-3 right-3 flex flex-col gap-2">
            <div class="bg-amber-100 text-amber-900 px-3 py-1 rounded-full text-[10px] font-bold flex items-center gap-1 shadow-lg">
              <span class="material-symbols-outlined text-[14px]">workspace_premium</span> Premium Grade
            </div>
            <div class="bg-primary text-on-primary px-3 py-1 rounded-full text-[10px] font-bold flex items-center gap-1 shadow-lg">
              <span class="material-symbols-outlined text-[14px]">verified</span> Lolos Tes Kemenag
            </div>
          </div>
        </section>

        <section class="glass-panel p-5 rounded-2xl shadow-xs flex flex-col gap-4">
          <div class="flex justify-between items-start border-b border-white/20 pb-3">
            <div>
              <h1 class="font-bold text-lg text-on-surface">${activeLivestock.name}</h1>
              <p class="text-xs text-primary font-bold tracking-wider mt-0.5">ID: #${activeLivestock.id.toUpperCase()}</p>
            </div>
            <div class="text-right">
              <span class="text-[10px] text-on-surface-variant uppercase font-bold block">Harga Paket</span>
              <p class="font-bold text-primary text-lg">${formatCurrency(activeLivestock.price)}</p>
            </div>
          </div>

          <!-- Specs grid details -->
          <div class="grid grid-cols-3 gap-2 py-3 text-center bg-white/20 border border-white/10 rounded-2xl backdrop-blur-xs">
            <div>
              <span class="material-symbols-outlined text-primary block text-xl">weight</span>
              <span class="text-[9px] text-on-surface-variant block">Bobot</span>
              <p class="font-bold text-xs text-on-surface mt-0.5">${activeLivestock.weight}</p>
            </div>
            <div>
              <span class="material-symbols-outlined text-primary block text-xl">calendar_today</span>
              <span class="text-[9px] text-on-surface-variant block">Umur Hewan</span>
              <p class="font-bold text-xs text-on-surface mt-0.5">${activeLivestock.age}</p>
            </div>
            <div>
              <span class="material-symbols-outlined text-primary block text-xl">health_and_safety</span>
              <span class="text-[9px] text-on-surface-variant block">Kesehatan</span>
              <p class="font-bold text-xs text-primary mt-0.5">${activeLivestock.fit}</p>
            </div>
          </div>
          
          <div class="text-xs text-on-surface-variant leading-relaxed">
            <h4 class="font-bold text-on-surface mb-1 text-sm">Deskripsi Hewan</h4>
            ${activeLivestock.desc}
          </div>
        </section>

        <!-- Origin Farm info -->
        <section class="glass-panel rounded-2xl overflow-hidden shadow-xs">
          <div class="h-28 w-full bg-surface-container relative">
            <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBAr5DCEloZSy50HnNRUPHnFcWqlr-K4RFGu3KBsy9RvMG4RNql3gxsBwU-DI5in4H32JMHEDTb5ycQEecf3JzG4lZJrHN0UodkBDumInc6Co4jdmIvp5zBa3tE1BWoj27y_X1Ya3Zcw1Fpmg3zSa7u88OcY1xM_qT4916eXdlNjPIXmv_BmXOp_ml_1cPMlOSX7HXpM6xiCrNLcS4QQzWE49gUrZRO4ntW4QueaOO0V66p7XveWqfPkjEOi1PZhITdeuwW5j8OyIFk" alt="Farm">
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
            <div class="absolute bottom-2.5 left-4 text-white">
              <span class="text-[8px] opacity-80 uppercase block">Peternakan Asal</span>
              <h4 class="font-bold text-xs">Malang Highland Farm - Kemitraan Amanah</h4>
            </div>
          </div>
          <div class="p-3 flex items-center gap-3">
            <img class="w-10 h-10 rounded-full object-cover border" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBqAf_iTyCjJXI8vYXXH7IJsSRWwub6kvY-3Uh04IYkBVMujtAusV1G85BbY5xqM1ZwwHK3ltqWPw0WBcD9NSpHIDo9oQd5fCZvWg4uiV71aB3w04ZmN8daqZ7KTA0wNjUn16x_bM8F_itz6pzrZKTttpriVIb_BEyRzJmOggqSV-JfapV5udHJuTxgJPgqbMmF0Hw3WZVghWYl-dOfKWrzQL_NPNPw4DwYJ1cJBtSVjzLYcKhjko7xaXpoqRJtBSW5zUnIlfJCZzTP" alt="Peternak">
            <div class="flex-grow">
              <h5 class="text-xs font-bold text-on-surface">Pak Ahmad Santoso</h5>
              <span class="text-[10px] text-on-surface-variant">Keahlian 15 Tahun • Peternak Binaan Utama</span>
            </div>
            <button onclick="alert('Layanan chat konsultasi belum aktif.')" class="w-8 h-8 rounded-full bg-emerald-50 text-primary flex items-center justify-center hover:bg-emerald-100 active:scale-95 transition-transform">
              <span class="material-symbols-outlined text-[18px]">chat</span>
            </button>
          </div>
        </section>

        <!-- Buy button trigger -->
        <button onclick="openSimulationModal('${activeLivestock.id}')" class="w-full bg-primary text-on-primary font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 hover:bg-primary/95 transition-all shadow-md">
          <span class="material-symbols-outlined text-[18px]">payments</span> Mulai Menabung Paket Ini
        </button>
      `;

      switchTab('livestock-detail');
    }

    // Modal Simulation Controls
    function updateAqiqahTargetPrice() {
      if (!selectedPackage) return;
      
      let targetPrice = selectedPackage.price;
      const isAqiqah = selectedPackage.category === 'aqiqah';
      
      if (isAqiqah) {
        const gender = document.getElementById('sim-aqiqah-child-gender').value;
        if (gender === 'putra') {
          targetPrice = selectedPackage.price * 2;
          document.getElementById('sim-package-name').textContent = selectedPackage.name + " (Bundling 2 Ekor)";
        } else {
          document.getElementById('sim-package-name').textContent = selectedPackage.name;
        }
      }
      
      document.getElementById('sim-package-price').textContent = formatCurrency(targetPrice);
      
      // Re-calculate simulation estimates
      const initial = Number(document.getElementById('sim-initial-amount').value) || 0;
      const rem = Math.max(0, targetPrice - initial);
      const est = Math.round(rem / 10);
      document.getElementById('sim-monthly-estimate').textContent = formatCurrency(est) + " / bln";
    }

    function openSimulationModal(packageId) {
      selectedPackage = packagesData.find(p => p.id === packageId);
      if (!selectedPackage) return;

      document.getElementById('sim-package-category').textContent = selectedPackage.category.toUpperCase();
      document.getElementById('sim-package-name').textContent = selectedPackage.name;
      document.getElementById('sim-package-price').textContent = formatCurrency(selectedPackage.price);
      
      const isAqiqah = selectedPackage.category === 'aqiqah';
      const aqiqahFields = document.getElementById('aqiqah-wizard-fields');
      
      if (isAqiqah) {
        aqiqahFields.classList.remove('hidden');
        document.getElementById('sim-aqiqah-child-name').value = '';
        document.getElementById('sim-aqiqah-child-gender').value = 'putri';
        document.getElementById('sim-aqiqah-child-birthdate').value = '';
        updateAqiqahTargetPrice();
      } else {
        aqiqahFields.classList.add('hidden');
      }

      // Initial estimate default setoran awal = Rp 500.000 or targetPrice/10
      let targetPrice = selectedPackage.price;
      if (isAqiqah && document.getElementById('sim-aqiqah-child-gender').value === 'putra') {
        targetPrice = selectedPackage.price * 2;
      }
      const initialSeed = Math.round(targetPrice / 10);
      document.getElementById('sim-initial-amount').value = initialSeed;
      calculateMonthlyEstimate(initialSeed);

      // Add listener to calculate estimates on typing
      document.getElementById('sim-initial-amount').oninput = (e) => {
        calculateMonthlyEstimate(Number(e.target.value));
      };

      document.getElementById('simulation-modal').classList.remove('hidden');
    }

    function calculateMonthlyEstimate(initial) {
      if (!selectedPackage) return;
      let targetPrice = selectedPackage.price;
      const isAqiqah = selectedPackage.category === 'aqiqah';
      if (isAqiqah) {
        const gender = document.getElementById('sim-aqiqah-child-gender').value;
        if (gender === 'putra') {
          targetPrice = selectedPackage.price * 2;
        }
      }
      const rem = Math.max(0, targetPrice - initial);
      const est = Math.round(rem / 10);
      document.getElementById('sim-monthly-estimate').textContent = formatCurrency(est) + " / bln";
    }

    function closeSimulationModal() {
      document.getElementById('simulation-modal').classList.add('hidden');
    }

    function createTabungan() {
      if (!selectedPackage) return;

      const isAqiqah = selectedPackage.category === 'aqiqah';
      const childName = isAqiqah ? document.getElementById('sim-aqiqah-child-name').value.trim() : null;
      const childGender = isAqiqah ? document.getElementById('sim-aqiqah-child-gender').value : null;
      const childBirthdate = isAqiqah ? document.getElementById('sim-aqiqah-child-birthdate').value : null;

      let targetPrice = selectedPackage.price;
      if (isAqiqah) {
        if (!childName || !childBirthdate) {
          alert("Nama anak dan tanggal lahir anak wajib diisi untuk program Aqiqah.");
          return;
        }
        if (childGender === 'putra') {
          targetPrice = selectedPackage.price * 2;
        }
      }

      const initialAmount = Number(document.getElementById('sim-initial-amount').value) || 0;
      if (initialAmount <= 0) {
        alert("Nominal setoran awal minimal Rp 10.000");
        return;
      }
      if (initialAmount > targetPrice) {
        alert("Setoran awal melebihi harga paket!");
        return;
      }

      // Show loading
      const confirmBtn = document.querySelector('#simulation-modal button[onclick="createTabungan()"]');
      const originalText = confirmBtn ? confirmBtn.innerHTML : "Buat Rencana Tabungan";
      if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px] mr-1.5 align-middle">sync</span> Memproses...';
      }

      fetch('api.php?action=create_savings', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email: currentUser.email,
          packageId: selectedPackage.id,
          category: selectedPackage.category,
          shohibulName: currentUser.name,
          aqiqahChildName: childName,
          aqiqahChildGender: childGender,
          aqiqahChildBirthdate: childBirthdate,
          scheme: 'bulanan',
          initialAmount: initialAmount
        })
      })
      .then(response => response.json())
      .then(data => {
        if (confirmBtn) {
          confirmBtn.disabled = false;
          confirmBtn.innerHTML = originalText;
        }

        if (data.status === 'success') {
          closeSimulationModal();
          
          // Re-load the state from database and redirect
          activeSavingsId = data.savings_id;
          loadDashboardDataFromServer().then(() => {
            // Re-sync UI states
            updateUIStates();
            renderAccountSelectors();
            switchTab('savings');

            // If there's an initial amount, find the newly created pending transaction in history and trigger it!
            if (data.initial_amount > 0) {
              const pendingTx = userSavings.history.find(t => t.type === 'Setoran Awal' && t.status === 'Pending');
              if (pendingTx) {
                showPendingPaymentDetails(pendingTx.id);
              }
            } else {
              alert(`Rencana tabungan untuk ${selectedPackage.name} berhasil dibuat!`);
            }
          });
        } else {
          alert("Gagal membuat rencana tabungan: " + data.message);
        }
      })
      .catch(err => {
        if (confirmBtn) {
          confirmBtn.disabled = false;
          confirmBtn.innerHTML = originalText;
        }
        console.error(err);
        alert("Gagal menghubungi server untuk membuat rencana tabungan.");
      });
    }

    // Tab view switcher navigation router
    function switchTab(tabId) {
      // 1. Hide all tab views
      document.querySelectorAll('.tab-view').forEach(view => {
        view.classList.add('hidden');
      });

      // 2. Show selected tab view
      document.getElementById('tab-' + tabId).classList.remove('hidden');

      // 3. Update active nav button states (bottom navigation highlights)
      document.querySelectorAll('.nav-tab-btn').forEach(btn => {
        btn.className = "nav-tab-btn flex-1 flex flex-col items-center justify-center text-on-surface-variant hover:text-primary active:scale-95 transition-all text-xs py-1";
        const iconSpan = btn.querySelector('.material-symbols-outlined');
        if (iconSpan) {
          iconSpan.style.fontVariationSettings = "'FILL' 0";
        }
      });

      // Highlight active nav tab mapping
      let navButtonId = 'nav-packages';
      if (tabId === 'savings' || tabId === 'penyaluran') navButtonId = 'nav-savings';
      if (tabId === 'tracking') navButtonId = 'nav-tracking';
      if (tabId === 'impact' || tabId === 'certificate') navButtonId = 'nav-impact';
      if (tabId === 'profile') navButtonId = 'nav-profile';
      
      const activeBtn = document.getElementById(navButtonId);
      if (activeBtn) {
        activeBtn.className = "nav-tab-btn flex-1 flex flex-col items-center justify-center text-primary font-bold active:scale-95 transition-all text-xs py-1";
        const iconSpan = activeBtn.querySelector('.material-symbols-outlined');
        if (iconSpan) {
          iconSpan.style.fontVariationSettings = "'FILL' 1";
        }
      }

      // Scroll to top
      window.scrollTo(0, 0);
    }

    // Payment Gateway simulator box
    function openPaymentModal() {
      // Default invoice amount to remaining or 500000
      const defaultPay = Math.min(userSavings.remainingAmount || 0, 500000);
      
      const amountInput = document.getElementById('payment-amount-input');
      if (amountInput) {
        amountInput.value = defaultPay;
      }

      const stepInput = document.getElementById('payment-step-input');
      if (stepInput) {
        stepInput.classList.remove('hidden');
      }
      
      const modal = document.getElementById('payment-modal');
      if (modal) {
        modal.classList.remove('hidden');
      }
    }

    function setPaymentAmount(amt) {
      const actualAmt = Math.min(userSavings.remainingAmount, amt);
      document.getElementById('payment-amount-input').value = actualAmt;
    }

    function processPaymentSubmit() {
      const amtInput = Number(document.getElementById('payment-amount-input').value) || 0;
      if (amtInput <= 0) {
        alert("Nominal pembayaran minimal Rp 10.000");
        return;
      }
      if (amtInput > userSavings.remainingAmount) {
        alert(`Nominal pembayaran melebihi sisa tabungan (${formatCurrency(userSavings.remainingAmount)})!`);
        return;
      }

      // Read selected method
      const methods = document.getElementsByName('payment_method');
      let method = 'QRIS';
      for (const m of methods) {
        if (m.checked) {
          method = m.value;
          break;
        }
      }

      // Loading state on button
      const payBtn = document.querySelector('#payment-step-input button[onclick="processPaymentSubmit()"]');
      const originalBtnText = payBtn ? payBtn.innerHTML : "Lanjutkan Pembayaran";
      if (payBtn) {
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px] mr-1.5 align-middle">sync</span> Memproses...';
      }

      // IF Simulasi Instan (Lokal)
      if (method === 'Simulasi Instan') {
        fetch('api.php?action=simulate_payment', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            amount: amtInput,
            savings_id: userSavings.id,
            payment_method: 'Simulasi Instan'
          })
        })
        .then(response => response.json())
        .then(data => {
          if (payBtn) {
            payBtn.disabled = false;
            payBtn.innerHTML = originalBtnText;
          }
          if (data.status === 'success') {
            closePaymentModal();
            loadDashboardDataFromServer().then(() => {
              alert(`Simulasi Setoran sebesar ${formatCurrency(amtInput)} sukses diproses secara lokal!`);
            });
          } else {
            alert("Gagal memproses simulasi: " + data.message);
          }
        })
        .catch(err => {
          if (payBtn) {
            payBtn.disabled = false;
            payBtn.innerHTML = originalBtnText;
          }
          console.error("Simulation Failed:", err);
          alert("Gagal menghubungi server lokal untuk simulasi.");
        });
        return;
      }

      // Otherwise, process Midtrans Sandbox Online Payment Flow
      if (payBtn) {
        payBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px] mr-1.5 align-middle">sync</span> Memuat Snap Midtrans...';
      }

      // Save amount temporally to verify success in manual fallback simulation
      document.getElementById('payment-modal').dataset.pendingAmount = amtInput;
      document.getElementById('payment-modal').dataset.pendingMethod = method;

      fetch('get-snap-token.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          amount: amtInput,
          email: currentUser.email,
          name: currentUser.name,
          savings_id: userSavings.id,
          payment_method: method
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error("HTTP error status " + response.status);
        }
        return response.json();
      })
      .then(data => {
        // Reset button state
        if (payBtn) {
          payBtn.disabled = false;
          payBtn.innerHTML = originalBtnText;
        }

        if (data.token) {
          // Trigger Midtrans Snap Popup
          window.snap.pay(data.token, {
            onSuccess: function(result) {
              handleMidtransPaymentSuccess(amtInput, result.payment_type || 'Midtrans', result.order_id);
            },
            onPending: function(result) {
              handleMidtransPaymentPending(amtInput, result.payment_type || 'Midtrans', data.token);
            },
            onError: function(result) {
              alert("Pembayaran Midtrans gagal atau terjadi kesalahan.");
            },
            onClose: function() {
              alert("Jendela pembayaran ditutup.");
            }
          });
        } else {
          throw new Error(data.message || "Token Snap kosong.");
        }
      })
      .catch(error => {
        // Reset button state
        if (payBtn) {
          payBtn.disabled = false;
          payBtn.innerHTML = originalBtnText;
        }
        console.warn("Midtrans Backend Connection Failed:", error);
        alert("Gagal memanggil backend PHP Midtrans Sandbox. Pastikan server PHP berjalan dan konfigurasi .env Anda sudah benar.");
      });
    }

    function handleMidtransPaymentSuccess(amount, paymentType, orderId) {
      closePaymentModal();
      
      // Kirim approval langsung ke server lokal (karena webhook luar tidak bisa menembus localhost)
      fetch('api.php?action=approve_transaction', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          orderId: orderId
        })
      })
      .then(response => response.json())
      .then(data => {
        loadDashboardDataFromServer().then(() => {
          alert(`Setoran tabungan sebesar ${formatCurrency(amount)} melalui Midtrans (${paymentType}) berhasil disetujui!`);
        });
      })
      .catch(err => {
        console.error("Gagal melakukan approval lokal:", err);
        loadDashboardDataFromServer().then(() => {
          alert(`Setoran tabungan sebesar ${formatCurrency(amount)} melalui Midtrans (${paymentType}) berhasil disetujui!`);
        });
      });
    }

    function handleMidtransPaymentPending(amount, paymentType, token) {
      closePaymentModal();
      loadDashboardDataFromServer().then(() => {
        alert(`Pembayaran sebesar ${formatCurrency(amount)} sedang tertunda (Pending). Silakan selesaikan pembayaran Anda sesuai instruksi Midtrans.`);
      });
    }

    function closePaymentModal() {
      document.getElementById('payment-modal').classList.add('hidden');
    }

    // Penyaluran Page View Controllers
    function openPenyaluranView() {
      // Populate fields if present
      document.getElementById('kirim-nama').value = currentUser.name;
      document.getElementById('kirim-hp').value = currentUser.phone;

      const isPatungan = userSavings.packageId === 'Sapi-Patungan';
      const labelKirim = document.getElementById('label-method-kirim');
      const labelAmbil = document.getElementById('label-method-ambil');

      if (isPatungan) {
        if (labelKirim) labelKirim.classList.add('hidden');
        if (labelAmbil) labelAmbil.classList.add('hidden');
        selectPenyaluranMethod('titip');
        const titipRadio = document.querySelector('input[name="penyaluran_method"][value="Titip Qurban"]');
        if (titipRadio) titipRadio.checked = true;
      } else {
        if (labelKirim) labelKirim.classList.remove('hidden');
        if (labelAmbil) labelAmbil.classList.remove('hidden');
      }

      switchTab('penyaluran');
    }

    function simulateGroupFilled() {
      localStorage.setItem(`groupProgress_${userSavings.id}`, 7);
      alert("Simulasi kelompok lengkap (7/7) sukses! Sistem otomatis memproses penyaluran.");
      updateUIStates();
    }

    function autoSavePatunganPenyaluran() {
      fetch('api.php?action=save_penyaluran', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          savings_id: userSavings.id,
          method: 'Titip Qurban',
          receiver: 'Kelompok Patungan Sapi',
          phone: currentUser.phone,
          address: 'Palestina (Melalui Penyalur Khusus)'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          loadDashboardDataFromServer().then(() => {
            alert("Kelompok patungan 7/7 terpenuhi! Pelaksanaan qurban otomatis dititipkan untuk disembelih di Palestina. Silakan lacak progresnya di tab 'Lacak'.");
          });
        }
      })
      .catch(err => console.error("Auto save penyaluran failed:", err));
    }

    function submitVote(locationId) {
      if (!userSavings) return;
      if (!confirm("Apakah Anda yakin ingin memilih lokasi ini? Pilihan Anda tidak dapat diubah.")) {
        return;
      }
      
      fetch('api.php?action=submit_vote', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          savings_id: userSavings.id,
          location_id: locationId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          alert(data.message);
          loadDashboardDataFromServer().then(() => {
            updateUIStates();
          });
        } else {
          alert("Gagal melakukan voting: " + data.message);
        }
      })
      .catch(err => console.error("Vote failed:", err));
    }

    function openCancelSavingsModal() {
      if (!userSavings) return;
      
      fetch(`api.php?action=get_refund_simulation&savings_id=${userSavings.id}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          document.getElementById('cancel-collected-amount').textContent = formatCurrency(data.collected_amount);
          document.getElementById('cancel-fee-percent-label').textContent = `Potongan Biaya (${data.fee_percent}%):`;
          document.getElementById('cancel-fee-amount').textContent = formatCurrency(data.fee_amount);
          document.getElementById('cancel-net-amount').textContent = formatCurrency(data.net_amount);
          
          document.getElementById('cancel-bank-account').value = '';
          document.getElementById('cancel-reason').value = '';
          
          document.getElementById('cancel-savings-modal').classList.remove('hidden');
        } else {
          alert("Gagal memuat simulasi refund: " + data.message);
        }
      })
      .catch(err => console.error("Refund simulation failed:", err));
    }

    function closeCancelSavingsModal() {
      document.getElementById('cancel-savings-modal').classList.add('hidden');
    }

    function submitCancelSavings() {
      if (!userSavings) return;
      const bankAccount = document.getElementById('cancel-bank-account').value.trim();
      const reason = document.getElementById('cancel-reason').value.trim();

      if (!bankAccount) {
        alert("Nomor rekening bank wajib diisi untuk pencairan refund.");
        return;
      }

      if (!confirm("Apakah Anda yakin ingin membatalkan rencana tabungan ini? Dana tabungan Anda akan dipotong biaya administrasi.")) {
        return;
      }

      fetch('api.php?action=request_cancellation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          savings_id: userSavings.id,
          bank_account: bankAccount,
          reason: reason
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          alert(data.message);
          closeCancelSavingsModal();
          loadDashboardDataFromServer().then(() => {
            updateUIStates();
            renderAccountSelectors();
          });
        } else {
          alert("Gagal membatalkan rencana tabungan: " + data.message);
        }
      })
      .catch(err => console.error("Cancel failed:", err));
    }

    function simulateGroupFilled() {
      if (!userSavings || !userSavings.cow_group_id) return;
      
      fetch('api.php?action=talangi_slot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          groupId: userSavings.cow_group_id,
          admin_id: 1
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          alert("Simulasi kelompok terpenuhi sukses!");
          loadDashboardDataFromServer().then(() => {
            updateUIStates();
          });
        } else {
          alert("Gagal mensimulasikan kelompok terpenuhi: " + data.message);
        }
      })
      .catch(err => console.error("Simulation failed:", err));
    }

    function selectPenyaluranMethod(method) {
      // Toggle form displays
      document.getElementById('form-penyaluran-titip').classList.add('hidden');
      document.getElementById('form-penyaluran-kirim').classList.add('hidden');
      document.getElementById('form-penyaluran-ambil').classList.add('hidden');

      // Toggle radio border accents
      document.getElementById('label-method-titip').className = "border border-outline-variant p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors";
      document.getElementById('label-method-kirim').className = "border border-outline-variant p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors";
      document.getElementById('label-method-ambil').className = "border border-outline-variant p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors";

      if (method === 'titip') {
        document.getElementById('form-penyaluran-titip').classList.remove('hidden');
        document.getElementById('label-method-titip').className = "border-2 border-primary bg-emerald-50/50 p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors";
      } else if (method === 'kirim') {
        document.getElementById('form-penyaluran-kirim').classList.remove('hidden');
        document.getElementById('label-method-kirim').className = "border-2 border-primary bg-emerald-50/50 p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors";
      } else if (method === 'ambil') {
        document.getElementById('form-penyaluran-ambil').classList.remove('hidden');
        document.getElementById('label-method-ambil').className = "border-2 border-primary bg-emerald-50/50 p-4 rounded-xl flex items-start gap-3 cursor-pointer select-none transition-colors";
      }
    }

    document.getElementById('penyaluran-form').addEventListener('submit', (e) => {
      e.preventDefault();

      const methods = document.getElementsByName('penyaluran_method');
      let methodVal = 'Titip Qurban';
      for (const m of methods) {
        if (m.checked) {
          methodVal = m.value;
          break;
        }
      }

      let address = 'Palestina (Melalui Penyalur)';
      let receiver = currentUser.name;
      let phone = currentUser.phone;

      if (methodVal === 'Titip Qurban') {
        address = document.getElementById('titip-wilayah').value;
      } else if (methodVal === 'Dikirim') {
        receiver = document.getElementById('kirim-nama').value.trim();
        phone = document.getElementById('kirim-hp').value.trim();
        address = document.getElementById('kirim-alamat').value.trim();
        if (!receiver || !address) {
          alert("Silakan lengkapi nama penerima dan alamat pengiriman!");
          return;
        }
      } else if (methodVal === 'Ambil Langsung') {
        address = 'Malang Highland Farm - Pos Pengambilan';
      }

      // Show loading
      const submitBtn = e.target.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : "Simpan Pilihan Penyaluran";
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Menyimpan...';
      }

      // Save Penyaluran to MySQL
      fetch('api.php?action=save_penyaluran', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          savings_id: userSavings.id,
          method: methodVal,
          receiver: receiver,
          phone: phone,
          address: address
        })
      })
      .then(response => response.json())
      .then(data => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }

        if (data.status === 'success') {
          loadDashboardDataFromServer().then(() => {
            alert("Metode penyaluran berhasil disimpan! Anda dapat melacak progres qurban di tab 'Lacak'.");
            switchTab('tracking');
          });
        } else {
          alert("Gagal menyimpan metode penyaluran: " + data.message);
        }
      })
      .catch(err => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }
        console.error(err);
        alert("Gagal menghubungi server untuk menyimpan metode penyaluran.");
      });
    });

    // Certificate Sub-view and Dynamic Print Controller
    function openCertificateView(savingId) {
      let targetSaving = userSavings;
      if (savingId) {
        targetSaving = allUserSavings.find(s => s.id === savingId) || userSavings;
      }
      
      // Populates certificate text fields
      document.getElementById('cert-user-name').textContent = currentUser.name;
      document.getElementById('cert-package').textContent = targetSaving.packageName;
      document.getElementById('cert-location').textContent = targetSaving.penyaluran ? targetSaving.penyaluran.address : "Daerah Pelosok";
      
      // Seed consistent cert number from saving ID or generate random
      let certNo = targetSaving.certNumber;
      if (!certNo) {
        certNo = "#TQ-2026-" + Math.floor(1000 + Math.random() * 9000);
        targetSaving.certNumber = certNo; // Save to persist
        
        // Save to DB in background
        fetch('api.php?action=save_certificate', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            savings_id: targetSaving.id,
            cert_number: certNo
          })
        })
        .catch(err => console.error("Gagal menyimpan sertifikat ke database:", err));
      }
      document.getElementById('cert-number').textContent = certNo;

      // Generate dynamic QR Code for verify.php
      const verifyBaseUrl = window.location.origin + window.location.pathname.replace('dashboard.html', '') + 'verify.php';
      const verifyUrl = verifyBaseUrl + '?cert=' + encodeURIComponent(certNo);
      document.getElementById('cert-qr-img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(verifyUrl);

      switchTab('certificate');
    }

    function downloadCertificatePDF() {
      // Triggers browser standard print dialog with stylesheet optimized for cert dimensions
      window.print();
    }

    function showPendingPaymentDetails(txId) {
      const tx = userSavings.history.find(t => t.id == txId);
      if (!tx || tx.status !== "Pending") return;

      let method = 'E-Wallet';
      const rawMethod = tx.paymentMethod || tx.type || '';
      if (rawMethod.includes('bank_transfer') || rawMethod.includes('Virtual Account')) {
        method = 'Virtual Account';
      }

      // Populate pending modal details
      document.getElementById('pending-modal-order-id').textContent = tx.orderId || tx.order_id || 'N/A';
      document.getElementById('pending-modal-amount').textContent = formatCurrency(tx.amount);
      document.getElementById('pending-modal-method').textContent = method;
      document.getElementById('pending-modal-datetime').textContent = `${tx.date} • ${tx.time}`;

      // Reset button click handlers
      const checkBtn = document.getElementById('pending-modal-check-btn');
      const payBtn = document.getElementById('pending-modal-pay-btn');
      const bypassBtn = document.getElementById('pending-modal-bypass-btn');

      checkBtn.onclick = () => checkPendingTxStatus(txId);
      payBtn.onclick = () => payPendingTx(txId, method);
      bypassBtn.onclick = () => bypassPendingTx(txId);

      // Show modal
      document.getElementById('pending-tx-modal').classList.remove('hidden');
    }

    function closePendingTxModal() {
      document.getElementById('pending-tx-modal').classList.add('hidden');
    }

    function bypassPendingTx(txId) {
      const bypassBtn = document.getElementById('pending-modal-bypass-btn');
      if (!bypassBtn) return;
      const originalText = bypassBtn.innerHTML;
      bypassBtn.disabled = true;
      bypassBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[14px] mr-1 align-middle">sync</span> Memproses...';

      fetch('api.php?action=approve_transaction', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          txId: txId
        })
      })
      .then(response => response.json())
      .then(data => {
        bypassBtn.disabled = false;
        bypassBtn.innerHTML = originalText;

        if (data.status === 'success') {
          closePendingTxModal();
          loadDashboardDataFromServer().then(() => {
            alert("Bypass Sukses Berhasil! Status transaksi diperbarui menjadi Sukses secara lokal.");
          });
        } else {
          alert("Gagal memproses bypass: " + data.message);
        }
      })
      .catch(err => {
        bypassBtn.disabled = false;
        bypassBtn.innerHTML = originalText;
        console.error("Bypass failed:", err);
        alert("Gagal menghubungi server untuk bypass.");
      });
    }

    function checkPendingTxStatus(txId) {
      const checkBtn = document.getElementById('pending-modal-check-btn');
      const originalText = checkBtn.innerHTML;
      checkBtn.disabled = true;
      checkBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px] mr-1.5 align-middle">sync</span> Memeriksa...';

      fetch('api.php?action=check_midtrans_status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          txId: txId
        })
      })
      .then(response => response.json())
      .then(data => {
        checkBtn.disabled = false;
        checkBtn.innerHTML = originalText;

        if (data.status === 'success') {
          if (data.payment_status === 'Success') {
            closePendingTxModal();
            loadDashboardDataFromServer().then(() => {
              alert("Pembayaran terverifikasi! Status transaksi berhasil diperbarui menjadi Sukses.");
            });
          } else if (data.payment_status === 'Pending') {
            alert("Transaksi ini masih dalam status tertunda (Pending) di Midtrans. Silakan lakukan pembayaran terlebih dahulu.");
          } else {
            closePendingTxModal();
            loadDashboardDataFromServer().then(() => {
              alert(`Status transaksi: ${data.payment_status}.`);
            });
          }
        } else {
          alert("Gagal memverifikasi status: " + data.message);
        }
      })
      .catch(err => {
        checkBtn.disabled = false;
        checkBtn.innerHTML = originalText;
        console.error("Check status failed:", err);
        alert("Gagal menghubungi server untuk memverifikasi status.");
      });
    }

    function payPendingTx(txId, method) {
      const tx = userSavings.history.find(t => t.id == txId);
      if (!tx) return;

      const payBtn = document.getElementById('pending-modal-pay-btn');
      const originalText = payBtn.innerHTML;
      payBtn.disabled = true;
      payBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-[16px] mr-1.5 align-middle">sync</span> Menghubungkan...';

      fetch('get-snap-token.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          amount: tx.amount,
          email: currentUser.email,
          name: currentUser.name,
          savings_id: userSavings.id,
          payment_method: method
        })
      })
      .then(response => {
        if (!response.ok) throw new Error("HTTP error status " + response.status);
        return response.json();
      })
      .then(data => {
        payBtn.disabled = false;
        payBtn.innerHTML = originalText;
        closePendingTxModal();

        if (data.token) {
          window.snap.pay(data.token, {
            onSuccess: function(result) {
              handleMidtransPaymentSuccess(tx.amount, result.payment_type || 'Midtrans', result.order_id || tx.orderId);
            },
            onPending: function(result) {
              loadDashboardDataFromServer().then(() => {
                alert("Pembayaran Anda masih dalam status tertunda.");
              });
            },
            onError: function(result) {
              alert("Pembayaran gagal.");
            }
          });
        } else {
          throw new Error(data.message || "Token Snap kosong.");
        }
      })
      .catch(error => {
        payBtn.disabled = false;
        payBtn.innerHTML = originalText;
        console.warn("Gagal mendapatkan Snap Token:", error);
        alert("Gagal menghubungi server Midtrans. Pastikan server PHP berjalan.");
      });
    }

    // Helper functions for date calculations
    function formatCurrency(num) {
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    }

    function getCurrentDateString() {
      const today = new Date();
      const options = { day: 'numeric', month: 'long', year: 'numeric' };
      return today.toLocaleDateString('id-ID', options);
    }

    function getCurrentTimeString() {
      const today = new Date();
      return today.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + " WIB";
    }

    function toggleNotification() {
      const modal = document.getElementById('notification-modal');
      modal.classList.toggle('hidden');

      // Populate list with timeline history notices if any qurban penyaluran is active
      const list = document.getElementById('notification-list');
      list.innerHTML = '';

      if (userSavings.penyaluran) {
        list.innerHTML = `
          <div class="p-2.5 border-b border-outline-variant/30 flex gap-2.5 items-start">
            <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
            <div>
              <p class="text-xs font-bold text-on-surface">Progres Qurban Anda</p>
              <p class="text-[10px] text-on-surface-variant leading-normal mt-0.5">Hewan qurban Anda saat ini berada pada tahap: <strong>${userSavings.penyaluran.status}</strong></p>
            </div>
          </div>
          <div class="p-2.5 flex gap-2.5 items-start">
            <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
            <div>
              <p class="text-xs font-bold text-on-surface">Pembayaran Berhasil</p>
              <p class="text-[10px] text-on-surface-variant leading-normal mt-0.5">Seluruh setoran terverifikasi instan melalui sistem syariah otomatis.</p>
            </div>
          </div>
        `;
      } else {
        list.innerHTML = '<div class="text-xs text-on-surface-variant py-4 text-center">Tidak ada notifikasi baru.</div>';
      }
    }

    function logout() {
      document.getElementById('logout-confirm-modal').classList.remove('hidden');
    }

    function closeLogoutModal() {
      document.getElementById('logout-confirm-modal').classList.add('hidden');
    }

    // Process actual logout redirection
    function confirmLogoutSubmit() {
      localStorage.removeItem('currentUser');
      window.location.href = '/';
    }

    // Custom alert modal handlers
    function showAlert(message, type = 'success', title = 'Pemberitahuan') {
      const modal = document.getElementById('custom-alert-modal');
      const titleEl = document.getElementById('custom-alert-title');
      const messageEl = document.getElementById('custom-alert-message');
      const iconContainer = document.getElementById('custom-alert-icon-container');
      const buttonEl = document.getElementById('custom-alert-btn');

      if (!modal) return;

      titleEl.textContent = title;
      messageEl.innerHTML = message;

      iconContainer.innerHTML = '';
      const iconSpan = document.createElement('span');
      iconSpan.className = 'material-symbols-outlined text-4xl';
      
      if (type === 'success') {
        iconContainer.className = 'w-16 h-16 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2';
        iconSpan.textContent = 'check_circle';
        iconSpan.style.fontVariationSettings = "'FILL' 1";
        buttonEl.className = 'w-full bg-primary text-on-primary font-bold py-3 rounded-xl transition-all text-xs hover:bg-primary/90 active:scale-95 shadow-sm';
      } else if (type === 'error') {
        iconContainer.className = 'w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-2';
        iconSpan.textContent = 'error';
        iconSpan.style.fontVariationSettings = "'FILL' 1";
        buttonEl.className = 'w-full bg-red-600 text-white font-bold py-3 rounded-xl transition-all text-xs hover:bg-red-700 active:scale-95 shadow-sm shadow-red-200';
      } else {
        iconContainer.className = 'w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-2';
        iconSpan.textContent = 'info';
        iconSpan.style.fontVariationSettings = "'FILL' 1";
        buttonEl.className = 'w-full bg-amber-600 text-white font-bold py-3 rounded-xl transition-all text-xs hover:bg-amber-700 active:scale-95 shadow-sm shadow-amber-200';
      }

      iconContainer.appendChild(iconSpan);
      modal.classList.remove('hidden');
    }

    function closeAlertModal() {
      const modal = document.getElementById('custom-alert-modal');
      if (modal) modal.classList.add('hidden');
    }

    // Override the native browser alert() globally with custom theme dialog
    window.alert = function(message) {
      let type = 'info';
      let title = 'Informasi';
      const lowercaseMsg = (message || '').toLowerCase();

      if (lowercaseMsg.includes('berhasil') || lowercaseMsg.includes('sukses') || lowercaseMsg.includes('disetujui') || lowercaseMsg.includes('selesai')) {
        type = 'success';
        title = 'Berhasil';
      } else if (lowercaseMsg.includes('gagal') || lowercaseMsg.includes('kesalahan') || lowercaseMsg.includes('melebihi') || lowercaseMsg.includes('minimal') || lowercaseMsg.includes('error') || lowercaseMsg.includes('salah')) {
        type = 'error';
        title = 'Kesalahan';
      } else if (lowercaseMsg.includes('demo:')) {
        type = 'info';
        title = 'Fitur Demo';
        message = message.replace(/^demo:\s*/i, '');
      }

      showAlert(message, type, title);
    };
  </script>
</body>
</html>
