<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BisnisModel;

/**
 * Bisnis Controller
 *
 * Menangani:
 * 1. Ganti bisnis aktif via session (POST /dashboard/bisnis/switch/{id})
 * 2. CRUD bisnis di Master Data (superadmin/owner only)
 */
class Bisnis extends BaseController
{
    private BisnisModel $bisnisModel;

    public function __construct()
    {
        $this->bisnisModel = new BisnisModel();
    }

    // =========================================================================
    // SWITCH BISNIS (Session)
    // =========================================================================

    /**
     * POST/GET /dashboard/bisnis/switch/{id}
     * Ganti bisnis aktif — simpan bisnis_id baru ke session.
     * Semua role boleh ganti bisnis aktif.
     */
    public function switch(int $id): \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! session('logged_in')) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Anda belum login.',
                ]);
            }
            return redirect()->to('/login');
        }

        $bisnis = $this->bisnisModel->getById($id);
        if (! $bisnis) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Bisnis tidak ditemukan atau tidak aktif.',
                ]);
            }
            return redirect()->back()->with('error', 'Bisnis tidak ditemukan atau tidak aktif.');
        }

        // Simpan bisnis aktif ke session
        session()->set([
            'bisnis_aktif_id'    => (int) $bisnis['id'],
            'bisnis_aktif_nama'  => $bisnis['nama_bisnis'],
            'bisnis_aktif_warna' => $bisnis['warna'],
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'sukses',
                'pesan'  => 'Bisnis aktif berhasil diganti ke: ' . $bisnis['nama_bisnis'],
                'data'   => [
                    'id'    => (int) $bisnis['id'],
                    'nama'  => $bisnis['nama_bisnis'],
                    'warna' => $bisnis['warna'],
                ],
            ]);
        }

        return redirect()->back()->with('sukses', 'Bisnis aktif berhasil diganti ke: ' . $bisnis['nama_bisnis']);
    }

    // =========================================================================
    // CRUD BISNIS (Master Data)
    // =========================================================================

    /**
     * GET /dashboard/master/bisnis
     * Tampilkan halaman manajemen bisnis (superadmin/owner only).
     */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $role = session('kode_role');
        if (! in_array($role, ['superadmin', 'owner'], true)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $semua = $this->bisnisModel->orderBy('urutan', 'ASC')->findAll();

        return view('master/bisnis/index', [
            'judul'        => 'Manajemen Bisnis',
            'semua_bisnis' => $semua,
            'kode_role'    => $role,
        ]);
    }

    /**
     * POST /dashboard/master/bisnis/store
     * Tambah bisnis baru (superadmin/owner only). Maks 4 bisnis.
     */
    public function store(): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak memiliki akses.', 403);
        }

        // Cek batas maksimal 10 bisnis
        $total = $this->bisnisModel->countAll();
        if ($total >= 10) {
            return $this->jsonGagal('Maksimal 10 bisnis yang dapat dikelola sekaligus.', 422);
        }

        $data = [
            'nama_bisnis' => trim($this->request->getPost('nama_bisnis') ?? ''),
            'deskripsi'   => trim($this->request->getPost('deskripsi') ?? ''),
            'warna'       => $this->request->getPost('warna') ?: '#6C5CE7',
            'logo_url'    => $this->request->getPost('logo_url') ?: null,
            'status'      => 'aktif',
            'urutan'      => $total + 1,
        ];

        if (! $this->bisnisModel->insert($data)) {
            return $this->jsonGagal('Gagal menyimpan bisnis: ' . implode(', ', $this->bisnisModel->errors()), 422);
        }

        $newId = (int) $this->bisnisModel->getInsertID();
        if ($newId) {
            $this->seedMasterForBisnis($newId);
        }

        return $this->jsonSukses('Bisnis baru berhasil ditambahkan.', [
            'id' => $newId,
        ], 201);
    }

    /**
     * POST /dashboard/master/bisnis/update/{id}
     * Update data bisnis (superadmin/owner only).
     */
    public function update(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak memiliki akses.', 403);
        }

        $bisnis = $this->bisnisModel->find($id);
        if (! $bisnis) {
            return $this->jsonGagal('Bisnis tidak ditemukan.', 404);
        }

        $data = [
            'nama_bisnis' => trim($this->request->getPost('nama_bisnis') ?? $bisnis['nama_bisnis']),
            'deskripsi'   => trim($this->request->getPost('deskripsi') ?? ''),
            'warna'       => $this->request->getPost('warna') ?: $bisnis['warna'],
            'logo_url'    => $this->request->getPost('logo_url') ?: null,
            'status'      => $this->request->getPost('status') ?: $bisnis['status'],
        ];

        if (! $this->bisnisModel->update($id, $data)) {
            return $this->jsonGagal('Gagal memperbarui bisnis: ' . implode(', ', $this->bisnisModel->errors()), 422);
        }

        // Jika bisnis yang diupdate adalah bisnis aktif di session, perbarui session
        if (session('bisnis_aktif_id') == $id) {
            session()->set([
                'bisnis_aktif_nama'  => $data['nama_bisnis'],
                'bisnis_aktif_warna' => $data['warna'],
            ]);
        }

        return $this->jsonSukses('Bisnis berhasil diperbarui.');
    }

    /**
     * POST /dashboard/master/bisnis/delete/{id}
     * Hapus bisnis beserta seluruh data terkait (superadmin/owner only).
     */
    public function delete(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['superadmin', 'owner'], true)) {
            return $this->jsonGagal('Anda tidak memiliki akses.', 403);
        }

        $bisnis = $this->bisnisModel->find($id);
        if (! $bisnis) {
            return $this->jsonGagal('Bisnis tidak ditemukan.', 404);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Ambil semua ID content_plan milik bisnis ini
        $cpIds = $db->table('content_plan')->select('id')->where('bisnis_id', $id)->get()->getResultArray();
        $cpIdList = array_column($cpIds, 'id');

        if (! empty($cpIdList)) {
            $db->table('content_status_log')->whereIn('content_plan_id', $cpIdList)->delete();
            $db->table('bukti_upload')->whereIn('content_plan_id', $cpIdList)->delete();
            $db->table('content_platforms')->whereIn('content_plan_id', $cpIdList)->delete();
            $db->table('content_plan')->where('bisnis_id', $id)->delete();
        }

        // 2. Hapus master data terisolasi milik bisnis ini
        $db->table('platforms')->where('bisnis_id', $id)->delete();
        $db->table('jenis_konten')->where('bisnis_id', $id)->delete();
        $db->table('content_types')->where('bisnis_id', $id)->delete();
        $db->table('brand_assets')->where('bisnis_id', $id)->delete();
        $db->table('trend_bank')->where('bisnis_id', $id)->delete();

        // 3. Hapus record bisnis itu sendiri
        $db->table('bisnis')->where('id', $id)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->jsonGagal('Gagal menghapus bisnis dari database.', 500);
        }

        // 4. Jika bisnis yang dihapus adalah bisnis aktif di session, switch ke bisnis tersisa
        if (session('bisnis_aktif_id') == $id) {
            $default = $this->bisnisModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->first();
            if ($default) {
                session()->set([
                    'bisnis_aktif_id'    => (int) $default['id'],
                    'bisnis_aktif_nama'  => $default['nama_bisnis'],
                    'bisnis_aktif_warna' => $default['warna'],
                ]);
            } else {
                session()->remove(['bisnis_aktif_id', 'bisnis_aktif_nama', 'bisnis_aktif_warna']);
            }
        }

        return $this->jsonSukses('Bisnis beserta seluruh data terkait berhasil dihapus.');
    }

    // =========================================================================
    // Helper
    // =========================================================================

    private function seedMasterForBisnis(int $bisnisId): void
    {
        $db = \Config\Database::connect();

        $defaultPlatforms = [
            ['nama_platform' => 'Instagram',   'status' => 'aktif'],
            ['nama_platform' => 'TikTok',      'status' => 'aktif'],
            ['nama_platform' => 'Facebook',    'status' => 'aktif'],
            ['nama_platform' => 'Twitter / X', 'status' => 'aktif'],
            ['nama_platform' => 'YouTube',     'status' => 'aktif'],
            ['nama_platform' => 'LinkedIn',    'status' => 'aktif'],
        ];

        $defaultJenis = [
            ['nama_jenis' => 'Reels / Video',   'keterangan' => 'Konten video pendek atau panjang'],
            ['nama_jenis' => 'Carousel',        'keterangan' => 'Slide multi-gambar'],
            ['nama_jenis' => 'Static Post',     'keterangan' => 'Gambar tunggal'],
            ['nama_jenis' => 'Story',           'keterangan' => 'Konten 24 jam'],
            ['nama_jenis' => 'Thread / Caption','keterangan' => 'Konten teks panjang'],
            ['nama_jenis' => 'Live',            'keterangan' => 'Siaran langsung'],
        ];

        $defaultPillars = [
            ['nama_type' => 'Edukasi'],
            ['nama_type' => 'Promosi'],
            ['nama_type' => 'Hiburan'],
            ['nama_type' => 'Inspirasi'],
            ['nama_type' => 'Behind the Scene'],
            ['nama_type' => 'Testimoni'],
        ];

        foreach ($defaultPlatforms as $p) {
            $db->table('platforms')->insert([
                'bisnis_id'     => $bisnisId,
                'nama_platform' => $p['nama_platform'],
                'status'        => $p['status'],
            ]);
        }

        foreach ($defaultJenis as $j) {
            $db->table('jenis_konten')->insert([
                'bisnis_id'  => $bisnisId,
                'nama_jenis' => $j['nama_jenis'],
                'keterangan' => $j['keterangan'],
            ]);
        }

        foreach ($defaultPillars as $cp) {
            $db->table('content_types')->insert([
                'bisnis_id' => $bisnisId,
                'nama_type' => $cp['nama_type'],
            ]);
        }
    }

    private function jsonSukses(string $pesan, array $data = [], int $code = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status' => 'sukses',
            'pesan'  => $pesan,
            'data'   => $data,
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
