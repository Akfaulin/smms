<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$roleNow = $kode_role ?? session('kode_role');
?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<div class="ik-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color:#0284c7; box-shadow:0 2px 8px rgba(2, 132, 199, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Dashboard Tugas Content Creator</h1>
            <p class="ik-header-sub">Monitoring pengerjaan visual desain, penulisan caption AI, dan status peninjauan Manager.</p>
        </div>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="ik-stat-grid">
    <div class="ik-stat-card">
        <div class="ik-stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statInDesign ?></div>
            <div class="ik-stat-lbl">Proses Desain</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statReview ?></div>
            <div class="ik-stat-lbl">Review Manager</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon orange">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statRevisi ?></div>
            <div class="ik-stat-lbl">Perlu Revisi</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statCompleted ?></div>
            <div class="ik-stat-lbl">Selesai di-ACC</div>
        </div>
    </div>
    <div class="ik-stat-card" onclick="window.location.href='?status=overdue'" style="cursor:pointer;" title="Klik untuk lihat konten lewat tenggat">
        <div class="ik-stat-icon" style="background:#fee2e2; color:#dc2626;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
            <div class="ik-stat-val" style="color:#dc2626;"><?= $statOverdue ?? 0 ?></div>
            <div class="ik-stat-lbl" style="color:#dc2626; font-weight:700;">Lewat Tenggat</div>
        </div>
    </div>
</div>

