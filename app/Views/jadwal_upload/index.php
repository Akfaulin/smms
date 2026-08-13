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
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color:#059669; box-shadow:0 2px 8px rgba(5, 150, 105, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Dashboard Jadwal & Upload Admin Medsos</h1>
            <p class="ik-header-sub">Pemantauan jadwal posting harian, eksekusi publish konten yang di-ACC Final, dan input link URL media sosial.</p>
        </div>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────── -->
<div class="ik-stat-grid">
    <div class="ik-stat-card">
        <div class="ik-stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statReady ?></div>
            <div class="ik-stat-lbl">Siap Diposting</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statToday ?></div>
            <div class="ik-stat-lbl">Posting Hari Ini</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statPublished ?></div>
            <div class="ik-stat-lbl">Sudah Published</div>
        </div>
    </div>
    <div class="ik-stat-card">
        <div class="ik-stat-icon orange">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div>
            <div class="ik-stat-val"><?= $statTotal ?></div>
            <div class="ik-stat-lbl">Total Data Posting</div>
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
                Semua Posting
            </a>
            <a href="?status=ready" class="cp-tog <?= ($filterStatus === 'ready') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/></svg>
                Siap Upload
            </a>
            <a href="?status=today" class="cp-tog <?= ($filterStatus === 'today') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Hari Ini
            </a>
            <a href="?status=published" class="cp-tog <?= ($filterStatus === 'published') ? 'active' : '' ?>">
                <svg class="ik-tog-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Sudah Published
            </a>
        </div>

        <div class="cp-filters">
            <div style="position:relative;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchQuery" class="cp-inp" style="width:230px; padding:8px 12px 8px 34px; font-size:12.5px; border-radius:10px; border:1px solid #cbd5e1; outline:none;" placeholder="Cari konten posting..." oninput="renderIdeList()">
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
                    <th style="width:18%;">Status Upload</th>
                    <th style="width:16%;">Platform</th>
                    <th style="width:14%; white-space:nowrap;">Tanggal Publish</th>
                    <th style="width:12%;">Designer</th>
                    <th style="text-align:right; width:12%;">Aksi</th>
                </tr>
            </thead>
            <tbody id="ideTableBody">
                <?php if (empty($konten)): ?>
                <tr>
                    <td colspan="7">
                        <div class="cp-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/>
                            </svg>
                            <p>Belum ada postingan yang cocok dengan filter.</p>
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
                            'acc_final' => 'acc',
                            'ditolak'   => 'ditolak',
                            default => 'draft'
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
                            <?= esc($k['nama_designer'] ?: '—') ?>
                        </span>
                    </td>
                    <td style="text-align:right" onclick="event.stopPropagation()">
                        <button class="cpb cpb-pri" onclick="bukaDetail(<?= $k['id'] ?>)" style="padding:6px 14px; font-size:12px; font-weight:600; border-radius:8px; white-space:nowrap; background:#16a34a; color:#fff;">
                            Publish & Link
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
                </div>
                <div>
                    <textarea id="inCaptionText" class="cp-inp" rows="4" placeholder="Tulis caption manual..." style="width:100%; font-size:13.5px; padding:10px 12px; border-radius:8px; line-height:1.5; resize:vertical; min-height:90px;"></textarea>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
                        <div id="captionStatus" style="font-size:12px; color:#16a34a; font-weight:500; display:none;"></div>
                        <button type="button" class="cpb cpb-pri" id="btnSimpanCaption" onclick="simpanCaptionManual()" style="padding:7px 16px; font-size:12px; font-weight:600; border-radius:8px; margin-left:auto;">
                            Simpan Caption
                        </button>
                    </div>
                </div>
                <div id="detCaption" style="font-size:14px; color:var(--cp-muted); white-space:pre-wrap; display:none;">(Belum ada caption)</div>
            </div>

            <!-- Link Desain Canva Box -->
            <div class="cp-design-box" id="designBox" style="margin-top:16px; border:1px solid var(--cp-border); border-radius:12px; padding:16px; background:var(--cp-white);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#00c4cc" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                        Link Desain Canva / Figma
                    </div>
                    <a id="btnBukaCanva" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600; border-radius:8px; opacity:0.45; cursor:not-allowed; filter:grayscale(0.7);" title="Link desain belum diisi" onclick="toast('Link desain belum diisi. Paste link Canva/Figma terlebih dahulu lalu klik Simpan Link.', 'error'); return false;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Buka Canva ↗
                    </a>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="url" id="inDesignUrl" class="cp-inp" placeholder="Paste link Canva/Figma di sini (https://canva.com/design/...)" style="flex:1; font-size:13px; padding:8px 12px; border-radius:8px;">
                    <button type="button" class="cpb cpb-pri" id="btnSimpanDesignUrl" onclick="simpanDesignUrl()" style="padding:8px 16px; font-size:12px; font-weight:600; white-space:nowrap; border-radius:8px;">
                        Simpan Link
                    </button>
                </div>
                <div id="designUrlStatus" style="font-size:12px; color:#16a34a; margin-top:6px; display:none; font-weight:500;"></div>
            </div>

            <!-- Link Gambar / Google Drive Box -->
            <div class="cp-upload-box" id="uploadImageBox" style="margin-top:16px; border:1px solid var(--cp-border); border-radius:12px; padding:16px; background:var(--cp-white);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Link Gambar Konten
                    </div>
                    <a id="btnBukaGambar" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600; border-radius:8px; opacity:0.45; cursor:not-allowed; filter:grayscale(0.7);" title="Link gambar belum diisi" onclick="toast('Link gambar belum diisi. Paste link Google Drive terlebih dahulu lalu klik Simpan Link Gambar.', 'error'); return false;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Preview ↗
                    </a>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <input type="text" id="inImageUrl" class="cp-inp" placeholder="Paste link Google Drive (https://drive.google.com/file/d/.../view) atau URL publik lainnya" style="flex:1; font-size:13px; padding:8px 12px; border-radius:8px;">
                    <button type="button" class="cpb cpb-pri" id="btnSimpanImageUrl" onclick="simpanImageUrl()" style="padding:8px 16px; font-size:12px; font-weight:600; white-space:nowrap; border-radius:8px;">
                        Simpan Link Gambar
                    </button>
                </div>
                <div style="font-size:11px; color:var(--cp-muted); margin-top:6px; display:flex; align-items:flex-start; gap:4px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Pastikan file di Google Drive sudah di-share dengan akses <strong>"Anyone with the link"</strong> agar bisa diakses sistem. Link Drive akan otomatis dikonversi ke format direct-access.
                </div>
                <div id="uploadImageStatus" style="font-size:12px; color:#16a34a; margin-top:8px; display:none; font-weight:500;"></div>
            </div>


            <!-- Publish Otomatis Box (Instagram API) -->
            <div id="autoPublishBox" style="display:none; margin-top:16px; border:1px solid #bbf7d0; border-radius:12px; padding:16px; background:#f0fdf4;">
                <div style="font-weight:600; color:#16a34a; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Publish Otomatis via API
                </div>
                <p style="font-size:12.5px; color:#166534; margin-bottom:12px; line-height:1.4;">Konten ini bertipe Foto/Static Post dan memiliki gambar publik. Anda dapat mempublikasikannya langsung ke Instagram secara otomatis.</p>
                <button type="button" class="cpb" id="btnAutoPublish" onclick="eksekusiPublishOtomatis()" style="width:100%; background:#16a34a; color:#fff; font-weight:600; padding:10px; border-radius:8px; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                    🚀 Publish ke Instagram Sekarang (Otomatis)
                </button>
            </div>

            <!-- Transition Box -->
            <div class="cp-transition-box" id="transitionBox" style="display:none;margin-top:16px">

                <div class="cp-transition-label" style="display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Publish Konten & Input Link URL
                </div>
                <input type="hidden" id="selTransisi" value="">
                <div class="cp-trans-field" style="margin-bottom:12px;">
                    <label style="margin-bottom:6px; display:block;">Pilih Status Tujuan</label>
                    <div class="cp-status-btn-group" id="statusBtnContainer">
                    </div>
                </div>
                <div class="cp-catatan-wrap">
                    <textarea class="cp-inp" id="txCatatan" placeholder="Catatan (opsional)..." rows="2"></textarea>
                </div>
                <div class="cp-catatan-wrap" id="wrapLinkPost" style="display:none">
                    <input type="url" class="cp-inp" id="inLinkPost" placeholder="Link postingan Instagram / TikTok / Facebook (wajib untuk publish)...">
                    <div class="cp-req-note show" id="noteLinkPost" style="display:none">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Link postingan wajib diisi.
                    </div>
                </div>
                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button class="cpb cpb-pri" id="btnEksekusi" onclick="eksekusiTransisi()" style="width:100%; background:#16a34a; color:#fff;">Publish Konten</button>
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
    window.IS_ADMIN_MEDSOS_VIEW = true;

    function renderIdeList() {
        const query = (document.getElementById('searchQuery')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.cp-tr-item');
        rows.forEach(tr => {
            const text = tr.textContent.toLowerCase();
            tr.style.display = (!query || text.includes(query)) ? '' : 'none';
        });
    }
</script>
<script src="/js/content-plan.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
