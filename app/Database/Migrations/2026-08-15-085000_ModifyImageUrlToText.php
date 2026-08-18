<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyImageUrlToText extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('content_plan', [
            'image_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('content_plan', [
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
        ]);
    }
}
