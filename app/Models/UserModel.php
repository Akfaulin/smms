<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Model untuk tabel `users`. Digunakan oleh Auth controller
 * dan manajemen user (Tahap selanjutnya).
 */
class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'nama',
        'email',
        'password',
        'role_id',
        'status',
    ];

    protected $validationRules = [
        'nama'     => 'required|max_length[100]',
        'email'    => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role_id'  => 'required|is_natural_no_zero',
    ];

    // -------------------------------------------------------------------------
    // Auth Helper
    // -------------------------------------------------------------------------

    /**
     * Cari user aktif berdasarkan email (untuk login).
     */
    public function findByEmail(string $email): ?array
    {
        return $this->select('users.*, roles.kode_role, roles.nama_role')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('users.email', $email)
                    ->where('users.status', 'aktif')
                    ->first();
    }

    /**
     * Verifikasi password.
     */
    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Hash password sebelum disimpan.
     */
    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    // -------------------------------------------------------------------------
    // Query Helper
    // -------------------------------------------------------------------------

    /**
     * Ambil semua user dengan info role (untuk manajemen user — Tahap selanjutnya).
     */
    public function withRole(): static
    {
        return $this->select('users.*, roles.kode_role, roles.nama_role')
                    ->join('roles', 'roles.id = users.role_id', 'left');
    }

    /**
     * Ambil user aktif berdasarkan role tertentu.
     */
    public function byRole(string $kodeRole): array
    {
        return $this->select('users.id, users.nama, users.email, roles.kode_role')
                    ->join('roles', 'roles.id = users.role_id', 'left')
                    ->where('roles.kode_role', $kodeRole)
                    ->where('users.status', 'aktif')
                    ->findAll();
    }
}
