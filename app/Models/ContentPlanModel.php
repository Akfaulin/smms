<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ContentPlanModel
 *
 * Model untuk tabel `content_plan`.
 *
 * PENTING: Kolom `status` sengaja TIDAK dimasukkan ke dalam $allowedFields
 * untuk mencegah update status langsung dari form/controller biasa.
 * Semua perubahan status HARUS melalui App\Services\TransisiKonten::transition().
 *
 * Pengecualian: TransisiKonten service menggunakan update() internal yang
 * memanggil $this->contentPlanModel->update() dengan melewati perlindungan ini
 * menggunakan protect(false) — lihat TransisiKonten::transition().
 */
class ContentPlanModel extends Model
{
    protected $table      = 'content_plan';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Kolom yang boleh di-insert/update dari luar.
     *
     * PERHATIAN: 'status' sengaja tidak ada di sini — gunakan
     * TransisiKonten::transition() untuk mengubah status.
     */
    protected $allowedFields = [
        'bisnis_id',
        'judul_konten',
        'deskripsi',
        'tanggal_publish',
        'scheduled_at',
        'auto_publish_status',
        'publish_attempts',
        'last_error',
        'jenis_konten_id',
        'content_type_id',
        'dibuat_oleh',
        'assigned_designer',
        'assigned_uploader',
        'caption',
        'design_url',
        'image_url',
    ];

    protected $validationRules = [
        'judul_konten'   => 'required|max_length[200]',
        'dibuat_oleh'    => 'required|is_natural_no_zero',
        'tanggal_publish' => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'judul_konten' => [
            'required'   => 'Judul konten wajib diisi.',
            'max_length' => 'Judul konten maksimal 200 karakter.',
        ],
        'dibuat_oleh' => [
            'required' => 'Data pembuat konten tidak valid.',
        ],
    ];

    // -------------------------------------------------------------------------
    // Query Helper
    // -------------------------------------------------------------------------

    /**
     * Ambil konten lengkap dengan relasi (join ke tabel terkait).
     */
    public function withRelasi(): static
    {
        return $this
            ->select('content_plan.*, 
                      u1.nama AS nama_pembuat,
                      u2.nama AS nama_designer,
                      u3.nama AS nama_uploader,
                      jk.nama_jenis,
                      ct.nama_type AS nama_pillar')
            ->join('users u1', 'u1.id = content_plan.dibuat_oleh', 'left')
            ->join('users u2', 'u2.id = content_plan.assigned_designer', 'left')
            ->join('users u3', 'u3.id = content_plan.assigned_uploader', 'left')
            ->join('jenis_konten jk', 'jk.id = content_plan.jenis_konten_id', 'left')
            ->join('content_types ct', 'ct.id = content_plan.content_type_id', 'left');
    }

    /**
     * Filter konten berdasarkan status (untuk dashboard per role).
     *
     * @param string|string[] $status
     */
    public function byStatus(string|array $status): static
    {
        if (is_array($status)) {
            return $this->whereIn('content_plan.status', $status);
        }
        return $this->where('content_plan.status', $status);
    }

    /**
     * Filter konten milik user tertentu (pembuat).
     */
    public function milikUser(int $userId): static
    {
        return $this->where('content_plan.dibuat_oleh', $userId);
    }

    /**
     * Filter konten berdasarkan bisnis_id.
     * Menjamin isolasi data ketat per bisnis (tidak tercampur antar bisnis).
     */
    public function byBisnis(int $bisnisId): static
    {
        $targetId = ($bisnisId > 0) ? $bisnisId : (int) (session('bisnis_aktif_id') ?: 1);
        return $this->where('content_plan.bisnis_id', $targetId);
    }

    /**
     * Sisipkan status awal 'ide_diajukan' secara otomatis.
     * Gunakan metode ini (bukan insert() langsung) saat membuat konten baru.
     */
    public function buatIde(array $data, int $userId): int|false
    {
        $data['dibuat_oleh'] = $userId;

        // Simpan tanpa status dulu agar $allowedFields tidak dilewati
        $inserted = $this->insert($data, true);

        if (! $inserted) {
            return false;
        }

        $contentId = $this->getInsertID();

        // Set status awal langsung (bypass allowedFields karena hanya diizinkan di sini)
        $this->protect(false)->update($contentId, [
            'status'     => 'ide_diajukan',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->protect(true);

        return $contentId;
    }

    /**
     * Update status — KHUSUS dipakai oleh TransisiKonten service.
     * Gunakan protect(false) untuk bypass allowedFields.
     */
    public function updateStatus(int $contentId, string $statusBaru): bool
    {
        $res = $this->protect(false)->skipValidation(true)->update($contentId, [
            'status'     => $statusBaru,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->protect(true)->skipValidation(false);
        return $res;
    }
}
