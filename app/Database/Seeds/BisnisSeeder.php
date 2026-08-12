<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * BisnisSeeder
 *
 * Mengisi tabel `bisnis` dengan 4 bisnis default.
 * Jalankan dengan: php spark db:seed BisnisSeeder
 *
 * PENTING: Seeder ini harus dijalankan SEBELUM migration yang menambahkan
 * bisnis_id ke tabel lain (AddBisnisIdToContentPlan, dll) karena migration
 * tersebut melakukan UPDATE data lama menggunakan bisnis pertama dari tabel ini.
 */
class BisnisSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $bisnis = [
            [
                'nama_bisnis' => 'Bisnis 1',
                'deskripsi'   => 'Bisnis pertama yang dikelola sosial medianya',
                'warna'       => '#6C5CE7', // Ungu
                'logo_url'    => null,
                'status'      => 'aktif',
                'urutan'      => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nama_bisnis' => 'Bisnis 2',
                'deskripsi'   => 'Bisnis kedua yang dikelola sosial medianya',
                'warna'       => '#00B894', // Hijau tosca
                'logo_url'    => null,
                'status'      => 'aktif',
                'urutan'      => 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nama_bisnis' => 'Bisnis 3',
                'deskripsi'   => 'Bisnis ketiga yang dikelola sosial medianya',
                'warna'       => '#E17055', // Oranye
                'logo_url'    => null,
                'status'      => 'aktif',
                'urutan'      => 3,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nama_bisnis' => 'Bisnis 4',
                'deskripsi'   => 'Bisnis keempat yang dikelola sosial medianya',
                'warna'       => '#0984E3', // Biru
                'logo_url'    => null,
                'status'      => 'aktif',
                'urutan'      => 4,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        // Hanya insert jika tabel kosong agar tidak duplikat saat re-seed
        $db = \Config\Database::connect();
        $count = $db->table('bisnis')->countAllResults();

        if ($count === 0) {
            $db->table('bisnis')->insertBatch($bisnis);
        }
    }
}
