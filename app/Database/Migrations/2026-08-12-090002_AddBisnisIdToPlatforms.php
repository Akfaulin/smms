<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddBisnisIdToPlatforms
 *
 * Menambahkan kolom bisnis_id ke tabel platforms.
 * Data lama diisi dengan bisnis pertama.
 */
class AddBisnisIdToPlatforms extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('platforms', [
            'bisnis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('
            UPDATE platforms
            SET bisnis_id = (SELECT id FROM bisnis ORDER BY urutan ASC LIMIT 1)
            WHERE bisnis_id IS NULL
        ');

        $this->db->query('
            ALTER TABLE `platforms`
            ADD CONSTRAINT `fk_platforms_bisnis_id`
                FOREIGN KEY (`bisnis_id`) REFERENCES `bisnis`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');

        $this->db->query('ALTER TABLE `platforms` ADD INDEX `idx_platforms_bisnis_id` (`bisnis_id`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `platforms` DROP FOREIGN KEY `fk_platforms_bisnis_id`');
        $this->db->query('ALTER TABLE `platforms` DROP INDEX `idx_platforms_bisnis_id`');
        $this->forge->dropColumn('platforms', 'bisnis_id');
    }
}
