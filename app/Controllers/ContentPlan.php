<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ContentPlanModel;
use App\Services\TransisiKonten;

/**
 * ContentPlan Controller
 *
 * Menangani CRUD konten dan endpoint transition status.
 *
 * Endpoint transition sesuai §6:
 *   POST /dashboard/content-plan/transition/{id}
 *   Body: { status_baru: string, catatan?: string }
 *
 * SEMUA perubahan status WAJIB melalui endpoint ini — tidak boleh
 * ada form lain yang langsung mengubah kolom `status` (§4 & §5).
 */
class ContentPlan extends BaseController
{
    private ContentPlanModel $model;
    private TransisiKonten $transisiService;

    public function __construct()
    {
        $this->model           = new ContentPlanModel();
        $this->transisiService = new TransisiKonten();
    }

    // =========================================================================
    // LIST & KALENDER
    // =========================================================================

    /**
     * GET /dashboard/content-plan
     * Tampilkan daftar konten + kalender.
     * Semua role boleh melihat konten (§5).
     */
    public function index(): string
    {
        $db       = \Config\Database::connect();
        $role     = session('kode_role');
        $userId   = session('user_id');
        $bisnisId = (int) session('bisnis_aktif_id');

        // Filter: default 'all' agar kalender & daftar konten langsung tampil lengkap
        $defaultView = 'all';
        $viewMode    = $this->request->getGet('view') ?? $defaultView;

        // Filter by bisnis aktif
        $query = $this->model->withRelasi()->byBisnis($bisnisId);

        // Poin 10 & 14: Kalender & Content Plan Content Creator HANYA menampilkan tugas aktif yang ditugaskan kepada desainer tersebut
        if ($role === 'content_creator' && $viewMode !== 'my_ideas') {
            $query->whereNotIn('content_plan.status', ['ide_diajukan', 'ditolak']);
            $query->groupStart()
                  ->where('content_plan.assigned_designer', $userId)
                  ->orWhere('content_plan.dibuat_oleh', $userId)
                  ->orWhere('content_plan.assigned_designer IS NULL')
                  ->groupEnd();
        }

        if ($viewMode === 'my_tasks') {
            if ($role === 'manager') {
                $query->whereIn('content_plan.status', ['ide_diajukan', 'review_design']);
            } elseif ($role === 'creative_team') {
                $query->groupStart()
                      ->whereIn('content_plan.status', ['ide_diajukan', 'revisi'])
                      ->where('content_plan.dibuat_oleh', $userId)
                      ->groupEnd();
            } elseif ($role === 'content_creator') {
                $query->groupStart()
                      ->whereIn('content_plan.status', ['acc_ide', 'in_design', 'revisi'])
                      ->groupStart()
                          ->where('content_plan.assigned_designer', $userId)
                          ->orWhere('content_plan.assigned_designer IS NULL')
                      ->groupEnd()
                      ->groupEnd();
            } elseif ($role === 'admin_medsos') {
                $query->groupStart()
                      ->where('content_plan.status', 'acc_final')
                      ->groupStart()
                          ->where('content_plan.assigned_uploader', $userId)
                          ->orWhere('content_plan.dibuat_oleh', $userId)
                          ->orWhere('content_plan.assigned_uploader IS NULL')
                      ->groupEnd()
                      ->groupEnd();
            }
        } elseif ($viewMode === 'my_ideas') {
            $query->where('content_plan.dibuat_oleh', $userId);
        } elseif ($viewMode === 'overdue') {
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

        // Hitung total konten yang lewat tenggat (overdue) untuk tab & badge
        $nowStr = date('Y-m-d H:i:s');
        $overdueBld = $this->model->byBisnis($bisnisId)
            ->where('content_plan.tanggal_publish IS NOT NULL')
            ->where('content_plan.tanggal_publish <', $nowStr)
            ->whereNotIn('content_plan.status', ['published', 'ditolak']);
        if ($role === 'content_creator') {
            $overdueBld->whereNotIn('content_plan.status', ['ide_diajukan', 'ditolak', 'published'])
                       ->groupStart()
                           ->where('content_plan.assigned_designer', $userId)
                           ->orWhere('content_plan.dibuat_oleh', $userId)
                           ->orWhere('content_plan.assigned_designer IS NULL')
                       ->groupEnd();
        }
        $totalOverdue = (int) $overdueBld->countAllResults();

        // Sertakan platforms per konten (GROUP_CONCAT)
        foreach ($konten as &$k) {
            $plats = $db->table('content_platforms cp')
                ->select('p.id, p.nama_platform')
                ->join('platforms p', 'p.id = cp.platform_id')
                ->where('cp.content_id', $k['id'])
                ->get()->getResultArray();
            $k['platforms'] = $plats;
            $k['platform_str'] = implode(', ', array_column($plats, 'nama_platform'));
        }
        unset($k);

        // Master data untuk form (filter by bisnis aktif + global fallback)
        $bisnisId     = (int) session('bisnis_aktif_id');
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

        // List user untuk assignment
        $allUsers     = $db->table('users u')
            ->select('u.id, u.nama, r.kode_role')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.status', 'aktif')
            ->get()->getResultArray();

        $designers    = array_filter($allUsers, fn($u) => in_array($u['kode_role'], ['content_creator', 'manager', 'superadmin', 'owner'], true));
        $uploaders    = array_filter($allUsers, fn($u) => in_array($u['kode_role'], ['admin_medsos', 'manager', 'superadmin', 'owner'], true));

        return view('content_plan/index', [
            'konten'       => $konten,
            'platforms'    => $platforms,
            'jenisKonten'  => $jenisKonten,
            'contentTypes' => $contentTypes,
            'designers'    => array_values($designers),
            'uploaders'    => array_values($uploaders),
            'kode_role'    => $role,
            'viewMode'     => $viewMode,
            'sortBy'       => $sortBy,
            'totalOverdue' => $totalOverdue,
            'judul'        => 'Content Plan',
        ]);
    }

    // =========================================================================
    // STORE (Buat Ide Baru)
    // =========================================================================

    /**
     * POST /dashboard/content-plan/store
     * Buat ide konten baru — status otomatis 'ide_diajukan'.
     * Role yang boleh: superadmin, owner, manager, content_creator (§5).
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');

        if (in_array($kodeRole, ['admin_medsos', 'content_creator'], true)) {
            return $this->jsonGagal('Anda tidak memiliki akses untuk membuat ide konten.', 403);
        }

        $db = \Config\Database::connect();

        $assignedDesigner = $this->request->getPost('assigned_designer') ?: null;
        if (! $assignedDesigner) {
            if ($kodeRole === 'content_creator') {
                $assignedDesigner = $userId;
            } else {
                $creatorUser = $db->table('users u')
                    ->select('u.id')
                    ->join('roles r', 'r.id = u.role_id', 'left')
                    ->where('r.kode_role', 'content_creator')
                    ->where('u.status', 'aktif')
                    ->get()->getRowArray();
                $assignedDesigner = $creatorUser['id'] ?? null;
            }
        }

        $assignedUploader = $this->request->getPost('assigned_uploader') ?: null;
        if (! $assignedUploader) {
            $uploaderUser = $db->table('users u')
                ->select('u.id')
                ->join('roles r', 'r.id = u.role_id', 'left')
                ->where('r.kode_role', 'admin_medsos')
                ->where('u.status', 'aktif')
                ->get()->getRowArray();
            $assignedUploader = $uploaderUser['id'] ?? null;
        }

        $rawTgl = $this->request->getPost('tanggal_publish');
        $tglPublish = $rawTgl ? date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $rawTgl))) : null;

        $data = [
            'bisnis_id'         => (int) session('bisnis_aktif_id'),
            'judul_konten'      => $this->request->getPost('judul_konten'),
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'tanggal_publish'   => $tglPublish,
            'jenis_konten_id'   => $this->request->getPost('jenis_konten_id') ?: null,
            'content_type_id'   => $this->request->getPost('content_type_id') ?: null,
            'assigned_designer' => $assignedDesigner,
            'assigned_uploader' => $assignedUploader,
        ];

        $contentId = $this->model->buatIde($data, $userId);
        if (! $contentId) {
            return $this->jsonGagal('Validasi gagal: ' . implode(', ', $this->model->errors()), 422);
        }

        // Simpan platforms (pivot)
        $platforms = $this->request->getPost('platforms') ?? [];
        if (! empty($platforms)) {
            $db = \Config\Database::connect();
            foreach ($platforms as $platId) {
                $db->table('content_platforms')->insert([
                    'content_id'  => $contentId,
                    'platform_id' => (int) $platId,
                ]);
            }
        }

        // Catat status awal ke log
        $logModel = new \App\Models\ContentStatusLogModel();
        $logModel->insert([
            'content_id'  => $contentId,
            'status_lama' => null,
            'status_baru' => 'ide_diajukan',
            'user_id'     => $userId,
            'catatan'     => 'Ide baru diajukan.',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        // Kirim notifikasi ke manager
        $notifService = new \App\Services\NotificationService();
        $kontenBaru   = $this->model->find($contentId);
        if ($kontenBaru) {
            $notifService->notifikasiTransisi($kontenBaru, '', 'ide_diajukan', $userId);
        }

        return $this->jsonSukses('Ide konten berhasil diajukan.', [
            'content_id' => $contentId,
            'status'     => 'ide_diajukan',
        ], 201);
    }

    // =========================================================================
    // UPDATE (Data Konten — BUKAN Status)
    // =========================================================================

    /**
     * POST /dashboard/content-plan/update/{id}
     * Edit data konten (judul, deskripsi, dll) — BUKAN status.
     * Role: superadmin, owner, manager boleh edit semua;
     *       content_creator hanya boleh edit miliknya (sebelum acc_final).
     */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        // Cek permission edit (§5)
        if (! $this->bolehEdit($konten, $userId, $kodeRole)) {
            return $this->jsonGagal('Anda tidak berwenang mengedit konten ini.', 403);
        }

        // Cegah edit setelah acc_final (§5)
        if (in_array($konten['status'], ['acc_final', 'published', 'ditolak'], true)
            && ! in_array($kodeRole, ['superadmin', 'owner'], true)
        ) {
            return $this->jsonGagal(
                "Konten berstatus '{$konten['status']}' tidak dapat diedit.", 403
            );
        }

        $rawTgl = $this->request->getPost('tanggal_publish');
        $tglPublish = $rawTgl ? date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $rawTgl))) : null;

        $data = [
            'judul_konten'    => $this->request->getPost('judul_konten'),
            'deskripsi'       => $this->request->getPost('deskripsi'),
            'tanggal_publish' => $tglPublish,
            'jenis_konten_id' => $this->request->getPost('jenis_konten_id') ?: null,
            'content_type_id' => $this->request->getPost('content_type_id') ?: null,
        ];

        // Update caption jika dikirim
        if ($this->request->getPost('caption') !== null) {
            $data['caption'] = trim((string) $this->request->getPost('caption')) ?: null;
        }

        // Update design_url & image_url jika dikirim
        if ($this->request->getPost('design_url') !== null) {
            $data['design_url'] = trim((string) $this->request->getPost('design_url')) ?: null;
        }
        if ($this->request->getPost('image_url') !== null) {
            $iUrl = trim((string) $this->request->getPost('image_url'));
            if (! empty($iUrl) && str_contains($iUrl, 'drive.google.com')) {
                $graphService = new \App\Services\GraphApiService();
                $data['image_url'] = $graphService->convertDriveLink($iUrl);
            } else {
                $data['image_url'] = $iUrl ?: null;
            }
        }

        // Opsional: assigned_designer & assigned_uploader boleh diset oleh manager/owner/superadmin
        if (in_array($kodeRole, ['superadmin', 'owner', 'manager'], true)) {
            if ($this->request->getPost('assigned_designer') !== null) {
                $data['assigned_designer'] = $this->request->getPost('assigned_designer') ?: null;
            }
            if ($this->request->getPost('assigned_uploader') !== null) {
                $data['assigned_uploader'] = $this->request->getPost('assigned_uploader') ?: null;
            }
        }

        if (! $this->model->update($id, $data)) {
            $errors = $this->model->errors();
            return $this->jsonGagal('Validasi gagal: ' . implode(', ', $errors), 422);
        }

        // Update platforms pivot jika dikirim
        $platforms = $this->request->getPost('platforms');
        if (is_array($platforms)) {
            $db = \Config\Database::connect();
            $db->table('content_platforms')->where('content_id', $id)->delete();
            foreach ($platforms as $platId) {
                if ($platId) {
                    $db->table('content_platforms')->insert([
                        'content_id'  => $id,
                        'platform_id' => (int) $platId,
                    ]);
                }
            }
        }

        return $this->jsonSukses('Data konten berhasil diperbarui.');
    }

    // =========================================================================
    // TRANSITION (Perubahan Status — INTI TAHAP 2)
    // =========================================================================

    /**
     * POST /dashboard/content-plan/transition/{id}
     *
     * Endpoint SATU-SATUNYA untuk mengubah status konten.
     * Body JSON atau form: { status_baru: string, catatan?: string }
     *
     * Alur:
     *   1. Ambil input
     *   2. Panggil TransisiKonten::transition() — validasi + update DB + log
     *   3. Return JSON response
     *
     * Sesuai §6 dan §4.
     */
    public function transition(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId     = (int) session('user_id');
        $json       = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $statusBaru = trim((string) ($this->request->getPost('status_baru') ?? $json?->status_baru ?? $this->request->getVar('status_baru') ?? ''));
        $catatan    = trim((string) ($this->request->getPost('catatan') ?? $json?->catatan ?? $this->request->getVar('catatan') ?? ''));
        $linkPost   = trim((string) ($this->request->getPost('link_postingan') ?? $json?->link_postingan ?? $this->request->getVar('link_postingan') ?? ''));

        if (empty($statusBaru)) {
            return $this->jsonGagal('Parameter status_baru wajib diisi.', 422);
        }

        // Serahkan sepenuhnya ke service — tidak ada validasi manual di sini
        $hasil = $this->transisiService->transition($id, $statusBaru, $userId, $catatan);

        if (! $hasil['ok']) {
            return $this->jsonGagal($hasil['pesan'], 422);
        }

        // Tahap 5: Jika status menjadi published, simpan bukti_upload
        if ($statusBaru === 'published' && !empty($linkPost)) {
            $db = \Config\Database::connect();

            // Bug #4 Fix: ambil platform_id dari request, atau fallback ke platform
            // pertama yang terdaftar di konten ini.
            $platformId = (int) ($this->request->getPost('platform_id') ?? $json?->platform_id ?? 0);
            if (! $platformId) {
                $cpRow = $db->table('content_platforms')
                    ->where('content_id', $id)
                    ->get()->getRowArray();
                $platformId = $cpRow ? (int) $cpRow['platform_id'] : null;
            }

            $db->table('bukti_upload')->insert([
                'content_id'     => $id,
                'platform_id'    => $platformId ?: null,
                'link_postingan' => $linkPost,
                'uploaded_by'    => $userId,
                'uploaded_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->jsonSukses($hasil['pesan'], [
            'status_lama' => $hasil['status_lama'],
            'status_baru' => $hasil['status_baru'],
            'label_baru'  => TransisiKonten::labelStatus($hasil['status_baru']),
        ]);
    }

    // =========================================================================
    // LOG RIWAYAT STATUS
    // =========================================================================

    /**
     * GET /dashboard/content-plan/{id}/log
     * Ambil riwayat perubahan status konten (§6).
     * Semua role boleh melihat (§5: lihat semua konten).
     */
    public function log(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $konten = $this->model->withRelasi()->find($id);
        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        $db = \Config\Database::connect();
        $plats = $db->table('content_platforms cp')
            ->select('p.id, p.nama_platform')
            ->join('platforms p', 'p.id = cp.platform_id')
            ->where('cp.content_id', $id)
            ->get()->getResultArray();
        $konten['platforms']    = $plats;
        $konten['platform_str'] = implode(', ', array_column($plats, 'nama_platform'));

        $logModel = new \App\Models\ContentStatusLogModel();
        $log      = $logModel->logKonten($id);

        // Tambahkan label status ke setiap entry
        foreach ($log as &$entry) {
            $entry['label_status_lama'] = $entry['status_lama']
                ? TransisiKonten::labelStatus($entry['status_lama'])
                : null;
            $entry['label_status_baru'] = TransisiKonten::labelStatus($entry['status_baru']);
        }

        return $this->jsonSukses('OK', [
            'content_id'   => $id,
            'judul_konten' => $konten['judul_konten'],
            'status'       => $konten['status'],
            'log'          => $log,
            'konten'       => $konten,
        ]);
    }

    // =========================================================================
    // AI INTEGRATION
    // =========================================================================

    /**
     * POST /dashboard/content-plan/ai-caption/{id}
     * Membuat draft caption dengan AI.
     */
    public function generateCaption(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $konten = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        // Cek jika konten sudah published
        if ($konten['status'] === 'published') {
            return $this->jsonGagal('Konten yang sudah dipublish tidak dapat di-generate ulang captionnya.', 400);
        }

        $judul    = $konten['judul_konten'];
        $brief    = $konten['deskripsi'] ?? '';
        $platform = $this->request->getPost('platform') ?: 'Instagram';

        $ai = new \App\Services\AiService();
        $captionBaru = $ai->generateCaption($id, $judul, $platform, $brief, $userId);

        if (strpos($captionBaru, 'Fitur AI belum') !== false || strpos($captionBaru, 'Gagal') !== false) {
            return $this->jsonGagal($captionBaru, 500);
        }

        // Simpan caption ke database
        $this->model->update($id, [
            'caption'    => $captionBaru,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSukses('Caption berhasil di-generate', ['caption' => $captionBaru]);
    }

    /**
     * POST /dashboard/content-plan/update-caption/{id}
     * Simpan / update caption manual untuk konten.
     */
    public function updateCaption(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');
        $konten   = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        // Pengecekan otorisasi role
        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'manager', 'admin_medsos', 'superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak berwenang mengubah caption.', 403);
        }

        $postVal = $this->request->getPost('caption') ?? $this->request->getVar('caption');
        if ($postVal !== null) {
            $caption = (string) $postVal;
        } else {
            $json    = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON(true) : [];
            $caption = (string) ($json['caption'] ?? '');
        }
        $caption = trim($caption);

        $this->model->update($id, [
            'caption'    => $caption ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSukses('Caption berhasil disimpan.', ['caption' => $caption]);
    }

    /**
     * POST /dashboard/content-plan/ai-ideas
     * Ide konten otomatis dari AI berdasarkan topik & platform.
     */
    public function generateIdeas(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $topik    = trim((string) $this->request->getPost('topik'));
        $platform = trim((string) $this->request->getPost('platform')) ?: 'Instagram';

        if (empty($topik)) {
            return $this->jsonGagal('Topik / Produk wajib diisi.', 422);
        }

        $ai    = new \App\Services\AiService();
        $ide   = $ai->generateIdeas($topik, $platform, $userId);

        if (strpos($ide, 'Fitur AI belum') !== false || strpos($ide, 'Gagal') !== false) {
            return $this->jsonGagal($ide, 500);
        }

        return $this->jsonSukses('Ide berhasil di-generate', ['hasil' => $ide]);
    }

    /**
     * POST /dashboard/content-plan/ai-brief
     * Membuat draft brief / deskripsi ide dengan AI berdasarkan Judul Konten.
     */
    public function generateBrief(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $judul  = trim((string) $this->request->getPost('judul'));
        $jenis  = trim((string) $this->request->getPost('jenis'));
        $pillar = trim((string) $this->request->getPost('pillar'));

        if (empty($judul)) {
            return $this->jsonGagal('Judul Konten wajib diisi terlebih dahulu.', 422);
        }

        $ai        = new \App\Services\AiService();
        $briefBaru = $ai->generateBrief($judul, $jenis, $pillar, $userId);

        if (strpos($briefBaru, 'Fitur AI belum') !== false || strpos($briefBaru, 'Gagal') !== false || strpos($briefBaru, 'kesalahan koneksi') !== false) {
            return $this->jsonGagal($briefBaru, 500);
        }

        return $this->jsonSukses('Brief ide berhasil di-generate', ['brief' => $briefBaru]);
    }

    /**
     * POST /dashboard/content-plan/design-url/{id}
     * Simpan / update link desain Canva / Figma untuk konten.
     * Poin 12: Otomatis beralih ke review_design saat desainer melampirkan link desain.
     */
    public function updateDesignUrl(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');
        $konten   = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        // Pengecekan otorisasi role
        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'manager', 'superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak berwenang mengubah link desain.', 403);
        }

        // Support both POST form data and JSON payload
        $postVal   = $this->request->getPost('design_url') ?? $this->request->getVar('design_url');
        if ($postVal !== null) {
            $designUrl = (string) $postVal;
        } else {
            $json      = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON(true) : [];
            $designUrl = (string) ($json['design_url'] ?? '');
        }
        $designUrl = trim((string) $designUrl);

        // Validasi format URL jika tidak kosong
        if (! empty($designUrl) && ! filter_var($designUrl, FILTER_VALIDATE_URL)) {
            return $this->jsonGagal('Format URL link desain tidak valid (harus diawali http:// atau https://).', 422);
        }

        $updateData = [
            'design_url' => $designUrl ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $statusChanged = false;
        $newStatus = $konten['status'];
        if (! empty($designUrl) && in_array($kodeRole, ['content_creator', 'creative_team', 'superadmin', 'owner'], true)) {
            $trans = $this->tryAutoTransitionToReviewDesign($konten, $userId, 'Desainer melampirkan link Canva/Figma baru. Otomatis beralih ke Review Desain.');
            if ($trans) {
                $updateData['status'] = $trans;
                $newStatus = $trans;
                $statusChanged = true;
            }
        }

        $this->model->protect(false)->update($id, $updateData);
        $this->model->protect(true);

        return $this->jsonSukses('Link desain berhasil disimpan' . ($statusChanged ? ' & status otomatis beralih ke Review Desain.' : '.'), [
            'design_url'     => $designUrl,
            'status'         => $newStatus,
            'status_changed' => $statusChanged,
        ]);
    }

    /**
     * POST /dashboard/content-plan/image-url/{id}
     * Simpan / update URL gambar konten (Google Drive link atau URL publik langsung).
     * Link Drive otomatis dikonversi ke format direct-access oleh GraphApiService::convertDriveLink().
     * Poin 12: Otomatis beralih ke review_design jika desainer melampirkan link gambar.
     */
    public function updateImageUrl(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');
        $konten   = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'admin_medsos', 'manager', 'superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak berwenang mengubah gambar konten.', 403);
        }

        $graphService = new \App\Services\GraphApiService();

        // Support array of image_urls[] or single image_url
        $postUrls = $this->request->getPost('image_urls') ?? $this->request->getVar('image_urls');
        $postVal  = $this->request->getPost('image_url')  ?? $this->request->getVar('image_url');

        $urlsToProcess = [];

        if (is_array($postUrls)) {
            $urlsToProcess = $postUrls;
        } elseif ($postVal !== null) {
            $urlsToProcess = $graphService->parseMediaUrls((string)$postVal);
        } else {
            $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON(true) : [];
            if (isset($json['image_urls']) && is_array($json['image_urls'])) {
                $urlsToProcess = $json['image_urls'];
            } elseif (isset($json['image_url'])) {
                $urlsToProcess = $graphService->parseMediaUrls((string)$json['image_url']);
            }
        }

        $cleanedUrls = [];
        foreach ($urlsToProcess as $u) {
            $strUrl = trim((string)$u);
            if (empty($strUrl)) {
                continue;
            }

            if (! filter_var($strUrl, FILTER_VALIDATE_URL)) {
                return $this->jsonGagal('Format URL tidak valid: ' . htmlspecialchars($strUrl) . ' (harus diawali http:// atau https://).', 422);
            }

            $cleanedUrls[] = $graphService->convertDriveLink($strUrl);
        }

        $finalValue = null;
        if (count($cleanedUrls) > 1) {
            $finalValue = json_encode(array_values($cleanedUrls));
        } elseif (count($cleanedUrls) === 1) {
            $finalValue = $cleanedUrls[0];
        }

        $updateData = [
            'image_url'  => $finalValue,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $statusChanged = false;
        $newStatus = $konten['status'];
        if (! empty($finalValue) && in_array($kodeRole, ['content_creator', 'creative_team', 'superadmin', 'owner'], true)) {
            $trans = $this->tryAutoTransitionToReviewDesign($konten, $userId, 'Desainer melampirkan link gambar/Drive baru. Otomatis beralih ke Review Desain.');
            if ($trans) {
                $updateData['status'] = $trans;
                $newStatus = $trans;
                $statusChanged = true;
            }
        }

        $this->model->protect(false)->update($id, $updateData);
        $this->model->protect(true);

        return $this->jsonSukses('Link media konten berhasil disimpan' . ($statusChanged ? ' & status otomatis beralih ke Review Desain.' : '.'), [
            'image_url'      => $finalValue,
            'urls'           => $cleanedUrls,
            'is_carousel'    => count($cleanedUrls) > 1,
            'slide_count'    => count($cleanedUrls),
            'status'         => $newStatus,
            'status_changed' => $statusChanged,
        ]);
    }

    /**
     * POST /dashboard/content-plan/upload-image/{id}
     * Handle upload file gambar lokal untuk konten (JPG, JPEG, PNG, max 5MB).
     * Poin 12: Otomatis beralih ke review_design jika desainer mengunggah file gambar.
     */
    public function uploadImage(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');
        $konten   = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        // Pengecekan otorisasi role
        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'manager', 'superadmin', 'owner', 'admin_medsos'], true)) {
            return $this->jsonGagal('Anda tidak berwenang mengunggah gambar konten.', 403);
        }

        $file = $this->request->getFile('image_file');
        if (! $file || ! $file->isValid()) {
            return $this->jsonGagal('File gambar wajib diunggah.', 422);
        }

        // Validasi ekstensi & ukuran (max 5MB = 5120KB)
        $validMime = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($file->getMimeType(), $validMime, true)) {
            return $this->jsonGagal('Format file harus JPG, JPEG, PNG, atau WEBP.', 422);
        }

        if ($file->getSizeByUnit('kb') > 5120) {
            return $this->jsonGagal('Ukuran file maksimal 5MB.', 422);
        }

        // Buat nama file unik & simpan ke public/uploads/content-images/
        $uploadDir = FCPATH . 'uploads/content-images/';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = 'img_' . $id . '_' . uniqid() . '.' . $file->getExtension();
        $file->move($uploadDir, $newName);

        $imageUrl = base_url('uploads/content-images/' . $newName);

        $updateData = [
            'image_url'  => $imageUrl,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $statusChanged = false;
        $newStatus = $konten['status'];
        if (in_array($kodeRole, ['content_creator', 'creative_team', 'superadmin', 'owner'], true)) {
            $trans = $this->tryAutoTransitionToReviewDesign($konten, $userId, 'Desainer mengunggah file gambar materi baru. Otomatis beralih ke Review Desain.');
            if ($trans) {
                $updateData['status'] = $trans;
                $newStatus = $trans;
                $statusChanged = true;
            }
        }

        $this->model->protect(false)->update($id, $updateData);
        $this->model->protect(true);

        return $this->jsonSukses('Gambar konten berhasil diunggah' . ($statusChanged ? ' & status otomatis beralih ke Review Desain.' : '.'), [
            'image_url'      => $imageUrl,
            'file_name'      => $newName,
            'status'         => $newStatus,
            'status_changed' => $statusChanged,
        ]);
    }

    /**
     * POST /dashboard/content-plan/update-details/{id}
     * Poin 11: Simpan Desain & Caption Sekaligus (Unified Batch Update & Auto-Save).
     * Poin 12: Otomatis ubah status ke review_design jika link desain diisi desainer.
     */
    public function updateDetails(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId   = (int) session('user_id');
        $kodeRole = session('kode_role');
        $konten   = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'manager', 'superadmin', 'owner', 'admin_medsos'], true)) {
            return $this->jsonGagal('Anda tidak berwenang mengubah data konten ini.', 403);
        }

        // Support both POST form data and JSON payload
        $postCaption   = $this->request->getPost('caption') ?? $this->request->getVar('caption');
        $postDesignUrl = $this->request->getPost('design_url') ?? $this->request->getVar('design_url');
        $postImageUrl  = $this->request->getPost('image_url') ?? $this->request->getVar('image_url');
        $autoSubmit    = (bool) ($this->request->getPost('auto_submit') ?? $this->request->getVar('auto_submit'));

        $updateData = ['updated_at' => date('Y-m-d H:i:s')];

        if ($postCaption !== null) {
            $updateData['caption'] = trim((string)$postCaption) ?: null;
        }

        if ($postDesignUrl !== null) {
            $dUrl = trim((string)$postDesignUrl);
            if (! empty($dUrl) && ! filter_var($dUrl, FILTER_VALIDATE_URL)) {
                return $this->jsonGagal('Format URL link desain tidak valid (harus diawali http:// atau https://).', 422);
            }
            $updateData['design_url'] = $dUrl ?: null;
        }

        if ($postImageUrl !== null) {
            $iUrl = trim((string)$postImageUrl);
            if (! empty($iUrl)) {
                if (str_contains($iUrl, 'drive.google.com')) {
                    $graphService = new \App\Services\GraphApiService();
                    $directUrl = $graphService->convertDriveLink($iUrl);
                    $updateData['image_url'] = $directUrl;
                } else {
                    $updateData['image_url'] = $iUrl;
                }
            } else {
                $updateData['image_url'] = null;
            }
        }

        // Poin 12: Jika creator melampirkan link desain/gambar dan status masih 'acc_ide', 'in_design', atau 'revisi' -> otomatis ke 'review_design'
        $statusChanged = false;
        $newStatus = $konten['status'];
        $hasNewAsset = (!empty($updateData['design_url']) || !empty($updateData['image_url']));
        if (($autoSubmit || $hasNewAsset) && in_array($kodeRole, ['content_creator', 'creative_team', 'superadmin', 'owner'], true)) {
            $trans = $this->tryAutoTransitionToReviewDesign($konten, $userId, 'Desainer melampirkan materi desain & caption. Sistem otomatis memindahkan status ke Review Desain.');
            if ($trans) {
                $updateData['status'] = $trans;
                $newStatus = $trans;
                $statusChanged = true;
            }
        }

        $this->model->protect(false)->update($id, $updateData);
        $this->model->protect(true);

        return $this->jsonSukses('Desain & Caption berhasil disimpan' . ($statusChanged ? ' & status otomatis beralih ke Review Desain.' : '.'), [
            'caption'        => $updateData['caption'] ?? $konten['caption'],
            'design_url'     => $updateData['design_url'] ?? $konten['design_url'],
            'image_url'      => $updateData['image_url'] ?? $konten['image_url'],
            'status'         => $newStatus,
            'status_changed' => $statusChanged,
        ]);
    }

    /**
     * Helper Poin 12: Otomatis transisi ke 'review_design' jika desainer melampirkan materi desain.
     */
    private function tryAutoTransitionToReviewDesign(array $konten, int $userId, string $catatan): ?string
    {
        $statusLama = $konten['status'];
        if (in_array($statusLama, ['acc_ide', 'in_design', 'revisi'], true)) {
            $db = \Config\Database::connect();
            $db->table('content_status_log')->insert([
                'content_id'  => $konten['id'],
                'status_lama' => $statusLama,
                'status_baru' => 'review_design',
                'user_id'     => $userId,
                'catatan'     => $catatan,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            // Kirim notifikasi ke Manager
            $notifService = new \App\Services\NotificationService();
            $notifService->notifikasiTransisi($konten, $statusLama, 'review_design', $userId);

            return 'review_design';
        }
        return null;
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    /**
     * POST /dashboard/content-plan/delete/{id}
     * Hapus konten — hanya superadmin, owner, manager (§5).
     */
    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $kodeRole = session('kode_role');

        if (! in_array($kodeRole, ['superadmin', 'owner', 'manager'], true)) {
            return $this->jsonGagal('Anda tidak berwenang menghapus konten.', 403);
        }

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        $this->model->delete($id);
        return $this->jsonSukses('Konten berhasil dihapus.');
    }

    // =========================================================================
    // Internal Helper
    // =========================================================================

    private function bolehEdit(array $konten, int $userId, string $kodeRole): bool
    {
        // superadmin & owner boleh edit semua
        if (in_array($kodeRole, ['superadmin', 'owner'], true)) {
            return true;
        }
        // manager boleh edit semua
        if ($kodeRole === 'manager') {
            return true;
        }
        // content_creator hanya boleh edit miliknya
        if ($kodeRole === 'content_creator') {
            return (int) $konten['dibuat_oleh'] === $userId;
        }
        // admin_medsos tidak boleh edit
        return false;
    }

    private function jsonSukses(string $pesan, array $data = [], int $code = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => 'sukses',
            'pesan'   => $pesan,
            'data'    => $data,
        ]);
    }

    private function jsonGagal(string $pesan, int $code = 422): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status' => 'gagal',
            'pesan'  => $pesan,
        ]);
    }
}
