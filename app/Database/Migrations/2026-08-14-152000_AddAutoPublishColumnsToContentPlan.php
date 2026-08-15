<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAutoPublishColumnsToContentPlan extends Migration
{
    public function up(): void
    {
        $existingFields = $this->db->getFieldNames('content_plan');

        $fieldsToAdd = [];

        if (! in_array('scheduled_at', $existingFields, true)) {
            $fieldsToAdd['scheduled_at'] = [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'tanggal_publish',
            ];
        }

        if (! in_array('auto_publish_status', $existingFields, true)) {
            $fieldsToAdd['auto_publish_status'] = [
                'type'       => 'ENUM',
                'constraint' => ['menunggu', 'diproses', 'berhasil', 'gagal'],
                'null'       => true,
                'default'    => null,
                'after'      => in_array('scheduled_at', $existingFields, true) ? 'scheduled_at' : 'tanggal_publish',
            ];
        }

        if (! in_array('publish_attempts', $existingFields, true)) {
            $fieldsToAdd['publish_attempts'] = [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => in_array('auto_publish_status', $existingFields, true) ? 'auto_publish_status' : 'scheduled_at',
            ];
        }

        if (! in_array('last_error', $existingFields, true)) {
            $fieldsToAdd['last_error'] = [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => in_array('publish_attempts', $existingFields, true) ? 'publish_attempts' : 'auto_publish_status',
            ];
        }

        if (! empty($fieldsToAdd)) {
            $this->forge->addColumn('content_plan', $fieldsToAdd);
        }

        // Tambahkan index komposit jika belum ada
        try {
            $this->db->query('ALTER TABLE `content_plan` ADD INDEX `idx_cp_autopublish` (`scheduled_at`, `auto_publish_status`)');
        } catch (\Throwable $e) {
            // Abaikan jika index sudah ada
        }
    }

    public function down(): void
    {
        try {
            $this->db->query('ALTER TABLE `content_plan` DROP INDEX `idx_cp_autopublish`');
        } catch (\Throwable $e) {
            // Abaikan jika index belum ada
        }

        $existingFields = $this->db->getFieldNames('content_plan');
        $colsToDrop = array_intersect(['auto_publish_status', 'publish_attempts', 'last_error'], $existingFields);

        if (! empty($colsToDrop)) {
            $this->forge->dropColumn('content_plan', array_values($colsToDrop));
        }
    }
}

