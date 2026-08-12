<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddBisnisIdToContentPlan
 *
 * Menambahkan kolom bisnis_id ke tabel content_plan.
 * Data lama (bisnis_id NULL) diisi dengan id bisnis pertama (urutan=1).
 */
class AddBisnisIdToContentPlan extends Migration
{
    public function up(): void
    {
        // Tambah kolom bisnis_id
        $this->forge->addColumn('content_plan', [
            'bisnis_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        // Isi data lama dengan bisnis pertama (urutan terkecil)
        $this->db->query('
            UPDATE content_plan
            SET bisnis_id = (SELECT id FROM bisnis ORDER BY urutan ASC LIMIT 1)
            WHERE bisnis_id IS NULL
        ');

        // Tambah foreign key
        $this->db->query('
            ALTER TABLE `content_plan`
            ADD CONSTRAINT `fk_cp_bisnis_id`
                FOREIGN KEY (`bisnis_id`) REFERENCES `bisnis`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');

        // Tambah index
        $this->db->query('ALTER TABLE `content_plan` ADD INDEX `idx_cp_bisnis_id` (`bisnis_id`)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `content_plan` DROP FOREIGN KEY `fk_cp_bisnis_id`');
        $this->db->query('ALTER TABLE `content_plan` DROP INDEX `idx_cp_bisnis_id`');
        $this->forge->dropColumn('content_plan', 'bisnis_id');
    }
}
