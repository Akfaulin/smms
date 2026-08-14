<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTanggalPublishToDatetime extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('content_plan', [
            'tanggal_publish' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('content_plan', [
            'tanggal_publish' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }
}
