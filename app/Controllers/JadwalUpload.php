<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;
use App\Services\TransisiKonten;

/**
 * JadwalUpload Controller
 *
 * Dashboard khusus Admin Media Sosial untuk memantau jadwal tayang postingan,
 * memposting konten yang sudah di-ACC Final, dan menginput URL link postingan.
 */
class JadwalUpload extends BaseController
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

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $filterStatus = $this->request->getGet('status') ?? 'all';
        $todayStr     = date('Y-m-d');
        $bisnisId     = (int) session('bisnis_aktif_id');

        $query = $this->model->withRelasi()->byBisnis($bisnisId);

        // Apply status filter tab
        if ($filterStatus === 'ready') {
            $query->where('content_plan.status', 'acc_final');
        } elseif ($filterStatus === 'today') {
            $query->where('content_plan.tanggal_publish', $todayStr);
        } elseif ($filterStatus === 'published') {
            $query->where('content_plan.status', 'published');
        } else {
            // Default filter list untuk Admin Medsos: fokus ke acc_final & published
            if ($role === 'admin_medsos') {
                $query->whereIn('content_plan.status', ['acc_final', 'published']);
            }
        }

        $konten = $query->orderBy('content_plan.tanggal_publish', 'ASC')->findAll();

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

        // Calculate summary metrics (filter by bisnis)
        $allData = $db->table('content_plan')->where('bisnis_id', $bisnisId)->get()->getResultArray();

        $statReady     = count(array_filter($allData, fn($i) => $i['status'] === 'acc_final'));
        $statToday     = count(array_filter($allData, fn($i) => !empty($i['tanggal_publish']) && date('Y-m-d', strtotime($i['tanggal_publish'])) === $todayStr));
        $statPublished = count(array_filter($allData, fn($i) => $i['status'] === 'published'));
        $statTotal     = count($allData);

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

        return view('jadwal_upload/index', [
            'judul'         => 'Dashboard Jadwal & Upload Admin Medsos',
            'konten'        => $konten,
            'statReady'     => $statReady,
            'statToday'     => $statToday,
            'statPublished' => $statPublished,
            'statTotal'     => $statTotal,
            'filterStatus'  => $filterStatus,
            'platforms'     => $platforms,
            'jenisKonten'   => $jenisKonten,
            'contentTypes'  => $contentTypes,
            'kode_role'     => $role,
        ]);
    }

    /**
     * POST /dashboard/jadwal-upload/publish/{id}
     * Simulasikan / Jalankan publishing otomatis media gambar ke platform (Meta Graph API).
     */
    public function publish(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses untuk melakukan publish.',
            ]);
        }

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan.',
            ]);
        }

        // Validasi 1: Gambar publik wajib ada
        if (empty($konten['image_url'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten belum punya gambar untuk dipublish. Silakan upload media gambar terlebih dahulu.',
            ]);
        }

        // Validasi 2: Caption (Peringatan jika kosong)
        $warning = null;
        if (empty($konten['caption'])) {
            $warning = 'Catatan: Caption konten masih kosong, publishing akan menyertakan gambar tanpa caption.';
        }

        // RESPON SIMULASI INFRASTRUKTUR (Meta Graph API siap dipanggil di tahap berikutnya)
        return $this->response->setJSON([
            'status'  => 'sukses',
            'pesan'   => 'Infrastruktur publish siap! Simulasi publishing gambar ke Meta API berhasil.',
            'warning' => $warning,
            'data'    => [
                'content_id' => $id,
                'judul'      => $konten['judul_konten'],
                'image_url'  => $konten['image_url'],
                'caption'    => $konten['caption'] ?? '',
                'mode'       => 'simulation_ready',
            ],
        ]);
    }
}
