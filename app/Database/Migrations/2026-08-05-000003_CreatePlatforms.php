<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreatePlatforms
 *
 * Tabel `platforms` — master data platform media sosial.
 * Sesuai spesifikasi §3.3. Tidak berubah dari versi sebelumnya.
 * Dikelola oleh superadmin & owner saja (§5 permission matrix).
 */
class CreatePlatforms extends Migration
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
            'nama_platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('platforms');
    }

    public function down(): void
    {
        $this->forge->dropTable('platforms', true);
    }
}
