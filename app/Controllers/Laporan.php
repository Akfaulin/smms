<?php

namespace App\Controllers;

/**
 * Laporan Controller
 *
 * Rekap dan export data konten.
 * Hanya manager, owner, dan superadmin yang boleh mengakses.
 */
class Laporan extends BaseController
{
    private function checkAccess(): bool
    {
        return in_array(session('kode_role'), ['superadmin', 'owner', 'manager'], true);
    }

    /**
     * GET /dashboard/laporan
     * Tampilkan halaman laporan dengan filter bulan/tahun.
     */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->checkAccess()) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $db    = \Config\Database::connect();
        $bulan = (int) ($this->request->getGet('bulan') ?: date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $bisnisId = (int) session('bisnis_aktif_id');

        $bulanStr = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Rekap per status di bulan/tahun terpilih (filter by bisnis)
        $perStatus = $db->table('content_plan')
            ->select('status, COUNT(*) as jumlah')
            ->where('bisnis_id', $bisnisId)
            ->where("DATE_FORMAT(created_at, '%Y-%m')", "{$tahun}-{$bulanStr}")
            ->groupBy('status')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        // Rekap per platform (filter by bisnis)
        $perPlatform = $db->table('content_platforms cp')
            ->select('p.nama_platform, COUNT(*) as jumlah')
            ->join('platforms p', 'p.id = cp.platform_id')
            ->join('content_plan c', 'c.id = cp.content_id')
            ->where('c.bisnis_id', $bisnisId)
            ->where("DATE_FORMAT(c.created_at, '%Y-%m')", "{$tahun}-{$bulanStr}")
            ->groupBy('cp.platform_id')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResultArray();

        // Rekap produktivitas per user (filter by bisnis)
        $perUser = $db->table('content_plan cp')
            ->select('u.nama, r.nama_role, COUNT(*) as total, 
                      SUM(CASE WHEN cp.status = "published" THEN 1 ELSE 0 END) as published,
                      SUM(CASE WHEN cp.status = "ditolak" THEN 1 ELSE 0 END) as ditolak')
            ->join('users u', 'u.id = cp.dibuat_oleh', 'left')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('cp.bisnis_id', $bisnisId)
            ->where("DATE_FORMAT(cp.created_at, '%Y-%m')", "{$tahun}-{$bulanStr}")
            ->groupBy('cp.dibuat_oleh')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        // Total keseluruhan bulan ini (filter by bisnis)
        $totalBulanIni = $db->table('content_plan')
            ->where('bisnis_id', $bisnisId)
            ->where("DATE_FORMAT(created_at, '%Y-%m')", "{$tahun}-{$bulanStr}")
            ->countAllResults();

        // Daftar tahun tersedia (filter by bisnis)
        $tahunList = $db->table('content_plan')
            ->select("YEAR(created_at) as tahun")
            ->where('bisnis_id', $bisnisId)
            ->groupBy('YEAR(created_at)')
            ->orderBy('tahun', 'DESC')
            ->get()->getResultArray();

        $STATUS_LABEL = [
            'ide_diajukan'  => 'Ide Diajukan',
            'acc_ide'       => 'Acc Ide',
            'in_design'     => 'In Design',
            'review_design' => 'Review Design',
            'revisi'        => 'Revisi',
            'acc_final'     => 'Acc Final',
            'published'     => 'Published',
            'ditolak'       => 'Ditolak',
        ];

        return view('laporan/index', [
            'judul'        => 'Laporan',
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'tahunList'    => $tahunList,
            'perStatus'    => $perStatus,
            'perPlatform'  => $perPlatform,
            'perUser'      => $perUser,
            'totalBulanIni'=> $totalBulanIni,
            'STATUS_LABEL' => $STATUS_LABEL,
        ]);
    }

    /**
     * GET /dashboard/laporan/export
     * Export data ke CSV.
     */
    public function export(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (! $this->checkAccess()) {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }

        $db    = \Config\Database::connect();
        $bulan = (int) ($this->request->getGet('bulan') ?: date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $bulanStr = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        $data = $db->table('content_plan cp')
            ->select('cp.id, cp.judul_konten, cp.status, cp.tanggal_publish, cp.created_at,
                      u1.nama as pembuat, u2.nama as designer, u3.nama as uploader,
                      jk.nama_jenis, ct.nama_type as pillar')
            ->join('users u1', 'u1.id = cp.dibuat_oleh', 'left')
            ->join('users u2', 'u2.id = cp.assigned_designer', 'left')
            ->join('users u3', 'u3.id = cp.assigned_uploader', 'left')
            ->join('jenis_konten jk', 'jk.id = cp.jenis_konten_id', 'left')
            ->join('content_types ct', 'ct.id = cp.content_type_id', 'left')
            ->where("DATE_FORMAT(cp.created_at, '%Y-%m')", "{$tahun}-{$bulanStr}")
            ->orderBy('cp.created_at', 'DESC')
            ->get()->getResultArray();

        $NAMA_BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $filename = "laporan-konten-{$NAMA_BULAN[$bulan]}-{$tahun}.csv";

        // Build CSV
        $output = "\xEF\xBB\xBF"; // BOM UTF-8 agar Excel baca benar
        $output .= "No,Judul Konten,Status,Tanggal Publish,Dibuat Oleh,Designer,Uploader,Jenis Konten,Pillar,Tanggal Dibuat\n";

        foreach ($data as $i => $row) {
            $output .= implode(',', [
                $i + 1,
                '"' . str_replace('"', '""', $row['judul_konten']) . '"',
                '"' . $row['status'] . '"',
                '"' . ($row['tanggal_publish'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['pembuat'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['designer'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['uploader'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['nama_jenis'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['pillar'] ?? '-') . '"',
                '"' . $row['created_at'] . '"',
            ]) . "\n";
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setHeader('Pragma', 'no-cache')
            ->setBody($output);
    }
}
