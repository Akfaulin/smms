<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * NotificationModel
 *
 * Model untuk tabel `notifications`.
 * Digunakan oleh NotificationService untuk menyimpan & membaca notifikasi in-app.
 */
class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'judul',
        'pesan',
        'url',
        'is_read',
        'created_at',
    ];

    /**
     * Ambil notifikasi belum dibaca untuk user tertentu.
     */
    public function getUnread(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Ambil semua notifikasi user (paginasi manual, limit 30 terakhir).
     */
    public function getByUser(int $userId, int $limit = 30): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Jumlah notifikasi belum dibaca.
     */
    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     */
    public function bacaSemua(int $userId): void
    {
        $this->where('user_id', $userId)->set(['is_read' => 1])->update();
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function bacaSatu(int $notifId, int $userId): void
    {
        $this->where('id', $notifId)->where('user_id', $userId)->set(['is_read' => 1])->update();
    }
}
