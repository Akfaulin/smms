# Sistem Manajemen Sosial Media (SMMS)
> Dokumentasi teknis lengkap — arsitektur, alur kerja, database, API, dan panduan pengembang.
> Dibuat sebagai referensi internal tim pengembang dan pemangku kepentingan proyek.

---

## Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Teknologi yang Digunakan](#2-teknologi-yang-digunakan)
3. [Struktur Direktori](#3-struktur-direktori)
4. [Aktor & Role Pengguna](#4-aktor--role-pengguna)
5. [Skema Database](#5-skema-database)
6. [State Machine Approval Konten](#6-state-machine-approval-konten)
7. [Permission Matrix](#7-permission-matrix)
8. [Daftar Modul & Fitur](#8-daftar-modul--fitur)
9. [Arsitektur Routing & Controller](#9-arsitektur-routing--controller)
10. [Sistem Notifikasi](#10-sistem-notifikasi)
11. [Integrasi Kecerdasan Buatan (AI)](#11-integrasi-kecerdasan-buatan-ai)
12. [Asset Library & Brand Kit](#12-asset-library--brand-kit)
13. [Kalender Tayang Medsos](#13-kalender-tayang-medsos)
14. [Bank Tren AI](#14-bank-tren-ai)
15. [Laporan & Analitik](#15-laporan--analitik)
16. [Panduan Instalasi & Setup](#16-panduan-instalasi--setup)
17. [Panduan Seeder (Data Awal)](#17-panduan-seeder-data-awal)
18. [Konvensi Kode](#18-konvensi-kode)
19. [Rencana Pengembangan Lanjutan](#19-rencana-pengembangan-lanjutan)

---

## 1. Gambaran Umum Sistem

**SMMS (Sistem Manajemen Sosial Media)** adalah platform manajemen konten internal yang dirancang untuk satu tim sosial media. Sistem ini membantu mengoordinasikan seluruh siklus produksi konten — mulai dari pengajuan ide, proses desain, approval berjenjang, hingga publikasi di berbagai platform media sosial.

### Prinsip Utama

- **Satu tim, satu sistem** — tidak ada konsep multi-klien atau multi-tenant.
- **Approval berbasis state machine** — setiap perpindahan status konten dikontrol ketat berdasarkan role pengguna.
- **AI sebagai asisten** — seluruh output AI tetap melewati proses approval manusia, bukan pengganti keputusan.
- **Audit trail penuh** — setiap perubahan status konten tercatat lengkap dengan pelaku, waktu, dan catatan.

### Alur Bisnis Ringkas

```
Creative Team / Content Creator ajukan ide
    -> Manager acc ide
    -> Content Creator / Designer kerjakan desain
    -> Manager review & acc final
    -> Admin Medsos upload & publish
```

---

## 2. Teknologi yang Digunakan

| Lapisan | Teknologi |
|---|---|
| Backend Framework | CodeIgniter 4 (PHP 8.2+) |
| Database | MySQL / MariaDB |
| Frontend Templating | PHP View dengan layout inheritance |
| Styling | Vanilla CSS (modular per halaman) |
| Grafik & Chart | Chart.js (CDN) |
| Ikon | SVG inline (tanpa library ikon eksternal) |
| Font | DM Sans (Google Fonts) |
| AI / LLM | Google Gemini API (via AiService) |
| Server Lokal | Laragon (Apache + PHP + MySQL) |
| Dependency Manager | Composer |

---

## 3. Struktur Direktori

```
sistem-manajemen-sosmed/
+-- app/
¦   +-- Config/
¦   +-- Controllers/
¦   ¦   +-- ApprovalManager.php
¦   ¦   +-- AssetLibrary.php
¦   ¦   +-- Auth.php
¦   ¦   +-- ContentPlan.php
¦   ¦   +-- Dashboard.php
¦   ¦   +-- IdeKonten.php
¦   ¦   +-- JadwalUpload.php
¦   ¦   +-- KalenderTayang.php
¦   ¦   +-- Laporan.php
¦   ¦   +-- MasterData.php
¦   ¦   +-- Notifications.php
¦   ¦   +-- Profile.php
¦   ¦   +-- TrendAi.php
¦   ¦   +-- TugasCreator.php
¦   ¦   +-- UserManagement.php
¦   +-- Database/
¦   ¦   +-- Migrations/     # 15 file migrasi
¦   ¦   +-- Seeds/          # Data awal
¦   +-- Filters/
¦   +-- Models/
¦   ¦   +-- AiGenerationLogModel.php
¦   ¦   +-- BuktiUploadModel.php
¦   ¦   +-- ContentPlanModel.php
¦   ¦   +-- ContentStatusLogModel.php
¦   ¦   +-- NotificationModel.php
¦   ¦   +-- UserModel.php
¦   +-- Services/
¦   ¦   +-- AiService.php
¦   ¦   +-- NotificationService.php
¦   ¦   +-- TransisiKonten.php
¦   +-- Views/
¦       +-- approval_manager/
¦       +-- asset_library/
¦       +-- content_plan/
¦       +-- dashboard/
¦       +-- ide_konten/
¦       +-- jadwal_upload/
¦       +-- kalender_tayang/
¦       +-- laporan/
¦       +-- master/
¦       +-- trend_ai/
¦       +-- tugas_creator/
¦       +-- layout.php
+-- public/
¦   +-- css/
¦   ¦   +-- app.css
¦   ¦   +-- content-plan.css
¦   ¦   +-- dashboard.css
¦   ¦   +-- ide-konten.css
¦   ¦   +-- kalender-tayang.css
¦   ¦   +-- laporan.css
¦   ¦   +-- login.css
¦   ¦   +-- master-data.css
¦   ¦   +-- profile.css
¦   +-- js/
+-- SISTEM_SMMS.md
+-- README.md
+-- rancangan-sistem-smm.md
```

---

## 4. Aktor & Role Pengguna

| Role | Kode | Tanggung Jawab |
|---|---|---|
| **Superadmin** | `superadmin` | Akses teknis penuh — kelola user, role, master data. |
| **Owner** | `owner` | Pimpinan tim — akses penuh seluruh konten & user. |
| **Manager** | `manager` | Approval ide, review desain, acc final, minta revisi. |
| **Creative Team** | `creative_team` | Ajukan ide konten, gunakan AI tools. |
| **Content Creator** | `content_creator` | Kerjakan desain, submit hasil, resubmit setelah revisi. |
| **Admin Medsos** | `admin_medsos` | Upload/publish konten acc final, catat bukti upload. |

> Satu user memiliki satu role tetap yang disimpan di kolom `role_id` pada tabel `users`.
> Role bersifat fixed dan diisi via seeder — tidak ada CRUD role dari UI.

---

## 5. Skema Database

### 5.1 `roles`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| kode_role | VARCHAR(30) | superadmin, owner, manager, creative_team, content_creator, admin_medsos |
| nama_role | VARCHAR(50) | Label tampilan |

### 5.2 `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| nama | VARCHAR(100) | Nama lengkap |
| email | VARCHAR(100) | Unique, untuk login |
| password | VARCHAR(255) | Bcrypt hash |
| role_id | INT, FK -> roles.id | Role tetap user |
| status | ENUM('aktif','nonaktif') | Nonaktifkan tanpa hapus akun |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### 5.3 `platforms`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| nama_platform | VARCHAR(50) | Instagram, TikTok, Facebook, dll. |
| status | ENUM('aktif','nonaktif') | |

### 5.4 `jenis_konten`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| nama_jenis | VARCHAR(50) | Reels, Carousel, Story, Feed, dll. |
| keterangan | TEXT | nullable |

### 5.5 `content_types` (Content Pillar)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| nama_type | VARCHAR(50) | Edukasi, Promosi, Hiburan, dll. |

### 5.6 `content_plan` (Tabel Utama)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| judul_konten | VARCHAR(200) | |
| deskripsi | TEXT | nullable |
| caption | TEXT | nullable, dapat di-generate AI |
| tanggal_publish | DATE | |
| jenis_konten_id | INT, FK | nullable |
| content_type_id | INT, FK | nullable |
| status | VARCHAR(30) | Lihat state machine §6 |
| dibuat_oleh | INT, FK -> users.id | Pengusul ide |
| assigned_designer | INT, FK -> users.id | nullable |
| assigned_uploader | INT, FK -> users.id | nullable |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### 5.7 `content_platforms` (Pivot Konten-Platform)
| Kolom | Tipe |
|---|---|
| id | INT, PK, AI |
| content_id | INT, FK -> content_plan.id |
| platform_id | INT, FK -> platforms.id |

### 5.8 `content_assets`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| content_id | INT, FK | |
| platform_id | INT, FK | nullable |
| asset_nama | VARCHAR(150) | nullable |
| asset_link | VARCHAR(255) | URL Google Drive / Figma |
| keterangan | TEXT | nullable |

### 5.9 `content_status_log` (Audit Trail)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| content_id | INT, FK | |
| status_lama | VARCHAR(30) | nullable (null = status pertama) |
| status_baru | VARCHAR(30) | |
| user_id | INT, FK | nullable (null = sistem/AI) |
| catatan | TEXT | Wajib diisi saat revisi atau ditolak |
| created_at | DATETIME | |

### 5.10 `bukti_upload`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| content_id | INT, FK | |
| platform_id | INT, FK | |
| link_postingan | VARCHAR(255) | URL postingan live |
| uploaded_by | INT, FK -> users.id | |
| uploaded_at | DATETIME | |

### 5.11 `notifications`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| user_id | INT, FK -> users.id | Penerima |
| judul | VARCHAR(150) | |
| pesan | TEXT | |
| url | VARCHAR(255) | Link tujuan (opsional) |
| is_read | TINYINT(1) | 0=belum, 1=sudah dibaca |
| created_at | DATETIME | |

### 5.12 `ai_generation_log`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| content_id | INT, FK | nullable |
| user_id | INT, FK | Siapa yang memicu |
| fitur | VARCHAR(30) | idea_gen, caption_gen, prereview, dll. |
| prompt_input | TEXT | |
| output | TEXT | |
| created_at | DATETIME | |

### 5.13 `trend_bank`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| judul | VARCHAR(150) | Nama format tren |
| badge | VARCHAR(50) | Label kategori |
| desk | TEXT | Deskripsi format |
| example | TEXT | Contoh penggunaan |
| category | VARCHAR(50) | TikTok & Reels, Tutorial, dll. |
| status | ENUM('aktif','nonaktif') | |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### 5.14 `brand_assets`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, AI | |
| nama | VARCHAR(150) | Nama aset |
| kategori | VARCHAR(50) | warna, logo, foto, audio, dokumen |
| deskripsi | TEXT | nullable |
| url | VARCHAR(255) | Link unduhan / referensi |
| warna_hex | VARCHAR(10) | nullable, khusus aset warna |
| created_at | DATETIME | |
| updated_at | DATETIME | |

---

## 6. State Machine Approval Konten

### 6.1 Daftar Status

| Status | Arti |
|---|---|
| `ide_diajukan` | Ide baru diajukan Creative Team / Content Creator |
| `acc_ide` | Manager menyetujui ide, siap masuk desain |
| `in_design` | Sedang dikerjakan Content Creator |
| `review_design` | Hasil desain disubmit, menunggu review Manager |
| `revisi` | Ditolak sementara, perlu perbaikan |
| `acc_final` | Manager acc hasil akhir, siap diupload |
| `published` | Sudah diupload Admin Medsos ke platform |
| `ditolak` | Ide ditolak permanen |

### 6.2 Diagram Alur

```
[START]
   |
   v
ide_diajukan ----[Manager acc]----> acc_ide ----[Creator mulai]----> in_design
   |                                                                      |
   |--[Manager revisi]---> revisi <--[Manager revisi]---- review_design <-+
   |                          |                                 |
   |--[Manager tolak]---> ditolak                       [Manager acc]
                         [END]                                  |
                                                           acc_final
                                                                |
                                                     [Admin upload] -> published
                                                                           |
                                                                         [END]
```

### 6.3 Tabel Transisi

| Dari | Ke | Role yang Boleh | Catatan Wajib? |
|---|---|---|---|
| `ide_diajukan` | `acc_ide` | manager, owner, superadmin | Tidak |
| `ide_diajukan` | `revisi` | manager, owner, superadmin | **Ya** |
| `ide_diajukan` | `ditolak` | manager, owner, superadmin | **Ya** |
| `revisi` | `ide_diajukan` | content_creator, creative_team | Tidak |
| `acc_ide` | `in_design` | content_creator, creative_team | Tidak |
| `in_design` | `review_design` | content_creator, creative_team | Tidak |
| `review_design` | `acc_final` | manager, owner, superadmin | Tidak |
| `review_design` | `revisi` | manager, owner, superadmin | **Ya** |
| `acc_final` | `published` | admin_medsos, owner, superadmin | Tidak |

> Semua perpindahan status melewati `TransisiKonten` service yang memvalidasi:
> (1) validitas transisi, (2) izin role, (3) kelengkapan catatan wajib.
> Setiap transisi otomatis mencatat ke `content_status_log` dan memicu notifikasi.

### 6.4 Override oleh Owner & Superadmin

Owner dan superadmin dapat mengoverride semua transisi untuk kondisi darurat.
Setiap override tetap dicatat di `content_status_log` dengan catatan alasan.

---

## 7. Permission Matrix

| Aksi | superadmin | owner | manager | creative_team | content_creator | admin_medsos |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| Kelola akun user & role | Ya | - | - | - | - | - |
| Kelola master data | Ya | Ya | - | - | - | - |
| Ajukan ide konten baru | Ya | Ya | Ya | Ya | Ya | - |
| Edit konten (sebelum acc_final) | Ya | Ya | Ya | miliknya | miliknya | - |
| Approval ide | Ya | Ya | Ya | - | - | - |
| Kerjakan desain | Ya | Ya | - | Ya | Ya | - |
| Review & acc final | Ya | Ya | Ya | - | - | - |
| Upload & publish | Ya | Ya | - | - | - | Ya |
| Lihat semua konten | Ya | Ya | Ya | Ya | Ya | Ya |
| Hapus konten | Ya | Ya | Ya | - | - | - |
| Akses Bank Tren AI | Ya | Ya | Ya | Ya | Ya | - |
| Akses Asset Library | Ya | Ya | Ya | Ya | Ya | Ya |
| Akses Kalender Tayang | Ya | Ya | Ya | - | - | Ya |
| Akses Laporan | Ya | Ya | Ya | - | - | - |

---

## 8. Daftar Modul & Fitur

### 8.1 Dashboard
- Kartu statistik: konten aktif, published bulan ini, perlu revisi, ditolak.
- Antrean tugas dipersonalisasi per role.
- Alert konten mendekati tanggal publish (<= 3 hari).
- Distribusi status konten (progress bar visual).
- Statistik platform teratas.
- Grafik tren konten baru 7 hari terakhir (Chart.js).

### 8.2 Content Plan
- Tampilan Kanban (per kolom status) dan tampilan tabel.
- Filter status, platform, jenis konten, content pillar, kata kunci.
- Form tambah & edit konten.
- Modal detail konten (tab Info + tab Riwayat Status/timeline).
- Transisi status konten dengan validasi role-based.
- Generate caption & ide konten via AI.

### 8.3 Ide Konten
- Form pengajuan ide baru yang disederhanakan untuk tim kreatif.
- Daftar ide yang diajukan pengguna yang sedang login.

### 8.4 Tugas Creator
- Daftar konten yang ditugaskan ke content creator yang login.
- Status tugas aktif: acc_ide, in_design, revisi.

### 8.5 Approval Manager
- Antarmuka khusus manager untuk konten menunggu approval.
- Tindakan cepat: acc, revisi, atau tolak + catatan.

### 8.6 Jadwal Upload
- Daftar konten berstatus acc_final siap diupload.
- Input link postingan per platform + konfirmasi published.

### 8.7 Bank Tren AI (/dashboard/trend-ai)
- Instant Viral Hook Generator AI — generate kalimat pembuka berdasarkan topik & platform.
- Format Tren Audio & Visual Populer — kartu format tren yang dapat digunakan sebagai ide konten.
- Kalender Momen Promo & Event — tabel momen promo bulanan untuk perencanaan konten.

### 8.8 Asset Library & Brand Kit (/dashboard/asset-library)
- Brand Color Palette — palet warna brand yang dapat ditambah dan dihapus secara dinamis.
- Logo & Visual Assets — koleksi aset logo dan visual brand.
- Foto & Media Library — perpustakaan foto dan video tim.
- Audio & Music Library — koleksi audio/musik untuk konten video.
- Dokumen & Template — panduan brand dan template kerja.

### 8.9 Kalender Tayang Medsos (/dashboard/kalender-tayang)
- Grid kalender bulanan visual dengan navigasi bulan.
- Pill badge per hari dengan kode warna status konten.
- Panduan waktu posting terbaik per platform.
- Klik pill badge membuka modal detail konten + riwayat status.

### 8.10 Laporan (/dashboard/laporan)
- Statistik produksi konten per periode.
- Performa per platform, jenis konten, dan content pillar.
- Visualisasi grafik dan tabel.

### 8.11 Master Data (/dashboard/master)
- Kelola Platform, Jenis Konten, Content Pillar.
- Hanya dapat diakses oleh superadmin dan owner.

### 8.12 Manajemen User (/dashboard/user-management)
- Tambah, edit, nonaktifkan akun pengguna.
- Hanya dapat diakses oleh superadmin.

### 8.13 Profil (/dashboard/profile)
- Update informasi profil, ganti password.

### 8.14 Notifikasi
- Bell notifikasi di navbar dengan badge hitungan belum dibaca.
- Dropdown notifikasi terbaru.
- Halaman semua notifikasi (/dashboard/notifications).
- Auto-mark as read saat notifikasi diklik.

---

## 9. Arsitektur Routing & Controller

### Route Utama

```
GET    /                                     -> Auth::index
GET    /login                                -> Auth::loginForm
POST   /login                                -> Auth::loginProses
GET    /logout                               -> Auth::logout

GET    /dashboard                            -> Dashboard::index
GET    /dashboard/content-plan               -> ContentPlan::index
POST   /dashboard/content-plan/store         -> ContentPlan::store
POST   /dashboard/content-plan/update/{id}   -> ContentPlan::update
POST   /dashboard/content-plan/transition/{id} -> ContentPlan::transition
GET    /dashboard/content-plan/{id}/log      -> ContentPlan::log
DELETE /dashboard/content-plan/delete/{id}   -> ContentPlan::delete

GET    /dashboard/ide-konten                 -> IdeKonten::index
POST   /dashboard/ide-konten/store           -> IdeKonten::store
GET    /dashboard/tugas-creator              -> TugasCreator::index
GET    /dashboard/approval-manager           -> ApprovalManager::index
GET    /dashboard/jadwal-upload              -> JadwalUpload::index
POST   /dashboard/jadwal-upload/publish/{id} -> JadwalUpload::publish

GET    /dashboard/trend-ai                   -> TrendAi::index
POST   /dashboard/trend-ai/hook              -> TrendAi::generateHook

GET    /dashboard/asset-library              -> AssetLibrary::index
POST   /dashboard/asset-library/store        -> AssetLibrary::store
DELETE /dashboard/asset-library/delete/{id}  -> AssetLibrary::delete

GET    /dashboard/kalender-tayang            -> KalenderTayang::index
GET    /dashboard/laporan                    -> Laporan::index
GET    /dashboard/master                     -> MasterData::index
GET    /dashboard/user-management            -> UserManagement::index
GET    /dashboard/profile                    -> Profile::index
GET    /dashboard/notifications              -> Notifications::index
```

### Filter Autentikasi

Semua route `/dashboard/*` dilindungi filter yang memeriksa:
1. Sesi login valid (session('user_id') ada).
2. Status akun pengguna adalah aktif.
3. Role pengguna sesuai izin akses halaman.

---

## 10. Sistem Notifikasi

Dikelola oleh `NotificationService` dan dipicu otomatis setiap ada transisi status konten.

### Pemetaan Notifikasi per Transisi

| Transisi Status | Penerima |
|---|---|
| ide_diajukan (baru) | Semua Manager |
| ide_diajukan (resubmit) | Semua Manager |
| acc_ide | Pembuat konten, Designer ditugaskan |
| revisi | Pembuat konten, Designer ditugaskan |
| ditolak | Pembuat konten |
| in_design | Semua Manager |
| review_design | Semua Manager |
| acc_final | Uploader ditugaskan / semua Admin Medsos, Pembuat konten |
| published | Pembuat konten, Semua Owner |

---

## 11. Integrasi Kecerdasan Buatan (AI)

Semua fitur AI menggunakan Google Gemini API melalui `AiService`.

### Fitur AI yang Tersedia

| Fitur | Keterangan |
|---|---|
| Caption Generator | Draft caption + hashtag berdasarkan judul, platform, tone |
| Idea Generator | 3-5 ide konten berdasarkan pillar, jenis, kata kunci |
| AI Pre-Review Checklist | Otomatis saat in_design -> review_design, cek ejaan & aset |
| Viral Hook Generator | 5 variasi kalimat pembuka viral berdasarkan topik & platform |

Setiap eksekusi AI dicatat di `ai_generation_log` untuk audit dan tracking biaya API.

---

## 12. Asset Library & Brand Kit

### Kategori Aset

| Kategori | Deskripsi | Field Khusus |
|---|---|---|
| warna | Palet warna brand | warna_hex (kode HEX) |
| logo | File logo berbagai format | - |
| foto | Foto dan visual media | - |
| audio | Musik latar / sound effect | - |
| dokumen | Brief, panduan brand, template | - |

Fitur: tambah aset baru tanpa menghapus yang lama, hapus individual,
preview blok warna dari warna_hex, klik untuk salin kode HEX.

---

## 13. Kalender Tayang Medsos

### Fitur
- Grid 7 kolom (Senin-Minggu) responsif tanpa horizontal scroll.
- Navigasi bulan sebelumnya/berikutnya.
- Highlight tanggal hari ini.
- Pill badge konten dengan kode warna per status.
- Modal detail konten + riwayat status saat klik.
- Panduan jam posting terbaik per platform.

### Waktu Posting Terbaik

| Platform | Jam Optimal | Catatan |
|---|---|---|
| Instagram | 07.00-09.00 & 18.00-21.00 | Engagement tertinggi pagi & sore |
| TikTok | 19.00-23.00 | Peak hours malam hari |
| Facebook | 09.00-13.00 | Aktivitas pengguna dewasa jam kerja |
| LinkedIn | 08.00-10.00 (Selasa-Kamis) | Profesional aktif awal pekan |
| YouTube | 12.00-15.00 & 17.00-21.00 | Jam istirahat & santai |

---

## 14. Bank Tren AI

### Format Tren Default

| Format | Badge | Platform |
|---|---|---|
| POV & Problem Relatable | Highly Viral | TikTok & Reels |
| Stop Scrolling Hook | High Retention | Short Video |
| Before vs After Transformation | Visual Proof | Reels & Carousel |
| 3 Kesalahan Fatal | Edukasi | Edukasi & Tips |
| Quick Tutorial Under 15s | High Completion | Tutorial |
| Behind The Scenes (BTS) | Build Trust | Branding |

### Alur Penggunaan
1. Pilih format tren yang relevan.
2. Klik "Gunakan Jadi Ide Konten" — topik & contoh terisi otomatis di Hook Generator.
3. Generate hook AI dan kembangkan menjadi ide konten baru.

---

## 15. Laporan & Analitik

Data yang tersedia:
- Total konten per status dalam periode tertentu.
- Jumlah konten published per bulan.
- Breakdown per platform, per jenis konten, per content pillar.
- Rata-rata waktu produksi (ide_diajukan -> published).
- Frekuensi revisi per konten.

---

## 16. Panduan Instalasi & Setup

### Prasyarat
- PHP 8.2 atau lebih baru
- MySQL 5.7 / MariaDB 10.4 atau lebih baru
- Composer
- Laragon (direkomendasikan)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone <url-repo> sistem-manajemen-sosmed
cd sistem-manajemen-sosmed

# 2. Install dependencies
composer install

# 3. Salin file environment
cp env .env

# 4. Edit .env
# CI_ENVIRONMENT = development
# app.baseURL = 'http://sistem-manajemen-sosmed.test/'
# database.default.hostname = localhost
# database.default.database = smms_db
# database.default.username = root
# database.default.password =
# database.default.DBDriver = MySQLi

# 5. Buat database
# CREATE DATABASE smms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Jalankan migrasi
php spark migrate

# 7. Jalankan seeder
php spark db:seed DatabaseSeeder
```

### Konfigurasi Gemini AI

Tambahkan di `.env` untuk mengaktifkan fitur AI:

```
GEMINI_API_KEY = "your-google-gemini-api-key-here"
```

Dapatkan API key gratis di: https://aistudio.google.com/app/apikey

---

## 17. Panduan Seeder (Data Awal)

```bash
# Semua seeder sekaligus
php spark db:seed DatabaseSeeder

# Per seeder
php spark db:seed RolesSeeder       # 6 role
php spark db:seed UserSeeder        # Akun per role
php spark db:seed MasterDataSeeder  # Platform, jenis konten, pillar
php spark db:seed ContentPlanSeeder # Contoh data konten
php spark db:seed TrendBankSeeder   # 6 format tren
php spark db:seed BrandAssetsSeeder # Contoh aset brand
```

### Akun Default

| Email | Password | Role |
|---|---|---|
| superadmin@smms.id | password | superadmin |
| owner@smms.id | password | owner |
| manager@smms.id | password | manager |
| creative@smms.id | password | creative_team |
| creator@smms.id | password | content_creator |
| admin@smms.id | password | admin_medsos |

> **Ganti password default setelah instalasi pertama!**

---

## 18. Konvensi Kode

### PHP / CodeIgniter 4
- Namespace: `App\Controllers`, `App\Models`, `App\Services`.
- Metode controller: `index()`, `store()`, `update()`, `delete()`, `transition()`.
- Query menggunakan Query Builder CI4.
- Validasi semua input request sebelum diproses.
- Escape semua output ke view dengan `esc()`.

### CSS
- Satu file CSS per halaman (kalender-tayang.css, laporan.css, dll.).
- Gunakan CSS custom property (`--variabel`) untuk nilai berulang.
- Prefiks kelas sesuai modul: `kt-` (kalender tayang), `cp-` (content plan), dll.

### JavaScript
- Satu file JS per halaman kompleks (content-plan.js, dll.).
- Gunakan `window.VARIABLE` untuk meneruskan data PHP ke JS.
- Vanilla JavaScript — tanpa jQuery.

---

## 19. Rencana Pengembangan Lanjutan

### Prioritas Tinggi
- [ ] Ringkasan revisi otomatis AI (bandingkan deskripsi lama vs baru).
- [ ] Export laporan ke PDF / Excel.
- [ ] Rekomendasi waktu posting optimal berbasis data historis.

### Prioritas Menengah
- [ ] Integrasi API platform (Instagram Graph API, TikTok for Business).
- [ ] Kalender mode minggu (weekly view).
- [ ] Attachment file langsung ke konten.
- [ ] Notifikasi email (selain in-app).

### Prioritas Rendah / Jangka Panjang
- [ ] Asisten tanya-jawab dashboard berbasis AI.
- [ ] Ringkasan performa konten bulanan otomatis.
- [ ] Bank ide konten terpisah.
- [ ] Mode dark theme.

---

> Dokumen ini terakhir diperbarui: **Agustus 2026**
> Versi sistem: **1.0.0**
> Dikembangkan dengan CodeIgniter 4 & Google Gemini AI
