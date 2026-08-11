<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrendBankTable extends Migration
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
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'badge' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Viral',
            ],
            'desk' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'example' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => 'TikTok & Reels',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
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
        $this->forge->createTable('trend_bank', true);
    }

    public function down()
    {
        $this->forge->dropTable('trend_bank', true);
    }
}
