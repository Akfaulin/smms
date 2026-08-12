<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BisnisModel;

/**
 * Auth Controller
 *
 * Menangani login dan logout sistem.
 * Session yang disimpan: user_id, nama, email, kode_role, nama_role.
 */
class Auth extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // =========================================================================
    // LOGIN
    // =========================================================================

    /**
     * GET /login
     * Tampilkan halaman login.
     * Jika sudah login, redirect ke dashboard.
     */
    public function login(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (session('logged_in')) {
            return redirect()->to('/dashboard/content-plan');
        }

        return view('auth/login', [
            'judul' => 'Login — SMMS',
        ]);
    }

    /**
     * POST /login
     * Proses autentikasi.
     */
    public function prosesLogin(): \CodeIgniter\HTTP\RedirectResponse
    {
        $email    = trim($this->request->getPost('email') ?? $this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';

        // Validasi input dasar
        if (empty($email) || empty($password)) {
            return redirect()->back()->withInput()
                ->with('error', 'Email dan password wajib diisi.');
        }

        // Cari user
        $user = $this->userModel->findByEmail($email);
        if (! $user) {
            return redirect()->back()->withInput()
                ->with('error', 'Email tidak ditemukan atau akun tidak aktif.');
        }

        // Verifikasi password
        if (! UserModel::verifyPassword($password, $user['password'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Password salah.');
        }

        // Cari bisnis default untuk sesi aktif
        $bisnisModel  = new BisnisModel();
        $bisnisDefault = $bisnisModel->getDefault();

        // Simpan ke session
        session()->set([
            'user_id'            => $user['id'],
            'nama'               => $user['nama'],
            'email'              => $user['email'],
            'kode_role'          => $user['kode_role'],
            'nama_role'          => $user['nama_role'],
            'logged_in'          => true,
            'bisnis_aktif_id'    => $bisnisDefault['id']    ?? null,
            'bisnis_aktif_nama'  => $bisnisDefault['nama_bisnis'] ?? 'Default',
            'bisnis_aktif_warna' => $bisnisDefault['warna'] ?? '#6C5CE7',
        ]);

        return redirect()->to('/dashboard')
            ->with('sukses', 'Selamat datang, ' . $user['nama'] . '!');
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    /**
     * GET /logout
     */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove(['user_id', 'nama', 'email', 'kode_role', 'nama_role', 'logged_in',
                           'bisnis_aktif_id', 'bisnis_aktif_nama', 'bisnis_aktif_warna']);
        session()->destroy();
        return redirect()->to('/login')
            ->with('pesan', 'Anda telah berhasil logout.');
    }
}
