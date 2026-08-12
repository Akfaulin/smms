<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateBisnis
 *
 * Membuat tabel `bisnis` untuk mendukung multi-account management.
 * Setiap bisnis mewakili satu akun/brand yang sosmed-nya dikelola oleh tim SMMS.
 *
 * Maksimal 4 bisnis sesuai spesifikasi produk.
 */
class CreateBisnis extends Migration
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
            'nama_bisnis' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // HEX color code untuk identifikasi visual di UI, contoh: #6C5CE7
            'warna' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'default'    => '#6C5CE7',
                'null'       => false,
            ],
            // URL logo atau ikon bisnis (opsional)
            'logo_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
                'null'       => false,
            ],
            'urutan' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 1,
                'null'       => false,
                // Untuk menentukan bisnis default (urutan terkecil = default)
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
        $this->forge->addKey('status');
        $this->forge->addKey('urutan');

        $this->forge->createTable('bisnis');
    }

    public function down(): void
    {
        $this->forge->dropTable('bisnis', true);
    }
}
