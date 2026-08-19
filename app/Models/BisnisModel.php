<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * BisnisModel
 *
 * Model untuk tabel `bisnis`. Menyimpan daftar bisnis/brand yang sosmed-nya
 * dikelola dalam sistem SMMS.
 */
class BisnisModel extends Model
{
    protected $table      = 'bisnis';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'nama_bisnis',
        'deskripsi',
        'warna',
        'logo_url',
        'status',
        'urutan',
        'meta_app_id',
        'meta_app_secret',
        'meta_access_token',
        'meta_ig_account_id',
        'meta_ig_username',
        'gemini_api_key',
    ];

    protected $validationRules = [
        'nama_bisnis' => 'required|max_length[100]',
        'warna'       => 'permit_empty|max_length[7]',
        'status'      => 'permit_empty|in_list[aktif,nonaktif]',
    ];

    protected $validationMessages = [
        'nama_bisnis' => [
            'required'   => 'Nama bisnis wajib diisi.',
            'max_length' => 'Nama bisnis maksimal 100 karakter.',
        ],
    ];

    // -------------------------------------------------------------------------
    // Query Helper
    // -------------------------------------------------------------------------

    /**
     * Ambil semua bisnis yang aktif, diurutkan berdasarkan urutan.
     */
    public function getAktif(): array
    {
        return $this->where('status', 'aktif')
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil bisnis default (urutan terkecil, status aktif).
     * Digunakan saat login untuk menentukan bisnis aktif awal.
     */
    public function getDefault(): ?array
    {
        return $this->where('status', 'aktif')
                    ->orderBy('urutan', 'ASC')
                    ->first();
    }

    /**
     * Ambil bisnis by ID — return null jika tidak ditemukan atau nonaktif.
     */
    public function getById(int $id): ?array
    {
        return $this->where('id', $id)
                    ->where('status', 'aktif')
                    ->first();
    }
}
