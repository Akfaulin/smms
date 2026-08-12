<?php

namespace App\Controllers;

/**
 * AssetLibrary Controller
 *
 * Dashboard khusus Content Creator untuk mengelola Asset Library & Brand Kit resmi.
 * Memungkinkan tambah, hapus, dan salin aset warna, file, atau tautan desain secara dinamis.
 */
class AssetLibrary extends BaseController
{
    public function index()
    {
        $db   = \Config\Database::connect();
        $role = session('kode_role');

        if (! in_array($role, ['content_creator', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $bisnisId = (int) session('bisnis_aktif_id');

        $allAssets = $db->table('brand_assets')
            ->where('bisnis_id', $bisnisId)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $palettes    = array_values(array_filter($allAssets, fn($a) => $a['kategori'] === 'palette'));
        $otherAssets = array_values(array_filter($allAssets, fn($a) => $a['kategori'] !== 'palette'));

        return view('asset_library/index', [
            'judul'       => 'Asset Library & Brand Kit',
            'palettes'    => $palettes,
            'otherAssets' => $otherAssets,
            'kode_role'   => $role,
        ]);
    }

    public function store()
    {
        $role = session('kode_role');
        if (! in_array($role, ['content_creator', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $db       = \Config\Database::connect();
        $kategori = $this->request->getPost('kategori') ?: 'palette';
        $val      = trim($this->request->getPost('nilai_atau_url') ?: '');

        if ($kategori === 'palette' && $val && ! str_starts_with($val, '#')) {
            $val = '#' . $val;
        }

        $data = [
            'bisnis_id'      => (int) session('bisnis_aktif_id'),
            'nama_aset'      => $this->request->getPost('nama_aset'),
            'kategori'       => $kategori,
            'nilai_atau_url' => $val,
            'keterangan'     => $this->request->getPost('keterangan') ?: null,
            'dibuat_oleh'    => (int) session('user_id'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if (! empty($data['nama_aset']) && ! empty($data['nilai_atau_url'])) {
            $db->table('brand_assets')->insert($data);
        }

        return redirect()->to('/dashboard/asset-library');
    }

    public function delete($id)
    {
        $role = session('kode_role');
        if (! in_array($role, ['content_creator', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $db = \Config\Database::connect();
        $db->table('brand_assets')->where('id', (int) $id)->delete();

        return redirect()->to('/dashboard/asset-library');
    }
}
