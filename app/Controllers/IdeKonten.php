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

        $userId       = (int) session('user_id');
        $bisnisId     = (int) session('bisnis_aktif_id');
        $filterStatus = $this->request->getGet('status') ?? 'all';

        $query = $this->model->withRelasi()->byBisnis($bisnisId);

        // Apply status filter tab
        if ($filterStatus === 'pending') {
            $query->where('content_plan.status', 'ide_diajukan');
        } elseif ($filterStatus === 'approved') {
            $query->whereIn('content_plan.status', ['acc_ide', 'in_design', 'review_design', 'acc_final', 'published']);
        } elseif ($filterStatus === 'revision') {
            $query->where('content_plan.status', 'revisi');
        } elseif ($filterStatus === 'rejected') {
            $query->where('content_plan.status', 'ditolak');
        } elseif ($filterStatus === 'overdue') {
            $nowStr = date('Y-m-d H:i:s');
            $query->where('content_plan.tanggal_publish IS NOT NULL')
                  ->where('content_plan.tanggal_publish <', $nowStr)
                  ->whereNotIn('content_plan.status', ['published', 'ditolak']);
        }

        // Poin 3: Sortir default tanggal publish paling mepet
        $sortBy = $this->request->getGet('sort') ?: 'publish_mepet';
        switch ($sortBy) {
            case 'publish_jauh':
                $query->orderBy('CASE WHEN content_plan.tanggal_publish IS NULL THEN 1 ELSE 0 END', 'ASC', false)
                      ->orderBy('content_plan.tanggal_publish', 'DESC')
                      ->orderBy('content_plan.created_at', 'DESC');
                break;
            case 'diajukan_terbaru':
                $query->orderBy('content_plan.created_at', 'DESC');
                break;
            case 'diajukan_terlama':
                $query->orderBy('content_plan.created_at', 'ASC');
                break;
            case 'publish_mepet':
            default:
                $query->orderBy('CASE WHEN content_plan.tanggal_publish IS NULL THEN 1 ELSE 0 END', 'ASC', false)
                      ->orderBy('content_plan.tanggal_publish', 'ASC')
                      ->orderBy('content_plan.created_at', 'DESC');
                break;
        }

        $konten = $query->findAll();

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

        // Calculate summary metrics (filter by bisnis)
        $allIdeData = $this->model->byBisnis($bisnisId)->findAll();
        $nowStr     = date('Y-m-d H:i:s');

        $statTotal    = count($allIdeData);
        $statPending  = count(array_filter($allIdeData, fn($i) => $i['status'] === 'ide_diajukan'));
        $statApproved = count(array_filter($allIdeData, fn($i) => in_array($i['status'], ['acc_ide', 'in_design', 'review_design', 'acc_final', 'published'], true)));
        $statRevisi   = count(array_filter($allIdeData, fn($i) => $i['status'] === 'revisi'));
        $statOverdue  = count(array_filter($allIdeData, fn($i) => !empty($i['tanggal_publish']) && $i['tanggal_publish'] < $nowStr && !in_array($i['status'], ['published', 'ditolak'], true)));

        // Master data untuk form modal (filter by bisnis + global fallback)
        $platforms    = $db->table('platforms')
            ->where('status', 'aktif')
            ->groupStart()
                ->where('bisnis_id', $bisnisId)
                ->orWhere('bisnis_id IS NULL')
            ->groupEnd()
            ->get()->getResultArray();

        $jenisKonten  = $db->table('jenis_konten')
            ->groupStart()
                ->where('bisnis_id', $bisnisId)
                ->orWhere('bisnis_id IS NULL')
            ->groupEnd()
            ->get()->getResultArray();

        $contentTypes = $db->table('content_types')
            ->groupStart()
                ->where('bisnis_id', $bisnisId)
                ->orWhere('bisnis_id IS NULL')
            ->groupEnd()
            ->get()->getResultArray();

        return view('ide_konten/index', [
            'konten'        => $konten,
            'statTotal'     => $statTotal,
            'statPending'   => $statPending,
            'statApproved'  => $statApproved,
            'statRevisi'    => $statRevisi,
            'statOverdue'   => $statOverdue,
            'filterStatus'  => $filterStatus,
            'sortBy'        => $sortBy,
            'platforms'     => $platforms,
            'jenisKonten'   => $jenisKonten,
            'contentTypes'  => $contentTypes,
            'kode_role'     => $role,
            'judul'         => 'Dashboard Ide Konten',
        ]);
    }

    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $contentPlanController = new ContentPlan();
        return $contentPlanController->store();
    }
}
