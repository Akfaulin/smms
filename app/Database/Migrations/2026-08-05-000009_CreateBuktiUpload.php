<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateBuktiUpload
 *
 * Tabel `bukti_upload` — mencatat bukti link setelah admin_medsos publish.
 * Sesuai spesifikasi §3.10.
 */
class CreateBuktiUpload extends Migration
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
            // Asumsi 1 link mewakili 1 postingan, tidak strict per platform untuk v1,
            // atau bisa nullable platform_id. Kita ikuti spec: platform_id.
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'link_postingan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'uploaded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'uploaded_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('content_id');

        $this->forge->createTable('bukti_upload');

        $this->db->query('ALTER TABLE `bukti_upload`
            ADD CONSTRAINT `fk_bukti_content_id`
                FOREIGN KEY (`content_id`) REFERENCES `content_plan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_bukti_platform_id`
                FOREIGN KEY (`platform_id`) REFERENCES `platforms`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_bukti_uploaded_by`
                FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
        ');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `bukti_upload`
            DROP FOREIGN KEY `fk_bukti_content_id`,
            DROP FOREIGN KEY `fk_bukti_platform_id`,
            DROP FOREIGN KEY `fk_bukti_uploaded_by`
        ');
        $this->forge->dropTable('bukti_upload', true);
    }
}
