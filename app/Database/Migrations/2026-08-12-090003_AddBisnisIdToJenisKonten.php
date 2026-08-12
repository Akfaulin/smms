<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddBisnisIdToJenisKonten
 */
class AddBisnisIdToJenisKonten extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('jenis_konten', [
            'bisnis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('
            UPDATE jenis_konten
            SET bisnis_id = (SELECT id FROM bisnis ORDER BY urutan ASC LIMIT 1)
            WHERE bisnis_id IS NULL
        ');

        $this->db->query('
            ALTER TABLE `jenis_konten`
            ADD CONSTRAINT `fk_jenis_konten_bisnis_id`
                FOREIGN KEY (`bisnis_id`) REFERENCES `bisnis`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');

        $this->db->query('ALTER TABLE `jenis_konten` ADD INDEX `idx_jenis_konten_bisnis_id` (`bisnis_id`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `jenis_konten` DROP FOREIGN KEY `fk_jenis_konten_bisnis_id`');
        $this->db->query('ALTER TABLE `jenis_konten` DROP INDEX `idx_jenis_konten_bisnis_id`');
        $this->forge->dropColumn('jenis_konten', 'bisnis_id');
    }
}
