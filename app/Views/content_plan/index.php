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
// ─── Data dari PHP ────────────────────────────────────────
const ALL_KONTEN = <?= json_encode(array_values($konten)) ?>;
const ROLE       = <?= json_encode(session('kode_role')) ?>;
const USER_ID    = <?= json_encode(session('user_id')) ?>;

// ─── State ────────────────────────────────────────────────
let calYear  = new Date().getFullYear();
let calMonth = new Date().getMonth(); // 0-indexed
let activeView    = 'cal';
let activeContent = null;

// ─── Konstanta ────────────────────────────────────────────
const STATUS_LABEL = {
    ide_diajukan:  'Ide Diajukan',
    acc_ide:       'Acc Ide',
    in_design:     'In Design',
    review_design: 'Review Design',
    revisi:        'Revisi',
    acc_final:     'Acc Final',
    published:     'Published',
    ditolak:       'Ditolak',
};

// Class pill/badge berdasarkan status
function statusClass(s) {
    if (s === 'published') return 'published';
    if (s === 'ditolak')   return 'ditolak';
    if (['ide_diajukan','revisi'].includes(s)) return 'draft';
    return 'acc';
}

// Transisi valid per status & role (mirror dari TransisiKonten::TRANSISI_VALID)
const TRANSISI_MAP = {
    ide_diajukan: { manager: ['acc_ide','revisi','ditolak'] },
    revisi:        { content_creator: ['ide_diajukan'] },
    acc_ide:       { content_creator: ['in_design'] },
    in_design:     { content_creator: ['review_design'] },
    review_design: { manager: ['acc_final','revisi'] },
    acc_final:     { admin_medsos: ['published'] },
};

const CATATAN_WAJIB = { revisi: true, ditolak: true };
const OVERRIDE_ROLES = ['owner','superadmin'];

function getTransisiTersedia(statusNow) {
    if (OVERRIDE_ROLES.includes(ROLE)) {
        return Object.keys(STATUS_LABEL).filter(s => s !== statusNow);
    }
    return TRANSISI_MAP[statusNow]?.[ROLE] || [];
}

// ─── Filter Data ──────────────────────────────────────────
function getFilteredData() {
    const status   = document.getElementById('filterStatus').value;
    const platform = document.getElementById('filterPlatform').value;

    return ALL_KONTEN.filter(k => {
        const matchS = !status || k.status === status;
        const matchP = !platform || (k.platforms || []).some(p => String(p.id) === platform);
        return matchS && matchP;
    });
}

// ─── View Switch ──────────────────────────────────────────
function switchView(v) {
    activeView = v;
    document.getElementById('viewCal').style.display  = v === 'cal'  ? '' : 'none';
    document.getElementById('viewList').style.display = v === 'list' ? '' : 'none';
    document.getElementById('togCal').classList.toggle('active', v === 'cal');
    document.getElementById('togList').classList.toggle('active', v === 'list');
    renderView();
}

function renderView() {
    if (activeView === 'cal') buildCalendar();
    else buildList();
    updateLegendCount();
}

function updateLegendCount() {
    const d = getFilteredData();
    document.getElementById('legendCount').textContent = d.length + ' konten ditampilkan';
}

// ─── CALENDAR ─────────────────────────────────────────────
const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'];

function prevMonth() { if (--calMonth < 0) { calMonth = 11; calYear--; } buildCalendar(); }
function nextMonth() { if (++calMonth > 11) { calMonth = 0; calYear++; } buildCalendar(); }

