<?php

namespace App\Controllers;

use App\Models\ContentPlanModel;
use App\Models\ContentStatusLogModel;

/**
 * AuditTrail Controller
 *
 * Dashboard Komprehensif History Pengajuan & Audit Trail:
 * Menampilkan seluruh riwayat alur pengajuan, revisi, persetujuan (approval),
 * dan publikasi konten terisolasi per bisnis dengan filter lengkap.
 */
class AuditTrail extends BaseController
{
    protected ContentStatusLogModel $logModel;
    protected ContentPlanModel $contentModel;

    public function __construct()
    {
        $this->logModel     = new ContentStatusLogModel();
        $this->contentModel = new ContentPlanModel();
    }

    /**
     * GET /dashboard/audit-trail
     */
    public function index()
    {
        $db       = \Config\Database::connect();
        $role     = session('kode_role');
        $bisnisId = (int) session('bisnis_aktif_id');

        // Parameter Filter
        $search   = trim($this->request->getGet('search') ?? '');
        $action   = $this->request->getGet('action') ?? 'all';
        $userId   = (int) ($this->request->getGet('user_id') ?? 0);
        $period   = $this->request->getGet('period') ?? '30days';

        // Base Query Logs (Isolasi Bisnis via join content_plan)
        $builder = $db->table('content_status_log csl')
            ->select('csl.*, cp.judul_konten, cp.bisnis_id, cp.tanggal_publish, cp.image_url, cp.design_url, u.nama as nama_user, r.kode_role, r.nama_role')
            ->join('content_plan cp', 'cp.id = csl.content_id')
            ->join('users u', 'u.id = csl.user_id', 'left')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('cp.bisnis_id', $bisnisId);

        // Filter Action / Status Baru
        if ($action !== 'all' && !empty($action)) {
            if ($action === 'approval') {
                $builder->whereIn('csl.status_baru', ['acc_ide', 'acc_final']);
            } elseif ($action === 'revisi') {
                $builder->where('csl.status_baru', 'revisi');
            } elseif ($action === 'published') {
                $builder->where('csl.status_baru', 'published');
            } elseif ($action === 'ide_diajukan') {
                $builder->where('csl.status_baru', 'ide_diajukan');
            } elseif ($action === 'review_design') {
                $builder->where('csl.status_baru', 'review_design');
            } elseif ($action === 'ditolak') {
                $builder->where('csl.status_baru', 'ditolak');
            } else {
                $builder->where('csl.status_baru', $action);
            }
        }

        // Filter User
        if ($userId > 0) {
            $builder->where('csl.user_id', $userId);
        }

        // Filter Periode Waktu
        if ($period === 'today') {
            $builder->where('DATE(csl.created_at)', date('Y-m-d'));
        } elseif ($period === '7days') {
            $builder->where('csl.created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')));
        } elseif ($period === '30days') {
            $builder->where('csl.created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')));
        } elseif ($period === 'this_month') {
            $builder->where('MONTH(csl.created_at)', date('m'))->where('YEAR(csl.created_at)', date('Y'));
        }

        // Search Filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('cp.judul_konten', $search)
                ->orLike('csl.catatan', $search)
                ->orLike('u.nama', $search)
            ->groupEnd();
        }

        $logs = $builder->orderBy('csl.created_at', 'DESC')->limit(150)->get()->getResultArray();

        // Enrich platforms per log item
        foreach ($logs as &$log) {
            $plats = $db->table('content_platforms cp')
                ->select('p.id, p.nama_platform')
                ->join('platforms p', 'p.id = cp.platform_id')
                ->where('cp.content_id', $log['content_id'])
                ->get()->getResultArray();
            $log['platforms']    = $plats;
            $log['platform_str'] = implode(', ', array_column($plats, 'nama_platform'));
            $log['time_ago']     = $this->formatTimeAgo($log['created_at']);
        }
        unset($log);

        // Calculate Summary Metrics for the Active Business
        $allBizLogs = $db->table('content_status_log csl')
            ->select('csl.status_baru')
            ->join('content_plan cp', 'cp.id = csl.content_id')
            ->where('cp.bisnis_id', $bisnisId)
            ->get()->getResultArray();

        $statTotalLogs = count($allBizLogs);
        $statPengajuan = count(array_filter($allBizLogs, fn($l) => $l['status_baru'] === 'ide_diajukan'));
        $statApproved  = count(array_filter($allBizLogs, fn($l) => in_array($l['status_baru'], ['acc_ide', 'acc_final'], true)));
        $statRevisi    = count(array_filter($allBizLogs, fn($l) => $l['status_baru'] === 'revisi'));
        $statPublished = count(array_filter($allBizLogs, fn($l) => $l['status_baru'] === 'published'));
        $statDitolak   = count(array_filter($allBizLogs, fn($l) => $l['status_baru'] === 'ditolak'));

        // List Users for filter dropdown
        $users = $db->table('users u')
            ->select('u.id, u.nama, r.kode_role, r.nama_role')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.status', 'aktif')
            ->orderBy('u.nama', 'ASC')
            ->get()->getResultArray();

        return view('audit_trail/index', [
            'judul'         => 'History Pengajuan & Audit Trail',
            'logs'          => $logs,
            'statTotalLogs' => $statTotalLogs,
            'statPengajuan' => $statPengajuan,
            'statApproved'  => $statApproved,
            'statRevisi'    => $statRevisi,
            'statPublished' => $statPublished,
            'statDitolak'   => $statDitolak,
            'users'         => $users,
            'search'        => $search,
            'action'        => $action,
            'userId'        => $userId,
            'period'        => $period,
            'kode_role'     => $role,
        ]);
    }

    /**
     * GET /dashboard/audit-trail/detail/{contentId}
     * Ambil riwayat lengkap satu konten via JSON untuk modal timeline
     */
    public function detail(int $contentId)
    {
        $db = \Config\Database::connect();
        $bisnisId = (int) session('bisnis_aktif_id');

        $konten = $this->contentModel->withRelasi()->byBisnis($bisnisId)->find($contentId);
        if (!$konten) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'gagal',
                'pesan'  => 'Konten tidak ditemukan atau di luar bisnis aktif.',
            ]);
        }

        $logs = $this->logModel->logKonten($contentId);
        foreach ($logs as &$l) {
            $l['time_ago'] = $this->formatTimeAgo($l['created_at']);
        }
        unset($l);

        return $this->response->setJSON([
            'status' => 'sukses',
            'konten' => $konten,
            'logs'   => $logs,
        ]);
    }

    /**
     * Helper relative time formatter
     */
    private function formatTimeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Baru saja';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' menit lalu';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' jam lalu';
        }
        if ($diff < 172800) {
            return 'Kemarin, ' . date('H:i', $time);
        }
        return date('d M Y, H:i', $time);
    }
}
