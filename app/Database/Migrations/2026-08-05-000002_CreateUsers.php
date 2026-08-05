<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateUsers
 *
 * Membuat tabel `users` dengan kolom-kolom standar ditambah:
 *   - role_id    : FK → roles.id (wajib) — sesuai §3.2
 *   - status     : ENUM('aktif','nonaktif') — sesuai §3.2
 *
 * Strategi default role untuk data lama:
 *   Jika suatu saat tabel ini ditambahkan ke DB yang sudah memiliki user,
 *   kolom role_id diberi DEFAULT ke role dengan kode 'content_creator'
 *   (paling minimal permission-nya). Superadmin / Owner wajib mengubah
 *   assignment role setelah migrasi.
 *
 * Catatan: password disimpan sebagai hash (bcrypt via password_hash()).
 */
class CreateUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            // role_id — FK ke tabel roles (§3.2)
            // DEFAULT NULL diizinkan saat kolom baru ditambah ke tabel existing,
            // tapi aplikasi harus selalu memastikan role_id terisi.
            'role_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true,  // nullable agar aman saat migrasi data lama
                'default'  => null,
            ],
            // status — aktif / nonaktif (§3.2)
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('role_id');

        $this->forge->createTable('users');

        // Foreign key: users.role_id → roles.id
        // Dibuat setelah tabel agar tabel roles sudah pasti ada
        $this->db->query('ALTER TABLE `users` ADD CONSTRAINT `fk_users_role_id`
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        // Hapus FK dulu sebelum drop tabel
        $this->db->query('ALTER TABLE `users` DROP FOREIGN KEY `fk_users_role_id`');
        $this->forge->dropTable('users', true);
    }
}
