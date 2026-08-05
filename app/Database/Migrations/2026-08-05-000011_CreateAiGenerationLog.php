<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateAiGenerationLog
 *
 * Tabel `ai_generation_log` untuk audit trail AI. Sesuai spec 9.8.
 */
class CreateAiGenerationLog extends Migration
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
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'fitur' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'prompt_input' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'output' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        
        $this->forge->createTable('ai_generation_log');

        $this->db->query('ALTER TABLE `ai_generation_log`
            ADD CONSTRAINT `fk_ai_log_content_id`
                FOREIGN KEY (`content_id`) REFERENCES `content_plan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_ai_log_user_id`
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `ai_generation_log`
            DROP FOREIGN KEY `fk_ai_log_content_id`,
            DROP FOREIGN KEY `fk_ai_log_user_id`
        ');
        $this->forge->dropTable('ai_generation_log', true);
    }
}
