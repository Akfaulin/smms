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

        // Filter: default 'my_ideas' untuk creative_team, 'my_tasks' untuk role lain
        $defaultView = ($role === 'creative_team') ? 'my_ideas' : 'my_tasks';
        $viewMode    = $this->request->getGet('view') ?? $defaultView;

        // Filter by bisnis aktif
        $query = $this->model->withRelasi()->byBisnis($bisnisId);

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
                          ->orWhere('content_plan.dibuat_oleh', $userId)
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
        }

        $konten = $query->orderBy('content_plan.created_at', 'DESC')->findAll();

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

        // Users per role untuk assigned_designer & assigned_uploader
        $allUsers     = $db->table('users u')
            ->select('u.id, u.nama, r.kode_role')
            ->join('roles r', 'r.id = u.role_id', 'left')
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

        $data = [
            'bisnis_id'         => (int) session('bisnis_aktif_id'),
            'judul_konten'      => $this->request->getPost('judul_konten'),
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'tanggal_publish'   => $this->request->getPost('tanggal_publish') ?: null,
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

        $data = [
            'judul_konten'    => $this->request->getPost('judul_konten'),
            'deskripsi'       => $this->request->getPost('deskripsi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish') ?: null,
            'jenis_konten_id' => $this->request->getPost('jenis_konten_id') ?: null,
            'content_type_id' => $this->request->getPost('content_type_id') ?: null,
        ];

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

        // Hanya pembuat konten yang bisa generate caption di tahap in_design,
        // atau superadmin/owner.
        if ($konten['status'] !== 'in_design') {
            return $this->jsonGagal('Caption AI hanya dapat di-generate pada tahap In Design.', 400);
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
        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'manager', 'superadmin', 'owner'], true)) {
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

        $this->model->update($id, [
            'design_url' => $designUrl ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSukses('Link desain berhasil disimpan.', ['design_url' => $designUrl]);
    }

    /**
     * POST /dashboard/content-plan/image-url/{id}
     * Simpan / update URL gambar konten (Google Drive link atau URL publik langsung).
     * Link Drive otomatis dikonversi ke format direct-access oleh GraphApiService::convertDriveLink().
     */
    public function updateImageUrl(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $kodeRole = session('kode_role');
        $konten   = $this->model->find($id);

        if (! $konten) {
            return $this->jsonGagal('Konten tidak ditemukan.', 404);
        }

        if (! in_array($kodeRole, ['content_creator', 'creative_team', 'admin_medsos', 'manager', 'superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak berwenang mengubah gambar konten.', 403);
        }

        // Support both POST form data and JSON payload
        $postVal = $this->request->getPost('image_url') ?? $this->request->getVar('image_url');
        if ($postVal !== null) {
            $imageUrl = (string) $postVal;
        } else {
            $json     = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON(true) : [];
            $imageUrl = (string) ($json['image_url'] ?? '');
        }
        $imageUrl = trim($imageUrl);

        // Validasi format URL jika tidak kosong
        if (! empty($imageUrl) && ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return $this->jsonGagal('Format URL tidak valid (harus diawali http:// atau https://).', 422);
        }

        // Konversi Google Drive share link → direct-access URL sebelum disimpan
        if (! empty($imageUrl)) {
            $graphService = new \App\Services\GraphApiService();
            $imageUrl = $graphService->convertDriveLink($imageUrl);
        }

        $this->model->protect(false)->update($id, [
            'image_url'  => $imageUrl ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->model->protect(true);

        return $this->jsonSukses('Link gambar berhasil disimpan.', ['image_url' => $imageUrl]);
    }

    /**
     * POST /dashboard/content-plan/upload-image/{id}
     * Handle upload file gambar lokal untuk konten (JPG, JPEG, PNG, max 5MB).
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

        $this->model->update($id, [
            'image_url'  => $imageUrl,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->jsonSukses('Gambar konten berhasil diunggah.', [
            'image_url' => $imageUrl,
            'file_name' => $newName,
        ]);
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
