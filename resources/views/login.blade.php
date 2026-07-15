<!DOCTYPE html>
<html class="light" lang="id">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
  <title>Masuk - Tabungan Qurban Digital</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
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
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col justify-center items-center px-6 py-12 relative overflow-x-hidden">
  
  <div class="absolute inset-0 islamic-pattern pointer-events-none -z-10"></div>
  <div class="absolute top-10 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
 
  <!-- Card Wrapper -->
  <div class="w-full max-w-md glass-panel rounded-3xl shadow-xl p-8 relative">
    
    <!-- Logo & Header -->
    <div class="flex flex-col items-center mb-8">
      <a href="/" class="flex items-center gap-2 mb-4">
        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-md">
          <span class="material-symbols-outlined text-white text-[24px]">payments</span>
        </div>
        <span class="font-bold text-xl text-primary tracking-tight">Tabungan Qurban</span>
      </a>
      <h2 class="text-2xl font-bold text-on-surface">Selamat Datang Kembali</h2>
      <p class="text-xs text-on-surface-variant mt-1 text-center">Silakan masuk untuk melanjutkan tabungan dan melihat progres qurban Anda.</p>
    </div>

    <!-- Error Alert Box -->
    <div id="error-box" class="hidden mb-4 p-3 bg-red-100 border border-red-300 text-red-900 rounded-lg text-sm flex items-center gap-2">
      <span class="material-symbols-outlined text-red-700 text-[18px]">error</span>
      <span id="error-message">Email atau password salah!</span>
    </div>

    <!-- Seed Information Notice for testing -->
    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-lg text-xs">
      <p class="font-semibold mb-1">💡 Akun Demo Evaluasi:</p>
      <ul class="list-disc list-inside space-y-0.5">
        <li><strong>Jamaah (User):</strong> ahmad@thankquu.com / password</li>
        <li><strong>Admin:</strong> admin@thankquu.com / admin</li>
      </ul>
    </div>

    <!-- Login Form -->
    <form id="login-form" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-semibold text-on-surface-variant mb-1">Alamat Email</label>
        <input type="email" id="email" required placeholder="nama@email.com" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-2 focus:border-primary focus:ring-0 outline-none transition-all">
      </div>

      <div>
        <div class="flex justify-between items-center mb-1">
          <label for="password" class="block text-sm font-semibold text-on-surface-variant">Kata Sandi</label>
          <a href="#" onclick="alert('Demo: Silakan gunakan email demo di atas.')" class="text-xs text-primary hover:underline">Lupa Password?</a>
        </div>
        <input type="password" id="password" required placeholder="••••••••" class="w-full bg-surface-container-low border border-outline-variant rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-2 focus:border-primary focus:ring-0 outline-none transition-all">
      </div>

      <button type="submit" class="w-full bg-primary text-on-primary font-bold py-3.5 rounded-xl shadow-md hover:bg-primary/95 transition-all flex items-center justify-center gap-2">
        Masuk
        <span class="material-symbols-outlined text-[18px]">login</span>
      </button>
    </form>

    <div class="mt-6 text-center text-xs text-on-surface-variant">
      Belum memiliki akun? <a href="register.html" class="text-primary font-semibold hover:underline">Daftar Sekarang</a>
    </div>

  </div>

  <script>
    document.getElementById('login-form').addEventListener('submit', (e) => {
      e.preventDefault();

      const email = document.getElementById('email').value.trim().toLowerCase();
      const password = document.getElementById('password').value;
      const errorBox = document.getElementById('error-box');
      const errorMessage = document.getElementById('error-message');

      // Loading state on button
      const loginBtn = e.target.querySelector('button[type="submit"]');
      const originalText = loginBtn.innerHTML;
      loginBtn.disabled = true;
      loginBtn.innerHTML = 'Memproses...';

      fetch('api.php?action=login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email, password })
      })
      .then(response => response.json())
      .then(data => {
        loginBtn.disabled = false;
        loginBtn.innerHTML = originalText;

        if (data.status === 'success') {
          localStorage.setItem('currentUser', JSON.stringify(data.user));
          if (data.user.role === 'admin') {
            window.location.href = 'admin.html';
          } else {
            window.location.href = 'dashboard.html';
          }
        } else {
          errorBox.classList.remove('hidden');
          errorMessage.textContent = data.message || 'Alamat email atau kata sandi yang Anda masukkan salah!';
        }
      })
      .catch(err => {
        loginBtn.disabled = false;
        loginBtn.innerHTML = originalText;
        console.error(err);
        errorBox.classList.remove('hidden');
        errorMessage.textContent = 'Gagal terhubung ke server. Pastikan Apache berjalan di Laragon.';
      });
    });
  </script>
</body>
</html>
