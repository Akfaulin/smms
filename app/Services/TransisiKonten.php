<?php

namespace App\Services;

use App\Models\ContentPlanModel;
use App\Models\ContentStatusLogModel;

/**
 * TransisiKonten — State Machine Approval Service
 *
 * Implementasi fungsi terpusat canTransition() sesuai spesifikasi §4.3.
 *
 * SEMUA perubahan status konten WAJIB melalui metode transition() di class ini.
 * Jangan update kolom `status` di tabel `content_plan` langsung dari controller
 * atau form tanpa melalui class ini.
 *
 * Sesuai §4.3, fungsi mengecek 3 hal:
 *   1. Apakah transisi status_lama → status_baru valid?
 *   2. Apakah role user diizinkan untuk transisi tersebut?
 *   3. Apakah catatan wajib diisi (dan sudah diisi)?
 *
 * Override oleh owner & superadmin (§4.4):
 *   Bisa melewati semua pembatasan transisi, tapi WAJIB tetap masuk log.
 */
class TransisiKonten
{
    /**
     * Tabel transisi valid sesuai §4.3.
     *
     * Format:
     *   'status_lama' => [
     *       'status_baru' => [
     *           'roles'          => [...role yang diizinkan],
     *           'catatan_wajib'  => true|false,
     *       ],
     *   ]
     *
     * Role 'owner' dan 'superadmin' dapat melakukan semua transisi (override §4.4),
     * sehingga tidak perlu didaftarkan eksplisit di sini — ditangani secara khusus
     * di canTransition().
     */
    private const TRANSISI_VALID = [
        'ide_diajukan' => [
            'acc_ide'  => ['roles' => ['manager'], 'catatan_wajib' => false],
            'revisi'   => ['roles' => ['manager'], 'catatan_wajib' => true],
            'ditolak'  => ['roles' => ['manager'], 'catatan_wajib' => true],
        ],
        'revisi' => [
            'ide_diajukan' => ['roles' => ['content_creator'], 'catatan_wajib' => false],
        ],
        'acc_ide' => [
            'in_design' => ['roles' => ['content_creator'], 'catatan_wajib' => false],
        ],
        'in_design' => [
            'review_design' => ['roles' => ['content_creator'], 'catatan_wajib' => false],
        ],
        'review_design' => [
            'acc_final' => ['roles' => ['manager'], 'catatan_wajib' => false],
            'revisi'    => ['roles' => ['manager'], 'catatan_wajib' => true],
        ],
        'acc_final' => [
            'published' => ['roles' => ['admin_medsos'], 'catatan_wajib' => false],
        ],
    ];

    /**
     * Role yang bisa override semua transisi (§4.4).
     * Tetap wajib masuk content_status_log.
     */
    private const ROLE_OVERRIDE = ['owner', 'superadmin'];

    /**
     * Status terminal — tidak bisa ditransisi ke status lain
     * kecuali oleh override owner/superadmin.
     */
    private const STATUS_TERMINAL = ['published', 'ditolak'];

    private ContentPlanModel $contentPlanModel;
    private ContentStatusLogModel $statusLogModel;

    public function __construct()
    {
        $this->contentPlanModel = new ContentPlanModel();
        $this->statusLogModel   = new ContentStatusLogModel();
    }

    // -------------------------------------------------------------------------
    // Metode Publik Utama
    // -------------------------------------------------------------------------

    /**
     * Validasi apakah transisi status boleh dilakukan.
     *
     * @param int    $contentId  ID konten di tabel content_plan
     * @param string $statusBaru Status baru yang ingin dituju
     * @param int    $userId     ID user yang melakukan aksi
     * @param string $catatan    Catatan (wajib untuk transisi tertentu)
     *
     * @return array{ok: bool, pesan: string}
     */
    public function canTransition(int $contentId, string $statusBaru, int $userId, string $catatan = ''): array
    {
        // 1. Ambil data konten
        $konten = $this->contentPlanModel->find($contentId);
        if (! $konten) {
            return $this->gagal('Konten tidak ditemukan.');
        }

        // 2. Ambil role user
        $db       = \Config\Database::connect();
        $userRow  = $db->table('users u')
                       ->select('u.id, r.kode_role')
                       ->join('roles r', 'r.id = u.role_id', 'left')
                       ->where('u.id', $userId)
                       ->where('u.status', 'aktif')
                       ->get()
                       ->getRowArray();

        if (! $userRow) {
            return $this->gagal('User tidak ditemukan atau tidak aktif.');
        }

        $kodeRole   = $userRow['kode_role'] ?? null;
        $statusLama = $konten['status'];

        // 3. Jika sudah sama, tidak perlu transisi
        if ($statusLama === $statusBaru) {
            return $this->gagal("Status konten sudah '{$statusBaru}', tidak ada perubahan.");
        }

        // 4. Cek apakah ini transisi standar yang terdaftar di TRANSISI_VALID
        $isTransisiStandar = isset(self::TRANSISI_VALID[$statusLama][$statusBaru]);

        // Owner & Superadmin: override (§4.4) — boleh melakukansemua transisi.
        // Jika transisinya adalah transisi standar (misal ide_diajukan -> acc_ide), tidak perlu memaksa catatan override.
        // Jika transisinya NON-standar (override aturan), barulah catatan wajib diisi sebagai jejak audit.
        if (in_array($kodeRole, self::ROLE_OVERRIDE, true)) {
            $aturan = self::TRANSISI_VALID[$statusLama][$statusBaru] ?? null;
            if ($aturan && $aturan['catatan_wajib'] && empty(trim($catatan))) {
                return $this->gagal("Catatan wajib diisi untuk transisi ke '{$statusBaru}'.");
            }
            if (! $aturan && empty(trim($catatan))) {
                return $this->gagal('Override oleh ' . strtoupper($kodeRole) . ' memerlukan catatan sebagai jejak audit.');
            }
            return $this->sukses($konten, $kodeRole, $statusLama, $statusBaru);
        }

        // 5. Cek apakah transisi dari status_lama ini terdaftar
        if (! isset(self::TRANSISI_VALID[$statusLama])) {
            // Bisa jadi status terminal (published/ditolak)
            if (in_array($statusLama, self::STATUS_TERMINAL, true)) {
                return $this->gagal("Konten sudah berstatus '{$statusLama}' dan tidak dapat diubah.");
            }
            return $this->gagal("Tidak ada transisi yang valid dari status '{$statusLama}'.");
        }

        // 6. Cek apakah status_baru ada di daftar transisi dari status_lama
        $aturan = self::TRANSISI_VALID[$statusLama][$statusBaru] ?? null;
        if (! $aturan) {
            return $this->gagal(
                "Transisi dari '{$statusLama}' ke '{$statusBaru}' tidak diizinkan."
            );
        }

        // 7. Cek apakah role user sesuai
        if (! in_array($kodeRole, $aturan['roles'], true)) {
            $rolesDiizinkan = implode(', ', $aturan['roles']);
            return $this->gagal(
                "Role '{$kodeRole}' tidak berwenang untuk transisi ini. Role yang diizinkan: {$rolesDiizinkan}."
            );
        }

        // 8. Cek catatan wajib
        if ($aturan['catatan_wajib'] && empty(trim($catatan))) {
            return $this->gagal(
                "Catatan wajib diisi untuk transisi ke '{$statusBaru}'."
            );
        }

        return $this->sukses($konten, $kodeRole, $statusLama, $statusBaru);
    }

