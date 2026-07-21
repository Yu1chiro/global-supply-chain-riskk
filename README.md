# Supply Chain Risk Intelligence (RiskIntel)

Aplikasi web berbasis Laravel untuk memantau risiko rantai pasok global. Aplikasi ini mengumpulkan dan menampilkan data negara, berita, cuaca, kurs mata uang, dan lalu lintas pelabuhan untuk membantu menilai tingkat risiko suatu negara atau kawasan.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Perintah Artisan Kustom](#perintah-artisan-kustom)
- [Struktur Role & Autentikasi](#struktur-role--autentikasi)
- [Panel Admin](#panel-admin)
- [Struktur Direktori Penting](#struktur-direktori-penting)
- [Catatan Pengembangan](#catatan-pengembangan)
- [Troubleshooting Umum](#troubleshooting-umum)

## Fitur Utama

- **Dashboard Risiko Negara** — ringkasan skor risiko per negara berdasarkan data ekonomi, cuaca, dan berita.
- **Daftar & Detail Negara** — informasi lengkap tiap negara termasuk data World Bank dan REST Countries.
- **Pemantauan Cuaca** — data cuaca real-time via Open-Meteo.
- **Kurs Mata Uang** — data nilai tukar mata uang antar negara.
- **News Intelligence** — agregasi berita terkait logistik, ekonomi, dan perdagangan, lengkap dengan analisis sentimen (positif/netral/negatif).
- **Pemantauan Pelabuhan (Ports)** — data lalu lintas dan kemacetan pelabuhan.
- **Perbandingan Negara (Comparison)** — membandingkan beberapa negara sekaligus berdasarkan metrik risiko.
- **Watchlist** — menyimpan daftar negara yang ingin terus dipantau oleh pengguna.
- **Login dengan Google** — autentikasi via Google OAuth (Laravel Socialite), selain login manual.
- **Lupa Password / Reset Password** — alur reset password standar Laravel (kirim link ke email, lalu set password baru).
- **Panel Admin** — pengelolaan pengguna, pelabuhan, artikel, kata kunci sentimen, dan analitik perilaku pengguna (khusus akun dengan hak admin).

## Teknologi yang Digunakan

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade Templates, Bootstrap, Tailwind CSS (via Vite)
- **Database**: MySQL
- **Autentikasi**: Sesi Laravel bawaan + Laravel Socialite (Google OAuth)
- **Build tool**: Vite
- **Sumber data eksternal**: World Bank API, REST Countries API, Open-Meteo API, GNews API, Exchange Rate API

## Persyaratan Sistem

Pastikan sudah terpasang di komputer kamu:

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- MySQL (atau database lain yang kompatibel, sesuaikan di `.env`)
- Ekstensi PHP standar Laravel (mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath)

## Instalasi

1. Clone atau extract project ke folder lokal kamu.

2. Masuk ke folder project:
   ```bash
   cd scs
   ```

3. Install dependency PHP:
   ```bash
   composer install
   ```

4. Install dependency JavaScript:
   ```bash
   npm install
   ```

5. Salin file environment:
   ```bash
   cp .env.example .env
   ```

6. Generate application key:
   ```bash
   php artisan key:generate
   ```

7. Buat database kosong di MySQL sesuai nama yang akan diisi di `.env` (default: `supply_chain_risk`).

8. Jalankan migration dan seeder:
   ```bash
   php artisan migrate --seed
   ```

9. Build asset frontend:
   ```bash
   npm run build
   ```
   Atau untuk mode pengembangan (auto reload saat ada perubahan):
   ```bash
   npm run dev
   ```

## Konfigurasi Environment

Beberapa variabel penting yang perlu diisi di file `.env`:

| Variabel | Keterangan |
|---|---|
| `APP_URL` | URL dasar aplikasi, contoh `http://localhost:8000` |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Koneksi database MySQL |
| `MAIL_MAILER` | Driver pengiriman email. Gunakan `log` untuk pengembangan lokal (email akan tercatat di `storage/logs/laravel.log`, bukan benar-benar terkirim) |
| `GNEWS_API_KEY` | API key dari GNews, dipakai untuk fitur News Intelligence |
| `EXCHANGE_RATE_API_KEY` | API key untuk data kurs mata uang |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | Kredensial untuk fitur login dengan Google |

`OPEN_METEO_API_URL` dan `WORLD_BANK_API_URL` tidak memerlukan API key karena keduanya layanan publik gratis.

Catatan tentang APP_KEY: nilai ini unik per environment dan tidak boleh dibagikan atau disamakan antar lingkungan (lokal, staging, produksi). Selalu generate ulang dengan `php artisan key:generate` di tiap environment baru.

## Menjalankan Aplikasi

Jalankan server pengembangan bawaan Laravel:

```bash
php artisan serve
```

Aplikasi bisa diakses di `http://127.0.0.1:8000`.

Kalau tampilan frontend belum termuat dengan benar (CSS/JS tidak muncul), pastikan proses `npm run dev` atau `npm run build` sudah dijalankan di terminal terpisah.

## Perintah Artisan Kustom

Selain perintah bawaan Laravel, project ini punya beberapa perintah tambahan:

| Perintah | Fungsi |
|---|---|
| `php artisan user:create` | Membuat akun login baru secara manual lewat terminal (berguna karena form registrasi publik dibatasi hanya untuk akun pertama) |
| `php artisan data:sync-all` | Sinkronisasi data World Bank, REST Countries, cuaca, dan perhitungan skor risiko untuk semua negara. Tambahkan opsi `--only-missing` untuk hanya memproses negara yang datanya masih kosong |
| `php artisan ports:seed-traffic` | Mengisi data dummy kemacetan pelabuhan untuk pelabuhan yang belum punya data log. Tambahkan opsi `--fresh` untuk menghapus data lama terlebih dahulu |

## Struktur Role & Autentikasi

Project ini menggunakan sistem login berbasis sesi bawaan Laravel dengan dua peran:

- **User biasa** — bisa mengakses dashboard, negara, cuaca, kurs, berita, pelabuhan, perbandingan, dan watchlist.
- **Admin** (`is_admin = true` di tabel `users`) — mendapat akses tambahan ke Panel Admin.

Halaman registrasi publik (`/register`) hanya aktif ketika belum ada satu pun akun terdaftar di database. Setelah akun pertama dibuat, route registrasi otomatis menolak akses (mengembalikan 404). Akun tambahan setelah itu dibuat lewat perintah `php artisan user:create`.

Login juga bisa dilakukan lewat Google (tombol "Login dengan Google"), dan tersedia alur lupa password standar (`/forgot-password` dan `/reset-password/{token}`) yang mengirim link reset lewat email sesuai konfigurasi `MAIL_MAILER`.

Seeder `UserSeeder` membuat akun admin default berikut untuk pengembangan lokal:

```
Email: admin@demo.com
Password: password
```

Ganti kredensial ini sebelum digunakan di lingkungan produksi.

## Panel Admin

Panel admin bisa diakses lewat menu "Admin" di sidebar (hanya muncul untuk akun dengan `is_admin = true`), atau langsung ke `/admin/dashboard`. Semua route di bawah `/admin/*` dilindungi middleware `admin` sehingga user biasa yang mencoba mengakses langsung lewat URL akan ditolak (403).

Menu yang tersedia di panel admin:

- **News Intelligence** (`/admin/dashboard`) — ringkasan sentimen berita dan daftar berita terbaru.
- **Manage Users** (`/admin/users`) — melihat, mengedit (nama, email, status admin, password), dan menghapus akun pengguna. Admin tidak bisa menghapus atau mencabut status admin dari akun miliknya sendiri.
- **Manage Ports** (`/admin/ports`) — mengelola data pelabuhan.
- **Manage Articles** (`/admin/articles`) — CRUD artikel berita yang ditampilkan di aplikasi.
- **Sentiment Words** (`/admin/sentiment-words`) — mengelola daftar kata kunci yang dipakai untuk analisis sentimen berita.
- **User Behavior Analytics** (`/admin/analytics/user-behavior`) — statistik perilaku pengguna: negara yang paling sering dilihat, user paling aktif, tren aktivitas harian. Akun admin dikecualikan dari perhitungan ini supaya datanya murni mencerminkan perilaku pengguna biasa.

## Struktur Direktori Penting

```
scs/
├── app/
│   ├── Console/Commands/     Perintah artisan kustom
│   ├── Http/Controllers/     Controller (Web, Auth, Admin)
│   ├── Http/Middleware/      Middleware kustom (termasuk EnsureUserIsAdmin)
│   ├── Models/                Model Eloquent
│   └── Services/              Service untuk integrasi API eksternal
├── database/
│   ├── migrations/            Skema database
│   └── seeders/                Data awal (user, negara, pelabuhan, kata sentimen)
├── resources/views/
│   ├── admin/                  Halaman panel admin
│   ├── auth/                   Halaman login, register, lupa password
│   ├── dashboard/               Halaman dashboard utama & sub-fitur
│   └── layouts/                  Layout utama aplikasi (sidebar, navbar)
└── routes/web.php                Semua route aplikasi
```

## Catatan Pengembangan

- Jangan pernah membagikan file `.env` yang sudah terisi (berisi `APP_KEY`, kredensial database, API key, dan kredensial SMTP). Gunakan `.env.example` sebagai referensi ketika membagikan project ke orang lain.
- Setelah menarik perubahan kode baru (git pull atau extract ulang), selalu jalankan:
  ```bash
  php artisan migrate
  php artisan view:clear
  php artisan route:clear
  php artisan config:clear
  ```
  supaya cache lama tidak menyebabkan perilaku aplikasi tidak sesuai dengan kode terbaru.
- Kalau ingin mengosongkan dan mengisi ulang seluruh data database dari awal:
  ```bash
  php artisan migrate:fresh --seed
  ```
  Perintah ini menghapus seluruh data yang ada, gunakan hanya di lingkungan pengembangan.

## Troubleshooting Umum

**Error `No application encryption key has been specified`**
Jalankan `php artisan key:generate` di environment tersebut.

**Error `UnexpectedResponseException` / SMTP gagal terkirim**
Cek kredensial `MAIL_USERNAME`, `MAIL_PASSWORD`, dan `MAIL_FROM_ADDRESS` di `.env`. Untuk pengembangan lokal, set `MAIL_MAILER=log` supaya email tercatat di `storage/logs/laravel.log` tanpa perlu SMTP aktif.

**Error `Route [password.request] not defined`**
Pastikan file `routes/web.php` yang dipakai adalah versi terbaru dari project ini (route lupa password ada di bagian awal grup `guest`). Jalankan `php artisan route:clear` setelah memastikan file sudah benar.

**Perubahan kode tidak muncul di browser**
Jalankan `php artisan optimize:clear` untuk membersihkan seluruh cache (config, route, view).
