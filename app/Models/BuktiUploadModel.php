<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * BuktiUploadModel
 *
 * Model untuk tabel `bukti_upload`.
 * Mencatat link postingan dan bukti upload oleh admin_medsos (§3.10).
 */
class BuktiUploadModel extends Model
{
    protected $table            = 'bukti_upload';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'content_id',
        'platform_id',
        'link_postingan',
        'uploaded_by',
        'uploaded_at',
    ];

    /**
     * Ambil bukti upload untuk konten tertentu beserta info platform dan uploader.
     */
    public function getByContentId(int $contentId): array
    {
        return $this->select('bukti_upload.*, platforms.nama_platform, users.nama as uploader_nama')
            ->join('platforms', 'platforms.id = bukti_upload.platform_id', 'left')
            ->join('users', 'users.id = bukti_upload.uploaded_by', 'left')
            ->where('bukti_upload.content_id', $contentId)
            ->orderBy('bukti_upload.uploaded_at', 'ASC')
            ->findAll();
    }
}