function buildCalendar() {
    document.getElementById('monthLabel').textContent = `${MONTHS_ID[calMonth]} ${calYear}`;

    const data   = getFilteredData();
    const today  = new Date();
    const tY     = today.getFullYear();
    const tM     = today.getMonth();
    const tD     = today.getDate();

    // Hari pertama bulan (0=Sun … 6=Sat) → convert ke Mon-first
    const firstDay = new Date(calYear, calMonth, 1).getDay();
    const offset   = (firstDay === 0) ? 6 : firstDay - 1;
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

    // Kelompokkan konten per tanggal publish
    const byDate = {};
    data.forEach(k => {
        if (!k.tanggal_publish) return;
        const d = k.tanggal_publish.substring(0, 10);
        if (!byDate[d]) byDate[d] = [];
        byDate[d].push(k);
    });

    const body = document.getElementById('calBody');
    let html = '';

    // Empty cells
    for (let i = 0; i < offset; i++) html += `<div class="cp-day empty"></div>`;

    // Day cells
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr   = `${calYear}-${String(calMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isPast    = new Date(calYear, calMonth, d) < new Date(tY, tM, tD);
        const isToday   = (d === tD && calMonth === tM && calYear === tY);
        const isWE      = (() => { const day = new Date(calYear,calMonth,d).getDay(); return day === 0 || day === 6; })();
        const dayKonten = byDate[dateStr] || [];

        let cls = 'cp-day';
        if (isPast)  cls += ' past';
        if (isToday) cls += ' today';
        if (isWE)    cls += ' we';
        if (dayKonten.length > 0) cls += ' has-c';

        const MAX_PILLS = 3;
        const pills = dayKonten.slice(0, MAX_PILLS).map(k => {
            const pillCls = statusClass(k.status) + (isPast ? ' past-pill' : '');
            return `<div class="cp-epill ${pillCls}" onclick="event.stopPropagation();bukaDetail(${k.id})" title="${escHtml(k.judul_konten)}">${escHtml(k.judul_konten)}</div>`;
        }).join('');

        const more = dayKonten.length > MAX_PILLS
            ? `<div class="cp-more">+${dayKonten.length - MAX_PILLS}</div>` : '';

        const hint = isPast
            ? `<div class="cp-lock-hint">🔒</div>`
            : `<div class="cp-add-hint" onclick="event.stopPropagation();bukaFormTambah('${dateStr}')">+ tambah</div>`;

        html += `
        <div class="${cls}" onclick="dayClick('${dateStr}', ${isPast ? 1 : 0})">
            <div class="cp-day-num">${d}</div>
            <div class="cp-day-evs">${pills}${more}</div>
            ${hint}
        </div>`;
    }

    // Fill remaining cells to complete last row
    const totalCells = offset + daysInMonth;
    const remainder  = totalCells % 7;
    if (remainder > 0) {
        for (let i = 0; i < 7 - remainder; i++) html += `<div class="cp-day empty"></div>`;
    }

    body.innerHTML = html;
}

function dayClick(dateStr, isPast) {
    if (!isPast) bukaFormTambah(dateStr);
}

// ─── LIST VIEW ────────────────────────────────────────────
function buildList() {
    const data = getFilteredData();
    const tbody = document.getElementById('listBody');

    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="cp-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/>
            </svg>
            <p>Belum ada konten yang cocok dengan filter.</p>
        </div></td></tr>`;
        return;
    }

    const today = new Date(); today.setHours(0,0,0,0);

    tbody.innerHTML = data.map((k, i) => {
        const isPast = k.tanggal_publish && new Date(k.tanggal_publish) < today;
        const tgl    = k.tanggal_publish ? formatTgl(k.tanggal_publish) : '—';
        const sCls   = statusClass(k.status);
        const plat   = k.platform_str || '—';

        return `<tr class="${isPast ? 'past-row' : ''}" onclick="bukaDetail(${k.id})" style="cursor:pointer">
            <td style="color:var(--cp-muted)">${i+1}</td>
            <td>
                <div style="font-weight:700;font-size:13px">${escHtml(k.judul_konten)}</div>
                ${k.nama_jenis ? `<div style="font-size:11px;color:var(--cp-muted);margin-top:2px">${escHtml(k.nama_jenis)}${k.nama_pillar ? ' · '+escHtml(k.nama_pillar) : ''}</div>` : ''}
            </td>
            <td><span class="cp-badge ${sCls}">${STATUS_LABEL[k.status]||k.status}</span></td>
            <td style="font-size:12px;color:var(--cp-muted)">${escHtml(plat)}</td>
            <td style="font-size:12px">${tgl}</td>
            <td style="font-size:12px;color:var(--cp-muted)">${escHtml(k.nama_pembuat||'—')}</td>
        </tr>`;
    }).join('');
}

