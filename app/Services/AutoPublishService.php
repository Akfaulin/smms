<?php

namespace App\Services;

use App\Models\BuktiUploadModel;
use App\Models\ContentPlanModel;
use App\Services\GraphApiService;
use App\Services\NotificationService;
use App\Services\TransisiKonten;
use CodeIgniter\Database\BaseConnection;

/**
 * AutoPublishService
 *
 * Service layer untuk memproses auto-publish postingan media sosial terjadwal di background.
 * Berjalan lintas seluruh bisnis (multi-bisnis) tanpa terikat pada session web.
 */
class AutoPublishService
{
    protected ContentPlanModel $contentPlanModel;
    protected BuktiUploadModel $buktiUploadModel;
    protected GraphApiService $graphApiService;
    protected TransisiKonten $transisiService;
    protected NotificationService $notifService;
    protected BaseConnection $db;

    /**
     * Batas maksimal percobaan retry sebelum status dinyatakan 'gagal'.
     */
    public const MAX_ATTEMPTS = 3;

    public function __construct(
        ?ContentPlanModel $contentPlanModel = null,
        ?BuktiUploadModel $buktiUploadModel = null,
        ?GraphApiService $graphApiService = null,
        ?TransisiKonten $transisiService = null,
        ?NotificationService $notifService = null
    ) {
        $this->db               = \Config\Database::connect();
        $this->contentPlanModel = $contentPlanModel ?? new ContentPlanModel();
        $this->buktiUploadModel = $buktiUploadModel ?? new BuktiUploadModel();
        $this->graphApiService  = $graphApiService  ?? new GraphApiService();
        $this->transisiService  = $transisiService  ?? new TransisiKonten();
        $this->notifService     = $notifService     ?? new NotificationService();
    }

