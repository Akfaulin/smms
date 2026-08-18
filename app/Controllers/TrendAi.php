<?php

namespace App\Controllers;

use App\Services\AiService;

/**
 * TrendAi Controller
 *
 * Dashboard khusus Creative Team untuk eksplorasi Bank Tren Medsos,
 * Kalender Event & Promo Musiman, serta Instant AI Viral Hook Generator.
 */
class TrendAi extends BaseController
{
    protected AiService $aiService;

    public function __construct()
    {
        $this->aiService = new AiService();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $role = session('kode_role');

        if (!in_array($role, ['creative_team', 'superadmin', 'owner'], true)) {
            return redirect()->to('/dashboard/content-plan');
        }

        $bisnisId = (int) session('bisnis_aktif_id');

        // Master trend audio & visual Reels/TikTok (Dinamis dari database trend_bank, filter by bisnis)
        $audioTrends = $db->table('trend_bank')
            ->where('bisnis_id', $bisnisId)
            ->where('status', 'aktif')
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        // Kalender event & momen promo musiman dihitung dinamis dari tanggal sekarang
        $eventCalendar = $this->getDynamicEventCalendar();

        // Master data untuk modal ajukan ide (filter by bisnis + global fallback)
        $platforms = $db->table('platforms')
            ->where('status', 'aktif')
            ->groupStart()
            ->where('bisnis_id', $bisnisId)
            ->orWhere('bisnis_id IS NULL')
            ->groupEnd()
            ->get()->getResultArray();

        $jenisKonten = $db->table('jenis_konten')
            ->groupStart()
            ->where('bisnis_id', $bisnisId)
            ->orWhere('bisnis_id IS NULL')
            ->groupEnd()
            ->get()->getResultArray();

        $contentTypes = $db->table('content_types')
            ->groupStart()
            ->where('bisnis_id', $bisnisId)
            ->orWhere('bisnis_id IS NULL')
            ->groupEnd()
            ->get()->getResultArray();

        return view('trend_ai/index', [
            'judul' => 'Bank Trend & Inspirasi AI',
            'audioTrends' => $audioTrends,
            'eventCalendar' => $eventCalendar,
            'platforms' => $platforms,
            'jenisKonten' => $jenisKonten,
            'contentTypes' => $contentTypes,
            'kode_role' => $role,
        ]);
    }

    /**
     * Hitung kalender event musiman & hari promo mendatang secara dinamis
     */
    private function getDynamicEventCalendar(): array
    {
        $currentYear = (int) date('Y');
        $eventsMaster = [
            ['month' => 1, 'day' => 1, 'momen' => 'Tahun Baru Masehi', 'tag' => 'Nasional'],
            ['month' => 2, 'day' => 14, 'momen' => 'Hari Kasih Sayang (Valentine Promo)', 'tag' => 'Promo Spesial'],
            ['month' => 3, 'day' => 8, 'momen' => 'International Women\'s Day', 'tag' => 'Branding'],
            ['month' => 4, 'day' => 21, 'momen' => 'Hari Kartini (Diskon Spesial Wanita)', 'tag' => 'Nasional'],
            ['month' => 5, 'day' => 2, 'momen' => 'Hari Pendidikan Nasional', 'tag' => 'Edukasi'],
            ['month' => 6, 'day' => 1, 'momen' => 'Hari Lahir Pancasila', 'tag' => 'Nasional'],
            ['month' => 7, 'day' => 23, 'momen' => 'Hari Anak Nasional', 'tag' => 'Branding'],
            ['month' => 8, 'day' => 17, 'momen' => 'Hari Kemerdekaan RI (Promo 17-an)', 'tag' => 'Nasional & Promo'],
            ['month' => 9, 'day' => 4, 'momen' => 'Hari Pelanggan Nasional', 'tag' => 'Loyalty & Promo'],
            ['month' => 9, 'day' => 9, 'momen' => 'Harbolnas 9.9 Mega Sale', 'tag' => 'Promo Big Sale'],
            ['month' => 10, 'day' => 10, 'momen' => 'Festival Belanja 10.10', 'tag' => 'Promo Big Sale'],
            ['month' => 10, 'day' => 28, 'momen' => 'Hari Sumpah Pemuda', 'tag' => 'Nasional'],
            ['month' => 11, 'day' => 11, 'momen' => 'Single Day 11.11 Super Sale', 'tag' => 'Promo Big Sale'],
            ['month' => 11, 'day' => 25, 'momen' => 'Hari Guru Nasional', 'tag' => 'Apresiasi'],
            ['month' => 12, 'day' => 12, 'momen' => 'Harbolnas 12.12 Puncak Promo', 'tag' => 'Promo Big Sale'],
            ['month' => 12, 'day' => 22, 'momen' => 'Hari Ibu Nasional', 'tag' => 'Branding'],
            ['month' => 12, 'day' => 25, 'momen' => 'Hari Natal & Libur Akhir Tahun', 'tag' => 'Holiday Promo'],
        ];

        $today = new \DateTime();
        $list = [];

        foreach ($eventsMaster as $em) {
            $eventDate = new \DateTime(sprintf('%04d-%02d-%02d', $currentYear, $em['month'], $em['day']));
            if ($eventDate < $today) {
                $eventDate->modify('+1 year');
            }
            $list[] = [
                'date_obj' => $eventDate,
                'tanggal'  => $eventDate->format('d F Y'),
                'momen'    => $em['momen'],
                'tag'      => $em['tag'],
            ];
        }

        // Tambahkan Payday promo terdekat (tanggal 25 bulan ini atau bulan depan)
        $paydayDate = new \DateTime(sprintf('%04d-%02d-25', (int)$today->format('Y'), (int)$today->format('m')));
        if ($paydayDate < $today) {
            $paydayDate->modify('+1 month');
        }
        $list[] = [
            'date_obj' => $paydayDate,
            'tanggal'  => $paydayDate->format('d F Y'),
            'momen'    => 'Payday Promo Gajian Akhir Bulan',
            'tag'      => 'Rutinan Sales',
        ];

        usort($list, fn($a, $b) => $a['date_obj'] <=> $b['date_obj']);

        return array_slice(array_map(function($item) {
            unset($item['date_obj']);
            return $item;
        }, $list), 0, 6);
    }

