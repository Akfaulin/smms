<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Profile Controller
 *
 * Memungkinkan semua user (role apapun) untuk:
 *   - Melihat & mengupdate nama profil
 *   - Mengganti password dengan verifikasi password lama
 */
class Profile extends BaseController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    /**
     * GET /dashboard/profil
     * Tampilkan halaman profil user yang sedang login.
     */
    public function index(): string
    {
        $userId = (int) session('user_id');
        $db     = \Config\Database::connect();

        $user = $db->table('users u')
            ->select('u.*, r.nama_role, r.kode_role')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $userId)
            ->get()->getRowArray();

        if (! $user) {
            return redirect()->to('/dashboard')->with('error', 'Data profil tidak ditemukan.');
        }

        return view('profile/index', [
            'judul' => 'Profil Saya',
            'user'  => $user,
        ]);
    }

    /**
     * POST /dashboard/profil/update
     * Update nama profil.
     */
    public function updateProfile(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $nama   = trim($this->request->getPost('nama') ?? '');

        if (empty($nama)) {
            return $this->jsonGagal('Nama tidak boleh kosong.', 422);
        }

        if (strlen($nama) > 100) {
            return $this->jsonGagal('Nama maksimal 100 karakter.', 422);
        }

        // Skip validation untuk update nama saja (hindari validasi email unique)
        $this->model->skipValidation(true)->update($userId, ['nama' => $nama]);
        $this->model->skipValidation(false);

        // Update session agar navbar langsung berubah
        session()->set('nama', $nama);

        return $this->jsonSukses('Profil berhasil diperbarui.', ['nama' => $nama]);
    }

    /**
     * POST /dashboard/profil/ganti-password
     * Ganti password dengan verifikasi password lama.
     */
    public function gantiPassword(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId      = (int) session('user_id');
        $passLama    = $this->request->getPost('password_lama') ?? '';
        $passBaru    = $this->request->getPost('password_baru') ?? '';
        $passKonfirm = $this->request->getPost('password_konfirmasi') ?? '';

        if (empty($passLama) || empty($passBaru) || empty($passKonfirm)) {
            return $this->jsonGagal('Semua field password wajib diisi.', 422);
        }

        if (strlen($passBaru) < 6) {
            return $this->jsonGagal('Password baru minimal 6 karakter.', 422);
        }

        if ($passBaru !== $passKonfirm) {
            return $this->jsonGagal('Konfirmasi password tidak cocok.', 422);
        }

        $user = $this->model->find($userId);
        if (! $user) {
            return $this->jsonGagal('User tidak ditemukan.', 404);
        }

        // Verifikasi password lama
        if (! password_verify($passLama, $user['password'])) {
            return $this->jsonGagal('Password lama tidak sesuai.', 422);
        }

        // Update password baru
        $this->model->skipValidation(true)->update($userId, [
            'password' => password_hash($passBaru, PASSWORD_BCRYPT),
        ]);
        $this->model->skipValidation(false);

        return $this->jsonSukses('Password berhasil diubah. Silakan login kembali jika diperlukan.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function jsonSukses(string $pesan, array $data = []): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'sukses',
            'pesan'  => $pesan,
            'data'   => $data,
        ]);
    }

    private function jsonGagal(string $pesan, int $code = 422): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status' => 'gagal',
            'pesan'  => $pesan,
        ]);
    }
}