    /**
     * Ambil dan proses seluruh postingan yang sudah jatuh tempo (scheduled_at <= NOW).
     *
     * @return array{total: int, sukses: int, gagal: int, dilewati: int, detail: array}
     */
    public function processDuePosts(): array
    {
        // Pastikan timezone konsisten Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');
        $now = date('Y-m-d H:i:s');

        // Query konten yang siap publish: status = acc_final, auto_publish_status = menunggu, scheduled_at <= NOW
        $duePosts = $this->db->table('content_plan cp')
            ->select('cp.*, b.nama_bisnis, jk.nama_jenis')
            ->join('bisnis b', 'b.id = cp.bisnis_id', 'left')
            ->join('jenis_konten jk', 'jk.id = cp.jenis_konten_id', 'left')
            ->where('cp.status', 'acc_final')
            ->where('cp.auto_publish_status', 'menunggu')
            ->where('cp.scheduled_at <=', $now)
            ->orderBy('cp.scheduled_at', 'ASC')
            ->get()
            ->getResultArray();

        $summary = [
            'total'    => count($duePosts),
            'sukses'   => 0,
            'gagal'    => 0,
            'dilewati' => 0,
            'detail'   => [],
        ];

        if (empty($duePosts)) {
            return $summary;
        }

        foreach ($duePosts as $post) {
            $contentId = (int) $post['id'];
            $judul     = $post['judul_konten'];

            // -----------------------------------------------------------------
            // 1. Anti-Double-Publish Guard (Atomic Flag Locking)
            // Kunci baris dengan update status ke 'diproses'.
            // Jika affectedRows === 0, berarti sudah dikunci worker lain secara simultan.
            // -----------------------------------------------------------------
            $this->db->table('content_plan')
                ->where('id', $contentId)
                ->where('auto_publish_status', 'menunggu')
                ->update([
                    'auto_publish_status' => 'diproses',
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);

            if ($this->db->affectedRows() === 0) {
                $summary['dilewati']++;
                $summary['detail'][] = [
                    'id'     => $contentId,
                    'judul'  => $judul,
                    'status' => 'dilewati',
                    'pesan'  => 'Konten sedang diproses oleh instance lain (locked).',
                ];
                continue;
            }

            // -----------------------------------------------------------------
            // 2. Eksekusi Publishing
            // -----------------------------------------------------------------
            $result = $this->publishSinglePost($post);

            if ($result['sukses']) {
                $summary['sukses']++;
            } else {
                $summary['gagal']++;
            }

            $summary['detail'][] = [
                'id'     => $contentId,
                'judul'  => $judul,
                'status' => $result['sukses'] ? 'sukses' : 'gagal',
                'pesan'  => $result['pesan'],
            ];
        }

        return $summary;
    }

    /**
     * Eksekusi publish untuk satu konten tertentu.
     *
     * @param array $konten Data row content_plan
     * @return array{sukses: bool, pesan: string}
     */
    protected function publishSinglePost(array $konten): array
    {
        $contentId = (int) $konten['id'];
        $judul     = $konten['judul_konten'];
        $caption   = $konten['caption'] ?? '';
        $imageUrl  = trim($konten['image_url'] ?? '');
        $attempts  = (int) ($konten['publish_attempts'] ?? 0) + 1;
        $bisnisId  = !empty($konten['bisnis_id']) ? (int) $konten['bisnis_id'] : null;
        $namaJenis = $konten['nama_jenis'] ?? '';

        // Fallback user ID untuk jejak transisi status audit (Admin Medsos / Uploader / Superadmin)
        $userId = (int) ($konten['assigned_uploader'] ?: ($konten['dibuat_oleh'] ?: 1));

        // Validasi ketersediaan media gambar/video
        if (empty($imageUrl)) {
            $errorMsg = 'Media gambar/video belum diunggah untuk postingan ini.';
            $this->handleFailure($contentId, $konten, $attempts, $errorMsg);
            return ['sukses' => false, 'pesan' => $errorMsg];
        }

        // Panggil GraphApiService untuk publish ke Meta Instagram (mendukung Foto & Video/Reels)
        try {
            $serviceToUse = new GraphApiService($bisnisId);
            $apiResult = $serviceToUse->publishToInstagram($imageUrl, $caption, $namaJenis);
        } catch (\Throwable $e) {
            $apiResult = [
                'status' => 'gagal',
                'pesan'  => 'Exception: ' . $e->getMessage(),
            ];
        }

        // ---------------------------------------------------------------------
        // JIKA PUBLISH BERHASIL
        // ---------------------------------------------------------------------
        if (($apiResult['status'] ?? '') === 'sukses') {
            $mediaId = $apiResult['data']['media_id'] ?? null;
            $linkPostingan = $mediaId ? 'https://www.instagram.com/p/' . $mediaId : 'https://instagram.com';

            // 1. Simpan bukti upload
            try {
                $platform = $this->db->table('platforms')
                    ->groupStart()
                        ->where('bisnis_id', $bisnisId)
                        ->orWhere('bisnis_id IS NULL')
                    ->groupEnd()
                    ->like('nama_platform', 'Instagram')
                    ->get()
                    ->getRowArray();

                $platformId = $platform ? (int) $platform['id'] : null;

                $this->buktiUploadModel->insert([
                    'content_id'     => $contentId,
                    'platform_id'    => $platformId,
                    'link_postingan' => $linkPostingan,
                    'uploaded_by'    => $userId,
                    'uploaded_at'    => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $t) {
                log_message('error', "[AutoPublishService] Gagal simpan bukti upload ID {$contentId}: " . $t->getMessage());
            }

            // 2. Transisi status ke 'published' melalui TransisiKonten Service
            $transisi = $this->transisiService->transition(
                $contentId,
                'published',
                $userId,
                'Dipublish otomatis sesuai jadwal oleh Background Auto-Publish Service'
            );

            // Fallback direct update jika rule transisi terhalang
            if (! $transisi['ok']) {
                $this->contentPlanModel->updateStatus($contentId, 'published');
            }

            // 3. Update status auto-publish ke 'berhasil'
            $this->contentPlanModel->protect(false)->update($contentId, [
                'auto_publish_status' => 'berhasil',
                'publish_attempts'    => $attempts,
                'last_error'          => null,
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
            $this->contentPlanModel->protect(true);

            return [
                'sukses' => true,
                'pesan'  => $apiResult['pesan'] ?? "Berhasil dipublish ke Instagram (Media ID: {$mediaId}).",
            ];
        }

        // ---------------------------------------------------------------------
        // JIKA PUBLISH GAGAL
        // ---------------------------------------------------------------------
        $errorMsg = $apiResult['pesan'] ?? 'Gagal mempublikasikan postingan ke Instagram API.';
        $this->handleFailure($contentId, $konten, $attempts, $errorMsg);

        return [
            'sukses' => false,
            'pesan'  => $errorMsg,
        ];
    }

    /**
     * Tangani kegagalan publish (retry logic & notifikasi).
     */
    protected function handleFailure(int $contentId, array $konten, int $attempts, string $errorMsg): void
    {
        $judul = $konten['judul_konten'] ?? "ID #{$contentId}";

        // Tentukan apakah masih bisa di-retry atau sudah gagal permanen
        $statusBaru = ($attempts < self::MAX_ATTEMPTS) ? 'menunggu' : 'gagal';

        $this->contentPlanModel->protect(false)->update($contentId, [
            'auto_publish_status' => $statusBaru,
            'publish_attempts'    => $attempts,
            'last_error'          => $errorMsg,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $this->contentPlanModel->protect(true);

        // Jika sudah mencapai batas percobaan (gagal permanen), kirim notifikasi ke Admin Medsos & Manager
        if ($statusBaru === 'gagal') {
            log_message('error', "[AutoPublishService] Konten ID {$contentId} gagal dipublish setelah {$attempts} percobaan. Error: {$errorMsg}");

            $judulNotif = 'Auto-Publish Konten Gagal';
            $pesanNotif = "Postingan \"{$judul}\" gagal dipublish otomatis setelah {$attempts} percobaan. Alasan: {$errorMsg}";
            $urlNotif   = '/dashboard/jadwal-upload';

            // Kirim ke role admin_medsos dan manager
            $this->notifService->kirimKeRole('admin_medsos', $judulNotif, $pesanNotif, $urlNotif);
            $this->notifService->kirimKeRole('manager', $judulNotif, $pesanNotif, $urlNotif);

            // Kirim ke assigned uploader jika ada
            $uploaderId = (int) ($konten['assigned_uploader'] ?? 0);
            if ($uploaderId > 0) {
                $this->notifService->kirim($uploaderId, $judulNotif, $pesanNotif, $urlNotif);
            }
        }
    }
}
