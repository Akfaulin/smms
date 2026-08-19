<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGeminiApiKeyToBisnis extends Migration
{
    public function up(): void
    {
        $fields = [
            'gemini_api_key' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'meta_ig_username',
            ],
        ];

        $this->forge->addColumn('bisnis', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('bisnis', [
            'gemini_api_key',
        ]);
    }
}
