# Prompt Pengerjaan — Sistem Social Media Management (CI4)

> Cara pakai: buka project CodeIgniter 4 kamu di Claude Code (atau AI coding
> assistant lain yang bisa akses file project), lampirkan `rancangan-sistem-smm.md`
> di root project, lalu paste prompt di bawah ini apa adanya.

---

```
Kamu membantu saya mengembangkan sistem Social Media Management berbasis
CodeIgniter 4 untuk penggunaan INTERNAL tim saya (bukan agency, tidak ada
konsep klien/multi-tenant).

KONTEKS PROJECT:
- Framework: CodeIgniter 4 (PHP), pola MVC standar CI4
- Sudah ada satu modul jadi: "Content Plan" — kalender konten dengan
  CRUD, status draft/acc/published, master data platform/jenis
  konten/content pillar, dan link asset desain per platform. File
  contoh ada di project (view + JS-nya memakai konvensi penamaan class
  CSS "cp-", fungsi fetch helper `api()`, toast notifikasi `toast()`,
  dan modal berbasis show/hide class ".cp-back.show").
- Dokumen `rancangan-sistem-smm.md` (saya lampirkan) adalah SPESIFIKASI
  RESMI untuk pengembangan selanjutnya: skema tabel baru, state machine
  approval, role & permission, dan fitur AI. Anggap dokumen ini sebagai
  sumber kebenaran (source of truth) — kalau ada bagian yang ambigu
  atau bentrok dengan kode yang sudah ada, TANYAKAN ke saya dulu,
  jangan menebak.

ATURAN KERJA — WAJIB DIIKUTI:
1. Kerjakan SATU TAHAP dari bagian "7. Urutan Pengerjaan" per sesi/per
   giliran. Jangan loncat ke tahap berikutnya sebelum saya konfirmasi
   tahap sekarang sudah beres.
2. Sebelum menulis kode, ringkas dulu rencana kamu untuk tahap itu
   (file apa saja yang akan dibuat/diubah, migration apa, dampak ke
   data lama) — biar saya bisa koreksi sebelum kode ditulis.
3. Ikuti penamaan tabel/kolom PERSIS seperti di rancangan-sistem-smm.md
   (contoh: `content_status_log`, `roles`, kolom `role_id` di `users`,
   kode role `superadmin`/`owner`/`manager`/`content_creator`/
   `admin_medsos`). Jangan improvisasi nama baru tanpa bilang ke saya.
4. Sistem ini TIDAK PAKAI konsep klien/tenant — jangan tambahkan kolom
   atau tabel `klien`/`client` apa pun, semua data bersifat satu tim.
5. Status konten TIDAK BOLEH diupdate langsung lewat form/dropdown
   bebas. Semua perubahan status harus lewat satu fungsi terpusat
   sesuai bagian 4.3 dokumen (`canTransition`) yang mengecek: transisi
   valid, role user sesuai, dan catatan wajib diisi untuk transisi
   tertentu.
6. Migration harus AMAN untuk data existing — kalau menambah kolom
   `role_id` ke tabel `users`, sertakan strategi default role untuk
   user yang sudah ada, jangan biarkan jadi NULL tanpa penjelasan.
7. Sesuaikan gaya kode dengan yang sudah ada di project (penamaan
   variabel, struktur controller, cara CI4 project ini menangani CSRF,
   response JSON, dst) — jangan perkenalkan pola/struktur baru kalau
   tidak perlu.
8. Setelah selesai satu tahap, kasih ringkasan singkat: file apa saja
   yang berubah/dibuat, dan apa yang perlu saya cek/test manual sebelum
   lanjut ke tahap berikutnya.

MULAI DARI:
Tahap 1 di bagian 7 — tabel `roles` (seeder 5 role tetap) + tambah
kolom `role_id` dan `status` ke tabel `users`, termasuk strategi
default role untuk user lama. Mulai dengan ringkasan rencana dulu,
jangan langsung generate migration.
```

---

## Catatan Pemakaian

- Kalau AI assistant-nya belum bisa baca file langsung, tempel juga isi
  `rancangan-sistem-smm.md` setelah prompt di atas.
- Prompt ini sengaja meminta ringkasan rencana dulu sebelum kode ditulis,
  supaya kamu bisa koreksi arah sebelum banyak file berubah sekaligus.
- Kalau nanti sampai ke tahap fitur AI (bagian 9), tambahkan instruksi
  spesifik model/API AI yang mau dipakai (misalnya Anthropic API,
  OpenAI, dsb) — dokumen rancangan sengaja tidak mengunci ke satu
  provider.