// ─── MODAL FORM: Ajukan Ide ───────────────────────────────
function bukaFormTambah(tanggal = '') {
    // Reset form
    document.getElementById('fJudul').value    = '';
    document.getElementById('fDesc').value     = '';
    document.getElementById('fJenis').value    = '';
    document.getElementById('fPillar').value   = '';
    document.getElementById('fDesigner').value = '';
    document.getElementById('fUploader').value = '';
    document.getElementById('fTanggal').value  = tanggal;

    // Uncheck all platforms
    document.querySelectorAll('.plat-cb').forEach(cb => {
        cb.checked = false;
        cb.closest('.cp-plat-lbl').classList.remove('on');
    });

    document.getElementById('formModalTitle').textContent = 'Ajukan Ide Konten';
    bukaModal('backForm');
}

// Toggle platform checkbox style
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.plat-cb').forEach(cb => {
        cb.addEventListener('change', () => {
            cb.closest('.cp-plat-lbl').classList.toggle('on', cb.checked);
        });
    });
});

async function simpanIde() {
    const judul = document.getElementById('fJudul').value.trim();
    if (!judul) { toast('Judul konten wajib diisi.', 'error'); return; }

    const btn = document.getElementById('btnSimpanIde');
    btn.disabled = true;
    btn.innerHTML = `<span class="cp-spin"></span> Menyimpan...`;

    const fd = new FormData();
    fd.append('judul_konten',      judul);
    fd.append('deskripsi',         document.getElementById('fDesc').value);
    fd.append('tanggal_publish',   document.getElementById('fTanggal').value);
    fd.append('jenis_konten_id',   document.getElementById('fJenis').value);
    fd.append('content_type_id',   document.getElementById('fPillar').value);
    fd.append('assigned_designer', document.getElementById('fDesigner').value);
    fd.append('assigned_uploader', document.getElementById('fUploader').value);

    document.querySelectorAll('.plat-cb:checked').forEach(cb => fd.append('platforms[]', cb.value));

    const res = await api('/dashboard/content-plan/store', 'POST', fd);

    if (res.status === 'sukses') {
        toast('Ide berhasil diajukan!', 'success');
        tutupModal('backForm');
        setTimeout(() => location.reload(), 800);
    } else {
        toast(res.pesan || 'Gagal menyimpan.', 'error');
        btn.disabled = false;
        btn.textContent = 'Ajukan Ide';
    }
}

// ─── MODAL DETAIL + TIMELINE ─────────────────────────────
async function bukaDetail(id) {
    activeContent = id;
    bukaModal('backDet');
    gantiTab('info');

    document.getElementById('detJudul').textContent = 'Memuat...';
    document.getElementById('detMeta').innerHTML    = '';
    document.getElementById('transitionBox').style.display = 'none';
    document.getElementById('timelineWrap').innerHTML =
        '<div style="text-align:center;padding:32px;color:var(--cp-muted)">Memuat riwayat...</div>';

    // Fetch log
    const res = await api(`/dashboard/content-plan/${id}/log`);
    if (res.status !== 'sukses') { toast('Gagal memuat data.', 'error'); return; }

    const { judul_konten, status, log } = res.data;

    // Cari data konten dari ALL_KONTEN untuk info lengkap
    const k = ALL_KONTEN.find(x => x.id == id) || {};

    // Header modal
    document.getElementById('detJudul').textContent = judul_konten;
    document.getElementById('detMeta').innerHTML =
        `<span class="cp-badge ${statusClass(status)}">${STATUS_LABEL[status]||status}</span> &nbsp; ID #${id}`;

    // Info tab
    document.getElementById('detStatus').innerHTML   = `<span class="cp-badge ${statusClass(status)}">${STATUS_LABEL[status]||status}</span>`;
    document.getElementById('detTanggal').textContent = k.tanggal_publish ? formatTgl(k.tanggal_publish) : '—';
    document.getElementById('detPembuat').textContent = k.nama_pembuat || '—';
    document.getElementById('detPlatform').textContent = k.platform_str || '—';
    document.getElementById('detJenis').textContent   = k.nama_jenis || '—';
    document.getElementById('detPillar').textContent  = k.nama_pillar || '—';
    document.getElementById('detDesigner').textContent = k.nama_designer || '—';
    document.getElementById('detUploader').textContent = k.nama_uploader || '—';

    const descEl = document.getElementById('detDesc');
    if (k.deskripsi) {
        descEl.style.display = '';
        descEl.textContent = k.deskripsi;
    } else {
        descEl.style.display = 'none';
    }

    // AI Caption Logic
    const btnAi = document.getElementById('btnAiCaption');
    const capEl = document.getElementById('detCaption');
    if (k.caption) {
        capEl.innerHTML = escHtml(k.caption).replace(/\n/g, '<br>');
        btnAi.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI';
    } else {
        capEl.textContent = '(Belum ada caption)';
        btnAi.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';
    }

    // Tampilkan tombol generate caption jika status in_design dan user adalah content_creator
    // Atau jika user adalah owner/superadmin
    if (status === 'in_design' && (ROLE === 'content_creator' || OVERRIDE_ROLES.includes(ROLE))) {
        btnAi.style.display = 'block';
    } else {
        btnAi.style.display = 'none';
    }

    // Transition box
    const tersedia = getTransisiTersedia(status);
    const txBox    = document.getElementById('transitionBox');

    if (tersedia.length > 0) {
        txBox.style.display = '';
        const sel = document.getElementById('selTransisi');
        sel.innerHTML = '<option value="">— Pilih Status —</option>' +
            tersedia.map(s => `<option value="${s}">${STATUS_LABEL[s]||s}</option>`).join('');
        document.getElementById('txCatatan').value = '';
        document.getElementById('inLinkPost').value = '';
        document.getElementById('noteWajib').classList.remove('show');
        document.getElementById('wrapLinkPost').style.display = 'none';
    } else {
        txBox.style.display = 'none';
    }

    // Timeline
    renderTimeline(log);
}

