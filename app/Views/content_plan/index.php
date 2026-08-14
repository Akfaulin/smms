<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// ── Data dari controller ──────────────────────────────────
$totalKonten  = count($konten);
$totalPublish = count(array_filter($konten, fn($k) => $k['status'] === 'published'));
$totalRevisi  = count(array_filter($konten, fn($k) => $k['status'] === 'revisi'));
$totalAktif   = count(array_filter($konten, fn($k) => !in_array($k['status'], ['published','ditolak'])));

$bolehBuat = in_array($kode_role ?? session('kode_role'), ['superadmin','owner','manager','creative_team']);
$roleNow   = $kode_role ?? session('kode_role');
?>

<!-- ── Stats ─────────────────────────────────────────────── -->
<div class="cp-stats">
    <div class="cp-stat">
        <div class="cp-stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
            <div class="cp-stat-val"><?= $totalKonten ?></div>
            <div class="cp-stat-lbl">Total Konten</div>
        </div>
    </div>
    <div class="cp-stat">
        <div class="cp-stat-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="cp-stat-val"><?= $totalPublish ?></div>
            <div class="cp-stat-lbl">Published</div>
        </div>
    </div>
    <div class="cp-stat">
        <div class="cp-stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </div>
        <div>
            <div class="cp-stat-val"><?= $totalAktif ?></div>
            <div class="cp-stat-lbl">Sedang Berjalan</div>
        </div>
    </div>
    <div class="cp-stat">
        <div class="cp-stat-icon orange">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <div class="cp-stat-val"><?= $totalRevisi ?></div>
            <div class="cp-stat-lbl">Perlu Revisi</div>
        </div>
    </div>
</div>

