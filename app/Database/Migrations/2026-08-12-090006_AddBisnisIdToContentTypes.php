<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddBisnisIdToContentTypes
 *
 * Menambahkan kolom bisnis_id ke tabel content_types (Content Pillars).
 * Memungkinkan setiap bisnis memiliki Content Pillar masing-masing.
 */
class AddBisnisIdToContentTypes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('content_types', [
            'bisnis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('
            ALTER TABLE `content_types`
            ADD CONSTRAINT `fk_content_types_bisnis_id`
                FOREIGN KEY (`bisnis_id`) REFERENCES `bisnis`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');

        $this->db->query('ALTER TABLE `content_types` ADD INDEX `idx_content_types_bisnis_id` (`bisnis_id`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `content_types` DROP FOREIGN KEY `fk_content_types_bisnis_id`');
        $this->db->query('ALTER TABLE `content_types` DROP INDEX `idx_content_types_bisnis_id`');
        $this->forge->dropColumn('content_types', 'bisnis_id');
    }
}
