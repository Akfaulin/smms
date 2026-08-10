<?php

namespace App\Controllers;

use App\Models\NotificationModel;

/**
 * Notifications Controller
 *
 * Mengelola notifikasi in-app per user.
 */
class Notifications extends BaseController
{
    private NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    /**
     * GET /dashboard/notifikasi/unread-count
     * Jumlah notifikasi belum dibaca (untuk badge di topbar — polling).
     */
    public function unreadCount(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $count  = $this->model->countUnread($userId);
        return $this->response->setJSON(['count' => $count]);
    }

    /**
     * GET /dashboard/notifikasi/list
     * Ambil 20 notifikasi terbaru (JSON, untuk dropdown di topbar).
     */
    public function list(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $notifs = $this->model->getByUser($userId, 20);

        // Tandai semua sebagai dibaca saat dropdown dibuka
        $this->model->bacaSemua($userId);

        return $this->response->setJSON([
            'status' => 'sukses',
            'data'   => $notifs,
        ]);
    }

    /**
     * POST /dashboard/notifikasi/baca/(:num)
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function baca(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $this->model->bacaSatu($id, $userId);
        return $this->response->setJSON(['status' => 'sukses']);
    }

    /**
     * POST /dashboard/notifikasi/baca-semua
     * Tandai semua notifikasi sebagai dibaca.
     */
    public function bacaSemua(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userId = (int) session('user_id');
        $this->model->bacaSemua($userId);
        return $this->response->setJSON(['status' => 'sukses']);
    }
}