    /**
     * POST /dashboard/trend-ai/scan-trends (AI Trend Radar)
     */
    public function scanTrends(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db = \Config\Database::connect();
        $bisnisId = (int) session('bisnis_aktif_id');
        $userId = (int) session('user_id');
        $platform = $this->request->getPost('platform') ?: 'TikTok & Reels';

        // Ambil info bisnis aktif
        $bisnisRow = $db->table('bisnis')->where('id', $bisnisId)->get()->getRowArray();
        $namaBisnis = $bisnisRow['nama_bisnis'] ?? 'Brand';
        $deskripsiBisnis = $bisnisRow['deskripsi'] ?? 'Retail & E-commerce';

        try {
            $curatedTrends = $this->aiService->discoverTrends($namaBisnis, $deskripsiBisnis, $platform, $userId);
            $inserted = 0;

            foreach ($curatedTrends as $tr) {
                if (empty($tr['judul'])) continue;

                // Cek duplikasi judul di bisnis yang sama
                $exists = $db->table('trend_bank')
                    ->where('bisnis_id', $bisnisId)
                    ->where('judul', $tr['judul'])
                    ->countAllResults();

                if ($exists === 0) {
                    $db->table('trend_bank')->insert([
                        'bisnis_id'   => $bisnisId,
                        'judul'       => $tr['judul'],
                        'badge'       => $tr['badge'] ?? 'Viral',
                        'category'    => $tr['category'] ?? $platform,
                        'desk'        => $tr['desk'] ?? '',
                        'example'     => $tr['example'] ?? '',
                        'status'      => 'aktif',
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                    $inserted++;
                }
            }

            return $this->response->setJSON([
                'sukses' => true,
                'pesan'  => "AI Trend Radar berhasil menemukan dan menambahkan {$inserted} tren baru untuk bisnis Anda!",
                'count'  => $inserted,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'sukses' => false,
                'pesan'  => 'Gagal melakukan scan tren AI: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * POST /dashboard/trend-ai/store-trend (Tambah Temuan Tren Manual oleh Tim Kreatif)
     */
    public function storeTrend(): \CodeIgniter\HTTP\ResponseInterface
    {
        $db = \Config\Database::connect();
        $bisnisId = (int) session('bisnis_aktif_id');

        $judul = trim((string) $this->request->getPost('judul'));
        $badge = trim((string) $this->request->getPost('badge')) ?: 'Viral';
        $category = trim((string) $this->request->getPost('category')) ?: 'TikTok & Reels';
        $desk = trim((string) $this->request->getPost('desk'));
        $example = trim((string) $this->request->getPost('example'));

        if (!$judul) {
            return $this->response->setJSON([
                'sukses' => false,
                'pesan'  => 'Judul tren wajib diisi.',
            ])->setStatusCode(400);
        }

        $db->table('trend_bank')->insert([
            'bisnis_id'   => $bisnisId,
            'judul'       => $judul,
            'badge'       => $badge,
            'category'    => $category,
            'desk'        => $desk,
            'example'     => $example,
            'status'      => 'aktif',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'sukses' => true,
            'pesan'  => 'Temuan tren baru berhasil disimpan ke Bank Tren!',
        ]);
    }

    /**
     * POST /dashboard/trend-ai/delete-trend/(:num)
     */
    public function deleteTrend(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $db = \Config\Database::connect();
        $bisnisId = (int) session('bisnis_aktif_id');

        $db->table('trend_bank')
            ->where('id', $id)
            ->where('bisnis_id', $bisnisId)
            ->delete();

        return $this->response->setJSON([
            'sukses' => true,
            'pesan'  => 'Tren berhasil dihapus dari Bank Tren.',
        ]);
    }

    /**
     * POST /dashboard/trend-ai/generate-hook
     */
    public function generateHook(): \CodeIgniter\HTTP\ResponseInterface
    {
        $topik = $this->request->getPost('topik');
        $platform = $this->request->getPost('platform') ?: 'Instagram';

        if (!$topik) {
            return $this->response->setJSON([
                'sukses' => false,
                'pesan' => 'Topik wajib diisi.',
            ])->setStatusCode(400);
        }

        $userId = (int) session('user_id');

        try {
            $hasilAi = $this->aiService->generateHooks($topik, $platform, $userId);
            return $this->response->setJSON([
                'sukses' => true,
                'data'   => $hasilAi,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'sukses' => false,
                'pesan' => 'Gagal generate hook AI: ' . $e->getMessage(),
            ])->setStatusCode(500);
        }
    }
}
