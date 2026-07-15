# Panduan Deploy Projek ThankQuu di aaPanel VPS

Dokumen ini menjelaskan langkah-langkah untuk mendeploy aplikasi web fintech **ThankQuu** ke VPS yang menggunakan kontrol panel **aaPanel**.

---

## Prasyarat Server
1. aaPanel sudah terinstal di VPS Anda.
2. Web Server: **Nginx** atau **Apache** terinstal (direkomendasikan Nginx).
3. Database: **MySQL** (direkomendasikan versi 5.7 atau 8.0).
4. PHP: Versi **7.4**, **8.0**, atau **8.1** (beserta ekstensi `PDO`, `PDO_MySQL`, dan `cURL`).

---

## Langkah 1: Buat Database di aaPanel
1. Masuk ke dashboard aaPanel Anda.
2. Klik menu **Databases** di sidebar kiri.
3. Klik tombol **Add Database**.
4. Isi data database:
   - **Database name**: `thankquu` (atau nama lain pilihan Anda).
   - **Username**: (secara default disamakan dengan nama database).
   - **Password**: (generate password yang aman).
5. Klik **Submit**.
6. Simpan detail **Host (127.0.0.1)**, **Database Name**, **Username**, dan **Password** tersebut untuk konfigurasi di Langkah 4.

---

## Langkah 2: Impor Schema Database
1. Pada baris database yang baru saja dibuat di menu Databases aaPanel, klik tombol **Import** (atau masuk ke **phpMyAdmin**).
2. Unggah file `thankquu.sql` yang ada di direktori root projek.
3. Klik **Import** untuk memasukkan struktur tabel dan data default (seeds).

---

## Langkah 3: Tambahkan Website & Unggah File
1. Klik menu **Website** di sidebar kiri aaPanel.
2. Klik **Add site**.
3. Masukkan domain Anda (misal: `thankquu.com` atau subdomain `qurban.thankquu.com`).
4. Pada pilihan **PHP Version**, pilih PHP 7.4 ke atas.
5. Klik **Submit**.
6. Klik folder root website yang baru dibuat (misal: `/www/wwwroot/thankquu.com`).
7. Hapus file bawaan default (`index.html` dan `404.html` bawaan aaPanel).
8. Unggah seluruh file projek ThankQuu ke folder tersebut (Anda dapat mem-zip file di lokal terlebih dahulu, lalu mengunggahnya dan mengekstraknya lewat **aaPanel File Manager**).

---

## Langkah 4: Konfigurasi File `.env`
1. Di dalam File Manager aaPanel, cari file `.env.example` di folder root situs Anda.
2. Salin (*Copy*) file tersebut dan beri nama baru: `.env`.
3. Edit file `.env` tersebut dan masukkan kredensial database VPS Anda (dari Langkah 1):
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXX  # Masukkan Server Key Sandbox Anda
   MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXX  # Masukkan Client Key Sandbox Anda

   DB_HOST=127.0.0.1
   DB_DATABASE=nama_database_vps_anda
   DB_USERNAME=username_database_vps_anda
   DB_PASSWORD=password_database_vps_anda
   ```
4. Simpan perubahan file.

---

## Langkah 5: Keamanan Server (Penting!)
Untuk mencegah file sensitif seperti `.env` dan `thankquu.sql` diakses langsung secara publik dari browser:

### Jika Menggunakan Nginx (aaPanel Default):
1. Masuk ke menu **Website** di aaPanel.
2. Klik nama domain website Anda untuk membuka jendela **Settings**.
3. Pilih tab **Configuration** (atau **URL rewrite**).
4. Tambahkan baris rule berikut di dalam blok server utama Nginx Anda:
   ```nginx
   # Blokir akses publik ke file .env dan .sql
   location ~ /\.(env|htaccess|git) {
       deny all;
   }
   location ~ \.(sql|zip|rar)$ {
       deny all;
   }
   ```
5. Klik **Save**.

### Jika Menggunakan Apache:
- Projek ini sudah dilengkapi file `.htaccess` di folder root yang secara otomatis memblokir akses publik ke `.env` dan `thankquu.sql`.

---

## Langkah 6: Konfigurasi Webhook Midtrans (Opsional untuk Online)
Jika Anda ingin menerima notifikasi sukses otomatis dari server Midtrans (tidak perlu klik manual):
1. Masuk ke **Midtrans Dashboard** Sandbox/Production Anda.
2. Buka menu **Settings** > **Configuration**.
3. Set **Payment Notification URL** Anda ke:
   `https://domain-anda.com/midtrans-webhook.php`
4. Set **Finish Redirect URL** ke:
   `https://domain-anda.com/dashboard.html`
5. Klik **Update**.

---

## Akun Demo Default untuk Pengujian
Setelah berhasil dideploy, Anda dapat langsung login di browser menggunakan akun demo berikut:
- **Halaman Pengguna**: Buka `https://domain-anda.com/login.html`
  - **Email**: `ahmad@thankquu.com`
  - **Password**: `password`
- **Halaman Admin**: Buka `https://domain-anda.com/admin.html`
  - **Email**: `admin@thankquu.com`
  - **Password**: `admin`
