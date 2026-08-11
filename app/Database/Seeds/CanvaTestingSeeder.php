<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * CanvaTestingSeeder
 *
 * Mengisi data dummy content_plan dengan status 'acc_ide' atau 'in_design'
 * serta variasi kombinasi platform & jenis_konten untuk menguji Smart Canva Link.
 */
class CanvaTestingSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        // 1. Ambil ID User Creator & Manager
        $creator = $db->table('users')->where('email', 'creator@smm.local')->get()->getRowArray();
        $manager = $db->table('users')->where('email', 'manager@smm.local')->get()->getRowArray();

        $creatorId = $creator['id'] ?? 1;
        $managerId = $manager['id'] ?? 1;

        // 2. Ambil Master Data
        $jenisMap = [];
        foreach ($db->table('jenis_konten')->get()->getResultArray() as $j) {
            $jenisMap[$j['nama_jenis']] = $j['id'];
        }

        $platformMap = [];
        foreach ($db->table('platforms')->get()->getResultArray() as $p) {
            $platformMap[$p['nama_platform']] = $p['id'];
        }

        $pillarMap = [];
        foreach ($db->table('content_types')->get()->getResultArray() as $c) {
            $pillarMap[$c['nama_type']] = $c['id'];
        }

        // 3. Skenario Konten Testing Smart Canva Link
        $dummyItems = [
            [
                'judul_konten'    => 'Promo Diskon Kemerdekaan 17-an (Carousel IG)',
                'deskripsi'       => 'Draft visual slide carousel berisi 5 slide promo diskon hemat.',
                'jenis'           => 'Carousel',
                'platforms'       => ['Instagram'],
                'pillar'          => 'Promosi',
                'status'          => 'in_design',
                'tanggal_publish' => date('Y-m-d', strtotime('+3 days')),
            ],
            [
                'judul_konten'    => 'Behind The Scene Shooting Iklan (Story IG)',
                'deskripsi'       => 'Video singkat cuplikan suasana shooting tim di kantor.',
                'jenis'           => 'Story',
                'platforms'       => ['Instagram'],
                'pillar'          => 'Behind the Scene',
                'status'          => 'in_design',
                'tanggal_publish' => date('Y-m-d', strtotime('+2 days')),
            ],
            [
                'judul_konten'    => 'Review Jujur Fitur Baru App (TikTok Video)',
                'deskripsi'       => 'Video reaksi cepat penggunaan fitur baru dengan gaya santai.',
                'jenis'           => 'Reels / Video',
                'platforms'       => ['TikTok'],
                'pillar'          => 'Edukasi',
                'status'          => 'in_design',
                'tanggal_publish' => date('Y-m-d', strtotime('+5 days')),
            ],
            [
                'judul_konten'    => 'Tutorial Panduan Lengkap SMMS (YouTube Video)',
                'deskripsi'       => 'Video walkthrough panduan penggunaan sistem SMMS dari A sampai Z.',
                'jenis'           => 'Reels / Video',
                'platforms'       => ['YouTube'],
                'pillar'          => 'Edukasi',
                'status'          => 'in_design',
                'tanggal_publish' => date('Y-m-d', strtotime('+7 days')),
            ],
            [
                'judul_konten'    => 'Infografis Ringkasan Performa Tim (Facebook Post)',
                'deskripsi'       => 'Gambar tunggal berisi rincian angka pencapaian bulanan.',
                'jenis'           => 'Static Post',
                'platforms'       => ['Facebook'],
                'pillar'          => 'Inspirasi',
                'status'          => 'acc_ide',
                'tanggal_publish' => date('Y-m-d', strtotime('+4 days')),
            ],
        ];

        foreach ($dummyItems as $item) {
            // Insert ke content_plan
            $dataInsert = [
                'judul_konten'      => $item['judul_konten'],
                'deskripsi'         => $item['deskripsi'],
                'tanggal_publish'   => $item['tanggal_publish'],
                'jenis_konten_id'   => $jenisMap[$item['jenis']] ?? null,
                'content_type_id'   => $pillarMap[$item['pillar']] ?? null,
                'status'            => $item['status'],
                'dibuat_oleh'       => $creatorId,
                'assigned_designer' => $creatorId,
                'assigned_uploader' => null,
                'caption'           => null,
                'design_url'        => null, // Null agar menguji Smart Canva Link
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $db->table('content_plan')->insert($dataInsert);
            $contentId = $db->insertID();

            // Insert ke content_platforms
            foreach ($item['platforms'] as $pName) {
                if (isset($platformMap[$pName])) {
                    $db->table('content_platforms')->insert([
                        'content_id'  => $contentId,
                        'platform_id' => $platformMap[$pName],
                    ]);
                }
            }

            // Insert audit trail status log
            $db->table('content_status_log')->insert([
                'content_id'  => $contentId,
                'status_lama' => 'ide_diajukan',
                'status_baru' => 'acc_ide',
                'user_id'     => $managerId,
                'catatan'     => 'Ide disetujui Manager, siap dikerjakan desainnya.',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-1 hour')),
            ]);

            if ($item['status'] === 'in_design') {
                $db->table('content_status_log')->insert([
                    'content_id'  => $contentId,
                    'status_lama' => 'acc_ide',
                    'status_baru' => 'in_design',
                    'user_id'     => $creatorId,
                    'catatan'     => 'Content Creator mulai proses desain.',
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo "  - CanvaTestingSeeder: " . count($dummyItems) . " data konten testing berhasil dibuat.\n";
    }
}
