<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * PerBusinessMasterDataSeeder
 *
 * Mengisi data master (platforms, jenis_konten, content_types) secara terisolasi
 * untuk setiap bisnis yang terdaftar di database.
 */
class PerBusinessMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        $semuaBisnis = $db->table('bisnis')->get()->getResultArray();
        if (empty($semuaBisnis)) {
            return;
        }

        $defaultPlatforms = [
            ['nama_platform' => 'Instagram',   'status' => 'aktif'],
            ['nama_platform' => 'TikTok',      'status' => 'aktif'],
            ['nama_platform' => 'Facebook',    'status' => 'aktif'],
            ['nama_platform' => 'Twitter / X', 'status' => 'aktif'],
            ['nama_platform' => 'YouTube',     'status' => 'aktif'],
            ['nama_platform' => 'LinkedIn',    'status' => 'aktif'],
        ];

        $defaultJenis = [
            ['nama_jenis' => 'Reels / Video',   'keterangan' => 'Konten video pendek atau panjang'],
            ['nama_jenis' => 'Carousel',        'keterangan' => 'Slide multi-gambar'],
            ['nama_jenis' => 'Static Post',     'keterangan' => 'Gambar tunggal'],
            ['nama_jenis' => 'Story',           'keterangan' => 'Konten 24 jam'],
            ['nama_jenis' => 'Thread / Caption','keterangan' => 'Konten teks panjang'],
            ['nama_jenis' => 'Live',            'keterangan' => 'Siaran langsung'],
        ];

        $defaultPillars = [
            ['nama_type' => 'Edukasi'],
            ['nama_type' => 'Promosi'],
            ['nama_type' => 'Hiburan'],
            ['nama_type' => 'Inspirasi'],
            ['nama_type' => 'Behind the Scene'],
            ['nama_type' => 'Testimoni'],
        ];

        foreach ($semuaBisnis as $b) {
            $bisnisId = (int) $b['id'];

            // 1. Platforms per bisnis
            foreach ($defaultPlatforms as $p) {
                $exists = $db->table('platforms')
                    ->where('bisnis_id', $bisnisId)
                    ->where('nama_platform', $p['nama_platform'])
                    ->countAllResults();
                if (! $exists) {
                    $db->table('platforms')->insert([
                        'bisnis_id'     => $bisnisId,
                        'nama_platform' => $p['nama_platform'],
                        'status'        => $p['status'],
                    ]);
                }
            }

            // 2. Jenis Konten per bisnis
            foreach ($defaultJenis as $j) {
                $exists = $db->table('jenis_konten')
                    ->where('bisnis_id', $bisnisId)
                    ->where('nama_jenis', $j['nama_jenis'])
                    ->countAllResults();
                if (! $exists) {
                    $db->table('jenis_konten')->insert([
                        'bisnis_id'  => $bisnisId,
                        'nama_jenis' => $j['nama_jenis'],
                        'keterangan' => $j['keterangan'] ?? null,
                    ]);
                }
            }

            // 3. Content Pillars per bisnis
            foreach ($defaultPillars as $cp) {
                $exists = $db->table('content_types')
                    ->where('bisnis_id', $bisnisId)
                    ->where('nama_type', $cp['nama_type'])
                    ->countAllResults();
                if (! $exists) {
                    $db->table('content_types')->insert([
                        'bisnis_id' => $bisnisId,
                        'nama_type' => $cp['nama_type'],
                    ]);
                }
            }
        }

        // Hapus data master lama yang bisnis_id NULL agar tidak ada data mengambang
        $db->table('platforms')->where('bisnis_id IS NULL')->delete();
        $db->table('jenis_konten')->where('bisnis_id IS NULL')->delete();
        $db->table('content_types')->where('bisnis_id IS NULL')->delete();

        echo "Per-business master data seeded and isolated successfully.\n";
    }
}