    /**
     * Eksekusi transisi status.
     * Memanggil canTransition() terlebih dahulu, lalu update DB jika valid.
     *
     * @return array{ok: bool, pesan: string, data?: array}
     */
    public function transition(int $contentId, string $statusBaru, int $userId, string $catatan = ''): array
    {
        // Validasi dulu
        $cek = $this->canTransition($contentId, $statusBaru, $userId, $catatan);
        if (! $cek['ok']) {
            return $cek;
        }

        $statusLama = $cek['status_lama'];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update status di content_plan (Gunakan protect(false) / updateStatus agar tidak terfilter allowedFields)
            $this->contentPlanModel->updateStatus($contentId, $statusBaru);

            // Simpan ke audit log
            $this->statusLogModel->insert([
                'content_id'  => $contentId,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'user_id'     => $userId,
                'catatan'     => $catatan ?: null,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            // --- FASE 9.3: AI Pre-Review Checklist ---
            // Jika konten baru saja ditransisi ke 'review_design', 
            // jalankan pre-review check otomatis oleh AI secara asinkron/non-blocking
            // Di sini kita jalankan sinkron (karena ini PoC/demo).
            if ($statusBaru === 'review_design') {
                $ai = new \App\Services\AiService();
                $kontenUpdated = $this->contentPlanModel->find($contentId);
                if ($kontenUpdated) {
                    $ai->preReviewCheck($kontenUpdated);
                }
            }

            return [
                'ok'          => true,
                'pesan'       => "Status konten berhasil diubah dari '{$statusLama}' ke '{$statusBaru}'.",
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[TransisiKonten::transition] ' . $e->getMessage());
            return $this->gagal('Terjadi kesalahan sistem saat menyimpan transisi. Silakan coba lagi.');
        }
    }

    // -------------------------------------------------------------------------
    // Helper Statis
    // -------------------------------------------------------------------------

    /**
     * Kembalikan daftar transisi yang tersedia untuk status tertentu,
     * difilter berdasarkan role user. Berguna untuk render tombol aksi di UI.
     *
     * @return string[] — daftar status_baru yang bisa dituju
     */
    public static function transisiTersedia(string $statusSekarang, string $kodeRole): array
    {
        // Override role bisa ke mana saja kecuali status yang sama
        if (in_array($kodeRole, self::ROLE_OVERRIDE, true)) {
            $semuaStatus = [
                'ide_diajukan', 'acc_ide', 'in_design', 'review_design',
                'revisi', 'acc_final', 'published', 'ditolak',
            ];
            return array_values(array_filter($semuaStatus, fn($s) => $s !== $statusSekarang));
        }

        $transisiDariStatus = self::TRANSISI_VALID[$statusSekarang] ?? [];
        $hasil = [];

        foreach ($transisiDariStatus as $statusBaru => $aturan) {
            if (in_array($kodeRole, $aturan['roles'], true)) {
                $hasil[] = $statusBaru;
            }
        }

        return $hasil;
    }

    /**
     * Ambil label tampilan untuk kode status.
     */
    public static function labelStatus(string $status): string
    {
        return match ($status) {
            'ide_diajukan'  => 'Ide Diajukan',
            'acc_ide'       => 'Acc Ide',
            'in_design'     => 'Dalam Proses Design',
            'review_design' => 'Review Design',
            'revisi'        => 'Perlu Revisi',
            'acc_final'     => 'Acc Final',
            'published'     => 'Published',
            'ditolak'       => 'Ditolak',
            default         => ucwords(str_replace('_', ' ', $status)),
        };
    }

    // -------------------------------------------------------------------------
    // Internal Helper
    // -------------------------------------------------------------------------

    private function gagal(string $pesan): array
    {
        return ['ok' => false, 'pesan' => $pesan];
    }

    private function sukses(array $konten, string $kodeRole, string $statusLama, string $statusBaru): array
    {
        return [
            'ok'          => true,
            'pesan'       => 'Validasi berhasil.',
            'kode_role'   => $kodeRole,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
        ];
    }
}
