<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;
use App\Services\TransisiKonten;

/**
 * TugasCreator Controller
 *
 * Dashboard khusus untuk Content Creator (Designer) memonitoring seluruh tugas desain,
 * penulisan caption AI, dan status pengajuan ke Manager.
 */
class TugasCreator extends BaseController
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

        if (! in_array($role, ['content_creator', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $userId       = (int) session('user_id');
        $bisnisId     = (int) session('bisnis_aktif_id');
        $filterStatus = $this->request->getGet('status') ?? 'all';

        $query = $this->model->withRelasi()->byBisnis($bisnisId);

        // Content Creator memonitoring tugas yang ditugaskan kepadanya (assigned_designer) atau dibuat olehnya
        if ($role === 'content_creator') {
            $query->groupStart()
                  ->where('content_plan.assigned_designer', $userId)
                  ->orWhere('content_plan.dibuat_oleh', $userId)
                  ->groupEnd();
        }

        // Apply status filter tab
        if ($filterStatus === 'in_design') {
            $query->whereIn('content_plan.status', ['acc_ide', 'in_design']);
        } elseif ($filterStatus === 'review_design') {
            $query->where('content_plan.status', 'review_design');
        } elseif ($filterStatus === 'revision') {
            $query->where('content_plan.status', 'revisi');
        } elseif ($filterStatus === 'completed') {
            $query->whereIn('content_plan.status', ['acc_final', 'published']);
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

        // Calculate summary metrics (filter by bisnis)
        $baseCountQuery = $db->table('content_plan')->where('bisnis_id', $bisnisId);
        if ($role === 'content_creator') {
            $baseCountQuery->groupStart()
                           ->where('assigned_designer', $userId)
                           ->orWhere('dibuat_oleh', $userId)
                           ->groupEnd();
        }

        $allTaskData = $baseCountQuery->get()->getResultArray();

        $statInDesign   = count(array_filter($allTaskData, fn($i) => in_array($i['status'], ['acc_ide', 'in_design'], true)));
        $statReview     = count(array_filter($allTaskData, fn($i) => $i['status'] === 'review_design'));
        $statRevisi     = count(array_filter($allTaskData, fn($i) => $i['status'] === 'revisi'));
        $statCompleted  = count(array_filter($allTaskData, fn($i) => in_array($i['status'], ['acc_final', 'published'], true)));

        // Master data untuk form & modal (filter by bisnis + global fallback)
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

        return view('tugas_creator/index', [
            'judul'         => 'Dashboard Tugas Content Creator',
            'konten'        => $konten,
            'statInDesign'  => $statInDesign,
            'statReview'    => $statReview,
            'statRevisi'    => $statRevisi,
            'statCompleted' => $statCompleted,
            'filterStatus'  => $filterStatus,
            'platforms'     => $platforms,
            'jenisKonten'   => $jenisKonten,
            'contentTypes'  => $contentTypes,
            'kode_role'     => $role,
        ]);
    }
}
