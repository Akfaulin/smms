<?php

namespace App\Controllers;

use App\Services\TransisiKonten;

/**
 * Dashboard Controller
 *
 * Halaman beranda setelah login — menampilkan statistik, antrean tugas,
 * dan tren konten sesuai role pengguna.
 */
class Dashboard extends BaseController
{
    public function index(): string
    {
        $db       = \Config\Database::connect();
        $role     = session('kode_role');
        $userId   = (int) session('user_id');
        $bisnisId = (int) session('bisnis_aktif_id');

        // --- Statistik Global (filter by bisnis) ---
        $totalAktif   = $db->table('content_plan')
            ->where('bisnis_id', $bisnisId)
            ->whereNotIn('status', ['published', 'ditolak'])->countAllResults();
        $totalPublish = $db->table('content_plan')
            ->where('bisnis_id', $bisnisId)
            ->where('status', 'published')
            ->where('MONTH(updated_at)', date('n'))
            ->where('YEAR(updated_at)', date('Y'))
            ->countAllResults();
        $totalRevisi  = $db->table('content_plan')
            ->where('bisnis_id', $bisnisId)
            ->where('status', 'revisi')->countAllResults();
        $totalDitolak = $db->table('content_plan')
            ->where('bisnis_id', $bisnisId)
            ->where('status', 'ditolak')->countAllResults();

        // --- Antrean Tugas per Role (filter by bisnis) ---
        $antrean = [];
        if ($role === 'manager') {
            $antrean = $db->table('content_plan cp')
                ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish, u.nama as nama_pembuat')
                ->join('users u', 'u.id = cp.dibuat_oleh', 'left')
                ->where('cp.bisnis_id', $bisnisId)
                ->whereIn('cp.status', ['ide_diajukan', 'review_design'])
                ->orderBy('cp.updated_at', 'ASC')
                ->limit(8)
                ->get()->getResultArray();
        } elseif ($role === 'creative_team') {
            $antrean = $db->table('content_plan cp')
                ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish, u.nama as nama_pembuat')
                ->join('users u', 'u.id = cp.dibuat_oleh', 'left')
                ->where('cp.bisnis_id', $bisnisId)
                ->whereIn('cp.status', ['ide_diajukan', 'revisi'])
                ->where('cp.dibuat_oleh', $userId)
                ->orderBy('cp.updated_at', 'ASC')
                ->limit(8)
                ->get()->getResultArray();
        } elseif ($role === 'content_creator') {
            $antrean = $db->table('content_plan cp')
                ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish, u.nama as nama_pembuat')
                ->join('users u', 'u.id = cp.dibuat_oleh', 'left')
                ->where('cp.bisnis_id', $bisnisId)
                ->whereIn('cp.status', ['acc_ide', 'in_design', 'revisi'])
                ->where('cp.dibuat_oleh', $userId)
                ->orderBy('cp.updated_at', 'ASC')
                ->limit(8)
                ->get()->getResultArray();
        } elseif ($role === 'admin_medsos') {
            $antrean = $db->table('content_plan cp')
                ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish, u.nama as nama_pembuat')
                ->join('users u', 'u.id = cp.dibuat_oleh', 'left')
                ->where('cp.bisnis_id', $bisnisId)
                ->where('cp.status', 'acc_final')
                ->orderBy('cp.updated_at', 'ASC')
                ->limit(8)
                ->get()->getResultArray();
        } elseif (in_array($role, ['owner', 'superadmin'], true)) {
            $antrean = $db->table('content_plan cp')
                ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish, u.nama as nama_pembuat')
                ->join('users u', 'u.id = cp.dibuat_oleh', 'left')
                ->where('cp.bisnis_id', $bisnisId)
                ->whereNotIn('cp.status', ['published', 'ditolak'])
                ->orderBy('cp.updated_at', 'DESC')
                ->limit(8)
                ->get()->getResultArray();
        }

        // --- Konten Mendekati Tanggal Publish (<= 3 hari ke depan, filter by bisnis) ---
        $soon = $db->table('content_plan cp')
            ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish')
            ->where('cp.bisnis_id', $bisnisId)
            ->where('cp.tanggal_publish >=', date('Y-m-d'))
            ->where('cp.tanggal_publish <=', date('Y-m-d', strtotime('+3 days')))
            ->whereNotIn('cp.status', ['published', 'ditolak'])
            ->orderBy('cp.tanggal_publish', 'ASC')
            ->get()->getResultArray();

        // --- Data Tren 7 Hari Terakhir (filter by bisnis) ---
        $tren = [];
        for ($i = 6; $i >= 0; $i--) {
            $tgl    = date('Y-m-d', strtotime("-{$i} days"));
            $jumlah = $db->table('content_plan')
                ->where('bisnis_id', $bisnisId)
                ->where('DATE(created_at)', $tgl)
                ->countAllResults();
            $tren[] = [
                'tanggal' => date('d/m', strtotime($tgl)),
                'jumlah'  => $jumlah,
            ];
        }

        // --- Distribusi Status (filter by bisnis) ---
        $distribusiRaw = $db->table('content_plan')
            ->select('status, COUNT(*) as jumlah')
            ->where('bisnis_id', $bisnisId)
            ->groupBy('status')
            ->get()->getResultArray();

        $distribusi = [];
        foreach ($distribusiRaw as $row) {
            $distribusi[$row['status']] = (int) $row['jumlah'];
        }

        // --- Statistik per Platform (top 5, filter by bisnis) ---
        $perPlatform = $db->table('content_platforms cp')
            ->select('p.nama_platform, COUNT(*) as jumlah')
            ->join('platforms p', 'p.id = cp.platform_id')
            ->join('content_plan c', 'c.id = cp.content_id')
            ->where('c.bisnis_id', $bisnisId)
            ->groupBy('cp.platform_id')
            ->orderBy('jumlah', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('dashboard/index', [
            'judul'        => 'Dashboard',
            'totalAktif'   => $totalAktif,
            'totalPublish' => $totalPublish,
            'totalRevisi'  => $totalRevisi,
            'totalDitolak' => $totalDitolak,
            'antrean'      => $antrean,
            'soon'         => $soon,
            'tren'         => $tren,
            'distribusi'   => $distribusi,
            'perPlatform'  => $perPlatform,
            'kode_role'    => $role,
        ]);
    }
}