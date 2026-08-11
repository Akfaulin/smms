<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;

/**
 * KalenderTayang Controller
 *
 * Dashboard khusus Admin Media Sosial untuk visualisasi Kalender Tayang Medsos,
 * rekomendasi jam posting terbaik per platform, dan eksekusi publish.
 */
class KalenderTayang extends BaseController
{
    protected ContentPlanModel $model;

    public function __construct()
    {
        $this->model = new ContentPlanModel();
    }

    public function index()
    {
        $db   = \Config\Database::connect();
        $role = session('kode_role');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $allData = $this->model->withRelasi()
            ->where('content_plan.tanggal_publish IS NOT NULL')
            ->orderBy('content_plan.tanggal_publish', 'ASC')
            ->findAll();

        foreach ($allData as &$k) {
            $plats = $db->table('content_platforms cp')
                ->select('p.id, p.nama_platform')
                ->join('platforms p', 'p.id = cp.platform_id')
                ->where('cp.content_id', $k['id'])
                ->get()->getResultArray();
            $k['platforms']    = $plats;
            $k['platform_str'] = implode(', ', array_column($plats, 'nama_platform'));
        }
        unset($k);

        // Rekomendasi Waktu Posting Terbaik
        $bestPostingTimes = [
            ['platform' => 'Instagram', 'jam' => '11:00 WIB & 19:00 WIB', 'catatan' => 'Peak engagement saat jam istirahat siang & waktu santai malam.'],
            ['platform' => 'TikTok', 'jam' => '12:00 WIB & 20:00 WIB', 'catatan' => 'Tingkat penyebaran FYP tertinggi pada makan siang & sebelum tidur.'],
            ['platform' => 'Facebook', 'jam' => '09:00 WIB & 13:00 WIB', 'catatan' => 'Aktivitas audiens dewasa di sela jam kerja & pasca istirahat.'],
            ['platform' => 'LinkedIn', 'jam' => '08:00 WIB & 10:00 WIB', 'catatan' => 'Waktu terbaik saat profesional membuka feed di awal jam kantor.']
        ];

        // Format event list untuk JS Kalender Grid
        $events = array_map(function($i) {
            return [
                'id'           => $i['id'],
                'judul'        => $i['judul_konten'],
                'tgl'          => date('Y-m-d', strtotime($i['tanggal_publish'])),
                'status'       => $i['status'],
                'platform_str' => $i['platform_str'],
                'nama_pembuat' => $i['nama_pembuat'],
            ];
        }, $allData);

        return view('kalender_tayang/index', [
            'judul'            => 'Kalender Tayang Medsos',
            'konten'           => $allData,
            'events'           => $events,
            'bestPostingTimes' => $bestPostingTimes,
            'kode_role'        => $role,
        ]);
    }
}
