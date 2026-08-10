<?php

namespace App\Services;

use App\Models\NotificationModel;

/**
 * NotificationService
 *
 * Service untuk mengirimkan notifikasi in-app ke user.
 * Dipanggil oleh TransisiKonten setelah setiap transisi status berhasil.
 */
class NotificationService
{
    private NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    /**
     * Kirim notifikasi ke satu user.
     */
    public function kirim(int $userId, string $judul, string $pesan, string $url = '/dashboard/content-plan'): void
    {
        if ($userId <= 0) return;

        $this->model->insert([
            'user_id'    => $userId,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'url'        => $url,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Kirim notifikasi ke semua user dengan role tertentu.
     */
    public function kirimKeRole(string $kodeRole, string $judul, string $pesan, string $url = '/dashboard/content-plan'): void
    {
        $db    = \Config\Database::connect();
        $users = $db->table('users u')
            ->select('u.id')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.kode_role', $kodeRole)
            ->where('u.status', 'aktif')
            ->get()->getResultArray();

        foreach ($users as $user) {
            $this->kirim((int) $user['id'], $judul, $pesan, $url);
        }
    }

    /**
     * Buat notifikasi otomatis berdasarkan transisi status konten.
     *
     * @param array  $konten       Data konten (id, judul_konten, dibuat_oleh, assigned_designer, assigned_uploader)
     * @param string $statusLama   Status sebelum transisi
     * @param string $statusBaru   Status setelah transisi
     * @param int    $doerId       User yang melakukan transisi
     */
    public function notifikasiTransisi(array $konten, string $statusLama, string $statusBaru, int $doerId): void
    {
        $judul       = $konten['judul_konten'];
        $contentId   = (int) $konten['id'];
        $url         = '/dashboard/content-plan';
        $dibuatOleh  = (int) ($konten['dibuat_oleh'] ?? 0);
        $designer    = (int) ($konten['assigned_designer'] ?? 0);
        $uploader    = (int) ($konten['assigned_uploader'] ?? 0);

        switch ($statusBaru) {
            case 'acc_ide':
                // Notify pembuat konten: ide disetujui
                if ($dibuatOleh && $dibuatOleh !== $doerId) {
                    $this->kirim($dibuatOleh, '✅ Ide Disetujui', "Ide konten \"{$judul}\" telah disetujui oleh Manager.", $url);
                }
                // Notify assigned designer (jika ada)
                if ($designer && $designer !== $doerId && $designer !== $dibuatOleh) {
                    $this->kirim($designer, '📋 Tugas Baru Untukmu', "Kamu ditugaskan mengerjakan desain untuk konten \"{$judul}\".", $url);
                }
                break;

            case 'revisi':
                // Notify pembuat/designer: perlu revisi
                if ($dibuatOleh && $dibuatOleh !== $doerId) {
                    $this->kirim($dibuatOleh, '🔄 Perlu Revisi', "Konten \"{$judul}\" diminta untuk direvisi.", $url);
                }
                if ($designer && $designer !== $doerId && $designer !== $dibuatOleh) {
                    $this->kirim($designer, '🔄 Perlu Revisi', "Konten \"{$judul}\" yang kamu kerjakan perlu direvisi.", $url);
                }
                break;

            case 'ditolak':
                // Notify pembuat konten: ide ditolak
                if ($dibuatOleh && $dibuatOleh !== $doerId) {
                    $this->kirim($dibuatOleh, '❌ Ide Ditolak', "Ide konten \"{$judul}\" tidak dapat dilanjutkan.", $url);
                }
                break;

            case 'in_design':
                // Notify manager bahwa content creator mulai mengerjakan
                $this->kirimKeRole('manager', '🎨 Konten Mulai Dikerjakan', "Konten \"{$judul}\" sedang dalam proses desain.", $url);
                break;

            case 'review_design':
                // Notify semua manager: ada yang perlu direview
                $this->kirimKeRole('manager', '👀 Review Dibutuhkan', "Konten \"{$judul}\" selesai didesain dan menunggu review Anda.", $url);
                break;

            case 'acc_final':
                // Notify uploader/admin_medsos
                if ($uploader && $uploader !== $doerId) {
                    $this->kirim($uploader, '🚀 Siap Dipublish', "Konten \"{$judul}\" telah di-approve dan siap untuk diupload.", $url);
                } else {
                    // Notify semua admin_medsos jika belum ada uploader
                    $this->kirimKeRole('admin_medsos', '🚀 Konten Siap Dipublish', "Konten \"{$judul}\" telah di-approve dan siap untuk diupload.", $url);
                }
                // Notify pembuat konten
                if ($dibuatOleh && $dibuatOleh !== $doerId) {
                    $this->kirim($dibuatOleh, '🎉 Konten Acc Final!', "Konten \"{$judul}\" telah mendapat persetujuan final!", $url);
                }
                break;

            case 'published':
                // Notify pembuat konten & semua manager
                if ($dibuatOleh && $dibuatOleh !== $doerId) {
                    $this->kirim($dibuatOleh, '🌟 Konten Published!', "Konten \"{$judul}\" telah berhasil dipublish!", $url);
                }
                $this->kirimKeRole('owner', '📢 Konten Published', "Konten \"{$judul}\" berhasil dipublish.", $url);
                break;

            case 'ide_diajukan':
                // Saat creator resubmit setelah revisi — notify manager
                if ($statusLama === 'revisi') {
                    $this->kirimKeRole('manager', '📥 Ide Diajukan Ulang', "Konten \"{$judul}\" telah direvisi dan diajukan ulang.", $url);
                } else {
                    // Ide baru — notify semua manager
                    $this->kirimKeRole('manager', '💡 Ide Konten Baru', "Ada ide konten baru yang menunggu review Anda: \"{$judul}\".", $url);
                }
                break;
        }
    }
}