<!-- ── Main Data Card ──────────────────────────────────────── -->
<div class="cp-card">

    <!-- Toolbar & Filter Tabs -->
    <div class="cp-toolbar">
        <div class="cp-toggle-wrap">
            <a href="?status=all" class="cp-tog <?= ($filterStatus === 'all') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Semua Tugas
            </a>
            <a href="?status=in_design" class="cp-tog <?= ($filterStatus === 'in_design') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                Proses Desain
            </a>
            <a href="?status=review_design" class="cp-tog <?= ($filterStatus === 'review_design') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Review Manager
            </a>
            <a href="?status=revision" class="cp-tog <?= ($filterStatus === 'revision') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Perlu Revisi
            </a>
            <a href="?status=completed" class="cp-tog <?= ($filterStatus === 'completed') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Selesai di-ACC
            </a>
            <a href="?status=overdue" class="cp-tog <?= ($filterStatus === 'overdue') ? 'active' : '' ?>" style="<?= ($filterStatus === 'overdue') ? 'background:#ef4444;color:#fff;' : 'color:#dc2626;' ?>">
                <svg class="ik-tog-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Lewat Tenggat (<?= $statOverdue ?? 0 ?>)
            </a>
        </div>

        <div class="cp-filters" style="display:flex; align-items:center; gap:8px;">
            <select id="filterSort" class="cp-inp" style="width:auto; padding:7px 12px; font-size:12px; font-weight:700; border-radius:10px; border:1.5px solid #cbd5e1; cursor:pointer; background:#fff;" onchange="gantiSort(this.value)">
                <option value="publish_mepet" <?= ($sortBy === 'publish_mepet') ? 'selected' : '' ?>>Publish (Paling Mepet)</option>
                <option value="publish_jauh" <?= ($sortBy === 'publish_jauh') ? 'selected' : '' ?>>Publish (Paling Jauh)</option>
                <option value="diajukan_terbaru" <?= ($sortBy === 'diajukan_terbaru') ? 'selected' : '' ?>>Diajukan (Terbaru)</option>
                <option value="diajukan_terlama" <?= ($sortBy === 'diajukan_terlama') ? 'selected' : '' ?>>Diajukan (Terlama)</option>
            </select>
            <div style="position:relative;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchQuery" class="cp-inp" style="width:200px; padding:7px 12px 7px 34px; font-size:12.5px; border-radius:10px; border:1.5px solid #cbd5e1; outline:none;" placeholder="Cari tugas desain..." oninput="renderIdeList()">
            </div>
        </div>
    </div>

    <!-- Table List -->
    <div style="overflow-x:auto;">
        <table class="cp-table">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">#</th>
                    <th style="width:26%;">Judul & Brief Ide</th>
                    <th style="width:16%;">Status Tugas</th>
                    <th style="width:14%;">Platform</th>
                    <th style="width:20%; white-space:nowrap;">Jadwal & Waktu</th>
                    <th style="width:12%;">Pembuat Ide</th>
                    <th style="text-align:right; width:12%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="ideTableBody">
                <?php if (empty($konten)): ?>
                <tr>
                    <td colspan="7">
                        <div class="cp-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/>
                            </svg>
                            <p>Belum ada tugas desain yang cocok dengan filter.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($konten as $i => $k): ?>
                <tr class="cp-tr-item" onclick="bukaDetail(<?= $k['id'] ?>)" style="cursor:pointer">
                    <td style="color:#64748b; font-weight:600; text-align:center;"><?= $i + 1 ?></td>
                    <td>
                        <div style="font-weight:700; font-size:13.5px; color:#0f172a; line-height:1.4; margin-bottom:2px;"><?= esc($k['judul_konten']) ?></div>
                        <?php if (!empty($k['deskripsi'])): ?>
                        <div style="font-size:12px; color:#64748b; line-height:1.4; max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?= esc($k['deskripsi']) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $sCls = match($k['status']) {
                            'published', 'acc_final' => 'published',
                            'ditolak'   => 'ditolak',
                            'in_design', 'acc_ide' => 'acc',
                            'review_design' => 'draft',
                            'revisi'    => 'draft',
                            default => 'acc'
                        };
                        ?>
                        <span class="cp-badge <?= $sCls ?>" style="padding:4px 12px; font-size:12px; display:inline-block; font-weight:600; border-radius:20px; white-space:nowrap;">
                            <?= esc(\App\Services\TransisiKonten::labelStatus($k['status'])) ?>
                        </span>
                    </td>
                    <td style="font-size:13px; color:#475569; font-weight:500;">
                        <?= esc($k['platform_str'] ?: '—') ?>
                    </td>
                    <td>
                        <?php if ($k['tanggal_publish']): ?>
                            <?php 
                            $tglPub = strtotime($k['tanggal_publish']);
                            $todayMid = strtotime('today');
                            $pubMid   = strtotime(date('Y-m-d', $tglPub));
                            $diffDays = (int) round(($pubMid - $todayMid) / 86400);
                            $isPast   = ($tglPub < time());
                            $timeStr  = (strlen($k['tanggal_publish']) > 10) ? date('H:i', $tglPub) : '';
                            $timeDisplay = ($timeStr && $timeStr !== '00:00') ? ', ' . $timeStr : '';
                            ?>
                            <div style="font-size:12.5px; font-weight:700; color:#0f172a; white-space:nowrap; display:flex; align-items:center; gap:5px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?= date('d M Y', $tglPub) . $timeDisplay ?>
                            </div>
                            <?php if ($k['status'] === 'published'): ?>
                                <span style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Tayang
                                </span>
                            <?php elseif ($isPast): ?>
                                <span style="background:#fee2e2; color:#dc2626; border:1px solid #fecaca; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Lewat Tenggat
                                </span>
                            <?php elseif ($diffDays === 0): ?>
                                <span style="background:#fef3c7; color:#d97706; border:1px solid #fde68a; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Hari Ini
                                </span>
                            <?php elseif ($diffDays === 1): ?>
                                <span style="background:#e0f2fe; color:#0284c7; border:1px solid #bae6fd; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Besok
                                </span>
                            <?php else: ?>
                                <span style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?= $diffDays ?> Hari Lagi
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#94a3b8; font-size:12px; font-style:italic;">Belum dijadwalkan</span>
                        <?php endif; ?>

                        <?php if (!empty($k['created_at'])): ?>
                            <div style="font-size:11px; color:#64748b; margin-top:4px; white-space:nowrap; display:flex; align-items:center; gap:4px;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                Diajukan: <?= date('d M Y, H:i', strtotime($k['created_at'])) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px; color:#475569; font-weight:500;">
                        <span style="display:inline-flex; align-items:center; gap:5px; background:#f1f5f9; padding:3px 10px; border-radius:12px; font-size:12px; color:#334155; font-weight:600;">
                            <?= esc($k['nama_pembuat'] ?: '—') ?>
                        </span>
                    </td>
                    <td style="text-align:right" onclick="event.stopPropagation()">
                        <button class="cpb cpb-pri" onclick="bukaDetail(<?= $k['id'] ?>)" style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; white-space:nowrap;">
                            Kerjakan Desain
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: Detail Konten + Timeline                           -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="backDet">
    <div class="cp-modal modal-lg">
        <div class="cp-mh">
            <div>
                <div class="cp-mt" id="detJudul">Memuat...</div>
                <div class="cp-ms" id="detMeta"></div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('backDet')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="cp-tabs">
            <button class="cp-tab active" id="tabInfo" onclick="gantiTab('info')">Info Konten</button>
            <button class="cp-tab" id="tabTimeline" onclick="gantiTab('timeline')">Riwayat Status</button>
        </div>

        <!-- Tab Info -->
        <div class="cp-tab-panel active" id="panelInfo">
            <div class="cp-det-grid" id="detGrid">
                <div><div class="cp-det-label">Status</div><div class="cp-det-val" id="detStatus">—</div></div>
                <div><div class="cp-det-label">Tanggal Publish</div><div class="cp-det-val" id="detTanggal">—</div></div>
                <div><div class="cp-det-label">Dibuat Oleh</div><div class="cp-det-val" id="detPembuat">—</div></div>
                <div><div class="cp-det-label">Platform</div><div class="cp-det-val" id="detPlatform">—</div></div>
                <div><div class="cp-det-label">Jenis Konten</div><div class="cp-det-val" id="detJenis">—</div></div>
                <div><div class="cp-det-label">Content Pillar</div><div class="cp-det-val" id="detPillar">—</div></div>
                <div><div class="cp-det-label">Designer</div><div class="cp-det-val" id="detDesigner">—</div></div>
                <div><div class="cp-det-label">Uploader</div><div class="cp-det-val" id="detUploader">—</div></div>
            </div>
            <div class="cp-det-desc" id="detDesc" style="display:none;margin-top:16px;"></div>

            <!-- AI & Manual Caption Box -->
            <div class="cp-caption-box" id="captionBox" style="margin-top:16px; border:1px solid var(--cp-border); border-radius:12px; padding:16px; background:var(--cp-white);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                    <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Caption Konten
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="cpb cpb-sec" id="btnAiCaption" onclick="generateAiCaption()" style="padding:4px 10px; font-size:11.5px; background:var(--cp-purple-l); color:var(--cp-purple); border-color:var(--cp-purple-l);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg>
                            Tulis Caption AI
                        </button>
                    </div>
                </div>
                <textarea id="detCaption" class="cp-inp" rows="4" style="width:100%; font-size:12.5px; resize:vertical;" placeholder="Tulis atau hasilkan caption untuk postingan ini..."></textarea>
                <div style="text-align:right; margin-top:8px;">
                    <button type="button" class="cpb cpb-pri" onclick="simpanCaptionManual()" style="padding:4px 12px; font-size:11.5px;">Simpan Caption</button>
                </div>
            </div>

            <!-- Workspace Desain Content Creator -->
            <div id="boxWorkspaceCreator" style="display:none; margin-top:16px; border:1px solid #c7d2fe; background:#f5f7ff; border-radius:12px; padding:16px;">
                <div style="font-weight:700; color:#3730a3; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                    Workspace & Upload Hasil Desain
                </div>

                <div class="cp-form-grid" style="grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:11.5px; font-weight:600; color:#4338ca; display:block; margin-bottom:4px;">Link Canva / Figma / Google Drive</label>
                        <div style="display:flex; gap:6px;">
                            <input type="url" id="detDesignUrl" class="cp-inp" placeholder="https://canva.com/design/..." style="font-size:12px;">
                            <button type="button" class="cpb cpb-pri" onclick="simpanDesignUrl()" style="padding:4px 10px; font-size:11.5px; white-space:nowrap;">Simpan Link</button>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:11.5px; font-weight:600; color:#4338ca; display:block; margin-bottom:4px;">Direct Image URL (Opsional)</label>
                        <div style="display:flex; gap:6px;">
                            <input type="url" id="detImageUrl" class="cp-inp" placeholder="https://..." style="font-size:12px;">
                            <button type="button" class="cpb cpb-sec" onclick="simpanImageUrl()" style="padding:4px 10px; font-size:11.5px; white-space:nowrap;">Simpan URL</button>
                        </div>
                    </div>
                </div>

                <!-- Direct File Upload -->
                <div style="background:#ffffff; border:1px dashed #a5b4fc; border-radius:8px; padding:12px; text-align:center; margin-bottom:12px;">
                    <div style="font-size:12px; font-weight:600; color:#4338ca; margin-bottom:4px;">Atau Upload File Gambar / Video Desain (Max 5MB)</div>
                    <input type="file" id="detImageFile" accept="image/*,video/mp4" style="font-size:11.5px;" onchange="uploadImageFileDirect(this)">
                </div>

                <!-- Preview Area -->
                <div id="detPreviewBox" style="display:none; text-align:center; margin-bottom:12px;">
                    <img id="detPreviewImg" src="" style="max-height:180px; border-radius:8px; border:1px solid #e2e8f0;">
                </div>

                <!-- Ajukan Review ke Manager -->
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e0e7ff; padding-top:12px;">
                    <span style="font-size:11.5px; color:#6366f1;">Pastikan link desain atau file gambar sudah terisi sebelum mengajukan review.</span>
                    <button type="button" class="cpb" onclick="ajukanReviewDesign()" style="background:#4338ca; color:#ffffff; padding:7px 16px; font-size:12px; font-weight:600; border-radius:8px;">
                        Ajukan Review ke Manager ➔
                    </button>
                </div>
            </div>

            <!-- Transition Actions Area -->
            <div id="detActionArea" style="margin-top:16px;"></div>
        </div>

        <!-- Tab Timeline -->
        <div class="cp-tab-panel" id="panelTimeline">
            <div class="cp-timeline" id="detTimeline">
                <div style="color:var(--cp-muted);font-size:13px;text-align:center;padding:24px 0">Memuat riwayat...</div>
            </div>
        </div>

        <div class="cp-mf">
            <div class="cp-mf-left"></div>
            <div class="cp-mf-right">
                <button class="cpb cpb-sec" onclick="tutupModal('backDet')">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/js/content-plan.js"></script>
