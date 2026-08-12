<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddBisnisIdToTrendBank
 */
class AddBisnisIdToTrendBank extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('trend_bank', [
            'bisnis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->db->query('
            UPDATE trend_bank
            SET bisnis_id = (SELECT id FROM bisnis ORDER BY urutan ASC LIMIT 1)
            WHERE bisnis_id IS NULL
        ');

        $this->db->query('
            ALTER TABLE `trend_bank`
            ADD CONSTRAINT `fk_trend_bank_bisnis_id`
                FOREIGN KEY (`bisnis_id`) REFERENCES `bisnis`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');

        $this->db->query('ALTER TABLE `trend_bank` ADD INDEX `idx_trend_bank_bisnis_id` (`bisnis_id`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `trend_bank` DROP FOREIGN KEY `fk_trend_bank_bisnis_id`');
        $this->db->query('ALTER TABLE `trend_bank` DROP INDEX `idx_trend_bank_bisnis_id`');
        $this->forge->dropColumn('trend_bank', 'bisnis_id');
    }
}
