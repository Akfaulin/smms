<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TrendBankSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'judul'      => 'POV & Problem Relatable',
                'badge'      => 'Highly Viral',
                'desk'       => 'Tampilkan situasi sehari-hari audiens yang menghibur atau membuat mereka merasa relate.',
                'example'    => '"POV: Ketika kamu udah coba 10 cara tapi tetap aja gagal..."',
                'category'   => 'TikTok & Reels',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'judul'      => 'Stop Scrolling Hook',
                'badge'      => 'High Retention',
                'desk'       => 'Kalimat pembuka tajam di 3 detik pertama yang menahan audiens agar tidak menggeser layar.',
                'example'    => '"Jangan beli ini sebelum kamu dengar rahasia ini!"',
                'category'   => 'Short Video',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'judul'      => 'Before vs After Transformation',
                'badge'      => 'Visual Proof',
                'desk'       => 'Perbandingan hasil penggunaan produk/jasa dari kondisi awal ke kondisi memuaskan.',
                'example'    => '"Hasil perombakan desain feed sosial media dalam 7 hari..."',
                'category'   => 'Reels & Carousel',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'judul'      => '3 Kesalahan Fatal (3 Fatal Mistakes)',
                'badge'      => 'Edukasi',
                'desk'       => 'Peringatan edukatif yang memicu rasa ingin tahu audiens akan kesalahan yang tidak mereka sadari.',
                'example'    => '"3 kesalahan pembuatan ide konten yang buat omset kamu stagnan..."',
                'category'   => 'Edukasi & Tips',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'judul'      => 'Quick Tutorial Under 15s',
                'badge'      => 'High Completion',
                'desk'       => 'Tutorial singkat & padat tanpa bertele-tele langsung memberikan solusi berharga.',
                'example'    => '"Cara mudah membuat AI Caption dalam 10 detik tanpa aplikasi..."',
                'category'   => 'Tutorial',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'judul'      => 'Behind The Scenes (BTS)',
                'badge'      => 'Build Trust',
                'desk'       => 'Perlihatkan proses kerja tim, persiapan pesanan, atau kehebohan kantor.',
                'example'    => '"Di balik layar penyiapan promo gajian tim marketing kami..."',
                'category'   => 'Branding',
                'status'     => 'aktif',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $item) {
            $this->db->table('trend_bank')->ignore(true)->insert($item);
        }
    }
}
