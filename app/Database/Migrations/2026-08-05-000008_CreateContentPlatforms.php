<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateContentPlatforms
 *
 * Tabel pivot `content_platforms` — konten ↔ platform.
 * Sesuai spesifikasi §3.7.
 */
class CreateContentPlatforms extends Migration
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
            'content_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['content_id', 'platform_id']);

        $this->forge->createTable('content_platforms');

        $this->db->query('ALTER TABLE `content_platforms`
            ADD CONSTRAINT `fk_cplat_content_id`
                FOREIGN KEY (`content_id`) REFERENCES `content_plan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_cplat_platform_id`
                FOREIGN KEY (`platform_id`) REFERENCES `platforms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `content_platforms`
            DROP FOREIGN KEY `fk_cplat_content_id`,
            DROP FOREIGN KEY `fk_cplat_platform_id`
        ');
        $this->forge->dropTable('content_platforms', true);
    }
}
