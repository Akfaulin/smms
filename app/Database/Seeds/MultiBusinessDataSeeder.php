<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MultiBusinessDataSeeder
 *
 * Mengisi data sampel realistis untuk 4 bisnis/brand yang dikelola di SMMS:
 *   1. Bisnis 1 (Digital Agency / Core Brand)
 *   2. Toko Kopi Nusantara (F&B / Culinary)
 *   3. GlowSkin Beauty (Skincare & Cosmetics)
 *   4. FitLife Apparel (Sportswear & Activewear)
 *
 * Mengisi tabel:
 *   - bisnis (update nama & deskripsi jika masih default)
 *   - content_plan (konten dengan berbagai status per bisnis)
 *   - content_platforms (relasi ke platform per bisnis)
 *   - content_status_log (timeline status)
 *   - brand_assets (brand kit / warna palette per bisnis)
 *   - trend_bank (bank tren per bisnis)
 */
class MultiBusinessDataSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        // ── 1. Ambil / Buat 4 Bisnis ───────────────────────────
        $bisnisData = [
            [
                'id'          => 1,
                'nama_bisnis' => 'SMMS Digital Agency',
                'deskripsi'   => 'Layanan konsultan & manajemen media sosial profesional untuk brand nasional.',
                'warna'       => '#6C5CE7',
                'urutan'      => 1,
            ],
            [
                'id'          => 2,
                'nama_bisnis' => 'Toko Kopi Nusantara',
                'deskripsi'   => 'Kedai kopi kekinian & suplier biji kopi pilihan dari petani lokal seluruh Indonesia.',
                'warna'       => '#00B894',
                'urutan'      => 2,
            ],
            [
                'id'          => 3,
                'nama_bisnis' => 'GlowSkin Beauty',
                'deskripsi'   => 'Brand produk perawatan kulit & kecantikan alami aman teruji BPOM.',
                'warna'       => '#E17055',
                'urutan'      => 3,
            ],
            [
                'id'          => 4,
                'nama_bisnis' => 'FitLife Apparel',
                'deskripsi'   => 'Pakaian olahraga & gym wear performa tinggi dengan bahan breathable ultralight.',
                'warna'       => '#0984E3',
                'urutan'      => 4,
            ],
        ];

        foreach ($bisnisData as $b) {
            $exists = $db->table('bisnis')->where('id', $b['id'])->get()->getRowArray();
            if ($exists) {
                $db->table('bisnis')->where('id', $b['id'])->update([
                    'nama_bisnis' => $b['nama_bisnis'],
                    'deskripsi'   => $b['deskripsi'],
                    'warna'       => $b['warna'],
                ]);
            } else {
                $db->table('bisnis')->insert([
                    'id'          => $b['id'],
                    'nama_bisnis' => $b['nama_bisnis'],
                    'deskripsi'   => $b['deskripsi'],
                    'warna'       => $b['warna'],
                    'status'      => 'aktif',
                    'urutan'      => $b['urutan'],
                ]);
            }
        }

        echo "  - MultiBusinessDataSeeder: 4 Bisnis siap.\n";

        // Pastikan master data per bisnis tersedia
        $this->call('PerBusinessMasterDataSeeder');

        // Ambil User ID untuk creator & manager
        $userCreator = $db->table('users')->where('email', 'creator@smm.local')->get()->getRowArray()['id'] ?? 1;
        $userManager = $db->table('users')->where('email', 'manager@smm.local')->get()->getRowArray()['id'] ?? 1;
        $userSosmed  = $db->table('users')->where('email', 'sosmed@smm.local')->get()->getRowArray()['id'] ?? 1;

        // ── 2. Data Konten Realistis per Bisnis ───────────────
        $kontenPerBisnis = [
            // ================= BISNIS 1 =================
            1 => [
                [
                    'judul'        => 'Peluncuran Fitur AI Assistant Q3 SMMS',
                    'deskripsi'    => 'Konten promosi fitur AI assistant teranyar untuk membantu tim meng-generate caption.',
                    'caption'      => 'Capek bikin caption dari nol? ✨ Sekarang SMMS sudah dilengkapi AI Assistant! Buat caption menarik untuk Instagram dan TikTok dalam hitungan detik. Coba gratis sekarang!',
                    'tgl_publish'  => date('Y-m-d', strtotime('-2 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-smms-1',
                ],
                [
                    'judul'        => '5 Tips Naikkan Engagement Instagram Reels 2026',
                    'deskripsi'    => 'Infografis carousel berisi tips optimasi hook 3 detik pertama pada video pendek.',
                    'caption'      => 'Mau Reels kamu tembus FYP dan dapet ribuan likes? Simak 5 rahasia struktur video pendek yang terbukti efektif meningkatkan engagement rate hingga 3x lipat! 🚀',
                    'tgl_publish'  => date('Y-m-d', strtotime('+1 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-smms-2',
                ],
                [
                    'judul'        => 'Behind The Scene: Suasana Kerja Tim Creative SMMS',
                    'deskripsi'    => 'Video santai vlog kantor memperlihatkan kolaborasi designer & copywriter.',
                    'caption'      => 'Intip serunya balik layar tim kreatif SMMS saat brainstorming ide kampanye klien! Siapa di sini yang tim coffee lover saat ngerjain desain? ☕',
                    'tgl_publish'  => date('Y-m-d', strtotime('+3 days')),
                    'status'       => 'in_design',
                    'design_url'   => 'https://canva.com/design/sample-smms-3',
                ],
                [
                    'judul'        => 'Studi Kasus: Menaikkan Followers Klien 300% dalam 90 Hari',
                    'deskripsi'    => 'Postingan testimoni & hasil analisa analitik akun klien industri kuliner.',
                    'caption'      => 'Hasil nyata strategi konten berbasis data! Dalam 90 hari, jangkauan organik akun klien kami melonjak naik 300%. Konsultasikan sosial mediamu bersama kami!',
                    'tgl_publish'  => date('Y-m-d', strtotime('+5 days')),
                    'status'       => 'ide_diajukan',
                ],
            ],

            // ================= BISNIS 2 (Toko Kopi Nusantara) =================
            2 => [
                [
                    'judul'        => 'Promo Kopi Susu Gula Aren Buy 1 Get 1 Spesial Gajian',
                    'deskripsi'    => 'Promo besar akhir bulan untuk varian signature Kopi Susu Gula Aren.',
                    'caption'      => 'PROMO BUY 1 GET 1 KOPI SUSU GULA AREN! ☕ Sapa teman kantor atau sahabat kamu buat ngopi bareng sore ini. Berlaku di seluruh gerai Toko Kopi Nusantara!',
                    'tgl_publish'  => date('Y-m-d', strtotime('-1 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-kopi-1',
                ],
                [
                    'judul'        => 'Edukasi Biji Kopi Arabika vs Robusta: Pilih Mana?',
                    'deskripsi'    => 'Carousel perbandingan cita rasa, kadar kafein, dan cara penyeduhan yang pas.',
                    'caption'      => 'Kamu tim Arabika yang asam segar beraroma floral, atau tim Robusta yang pahit mantap dan bold? Yuk pelajari bedanya di slide ini biar gak salah pilih roasted bean! ☕✨',
                    'tgl_publish'  => date('Y-m-d', strtotime('+2 days')),
                    'status'       => 'review_design',
                    'design_url'   => 'https://canva.com/design/sample-kopi-2',
                ],
                [
                    'judul'        => 'Resep Cold Brew Kopi Susu Creamy di Rumah',
                    'deskripsi'    => 'Video reels tutorial menyeduh cold brew ramah lambung dengan alat sederhana.',
                    'caption'      => 'Bikin Cold Brew ala cafe di rumah cuma butuh 3 bahan! Simpan video ini untuk panduan weekend brewing kamu. 🥛☕',
                    'tgl_publish'  => date('Y-m-d', strtotime('+4 days')),
                    'status'       => 'in_design',
                ],
                [
                    'judul'        => 'Suasana Santai Co-Working Space Kopi Nusantara',
                    'deskripsi'    => 'Foto estetis suasana area indoor & outdoor dengan fasilitas Wi-Fi kencang.',
                    'caption'      => 'Nugas atau WFH makin fokus kalau dapet tempat nyaman + aroma kopi yang menenangkan. Mampir yuk ke cabang terdekat!',
                    'tgl_publish'  => date('Y-m-d', strtotime('+6 days')),
                    'status'       => 'ide_diajukan',
                ],
            ],

            // ================= BISNIS 3 (GlowSkin Beauty) =================
            3 => [
                [
                    'judul'        => 'Sunscreen 101: Kenapa Wajib Reapply Tiap 2 Jam?',
                    'deskripsi'    => 'Edukasi pentingnya perlindungan sinar UV A & UV B untuk mencegah penuaan dini.',
                    'caption'      => 'Pake sunscreen pagi aja belum cukup lho! ☀️ Sinar matahari tetap bisa merusak kulit kalau tidak di-reapply tiap 2 jam. Pakai GlowSkin Air-Touch Sunscreen yang gak bikin dempul!',
                    'tgl_publish'  => date('Y-m-d', strtotime('-3 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-skin-1',
                ],
                [
                    'judul'        => 'Rangkaian Morning Skincare Routine untuk Kulit Glowing',
                    'deskripsi'    => 'Urutan penggunaan cleanser, toner, serum niacinamide, dan moisturizer.',
                    'caption'      => 'Bangun tidur dengan kulit kenyal & glowing bukan impian lagi! ✨ Ikuti 4 langkah simpel morning skincare routine bersama GlowSkin Beauty ini setiap hari.',
                    'tgl_publish'  => date('Y-m-d', strtotime('+1 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1608248597461-8f9f8c6b758b?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-skin-2',
                ],
                [
                    'judul'        => 'Review Jujur Serum Niacinamide 10%: Before After 14 Hari',
                    'deskripsi'    => 'Testimoni nyata pemakaian serum untuk memudarkan bekas jerawat.',
                    'caption'      => 'Lihat perubahan tekstur kulit dalam 14 hari pemakaian rutin GlowSkin Serum Niacinamide 10%! Flek hitam memudar dan skin barrier lebih sehat. 💖',
                    'tgl_publish'  => date('Y-m-d', strtotime('+3 days')),
                    'status'       => 'revisi',
                    'catatan'      => 'Tolong perbaiki pencahayaan foto Before After agar terlihat lebih natural dan kontras warna kulit sesuai.',
                ],
                [
                    'judul'        => 'Diskon Festival Belanja 9.9 Bundling Skincare Set',
                    'deskripsi'    => 'Promo hemat paket komplit cleanser + serum + moisturizer diskon 40%.',
                    'caption'      => 'READY FOR 9.9 MEGA SALE! 🛍️ Dapatkan paket bundling Glowing Skin Kit dengan diskon hingga 40% gratis ongkir ke seluruh Indonesia!',
                    'tgl_publish'  => date('Y-m-d', strtotime('+7 days')),
                    'status'       => 'ide_diajukan',
                ],
            ],

            // ================= BISNIS 4 (FitLife Apparel) =================
            4 => [
                [
                    'judul'        => 'Peluncuran Koleksi FitLife Jersey Running Ultralight Q3',
                    'deskripsi'    => 'Foto produk jersey lari dengan teknologi bahan daya serap keringat tinggi.',
                    'caption'      => 'LIGHTER. FASTER. STRONGER. 🔥 Memperkenalkan FitLife Ultralight Running Jersey! Bobot hanya 85 gram dengan sirkulasi udara maksimal untuk performa lari terbaikmu.',
                    'tgl_publish'  => date('Y-m-d', strtotime('-4 days')),
                    'status'       => 'published',
                    'image_url'    => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-fit-1',
                ],
                [
                    'judul'        => '5 Gerakan Stretching Wajib Sebelum & Sesudah Maraton',
                    'deskripsi'    => 'Video reels tutorial peregangan otot kaki untuk cegah kram saat lari.',
                    'caption'      => 'Jangan asal lari kalau gak mau cedera otot! 🏃‍♂️ Lakukan 5 gerakan peregangan wajib ini selama 5 menit sebelum kamu mulai jarak jauh.',
                    'tgl_publish'  => date('Y-m-d', strtotime('+2 days')),
                    'status'       => 'acc_final',
                    'image_url'    => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&q=80',
                    'design_url'   => 'https://canva.com/design/sample-fit-2',
                ],
                [
                    'judul'        => 'Uji Ketahanan Bahan Quick-Dry FitLife vs Katun Biasa',
                    'deskripsi'    => 'Edukasi keunggulan katun sintetis breathable dibanding baju kaos biasa.',
                    'caption'      => 'Kenapa kaos biasa bikin gatal pas keringatan di gym? Beda bahan, beda kenyamanan! Tonton uji coba daya kering FitLife Quick-Dry Fabric di video ini. 🏋️‍♂️',
                    'tgl_publish'  => date('Y-m-d', strtotime('+4 days')),
                    'status'       => 'review_design',
                    'design_url'   => 'https://canva.com/design/sample-fit-3',
                ],
                [
                    'judul'        => 'Payday Promo Gymwear: Buy 2 Get 1 Free Shorts',
                    'deskripsi'    => 'Promo gajian celana pendek olahraga serbaguna untuk workout & santai.',
                    'caption'      => 'PAYDAY OUTFIT CHECK! 💥 Dapatkan 1 celana olahraga gratis setiap pembelian 2 pcs FitLife Activewear. Persediaan terbatas!',
                    'tgl_publish'  => date('Y-m-d', strtotime('+6 days')),
                    'status'       => 'ide_diajukan',
                ],
            ],
        ];

        foreach ($kontenPerBisnis as $bisnisId => $daftarKonten) {
            // Ambil master jenis & pillar untuk bisnis ini
            $jenisList  = $db->table('jenis_konten')->where('bisnis_id', $bisnisId)->get()->getResultArray();
            $pillarList = $db->table('content_types')->where('bisnis_id', $bisnisId)->get()->getResultArray();
            $platList   = $db->table('platforms')->where('bisnis_id', $bisnisId)->get()->getResultArray();

            foreach ($daftarKonten as $idx => $k) {
                $jenisId  = $jenisList[$idx % count($jenisList)]['id'] ?? 1;
                $pillarId = $pillarList[$idx % count($pillarList)]['id'] ?? 1;

                // Cek apakah konten serupa sudah ada di bisnis ini
                $exists = $db->table('content_plan')
                    ->where('bisnis_id', $bisnisId)
                    ->where('judul_konten', $k['judul'])
                    ->countAllResults();

                if ($exists) continue;

                // Insert content plan
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
                    'created_at'        => date('Y-m-d H:i:s', strtotime("-{$idx} days")),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ];

                $db->table('content_plan')->insert($contentData);
                $contentId = $db->insertID();

                // Relasi Platforms (pilih 1 atau 2 platform)
                if (! empty($platList)) {
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
                    'catatan'     => $k['catatan'] ?? "Status diubah ke {$k['status']} oleh seeder sampel.",
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo "  - MultiBusinessDataSeeder: Sampel konten realistis untuk 4 bisnis berhasil di-seed!\n";

        // ── 3. Seed Brand Assets & Trend Bank per Bisnis ──────
        $assetsPerBisnis = [
            1 => [
                ['nama_aset' => 'Primary Brand Color', 'kategori' => 'palette', 'nilai_atau_url' => '#6C5CE7', 'keterangan' => 'Warna ungu utama SMMS Agency'],
                ['nama_aset' => 'Secondary Accent',    'kategori' => 'palette', 'nilai_atau_url' => '#a29bfe', 'keterangan' => 'Warna ungu muda aksen'],
                ['nama_aset' => 'Logo Vector HD',      'kategori' => 'link',    'nilai_atau_url' => 'https://drive.google.com/sample-logo-smms', 'keterangan' => 'File Google Drive logo resmi'],
            ],
            2 => [
                ['nama_aset' => 'Espresso Brown',      'kategori' => 'palette', 'nilai_atau_url' => '#4a2c11', 'keterangan' => 'Warna cokelat biji kopi sangrai'],
                ['nama_aset' => 'Fresh Mint',          'kategori' => 'palette', 'nilai_atau_url' => '#00B894', 'keterangan' => 'Warna hijau daun kopi'],
                ['nama_aset' => 'Preset Canva Kopi',   'kategori' => 'link',    'nilai_atau_url' => 'https://canva.com/brand/kopi-nusantara', 'keterangan' => 'Template feed Instagram Toko Kopi'],
            ],
            3 => [
                ['nama_aset' => 'Rose Peach Glow',     'kategori' => 'palette', 'nilai_atau_url' => '#E17055', 'keterangan' => 'Warna peach segar skincare'],
                ['nama_aset' => 'Pure Creamy White',   'kategori' => 'palette', 'nilai_atau_url' => '#fff5f2', 'keterangan' => 'Warna background bersih'],
                ['nama_aset' => 'HD Product Packaging','kategori' => 'link',    'nilai_atau_url' => 'https://drive.google.com/glowskin-photos', 'keterangan' => 'Folder foto produk kualitas tinggi'],
            ],
            4 => [
                ['nama_aset' => 'Electric Blue',       'kategori' => 'palette', 'nilai_atau_url' => '#0984E3', 'keterangan' => 'Warna biru semangat olahraga FitLife'],
                ['nama_aset' => 'Neon Yellow Active',  'kategori' => 'palette', 'nilai_atau_url' => '#fdcb6e', 'keterangan' => 'Warna aksen scotlight baju lari'],
                ['nama_aset' => 'Canva Sport Layouts', 'kategori' => 'link',    'nilai_atau_url' => 'https://canva.com/brand/fitlife-apparel', 'keterangan' => 'Frame promo baju gym'],
            ],
        ];

        foreach ($assetsPerBisnis as $bisnisId => $assets) {
            foreach ($assets as $a) {
                $exists = $db->table('brand_assets')
                    ->where('bisnis_id', $bisnisId)
                    ->where('nama_aset', $a['nama_aset'])
                    ->countAllResults();
                if (! $exists) {
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

        echo "  - MultiBusinessDataSeeder: Brand Assets per bisnis berhasil di-seed!\n";
    }
}
