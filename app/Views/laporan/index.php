<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/laporan.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$NAMA_BULAN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
               'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>

<!-- Filter Bar -->
<div class="lp-filter-bar">
    <form method="GET" class="lp-filter-form" id="filterForm">
        <div class="lp-filter-group">
            <label class="lp-filter-label">Bulan</label>
            <select name="bulan" class="lp-select" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= $NAMA_BULAN[$m] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="lp-filter-group">
            <label class="lp-filter-label">Tahun</label>
            <select name="tahun" class="lp-select" onchange="this.form.submit()">
                <?php foreach ($tahunList as $t): ?>
                    <option value="<?= $t['tahun'] ?>" <?= $tahun == $t['tahun'] ? 'selected' : '' ?>><?= $t['tahun'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="/dashboard/laporan/export?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="lp-btn-export">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </a>
    </form>
    <div class="lp-period-title"><?= $NAMA_BULAN[$bulan] ?> <?= $tahun ?> &mdash; <?= $totalBulanIni ?> konten</div>
</div>

<!-- Grid Laporan -->
<div class="lp-grid">

    <!-- Rekap per Status -->
    <div class="lp-card">
        <div class="lp-card-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Rekap per Status
        </div>
        <div class="lp-card-body">
            <?php if (empty($perStatus)): ?>
                <div class="lp-empty">Belum ada data konten di periode ini.</div>
            <?php else: ?>
                <?php
                $totalStatus = array_sum(array_column($perStatus, 'jumlah'));
                $statusColors = [
                    'ide_diajukan'  => '#6366f1', 'acc_ide' => '#3b82f6',
                    'in_design'     => '#8b5cf6', 'review_design' => '#f59e0b',
                    'revisi'        => '#ef4444', 'acc_final' => '#10b981',
                    'published'     => '#22c55e', 'ditolak' => '#6b7280',
                ];
                ?>
                <table class="lp-table">
                    <thead><tr><th>Status</th><th>Jumlah</th><th>Persentase</th></tr></thead>
                    <tbody>
                    <?php foreach ($perStatus as $row): ?>
                        <?php
                        $pct   = $totalStatus > 0 ? round($row['jumlah'] / $totalStatus * 100) : 0;
                        $color = $statusColors[$row['status']] ?? '#94a3b8';
                        ?>
                        <tr>
                            <td>
                                <span class="lp-dot" style="background:<?= $color ?>"></span>
                                <?= esc($STATUS_LABEL[$row['status']] ?? $row['status']) ?>
                            </td>
                            <td class="lp-num"><?= $row['jumlah'] ?></td>
                            <td>
                                <div class="lp-bar-wrap">
                                    <div class="lp-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                                    <span><?= $pct ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td><strong>Total</strong></td><td class="lp-num"><strong><?= $totalStatus ?></strong></td><td></td></tr></tfoot>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rekap per Platform -->
    <div class="lp-card">
        <div class="lp-card-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Rekap per Platform
        </div>
        <div class="lp-card-body">
            <?php if (empty($perPlatform)): ?>
                <div class="lp-empty">Belum ada data platform di periode ini.</div>
            <?php else: ?>
                <?php $maxPlat = max(1, (int)($perPlatform[0]['jumlah'] ?? 1)); ?>
                <table class="lp-table">
                    <thead><tr><th>Platform</th><th>Jumlah Konten</th></tr></thead>
                    <tbody>
                    <?php foreach ($perPlatform as $row): ?>
                        <tr>
                            <td><?= esc($row['nama_platform']) ?></td>
                            <td>
                                <div class="lp-bar-wrap">
                                    <div class="lp-bar" style="width:<?= round($row['jumlah']/$maxPlat*100) ?>%;background:var(--accent)"></div>
                                    <span><?= $row['jumlah'] ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Produktivitas per User -->
<div class="lp-card lp-card-full">
    <div class="lp-card-header">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Produktivitas Tim
    </div>
    <div class="lp-card-body">
        <?php if (empty($perUser)): ?>
            <div class="lp-empty">Belum ada data produktivitas di periode ini.</div>
        <?php else: ?>
            <table class="lp-table lp-table-full">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Total Konten</th>
                        <th>Published</th>
                        <th>Ditolak</th>
                        <th>Success Rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($perUser as $row): ?>
                    <?php
                    $rate = $row['total'] > 0 ? round($row['published'] / $row['total'] * 100) : 0;
                    $rateColor = $rate >= 70 ? '#22c55e' : ($rate >= 40 ? '#f59e0b' : '#ef4444');
                    ?>
                    <tr>
                        <td><strong><?= esc($row['nama'] ?? '—') ?></strong></td>
                        <td><span class="lp-role-badge"><?= esc($row['nama_role'] ?? '—') ?></span></td>
                        <td class="lp-num"><?= $row['total'] ?></td>
                        <td class="lp-num" style="color:#22c55e"><?= $row['published'] ?></td>
                        <td class="lp-num" style="color:#ef4444"><?= $row['ditolak'] ?></td>
                        <td>
                            <div class="lp-bar-wrap">
                                <div class="lp-bar" style="width:<?= $rate ?>%;background:<?= $rateColor ?>"></div>
                                <span style="color:<?= $rateColor ?>"><?= $rate ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
