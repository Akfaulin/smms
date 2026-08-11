<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;
use App\Services\TransisiKonten;

/**
 * JadwalUpload Controller
 *
 * Dashboard khusus Admin Media Sosial untuk memantau jadwal tayang postingan,
 * memposting konten yang sudah di-ACC Final, dan menginput URL link postingan.
 */
class JadwalUpload extends BaseController
{
    protected ContentPlanModel $model;
    protected TransisiKonten $transisiService;

    public function __construct()
    {
        $this->model           = new ContentPlanModel();
        $this->transisiService = new TransisiKonten();
    }

    public function index()
    {
        $db   = \Config\Database::connect();
        $role = session('kode_role');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $filterStatus = $this->request->getGet('status') ?? 'all';
        $todayStr     = date('Y-m-d');

        $query = $this->model->withRelasi();

        // Apply status filter tab
        if ($filterStatus === 'ready') {
            $query->where('content_plan.status', 'acc_final');
        } elseif ($filterStatus === 'today') {
            $query->where('content_plan.tanggal_publish', $todayStr);
        } elseif ($filterStatus === 'published') {
            $query->where('content_plan.status', 'published');
        } else {
            // Default filter list untuk Admin Medsos: fokus ke acc_final & published
            if ($role === 'admin_medsos') {
                $query->whereIn('content_plan.status', ['acc_final', 'published']);
            }
        }

        $konten = $query->orderBy('content_plan.tanggal_publish', 'ASC')->findAll();

        // Join platforms
        foreach ($konten as &$k) {
            $plats = $db->table('content_platforms cp')
                ->select('p.id, p.nama_platform')
                ->join('platforms p', 'p.id = cp.platform_id')
                ->where('cp.content_id', $k['id'])
                ->get()->getResultArray();
            $k['platforms']    = $plats;
            $k['platform_str'] = implode(', ', array_column($plats, 'nama_platform'));
        }
        unset($k);

        // Calculate summary metrics
        $allData = $db->table('content_plan')->get()->getResultArray();

        $statReady     = count(array_filter($allData, fn($i) => $i['status'] === 'acc_final'));
        $statToday     = count(array_filter($allData, fn($i) => !empty($i['tanggal_publish']) && date('Y-m-d', strtotime($i['tanggal_publish'])) === $todayStr));
        $statPublished = count(array_filter($allData, fn($i) => $i['status'] === 'published'));
        $statTotal     = count($allData);

        // Master data untuk form & modal
        $platforms    = $db->table('platforms')->where('status', 'aktif')->get()->getResultArray();
        $jenisKonten  = $db->table('jenis_konten')->get()->getResultArray();
        $contentTypes = $db->table('content_types')->get()->getResultArray();

        return view('jadwal_upload/index', [
            'judul'         => 'Dashboard Jadwal & Upload Admin Medsos',
            'konten'        => $konten,
            'statReady'     => $statReady,
            'statToday'     => $statToday,
            'statPublished' => $statPublished,
            'statTotal'     => $statTotal,
            'filterStatus'  => $filterStatus,
            'platforms'     => $platforms,
            'jenisKonten'   => $jenisKonten,
            'contentTypes'  => $contentTypes,
            'kode_role'     => $role,
        ]);
    }
}
