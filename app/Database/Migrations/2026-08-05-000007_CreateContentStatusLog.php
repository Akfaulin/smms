<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateContentStatusLog
 *
 * Tabel `content_status_log` — audit trail setiap perubahan status konten.
 * Sesuai spesifikasi §3.9.
 *
 * PENTING:
 *   - Tabel ini diisi OTOMATIS oleh canTransition() setiap kali status berubah.
 *   - Kolom `catatan` wajib diisi untuk transisi tertentu (lihat §4.3).
 *   - Tidak ada record yang boleh dihapus dari tabel ini — ini adalah audit trail.
 */
class CreateContentStatusLog extends Migration
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
            // FK → content_plan.id
            'content_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // null jika ini adalah status pertama (saat konten baru dibuat)
            'status_lama' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'status_baru' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
            ],
            // FK → users.id — siapa yang melakukan perubahan status
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // Wajib diisi untuk transisi: ide_diajukan→revisi, ide_diajukan→ditolak,
            // review_design→revisi (sesuai §4.3). Opsional untuk transisi lain.
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('content_id');
        $this->forge->addKey('user_id');

        $this->forge->createTable('content_status_log');

        // Foreign keys
        $this->db->query('ALTER TABLE `content_status_log`
            ADD CONSTRAINT `fk_csl_content_id`
                FOREIGN KEY (`content_id`) REFERENCES `content_plan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_csl_user_id`
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
        ');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `content_status_log`
            DROP FOREIGN KEY `fk_csl_content_id`,
            DROP FOREIGN KEY `fk_csl_user_id`
        ');
        $this->forge->dropTable('content_status_log', true);
    }
}
