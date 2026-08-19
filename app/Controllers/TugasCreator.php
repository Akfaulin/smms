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

        // Poin 10: Sembunyikan Ide dari Dashboard Creator sebelum di-ACC Manager!
        // Creator HANYA menerima dan melihat tugas desain setelah ide resmi di-ACC (status: acc_ide, in_design, review_design, revisi, acc_final, published).
        $query->whereNotIn('content_plan.status', ['ide_diajukan', 'ditolak']);

        // Content Creator memonitoring tugas yang ditugaskan kepadanya atau tugas unassigned yang siap dikerjakan
        if ($role === 'content_creator') {
            $query->groupStart()
                  ->where('content_plan.assigned_designer', $userId)
                  ->orWhere('content_plan.assigned_designer IS NULL')
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
        } elseif ($filterStatus === 'overdue') {
            $nowStr = date('Y-m-d H:i:s');
            $query->where('content_plan.tanggal_publish IS NOT NULL')
                  ->where('content_plan.tanggal_publish <', $nowStr)
                  ->whereNotIn('content_plan.status', ['published', 'ditolak', 'ide_diajukan']);
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

        // Calculate summary metrics (filter by bisnis & ACC-ed only)
        $baseCountQuery = $db->table('content_plan')
            ->where('bisnis_id', $bisnisId)
            ->whereNotIn('status', ['ide_diajukan', 'ditolak']);

        if ($role === 'content_creator') {
            $baseCountQuery->groupStart()
                           ->where('assigned_designer', $userId)
                           ->orWhere('assigned_designer IS NULL')
                           ->groupEnd();
        }

        $allTaskData = $baseCountQuery->get()->getResultArray();
        $nowStr      = date('Y-m-d H:i:s');

        $statInDesign   = count(array_filter($allTaskData, fn($i) => in_array($i['status'], ['acc_ide', 'in_design'], true)));
        $statReview     = count(array_filter($allTaskData, fn($i) => $i['status'] === 'review_design'));
        $statRevisi     = count(array_filter($allTaskData, fn($i) => $i['status'] === 'revisi'));
        $statCompleted  = count(array_filter($allTaskData, fn($i) => in_array($i['status'], ['acc_final', 'published'], true)));
        $statOverdue    = count(array_filter($allTaskData, fn($i) => !empty($i['tanggal_publish']) && $i['tanggal_publish'] < $nowStr && !in_array($i['status'], ['published', 'ditolak', 'ide_diajukan'], true)));

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
            'statOverdue'   => $statOverdue,
            'filterStatus'  => $filterStatus,
            'sortBy'        => $sortBy,
            'platforms'     => $platforms,
            'jenisKonten'   => $jenisKonten,
            'contentTypes'  => $contentTypes,
            'kode_role'     => $role,
            'roleNow'       => $role,  // Alias untuk view (window.ROLE)
        ]);
    }
}
