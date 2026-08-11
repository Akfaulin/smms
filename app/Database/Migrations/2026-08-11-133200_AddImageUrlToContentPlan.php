<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddImageUrlToContentPlan
 *
 * Menambahkan kolom `image_url` ke tabel `content_plan`
 * untuk menyimpan URL media gambar publik yang akan dipublish ke Instagram Meta Graph API.
 */
class AddImageUrlToContentPlan extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('content_plan', [
            'image_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'design_url',
                'comment'    => 'URL media gambar publik untuk publishing ke Meta API / Instagram',
            ]
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('content_plan', 'image_url');
    }
}
