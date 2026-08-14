<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ContentPlanModel;
use App\Models\BuktiUploadModel;
use App\Services\GraphApiService;
use App\Services\TransisiKonten;
use App\Services\NotificationService;

/**
 * PublishScheduledContent
 *
 * Spark CLI Command untuk memeriksa dan mengeksekusi konten yang telah dijadwalkan (Auto-Publish).
 * Dijalankan secara otomatis setiap menit melalui Cron Job (Linux) atau Task Scheduler (Windows).
 *
 * Penggunaan:
 *   php spark publish:scheduled
 */
class PublishScheduledContent extends BaseCommand
{
    protected $group       = 'Publishing';
    protected $name        = 'publish:scheduled';
    protected $description = 'Periksa dan publikasikan konten acc_final yang sudah mencapai jadwal publish secara otomatis.';

    public function run(array $params)
    {
        $startTime = microtime(true);
        $now = date('Y-m-d H:i:s');

        CLI::write("====================================================================", 'yellow');
        CLI::write("       SMMS BACKGROUND AUTO-PUBLISH SCHEDULER                      ", 'cyan');
        CLI::write("       Server Time: {$now}                                         ", 'white');
        CLI::write("====================================================================", 'yellow');

        $db               = \Config\Database::connect();
        $contentModel     = new ContentPlanModel();
        $buktiModel       = new BuktiUploadModel();
        $graphApiService  = new GraphApiService();
        $transisiService  = new TransisiKonten();
        $notifService     = new NotificationService();

        // 1. Ambil semua konten yang memenuhi kriteria auto-publish:
        // - status: acc_final
        // - is_scheduled: 1
        // - scheduled_at <= waktu sekarang
        // - is_processing: 0 (tidak sedang diproses worker lain)
        // - publish_attempt < 3 (maksimal 3 kali retry)
        $scheduledItems = $contentModel->withRelasi()
            ->where('content_plan.status', 'acc_final')
            ->where('content_plan.is_scheduled', 1)
            ->where('content_plan.scheduled_at <=', $now)
            ->where('content_plan.is_processing', 0)
            ->where('content_plan.publish_attempt <', 3)
            ->orderBy('content_plan.scheduled_at', 'ASC')
            ->findAll();

        $totalFound = count($scheduledItems);
        if ($totalFound === 0) {
            CLI::write("[INFO] Tidak ada konten terjadwal yang perlu dipublikasikan saat ini.\n", 'light_gray');
            return;
        }

        CLI::write("[INFO] Ditemukan {$totalFound} konten yang siap diproses untuk auto-publish.\n", 'green');

        $successCount = 0;
        $failedCount  = 0;

        foreach ($scheduledItems as $index => $item) {
            $contentId = (int) $item['id'];
            $judul     = $item['judul_konten'];
            $attempt   = (int) $item['publish_attempt'] + 1;

            CLI::write("--------------------------------------------------------------------", 'dark_gray');
            CLI::write(sprintf("[%d/%d] Memproses Konten ID #%d: \"%s\" (Percobaan %d/3)...", $index + 1, $totalFound, $contentId, $judul, $attempt), 'white');

            // 2. Atomic Row Locking: Set is_processing = 1 & increment publish_attempt
            $lockUpdated = $db->table('content_plan')
                ->where('id', $contentId)
                ->where('is_processing', 0)
                ->update([
                    'is_processing'   => 1,
                    'publish_attempt' => $attempt,
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);

            if (! $lockUpdated || $db->affectedRows() === 0) {
                CLI::write(" -> [SKIP] Konten ID #{$contentId} sedang diproses oleh proses lain.", 'yellow');
                continue;
            }

            try {
                // Ambil data terbaru setelah lock
                $freshKonten = $contentModel->withRelasi()->find($contentId);
                if (! $freshKonten) {
                    CLI::write(" -> [ERROR] Konten ID #{$contentId} tidak ditemukan di database.", 'red');
                    $this->releaseLock($db, $contentId, 'Konten tidak ditemukan di database.');
                    $failedCount++;
                    continue;
                }

                // 3. Eksekusi Publishing via GraphApiService
                $publishRes = $graphApiService->publishContentPlan($freshKonten);

                if ($publishRes['status'] === 'sukses') {
                    // Update status ke 'published' via TransisiKonten
                    $uploaderId = (int) ($freshKonten['assigned_uploader'] ?: ($freshKonten['dibuat_oleh'] ?: 1));
                    $transisi   = $transisiService->transition(
                        $contentId,
                        'published',
                        $uploaderId,
                        'Dipublish otomatis sesuai jadwal oleh Background Scheduler'
                    );

                    if (! $transisi['ok']) {
                        // Fallback update status langsung jika rule memblokir
                        $contentModel->updateStatus($contentId, 'published');
                    }

                    // Reset flags pada tabel content_plan
                    $db->table('content_plan')->where('id', $contentId)->update([
                        'is_scheduled'       => 0,
                        'is_processing'      => 0,
                        'last_publish_error' => null,
                        'updated_at'         => date('Y-m-d H:i:s'),
                    ]);

                    // Simpan bukti upload jika media_id tersedia
                    $mediaId = $publishRes['data']['media_id'] ?? null;
                    if ($mediaId) {
                        try {
                            $platform = $db->table('platforms')
                                ->groupStart()
                                    ->where('bisnis_id', $freshKonten['bisnis_id'])
                                    ->orWhere('bisnis_id IS NULL')
                                ->groupEnd()
                                ->like('nama_platform', 'Instagram')
                                ->get()->getRowArray();
                            $platformId = $platform ? (int) $platform['id'] : null;

                            $buktiModel->insert([
                                'content_id'     => $contentId,
                                'platform_id'    => $platformId,
                                'link_postingan' => 'https://www.instagram.com/p/' . $mediaId,
                                'catatan'        => 'Auto-publish via Background Scheduler. Media ID: ' . $mediaId,
                                'uploaded_by'    => $uploaderId,
                                'uploaded_at'    => date('Y-m-d H:i:s'),
                            ]);
                        } catch (\Throwable $t) {
                            log_message('error', '[PublishScheduledContent] Gagal simpan bukti upload: ' . $t->getMessage());
                        }
                    }

                    // Kirim notifikasi sukses
                    $notifService->notifikasiAutoPublishSukses($freshKonten);

                    CLI::write(" -> [SUKSES] Berhasil dipublish ke Instagram! Media ID: " . ($mediaId ?: 'N/A'), 'green');
                    $successCount++;
                } else {
                    $errorMsg = $publishRes['pesan'] ?? 'Terjadi kesalahan saat mempublikasikan ke Meta Graph API.';

                    // Simpan error & release lock
                    $this->releaseLock($db, $contentId, $errorMsg);

                    // Kirim notifikasi kegagalan
                    $notifService->notifikasiAutoPublishGagal($freshKonten, $errorMsg, $attempt);

                    CLI::write(" -> [GAGAL] " . $errorMsg, 'red');
                    if ($attempt >= 3) {
                        CLI::write("    -> [PERINGATAN] Batas 3x percobaan telah tercapai. Konten membutuhkan tindakan manual.", 'light_red');
                    }
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $errorMsg = 'Exception: ' . $e->getMessage();
                log_message('error', "[PublishScheduledContent Exception #{$contentId}] " . $e->getMessage() . "\n" . $e->getTraceAsString());

                $this->releaseLock($db, $contentId, $errorMsg);
                if (isset($freshKonten)) {
                    $notifService->notifikasiAutoPublishGagal($freshKonten, $errorMsg, $attempt);
                }

                CLI::write(" -> [EXCEPTION] " . $e->getMessage(), 'red');
                $failedCount++;
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        CLI::write("====================================================================", 'yellow');
        CLI::write(sprintf(" Selesai dalam %s detik. Sukses: %d, Gagal: %d", $elapsed, $successCount, $failedCount), 'cyan');
        CLI::write("====================================================================\n", 'yellow');
    }

    /**
     * Release processing lock dan simpan pesan error
     */
    private function releaseLock(\CodeIgniter\Database\BaseConnection $db, int $contentId, string $errorMessage): void
    {
        $db->table('content_plan')->where('id', $contentId)->update([
            'is_processing'      => 0,
            'last_publish_error' => $errorMessage,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }
}
