<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/ide-konten.css">
<style>
/* ─── Audit Trail Custom Styling ───────────────────────────── */
.at-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px 26px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.at-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.at-stat-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.at-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.04);
}
.at-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.at-stat-icon.indigo { background: #e0e7ff; color: #4338ca; }
.at-stat-icon.amber  { background: #fef3c7; color: #d97706; }
.at-stat-icon.emerald{ background: #d1fae5; color: #059669; }
.at-stat-icon.rose   { background: #ffe4e6; color: #e11d48; }
.at-stat-icon.purple { background: #f3e8ff; color: #7e22ce; }

.at-stat-val {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}
.at-stat-lbl {
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
    margin-top: 3px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ─── Timeline Feed Box ────────────────────────────────────── */
.at-timeline-box {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 24px;
}
.at-feed-item {
    display: flex;
    gap: 16px;
    position: relative;
    padding-bottom: 20px;
}
.at-feed-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 17px;
    top: 34px;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}
.at-feed-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    z-index: 1;
}
.at-feed-content {
    flex: 1;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
}

/* ─── Status Badge Colors ──────────────────────────────────── */
.st-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.st-ide_diajukan  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.st-acc_ide       { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.st-in_design     { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
.st-review_design { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }
.st-revisi        { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.st-acc_final     { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
.st-published     { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.st-ditolak       { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* ─── Modal Styles ─────────────────────────────────────────── */
.at-modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}
.at-modal-card {
    background: #ffffff;
    border-radius: 20px;
    max-width: 650px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    padding: 24px 28px;
    animation: modalPop 0.2s ease-out;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── 1. Page Header ────────────────────────────────────────── -->
<div class="at-header">
    <div style="display:flex; align-items:center; gap:14px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color:#4338ca; box-shadow:0 4px 12px rgba(67, 56, 202, 0.15);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <h1 style="font-size:22px; font-weight:800; color:#0f172a; margin:0;">History Pengajuan & Audit Trail</h1>
                <span style="font-size:11px; font-weight:700; background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:12px; border:1px solid #cbd5e1;">Poin 8 SMM</span>
            </div>
            <p style="font-size:13px; color:#64748b; margin:4px 0 0 0;">
                Audit log lengkap seluruh pengajuan ide, transisi revisi, persetujuan manager, hingga riwayat publikasi konten.
            </p>
        </div>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="/dashboard/content-plan" class="cpb cpb-sec" style="padding:8px 14px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Buka Content Plan
        </a>
    </div>
</div>

<!-- ── 2. Stat Summary Cards ─────────────────────────────────── -->
<div class="at-stat-grid">
    <div class="at-stat-card">
        <div class="at-stat-icon indigo">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <div class="at-stat-val"><?= $statTotalLogs ?></div>
            <div class="at-stat-lbl">Total Log Aktivitas</div>
        </div>
    </div>
    <div class="at-stat-card">
        <div class="at-stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/></svg>
        </div>
        <div>
            <div class="at-stat-val"><?= $statPengajuan ?></div>
            <div class="at-stat-lbl">Ide Baru Diajukan</div>
        </div>
    </div>
    <div class="at-stat-card">
        <div class="at-stat-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        </div>
        <div>
            <div class="at-stat-val"><?= $statApproved ?></div>
            <div class="at-stat-lbl">Persetujuan (ACC)</div>
        </div>
    </div>
    <div class="at-stat-card">
        <div class="at-stat-icon rose">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        </div>
        <div>
            <div class="at-stat-val"><?= $statRevisi ?></div>
            <div class="at-stat-lbl">Revisi Diminta</div>
        </div>
    </div>
    <div class="at-stat-card">
        <div class="at-stat-icon purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="at-stat-val"><?= $statPublished ?></div>
            <div class="at-stat-lbl">Konten Published</div>
        </div>
    </div>
</div>

<!-- ── 3. Main Card & Filter Toolbar ─────────────────────────── -->
<div class="cp-card">
    <form method="GET" action="/dashboard/audit-trail" class="cp-toolbar" style="flex-wrap:wrap; gap:10px;">
        <div class="cp-toggle-wrap">
            <a href="?action=all&period=<?= esc($period) ?>" class="cp-tog <?= ($action === 'all') ? 'active' : '' ?>">Semua Log</a>
            <a href="?action=ide_diajukan&period=<?= esc($period) ?>" class="cp-tog <?= ($action === 'ide_diajukan') ? 'active' : '' ?>">Pengajuan Ide</a>
            <a href="?action=approval&period=<?= esc($period) ?>" class="cp-tog <?= ($action === 'approval') ? 'active' : '' ?>">Persetujuan (ACC)</a>
            <a href="?action=revisi&period=<?= esc($period) ?>" class="cp-tog <?= ($action === 'revisi') ? 'active' : '' ?>">Revisi</a>
            <a href="?action=published&period=<?= esc($period) ?>" class="cp-tog <?= ($action === 'published') ? 'active' : '' ?>">Published</a>
            <a href="?action=ditolak&period=<?= esc($period) ?>" class="cp-tog <?= ($action === 'ditolak') ? 'active' : '' ?>">Ditolak</a>
        </div>

        <div class="cp-filters" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <!-- Periode Waktu -->
            <select name="period" class="cp-inp" style="width:auto; padding:7px 12px; font-size:12px; font-weight:700; border-radius:10px; border:1.5px solid #cbd5e1; background:#fff; cursor:pointer;" onchange="this.form.submit()">
                <option value="today" <?= ($period === 'today') ? 'selected' : '' ?>>Hari Ini</option>
                <option value="7days" <?= ($period === '7days') ? 'selected' : '' ?>>7 Hari Terakhir</option>
                <option value="30days" <?= ($period === '30days') ? 'selected' : '' ?>>30 Hari Terakhir</option>
                <option value="this_month" <?= ($period === 'this_month') ? 'selected' : '' ?>>Bulan Ini</option>
                <option value="all" <?= ($period === 'all') ? 'selected' : '' ?>>Semua Waktu</option>
            </select>

            <!-- Filter User -->
            <select name="user_id" class="cp-inp" style="width:auto; padding:7px 12px; font-size:12px; font-weight:700; border-radius:10px; border:1.5px solid #cbd5e1; background:#fff; cursor:pointer;" onchange="this.form.submit()">
                <option value="0">Semua Anggota Tim</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($userId === (int)$u['id']) ? 'selected' : '' ?>>
                        <?= esc($u['nama']) ?> (<?= esc($u['nama_role']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Search Input -->
            <div style="position:relative;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="<?= esc($search) ?>" class="cp-inp" style="width:200px; padding:7px 12px 7px 34px; font-size:12.5px; border-radius:10px; border:1.5px solid #cbd5e1; outline:none;" placeholder="Cari konten / catatan...">
            </div>

            <?php if (!empty($search) || $action !== 'all' || $userId > 0 || $period !== '30days'): ?>
                <a href="/dashboard/audit-trail" class="cpb cpb-sec" style="padding:7px 12px; font-size:12px; border-radius:10px; display:inline-flex; align-items:center; gap:4px;" title="Reset Filter">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ── 4. Table Audit Trail ──────────────────────────────────── -->
    <div style="overflow-x:auto;">
        <table class="cp-table">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">#</th>
                    <th style="width:16%; white-space:nowrap;">Waktu & Tanggal</th>
                    <th style="width:25%;">Konten Sasaran</th>
                    <th style="width:20%;">Perubahan Status</th>
                    <th style="width:15%;">Pelaksana (Aktor)</th>
                    <th style="width:16%;">Catatan / Keterangan</th>
                    <th style="text-align:right; width:8%;">Riwayat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7">
                        <div class="cp-empty" style="padding:48px 16px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="color:#cbd5e1; margin-bottom:12px;">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h4 style="font-size:15px; font-weight:700; color:#475569; margin:0 0 4px 0;">Belum Ada Riwayat Aktivitas</h4>
                            <p style="font-size:13px; color:#94a3b8; margin:0;">Tidak ditemukan catatan audit trail untuk filter yang Anda pilih.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $idx => $l): ?>
                    <?php
                    $statusMap = [
                        'ide_diajukan'  => 'Ide Diajukan',
                        'acc_ide'       => 'ACC Ide',
                        'in_design'     => 'Proses Desain',
                        'review_design' => 'Review Desain',
                        'revisi'        => 'Perlu Revisi',
                        'acc_final'     => 'ACC Final',
                        'published'     => 'Published',
                        'ditolak'       => 'Ditolak',
                    ];
                    $lblLama = $statusMap[$l['status_lama']] ?? ($l['status_lama'] ?: 'Awal');
                    $lblBaru = $statusMap[$l['status_baru']] ?? $l['status_baru'];
                    ?>
                    <tr style="transition:background 0.15s ease;">
                        <td style="text-align:center; font-weight:700; color:#94a3b8; font-size:12px;">
                            <?= $idx + 1 ?>
                        </td>
                        <td>
                            <div style="font-size:12.5px; font-weight:700; color:#1e293b;">
                                <?= date('d M Y', strtotime($l['created_at'])) ?>
                            </div>
                            <div style="font-size:11px; color:#64748b; margin-top:2px; display:flex; align-items:center; gap:4px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <?= date('H:i:s', strtotime($l['created_at'])) ?> WIB
                                <span style="background:#f1f5f9; padding:1px 6px; border-radius:6px; font-weight:600; color:#475569; margin-left:3px;"><?= $l['time_ago'] ?></span>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:13.5px; font-weight:700; color:#0f172a; line-height:1.35;">
                                <?= esc($l['judul_konten']) ?>
                            </div>
                            <?php if (!empty($l['platform_str'])): ?>
                                <div style="font-size:11px; color:#6366f1; font-weight:600; margin-top:4px; display:flex; align-items:center; gap:4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                                    <?= esc($l['platform_str']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span class="st-pill st-<?= esc($l['status_lama'] ?: 'ide_diajukan') ?>" style="opacity:0.85;">
                                    <?= esc($lblLama) ?>
                                </span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                <span class="st-pill st-<?= esc($l['status_baru']) ?>" style="box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                                    <?= esc($lblBaru) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:28px; height:28px; border-radius:50%; background:#e0e7ff; color:#4338ca; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <?= strtoupper(substr($l['nama_user'] ?: 'S', 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-size:12.5px; font-weight:700; color:#334155;">
                                        <?= esc($l['nama_user'] ?: 'Sistem Otomatis') ?>
                                    </div>
                                    <div style="font-size:11px; color:#64748b; font-weight:500;">
                                        <?= esc($l['nama_role'] ?: 'Bot / Auto') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($l['catatan'])): ?>
                                <div style="font-size:12px; color:#475569; background:#f8fafc; border-left:3px solid #6366f1; padding:6px 10px; border-radius:4px; line-height:1.4;">
                                    <?= nl2br(esc($l['catatan'])) ?>
                                </div>
                            <?php else: ?>
                                <span style="color:#cbd5e1; font-size:12px; font-style:italic;">Tidak ada catatan khusus</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <button onclick="bukaAuditDetail(<?= $l['content_id'] ?>)" class="cpb cpb-sec" style="padding:5px 10px; font-size:11.5px; font-weight:600; border-radius:8px; white-space:nowrap; background:#f8fafc; border:1px solid #cbd5e1; display:inline-flex; align-items:center; gap:4px;">
                                Detail
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── 5. Modal Detail Timeline Konten ───────────────────────── -->
<div id="modalAuditTimeline" class="at-modal-overlay" onclick="tutupModalAudit(event)">
    <div class="at-modal-card" onclick="event.stopPropagation()">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px; border-bottom:1px solid #e2e8f0; padding-bottom:14px;">
            <div>
                <span id="matStatusBadge" class="st-pill st-published" style="margin-bottom:6px; display:inline-flex;">Published</span>
                <h3 id="matJudul" style="font-size:17px; font-weight:800; color:#0f172a; margin:4px 0 0 0;">Judul Konten</h3>
                <p id="matMeta" style="font-size:12px; color:#64748b; margin:3px 0 0 0;">Platform & Jenis</p>
            </div>
            <button onclick="tutupModalAuditDirect()" style="background:#f1f5f9; border:none; width:32px; height:32px; border-radius:50%; color:#64748b; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Tutup">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div style="margin-bottom:20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 16px;">
            <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:4px;">Deskripsi / Brief Singkat:</div>
            <div id="matDeskripsi" style="font-size:12.5px; color:#334155; line-height:1.4;">—</div>
        </div>

        <h4 style="font-size:13.5px; font-weight:800; color:#0f172a; margin:0 0 14px 0; display:flex; align-items:center; gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Riwayat Lifecycle Status (Audit Trail)
        </h4>

        <!-- Timeline Container -->
        <div id="matTimelineList" style="padding:4px 0;">
            <!-- Rendered by JavaScript -->
        </div>

        <div style="margin-top:20px; text-align:right; border-top:1px solid #e2e8f0; padding-top:14px;">
            <button onclick="tutupModalAuditDirect()" class="cpb cpb-sec" style="padding:8px 18px; font-size:12.5px;">Tutup</button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const STATUS_NAMES = {
    'ide_diajukan': 'Ide Diajukan',
    'acc_ide': 'ACC Ide (Diterima)',
    'in_design': 'Proses Desain Visual',
    'review_design': 'Review Desain Manager',
    'revisi': 'Permintaan Revisi',
    'acc_final': 'ACC Final (Siap Upload)',
    'published': 'Published (Tayang Medsos)',
    'ditolak': 'Ide / Konten Ditolak'
};

async function bukaAuditDetail(contentId) {
    const modal = document.getElementById('modalAuditTimeline');
    const container = document.getElementById('matTimelineList');
    container.innerHTML = '<div style="text-align:center; padding:30px; color:#64748b; font-size:13px;">Memuat audit trail timeline...</div>';
    modal.style.display = 'flex';

    try {
        const res = await fetch(`/dashboard/audit-trail/detail/${contentId}`);
        const data = await res.json();

        if (data.status !== 'sukses') {
            container.innerHTML = `<div style="color:#ef4444; padding:20px; font-size:13px;">${data.pesan || 'Gagal memuat timeline'}</div>`;
            return;
        }

        const k = data.konten;
        const logs = data.logs || [];

        document.getElementById('matJudul').textContent = k.judul_konten || 'Tanpa Judul';
        document.getElementById('matDeskripsi').textContent = k.deskripsi || 'Tidak ada deskripsi.';
        document.getElementById('matMeta').textContent = `Target Publish: ${k.tanggal_publish ? k.tanggal_publish : 'Belum dijadwalkan'} | Pembuat: ${k.nama_pembuat || '—'}`;

        const badge = document.getElementById('matStatusBadge');
        badge.className = `st-pill st-${k.status}`;
        badge.textContent = STATUS_NAMES[k.status] || k.status;

        if (logs.length === 0) {
            container.innerHTML = '<div style="color:#94a3b8; font-size:12px; font-style:italic; padding:20px 0;">Belum ada catatan log aktivitas untuk konten ini.</div>';
            return;
        }

        let html = '';
        logs.forEach((l, idx) => {
            const isLatest = idx === 0;
            const dotBg = isLatest ? '#4f46e5' : '#94a3b8';
            const statusLabel = STATUS_NAMES[l.status_baru] || l.status_baru;

            html += `
                <div class="at-feed-item">
                    <div class="at-feed-dot" style="background:${dotBg}; color:#fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="at-feed-content" style="${isLatest ? 'border-color:#c7d2fe; background:#f5f7ff;' : ''}">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                            <span class="st-pill st-${l.status_baru}">${statusLabel}</span>
                            <span style="font-size:11px; color:#64748b;">${l.time_ago} (${l.created_at})</span>
                        </div>
                        <div style="font-size:12px; font-weight:700; color:#334155; margin-top:4px;">
                            Oleh: <span style="color:#4f46e5;">${l.nama_user || 'Sistem'}</span> (${l.nama_role || 'User'})
                        </div>
                        ${l.catatan ? `<div style="font-size:12px; color:#475569; background:#fff; border-left:3px solid ${dotBg}; padding:6px 10px; border-radius:4px; margin-top:6px; line-height:1.4;">${l.catatan}</div>` : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

    } catch (e) {
        container.innerHTML = '<div style="color:#ef4444; padding:20px; font-size:13px;">Terjadi kesalahan jaringan saat mengambil timeline.</div>';
    }
}

function tutupModalAuditDirect() {
    document.getElementById('modalAuditTimeline').style.display = 'none';
}

function tutupModalAudit(e) {
    if (e.target.id === 'modalAuditTimeline') {
        tutupModalAuditDirect();
    }
}
</script>
<?= $this->endSection() ?>
