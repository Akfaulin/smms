<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateRoles
 *
 * Membuat tabel `roles` dengan 5 role tetap (fixed) sesuai spesifikasi
 * rancangan-sistem-smm.md §3.1.
 *
 * Role bersifat tetap — isi via seeder, tidak di-CRUD dari UI,
 * demi menjaga konsistensi state machine approval di §4.
 */
class CreateRoles extends Migration
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
            'kode_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
                // Nilai valid: superadmin | owner | manager | content_creator | admin_medsos
            ],
            'nama_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_role');

        $this->forge->createTable('roles');
    }

    public function down(): void
    {
        $this->forge->dropTable('roles', true);
    }
}
