<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * RolesSeeder
 *
 * Mengisi tabel `roles` dengan 5 role tetap sesuai spesifikasi
 * rancangan-sistem-smm.md §3.1 dan §2.
 *
 * PENTING: Role bersifat FIXED — jangan tambah/hapus via seeder ulang
 * tanpa memeriksa dampak ke state machine di §4. Seeder ini idempoten
 * (menggunakan INSERT IGNORE agar aman dijalankan ulang).
 */
class RolesSeeder extends Seeder
{
    /**
     * Daftar 5 role tetap sistem.
     * Urutan dari paling tinggi (superadmin) ke paling terbatas (admin_medsos).
     */
    private array $roles = [
        [
            'kode_role' => 'superadmin',
            'nama_role' => 'Superadmin',
        ],
        [
            'kode_role' => 'owner',
            'nama_role' => 'Owner',
        ],
        [
            'kode_role' => 'manager',
            'nama_role' => 'Manager',
        ],
        [
            'kode_role' => 'content_creator',
            'nama_role' => 'Content Creator',
        ],
        [
            'kode_role' => 'admin_medsos',
            'nama_role' => 'Admin Media Sosial',
        ],
    ];

    public function run(): void
    {
        // Gunakan INSERT IGNORE agar idempoten:
        // aman dijalankan berulang tanpa duplikasi
        // (kode_role memiliki UNIQUE constraint)
        foreach ($this->roles as $role) {
            $this->db->table('roles')->ignore(true)->insert($role);
        }

        echo "  - RolesSeeder: 5 role tetap berhasil diisi.\n";
    }
}
