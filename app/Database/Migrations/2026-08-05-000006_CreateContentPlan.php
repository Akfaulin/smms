<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateContentPlan
 *
 * Tabel utama `content_plan` — sesuai spesifikasi §3.6.
 *
 * Kolom penting:
 *   - status        : VARCHAR(30), nilai valid dikontrol PENUH oleh canTransition()
 *                     (bukan ENUM agar mudah debug dan fleksibel di state machine)
 *   - dibuat_oleh   : FK → users.id — pengusul ide
 *   - assigned_designer  : nullable FK → users.id
 *   - assigned_uploader  : nullable FK → users.id
 *
 * PERINGATAN: Status konten TIDAK BOLEH diupdate langsung dari form/controller biasa.
 * Semua perubahan status WAJIB melalui canTransition() di TransisiKonten service.
 */
class CreateContentPlan extends Migration
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
            'judul_konten' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tanggal_publish' => [
                'type' => 'DATE',
                'null' => true,
            ],
            // FK → jenis_konten.id (§3.4)
            'jenis_konten_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            // FK → content_types.id — Content Pillar (§3.5)
            'content_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            // Status dikontrol state machine — nilai awal saat insert: 'ide_diajukan'
            // Nilai valid: ide_diajukan | acc_ide | in_design | review_design |
            //              revisi | acc_final | published | ditolak
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'ide_diajukan',
                'null'       => false,
            ],
            // FK → users.id — wajib diisi (pengusul ide) (§3.6)
            'dibuat_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // FK → users.id — nullable, diisi saat acc_ide (§3.6)
            'assigned_designer' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            // FK → users.id — nullable, diisi saat acc_final (§3.6)
            'assigned_uploader' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('dibuat_oleh');
        $this->forge->addKey('jenis_konten_id');
        $this->forge->addKey('content_type_id');

        $this->forge->createTable('content_plan');

        // Foreign keys
        $this->db->query('ALTER TABLE `content_plan`
            ADD CONSTRAINT `fk_cp_jenis_konten`
                FOREIGN KEY (`jenis_konten_id`) REFERENCES `jenis_konten`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_cp_content_type`
                FOREIGN KEY (`content_type_id`) REFERENCES `content_types`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_cp_dibuat_oleh`
                FOREIGN KEY (`dibuat_oleh`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_cp_designer`
                FOREIGN KEY (`assigned_designer`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_cp_uploader`
                FOREIGN KEY (`assigned_uploader`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE `content_plan`
            DROP FOREIGN KEY `fk_cp_jenis_konten`,
            DROP FOREIGN KEY `fk_cp_content_type`,
            DROP FOREIGN KEY `fk_cp_dibuat_oleh`,
            DROP FOREIGN KEY `fk_cp_designer`,
            DROP FOREIGN KEY `fk_cp_uploader`
        ');
        $this->forge->dropTable('content_plan', true);
    }
}
