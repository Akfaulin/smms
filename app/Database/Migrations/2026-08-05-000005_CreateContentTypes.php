<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateContentTypes
 *
 * Tabel `content_types` — Content Pillar (pilar konten).
 * Sesuai spesifikasi §3.5. Contoh: Edukasi, Promosi, Hiburan, Inspirasi.
 * Dikelola oleh superadmin & owner saja (§5 permission matrix).
 */
class CreateContentTypes extends Migration
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
            'nama_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('content_types');
    }

    public function down(): void
    {
        $this->forge->dropTable('content_types', true);
    }
}
