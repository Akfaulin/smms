<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;
use App\Services\TransisiKonten;

/**
 * ApprovalManager Controller
 *
 * Dashboard khusus Manager untuk menyetujui (Approval Gatekeeper), meminta revisi,
 * atau menolak ide dan hasil desain visual secara terpusat.
 */
class ApprovalManager extends BaseController
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

        if (! in_array($role, ['manager', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $filterStatus = $this->request->getGet('status') ?? 'all';

        $query = $this->model->withRelasi();

        // Apply status filter tab
        if ($filterStatus === 'pending_ide') {
            $query->where('content_plan.status', 'ide_diajukan');
        } elseif ($filterStatus === 'pending_design') {
            $query->where('content_plan.status', 'review_design');
        } elseif ($filterStatus === 'approved') {
            $query->whereIn('content_plan.status', ['acc_ide', 'in_design', 'acc_final', 'published']);
        } elseif ($filterStatus === 'revision') {
            $query->where('content_plan.status', 'revisi');
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
        $allData = $db->table('content_plan')->get()->getResultArray();

        $statIdePending    = count(array_filter($allData, fn($i) => $i['status'] === 'ide_diajukan'));
        $statDesignPending = count(array_filter($allData, fn($i) => $i['status'] === 'review_design'));
        $statApproved      = count(array_filter($allData, fn($i) => in_array($i['status'], ['acc_ide', 'in_design', 'acc_final', 'published'], true)));
        $statRevisi        = count(array_filter($allData, fn($i) => $i['status'] === 'revisi'));

        // Master data untuk form & modal
        $platforms    = $db->table('platforms')->where('status', 'aktif')->get()->getResultArray();
        $jenisKonten  = $db->table('jenis_konten')->get()->getResultArray();
        $contentTypes = $db->table('content_types')->get()->getResultArray();

        return view('approval_manager/index', [
            'judul'             => 'Dashboard Approval Manager',
            'konten'            => $konten,
            'statIdePending'    => $statIdePending,
            'statDesignPending' => $statDesignPending,
            'statApproved'      => $statApproved,
            'statRevisi'        => $statRevisi,
            'filterStatus'      => $filterStatus,
            'platforms'         => $platforms,
            'jenisKonten'       => $jenisKonten,
            'contentTypes'      => $contentTypes,
            'kode_role'         => $role,
        ]);
    }
}
