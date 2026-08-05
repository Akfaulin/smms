<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateJenisKonten
 *
 * Tabel `jenis_konten` — Content Type (format konten).
 * Sesuai spesifikasi §3.4. Contoh: Reels, Carousel, Static Post, Story.
 * Dikelola oleh superadmin & owner saja (§5 permission matrix).
 */
class CreateJenisKonten extends Migration
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
            'nama_jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('jenis_konten');
    }

    public function down(): void
    {
        $this->forge->dropTable('jenis_konten', true);
    }
}
