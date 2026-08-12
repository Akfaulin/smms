<?php

namespace App\Controllers;

/**
 * MasterData Controller
 * 
 * Mengelola entitas master (Platforms, Jenis Konten, Content Types / Pillar).
 * Setiap data master terisolasi per bisnis aktif.
 * Hanya superadmin dan owner yang dapat mengakses endpoint di sini.
 */
class MasterData extends BaseController
{
    /**
     * Memastikan hanya superadmin / owner yang bisa akses.
     */
    private function checkAccess(): bool
    {
        return in_array(session('kode_role'), ['superadmin', 'owner'], true);
    }

    /**
     * Tampilkan halaman Master Data (Tabbed View) untuk bisnis aktif.
     */
    public function index()
    {
        if (! $this->checkAccess()) {
            return redirect()->to('/dashboard/content-plan')->with('error', 'Akses ditolak.');
        }

        $db       = \Config\Database::connect();
        $bisnisId = (int) session('bisnis_aktif_id');

        $platforms   = $db->table('platforms')
            ->where('bisnis_id', $bisnisId)
            ->orderBy('nama_platform', 'ASC')->get()->getResultArray();

        $jenisKonten = $db->table('jenis_konten')
            ->where('bisnis_id', $bisnisId)
            ->orderBy('nama_jenis', 'ASC')->get()->getResultArray();

        $pillars     = $db->table('content_types')
            ->where('bisnis_id', $bisnisId)
            ->orderBy('nama_type', 'ASC')->get()->getResultArray();

        return view('master/data/index', [
            'judul'       => 'Master Data',
            'platforms'   => $platforms,
            'jenisKonten' => $jenisKonten,
            'pillars'     => $pillars,
        ]);
    }

    // =========================================================================
    // PLATFORM CRUD
    // =========================================================================
    public function storePlatform()
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);

        $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $db   = \Config\Database::connect();
        $db->table('platforms')->insert([
            'bisnis_id'     => (int) session('bisnis_aktif_id'),
            'nama_platform' => trim($this->request->getPost('nama_platform') ?? $json->nama_platform ?? ''),
            'status'        => $this->request->getPost('status') ?? $json->status ?? 'aktif',
        ]);

        return $this->jsonSukses('Platform berhasil ditambahkan.');
    }

    public function updatePlatform(int $id)
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);

        $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $db   = \Config\Database::connect();
        $db->table('platforms')
            ->where('id', $id)
            ->where('bisnis_id', (int) session('bisnis_aktif_id'))
            ->update([
                'nama_platform' => trim($this->request->getPost('nama_platform') ?? $json->nama_platform ?? ''),
                'status'        => $this->request->getPost('status') ?? $json->status ?? 'aktif',
            ]);

        return $this->jsonSukses('Platform berhasil diperbarui.');
    }

    public function deletePlatform(int $id)
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);
        $db = \Config\Database::connect();
        $db->table('platforms')
            ->where('id', $id)
            ->where('bisnis_id', (int) session('bisnis_aktif_id'))
            ->delete();
        return $this->jsonSukses('Platform berhasil dihapus.');
    }

    // =========================================================================
    // JENIS KONTEN CRUD
    // =========================================================================
    public function storeJenis()
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);

        $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $db   = \Config\Database::connect();
        $db->table('jenis_konten')->insert([
            'bisnis_id'  => (int) session('bisnis_aktif_id'),
            'nama_jenis' => trim($this->request->getPost('nama_jenis') ?? $json->nama_jenis ?? ''),
        ]);

        return $this->jsonSukses('Jenis konten berhasil ditambahkan.');
    }

    public function updateJenis(int $id)
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);

        $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $db   = \Config\Database::connect();
        $db->table('jenis_konten')
            ->where('id', $id)
            ->where('bisnis_id', (int) session('bisnis_aktif_id'))
            ->update([
                'nama_jenis' => trim($this->request->getPost('nama_jenis') ?? $json->nama_jenis ?? ''),
            ]);

        return $this->jsonSukses('Jenis konten berhasil diperbarui.');
    }

    public function deleteJenis(int $id)
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);
        $db = \Config\Database::connect();
        $db->table('jenis_konten')
            ->where('id', $id)
            ->where('bisnis_id', (int) session('bisnis_aktif_id'))
            ->delete();
        return $this->jsonSukses('Jenis konten berhasil dihapus.');
    }

    // =========================================================================
    // CONTENT PILLAR (TYPES) CRUD
    // =========================================================================
    public function storePillar()
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);

        $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $db   = \Config\Database::connect();
        $db->table('content_types')->insert([
            'bisnis_id' => (int) session('bisnis_aktif_id'),
            'nama_type' => trim($this->request->getPost('nama_type') ?? $json->nama_type ?? ''),
        ]);

        return $this->jsonSukses('Content Pillar berhasil ditambahkan.');
    }

    public function updatePillar(int $id)
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);

        $json = str_contains(strtolower($this->request->getHeaderLine('Content-Type')), 'json') ? $this->request->getJSON() : null;
        $db   = \Config\Database::connect();
        $db->table('content_types')
            ->where('id', $id)
            ->where('bisnis_id', (int) session('bisnis_aktif_id'))
            ->update([
                'nama_type' => trim($this->request->getPost('nama_type') ?? $json->nama_type ?? ''),
            ]);

        return $this->jsonSukses('Content Pillar berhasil diperbarui.');
    }

    public function deletePillar(int $id)
    {
        if (! $this->checkAccess()) return $this->jsonGagal('Akses ditolak', 403);
        $db = \Config\Database::connect();
        $db->table('content_types')
            ->where('id', $id)
            ->where('bisnis_id', (int) session('bisnis_aktif_id'))
            ->delete();
        return $this->jsonSukses('Content Pillar berhasil dihapus.');
    }


    // =========================================================================
    // HELPER
    // =========================================================================
    private function jsonSukses(string $pesan): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON(['status' => 'sukses', 'pesan' => $pesan]);
    }

    private function jsonGagal(string $pesan, int $code = 400): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON(['status' => 'gagal', 'pesan' => $pesan]);
    }
}
