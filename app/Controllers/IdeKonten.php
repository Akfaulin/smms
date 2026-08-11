<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;
use App\Services\TransisiKonten;

/**
 * IdeKonten Controller
 *
 * Dashboard khusus untuk Pengajuan, Peninjauan, dan Pelacakan Ide Konten.
 * Memudahkan Creative Team, Manager, dan tim terkait memantau ide secara terpisah
 * tanpa harus menumpuk di halaman Content Plan (Kalender).
 */
class IdeKonten extends BaseController
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
        if (! in_array($role, ['creative_team', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $userId   = (int) session('user_id');
        $filterStatus = $this->request->getGet('status') ?? 'all';

        $query = $this->model->withRelasi();

        // Jika Creative Team / Content Creator: tampilkan ide buatan sendiri (kecuali jika minta 'all')
        if (in_array($role, ['creative_team', 'content_creator'], true)) {
            $query->where('content_plan.dibuat_oleh', $userId);
        }

        // Apply status filter tab
        if ($filterStatus === 'pending') {
            $query->where('content_plan.status', 'ide_diajukan');
        } elseif ($filterStatus === 'approved') {
            $query->whereIn('content_plan.status', ['acc_ide', 'in_design', 'review_design', 'acc_final', 'published']);
        } elseif ($filterStatus === 'revision') {
            $query->where('content_plan.status', 'revisi');
        } elseif ($filterStatus === 'rejected') {
            $query->where('content_plan.status', 'ditolak');
        }

        $konten = $query->orderBy('content_plan.created_at', 'DESC')->findAll();

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
        $baseCountQuery = $db->table('content_plan');
        if (in_array($role, ['creative_team', 'content_creator'], true)) {
            $baseCountQuery->where('dibuat_oleh', $userId);
        }

        $allIdeData = $baseCountQuery->get()->getResultArray();

        $statTotal    = count($allIdeData);
        $statPending  = count(array_filter($allIdeData, fn($i) => $i['status'] === 'ide_diajukan'));
        $statApproved = count(array_filter($allIdeData, fn($i) => in_array($i['status'], ['acc_ide', 'in_design', 'review_design', 'acc_final', 'published'], true)));
        $statRevisi   = count(array_filter($allIdeData, fn($i) => $i['status'] === 'revisi'));

        // Master data untuk form modal
        $platforms    = $db->table('platforms')->where('status', 'aktif')->get()->getResultArray();
        $jenisKonten  = $db->table('jenis_konten')->get()->getResultArray();
        $contentTypes = $db->table('content_types')->get()->getResultArray();

        return view('ide_konten/index', [
            'judul'        => 'Dashboard Ide Konten',
            'konten'       => $konten,
            'statTotal'    => $statTotal,
            'statPending'  => $statPending,
            'statApproved' => $statApproved,
            'statRevisi'   => $statRevisi,
            'filterStatus' => $filterStatus,
            'platforms'    => $platforms,
            'jenisKonten'  => $jenisKonten,
            'contentTypes' => $contentTypes,
            'kode_role'    => $role,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $contentPlanController = new ContentPlan();
        return $contentPlanController->store();
    }
}
