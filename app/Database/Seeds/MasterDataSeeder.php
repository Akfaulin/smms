<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MasterDataSeeder
 *
 * Mengisi data master: platforms, jenis_konten, content_types.
 * Dijalankan setelah RolesSeeder dan UserSeeder.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Platforms ────────────────────────────────────────
        $platforms = [
            ['nama_platform' => 'Instagram',  'status' => 'aktif'],
            ['nama_platform' => 'TikTok',     'status' => 'aktif'],
            ['nama_platform' => 'Facebook',   'status' => 'aktif'],
            ['nama_platform' => 'Twitter / X','status' => 'aktif'],
            ['nama_platform' => 'YouTube',    'status' => 'aktif'],
            ['nama_platform' => 'LinkedIn',   'status' => 'aktif'],
        ];

        foreach ($platforms as $p) {
            $exists = $this->db->table('platforms')
                ->where('nama_platform', $p['nama_platform'])
                ->countAllResults();
            if (! $exists) {
                $this->db->table('platforms')->insert($p);
            }
        }

        echo "  - MasterDataSeeder: " . count($platforms) . " platform selesai.\n";

        // ── Jenis Konten ──────────────────────────────────────
        $jenisKonten = [
            ['nama_jenis' => 'Reels / Video',  'keterangan' => 'Konten video pendek atau panjang'],
            ['nama_jenis' => 'Carousel',        'keterangan' => 'Slide multi-gambar'],
            ['nama_jenis' => 'Static Post',     'keterangan' => 'Gambar tunggal'],
            ['nama_jenis' => 'Story',           'keterangan' => 'Konten 24 jam'],
            ['nama_jenis' => 'Thread / Caption','keterangan' => 'Konten teks panjang'],
            ['nama_jenis' => 'Live',            'keterangan' => 'Siaran langsung'],
        ];

        foreach ($jenisKonten as $j) {
            $exists = $this->db->table('jenis_konten')
                ->where('nama_jenis', $j['nama_jenis'])
                ->countAllResults();
            if (! $exists) {
                $this->db->table('jenis_konten')->insert($j);
            }
        }

        echo "  - MasterDataSeeder: " . count($jenisKonten) . " jenis konten selesai.\n";

        // ── Content Types (Content Pillar) ────────────────────
        $contentTypes = [
            ['nama_type' => 'Edukasi'],
            ['nama_type' => 'Promosi'],
            ['nama_type' => 'Hiburan'],
            ['nama_type' => 'Inspirasi'],
            ['nama_type' => 'Behind the Scene'],
            ['nama_type' => 'Testimoni'],
        ];

        foreach ($contentTypes as $ct) {
            $exists = $this->db->table('content_types')
                ->where('nama_type', $ct['nama_type'])
                ->countAllResults();
            if (! $exists) {
                $this->db->table('content_types')->insert($ct);
            }
        }

        echo "  - MasterDataSeeder: " . count($contentTypes) . " content type selesai.\n";
    }
}
