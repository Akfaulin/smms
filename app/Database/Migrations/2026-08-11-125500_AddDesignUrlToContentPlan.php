<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddDesignUrlToContentPlan
 *
 * Menambahkan kolom `design_url` ke tabel `content_plan`
 * untuk menyimpan link desain Canva/Figma per konten.
 */
class AddDesignUrlToContentPlan extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('content_plan', [
            'design_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'caption',
                'comment'    => 'Link desain Canva/Figma untuk konten ini',
            ]
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('content_plan', 'design_url');
    }
}
