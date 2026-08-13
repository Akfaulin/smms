<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMetaApiLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'endpoint' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'method' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'GET',
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'response_code' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
            ],
            'response_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['sukses', 'gagal'],
                'default'    => 'sukses',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('meta_api_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('meta_api_logs', true);
    }
}
