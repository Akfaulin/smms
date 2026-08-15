<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterBrandAssetsKategori extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('brand_assets', [
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'template',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('brand_assets', [
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['logo', 'palette', 'font', 'template', 'foto_produk', 'ikon', 'guideline'],
                'default'    => 'template',
            ],
        ]);
    }
}
