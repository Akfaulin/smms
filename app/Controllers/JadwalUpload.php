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
        $sortBy       = $this->request->getGet('sort') ?: 'publish_mepet';
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
        } elseif ($filterStatus === 'overdue') {
            $nowStr = date('Y-m-d H:i:s');
            $query->where('content_plan.tanggal_publish IS NOT NULL')
                  ->where('content_plan.tanggal_publish <', $nowStr)
                  ->whereNotIn('content_plan.status', ['published', 'ditolak']);
        } else {
            // Default filter list untuk Admin Medsos: fokus ke acc_final & published
            if ($role === 'admin_medsos') {
                $query->whereIn('content_plan.status', ['acc_final', 'published']);
            }
        }

        // Poin 3: Sortir default tanggal publish paling mepet
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
        $nowStr  = date('Y-m-d H:i:s');

        $statReady     = count(array_filter($allData, fn($i) => $i['status'] === 'acc_final'));
        $statToday     = count(array_filter($allData, fn($i) => !empty($i['tanggal_publish']) && date('Y-m-d', strtotime($i['tanggal_publish'])) === $todayStr));
        $statPublished = count(array_filter($allData, fn($i) => $i['status'] === 'published'));
        $statOverdue   = count(array_filter($allData, fn($i) => !empty($i['tanggal_publish']) && $i['tanggal_publish'] < $nowStr && !in_array($i['status'], ['published', 'ditolak'], true)));
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
            'konten'        => $konten,
            'statReady'     => $statReady,
            'statToday'     => $statToday,
            'statPublished' => $statPublished,
            'statOverdue'   => $statOverdue,
            'statTotal'     => $statTotal,
            'filterStatus'  => $filterStatus,
            'sortBy'        => $sortBy,
            'platforms'     => $platforms,
            'jenisKonten'   => $jenisKonten,
            'contentTypes'  => $contentTypes,
            'kode_role'     => $role,
            'judul'         => 'Jadwal Upload Medsos',
        ]);
    }

    /**
     * POST /dashboard/jadwal-upload/publish/{id}
     * Jalankan publishing otomatis media gambar ke platform Instagram via Meta Graph API.
     */
    public function publish(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role   = session('kode_role');
        $userId = (int) session('user_id');

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
        $imageUrl = trim($konten['image_url'] ?? '');
        if (empty($imageUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten belum punya gambar untuk dipublish. Silakan upload media gambar terlebih dahulu.',
            ]);
        }

        $graphApiService = new \App\Services\GraphApiService();

        // Validasi 2: image_url harus URL publik (bukan localhost / private IP)
        $urlValidation = $graphApiService->validatePublicUrl($imageUrl);
        if ($urlValidation['status'] === 'gagal') {
            return $this->response->setStatusCode(400)->setJSON($urlValidation);
        }

        // Validasi 3: Caption (Peringatan jika kosong)
        $caption = $konten['caption'] ?? '';
        $warning = null;
        if (empty(trim($caption))) {
            $warning = 'Catatan: Caption konten masih kosong, publishing menyertakan gambar tanpa caption.';
        }

        // Panggil GraphApiService untuk publish ke Meta Instagram
        $result = $graphApiService->publishToInstagram($imageUrl, $caption);

        if ($result['status'] === 'sukses') {
            // Update status ke 'published' di database via TransisiKonten
            $transisi = $this->transisiService->transition($id, 'published', $userId > 0 ? $userId : 1, 'Dipublish otomatis via Meta Graph API Instagram');
            if (! $transisi['ok']) {
                // Fallback direct update jika transisi ditolak oleh rule status awal
                $this->model->updateStatus($id, 'published');
            }

            // Simpan bukti upload ke tabel bukti_upload jika media_id tersedia
            $mediaId = $result['data']['media_id'] ?? null;
            if ($mediaId) {
                try {
                    $buktiModel = new \App\Models\BuktiUploadModel();
                    $buktiModel->insert([
                        'content_id'    => $id,
                        'url_postingan' => 'https://www.instagram.com/p/' . $mediaId,
                        'catatan'       => 'Media ID Meta Instagram: ' . $mediaId,
                        'uploaded_at'   => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $t) {
                    log_message('error', 'Gagal simpan bukti upload: ' . $t->getMessage());
                }
            }

            return $this->response->setJSON([
                'status'  => 'sukses',
                'pesan'   => 'Berhasil mempublikasikan konten ke Instagram via Meta Graph API!',
                'warning' => $warning ?? ($result['data']['warning'] ?? null),
                'data'    => array_merge([
                    'content_id' => $id,
                    'judul'      => $konten['judul_konten'],
                    'image_url'  => $imageUrl,
                    'caption'    => $caption,
                ], $result['data'] ?? []),
            ]);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => $result['pesan'],
                'data'   => $result['data'] ?? [],
            ]);
        }
    }

    /**
     * POST /dashboard/jadwal-upload/publish-otomatis/{id}
     * Jalankan publishing otomatis khusus konten berjenis Foto/Static Post ke Instagram.
     */
    public function publishOtomatis(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role   = session('kode_role');
        $userId = (int) session('user_id');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses untuk melakukan publish otomatis.',
            ]);
        }

        $konten = $this->model->withRelasi()->find($id);
        if (! $konten) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan.',
            ]);
        }

        $namaJenis = $konten['nama_jenis'] ?? '';

        // Validasi Media URL
        $imageUrl = trim($konten['image_url'] ?? '');
        if (empty($imageUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Media konten (gambar/video) belum diunggah. Silakan simpan link media terlebih dahulu.',
            ]);
        }

        $graphApiService = new \App\Services\GraphApiService();

        // Validasi image_url harus URL publik
        $urlValidation = $graphApiService->validatePublicUrl($imageUrl);
        if ($urlValidation['status'] === 'gagal') {
            return $this->response->setStatusCode(400)->setJSON($urlValidation);
        }

        $caption = $konten['caption'] ?? '';
        $warning = null;
        if (empty(trim($caption))) {
            $warning = 'Catatan: Caption konten kosong, publishing menyertakan media saja.';
        }

        // Panggil service untuk publish (mendukung Foto & Video/Reels)
        $result = $graphApiService->publishToInstagram($imageUrl, $caption, $namaJenis);

        if ($result['status'] === 'sukses') {
            // Update status ke 'published' via TransisiKonten
            $transisi = $this->transisiService->transition($id, 'published', $userId > 0 ? $userId : 1, 'Dipublish otomatis via Instagram Login API');
            if (! $transisi['ok']) {
                // Fallback direct update status
                $this->model->protect(false)->update($id, [
                    'status'     => 'published',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Simpan bukti upload ke tabel bukti_upload
            $mediaId = $result['data']['media_id'] ?? null;
            if ($mediaId) {
                try {
                    $db = \Config\Database::connect();
                    // Dapatkan platform_id Instagram
                    $platform = $db->table('platforms')
                        ->groupStart()
                            ->where('bisnis_id', session('bisnis_aktif_id'))
                            ->orWhere('bisnis_id IS NULL')
                        ->groupEnd()
                        ->like('nama_platform', 'Instagram')
                        ->get()->getRowArray();
                    $platformId = $platform ? (int) $platform['id'] : null;

                    $buktiModel = new \App\Models\BuktiUploadModel();
                    $buktiModel->insert([
                        'content_id'     => $id,
                        'platform_id'    => $platformId,
                        'link_postingan' => 'https://www.instagram.com/p/' . $mediaId,
                        'uploaded_by'    => $userId > 0 ? $userId : 1,
                        'uploaded_at'    => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $t) {
                    log_message('error', 'Gagal simpan bukti upload otomatis: ' . $t->getMessage());
                }
            }

            // Update status auto-publish ke 'berhasil'
            $this->model->protect(false)->update($id, [
                'auto_publish_status' => 'berhasil',
                'last_error'          => null,
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
            $this->model->protect(true);

            return $this->response->setJSON([
                'status'  => 'sukses',
                'pesan'   => 'Berhasil mempublikasikan konten ke Instagram secara otomatis!',
                'warning' => $warning ?? ($result['data']['warning'] ?? null),
                'data'    => array_merge([
                    'content_id' => $id,
                    'judul'      => $konten['judul_konten'],
                    'image_url'  => $imageUrl,
                    'caption'    => $caption,
                ], $result['data'] ?? []),
            ]);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Gagal mempublikasikan ke Instagram: ' . $result['pesan'],
                'data'   => $result['data'] ?? [],
            ]);
        }
    }

    /**
     * POST /dashboard/jadwal-upload/jadwalkan/{id}
     * Simpan jadwal publish otomatis (scheduled_at) untuk konten yang sudah di-ACC Final.
     */
    public function jadwalkan(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses untuk menjadwalkan postingan.',
            ]);
        }

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan.',
            ]);
        }

        if ($konten['status'] !== 'acc_final') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Hanya konten dengan status Acc Final yang dapat dijadwalkan untuk auto-publish.',
            ]);
        }

        $rawScheduled = $this->request->getPost('scheduled_at');
        if (empty($rawScheduled)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Tanggal dan jam jadwal publish wajib diisi.',
            ]);
        }

        // Format datetime ke Y-m-d H:i:s
        $scheduledTime = date('Y-m-d H:i:s', strtotime($rawScheduled));
        if (! $scheduledTime || $scheduledTime === '1970-01-01 07:00:00') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Format tanggal dan jam jadwal tidak valid.',
            ]);
        }

        $imageUrl = trim($konten['image_url'] ?? '');
        if (empty($imageUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Media gambar belum diunggah. Silakan simpan link gambar publik terlebih dahulu sebelum menjadwalkan.',
            ]);
        }

        // Simpan ke database
        $this->model->protect(false)->update($id, [
            'scheduled_at'        => $scheduledTime,
            'auto_publish_status' => 'menunggu',
            'publish_attempts'    => 0,
            'last_error'          => null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $this->model->protect(true);

        return $this->response->setJSON([
            'status' => 'sukses',
            'pesan'  => 'Jadwal auto-publish berhasil disimpan untuk: ' . date('d M Y, H:i', strtotime($scheduledTime)) . ' WIB.',
            'data'   => [
                'id'                  => $id,
                'scheduled_at'        => $scheduledTime,
                'auto_publish_status' => 'menunggu',
            ],
        ]);
    }

    /**
     * POST /dashboard/jadwal-upload/batal-jadwal/{id}
     * Batalkan jadwal auto-publish.
     */
    public function batalJadwal(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses untuk membatalkan jadwal.',
            ]);
        }

        $konten = $this->model->find($id);
        if (! $konten) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan.',
            ]);
        }

        $this->model->protect(false)->update($id, [
            'scheduled_at'        => null,
            'auto_publish_status' => null,
            'publish_attempts'    => 0,
            'last_error'          => null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $this->model->protect(true);

        return $this->response->setJSON([
            'status' => 'sukses',
            'pesan'  => 'Jadwal auto-publish berhasil dibatalkan.',
            'data'   => [
                'id'                  => $id,
                'scheduled_at'        => null,
                'auto_publish_status' => null,
            ],
        ]);
    }

    /**
     * POST|GET /dashboard/jadwal-upload/check-scheduled
     * Endpoint background runner yang bisa dipanggil otomatis oleh browser (Web Ping / Local Fallback).
     */
    public function checkScheduled(): \CodeIgniter\HTTP\ResponseInterface
    {
        $autoPublishService = new \App\Services\AutoPublishService();
        $result = $autoPublishService->processDuePosts();

        return $this->response->setJSON([
            'status' => 'sukses',
            'data'   => $result,
        ]);
    }
}



