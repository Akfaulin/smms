<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddBisnisIdToBrandAssets
 */
class AddBisnisIdToBrandAssets extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('brand_assets', [
            'bisnis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('
            UPDATE brand_assets
            SET bisnis_id = (SELECT id FROM bisnis ORDER BY urutan ASC LIMIT 1)
            WHERE bisnis_id IS NULL
        ');

        $this->db->query('
            ALTER TABLE `brand_assets`
            ADD CONSTRAINT `fk_brand_assets_bisnis_id`
                FOREIGN KEY (`bisnis_id`) REFERENCES `bisnis`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');

        $this->db->query('ALTER TABLE `brand_assets` ADD INDEX `idx_brand_assets_bisnis_id` (`bisnis_id`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `brand_assets` DROP FOREIGN KEY `fk_brand_assets_bisnis_id`');
        $this->db->query('ALTER TABLE `brand_assets` DROP INDEX `idx_brand_assets_bisnis_id`');
        $this->forge->dropColumn('brand_assets', 'bisnis_id');
    }
}