function onTransisiChange() {
    const val   = document.getElementById('selTransisi').value;
    const wajib = CATATAN_WAJIB[val] && !OVERRIDE_ROLES.includes(ROLE);
    document.getElementById('noteWajib').classList.toggle('show', !!wajib);

    const wrapLink = document.getElementById('wrapLinkPost');
    if (val === 'published') {
        wrapLink.style.display = 'block';
    } else {
        wrapLink.style.display = 'none';
    }
}

function renderTimeline(log) {
    const wrap = document.getElementById('timelineWrap');

    if (!log || !log.length) {
        wrap.innerHTML = '<div style="text-align:center;padding:32px;color:var(--cp-muted)">Belum ada riwayat status.</div>';
        return;
    }

    // Sort ascending (dari awal)
    const sorted = [...log].reverse();

    wrap.innerHTML = sorted.map(e => {
        // AI log detected if role is empty/system and contains robot icon
        const isAi = !e.user_id && e.catatan && e.catatan.includes('[🤖 AI');
        const role     = e.kode_role || (isAi ? 'ai' : 'system');
        const inisial  = isAi ? 'AI' : (e.nama_user || 'S').substring(0,2).toUpperCase();
        const namaUser = isAi ? 'AI Assistant' : (e.nama_user || 'Sistem');
        const namaRole = isAi ? 'System Checklist' : (e.nama_role || 'Sistem');
        
        const waktu    = formatWaktu(e.created_at);
        const lLama    = e.status_lama ? (STATUS_LABEL[e.status_lama] || e.status_lama) : null;
        const lBaru    = STATUS_LABEL[e.status_baru] || e.status_baru;

        const transHtml = lLama
            ? `<div class="cp-tl-transition">
                   <span class="cp-badge ${statusClass(e.status_lama)}">${lLama}</span>
                   <span class="cp-tl-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                   <span class="cp-badge ${statusClass(e.status_baru)}">${lBaru}</span>
               </div>`
            : `<div class="cp-tl-transition"><span class="cp-badge ${statusClass(e.status_baru)}">${lBaru}</span></div>`;

        const noteHtml = e.catatan
            ? `<div class="cp-tl-note" ${isAi ? 'style="background:var(--cp-purple-l); border-left:3px solid var(--cp-purple);"' : ''}>${escHtml(e.catatan)}</div>` : '';

        return `
        <div class="cp-tl-item">
            <div class="cp-tl-dot ${isAi ? '' : 'r-'+role}" ${isAi ? 'style="background:var(--cp-purple);color:white;"' : ''} title="${escHtml(namaUser)}">${inisial}</div>
            <div class="cp-tl-body">
                <div class="cp-tl-header">
                    <span class="cp-tl-who">${escHtml(namaUser)}</span>
                    <span class="cp-tl-rolebadge ${isAi ? '' : 'r-'+role}" ${isAi ? 'style="background:var(--cp-purple-l);color:var(--cp-purple);"' : ''}>${escHtml(namaRole)}</span>
                    <span class="cp-tl-time">${waktu}</span>
                </div>
                ${transHtml}
                ${noteHtml}
            </div>
        </div>`;
    }).join('');
}

