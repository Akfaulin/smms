<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddCaptionToContentPlan
 *
 * Menambahkan kolom `caption` ke tabel `content_plan` untuk memisahkan
 * text hasil tulisan AI/Copywriter dengan `deskripsi` (brief ide konten).
 */
class AddCaptionToContentPlan extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('content_plan', [
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'deskripsi'
            ]
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('content_plan', 'caption');
    }
}
