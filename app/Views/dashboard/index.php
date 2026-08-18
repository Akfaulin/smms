<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/dashboard.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$roleLabels = [
    'manager'         => 'Manager',
    'creative_team'   => 'Creative Team',
    'content_creator' => 'Content Creator',
    'admin_medsos'    => 'Admin Medsos',
    'owner'           => 'Owner',
    'superadmin'      => 'Superadmin',
];

$antreanLabel = match($kode_role) {
    'manager'         => 'Menunggu Approval Anda',
    'creative_team'   => 'Ide & Perbaikan Konten Anda',
    'content_creator' => 'Tugas Aktif Anda',
    'admin_medsos'    => 'Siap Diupload',
    default           => 'Antrean Konten Aktif',
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

<!-- ── Hero Welcome Strip ────────────────────────────────────── -->
<div class="db-welcome" style="background:linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border:1.5px solid #e2e8f0; border-radius:16px; padding:22px 26px;">
    <div class="db-welcome-text">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
            <span style="font-size:11.5px; font-weight:700; background:#eff6ff; color:#2563eb; padding:3px 10px; border-radius:20px; text-transform:uppercase; letter-spacing:0.5px; border:1px solid #dbeafe;">
                <?= esc($roleLabels[$kode_role] ?? $kode_role) ?>
            </span>
            <span style="font-size:12.5px; color:#94a3b8; font-weight:500;">&bull; <?= date('l, d F Y') ?></span>
        </div>
        <h1 class="db-welcome-title" style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">Selamat datang kembali, <?= esc(session('nama')) ?>!</h1>
        <p class="db-welcome-sub" style="font-size:13px; color:#64748b; margin-top:3px;">Berikut adalah ringkasan perkembangan produksi dan antrean tugas Anda hari ini.</p>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <?php if (in_array($kode_role, ['creative_team', 'superadmin', 'owner'], true)): ?>
        <a href="/dashboard/ide-konten" class="cpb cpb-pri" style="padding:9px 16px; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/></svg>
            Ajukan Ide Konten
        </a>
        <?php endif; ?>
        <a href="/dashboard/content-plan" class="cpb cpb-sec" style="padding:9px 16px; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Content Plan
        </a>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────────── -->
<div class="db-stats" style="margin-bottom:24px;">
    <div class="db-stat-card blue">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalAktif ?></div>
            <div class="db-stat-lbl">Konten Aktif</div>
        </div>
        <div class="db-stat-sub">Sedang Berjalan</div>
    </div>
    <div class="db-stat-card green">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalPublish ?></div>
            <div class="db-stat-lbl">Published</div>
        </div>
        <div class="db-stat-sub">Bulan Ini</div>
    </div>
    <div class="db-stat-card orange">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalRevisi ?></div>
            <div class="db-stat-lbl">Perlu Revisi</div>
        </div>
        <div class="db-stat-sub">Tindakan Diperlukan</div>
    </div>
    <div class="db-stat-card red">
        <div class="db-stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num"><?= $totalDitolak ?></div>
            <div class="db-stat-lbl">Ditolak</div>
        </div>
        <div class="db-stat-sub">Total Akumulasi</div>
    </div>
    <div class="db-stat-card" style="background:#fff1f2; border:1px solid #fecdd3; cursor:pointer;" onclick="window.location.href='/dashboard/content-plan?view=overdue'" title="Lihat konten lewat tenggat">
        <div class="db-stat-icon" style="background:#ffe4e6; color:#e11d48;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="db-stat-info">
            <div class="db-stat-num" style="color:#e11d48;"><?= $totalOverdue ?? 0 ?></div>
            <div class="db-stat-lbl" style="color:#be123c; font-weight:700;">Lewat Tenggat</div>
        </div>
        <div class="db-stat-sub" style="color:#e11d48; font-weight:600;">Perlu Tindakan</div>
    </div>
</div>

<?php if (!empty($overdueList)): ?>
<!-- ── Overdue Alert Banner & Quick Action Widget ────────────── -->
<div style="background:#fff1f2; border:1.5px solid #fda4af; border-radius:16px; padding:18px 22px; margin-bottom:24px; box-shadow:0 4px 12px rgba(225, 29, 72, 0.08);">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:10px; background:#e11d48; color:#fff; display:flex; align-items:center; justify-content:center;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <h3 style="font-size:15px; font-weight:800; color:#9f1239; margin:0;">Perhatian: <?= count($overdueList) ?> Konten Melewati Target Tanggal Publish!</h3>
                <p style="font-size:12px; color:#be123c; margin:2px 0 0 0;">Konten di bawah ini telah melewati jadwal tayang namun belum dipublish. Segera tindak lanjuti atau jadwalkan ulang.</p>
            </div>
        </div>
        <a href="/dashboard/content-plan?view=overdue" class="cpb" style="background:#e11d48; color:#fff; padding:7px 14px; font-size:12px; font-weight:700; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
            Buka Tab Overdue
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px;">
        <?php foreach (array_slice($overdueList, 0, 4) as $od): ?>
            <?php
            $tglPub = !empty($od['tanggal_publish']) ? date('d M Y, H:i', strtotime($od['tanggal_publish'])) : '—';
            $diffDays = !empty($od['tanggal_publish']) ? ceil((time() - strtotime($od['tanggal_publish'])) / 86400) : 1;
            ?>
            <div style="background:#ffffff; border:1px solid #fecdd3; border-radius:12px; padding:12px 14px; display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                        <span style="background:#fee2e2; color:#b91c1c; font-size:10.5px; font-weight:700; padding:2px 6px; border-radius:6px;">Terlambat <?= $diffDays ?> Hari</span>
                        <span style="font-size:11px; color:#64748b; font-weight:500;">Oleh: <?= esc($od['nama_pembuat'] ?: 'Tim') ?></span>
                    </div>
                    <div style="font-size:13px; font-weight:700; color:#0f172a; line-height:1.3; margin-bottom:4px;">
                        <?= esc($od['judul_konten']) ?>
                    </div>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; border-top:1px dashed #f1f5f9; padding-top:8px; margin-top:8px;">
                    <span style="font-size:11.5px; color:#64748b; display:flex; align-items:center; gap:4px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?= $tglPub ?>
                    </span>
                    <a href="/dashboard/content-plan" style="font-size:11.5px; font-weight:700; color:#2563eb; text-decoration:none;">Reschedule ↗</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Main Grid Layout ───────────────────────────────────────── -->
<div class="db-grid">

    <!-- Left Column: Antrean Tugas -->
    <div class="db-card db-card-queue" style="border:1.5px solid #e2e8f0; border-radius:16px;">
        <div class="db-card-header" style="padding:18px 22px; background:#ffffff; border-bottom:1px solid #f1f5f9;">
            <div class="db-card-title" style="font-size:13.5px; font-weight:800; color:#0f172a;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" style="color:#2563eb;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <?= esc($antreanLabel) ?>
            </div>
            <a href="/dashboard/content-plan" class="db-link-all" style="font-size:12.5px; font-weight:700; color:#2563eb;">Lihat Semua ↗</a>
        </div>
        <div class="db-card-body" style="padding:22px;">
            <?php if (empty($antrean)): ?>
                <div class="cp-empty" style="padding:32px 16px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="color:#cbd5e1;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p style="margin-top:8px; font-weight:600; color:#64748b;">Tidak ada antrean tugas aktif yang memerlukan tindakan saat ini.</p>
                </div>
            <?php else: ?>
                <div class="db-queue-list">
                    <?php foreach ($antrean as $item): ?>
                        <?php
                        $sc = match($item['status']) {
                            'published', 'acc_final' => 'published',
                            'ditolak'                => 'ditolak',
                            'ide_diajukan', 'revisi' => 'draft',
                            default                  => 'acc',
                        };
                        ?>
                        <a href="/dashboard/content-plan" class="db-queue-item" style="padding:14px 16px; border-radius:12px; border:1px solid #f1f5f9; background:#ffffff; text-decoration:none; transition:all 0.18s ease;">
                            <div class="db-queue-info">
                                <div class="db-queue-title" style="font-weight:700; font-size:13.5px; color:#0f172a; margin-bottom:3px;"><?= esc($item['judul_konten']) ?></div>
                                <div class="db-queue-meta" style="font-size:12px; color:#64748b;">
                                    <span style="font-weight:600; color:#334155;"><?= esc($item['nama_pembuat'] ?? '—') ?></span> &bull; 
                                    <?= $item['tanggal_publish'] ? date('d M Y', strtotime($item['tanggal_publish'])) : 'Belum dijadwalkan' ?>
                                </div>
                            </div>
                            <span class="cp-badge <?= $sc ?>" style="padding:4px 12px; font-size:11.5px; font-weight:600; border-radius:20px; white-space:nowrap; margin-left:12px;">
                                <?= $STATUS_LABEL[$item['status']] ?? $item['status'] ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Side Panel -->
    <div class="db-side">

        <!-- Alert: Konten Segera Publish -->
        <?php if (!empty($soon)): ?>
        <div class="db-card db-card-soon" style="border:1.5px solid #fde68a; background:#fffbeb; border-radius:16px;">
            <div class="db-card-header" style="background:transparent; border-bottom:1px solid #fef3c7; padding:16px 20px;">
                <div class="db-card-title" style="color:#d97706; font-weight:800; font-size:13px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Segera Publish
                </div>
            </div>
            <div class="db-card-body" style="padding:16px 20px;">
                <?php foreach ($soon as $item): ?>
                    <div class="db-soon-item" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <div class="db-soon-dot" style="width:8px; height:8px; border-radius:50%; background:#d97706; flex-shrink:0;"></div>
                        <div>
                            <div class="db-soon-title" style="font-size:13px; font-weight:700; color:#92400e; line-height:1.3;"><?= esc($item['judul_konten']) ?></div>
                            <div class="db-soon-date" style="font-size:11.5px; color:#b45309; font-weight:600; margin-top:2px;"><?= date('d M Y', strtotime($item['tanggal_publish'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Distribusi Status -->
        <div class="db-card" style="border:1.5px solid #e2e8f0; border-radius:16px;">
            <div class="db-card-header" style="padding:16px 20px; border-bottom:1px solid #f1f5f9;">
                <div class="db-card-title" style="font-size:13px; font-weight:800; color:#0f172a;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17" style="color:#2563eb;"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    Distribusi Status Konten
                </div>
            </div>
            <div class="db-card-body" style="padding:18px 20px;">
                <?php
                $statusColors = [
                    'ide_diajukan'  => '#6366f1',
                    'acc_ide'       => '#2563eb',
                    'in_design'     => '#8b5cf6',
                    'review_design' => '#d97706',
                    'revisi'        => '#dc2626',
                    'acc_final'     => '#059669',
                    'published'     => '#16a34a',
                    'ditolak'       => '#64748b',
                ];
                $totalAll = max(1, array_sum($distribusi));
                foreach ($distribusi as $st => $jml):
                    $pct = round($jml / $totalAll * 100);
                    $color = $statusColors[$st] ?? '#94a3b8';
                ?>
                <div class="db-dist-item" style="margin-bottom:12px;">
                    <div class="db-dist-label" style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
                        <span><?= $STATUS_LABEL[$st] ?? $st ?></span>
                        <span class="db-dist-num" style="font-weight:700; color:#0f172a;"><?= $jml ?></span>
                    </div>
                    <div class="db-dist-bar-wrap" style="height:6px; background:#f1f5f9; border-radius:10px; overflow:hidden;">
                        <div class="db-dist-bar" style="width:<?= $pct ?>%; background:<?= $color ?>; height:100%; border-radius:10px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Platform Teratas -->
        <?php if (!empty($perPlatform)): ?>
        <div class="db-card" style="border:1.5px solid #e2e8f0; border-radius:16px;">
            <div class="db-card-header" style="padding:16px 20px; border-bottom:1px solid #f1f5f9;">
                <div class="db-card-title" style="font-size:13px; font-weight:800; color:#0f172a;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17" style="color:#2563eb;"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Platform Teratas
                </div>
            </div>
            <div class="db-card-body" style="padding:18px 20px;">
                <?php $maxPlat = max(1, (int)($perPlatform[0]['jumlah'] ?? 1)); ?>
                <?php foreach ($perPlatform as $plat): ?>
                <div class="db-dist-item" style="margin-bottom:12px;">
                    <div class="db-dist-label" style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
                        <span><?= esc($plat['nama_platform']) ?></span>
                        <span class="db-dist-num" style="font-weight:700; color:#0f172a;"><?= $plat['jumlah'] ?></span>
                    </div>
                    <div class="db-dist-bar-wrap" style="height:6px; background:#f1f5f9; border-radius:10px; overflow:hidden;">
                        <div class="db-dist-bar" style="width:<?= round($plat['jumlah']/$maxPlat*100) ?>%; background:#2563eb; height:100%; border-radius:10px;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Grafik Tren Konten -->
<div class="db-card db-card-chart" style="border:1.5px solid #e2e8f0; border-radius:16px; margin-bottom:24px;">
    <div class="db-card-header" style="padding:18px 22px; border-bottom:1px solid #f1f5f9;">
        <div class="db-card-title" style="font-size:13.5px; font-weight:800; color:#0f172a;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" style="color:#2563eb;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Tren Konten Baru &mdash; 7 Hari Terakhir
        </div>
    </div>
    <div class="db-card-body" style="padding:22px;">
        <div class="db-chart-wrap">
            <canvas id="trenChart" height="80"></canvas>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('trenChart');
    if (!ctx) return;

    const labels = <?= json_encode(array_column($tren, 'tgl')) ?>;
    const data   = <?= json_encode(array_column($tren, 'jumlah')) ?>;

    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Konten Dibuat',
                data: data,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#2563eb',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'DM Sans', size: 11 }, color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, font: { family: 'DM Sans', size: 11 }, color: '#64748b' },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
