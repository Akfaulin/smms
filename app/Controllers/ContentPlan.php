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
        $db   = \Config\Database::connect();
        $role = session('kode_role');
        $userId = session('user_id');

        // Filter: default 'my_tasks', bisa di-override jadi 'all' dari URL
        $viewMode = $this->request->getGet('view') ?? 'my_tasks';

        $query = $this->model->withRelasi();

        if ($viewMode === 'my_tasks') {
            if ($role === 'manager') {
                $query->whereIn('content_plan.status', ['ide_diajukan', 'review_design']);
            } elseif ($role === 'content_creator') {
                $query->groupStart()
                      ->whereIn('content_plan.status', ['acc_ide', 'in_design', 'revisi'])
                      ->where('content_plan.dibuat_oleh', $userId)
                      ->groupEnd();
            } elseif ($role === 'admin_medsos') {
                $query->where('content_plan.status', 'acc_final');
            }
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

        // Master data untuk form
        $platforms    = $db->table('platforms')->where('status', 'aktif')->get()->getResultArray();
        $jenisKonten  = $db->table('jenis_konten')->get()->getResultArray();
        $contentTypes = $db->table('content_types')->get()->getResultArray();

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

        if ($kodeRole === 'admin_medsos') {
            return $this->jsonGagal('Anda tidak memiliki akses untuk membuat konten.', 403);
        }

        $data = [
            'judul_konten'      => $this->request->getPost('judul_konten'),
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'tanggal_publish'   => $this->request->getPost('tanggal_publish') ?: null,
            'jenis_konten_id'   => $this->request->getPost('jenis_konten_id') ?: null,
            'content_type_id'   => $this->request->getPost('content_type_id') ?: null,
            'assigned_designer' => $this->request->getPost('assigned_designer') ?: null,
            'assigned_uploader' => $this->request->getPost('assigned_uploader') ?: null,
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
        $json       = $this->request->getJSON();
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
            $db->table('bukti_upload')->insert([
                'content_id'     => $id,
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
        $konten = $this->model->find($id);
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

    private function jsonGagal(string $pesan, int $code = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'gagal',
            'pesan'  => $pesan,
        ]);
    }
}