<!-- ── Main Card ─────────────────────────────────────────── -->
<div class="cp-card">

    <!-- Toolbar -->
    <div class="cp-toolbar">
        <div class="cp-cal-nav">
            <button class="cp-nav-btn" id="btnPrev" onclick="prevMonth()">&#8249;</button>
            <div class="cp-month-lbl" id="monthLabel"></div>
            <button class="cp-nav-btn" id="btnNext" onclick="nextMonth()">&#8250;</button>
        </div>
        <div class="cp-filters">
            <!-- View Mode Toggle -->
            <div class="cp-toggle-wrap" style="margin-right:8px">
                <a href="?view=my_ideas" class="cp-tog <?= ($viewMode === 'my_ideas') ? 'active' : '' ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:3px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> Ide Saya
                </a>
                <a href="?view=my_tasks" class="cp-tog <?= ($viewMode === 'my_tasks') ? 'active' : '' ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:3px"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg> Antrean Tugas
                </a>
                <a href="?view=all" class="cp-tog <?= ($viewMode === 'all') ? 'active' : '' ?>">Semua Konten</a>
            </div>

            <!-- Search Box -->
            <input type="text" id="searchQuery" class="cp-inp" style="width:160px;padding:6px 10px;font-size:12px" placeholder="Cari konten..." oninput="renderView()">

            <select class="cp-sel" id="filterStatus" onchange="renderView()">
                <option value="">Semua Status</option>
                <option value="ide_diajukan">Ide Diajukan</option>
                <option value="acc_ide">Acc Ide</option>
                <option value="in_design">In Design</option>
                <option value="review_design">Review Design</option>
                <option value="revisi">Revisi</option>
                <option value="acc_final">Acc Final</option>
                <option value="published">Published</option>
                <option value="ditolak">Ditolak</option>
            </select>
            <select class="cp-sel" id="filterPlatform" onchange="renderView()">
                <option value="">Semua Platform</option>
                <?php foreach ($platforms as $p): ?>
                <option value="<?= $p['id'] ?>"><?= esc($p['nama_platform']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="cp-toggle-wrap">
                <button class="cp-tog active" id="togCal" onclick="switchView('cal')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:3px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Kalender
                </button>
                <button class="cp-tog" id="togList" onclick="switchView('list')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:3px"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg> List
                </button>
            </div>
            <?php if ($bolehBuat): ?>
            <button class="cpb cpb-sec" onclick="bukaModal('backAiIdeas')" style="background:var(--cp-purple-l);color:var(--cp-purple);border:1px solid var(--cp-purple-l); padding:8px 15px; font-weight:600;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Ide AI
            </button>
            <button class="cpb cpb-pri" onclick="bukaFormTambah()" style="padding:8px 16px; font-weight:600;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px; margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Ajukan Ide Konten
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="cp-legend">
        <span class="cp-leg-item"><span class="cp-leg-dot ide_diajukan"></span> Ide Diajukan</span>
        <span class="cp-leg-item"><span class="cp-leg-dot acc_ide"></span> Acc Ide</span>
        <span class="cp-leg-item"><span class="cp-leg-dot in_design"></span> In Design</span>
        <span class="cp-leg-item"><span class="cp-leg-dot review_design"></span> Review Desain</span>
        <span class="cp-leg-item"><span class="cp-leg-dot revisi"></span> Revisi</span>
        <span class="cp-leg-item"><span class="cp-leg-dot acc_final"></span> Acc Final</span>
        <span class="cp-leg-item"><span class="cp-leg-dot published"></span> Published</span>
        <span class="cp-leg-item"><span class="cp-leg-dot ditolak"></span> Ditolak</span>
        <span style="margin-left:auto;font-size:11px;color:var(--cp-muted)" id="legendCount"></span>
    </div>

    <!-- Calendar View -->
    <div id="viewCal">
        <div class="cp-cal-head">
            <div class="cp-cal-head-cell">Sen</div>
            <div class="cp-cal-head-cell">Sel</div>
            <div class="cp-cal-head-cell">Rab</div>
            <div class="cp-cal-head-cell">Kam</div>
            <div class="cp-cal-head-cell">Jum</div>
            <div class="cp-cal-head-cell we">Sab</div>
            <div class="cp-cal-head-cell we">Min</div>
        </div>
        <div class="cp-cal-body" id="calBody"></div>
    </div>

    <!-- List View -->
    <div id="viewList" style="display:none">
        <div style="overflow-x:auto">
            <table class="cp-ltbl">
                <thead>
                    <tr>
                        <th style="width:32px">#</th>
                        <th>Judul Konten</th>
                        <th>Status</th>
                        <th>Platform</th>
                        <th>Tgl Publish</th>
                        <th>Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody id="listBody"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah / Edit Ide Konten                           -->
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

            <!-- AI & Manual Caption Box -->
            <div class="cp-caption-box" id="captionBox" style="margin-top:16px; border:1px solid var(--cp-border); border-radius:12px; padding:16px; background:var(--cp-white);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                    <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Caption Konten
                    </div>
                    <button type="button" class="cpb cpb-sec" id="btnAiCaption" style="padding:6px 12px; font-size:12px; display:none; background:#f0f9ff; border:1px solid #bae6fd; color:#0284c7; font-weight:600; border-radius:8px;" onclick="generateAiCaption()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI
                    </button>
                </div>
                <div>
                    <textarea id="inCaptionText" class="cp-inp" rows="4" placeholder="Tulis caption manual atau gunakan bantuan AI di atas..." style="width:100%; font-size:13.5px; padding:10px 12px; border-radius:8px; line-height:1.5; resize:vertical; min-height:90px;"></textarea>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
                        <div id="captionStatus" style="font-size:12px; color:#16a34a; font-weight:500; display:none;"></div>
                        <button type="button" class="cpb cpb-pri" id="btnSimpanCaption" onclick="simpanCaptionManual()" style="padding:7px 16px; font-size:12px; font-weight:600; border-radius:8px; margin-left:auto;">
                            Simpan Caption
                        </button>
                    </div>
                </div>
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
                        <!-- Button status akan di-render via JavaScript -->
                    </div>
                </div>
                <div class="cp-catatan-wrap">
                    <textarea class="cp-inp" id="txCatatan" placeholder="Catatan (wajib untuk revisi/tolak)..." rows="2"></textarea>
                    <div class="cp-req-note" id="noteWajib">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Catatan wajib diisi untuk transisi ini.
                    </div>
                </div>
                <!-- Input untuk link postingan (hanya muncul jika transisi ke published) -->
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
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ide Konten
                </button>
            </div>
            <div id="aiIdeasResult" class="ai-ideas-box" style="display:none;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT                                                  -->
<!-- ══════════════════════════════════════════════════════════ -->
<?= $this->section('scripts') ?>
<script>
    window.ALL_KONTEN = <?= json_encode(array_values($konten)) ?>;
    window.ROLE       = <?= json_encode(session('kode_role')) ?>;
    window.USER_ID    = <?= json_encode(session('user_id')) ?>;
</script>
<script src="/js/content-plan.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
