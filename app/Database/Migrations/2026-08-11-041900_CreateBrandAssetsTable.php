<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBrandAssetsTable extends Migration
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
            'nama_aset' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['logo', 'palette', 'font', 'template', 'foto_produk', 'ikon'],
                'default'    => 'template',
            ],
            'nilai_atau_url' => [
                'type' => 'TEXT',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dibuat_oleh' => [
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
        $this->forge->createTable('brand_assets', true);
    }

    public function down()
    {
        $this->forge->dropTable('brand_assets', true);
    }
}
