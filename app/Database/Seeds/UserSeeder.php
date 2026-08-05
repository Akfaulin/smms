<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

/**
 * UserSeeder
 *
 * Membuat 1 akun superadmin awal untuk pertama kali masuk sistem.
 * Jalankan SETELAH RolesSeeder.
 *
 * Akun default:
 *   Email    : admin@smm.local
 *   Password : admin123
 *
 * PENTING: Ganti password segera setelah login pertama kali!
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userModel = new UserModel();

        // Ambil ID role superadmin
        $roleSuperadmin = $this->db->table('roles')
            ->where('kode_role', 'superadmin')
            ->get()
            ->getRowArray();

        if (! $roleSuperadmin) {
            echo "  - UserSeeder: role superadmin tidak ditemukan. Jalankan RolesSeeder dulu.\n";
            return;
        }

        // Jika sudah ada, update password & status agar bisa digunakan untuk login
        $existing = $userModel->where('email', 'admin@smm.local')->first();
        if ($existing) {
            $userModel->update($existing['id'], [
                'password' => UserModel::hashPassword('admin123'),
                'status'   => 'aktif',
                'role_id'  => $roleSuperadmin['id'],
            ]);
            echo "  - UserSeeder: Password akun admin@smm.local diperbarui ke 'admin123'.\n";
            return;
        }

        $userModel->insert([
            'nama'       => 'Superadmin',
            'email'      => 'admin@smm.local',
            'password'   => UserModel::hashPassword('admin123'),
            'role_id'    => $roleSuperadmin['id'],
            'status'     => 'aktif',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "  - UserSeeder: Akun superadmin dibuat (admin@smm.local / admin123).\n";
        echo "  - PENTING: Ganti password segera setelah login pertama!\n";
    }
}