<script>
function gantiSort(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
}

function renderIdeList() {
    const q = document.getElementById('searchQuery').value.toLowerCase();
    const rows = document.querySelectorAll('#ideTableBody tr.cp-tr-item');
    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
}

function uploadImageFileDirect(input) {
    if (!input.files || !input.files[0] || !activeContent) return;
    const file = input.files[0];
    const formData = new FormData();
    formData.append('image_file', file);

    toast('Mengunggah gambar...', 'info');
    fetch(`/dashboard/content-plan/upload-image/${activeContent.id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'sukses') {
            toast('Gambar berhasil diunggah!', 'success');
            document.getElementById('detImageUrl').value = res.url;
            document.getElementById('detPreviewImg').src = res.url;
            document.getElementById('detPreviewBox').style.display = 'block';
            activeContent.image_url = res.url;
        } else {
            toast(res.pesan || 'Upload gagal.', 'error');
        }
    })
    .catch(() => toast('Gagal mengunggah file gambar.', 'error'));
}

function ajukanReviewDesign() {
    if (!activeContent) return;
    const link = document.getElementById('detDesignUrl').value.trim();
    const imgUrl = document.getElementById('detImageUrl').value.trim();

    if (!link && !imgUrl && !activeContent.image_url && !activeContent.design_url) {
        toast('Harap isi Link Desain (Canva/Drive) atau upload file gambar terlebih dahulu!', 'error');
        return;
    }

    konfirmasi(
        'Ajukan Review Desain',
        'Apakah Anda yakin desain visual sudah siap untuk ditinjau oleh Manager?',
        () => {
            lakukanTransisi(activeContent.id, 'review_design', 'Desain selesai dikerjakan, mengajukan review ke manager.');
        }
    );
}
</script>
<?= $this->endSection() ?>
