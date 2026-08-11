<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BrandAssetsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Color Palette
            [
                'nama_aset'      => 'Primary Royal Blue',
                'kategori'       => 'palette',
                'nilai_atau_url' => '#2563eb',
                'keterangan'     => 'Warna utama tombol, header, dan elemen visual dominan brand.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nama_aset'      => 'Secondary Electric Purple',
                'kategori'       => 'palette',
                'nilai_atau_url' => '#7c3aed',
                'keterangan'     => 'Warna aksen AI, fitur inovasi, dan badge khusus.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nama_aset'      => 'Emerald Green Success',
                'kategori'       => 'palette',
                'nilai_atau_url' => '#059669',
                'keterangan'     => 'Warna status published, sukses, dan penawaran hemat.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nama_aset'      => 'Dark Slate Neutral',
                'kategori'       => 'palette',
                'nilai_atau_url' => '#0f172a',
                'keterangan'     => 'Warna teks utama, background dark mode, dan border tegas.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // Typography / Font
            [
                'nama_aset'      => 'DM Sans (Primary Font Body)',
                'kategori'       => 'font',
                'nilai_atau_url' => 'https://fonts.google.com/specimen/DM+Sans',
                'keterangan'     => 'Font utama untuk teks body, caption, dan UI elemen.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nama_aset'      => 'Outfit (Display Font Heading)',
                'kategori'       => 'font',
                'nilai_atau_url' => 'https://fonts.google.com/specimen/Outfit',
                'keterangan'     => 'Font khusus untuk judul banner, poster, dan headline promosi.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // Templates (Figma & Canva Links)
            [
                'nama_aset'      => 'Master Canva Template Feed Instagram 1080x1080',
                'kategori'       => 'template',
                'nilai_atau_url' => 'https://canva.com',
                'keterangan'     => 'Preset 20+ slide postingan Instagram Feed siap pakai.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nama_aset'      => 'Figma Kit Story & TikTok Video Frame 1080x1920',
                'kategori'       => 'template',
                'nilai_atau_url' => 'https://figma.com',
                'keterangan'     => 'Design kit resmi untuk layout Story IG dan cover video TikTok.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            // Logos & Assets
            [
                'nama_aset'      => 'Logo Resmi SMMS HD Transparent (.PNG)',
                'kategori'       => 'logo',
                'nilai_atau_url' => '/images/logo-placeholder.png',
                'keterangan'     => 'Logo resolusi tinggi dengan latar belakang transparan.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nama_aset'      => 'Icon Pack Vector Brand (.SVG)',
                'kategori'       => 'ikon',
                'nilai_atau_url' => '/images/icons-pack.zip',
                'keterangan'     => 'Kumpulan ikon vektor tema sosial media & bisnis.',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $item) {
            $this->db->table('brand_assets')->ignore(true)->insert($item);
        }
    }
}
