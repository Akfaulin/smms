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
     * Jalankan publishing otomatis media gambar ke platform Instagram via Meta Graph API.
     */
    public function publish(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role   = session('kode_role');
        $userId = (int) session('user_id');

        if (false && ! in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)) {
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
        // Validasi 2 & 3: Caption dan Image URL
        $caption = $konten['caption'] ?? '';
        $warning = null;
        if (empty(trim($caption))) {
            $warning = 'Catatan: Caption konten masih kosong, publishing menyertakan gambar tanpa caption.';
        }

        $db = \Config\Database::connect();
        $jenisKonten = $db->table('jenis_konten')->where('id', $konten['jenis_konten_id'])->get()->getRowArray();
        $namaJenisLower = $jenisKonten ? strtolower(trim($jenisKonten['nama_jenis'])) : '';
        $isCarousel = ($namaJenisLower === 'carousel');
        $isReels = ($namaJenisLower === 'reels / video' || $namaJenisLower === 'reels');
        $isStory = ($namaJenisLower === 'story' || $namaJenisLower === 'stories');

        if ($isCarousel) {
            // Bersihkan baris kosong dan buat array
            $urls = array_filter(array_map('trim', explode("\n", $imageUrl)));
            if (count($urls) < 2) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Konten Carousel membutuhkan minimal 2 link gambar (dipisah dengan Enter/Baris Baru).',
                ]);
            }
            $result = $graphApiService->publishCarouselToInstagram(array_values($urls), $caption);
        } elseif ($isReels) {
            // Bersihkan baris kosong dan buat array (Baris 1 = videoUrl, Baris 2 = coverUrl)
            $urls = array_filter(array_map('trim', explode("\n", $imageUrl)));
            $videoUrl = $urls[0] ?? '';
            $coverUrl = $urls[1] ?? null;

            if (empty($videoUrl)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Konten Reels membutuhkan minimal 1 link video (.mp4).',
                ]);
            }

            // Validasi ektensi MP4 sederhana
            $parsedPath = parse_url($videoUrl, PHP_URL_PATH);
            if ($parsedPath && substr(strtolower($parsedPath), -4) !== '.mp4' && strpos($videoUrl, 'drive.google.com') === false) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Format video tidak didukung. Harap masukkan link file video .mp4 (atau link Google Drive).',
                ]);
            }

            $result = $graphApiService->publishReelsToInstagram($videoUrl, $caption, $coverUrl);
        } elseif ($isStory) {
            // Story (bisa berupa video atau image)
            // Gunakan baris pertama
            $urls = array_filter(array_map('trim', explode("\n", $imageUrl)));
            $mediaUrl = $urls[0] ?? '';
            
            if (empty($mediaUrl)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Konten Story membutuhkan minimal 1 link media (gambar atau video).',
                ]);
            }
            
            $result = $graphApiService->publishStoryToInstagram($mediaUrl, $caption, 'AUTO');
        } else {
            // Single Image
            $urlValidation = $graphApiService->validatePublicUrl($imageUrl);
            if ($urlValidation['status'] === 'gagal') {
                return $this->response->setStatusCode(400)->setJSON($urlValidation);
            }
            $result = $graphApiService->publishToInstagram($imageUrl, $caption);
        }

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

        // Validasi Jenis Konten: Harus Foto / Static Post / Carousel / Reels / Story
        $namaJenis = strtolower($konten['nama_jenis'] ?? '');
        if ($namaJenis !== 'static post' && $namaJenis !== 'foto' && $namaJenis !== 'carousel' && $namaJenis !== 'reels / video' && $namaJenis !== 'reels' && $namaJenis !== 'story' && $namaJenis !== 'stories') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Publish otomatis hanya didukung untuk konten dengan tipe Foto / Static Post / Carousel / Reels / Story.',
            ]);
        }
        $isCarousel = ($namaJenis === 'carousel');
        $isReels = ($namaJenis === 'reels / video' || $namaJenis === 'reels');
        $isStory = ($namaJenis === 'story' || $namaJenis === 'stories');

        // Validasi Image URL
        $imageUrl = trim($konten['image_url'] ?? '');
        if (empty($imageUrl)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Gambar konten belum diunggah. Silakan upload media gambar terlebih dahulu.',
            ]);
        }

        $graphApiService = new \App\Services\GraphApiService();

        $caption = $konten['caption'] ?? '';
        $warning = null;
        if (empty(trim($caption))) {
            $warning = 'Catatan: Caption konten kosong, publishing menyertakan gambar saja.';
        }

        if ($isCarousel) {
            // Bersihkan baris kosong dan buat array
            $urls = array_filter(array_map('trim', explode("\n", $imageUrl)));
            if (count($urls) < 2) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Konten Carousel membutuhkan minimal 2 link gambar (dipisah dengan Enter/Baris Baru).',
                ]);
            }
            $result = $graphApiService->publishCarouselToInstagram(array_values($urls), $caption);
        } elseif ($isReels) {
            // Bersihkan baris kosong dan buat array (Baris 1 = videoUrl, Baris 2 = coverUrl)
            $urls = array_filter(array_map('trim', explode("\n", $imageUrl)));
            $videoUrl = $urls[0] ?? '';
            $coverUrl = $urls[1] ?? null;

            if (empty($videoUrl)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Konten Reels membutuhkan minimal 1 link video (.mp4).',
                ]);
            }

            // Validasi ektensi MP4 sederhana
            $parsedPath = parse_url($videoUrl, PHP_URL_PATH);
            if ($parsedPath && substr(strtolower($parsedPath), -4) !== '.mp4' && strpos($videoUrl, 'drive.google.com') === false) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Format video tidak didukung. Harap masukkan link file video .mp4 (atau link Google Drive).',
                ]);
            }

            $result = $graphApiService->publishReelsToInstagram($videoUrl, $caption, $coverUrl);
        } elseif ($isStory) {
            // Story (bisa berupa video atau image)
            // Gunakan baris pertama
            $urls = array_filter(array_map('trim', explode("\n", $imageUrl)));
            $mediaUrl = $urls[0] ?? '';
            
            if (empty($mediaUrl)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'gagal',
                    'pesan'  => 'Konten Story membutuhkan minimal 1 link media (gambar atau video).',
                ]);
            }
            
            $result = $graphApiService->publishStoryToInstagram($mediaUrl, $caption, 'AUTO');
        } else {
            // Single Image
            $urlValidation = $graphApiService->validatePublicUrl($imageUrl);
            if ($urlValidation['status'] === 'gagal') {
                return $this->response->setStatusCode(400)->setJSON($urlValidation);
            }
            $result = $graphApiService->publishToInstagram($imageUrl, $caption);
        }

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
     * POST /dashboard/jadwal-upload/atur-jadwal/{id}
     * Simpan / atur tanggal dan jam publish otomatis untuk konten acc_final.
     */
    public function aturJadwal(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role   = session('kode_role');
        $userId = (int) session('user_id');

        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner', 'manager'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses untuk mengatur jadwal publish.',
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
                'pesan'  => 'Hanya konten dengan status Acc Final yang dapat dijadwalkan auto-publish.',
            ]);
        }

        $tanggal = trim($this->request->getPost('tanggal_publish') ?? '');
        $jam     = trim($this->request->getPost('jam_publish') ?? '');

        if (empty($tanggal)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Tanggal publish wajib diisi.',
            ]);
        }

        if (empty($jam)) {
            $jam = '10:00'; // default jika tidak diisi
        }

        // Format jam menjadi H:i:s
        $jamFormatted = (strlen($jam) === 5) ? $jam . ':00' : $jam;
        $scheduledAt  = $tanggal . ' ' . $jamFormatted;

        // Validasi format tanggal & jam
        if (strtotime($scheduledAt) === false) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Format tanggal atau jam publish tidak valid.',
            ]);
        }

        $this->model->protect(false)->update($id, [
            'tanggal_publish'    => $tanggal,
            'jam_publish'        => $jamFormatted,
            'scheduled_at'       => $scheduledAt,
            'is_scheduled'       => 1,
            'publish_attempt'    => 0,
            'last_publish_error' => null,
            'is_processing'      => 0,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'sukses',
            'pesan'  => 'Jadwal auto-publish berhasil disimpan untuk ' . date('d M Y H:i', strtotime($scheduledAt)) . '.',
            'data'   => [
                'id'              => $id,
                'tanggal_publish' => $tanggal,
                'jam_publish'     => $jamFormatted,
                'scheduled_at'    => $scheduledAt,
                'is_scheduled'    => 1,
            ],
        ]);
    }

    /**
     * POST /dashboard/jadwal-upload/batal-jadwal/{id}
     * Batalkan jadwal auto-publish untuk konten.
     */
    public function batalJadwal(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner', 'manager'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses.',
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
            'is_scheduled'  => 0,
            'is_processing' => 0,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'sukses',
            'pesan'  => 'Jadwal auto-publish berhasil dibatalkan.',
        ]);
    }

    /**
     * POST /dashboard/jadwal-upload/retry/{id}
     * Reset status percobaan publish agar bisa dicoba kembali oleh scheduler atau langsung.
     */
    public function retryPublish(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $role = session('kode_role');
        if (! in_array($role, ['admin_medsos', 'superadmin', 'owner', 'manager'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Anda tidak memiliki hak akses.',
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
            'publish_attempt'    => 0,
            'last_publish_error' => null,
            'is_processing'      => 0,
            'is_scheduled'       => 1,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'sukses',
            'pesan'  => 'Status publish berhasil di-reset. Konten siap dicoba ulang.',
        ]);
    }
}