async function eksekusiTransisi() {
    const statusBaru = document.getElementById('selTransisi').value;
    const catatan    = document.getElementById('txCatatan').value.trim();
    const linkPost   = document.getElementById('inLinkPost').value.trim();

    if (!statusBaru) { toast('Pilih status tujuan terlebih dahulu.', 'error'); return; }

    // Validasi catatan wajib (client-side)
    if (CATATAN_WAJIB[statusBaru] && !catatan) {
        toast('Catatan wajib diisi untuk transisi ini.', 'error');
        document.getElementById('txCatatan').focus();
        return;
    }

    if (statusBaru === 'published' && !linkPost) {
        toast('Link postingan wajib diisi.', 'error');
        document.getElementById('inLinkPost').focus();
        return;
    }

    const btn = document.getElementById('btnEksekusi');
    btn.disabled = true;
    btn.innerHTML = `<span class="cp-spin"></span>`;

    const payload = {
        status_baru: statusBaru,
        catatan: catatan,
        link_postingan: linkPost
    };

    const res = await api(`/dashboard/content-plan/transition/${activeContent}`, 'POST', payload);

    if (res && res.status === 'sukses') {
        toast(res.pesan, 'success');
        tutupModal('backDet');
        setTimeout(() => location.reload(), 700);
    } else {
        toast(res ? res.pesan : 'Transisi gagal.', 'error');
        btn.disabled = false;
        btn.textContent = 'Ubah Status';
    }
}

// ─── AI Caption Assistant (Tahap 9) ───────────────────────
async function generateAiCaption() {
    if (!activeContent) return;
    const btn = document.getElementById('btnAiCaption');
    btn.disabled = true;
    btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Generating...`;

    // Ambil info platform
    const platformEls = document.querySelectorAll('#detPlatform span');
    let platforms = [];
    platformEls.forEach(el => platforms.push(el.textContent));
    const platform = platforms.length > 0 ? platforms.join(', ') : 'Instagram';

    const fd = new FormData();
    fd.append('platform', platform);

    const res = await api(`/dashboard/content-plan/ai-caption/${activeContent}`, 'POST', fd);

    if (res.status === 'sukses') {
        toast('Caption AI berhasil dibuat!', 'success');
        document.getElementById('detCaption').innerHTML = escHtml(res.data.caption).replace(/\n/g, '<br>');
        btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI';
    } else {
        toast(res.pesan || 'Gagal generate caption.', 'error');
        btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';
    }
    btn.disabled = false;
}

// ─── Modal Helpers ────────────────────────────────────────
function bukaModal(id) {
    document.getElementById(id).classList.add('show');
}

function tutupModal(id) {
    document.getElementById(id).classList.remove('show');
    if (id === 'backDet') activeContent = null;
}

function gantiTab(tab) {
    ['info','timeline'].forEach(t => {
        document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1)).classList.toggle('active', t === tab);
        document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1)).classList.toggle('active', t === tab);
    });
}

// Close modals on backdrop click
['backForm','backDet'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) tutupModal(id);
    });
});

// Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['backDet','backForm'].forEach(tutupModal);
});

// ─── Format Helpers ───────────────────────────────────────
function formatTgl(str) {
    if (!str) return '—';
    const [y,m,d] = str.substring(0,10).split('-');
    const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${parseInt(d)} ${bulan[parseInt(m)-1]} ${y}`;
}

function formatWaktu(dt) {
    if (!dt) return '';
    const d = new Date(dt.replace(' ','T'));
    if (isNaN(d)) return dt;
    const p = n => String(n).padStart(2,'0');
    return `${p(d.getDate())}/${p(d.getMonth()+1)}/${d.getFullYear()} ${p(d.getHours())}:${p(d.getMinutes())}`;
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    buildCalendar();
    buildList();
    updateLegendCount();
});
</script>
<?= $this->endSection() ?>
