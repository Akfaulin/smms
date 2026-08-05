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

$bolehBuat = in_array($kode_role ?? session('kode_role'), ['superadmin','owner','manager','content_creator']);
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
                <a href="?view=my_tasks" class="cp-tog <?= ($viewMode === 'my_tasks') ? 'active' : '' ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:3px"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg> Tugas Saya
                </a>
                <a href="?view=all" class="cp-tog <?= ($viewMode === 'all') ? 'active' : '' ?>">Semua</a>
            </div>

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
            <button class="cpb cpb-pri" onclick="bukaFormTambah()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-2px;margin-right:3px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Ajukan Ide
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="cp-legend">
        <span class="cp-leg-item"><span class="cp-leg-dot published"></span> Published</span>
        <span class="cp-leg-item"><span class="cp-leg-dot acc"></span> Dalam Proses</span>
        <span class="cp-leg-item"><span class="cp-leg-dot draft"></span> Draft / Revisi</span>
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
    <div class="cp-modal modal-md">
        <div class="cp-mh">
            <div>
                <div class="cp-mt" id="formModalTitle">Ajukan Ide Konten</div>
                <div class="cp-ms">Isi form di bawah, status awal: <strong>Ide Diajukan</strong></div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('backForm')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cp-mb">
            <div class="cp-row">
                <div class="cp-field" style="grid-column:1/-1">
                    <label>Judul Konten <span style="color:#dc2626">*</span></label>
                    <input type="text" id="fJudul" class="cp-inp" placeholder="Contoh: Tips Hemat Belanja di Akhir Bulan" maxlength="200">
                </div>
            </div>
            <div class="cp-row">
                <div class="cp-field">
                    <label>Tanggal Publish</label>
                    <input type="date" id="fTanggal" class="cp-inp">
                </div>
                <div class="cp-field">
                    <label>Jenis Konten</label>
                    <select id="fJenis" class="cp-inp">
                        <option value="">— Pilih —</option>
                        <?php foreach ($jenisKonten as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= esc($j['nama_jenis']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="cp-row">
                <div class="cp-field">
                    <label>Content Pillar</label>
                    <select id="fPillar" class="cp-inp">
                        <option value="">— Pilih —</option>
                        <?php foreach ($contentTypes as $ct): ?>
                        <option value="<?= $ct['id'] ?>"><?= esc($ct['nama_type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="cp-row">
                <div class="cp-field">
                    <label>Assigned Designer</label>
                    <select id="fDesigner" class="cp-inp">
                        <option value="">— Pilih Designer —</option>
                        <?php foreach ($designers as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= esc($d['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="cp-field">
                    <label>Assigned Uploader (Admin Medsos)</label>
                    <select id="fUploader" class="cp-inp">
                        <option value="">— Pilih Uploader —</option>
                        <?php foreach ($uploaders as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= esc($u['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="cp-row full">
                <div class="cp-field">
                    <label>Deskripsi / Brief Ide</label>
                    <textarea id="fDesc" class="cp-inp" placeholder="Jelaskan ide konten secara singkat..."></textarea>
                </div>
            </div>
            <div class="cp-row full">
                <div class="cp-field">
                    <label>Platform Tujuan</label>
                    <div class="cp-plat-wrap" id="fPlatforms">
                        <?php foreach ($platforms as $p): ?>
                        <label class="cp-plat-lbl" id="plat-lbl-<?= $p['id'] ?>">
                            <input type="checkbox" class="plat-cb" value="<?= $p['id'] ?>">
                            <?= esc($p['nama_platform']) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="cp-mf">
            <button class="cpb cpb-out" onclick="tutupModal('backForm')">Batal</button>
            <button class="cpb cpb-pri" id="btnSimpanIde" onclick="simpanIde()">Ajukan Ide</button>
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

            <!-- FASE 9.2 AI CAPTION ASSISTANT -->
            <div class="cp-caption-box" id="captionBox" style="margin-top:16px; border:1px solid var(--cp-border); border-radius:12px; padding:16px; background:var(--cp-white);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Caption Konten
                    </div>
                    <button class="cpb cpb-pri" id="btnAiCaption" style="padding:6px 12px; font-size:12px; display:none;" onclick="generateAiCaption()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI
                    </button>
                </div>
                <div id="detCaption" style="font-size:14px; color:var(--cp-muted); white-space:pre-wrap;">(Belum ada caption)</div>
            </div>

            <!-- Transition Box -->
            <div class="cp-transition-box" id="transitionBox" style="display:none;margin-top:16px">
                <div class="cp-transition-label" style="display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Ubah Status Konten
                </div>
                <div class="cp-trans-row">
                    <div class="cp-trans-field">
                        <label>Transisi ke</label>
                        <select class="cp-inp" id="selTransisi" onchange="onTransisiChange()">
                            <option value="">— Pilih Status —</option>
                        </select>
                    </div>
                    <button class="cpb cpb-pri" id="btnEksekusi" onclick="eksekusiTransisi()">Ubah Status</button>
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

<!-- ══════════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT                                                  -->
<!-- ══════════════════════════════════════════════════════════ -->
<?= $this->section('scripts') ?>
<script>
    window.ALL_KONTEN = <?= json_encode(array_values($konten)) ?>;
    window.ROLE       = <?= json_encode(session('kode_role')) ?>;
    window.USER_ID    = <?= json_encode(session('user_id')) ?>;
</script>
<script src="/js/content-plan.js"></script>
<?= $this->endSection() ?>
