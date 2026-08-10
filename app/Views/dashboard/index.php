<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/dashboard.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$roleLabels = [
    'manager'         => 'Manager',
    'content_creator' => 'Content Creator',
    'admin_medsos'    => 'Admin Medsos',
    'owner'           => 'Owner',
    'superadmin'      => 'Superadmin',
];

$antreanLabel = match($kode_role) {
    'manager'         => 'Menunggu Approval Anda',
    'content_creator' => 'Tugas Aktif Anda',
    'admin_medsos'    => 'Siap Diupload',
    default           => 'Konten Aktif',
};

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
?>

<!-- Hero Welcome -->
<div class="db-welcome">
    <div class="db-welcome-text">
        <h1 class="db-welcome-title">Selamat datang, <?= esc(session('nama')) ?>! 👋</h1>
        <p class="db-welcome-sub">
            <?= esc($roleLabels[$kode_role] ?? $kode_role) ?> &mdash; <?= date('l, d F Y') ?>
        </p>
    </div>
    <a href="/dashboard/content-plan" class="db-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Content Plan
    </a>
</div>

<!-- Stat Cards -->
<div class="db-stats">
    <div class="db-stat-card blue">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalAktif ?></div>
            <div class="db-stat-lbl">Konten Aktif</div>
        </div>
        <div class="db-stat-sub">Sedang berjalan</div>
    </div>
    <div class="db-stat-card green">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalPublish ?></div>
            <div class="db-stat-lbl">Published</div>
        </div>
        <div class="db-stat-sub">Bulan ini</div>
    </div>
    <div class="db-stat-card orange">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalRevisi ?></div>
            <div class="db-stat-lbl">Perlu Revisi</div>
        </div>
        <div class="db-stat-sub">Butuh tindakan</div>
    </div>
    <div class="db-stat-card red">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalDitolak ?></div>
            <div class="db-stat-lbl">Ditolak</div>
        </div>
        <div class="db-stat-sub">Total keseluruhan</div>
    </div>
</div>

<!-- Main Grid -->
<div class="db-grid">

    <!-- Antrean Tugas -->
    <div class="db-card db-card-queue">
        <div class="db-card-header">
            <div class="db-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <?= esc($antreanLabel) ?>
            </div>
            <a href="/dashboard/content-plan" class="db-link-all">Lihat Semua</a>
        </div>
        <div class="db-card-body">
            <?php if (empty($antrean)): ?>
                <div class="db-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <p>Tidak ada tugas yang menunggu 🎉</p>
                </div>
            <?php else: ?>
                <div class="db-queue-list">
                    <?php foreach ($antrean as $item): ?>
                        <?php
                        $sc = match($item['status']) {
                            'published'                   => 'published',
                            'ditolak'                     => 'ditolak',
                            'ide_diajukan', 'revisi'      => 'draft',
                            default                       => 'acc',
                        };
                        ?>
                        <a href="/dashboard/content-plan" class="db-queue-item">
                            <div class="db-queue-info">
                                <div class="db-queue-title"><?= esc($item['judul_konten']) ?></div>
                                <div class="db-queue-meta"><?= esc($item['nama_pembuat'] ?? '—') ?> &bull;
                                    <?= $item['tanggal_publish'] ? date('d M Y', strtotime($item['tanggal_publish'])) : 'Belum dijadwalkan' ?>
                                </div>
                            </div>
                            <span class="db-badge <?= $sc ?>"><?= $STATUS_LABEL[$item['status']] ?? $item['status'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Side Panel -->
    <div class="db-side">

        <!-- Alert: Konten Segera Publish -->
        <?php if (!empty($soon)): ?>
        <div class="db-card db-card-soon">
            <div class="db-card-header">
                <div class="db-card-title" style="color:#f59e0b">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Segera Publish
                </div>
            </div>
            <div class="db-card-body">
                <?php foreach ($soon as $item): ?>
                    <div class="db-soon-item">
                        <div class="db-soon-dot"></div>
                        <div>
                            <div class="db-soon-title"><?= esc($item['judul_konten']) ?></div>
                            <div class="db-soon-date"><?= date('d M Y', strtotime($item['tanggal_publish'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Distribusi Status -->
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    Distribusi Status
                </div>
            </div>
            <div class="db-card-body">
                <?php
                $statusColors = [
                    'ide_diajukan'  => '#6366f1',
                    'acc_ide'       => '#2d6cdf',
                    'in_design'     => '#8b5cf6',
                    'review_design' => '#d97706',
                    'revisi'        => '#dc2626',
                    'acc_final'     => '#059669',
                    'published'     => '#16a34a',
                    'ditolak'       => '#6b7280',
                ];
                $totalAll = max(1, array_sum($distribusi));
                foreach ($distribusi as $st => $jml):
                    $pct = round($jml / $totalAll * 100);
                    $color = $statusColors[$st] ?? '#94a3b8';
                ?>
                <div class="db-dist-item">
                    <div class="db-dist-label">
                        <span><?= $STATUS_LABEL[$st] ?? $st ?></span>
                        <span class="db-dist-num"><?= $jml ?></span>
                    </div>
                    <div class="db-dist-bar-wrap">
                        <div class="db-dist-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Platform Teratas -->
        <?php if (!empty($perPlatform)): ?>
        <div class="db-card">
            <div class="db-card-header">
                <div class="db-card-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Platform Teratas
                </div>
            </div>
            <div class="db-card-body">
                <?php $maxPlat = max(1, (int)($perPlatform[0]['jumlah'] ?? 1)); ?>
                <?php foreach ($perPlatform as $plat): ?>
                <div class="db-dist-item">
                    <div class="db-dist-label">
                        <span><?= esc($plat['nama_platform']) ?></span>
                        <span class="db-dist-num"><?= $plat['jumlah'] ?></span>
                    </div>
                    <div class="db-dist-bar-wrap">
                        <div class="db-dist-bar" style="width:<?= round($plat['jumlah']/$maxPlat*100) ?>%;background:var(--db-accent)"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Grafik Tren Konten -->
<div class="db-card db-card-chart">
    <div class="db-card-header">
        <div class="db-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Tren Konten Baru — 7 Hari Terakhir
        </div>
    </div>
    <div class="db-card-body">
        <div class="db-chart-wrap">
            <canvas id="trenChart" height="80"></canvas>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const labels = <?= json_encode(array_column($tren, 'tanggal')) ?>;
    const data   = <?= json_encode(array_column($tren, 'jumlah')) ?>;

    const ctx = document.getElementById('trenChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Konten Baru',
                data,
                backgroundColor: 'rgba(45, 108, 223, 0.15)',
                borderColor: '#2d6cdf',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(45, 108, 223, 0.35)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#9ca3af',
                    bodyColor: '#f9fafb',
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#6b7280', font: { size: 11 } },
                    grid: { color: '#e8edf5' }
                },
                x: {
                    ticks: { color: '#6b7280', font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>
<?= $this->endSection() ?>
