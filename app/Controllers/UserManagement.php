<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * UserManagement Controller
 *
 * Akses berdasarkan role (§5 permission matrix):
 *   - superadmin : CRUD penuh, termasuk buat user dengan role apapun & hapus user
 *   - owner      : lihat list user, update user (tidak bisa set role superadmin ke orang lain)
 */
class UserManagement extends BaseController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }
    
    /**
     * Cek apakah user boleh mengakses halaman user management.
     * Superadmin dan owner boleh mengakses.
     */
    private function checkAccess(): bool
    {
        return in_array(session('kode_role'), ['superadmin', 'owner'], true);
    }

    /**
     * Cek apakah user adalah superadmin (untuk operasi eksklusif superadmin).
     */
    private function checkSuperadminOnly(): bool
    {
        return session('kode_role') === 'superadmin';
    }

    /**
     * Tampilkan halaman daftar user
     */
    public function index()
    {
        if (! $this->checkAccess()) {
            return redirect()->to('/dashboard/content-plan')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        
        $users = $db->table('users u')
            ->select('u.*, r.nama_role, r.kode_role')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('u.created_at', 'DESC')
            ->get()->getResultArray();
            
        $roles = $db->table('roles')->get()->getResultArray();

        return view('master/user/index', [
            'judul' => 'Manajemen User',
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * POST /dashboard/master/user/store
     * Hanya superadmin yang boleh membuat user baru.
     */
    public function store()
    {
        if (! $this->checkSuperadminOnly()) {
            return $this->jsonGagal('Hanya Superadmin yang dapat menambahkan user baru.', 403);
        }

        $json = $this->request->getJSON();
        $nama     = $this->request->getPost('nama') ?? $json->nama ?? '';
        $email    = $this->request->getPost('email') ?? $json->email ?? '';
        $password = $this->request->getPost('password') ?? $json->password ?? '';
        $role_id  = $this->request->getPost('role_id') ?? $json->role_id ?? '';
        $status   = $this->request->getPost('status') ?? $json->status ?? 'aktif';

        if (empty($nama) || empty($email) || empty($password) || empty($role_id)) {
            return $this->jsonGagal('Semua field (nama, email, password, role) wajib diisi.', 422);
        }

        $data = [
            'nama'     => $nama,
            'email'    => $email,
            'password' => password_hash((string) $password, PASSWORD_BCRYPT),
            'role_id'  => $role_id,
            'status'   => $status,
        ];

        if (! $this->model->insert($data)) {
            return $this->jsonGagal('Validasi gagal: ' . implode(', ', $this->model->errors()), 422);
        }

        return $this->jsonSukses('User berhasil ditambahkan.');
    }

    /**
     * POST /dashboard/master/user/update/{id}
     * Superadmin dan owner boleh update user.
     * Owner tidak boleh mengubah role menjadi 'superadmin'.
     */
    public function update(int $id)
    {
        if (! $this->checkAccess()) {
            return $this->jsonGagal('Akses ditolak', 403);
        }

        $user = $this->model->find($id);
        if (! $user) {
            return $this->jsonGagal('User tidak ditemukan', 404);
        }

        $json    = $this->request->getJSON();
        $roleId  = $this->request->getPost('role_id') ?? ($json->role_id ?? '');

        // Owner tidak boleh assign role superadmin ke user lain
        if (! $this->checkSuperadminOnly() && ! empty($roleId)) {
            $db       = \Config\Database::connect();
            $roleRow  = $db->table('roles')->where('id', (int) $roleId)->get()->getRowArray();
            if ($roleRow && $roleRow['kode_role'] === 'superadmin') {
                return $this->jsonGagal('Owner tidak dapat mengubah role menjadi Superadmin.', 403);
            }
        }

        $data = [
            'nama'    => $this->request->getPost('nama') ?? ($json->nama ?? ''),
            'email'   => $this->request->getPost('email') ?? ($json->email ?? ''),
            'role_id' => $roleId,
            'status'  => $this->request->getPost('status') ?? ($json->status ?? 'aktif'),
        ];

        $password = $this->request->getPost('password') ?? ($json->password ?? '');
        if (! empty($password)) {
            $data['password'] = password_hash((string) $password, PASSWORD_BCRYPT);
        }

        if (! $this->model->update($id, $data)) {
            return $this->jsonGagal('Validasi gagal: ' . implode(', ', $this->model->errors()), 422);
        }

        return $this->jsonSukses('User berhasil diperbarui.');
    }

    /**
     * POST /dashboard/master/user/delete/{id}
     * Hanya superadmin yang boleh menghapus user.
     */
    public function delete(int $id)
    {
        if (! $this->checkSuperadminOnly()) {
            return $this->jsonGagal('Hanya Superadmin yang dapat menghapus user.', 403);
        }

        $user = $this->model->find($id);
        if (! $user) {
            return $this->jsonGagal('User tidak ditemukan', 404);
        }

        // Jangan izinkan superadmin menghapus dirinya sendiri
        if ($id === (int) session('user_id')) {
            return $this->jsonGagal('Anda tidak dapat menghapus akun Anda sendiri.', 403);
        }

        if (! $this->model->delete($id)) {
            return $this->jsonGagal('Gagal menghapus user.', 500);
        }

        return $this->jsonSukses('User berhasil dihapus.');
    }

    private function jsonSukses(string $pesan): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON(['status' => 'sukses', 'pesan' => $pesan]);
    }

    private function jsonGagal(string $pesan, int $code = 400): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON(['status' => 'gagal', 'pesan' => $pesan]);
    }
}
