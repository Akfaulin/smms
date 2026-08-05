<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

/**
 * ContentPlanSeeder
 *
 * Mengisi data simulasi realistis untuk tabel:
 *   - users (memastikan akun untuk 5 role lengkap)
 *   - content_plan (10+ ide/konten beragam status)
 *   - content_status_log (audit trail timeline realistis)
 *   - bukti_upload (bukti publish untuk konten yang published)
 */
class ContentPlanSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Pastikan 5 Akun Tim Lengkap ─────────────────────
        $roles = $this->db->table('roles')->get()->getResultArray();
        $roleMap = [];
        foreach ($roles as $r) {
            $roleMap[$r['kode_role']] = $r['id'];
        }

        $usersSeed = [
            ['nama' => 'Superadmin',     'email' => 'admin@smm.local',   'role' => 'superadmin'],
            ['nama' => 'Budi Owner',     'email' => 'owner@smm.local',   'role' => 'owner'],
            ['nama' => 'Siti Manager',   'email' => 'manager@smm.local', 'role' => 'manager'],
            ['nama' => 'Rian Creator',   'email' => 'creator@smm.local', 'role' => 'content_creator'],
            ['nama' => 'Dewi Sosmed',    'email' => 'sosmed@smm.local',  'role' => 'admin_medsos'],
        ];

        $userMap = []; // kode_role => user_id
        foreach ($usersSeed as $u) {
            $existing = $this->db->table('users')->where('email', $u['email'])->get()->getRowArray();
            if ($existing) {
                $userMap[$u['role']] = $existing['id'];
            } else {
                $this->db->table('users')->insert([
                    'nama'       => $u['nama'],
                    'email'      => $u['email'],
                    'password'   => UserModel::hashPassword('admin123'),
                    'role_id'    => $roleMap[$u['role']] ?? 1,
                    'status'     => 'aktif',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $userMap[$u['role']] = $this->db->insertID();
            }
        }

        echo "  - ContentPlanSeeder: Akun tim untuk 5 role siap.\n";

        // ── 2. Ambil Master Data (Platform, Jenis, Pillar) ────
        $platforms = $this->db->table('platforms')->get()->getResultArray();
        $jenisList = $this->db->table('jenis_konten')->get()->getResultArray();
        $pillarList = $this->db->table('content_types')->get()->getResultArray();

        if (empty($platforms) || empty($jenisList) || empty($pillarList)) {
            echo "  - ContentPlanSeeder: Data master belum ada. Jalankan MasterDataSeeder lebih dahulu.\n";
            return;
        }

        $pInsta  = $platforms[0]['id'] ?? 1;
        $pTikTok = $platforms[1]['id'] ?? 2;
        $pX      = $platforms[3]['id'] ?? 4;
        $pYT     = $platforms[4]['id'] ?? 5;

        $jReels    = $jenisList[0]['id'] ?? 1;
        $jCarousel = $jenisList[1]['id'] ?? 2;
        $jStatic   = $jenisList[2]['id'] ?? 3;
        $jThread   = $jenisList[4]['id'] ?? 5;

        $pilEdu  = $pillarList[0]['id'] ?? 1;
        $pilProm = $pillarList[1]['id'] ?? 2;
        $pilInsp = $pillarList[2]['id'] ?? 3;
        $pilEnt  = $pillarList[3]['id'] ?? 4;

        $idAdmin   = $userMap['superadmin'] ?? 1;
        $idManager = $userMap['manager'] ?? 1;
        $idCreator = $userMap['content_creator'] ?? 1;
        $idSosmed  = $userMap['admin_medsos'] ?? 1;

        // ── 3. Data Konten Realistis ──────────────────────────
        $dummyKonten = [
            [
                'judul'        => 'Peluncuran Fitur Baru AI Assistant Q3',
                'deskripsi'    => 'Konten promosi fitur AI assistant teranyar untuk membantu tim meng-generate caption.',
                'caption'      => 'Capek bikin caption dari nol? ✨ Sekarang SMMS sudah dilengkapi AI Assistant! Buat caption menarik untuk Instagram dan TikTok dalam hitungan detik. Coba sekarang!',
                'tgl_publish'  => '2026-08-05',
                'jenis_id'     => $jCarousel,
                'pillar_id'    => $pilProm,
                'platform_id'  => $pInsta,
                'status'       => 'published',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'link_post'    => 'https://instagram.com/p/C8921_SMMS_AI',
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Pengajuan ide promo AI Assistant'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'Ide menarik, silakan lanjut desain'],
                    ['status_lama' => 'acc_ide', 'status_baru' => 'in_design', 'user' => $idCreator, 'note' => 'Mulai pengerjaan carousel 5 slide'],
                    ['status_lama' => 'in_design', 'status_baru' => 'review_design', 'user' => $idCreator, 'note' => 'Desain slide 1-5 selesai diajukan'],
                    ['status_lama' => 'review_design', 'status_baru' => 'acc_final', 'user' => $idManager, 'note' => 'Desain & caption sudah oke'],
                    ['status_lama' => 'acc_final', 'status_baru' => 'published', 'user' => $idSosmed, 'note' => 'Konten telah diposting di IG Feeds'],
                ]
            ],
            [
                'judul'        => '5 Tips Mengoptimalkan Bio Instagram Usaha',
                'deskripsi'    => 'Konten edukasi mengenai elemen penting di Bio IG agar mengkonversi pengunjung jadi pembeli.',
                'caption'      => 'Bio IG kamu masih sepi pembeli? 😱 Simak 5 elemen wajib ini:\n1. Niche Jelas\n2. Call to Action\n3. Link Utama\nSimpan postingan ini ya!',
                'tgl_publish'  => '2026-08-10',
                'jenis_id'     => $jCarousel,
                'pillar_id'    => $pilEdu,
                'platform_id'  => $pInsta,
                'status'       => 'in_design',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Ide edukasi Bio IG'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'ACC ide edukasi'],
                    ['status_lama' => 'acc_ide', 'status_baru' => 'in_design', 'user' => $idCreator, 'note' => 'Proses pembuatan visual slide'],
                ]
            ],
            [
                'judul'        => 'Promo Spesial Diskon Kemerdekaan 17 Agustus',
                'deskripsi'    => 'Visual banner promo diskon 45% khusus hari Kemerdekaan Republik Indonesia.',
                'caption'      => 'MERDEKA! 🇮🇩 Dapatkan diskon 45% untuk semua paket langganan tahunan. Gunakan kode: MERDEKA45. Berlaku hingga 17 Agustus!',
                'tgl_publish'  => '2026-08-17',
                'jenis_id'     => $jStatic,
                'pillar_id'    => $pilProm,
                'platform_id'  => $pInsta,
                'status'       => 'review_design',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Pengajuan ide promo 17-an'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'ACC promo kemerdekaan'],
                    ['status_lama' => 'acc_ide', 'status_baru' => 'in_design', 'user' => $idCreator, 'note' => 'Desain poster merah putih'],
                    ['status_lama' => 'in_design', 'status_baru' => 'review_design', 'user' => $idCreator, 'note' => 'Mohon review desain poster promo'],
                ]
            ],
            [
                'judul'        => 'Tutorial Singkat Penggunaan Fitur Content Plan',
                'deskripsi'    => 'Video reels tutorial 30 detik mengajarkan alur pengajuan ide hingga publish.',
                'caption'      => 'Biar kerjaan tim medsos makin efisien, begini cara koordinasi ide pakai SMMS! Nggak ada lagi drama lupa posting 🔥',
                'tgl_publish'  => '2026-08-12',
                'jenis_id'     => $jReels,
                'pillar_id'    => $pilEdu,
                'platform_id'  => $pTikTok,
                'status'       => 'revisi',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Ide video tutorial'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'ACC video Reels'],
                    ['status_lama' => 'acc_ide', 'status_baru' => 'in_design', 'user' => $idCreator, 'note' => 'Editing video Reels'],
                    ['status_lama' => 'in_design', 'status_baru' => 'review_design', 'user' => $idCreator, 'note' => 'Video siap ditinjau'],
                    ['status_lama' => 'review_design', 'status_baru' => 'revisi', 'user' => $idManager, 'note' => 'Tolong percepat transisi video di detik ke-15 dan perjelas teks subtitle.'],
                ]
            ],
            [
                'judul'        => 'Behind The Scene Suasana Kerja Tim Kreatif',
                'deskripsi'    => 'Foto/video santai momen brainstorming tim kreatif di kantor.',
                'caption'      => 'Di balik postingan yang aesthetic, ada tim yang heboh diskusi ide tiap pagi ☕️ Mana momen BTS favorit kalian?',
                'tgl_publish'  => '2026-08-15',
                'jenis_id'     => $jStatic,
                'pillar_id'    => $pilEnt,
                'platform_id'  => $pInsta,
                'status'       => 'acc_ide',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Ide konten BTS tim'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'Bagus untuk engagement, silakan foto/video.'],
                ]
            ],
            [
                'judul'        => 'Quotes Motivasi Produktivitas Senin Pagi',
                'deskripsi'    => 'Kata motivasi pendek untuk menyapa audiens di awal minggu.',
                'caption'      => 'Awal minggu baru, semangat baru! Fokus pada langkah kecil hari ini. Happy Monday! 💪',
                'tgl_publish'  => '2026-08-18',
                'jenis_id'     => $jStatic,
                'pillar_id'    => $pilInsp,
                'platform_id'  => $pX,
                'status'       => 'ide_diajukan',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Pengajuan ide Quotes Senin'],
                ]
            ],
            [
                'judul'        => 'Infografis Tren Social Media Marketing 2026',
                'deskripsi'    => 'Thread & Infografis mengenai pergeseran algoritma dan tren short-video.',
                'caption'      => 'Tren konten apa yang paling mendominasi di 2026? Geser slide untuk pelajari data lengkapnya! 📈',
                'tgl_publish'  => '2026-08-20',
                'jenis_id'     => $jCarousel,
                'pillar_id'    => $pilEdu,
                'platform_id'  => $pInsta,
                'status'       => 'acc_final',
                'pembuat'      => $idManager,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idManager, 'note' => 'Inisiasi ide riset tren 2026'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'ACC otomatis oleh Manager'],
                    ['status_lama' => 'acc_ide', 'status_baru' => 'in_design', 'user' => $idCreator, 'note' => 'Penyusunan grafik & layout slide'],
                    ['status_lama' => 'in_design', 'status_baru' => 'review_design', 'user' => $idCreator, 'note' => 'Slide infografis lengkap siap ditinjau'],
                    ['status_lama' => 'review_design', 'status_baru' => 'acc_final', 'user' => $idManager, 'note' => 'Sangat bagus dan akurat. Tinggal scheduling.'],
                ]
            ],
            [
                'judul'        => 'Video Reels Giveaway Perayaan 10K Follower',
                'deskripsi'    => 'Konten giveaway dengan hadiah saldo e-wallet untuk followers aktif.',
                'caption'      => 'GIVEAWAY TIME! 🎉 Terima kasih 10.000 followers! Mau dapat saldo Gopay 500rb? Tulis harapanmu di kolom komentar dan tag 3 temen kamu!',
                'tgl_publish'  => '2026-08-01',
                'jenis_id'     => $jReels,
                'pillar_id'    => $pilEnt,
                'platform_id'  => $pTikTok,
                'status'       => 'published',
                'pembuat'      => $idCreator,
                'designer'     => $idCreator,
                'uploader'     => $idSosmed,
                'link_post'    => 'https://tiktok.com/@smms_official/video/739120349',
                'logs'         => [
                    ['status_lama' => null, 'status_baru' => 'ide_diajukan', 'user' => $idCreator, 'note' => 'Ide Giveaway 10k follower'],
                    ['status_lama' => 'ide_diajukan', 'status_baru' => 'acc_ide', 'user' => $idManager, 'note' => 'ACC giveaway'],
                    ['status_lama' => 'acc_ide', 'status_baru' => 'in_design', 'user' => $idCreator, 'note' => 'Pengerjaan video reels'],
                    ['status_lama' => 'in_design', 'status_baru' => 'review_design', 'user' => $idCreator, 'note' => 'Review video giveaway'],
                    ['status_lama' => 'review_design', 'status_baru' => 'acc_final', 'user' => $idManager, 'note' => 'ACC Final'],
                    ['status_lama' => 'acc_final', 'status_baru' => 'published', 'user' => $idSosmed, 'note' => 'Telah diunggah di TikTok official'],
                ]
            ],
        ];

        // ── 4. Masukkan Data ke Database ──────────────────────
        foreach ($dummyKonten as $data) {
            // Cek apakah judul sudah ada
            $exists = $this->db->table('content_plan')
                ->where('judul_konten', $data['judul'])
                ->get()
                ->getRowArray();

            if ($exists) {
                $contentId = $exists['id'];
            } else {
                $this->db->table('content_plan')->insert([
                    'judul_konten'      => $data['judul'],
                    'deskripsi'         => $data['deskripsi'],
                    'caption'           => $data['caption'],
                    'tanggal_publish'   => $data['tgl_publish'],
                    'jenis_konten_id'   => $data['jenis_id'],
                    'content_type_id'   => $data['pillar_id'],
                    'status'            => $data['status'],
                    'dibuat_oleh'       => $data['pembuat'],
                    'assigned_designer' => $data['designer'],
                    'assigned_uploader' => $data['uploader'],
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]);
                $contentId = $this->db->insertID();
            }

            // Masukkan status logs
            if (isset($data['logs']) && is_array($data['logs'])) {
                foreach ($data['logs'] as $log) {
                    $logExists = $this->db->table('content_status_log')
                        ->where('content_id', $contentId)
                        ->where('status_baru', $log['status_baru'])
                        ->countAllResults();

                    if (! $logExists) {
                        $this->db->table('content_status_log')->insert([
                            'content_id'  => $contentId,
                            'status_lama' => $log['status_lama'],
                            'status_baru' => $log['status_baru'],
                            'user_id'     => $log['user'],
                            'catatan'     => $log['note'],
                            'created_at'  => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            // Jika status published dan ada link_post, masukkan ke bukti_upload
            if ($data['status'] === 'published' && !empty($data['link_post'])) {
                $buktiExists = $this->db->table('bukti_upload')
                    ->where('content_id', $contentId)
                    ->countAllResults();

                if (! $buktiExists) {
                    $this->db->table('bukti_upload')->insert([
                        'content_id'     => $contentId,
                        'uploaded_by'    => $data['uploader'],
                        'link_postingan' => $data['link_post'],
                        'uploaded_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        echo "  - ContentPlanSeeder: " . count($dummyKonten) . " data konten simulasi realistis berhasil diisi lengkap dengan audit log & bukti upload!\n";
    }
}
