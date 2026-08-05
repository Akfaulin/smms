<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ContentStatusLogModel
 *
 * Model untuk tabel `content_status_log` — audit trail setiap perubahan status.
 * Sesuai spesifikasi §3.9.
 *
 * Model ini hanya untuk INSERT dan READ — tidak ada UPDATE/DELETE
 * karena audit trail tidak boleh diubah.
 */
class ContentStatusLogModel extends Model
{
    protected $table      = 'content_status_log';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // Tidak menggunakan useTimestamps CI4 karena hanya ada created_at (tidak ada updated_at)
    protected $useTimestamps = false;

    protected $allowedFields = [
        'content_id',
        'status_lama',
        'status_baru',
        'user_id',
        'catatan',
        'created_at',
    ];

    // -------------------------------------------------------------------------
    // Query Helper
    // -------------------------------------------------------------------------

    /**
     * Ambil seluruh log untuk satu konten, urut dari terbaru ke terlama.
     * Include data user (siapa yang melakukan perubahan).
     */
    public function logKonten(int $contentId): array
    {
        return $this->db->table('content_status_log csl')
            ->select('csl.*, u.nama AS nama_user, r.kode_role, r.nama_role')
            ->join('users u', 'u.id = csl.user_id', 'left')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('csl.content_id', $contentId)
            ->orderBy('csl.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil status terkini dari log (entry paling baru).
     */
    public function statusTerkini(int $contentId): ?array
    {
        return $this->db->table('content_status_log')
            ->where('content_id', $contentId)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * Hitung berapa kali konten pernah masuk status 'revisi'.
     */
    public function jumlahRevisi(int $contentId): int
    {
        return (int) $this->db->table('content_status_log')
            ->where('content_id', $contentId)
            ->where('status_baru', 'revisi')
            ->countAllResults();
    }
}
