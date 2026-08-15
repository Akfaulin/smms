<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$bolehBuat = in_array($kode_role ?? session('kode_role'), ['superadmin','owner','manager','creative_team'], true);
$roleNow   = $kode_role ?? session('kode_role');
?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<div class="ik-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="ik-header-icon-badge">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Dashboard Ide Konten</h1>
            <p class="ik-header-sub">Pengajuan, peninjauan, dan pelacakan status ide konten media sosial.</p>
        </div>
    </div>
    <?php if ($bolehBuat): ?>
    <div style="display:flex; gap:8px;">
        <button class="cpb cpb-sec" onclick="bukaModal('backAiIdeas')" style="background:var(--cp-purple-l);color:var(--cp-purple);border:1px solid var(--cp-purple-l); padding:9px 16px; font-weight:600;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Ide AI
        </button>
        <button class="cpb cpb-pri" onclick="bukaFormTambah()" style="padding:9px 18px; font-weight:600;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px; margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Ajukan Ide Konten
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="ik-stat-grid">
    <div class="ik-stat-card">
        <div class="ik-stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statTotal ?></div>
            <div class="ik-stat-lbl">Total Ide Konten</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statPending ?></div>
            <div class="ik-stat-lbl">Menunggu Approval</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statApproved ?></div>
            <div class="ik-stat-lbl">Disetujui / In Design</div>
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
                Semua Ide
            </a>
            <a href="?status=pending" class="cp-tog <?= ($filterStatus === 'pending') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Menunggu Review
            </a>
            <a href="?status=approved" class="cp-tog <?= ($filterStatus === 'approved') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Disetujui
            </a>
            <a href="?status=revision" class="cp-tog <?= ($filterStatus === 'revision') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Perlu Revisi
            </a>
            <a href="?status=rejected" class="cp-tog <?= ($filterStatus === 'rejected') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Ditolak
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
                <input type="text" id="searchQuery" class="cp-inp" style="width:200px; padding:7px 12px 7px 34px; font-size:12.5px; border-radius:10px; border:1.5px solid #cbd5e1; outline:none;" placeholder="Cari ide konten..." oninput="renderIdeList()">
            </div>
        </div>
    </div>

    <!-- Table List -->
    <div style="overflow-x:auto;">
        <table class="cp-table">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">#</th>
                    <th style="width:28%;">Judul & Brief Ide</th>
                    <th style="width:16%;">Status</th>
                    <th style="width:14%;">Platform</th>
                    <th style="width:20%; white-space:nowrap;">Jadwal & Waktu</th>
                    <th style="width:12%;">Pembuat</th>
                    <th style="text-align:right; width:10%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="ideTableBody">
                <?php if (empty($konten)): ?>
                <tr>
                    <td colspan="7">
                        <div class="cp-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/>
                            </svg>
                            <p>Belum ada ide konten yang cocok dengan filter.</p>
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
                            'published' => 'published',
                            'ditolak'   => 'ditolak',
                            'ide_diajukan', 'revisi' => 'draft',
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
                        <?php
                        $pembuatName = esc($k['nama_pembuat'] ?: '—');
                        $colorPalettes = [
                            ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                            ['bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
                            ['bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
                            ['bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
                            ['bg' => '#fdf2f8', 'text' => '#be185d', 'border' => '#fbcfe8'],
                        ];
                        $cIdx = abs(crc32($pembuatName)) % count($colorPalettes);
                        $pColor = $colorPalettes[$cIdx];
                        ?>
                        <span style="display:inline-flex; align-items:center; gap:5px; background:<?= $pColor['bg'] ?>; border:1px solid <?= $pColor['border'] ?>; padding:3px 10px; border-radius:12px; font-size:12px; color:<?= $pColor['text'] ?>; font-weight:600;">
                            <?= $pembuatName ?>
                        </span>
                    </td>
                    <td style="text-align:right" onclick="event.stopPropagation()">
                        <button class="cpb cpb-sec" onclick="bukaDetail(<?= $k['id'] ?>)" style="padding:6px 12px; font-size:11.5px; font-weight:600; border-radius:8px; white-space:nowrap; background:#f8fafc; border:1px solid #cbd5e1; color:#334155;">
                            Detail
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
<!-- MODAL: Tambah Ide Konten                                  -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="backForm">
    <div class="cp-modal modal-md" style="max-width:580px;">
        <div class="cp-mh" style="padding:20px 24px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color:#2563eb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                </div>
                <div>
                    <div class="cp-mt" id="formModalTitle" style="font-size:17px; font-weight:700; color:var(--cp-text);">Ajukan Ide Konten</div>
                    <div class="cp-ms" style="font-size:12.5px; color:var(--cp-muted); margin-top:2px;">Status awal: <span class="cp-badge draft" style="font-size:11px; padding:2px 8px; font-weight:600;">Ide Diajukan</span></div>
                </div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('backForm')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cp-mb" style="padding:20px 24px;">
            <div class="cp-row full" style="margin-bottom:16px;">
                <div class="cp-field">
                    <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Judul Konten <span style="color:#dc2626">*</span></label>
                    <input type="text" id="fJudul" class="cp-inp" placeholder="Contoh: Promo Spesial Kemerdekaan 17 Agustus" maxlength="200" style="padding:10px 14px; font-size:13.5px;">
                </div>
            </div>

            <div class="cp-row cp-row-3col" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Jadwal & Jam Publish</label>
                    <input type="datetime-local" id="fTanggal" class="cp-inp" style="padding:9px 12px;">
                </div>
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Jenis Konten</label>
                    <select id="fJenis" class="cp-inp" style="padding:9px 12px;">
                        <option value="">— Pilih —</option>
                        <?php foreach ($jenisKonten as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= esc($j['nama_jenis']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Content Pillar</label>
                    <select id="fPillar" class="cp-inp" style="padding:9px 12px;">
                        <option value="">— Pilih —</option>
                        <?php foreach ($contentTypes as $ct): ?>
                        <option value="<?= $ct['id'] ?>"><?= esc($ct['nama_type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="cp-row full" style="margin-bottom:16px;">
                <div class="cp-field">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin:0;">Deskripsi / Brief Ide</label>
                        <button type="button" class="cpb cpb-sec" id="btnAiBrief" style="padding:4px 10px; font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border-radius:6px; background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7;" onclick="generateAiBrief()" title="Bantu tulis brief otomatis dengan AI berdasarkan Judul Konten">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            Bantu Tulis Brief AI
                        </button>
                    </div>
                    <textarea id="fDesc" class="cp-inp" placeholder="Tulis deskripsi singkat ide, poin penting, atau konsep visual..." rows="3" style="padding:10px 14px; min-height:85px;"></textarea>
                </div>
            </div>

            <div class="cp-row full" style="margin-bottom:16px;">
                <div class="cp-field">
                    <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Platform Tujuan</label>
                    <div class="cp-plat-wrap" id="fPlatforms">
                        <?php foreach ($platforms as $p): ?>
                        <label class="cp-plat-lbl" id="plat-lbl-<?= $p['id'] ?>">
                            <input type="checkbox" class="plat-cb" value="<?= $p['id'] ?>">
                            <span><?= esc($p['nama_platform']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:11px 14px; display:flex; align-items:center; gap:10px; font-size:12px; color:#475569;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span><strong>Penugasan Otomatis:</strong> Designer & Uploader dialokasikan langsung oleh sistem (Content Creator & Admin Medsos).</span>
            </div>
        </div>
        <div class="cp-mf" style="padding:16px 24px; background:#fafafa; border-top:1px solid var(--cp-border);">
            <button class="cpb cpb-out" onclick="tutupModal('backForm')" style="padding:9px 18px; font-weight:600;">Batal</button>
            <button class="cpb cpb-pri" id="btnSimpanIde" onclick="simpanIde()" style="padding:9px 20px; font-weight:600;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px; margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Ajukan Ide Konten
            </button>
        </div>
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

            <!-- Unified Design, Asset & Caption Box (Poin 11 & 12) -->
            <div class="cp-unified-box" style="margin-top:16px; border:1.5px solid #e2e8f0; border-radius:14px; padding:18px; background:#fafbfc; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #edf2f7; padding-bottom:10px;">
                    <div style="font-weight:800; font-size:14px; color:#0f172a; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                        Materi Desain & Copywriting Konten
                    </div>
                    <button type="button" class="cpb cpb-sec" id="btnAiCaption" style="padding:6px 12px; font-size:12px; display:none; background:#f0f9ff; border:1px solid #bae6fd; color:#0284c7; font-weight:600; border-radius:8px;" onclick="generateAiCaption()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI
                    </button>
                </div>

                <!-- Caption Field -->
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:700; color:#334155; margin-bottom:6px; display:block;">
                        Caption Postingan (Copywriting & Hashtag)
                    </label>
                    <textarea id="inCaptionText" class="cp-inp" rows="4" placeholder="Tulis caption lengkap atau gunakan bantuan AI..." style="width:100%; font-size:13px; padding:10px 12px; border-radius:8px; line-height:1.5; resize:vertical; min-height:85px; background:#fff;"></textarea>
                    <div id="detCaption" style="display:none; font-size:13.5px; line-height:1.6; color:#1e293b; background:#fff; border:1px solid #e2e8f0; padding:12px; border-radius:8px;"></div>
                </div>

                <!-- Link Canva & Link Google Drive Grid -->
                <div id="gridDesainLinks" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <!-- Link Canva / Figma -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0; display:flex; align-items:center; gap:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#00c4cc" stroke-width="2.2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                                Link Canva / Figma
                            </label>
                            <a id="btnBukaCanva" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:4px 8px; font-size:11px; text-decoration:none; display:inline-flex; align-items:center; gap:3px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:700; border-radius:6px; opacity:0.45; cursor:not-allowed; filter:grayscale(0.7);" title="Link desain belum diisi" onclick="toast('Link desain belum diisi. Paste link Canva/Figma terlebih dahulu lalu klik Simpan.', 'error'); return false;">
                                Buka Canva ↗
                            </a>
                        </div>
                        <input type="url" id="inDesignUrl" class="cp-inp" placeholder="https://canva.com/design/..." style="width:100%; font-size:12.5px; padding:7px 10px; border-radius:8px;">
                    </div>

                    <!-- Link Gambar Drive / URL -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <label style="font-size:12px; font-weight:700; color:#334155; margin:0; display:flex; align-items:center; gap:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                Link Gambar / Drive
                            </label>
                            <a id="btnBukaGambar" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:4px 8px; font-size:11px; text-decoration:none; display:inline-flex; align-items:center; gap:3px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:700; border-radius:6px; opacity:0.45; cursor:not-allowed; filter:grayscale(0.7);" title="Link gambar belum diisi" onclick="toast('Link gambar belum diisi. Paste link Google Drive terlebih dahulu lalu klik Simpan.', 'error'); return false;">
                                Preview ↗
                            </a>
                        </div>
                        <input type="text" id="inImageUrl" class="cp-inp" placeholder="https://drive.google.com/file/d/..." style="width:100%; font-size:12.5px; padding:7px 10px; border-radius:8px;">
                    </div>
                </div>

                <!-- Unified 1-Click Save Action Button -->
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; border-top:1px dashed #e2e8f0; padding-top:14px;">
                    <div id="unifiedSaveStatus" style="font-size:12px; color:#16a34a; font-weight:700; display:none; align-items:center; gap:4px;"></div>
                    <button type="button" class="cpb cpb-pri" id="btnSimpanUnified" onclick="simpanDesainDanCaption()" style="padding:9px 20px; font-size:12.5px; font-weight:700; border-radius:10px; background:#2563eb; color:#fff; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(37,99,235,0.25); margin-left:auto;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Desain & Caption Sekaligus
                    </button>
                </div>
            </div>

            <!-- Transition Box -->
            <div class="cp-transition-box" id="transitionBox" style="display:none;margin-top:16px">
                <div class="cp-transition-label" style="display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Ubah Status Konten
                </div>
                <input type="hidden" id="selTransisi" value="">
                <div class="cp-trans-field" style="margin-bottom:12px;">
                    <label style="margin-bottom:6px; display:block;">Pilih Status Tujuan</label>
                    <div class="cp-status-btn-group" id="statusBtnContainer">
                    </div>
                </div>
                <div class="cp-catatan-wrap">
                    <textarea class="cp-inp" id="txCatatan" placeholder="Catatan (wajib untuk revisi/tolak)..." rows="2"></textarea>
                    <div class="cp-req-note" id="noteWajib">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Catatan wajib diisi untuk transisi ini.
                    </div>
                </div>
                <div class="cp-catatan-wrap" id="wrapLinkPost" style="display:none">
                    <input type="url" class="cp-inp" id="inLinkPost" placeholder="Link postingan (wajib untuk published)...">
                    <div class="cp-req-note show" id="noteLinkPost" style="display:none">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Link postingan wajib diisi.
                    </div>
                </div>
                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button class="cpb cpb-pri" id="btnEksekusi" onclick="eksekusiTransisi()" style="width:100%;">Ubah Status</button>
                </div>
            </div>
        </div>

        <!-- Tab Timeline -->
        <div class="cp-tab-panel" id="panelTimeline">
            <div class="cp-timeline" id="timelineWrap">
                <div style="text-align:center;padding:32px;color:var(--cp-muted)">Memuat riwayat...</div>
            </div>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: AI Idea Generator                                  -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="backAiIdeas">
    <div class="cp-modal modal-md">
        <div class="cp-mh">
            <div>
                <div class="cp-mt" style="display:flex;align-items:center;gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    AI Idea Generator
                </div>
                <div class="cp-ms">Generate 3 saran ide konten otomatis dengan AI</div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('backAiIdeas')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cp-mb">
            <div class="cp-row">
                <div class="cp-field">
                    <label>Topik / Produk / Promo <span style="color:#dc2626">*</span></label>
                    <input type="text" id="aiTopik" class="cp-inp" placeholder="Contoh: Diskon Kemerdekaan 17 Agustus">
                </div>
                <div class="cp-field">
                    <label>Platform Target</label>
                    <select id="aiPlatform" class="cp-inp">
                        <option value="Instagram">Instagram</option>
                        <option value="TikTok">TikTok</option>
                        <option value="Facebook">Facebook</option>
                        <option value="LinkedIn">LinkedIn</option>
                    </select>
                </div>
            </div>
            <div class="cp-row full">
                <button class="cpb cpb-pri" id="btnGenIde" style="width:100%;margin-top:8px" onclick="generateAiIdeas()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ide Konten
                </button>
            </div>
            <div id="aiIdeasResult" class="ai-ideas-box" style="display:none;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.ALL_KONTEN = <?= json_encode(array_values($konten)) ?>;
    window.ROLE       = <?= json_encode(session('kode_role')) ?>;
    window.USER_ID    = <?= json_encode(session('user_id')) ?>;

    function renderIdeList() {
        const query = (document.getElementById('searchQuery')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.cp-tr-item');
        rows.forEach(tr => {
            const text = tr.textContent.toLowerCase();
            tr.style.display = (!query || text.includes(query)) ? '' : 'none';
        });
    }

    function gantiSort(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', val);
        window.location.href = url.toString();
    }
</script>
<script src="/js/content-plan.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
