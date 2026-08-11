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

    /**
     * POST /dashboard/approval-manager/ai-review/{id}
     * Menjalankan evaluasi AI & analisis kualitas konten/video khusus Manager.
     */
    public function aiReview(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['manager', 'superadmin', 'owner'], true)) {
            return $this->response->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Akses ditolak. Fitur AI Review ini khusus untuk Manager.',
            ])->setStatusCode(403);
        }

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->response->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan.',
            ])->setStatusCode(404);
        }

        try {
            $aiService = new \App\Services\AiService();
            $aiService->preReviewCheck($konten);

            return $this->response->setJSON([
                'status' => 'sukses',
                'pesan'  => 'Analisis AI Review berhasil dijalankan! Hasil evaluasi dapat dilihat pada timeline/log status.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Terjadi kesalahan saat memproses AI: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * POST /dashboard/approval-manager/ai-caption/{id}
     * Minta saran perbaikan/generasi caption AI khusus Manager.
     */
    public function aiCaption(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['manager', 'superadmin', 'owner'], true)) {
            return $this->response->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Akses ditolak.',
            ])->setStatusCode(403);
        }

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->response->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan.',
            ])->setStatusCode(404);
        }

        $userId   = (int) session('user_id');
        $judul    = $konten['judul_konten'];
        $brief    = $konten['deskripsi'] ?? '';
        $platform = 'Instagram & TikTok';

        try {
            $aiService    = new \App\Services\AiService();
            $saranCaption = $aiService->generateCaption($id, $judul, $platform, $brief, $userId);

            return $this->response->setJSON([
                'status' => 'sukses',
                'data'   => [
                    'caption' => $saranCaption,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Gagal memuat saran AI: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}

