<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMetaCredentialsToBisnis extends Migration
{
    public function up(): void
    {
        $fields = [
            'meta_app_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'urutan',
            ],
            'meta_app_secret' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'meta_app_id',
            ],
            'meta_access_token' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'meta_app_secret',
            ],
            'meta_ig_account_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'meta_access_token',
            ],
            'meta_ig_username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'meta_ig_account_id',
            ],
        ];

        $this->forge->addColumn('bisnis', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('bisnis', [
            'meta_app_id',
            'meta_app_secret',
            'meta_access_token',
            'meta_ig_account_id',
            'meta_ig_username',
        ]);
    }
}
