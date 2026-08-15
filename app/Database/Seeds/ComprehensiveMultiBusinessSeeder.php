<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ComprehensiveMultiBusinessSeeder
 *
 * Mengisi data komprehensif, realistis, dan terisolasi penuh untuk 4 bisnis:
 * 1. SMMS Digital Agency (Jasa Manajemen Media Sosial & Agensi Kreatif)
 * 2. Toko Kopi Nusantara (F&B / Kuliner Kopi Kekinian & Roastery)
 * 3. GlowSkin Beauty (Produk Skincare & Kosmetik Kecantikan)
 * 4. FitLife Apparel (Pakaian Olahraga, Running & Gym Activewear)
 */
class ComprehensiveMultiBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        echo "🚀 Memulai Comprehensive Multi-Business Seeding...\n";

        // ── 1. Master 4 Bisnis ─────────────────────────────────────
        $bisnisList = [
            [
                'id'          => 1,
                'nama_bisnis' => 'SMMS Digital Agency',
                'deskripsi'   => 'Layanan konsultan & manajemen media sosial profesional untuk brand nasional.',
                'warna'       => '#6C5CE7',
                'urutan'      => 1,
                'status'      => 'aktif',
            ],
            [
                'id'          => 2,
                'nama_bisnis' => 'Toko Kopi Nusantara',
                'deskripsi'   => 'Kedai kopi kekinian & suplier biji kopi pilihan dari petani lokal seluruh Indonesia.',
                'warna'       => '#00B894',
                'urutan'      => 2,
                'status'      => 'aktif',
            ],
            [
                'id'          => 3,
                'nama_bisnis' => 'GlowSkin Beauty',
                'deskripsi'   => 'Brand produk perawatan kulit & kecantikan alami aman teruji BPOM.',
                'warna'       => '#E17055',
                'urutan'      => 3,
                'status'      => 'aktif',
            ],
            [
                'id'          => 4,
                'nama_bisnis' => 'FitLife Apparel',
                'deskripsi'   => 'Pakaian olahraga & gym wear performa tinggi dengan bahan breathable ultralight.',
                'warna'       => '#0984E3',
                'urutan'      => 4,
                'status'      => 'aktif',
            ],
        ];

        foreach ($bisnisList as $b) {
            $exists = $db->table('bisnis')->where('id', $b['id'])->get()->getRowArray();
            if ($exists) {
                $db->table('bisnis')->where('id', $b['id'])->update([
                    'nama_bisnis' => $b['nama_bisnis'],
                    'deskripsi'   => $b['deskripsi'],
                    'warna'       => $b['warna'],
                    'status'      => $b['status'],
                    'urutan'      => $b['urutan'],
                ]);
            } else {
                $db->table('bisnis')->insert($b);
            }
        }
        echo "  ✓ 4 Bisnis terdaftar & aktif.\n";

        // ── 2. Master Platforms, Jenis & Pillars per Bisnis ────────
        $defaultPlatforms = [
            ['nama_platform' => 'Instagram',   'status' => 'aktif'],
            ['nama_platform' => 'TikTok',      'status' => 'aktif'],
            ['nama_platform' => 'Facebook',    'status' => 'aktif'],
            ['nama_platform' => 'YouTube',     'status' => 'aktif'],
            ['nama_platform' => 'LinkedIn',    'status' => 'aktif'],
        ];

        $defaultJenis = [
            ['nama_jenis' => 'Reels / Video Pendek', 'keterangan' => 'Video vertikal 9:16 durasi 15-60 detik'],
            ['nama_jenis' => 'Carousel 1:1',         'keterangan' => 'Multi slide edukasi atau katalog produk'],
            ['nama_jenis' => 'Single Post / Feeds',   'keterangan' => 'Desain visual tunggal estetis'],
            ['nama_jenis' => 'Story Interaktif',     'keterangan' => 'Polling, QnA, dan kuis 24 jam'],
            ['nama_jenis' => 'Article / Thread',     'keterangan' => 'Caption panjang informatif'],
        ];

        $defaultPillars = [
            ['nama_type' => 'Brand Awareness'],
            ['nama_type' => 'Edukasi & Tips'],
            ['nama_type' => 'Promosi & Penjualan'],
            ['nama_type' => 'Behind The Scene (BTS)'],
            ['nama_type' => 'Testimoni & Social Proof'],
            ['nama_type' => 'Hiburan & Relatable POV'],
        ];

        foreach ($bisnisList as $b) {
            $bId = $b['id'];
            foreach ($defaultPlatforms as $p) {
                $ex = $db->table('platforms')->where('bisnis_id', $bId)->where('nama_platform', $p['nama_platform'])->countAllResults();
                if (!$ex) $db->table('platforms')->insert(['bisnis_id' => $bId, 'nama_platform' => $p['nama_platform'], 'status' => $p['status']]);
            }
            foreach ($defaultJenis as $j) {
                $ex = $db->table('jenis_konten')->where('bisnis_id', $bId)->where('nama_jenis', $j['nama_jenis'])->countAllResults();
                if (!$ex) $db->table('jenis_konten')->insert(['bisnis_id' => $bId, 'nama_jenis' => $j['nama_jenis'], 'keterangan' => $j['keterangan']]);
            }
            foreach ($defaultPillars as $cp) {
                $ex = $db->table('content_types')->where('bisnis_id', $bId)->where('nama_type', $cp['nama_type'])->countAllResults();
                if (!$ex) $db->table('content_types')->insert(['bisnis_id' => $bId, 'nama_type' => $cp['nama_type']]);
            }
        }
        echo "  ✓ Master data terisolasi per bisnis siap.\n";

        // User references
        $userCreator = $db->table('users')->where('email', 'creator@smm.local')->get()->getRowArray()['id'] ?? 3;
        $userManager = $db->table('users')->where('email', 'manager@smm.local')->get()->getRowArray()['id'] ?? 2;
        $userSosmed  = $db->table('users')->where('email', 'sosmed@smm.local')->get()->getRowArray()['id'] ?? 4;
        $userOwner   = $db->table('users')->where('email', 'owner@smm.local')->get()->getRowArray()['id'] ?? 1;

        // ── 3. Data Konten Komprehensif (8 Status Lengkap per Bisnis) ──
        $dataSemuaBisnis = [
            // ==========================================
            // BISNIS 1: SMMS DIGITAL AGENCY
            // ==========================================
            1 => [
                [
                    'judul'        => 'Peluncuran Fitur AI Assistant Q3 SMMS',
                    'deskripsi'    => 'Konten pengenalan fitur AI caption generator otomatis di aplikasi SMMS.',
                    'caption'      => 'Capek bikin caption dari nol? ✨ Sekarang SMMS sudah dilengkapi AI Assistant! Buat caption menarik untuk Instagram dan TikTok dalam hitungan detik. Coba gratis sekarang! #SocialMediaTools #AICaption #DigitalMarketing',
                    'tgl_publish'  => date('Y-m-d 09:00:00', strtotime('-3 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/smms-ai-q3',
                ],
                [
                    'judul'        => '5 Tips Naikkan Engagement Instagram Reels 2026',
                    'deskripsi'    => 'Infografis carousel 5 slide tentang hook visual, transisi cepat, dan sound viral.',
                    'caption'      => 'Mau Reels kamu tembus FYP dan dapet ribuan likes? Simak 5 rahasia struktur video pendek yang terbukti efektif meningkatkan engagement rate hingga 3x lipat! 🚀 Simpan postingan ini ya!',
                    'tgl_publish'  => date('Y-m-d 11:30:00', strtotime('+1 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/smms-reels-tips',
                ],
                [
                    'judul'        => 'Review Kualitas Feed Klien: Sebelum vs Sesudah Optimasi',
                    'deskripsi'    => 'Carousel showcase transformasi grid Instagram klien dari acak-acakan menjadi harmonis estetis.',
                    'caption'      => 'Feed rapi = Branding profesional! ✨ Lihat perbedaan dramatis akun klien kami setelah ditangani tim spesialis desain SMMS selama 30 hari.',
                    'tgl_publish'  => date('Y-m-d 14:00:00', strtotime('+2 days')),
                    'status'       => 'review_design',
                    'design_url'   => 'https://canva.com/design/smms-before-after',
                ],
                [
                    'judul'        => 'Behind The Scene: Suasana Kerja Tim Creative SMMS',
                    'deskripsi'    => 'Video santai vlog kantor memperlihatkan sesi brainstorming tim visual & copywriter.',
                    'caption'      => 'Intip serunya balik layar tim kreatif SMMS saat brainstorming ide kampanye klien! Siapa di sini yang tim coffee lover saat ngerjain desain? ☕ Tag temen kerjamu!',
                    'tgl_publish'  => date('Y-m-d 16:30:00', strtotime('+3 days')),
                    'status'       => 'in_design',
                    'design_url'   => 'https://canva.com/design/smms-bts-vlog',
                ],
                [
                    'judul'        => 'Infografis: Jam Posting Terbaik Medsos 2026',
                    'deskripsi'    => 'Jadwal waktu posting puncak berdasarkan analisa lebih dari 50.000 postingan aktif.',
                    'caption'      => 'Kapan waktu terbaik posting di IG & TikTok? Kami rangkum data jam tayang terbukti dengan reach tertinggi di 2026. Jangan sampai salah jadwal ya!',
                    'tgl_publish'  => date('Y-m-d 19:00:00', strtotime('+4 days')),
                    'status'       => 'revisi',
                    'catatan'      => 'Tolong ganti skema warna grafik agar lebih kontras dan tambahkan logo SMMS di pojok kanan bawah.',
                    'design_url'   => 'https://canva.com/design/smms-infografis-jam',
                ],
                [
                    'judul'        => 'Studi Kasus: Menaikkan Followers Klien 300% dalam 90 Hari',
                    'deskripsi'    => 'Postingan testimoni & hasil analisa analitik akun klien industri retail.',
                    'caption'      => 'Hasil nyata strategi konten berbasis data! Dalam 90 hari, jangkauan organik akun klien kami melonjak naik 300%. Konsultasikan sosial mediamu bersama kami!',
                    'tgl_publish'  => date('Y-m-d 10:00:00', strtotime('+5 days')),
                    'status'       => 'acc_ide',
                ],
                [
                    'judul'        => 'Webinar Gratis: Strategi Konten FYP TikTok untuk Pemula',
                    'deskripsi'    => 'Poster pengumuman live webinar online bersama founder SMMS.',
                    'caption'      => 'Yuk belajar langsung cara bangun personal branding dan raih 10K follower pertama di TikTok. Daftar gratis via link di bio sekarang!',
                    'tgl_publish'  => date('Y-m-d 13:00:00', strtotime('+7 days')),
                    'status'       => 'ide_diajukan',
                ],
                [
                    'judul'        => 'Meme Hari Senin: Desainer vs Revisi Tanpa Henti',
                    'deskripsi'    => 'Meme humor seputar revisi teks di menit-menit terakhir sebelum jam tayang.',
                    'caption'      => 'Yang desainer pasti nangis di pojokan relate banget 😂 Tag teman kerjamu yang sering minta revisi!',
                    'tgl_publish'  => date('Y-m-d 18:00:00', strtotime('+8 days')),
                    'status'       => 'ditolak',
                    'catatan'      => 'Tone humor kurang cocok dengan citra agensi B2B profesional. Harap ganti dengan konsep edukasi yang lebih elegan.',
                ],
            ],

            // ==========================================
            // BISNIS 2: TOKO KOPI NUSANTARA
            // ==========================================
            2 => [
                [
                    'judul'        => 'Promo Kopi Susu Gula Aren Buy 1 Get 1 Spesial Gajian',
                    'deskripsi'    => 'Promo besar akhir bulan untuk varian signature Kopi Susu Gula Aren.',
                    'caption'      => 'PROMO BUY 1 GET 1 KOPI SUSU GULA AREN! ☕ Sapa teman kantor atau sahabat kamu buat ngopi bareng sore ini. Berlaku di seluruh gerai Toko Kopi Nusantara! #KopiNusantara #KopiSusu #PromoKopi',
                    'tgl_publish'  => date('Y-m-d 08:30:00', strtotime('-2 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/kopi-promo-payday',
                ],
                [
                    'judul'        => 'Edukasi Biji Kopi: Arabika Gayo vs Robusta Lampung',
                    'deskripsi'    => 'Carousel perbandingan cita rasa, kadar kafein, keasaman, dan metode seduh terbaik.',
                    'caption'      => 'Kamu tim Arabika yang asam segar beraroma floral, atau tim Robusta yang pahit mantap dan bold? Yuk pelajari bedanya di slide ini biar gak salah pilih roasted bean! ☕✨',
                    'tgl_publish'  => date('Y-m-d 10:00:00', strtotime('+1 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/kopi-arabika-robusta',
                ],
                [
                    'judul'        => 'Resep Cold Brew Kopi Susu Creamy di Rumah',
                    'deskripsi'    => 'Video reels tutorial menyeduh cold brew ramah lambung dengan alat sederhana.',
                    'caption'      => 'Bikin Cold Brew ala cafe di rumah cuma butuh 3 bahan! Simpan video ini untuk panduan weekend brewing kamu. 🥛☕',
                    'tgl_publish'  => date('Y-m-d 14:30:00', strtotime('+2 days')),
                    'status'       => 'review_design',
                    'design_url'   => 'https://canva.com/design/kopi-coldbrew-recipe',
                ],
                [
                    'judul'        => 'Suasana Santai Co-Working Space Kopi Nusantara',
                    'deskripsi'    => 'Foto estetis area indoor ber-AC & outdoor rimbun dengan colokan lengkap & Wi-Fi kencang.',
                    'caption'      => 'Nugas atau WFH makin fokus kalau dapet tempat nyaman + aroma kopi yang menenangkan. Mampir yuk ke cabang terdekat!',
                    'tgl_publish'  => date('Y-m-d 16:00:00', strtotime('+3 days')),
                    'status'       => 'in_design',
                    'design_url'   => 'https://canva.com/design/kopi-coworking-vibe',
                ],
                [
                    'judul'        => 'Menu Baru: Matcha Espresso Fusion & Pandan Latte',
                    'deskripsi'    => 'Poster peluncuran 2 varian minuman fusion kolaborasi matcha Jepang & kopi lokal.',
                    'caption'      => 'Dua rasa terbaik menyatu dalam satu tegukan! Cicipi kesegaran Matcha Espresso & harumnya Pandan Latte mulai hari ini.',
                    'tgl_publish'  => date('Y-m-d 11:00:00', strtotime('+4 days')),
                    'status'       => 'revisi',
                    'catatan'      => 'Warna hijau matcha terlalu pucat, tolong tingkatkan saturasi agar terlihat lebih segar dan menggugah selera.',
                    'design_url'   => 'https://canva.com/design/kopi-new-menu',
                ],
                [
                    'judul'        => 'Mengenal Petani Biji Kopi Toraja Dibalik Secangkir Kopimu',
                    'deskripsi'    => 'Cerita human interest proses panen biji kopi langsung dari dataran tinggi Toraja.',
                    'caption'      => 'Setiap tetes kopi yang kamu nikmati berawal dari ketulusan tangan para petani lokal di Toraja. Dukung kopi asli Indonesia bersama kami!',
                    'tgl_publish'  => date('Y-m-d 09:30:00', strtotime('+5 days')),
                    'status'       => 'acc_ide',
                ],
                [
                    'judul'        => 'Kuis Tebak Biji Kopi Berhadiah Voucher Ngopi Rp 500 Ribu',
                    'deskripsi'    => 'Konten interaktif tebak aroma dan bentuk biji kopi lewat kolom komentar.',
                    'caption'      => 'Buktikan kalau kamu pecinta kopi sejati! Tebak asal biji kopi di gambar ini dan menangkan voucher ngopi gratis.',
                    'tgl_publish'  => date('Y-m-d 17:00:00', strtotime('+6 days')),
                    'status'       => 'ide_diajukan',
                ],
                [
                    'judul'        => 'Diskon Kopi Gratis untuk Pelanggan Bernama Agus',
                    'deskripsi'    => 'Promo musiman khusus kemerdekaan.',
                    'caption'      => 'Khusus kamu yang bernama Agus, datang dan tunjukkan KTP untuk dapatkan kopi gratis!',
                    'tgl_publish'  => date('Y-m-d 12:00:00', strtotime('+9 days')),
                    'status'       => 'ditolak',
                    'catatan'      => 'Promo serupa sudah lewat momentumnya. Ganti dengan promo bundling akhir pekan.',
                ],
            ],

            // ==========================================
            // BISNIS 3: GLOWSKIN BEAUTY
            // ==========================================
            3 => [
                [
                    'judul'        => 'Sunscreen 101: Kenapa Wajib Reapply Tiap 2 Jam?',
                    'deskripsi'    => 'Edukasi perlindungan sinar UV A & UV B untuk mencegah flek hitam & penuaan dini.',
                    'caption'      => 'Pake sunscreen pagi aja belum cukup lho! ☀️ Sinar matahari tetap bisa merusak kulit kalau tidak di-reapply tiap 2 jam. Pakai GlowSkin Air-Touch Sunscreen yang ringan dan bebas lengket! #GlowSkinBeauty #SunscreenViral #SkincareRoutine',
                    'tgl_publish'  => date('Y-m-d 09:00:00', strtotime('-1 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/glow-sunscreen-101',
                ],
                [
                    'judul'        => 'Rangkaian Morning Skincare Routine untuk Kulit Glowing',
                    'deskripsi'    => 'Urutan simpel 4 tahap: Cleanser, Toner, Serum Niacinamide, dan Barrier Moisturizer.',
                    'caption'      => 'Bangun tidur dengan kulit kenyal & glowing bukan impian lagi! ✨ Ikuti 4 langkah simpel morning skincare routine bersama GlowSkin Beauty ini setiap hari.',
                    'tgl_publish'  => date('Y-m-d 10:30:00', strtotime('+1 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1608248597461-8f9f8c6b758b?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/glow-morning-routine',
                ],
                [
                    'judul'        => 'Review Jujur Serum Niacinamide 10%: Before After 14 Hari',
                    'deskripsi'    => 'Testimoni nyata pelanggan dengan foto perbandingan flek hitam yang memudar.',
                    'caption'      => 'Lihat perubahan tekstur kulit dalam 14 hari pemakaian rutin GlowSkin Serum Niacinamide 10%! Flek hitam memudar dan skin barrier lebih sehat. 💖',
                    'tgl_publish'  => date('Y-m-d 13:00:00', strtotime('+2 days')),
                    'status'       => 'review_design',
                    'design_url'   => 'https://canva.com/design/glow-before-after',
                ],
                [
                    'judul'        => 'Kandungan Skincare yang Gak Boleh Dipakai Barengan',
                    'deskripsi'    => 'Panduan keamanan mencampur Retinol, AHA/BHA, Vitamin C, dan Niacinamide.',
                    'caption'      => 'Awas kulit iritasi dan breakout! Jangan pernah campur bahan-bahan skincare ini dalam waktu bersamaan ya. Simpan panduan ini!',
                    'tgl_publish'  => date('Y-m-d 15:30:00', strtotime('+3 days')),
                    'status'       => 'in_design',
                    'design_url'   => 'https://canva.com/design/glow-skincare-mix',
                ],
                [
                    'judul'        => 'Promo Gajian: Glowing Kit Bundling Diskon 35%',
                    'deskripsi'    => 'Paket hemat komplit pembersih wajah, serum pencerah, dan pelembab ceramide.',
                    'caption'      => 'PAYDAY SALE IS HERE! 🛍️ Dapatkan paket bundling Glowing Skin Kit dengan diskon 35% + gratis headband kecantikan eksklusif!',
                    'tgl_publish'  => date('Y-m-d 19:00:00', strtotime('+4 days')),
                    'status'       => 'revisi',
                    'catatan'      => 'Font harga diskon kurang menonjol, tolong dibuat lebih besar dengan aksen warna peach terang.',
                    'design_url'   => 'https://canva.com/design/glow-payday-bundle',
                ],
                [
                    'judul'        => 'Mitos vs Fakta: Kulit Berminyak Gak Perlu Moisturizer?',
                    'deskripsi'    => 'Edukasi bahwa dehidrasi kulit justru memicu produksi minyak berlebih.',
                    'caption'      => 'Mitos terbesar di dunia skincare! Kulit berminyak tetap butuh pelembab berbahan gel ringan. Yuk simak faktanya!',
                    'tgl_publish'  => date('Y-m-d 11:00:00', strtotime('+6 days')),
                    'status'       => 'acc_ide',
                ],
                [
                    'judul'        => 'Behind The Lab: Proses Uji Klinis & Keamanan BPOM Produk',
                    'deskripsi'    => 'Video dokumenter suasana laboratorium formulasi ramah kulit sensitif.',
                    'caption'      => 'Kualitas & keamanan adalah prioritas kami. Intip bagaimana setiap tetes produk GlowSkin diformulasikan oleh dermatologis terpercaya.',
                    'tgl_publish'  => date('Y-m-d 16:00:00', strtotime('+7 days')),
                    'status'       => 'ide_diajukan',
                ],
                [
                    'judul'        => 'Tips Menghilangkan Jerawat dalam 1 Malam dengan Pasta Gigi',
                    'deskripsi'    => 'Konten hack kecantikan rumahan.',
                    'caption'      => 'Coba oles pasta gigi di jerawat sebelum tidur...',
                    'tgl_publish'  => date('Y-m-d 14:00:00', strtotime('+9 days')),
                    'status'       => 'ditolak',
                    'catatan'      => 'Menyesatkan secara medis dan berbahaya bagi skin barrier. Brand GlowSkin harus selalu berbasis fakta sains dermatologi.',
                ],
            ],

            // ==========================================
            // BISNIS 4: FITLIFE APPAREL
            // ==========================================
            4 => [
                [
                    'judul'        => 'Peluncuran Koleksi FitLife Jersey Running Ultralight Q3',
                    'deskripsi'    => 'Foto produk jersey lari dengan teknologi bahan daya serap keringat tinggi.',
                    'caption'      => 'LIGHTER. FASTER. STRONGER. 🔥 Memperkenalkan FitLife Ultralight Running Jersey! Bobot hanya 85 gram dengan sirkulasi udara maksimal untuk performa lari terbaikmu. #FitLifeApparel #RunningJersey #GymWear',
                    'tgl_publish'  => date('Y-m-d 07:30:00', strtotime('-2 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/fitlife-running-jersey',
                ],
                [
                    'judul'        => '5 Gerakan Stretching Wajib Sebelum & Sesudah Maraton',
                    'deskripsi'    => 'Video reels tutorial peregangan otot kaki untuk cegah kram saat lari jarak jauh.',
                    'caption'      => 'Jangan asal lari kalau gak mau cedera otot! 🏃‍♂️ Lakukan 5 gerakan peregangan wajib ini selama 5 menit sebelum kamu mulai jarak jauh. Simpan video ini!',
                    'tgl_publish'  => date('Y-m-d 08:00:00', strtotime('+1 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/fitlife-stretching-guide',
                ],
                [
                    'judul'        => 'Uji Ketahanan Bahan Quick-Dry FitLife vs Kaos Katun Biasa',
                    'deskripsi'    => 'Video demonstrasi uji coba daya serap & kecepatan kering saat intensitas keringat tinggi.',
                    'caption'      => 'Kenapa kaos biasa bikin gatal pas keringatan di gym? Beda bahan, beda kenyamanan! Tonton uji coba daya kering FitLife Quick-Dry Fabric di video ini. 🏋️‍♂️',
                    'tgl_publish'  => date('Y-m-d 13:30:00', strtotime('+2 days')),
                    'status'       => 'review_design',
                    'design_url'   => 'https://canva.com/design/fitlife-fabric-test',
                ],
                [
                    'judul'        => 'Inspirasi Gym Outfit Pria & Wanita: Simple tapi Stylish',
                    'deskripsi'    => 'Foto kompilasi mix & match celana compression, sports bra, dan hoodie olahraga.',
                    'caption'      => 'Outfit keren bikin mood workout naik 100%! Cek inspirasi padu padan warna outfit gym minggu ini. Mana gaya favoritmu? 🔥',
                    'tgl_publish'  => date('Y-m-d 16:00:00', strtotime('+3 days')),
                    'status'       => 'in_design',
                    'design_url'   => 'https://canva.com/design/fitlife-outfit-inspo',
                ],
                [
                    'judul'        => 'Payday Promo Gymwear: Buy 2 Get 1 Free Shorts',
                    'deskripsi'    => 'Promo gajian celana pendek olahraga serbaguna untuk workout & santai.',
                    'caption'      => 'PAYDAY OUTFIT CHECK! 💥 Dapatkan 1 celana olahraga gratis setiap pembelian 2 pcs FitLife Activewear. Persediaan terbatas!',
                    'tgl_publish'  => date('Y-m-d 18:30:00', strtotime('+4 days')),
                    'status'       => 'revisi',
                    'catatan'      => 'Tolong cantumkan syarat & ketentuan promo di slide terakhir dan ukuran size chart.',
                    'design_url'   => 'https://canva.com/design/fitlife-payday-shorts',
                ],
                [
                    'judul'        => 'Panduan Memilih Sepatu Lari Sesuai Bentuk Telapak Kaki',
                    'deskripsi'    => 'Carousel edukasi flat foot, neutral, dan high arch untuk mencegah cedera ankle.',
                    'caption'      => 'Sering sakit tumit sehabis lari? Bisa jadi sepatu kamu tidak cocok dengan tipe telapak kaki! Geser slide untuk cek tipe kakimu.',
                    'tgl_publish'  => date('Y-m-d 10:00:00', strtotime('+5 days')),
                    'status'       => 'acc_ide',
                ],
                [
                    'judul'        => 'Tantangan 30 Hari Push-Up & Plank: Hadiah Jersey Gratis',
                    'deskripsi'    => 'Event komunitas olahraga tantangan harian di Instagram Story.',
                    'caption'      => 'Tantang dirimu selama 30 hari ke depan! Ikuti FitLife Fitness Challenge dan tag akun kami tiap hari untuk menangkan hadiah merchandise.',
                    'tgl_publish'  => date('Y-m-d 15:00:00', strtotime('+7 days')),
                    'status'       => 'ide_diajukan',
                ],
                [
                    'judul'        => 'Diet Ekstrem Tanpa Karbohidrat Turun 10 Kg Seminggu',
                    'deskripsi'    => 'Tips diet instan tanpa asupan gizi seimbang.',
                    'caption'      => 'Cara cepat kurus tanpa makan nasi sama sekali...',
                    'tgl_publish'  => date('Y-m-d 12:00:00', strtotime('+8 days')),
                    'status'       => 'ditolak',
                    'catatan'      => 'FitLife mempromosikan gaya hidup sehat berkelanjutan, bukan diet ekstrem berbahaya.',
                ],
            ],
        ];

        // ── 4. Insert Content Plan & Status Log per Bisnis ─────────
        foreach ($dataSemuaBisnis as $bisnisId => $kontenList) {
            $jenisList  = $db->table('jenis_konten')->where('bisnis_id', $bisnisId)->get()->getResultArray();
            $pillarList = $db->table('content_types')->where('bisnis_id', $bisnisId)->get()->getResultArray();
            $platList   = $db->table('platforms')->where('bisnis_id', $bisnisId)->get()->getResultArray();

            foreach ($kontenList as $idx => $k) {
                $jenisId  = $jenisList[$idx % count($jenisList)]['id'] ?? 1;
                $pillarId = $pillarList[$idx % count($pillarList)]['id'] ?? 1;

                // Cek atau update konten jika sudah ada
                $existing = $db->table('content_plan')
                    ->where('bisnis_id', $bisnisId)
                    ->where('judul_konten', $k['judul'])
                    ->get()->getRowArray();

                $contentData = [
                    'bisnis_id'         => $bisnisId,
                    'judul_konten'      => $k['judul'],
                    'deskripsi'         => $k['deskripsi'],
                    'caption'           => $k['caption'] ?? null,
                    'tanggal_publish'   => $k['tgl_publish'] ?? null,
                    'jenis_konten_id'   => $jenisId,
                    'content_type_id'   => $pillarId,
                    'status'            => $k['status'],
                    'assigned_designer' => $userCreator,
                    'assigned_uploader' => $userSosmed,
                    'image_url'         => $k['image_url'] ?? null,
                    'design_url'        => $k['design_url'] ?? null,
                    'dibuat_oleh'       => $userCreator,
                    'created_at'        => date('Y-m-d H:i:s', strtotime("-" . ($idx + 1) . " days")),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ];

                if ($existing) {
                    $db->table('content_plan')->where('id', $existing['id'])->update($contentData);
                    $contentId = $existing['id'];
                } else {
                    $db->table('content_plan')->insert($contentData);
                    $contentId = $db->insertID();
                }

                // Hapus relasi platform lama & buat yang baru
                $db->table('content_platforms')->where('content_id', $contentId)->delete();
                if (!empty($platList)) {
                    $p1 = $platList[0]['id'];
                    $db->table('content_platforms')->insert(['content_id' => $contentId, 'platform_id' => $p1]);
                    if (isset($platList[1])) {
                        $p2 = $platList[1]['id'];
                        $db->table('content_platforms')->insert(['content_id' => $contentId, 'platform_id' => $p2]);
                    }
                }

                // Log Status Timeline
                $db->table('content_status_log')->insert([
                    'content_id'  => $contentId,
                    'status_lama' => 'ide_diajukan',
                    'status_baru' => $k['status'],
                    'user_id'     => $k['status'] === 'published' ? $userSosmed : $userManager,
                    'catatan'     => $k['catatan'] ?? "Status {$k['status']} berhasil diperbarui pada seeder sampel.",
                    'created_at'  => date('Y-m-d H:i:s', strtotime("-" . ($idx + 1) . " hours")),
                ]);
            }
        }
        echo "  ✓ 32 Konten realistis dengan 8 variasi status per bisnis berhasil di-seed!\n";

        // ── 5. Seed Brand Assets Lengkap per Bisnis ─────────────────
        $brandAssets = [
            1 => [
                ['nama_aset' => 'Primary Brand Color', 'kategori' => 'palette', 'nilai_atau_url' => '#6C5CE7', 'keterangan' => 'Warna ungu utama SMMS Agency'],
                ['nama_aset' => 'Secondary Accent',    'kategori' => 'palette', 'nilai_atau_url' => '#a29bfe', 'keterangan' => 'Warna ungu muda aksen'],
                ['nama_aset' => 'Dark Slate Base',     'kategori' => 'palette', 'nilai_atau_url' => '#2d3436', 'keterangan' => 'Warna teks gelap profesional'],
                ['nama_aset' => 'Logo Vector HD',      'kategori' => 'link',    'nilai_atau_url' => 'https://drive.google.com/sample-logo-smms', 'keterangan' => 'File Google Drive logo resmi'],
                ['nama_aset' => 'Brand Guidelines PDF','kategori' => 'pdf',     'nilai_atau_url' => 'https://example.com/guidelines-smms.pdf', 'keterangan' => 'Buku panduan brand identity & typography'],
            ],
            2 => [
                ['nama_aset' => 'Espresso Brown',      'kategori' => 'palette', 'nilai_atau_url' => '#4a2c11', 'keterangan' => 'Warna cokelat biji kopi sangrai'],
                ['nama_aset' => 'Fresh Mint',          'kategori' => 'palette', 'nilai_atau_url' => '#00B894', 'keterangan' => 'Warna hijau daun kopi'],
                ['nama_aset' => 'Warm Cream',          'kategori' => 'palette', 'nilai_atau_url' => '#fdfbf7', 'keterangan' => 'Warna latar susu creamy'],
                ['nama_aset' => 'Preset Canva Kopi',   'kategori' => 'link',    'nilai_atau_url' => 'https://canva.com/brand/kopi-nusantara', 'keterangan' => 'Template feed Instagram Toko Kopi'],
                ['nama_aset' => 'Brand Guidelines PDF','kategori' => 'pdf',     'nilai_atau_url' => 'https://example.com/guidelines-kopi.pdf', 'keterangan' => 'Panduan logo & tone of voice kuliner'],
            ],
            3 => [
                ['nama_aset' => 'Rose Peach Glow',     'kategori' => 'palette', 'nilai_atau_url' => '#E17055', 'keterangan' => 'Warna peach segar skincare'],
                ['nama_aset' => 'Pure Creamy White',   'kategori' => 'palette', 'nilai_atau_url' => '#fff5f2', 'keterangan' => 'Warna background bersih'],
                ['nama_aset' => 'Soft Coral',          'kategori' => 'palette', 'nilai_atau_url' => '#fab1a0', 'keterangan' => 'Warna aksen kecantikan'],
                ['nama_aset' => 'HD Product Packaging','kategori' => 'link',    'nilai_atau_url' => 'https://drive.google.com/glowskin-photos', 'keterangan' => 'Folder foto produk kualitas tinggi'],
                ['nama_aset' => 'Brand Guidelines PDF','kategori' => 'pdf',     'nilai_atau_url' => 'https://example.com/guidelines-glowskin.pdf', 'keterangan' => 'Panduan warna pastel & font estetis'],
            ],
            4 => [
                ['nama_aset' => 'Electric Blue',       'kategori' => 'palette', 'nilai_atau_url' => '#0984E3', 'keterangan' => 'Warna biru semangat olahraga FitLife'],
                ['nama_aset' => 'Neon Yellow Active',  'kategori' => 'palette', 'nilai_atau_url' => '#fdcb6e', 'keterangan' => 'Warna aksen scotlight baju lari'],
                ['nama_aset' => 'Carbon Black Sport',  'kategori' => 'palette', 'nilai_atau_url' => '#1e272e', 'keterangan' => 'Warna dasar gelap gymwear'],
                ['nama_aset' => 'Canva Sport Layouts', 'kategori' => 'link',    'nilai_atau_url' => 'https://canva.com/brand/fitlife-apparel', 'keterangan' => 'Frame promo baju gym'],
                ['nama_aset' => 'Brand Guidelines PDF','kategori' => 'pdf',     'nilai_atau_url' => 'https://example.com/guidelines-fitlife.pdf', 'keterangan' => 'Panduan visual dinamis & sporty'],
            ],
        ];

        foreach ($brandAssets as $bisnisId => $assets) {
            foreach ($assets as $a) {
                $ex = $db->table('brand_assets')
                    ->where('bisnis_id', $bisnisId)
                    ->where('nama_aset', $a['nama_aset'])
                    ->countAllResults();
                if (!$ex) {
                    $db->table('brand_assets')->insert([
                        'bisnis_id'      => $bisnisId,
                        'nama_aset'      => $a['nama_aset'],
                        'kategori'       => $a['kategori'],
                        'nilai_atau_url' => $a['nilai_atau_url'],
                        'keterangan'     => $a['keterangan'],
                        'dibuat_oleh'    => $userCreator,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
        echo "  ✓ Brand Assets & Brand Guidelines PDF per bisnis berhasil di-seed!\n";

        // ── 6. Seed Trend Bank Relevan per Bisnis ──────────────────
        $trends = [
            1 => [
                ['judul' => 'POV: Ketika Deadline Desain Maju 2 Jam', 'badge' => 'Highly Viral', 'desk' => 'Format komedi relate agensi digital', 'example' => 'POV: Kamu baru mau pulang jam 5 sore...', 'category' => 'TikTok & Reels'],
                ['judul' => 'Stop Scrolling: Jangan Buat Konten Tanpa Formula Ini', 'badge' => 'High Retention', 'desk' => 'Hook edukasi marketing media sosial', 'example' => 'Formula 3 detik pertama penentu FYP!', 'category' => 'Short Video'],
            ],
            2 => [
                ['judul' => 'Sound ASMR: Suara Es Batu & Pouring Espresso', 'badge' => 'Viral Audio', 'desk' => 'Sensasi audio menenangkan saat menyeduh kopi dingin', 'example' => 'Suara tuangan susu kental & espresso double shot', 'category' => 'Audio Viral'],
                ['judul' => 'POV: Temen Ngajak Ngopi Bilangnya Cuma Sejam', 'badge' => 'Highly Viral', 'desk' => 'Humor nongkrong berjam-jam di kedai kopi', 'example' => 'Tiba-tiba udah magrib di kafe...', 'category' => 'TikTok & Reels'],
            ],
            3 => [
                ['judul' => 'Glass Skin Routine ASMR: Suara Tetesan Serum', 'badge' => 'High Retention', 'desk' => 'Visual kulit glowing dengan audio tapping botol kaca', 'example' => 'Tapping pipet serum niacinamide langsung ke kulit', 'category' => 'Audio Viral'],
                ['judul' => 'Stop Pake Sunscreen Cara Ini! Kulitmu Malah Kusam', 'badge' => 'Viral Hook', 'desk' => 'Edukasi cara pakai sunscreen yang benar', 'example' => '3 kesalahan fatal saat mengaplikasikan tabir surya', 'category' => 'Edukasi & Tips'],
            ],
            4 => [
                ['judul' => 'Sound Motivasi: Nobody Cares, Work Harder', 'badge' => 'Viral Audio', 'desk' => 'Audio gym pump up untuk video workout repetisi berat', 'example' => 'Transisi angkat beban saat beat drop', 'category' => 'Audio Viral'],
                ['judul' => 'POV: Hari Pertama vs Bulan ke-6 di Gym', 'badge' => 'Highly Viral', 'desk' => 'Format komparasi progres pembentukan otot', 'example' => 'Dari gak kuat angkat barbel sampe lancar pull-up', 'category' => 'TikTok & Reels'],
            ],
        ];

        foreach ($trends as $bisnisId => $tList) {
            foreach ($tList as $t) {
                $ex = $db->table('trend_bank')
                    ->where('bisnis_id', $bisnisId)
                    ->where('judul', $t['judul'])
                    ->countAllResults();
                if (!$ex) {
                    $db->table('trend_bank')->insert([
                        'bisnis_id'   => $bisnisId,
                        'judul'       => $t['judul'],
                        'badge'       => $t['badge'],
                        'desk'        => $t['desk'],
                        'example'     => $t['example'],
                        'category'    => $t['category'],
                        'status'      => 'aktif',
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
        echo "  ✓ Trend Bank per bisnis berhasil di-seed!\n";

        echo "\n🎉 SEEDER SELESAI: Seluruh 4 bisnis kini memiliki data mandiri, unik, dan terisolasi sempurna!\n";
    }
}
