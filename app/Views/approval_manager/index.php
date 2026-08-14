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
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); color:#ca8a04; box-shadow:0 2px 8px rgba(202, 138, 4, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Dashboard Approval & Review Manager</h1>
            <p class="ik-header-sub">Pusat peninjauan 2 pintu untuk persetujuan ide dari Creative Team dan hasil desain visual dari Content Creator.</p>
        </div>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="ik-stat-grid">
    <div class="ik-stat-card">
        <div class="ik-stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statIdePending ?></div>
            <div class="ik-stat-lbl">Review Ide Baru</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statDesignPending ?></div>
            <div class="ik-stat-lbl">Review Hasil Desain</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statApproved ?></div>
            <div class="ik-stat-lbl">Sudah Disetujui</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon orange">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statRevisi ?></div>
            <div class="ik-stat-lbl">Dikembalikan (Revisi)</div>
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
                Semua Approval
            </a>
            <a href="?status=pending_ide" class="cp-tog <?= ($filterStatus === 'pending_ide') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/></svg>
                Review Ide Baru
            </a>
            <a href="?status=pending_design" class="cp-tog <?= ($filterStatus === 'pending_design') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                Review Desain
            </a>
            <a href="?status=approved" class="cp-tog <?= ($filterStatus === 'approved') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Disetujui
            </a>
            <a href="?status=revision" class="cp-tog <?= ($filterStatus === 'revision') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Dikembalikan
            </a>
        </div>

        <div class="cp-filters">
            <div style="position:relative;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchQuery" class="cp-inp" style="width:230px; padding:8px 12px 8px 34px; font-size:12.5px; border-radius:10px; border:1px solid #cbd5e1; outline:none;" placeholder="Cari data review..." oninput="renderIdeList()">
            </div>
        </div>
    </div>

    <!-- Table List -->
    <div style="overflow-x:auto;">
        <table class="cp-table">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">#</th>
                    <th style="width:28%;">Judul & Brief Konten</th>
                    <th style="width:18%;">Status Saat Ini</th>
                    <th style="width:16%;">Platform</th>
                    <th style="width:14%; white-space:nowrap;">Tanggal Publish</th>
                    <th style="width:12%;">Pembuat</th>
                    <th style="text-align:right; width:12%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="ideTableBody">
                <?php if (empty($konten)): ?>
                <tr>
                    <td colspan="7">
                        <div class="cp-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            <p>Belum ada data review yang cocok dengan filter.</p>
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
                            'ide_diajukan' => 'draft',
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
                    <td style="font-size:13px; color:#334155; font-weight:600; white-space:nowrap;">
                        <?= $k['tanggal_publish'] ? date('d M Y', strtotime($k['tanggal_publish'])) : '—' ?>
                    </td>
                    <td style="font-size:13px; color:#475569; font-weight:500;">
                        <span style="display:inline-flex; align-items:center; gap:5px; background:#f1f5f9; padding:3px 10px; border-radius:12px; font-size:12px; color:#334155; font-weight:600;">
                            <?= esc($k['nama_pembuat'] ?: '—') ?>
                        </span>
                    </td>
                    <td style="text-align:right" onclick="event.stopPropagation()">
                        <button class="cpb cpb-pri" onclick="bukaDetail(<?= $k['id'] ?>)" style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; white-space:nowrap; background:#2563eb; color:#fff;">
                            Review & Keputusan
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

        <!-- Tab Info / Review & Revisi -->
        <div class="cp-tab-panel active" id="panelInfo">
            
            <!-- Summary Banner -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 16px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="cp-det-label" style="font-size:12px; font-weight:700; color:#64748b; margin:0;">Status Saat Ini:</div>
                    <div id="detStatus">—</div>
                </div>
                <div style="font-size:12px; color:#475569;">
                    Diusulkan oleh: <strong id="detPembuat">—</strong>
                </div>
            </div>

            <!-- Form Revisi Data Konten (Akses Penuh Manager) -->
            <div style="border:1.5px solid #e2e8f0; border-radius:14px; padding:18px; background:#ffffff; margin-bottom:18px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                    <div style="font-size:14px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Revisi & Penyesuaian Data Ide (Manager Direct Edit)
                    </div>
                    <span style="font-size:11.5px; font-weight:600; color:#2563eb; background:#eff6ff; padding:3px 10px; border-radius:12px;">Akses Penuh Edit</span>
                </div>

                <!-- Judul Konten -->
                <div class="cp-field" style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Judul Konten <span style="color:#dc2626">*</span></label>
                    <input type="text" id="fMgrJudul" class="cp-inp" placeholder="Judul konten..." style="padding:10px 14px; font-size:13.5px; font-weight:600;">
                </div>

                <!-- Grid 3 Kolom: Tanggal/Jam, Jenis, Pillar -->
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div class="cp-field">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Jadwal & Jam Publish</label>
                        <input type="datetime-local" id="fMgrTanggal" class="cp-inp" style="padding:9px 12px; font-size:12.5px;">
                    </div>
                    <div class="cp-field">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Jenis Konten</label>
                        <select id="fMgrJenis" class="cp-inp" style="padding:9px 12px; font-size:12.5px;">
                            <option value="">— Pilih Jenis —</option>
                            <?php foreach ($jenisKonten as $j): ?>
                            <option value="<?= $j['id'] ?>"><?= esc($j['nama_jenis']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cp-field">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Content Pillar</label>
                        <select id="fMgrPillar" class="cp-inp" style="padding:9px 12px; font-size:12.5px;">
                            <option value="">— Pilih Pillar —</option>
                            <?php foreach ($contentTypes as $ct): ?>
                            <option value="<?= $ct['id'] ?>"><?= esc($ct['nama_type']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Platform Target -->
                <div class="cp-field" style="margin-bottom:14px;">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Platform Target</label>
                    <div class="cp-plat-wrap" id="fMgrPlatformsWrap">
                        <?php foreach ($platforms as $p): ?>
                        <label class="cp-plat-lbl mgr-plat-lbl-<?= $p['id'] ?>">
                            <input type="checkbox" class="plat-cb mgr-plat-cb" value="<?= $p['id'] ?>">
                            <span><?= esc($p['nama_platform']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Penugasan Tim (Designer & Uploader) -->
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div class="cp-field">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Tugaskan Designer (Content Creator)</label>
                        <select id="fMgrDesigner" class="cp-inp" style="padding:9px 12px; font-size:12.5px;">
                            <option value="">— Pilih Designer —</option>
                            <?php foreach ($designers as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= esc($u['nama']) ?> (<?= esc($u['nama_role']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cp-field">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Tugaskan Uploader (Admin Medsos)</label>
                        <select id="fMgrUploader" class="cp-inp" style="padding:9px 12px; font-size:12.5px;">
                            <option value="">— Pilih Uploader —</option>
                            <?php foreach ($uploaders as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= esc($u['nama']) ?> (<?= esc($u['nama_role']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Deskripsi / Brief Ide -->
                <div class="cp-field" style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin:0;">Deskripsi / Brief Konsep Visual</label>
                        <button type="button" class="cpb cpb-sec" id="btnMgrAiBrief" style="padding:4px 10px; font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border-radius:6px; background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7;" onclick="generateAiBriefManager()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            Saran Brief AI
                        </button>
                    </div>
                    <textarea id="fMgrDesc" class="cp-inp" placeholder="Tulis brief ide, poin penting, arahan revisi atau konsep..." rows="3" style="padding:10px 14px; min-height:75px; font-size:13px; line-height:1.5;"></textarea>
                </div>

                <!-- Caption Konten (Bisa diedit Manager) -->
                <div class="cp-field" style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; flex-wrap:wrap; gap:8px;">
                        <label style="font-size:12px; font-weight:700; color:#374151; margin:0;">Caption Konten</label>
                        <button type="button" class="cpb cpb-sec" id="btnAiCaption" style="padding:4px 10px; font-size:11.5px; display:inline-flex; align-items:center; gap:4px; background:#f0f9ff; border:1px solid #bae6fd; color:#0284c7; font-weight:600; border-radius:6px;" onclick="generateAiCaption()">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Tulis Caption AI
                        </button>
                    </div>
                    <textarea id="inCaptionText" class="cp-inp" rows="3" placeholder="Tulis caption manual atau gunakan bantuan AI..." style="width:100%; font-size:13px; padding:10px 12px; border-radius:8px; line-height:1.5; resize:vertical; min-height:75px;"></textarea>
                </div>

                <!-- Tombol Simpan Perubahan Data -->
                <div style="display:flex; justify-content:flex-end; padding-top:6px;">
                    <button type="button" class="cpb cpb-pri" id="btnSimpanMgrEdit" onclick="simpanManagerEdit()" style="padding:9px 20px; font-size:12.5px; font-weight:700; border-radius:8px; display:inline-flex; align-items:center; gap:6px; background:#2563eb; color:#fff;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Perubahan Data
                    </button>
                </div>
            </div>

            <!-- Link Desain & Preview Materi Konten -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:18px;">
                <!-- Link Desain Canva Box -->
                <div class="cp-design-box" id="designBox" style="border:1px solid var(--cp-border); border-radius:12px; padding:14px; background:var(--cp-white);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <div style="font-weight:700; font-size:12.5px; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#00c4cc" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                            Link Desain Canva / Figma
                        </div>
                        <a id="btnBukaCanva" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:5px 10px; font-size:11.5px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600; border-radius:6px;">
                            Buka Desain ↗
                        </a>
                    </div>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <input type="url" id="inDesignUrl" class="cp-inp" placeholder="Link Canva/Figma..." style="flex:1; font-size:12px; padding:7px 10px; border-radius:6px;">
                        <button type="button" class="cpb cpb-pri" id="btnSimpanDesignUrl" onclick="simpanDesignUrl()" style="padding:7px 12px; font-size:11.5px; font-weight:600; white-space:nowrap; border-radius:6px;">
                            Simpan
                        </button>
                    </div>
                    <a id="btnBukaGambar" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600; border-radius:8px; opacity:0.45; cursor:not-allowed; filter:grayscale(0.7);" title="Link gambar belum diisi" onclick="toast('Link gambar belum diisi. Paste link Google Drive terlebih dahulu lalu klik Simpan Link Gambar.', 'error'); return false;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Preview ↗
                    </a>
                </div>
                <div style="display:flex; gap:8px; align-items:flex-start;">
                    <textarea id="inImageUrl" class="cp-inp" rows="3" placeholder="Paste link Google Drive (https://drive.google.com/...) atau URL publik lainnya.&#10;Untuk Carousel, pisahkan tiap link dengan Enter (baris baru)." style="flex:1; font-size:13px; padding:8px 12px; border-radius:8px; resize:vertical; min-height:40px;"></textarea>
                    <button type="button" class="cpb cpb-pri" id="btnSimpanImageUrl" onclick="simpanImageUrl()" style="padding:8px 16px; font-size:12px; font-weight:600; white-space:nowrap; border-radius:8px; align-self:stretch;">
                        Simpan Link Gambar
                    </button>
                </div>

                <!-- Link Gambar Google Drive Box -->
                <div class="cp-upload-box" id="uploadImageBox" style="border:1px solid var(--cp-border); border-radius:12px; padding:14px; background:var(--cp-white);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <div style="font-weight:700; font-size:12.5px; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            Link Gambar / Drive
                        </div>
                        <a id="btnBukaGambar" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:5px 10px; font-size:11.5px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600; border-radius:6px;">
                            Preview ↗
                        </a>
                    </div>
                    <div style="display:flex; gap:6px; align-items:center;">
                        <input type="text" id="inImageUrl" class="cp-inp" placeholder="Link Google Drive..." style="flex:1; font-size:12px; padding:7px 10px; border-radius:6px;">
                        <button type="button" class="cpb cpb-pri" id="btnSimpanImageUrl" onclick="simpanImageUrl()" style="padding:7px 12px; font-size:11.5px; font-weight:600; white-space:nowrap; border-radius:6px;">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transition & Decision Box (Keputusan Manager + Catatan Revisi) -->
            <div class="cp-transition-box" id="transitionBox" style="display:none; margin-top:0; border:1.5px solid #cbd5e1; border-radius:14px; padding:18px; background:#f8fafc;">
                <div class="cp-transition-label" style="display:flex; align-items:center; gap:6px; font-size:13.5px; font-weight:800; color:#0f172a; margin-bottom:12px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Keputusan Approval Manager & Catatan Instruksi
                </div>
                <input type="hidden" id="selTransisi" value="">
                <div class="cp-trans-field" style="margin-bottom:14px;">
                    <label style="margin-bottom:8px; display:block; font-size:12px; font-weight:700; color:#374151;">Pilih Keputusan Status:</label>
                    <div class="cp-status-btn-group" id="statusBtnContainer">
                        <!-- Buttons rendered dynamically -->
                    </div>
                </div>
                <div class="cp-catatan-wrap" style="margin-bottom:12px;">
                    <label style="margin-bottom:6px; display:block; font-size:12px; font-weight:700; color:#374151;">Catatan Instruksi Revisi / Feedback Manager:</label>
                    <textarea class="cp-inp" id="txCatatan" placeholder="Tulis instruksi perbaikan untuk Tim Kreatif / Designer (wajib jika memilih status Revisi atau Ditolak)..." rows="3" style="padding:10px 14px; font-size:13px; min-height:80px;"></textarea>
                    <div class="cp-req-note" id="noteWajib">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Catatan instruksi wajib diisi jika memilih opsi Revisi atau Ditolak.
                    </div>
                </div>
                <div class="cp-catatan-wrap" id="wrapLinkPost" style="display:none; margin-bottom:12px;">
                    <label style="margin-bottom:6px; display:block; font-size:12px; font-weight:700; color:#374151;">Link Postingan Publikasi:</label>
                    <input type="url" class="cp-inp" id="inLinkPost" placeholder="https://instagram.com/p/... (wajib untuk published)">
                    <div class="cp-req-note show" id="noteLinkPost" style="display:none">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Link postingan wajib diisi.
                    </div>
                </div>
                <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                    <button class="cpb cpb-pri" id="btnEksekusi" onclick="eksekusiTransisi()" style="width:100%; padding:11px; font-size:13.5px; font-weight:800; border-radius:10px; background:#059669; color:#fff;">
                        Kirim Keputusan Status & Catatan
                    </button>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.ALL_KONTEN = <?= json_encode(array_values($konten)) ?>;
    window.ROLE       = <?= json_encode(session('kode_role')) ?>;
    window.USER_ID    = <?= json_encode(session('user_id')) ?>;
    window.IS_MANAGER_APPROVAL = true;

    function renderIdeList() {
        const query = (document.getElementById('searchQuery')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.cp-tr-item');
        rows.forEach(tr => {
            const text = tr.textContent.toLowerCase();
            tr.style.display = (!query || text.includes(query)) ? '' : 'none';
        });
    }

    // Populate full form fields for Manager review & edit (Point 5)
    function populateManagerForm(k) {
        if (!k) return;
        const fJudul    = document.getElementById('fMgrJudul');
        const fDesc     = document.getElementById('fMgrDesc');
        const fTanggal  = document.getElementById('fMgrTanggal');
        const fJenis    = document.getElementById('fMgrJenis');
        const fPillar   = document.getElementById('fMgrPillar');
        const fDesigner = document.getElementById('fMgrDesigner');
        const fUploader = document.getElementById('fMgrUploader');
        const fCaption  = document.getElementById('inCaptionText');

        if (fJudul)    fJudul.value = k.judul_konten || '';
        if (fDesc)     fDesc.value = k.deskripsi || '';
        if (fTanggal) {
            fTanggal.value = k.tanggal_publish ? k.tanggal_publish.replace(' ', 'T').substring(0, 16) : '';
        }
        if (fJenis)    fJenis.value = k.jenis_konten_id || '';
        if (fPillar)   fPillar.value = k.content_type_id || '';
        if (fDesigner) fDesigner.value = k.assigned_designer || '';
        if (fUploader) fUploader.value = k.assigned_uploader || '';
        if (fCaption)  fCaption.value = k.caption || '';

        // Platform checkboxes
        const platIds = (k.platforms || []).map(p => String(p.id || p.platform_id));
        document.querySelectorAll('.mgr-plat-cb').forEach(cb => {
            const isChecked = platIds.includes(cb.value);
            cb.checked = isChecked;
            cb.closest('.cp-plat-lbl')?.classList.toggle('on', isChecked);
        });
    }

    // Direct save edited data for Manager
    async function simpanManagerEdit() {
        if (!activeContent) return;
        const judul = document.getElementById('fMgrJudul')?.value.trim();
        if (!judul) { toast('Judul konten tidak boleh kosong.', 'error'); return; }

        const btn = document.getElementById('btnSimpanMgrEdit');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="cp-spin"></span> Menyimpan...';
        }

        const fd = new FormData();
        fd.append('judul_konten', judul);
        fd.append('deskripsi', document.getElementById('fMgrDesc')?.value || '');
        fd.append('tanggal_publish', document.getElementById('fMgrTanggal')?.value || '');
        fd.append('jenis_konten_id', document.getElementById('fMgrJenis')?.value || '');
        fd.append('content_type_id', document.getElementById('fMgrPillar')?.value || '');
        fd.append('assigned_designer', document.getElementById('fMgrDesigner')?.value || '');
        fd.append('assigned_uploader', document.getElementById('fMgrUploader')?.value || '');
        fd.append('caption', document.getElementById('inCaptionText')?.value || '');

        document.querySelectorAll('.mgr-plat-cb:checked').forEach(cb => {
            fd.append('platforms[]', cb.value);
        });

        const res = await api(`/dashboard/content-plan/update/${activeContent}`, 'POST', fd);

        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }

        if (res.status === 'sukses') {
            toast('Data konten & penugasan berhasil diperbarui oleh Manager!', 'success');
            const detJudul = document.getElementById('detJudul');
            if (detJudul) detJudul.textContent = judul;
            
            // Sync local data
            const idx = (window.ALL_KONTEN || []).findIndex(x => x.id == activeContent);
            if (idx !== -1) {
                window.ALL_KONTEN[idx].judul_konten = judul;
                window.ALL_KONTEN[idx].deskripsi = document.getElementById('fMgrDesc')?.value || '';
                window.ALL_KONTEN[idx].tanggal_publish = document.getElementById('fMgrTanggal')?.value || '';
                window.ALL_KONTEN[idx].caption = document.getElementById('inCaptionText')?.value || '';
            }
        } else {
            toast(res.pesan || 'Gagal memperbarui data konten.', 'error');
        }
    }

    // Helper AI Brief for Manager
    async function generateAiBriefManager() {
        const judul = document.getElementById('fMgrJudul')?.value.trim();
        const jenis = document.getElementById('fMgrJenis')?.options[document.getElementById('fMgrJenis')?.selectedIndex]?.text || '';
        const pillar = document.getElementById('fMgrPillar')?.options[document.getElementById('fMgrPillar')?.selectedIndex]?.text || '';

        if (!judul) {
            toast('Silakan isi Judul Konten terlebih dahulu.', 'error');
            return;
        }

        const btn = document.getElementById('btnMgrAiBrief');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Generating...';
        }

        try {
            const csrfToken = (typeof getCsrfToken === 'function' ? getCsrfToken() : (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''));
            const response = await fetch('/dashboard/content-plan/ai-brief', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: 'judul=' + encodeURIComponent(judul) + '&jenis=' + encodeURIComponent(jenis) + '&pillar=' + encodeURIComponent(pillar)
            });

            const res = await response.json();
            if (res.status === 'sukses' && res.data && res.data.brief) {
                const descInput = document.getElementById('fMgrDesc');
                if (descInput) descInput.value = res.data.brief;
                toast('Brief ide berhasil di-generate AI!', 'success');
            } else {
                toast(res.pesan || 'Gagal membuat brief AI.', 'error');
            }
        } catch (err) {
            toast('Terjadi kesalahan koneksi.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.mgr-plat-cb').forEach(cb => {
            cb.addEventListener('change', () => {
                cb.closest('.cp-plat-lbl')?.classList.toggle('on', cb.checked);
            });
        });
    });
</script>
<script src="/js/content-plan.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
