<?php

namespace App\Controllers;

/**
 * AssetLibrary Controller
 *
 * Poin 13: Brand Guidelines PDF Viewer & Asset Library
 * Mengelola Brand Guidelines (PDF Viewer per bisnis), Palette Warna, Logo, dan Templat Desain.
 */
class AssetLibrary extends BaseController
{
    public function index()
    {
        $db   = \Config\Database::connect();
        $role = session('kode_role');

        if (! in_array($role, ['content_creator', 'creative_team', 'manager', 'superadmin', 'owner', 'admin_medsos'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $bisnisId = (int) session('bisnis_aktif_id');

        // Ambil info bisnis aktif
        $bisnis = $db->table('bisnis')->where('id', $bisnisId)->get()->getRowArray();
        $namaBisnis = $bisnis['nama_bisnis'] ?? 'Semua Bisnis';

        $allAssets = $db->table('brand_assets')
            ->where('bisnis_id', $bisnisId)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $guidelines  = array_values(array_filter($allAssets, fn($a) => $a['kategori'] === 'guideline'));
        $palettes    = array_values(array_filter($allAssets, fn($a) => $a['kategori'] === 'palette'));
        $otherAssets = array_values(array_filter($allAssets, fn($a) => ! in_array($a['kategori'], ['palette', 'guideline'], true)));

        return view('asset_library/index', [
            'judul'       => 'Asset Library & Brand Kit — ' . $namaBisnis,
            'namaBisnis'  => $namaBisnis,
            'bisnis'      => $bisnis,
            'guidelines'  => $guidelines,
            'palettes'    => $palettes,
            'otherAssets' => $otherAssets,
            'kode_role'   => $role,
        ]);
    }

    /**
     * POST /dashboard/asset-library/upload-guideline
     * Upload atau simpan link PDF Brand Guidelines per bisnis.
     */
    public function uploadGuideline()
    {
        $role = session('kode_role');
        if (! in_array($role, ['content_creator', 'manager', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/asset-library')->with('error', 'Anda tidak berwenang mengunggah Brand Guidelines.');
        }

        $bisnisId = (int) session('bisnis_aktif_id');
        $namaAset = trim((string) $this->request->getPost('nama_aset')) ?: 'Brand Guidelines & Visual Identity';
        $ket      = trim((string) $this->request->getPost('keterangan')) ?: 'Pedoman resmi logo, tipografi, warna, dan tone of voice.';

        $file   = $this->request->getFile('pdf_file');
        $pdfUrl = trim((string) $this->request->getPost('pdf_url'));
        $finalUrl = '';

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if ($ext !== 'pdf') {
                return redirect()->to('/dashboard/asset-library')->with('error', 'File panduan harus berformat PDF (.pdf).');
            }

            // Max 25MB = 25600KB
            if ($file->getSizeByUnit('kb') > 25600) {
                return redirect()->to('/dashboard/asset-library')->with('error', 'Ukuran file PDF maksimal 25MB.');
            }

            $uploadDir = FCPATH . 'uploads/brand-guidelines/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newName = 'guideline_' . $bisnisId . '_' . uniqid() . '.pdf';
            $file->move($uploadDir, $newName);
            $finalUrl = base_url('uploads/brand-guidelines/' . $newName);
        } elseif (! empty($pdfUrl)) {
            if (! filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
                return redirect()->to('/dashboard/asset-library')->with('error', 'Format URL dokumen PDF / Google Drive tidak valid.');
            }
            if (str_contains($pdfUrl, 'drive.google.com')) {
                $graphService = new \App\Services\GraphApiService();
                $finalUrl = $graphService->convertDriveLink($pdfUrl);
            } else {
                $finalUrl = $pdfUrl;
            }
        } else {
            return redirect()->to('/dashboard/asset-library')->with('error', 'Silakan pilih file PDF yang ingin diunggah atau isi URL dokumen.');
        }

        $db = \Config\Database::connect();
        $db->table('brand_assets')->insert([
            'bisnis_id'      => $bisnisId,
            'nama_aset'      => $namaAset,
            'kategori'       => 'guideline',
            'nilai_atau_url' => $finalUrl,
            'keterangan'     => $ket,
            'dibuat_oleh'    => (int) session('user_id'),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/dashboard/asset-library')->with('sukses', 'Brand Guidelines PDF berhasil disimpan & siap diakses.');
    }

    public function store()
    {
        $role = session('kode_role');
        if (! in_array($role, ['content_creator', 'manager', 'superadmin', 'owner'], true)) {
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

        return redirect()->to('/dashboard/asset-library')->with('sukses', 'Aset baru berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $role = session('kode_role');
        if (! in_array($role, ['content_creator', 'manager', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $db = \Config\Database::connect();
        $asset = $db->table('brand_assets')->where('id', (int) $id)->get()->getRowArray();

        if ($asset) {
            // Hapus file fisik jika tersimpan di direktori lokal uploads/brand-guidelines/
            if ($asset['kategori'] === 'guideline' && str_contains($asset['nilai_atau_url'], 'uploads/brand-guidelines/')) {
                $filename = basename(parse_url($asset['nilai_atau_url'], PHP_URL_PATH));
                $filepath = FCPATH . 'uploads/brand-guidelines/' . $filename;
                if (file_exists($filepath)) {
                    @unlink($filepath);
                }
            }
            $db->table('brand_assets')->where('id', (int) $id)->delete();
        }

        return redirect()->to('/dashboard/asset-library')->with('sukses', 'Aset berhasil dihapus.');
    }
}
