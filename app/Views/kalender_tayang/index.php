<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<link rel="stylesheet" href="/css/kalender-tayang.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<div class="ik-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color:#4f46e5; box-shadow:0 2px 8px rgba(79, 70, 229, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Kalender Tayang Medsos</h1>
            <p class="ik-header-sub">Visualisasi jadwal publikasi postingan harian, panduan waktu terbaik posting per platform, dan eksekusi tayang.</p>
        </div>
    </div>
</div>

<!-- ── Rekomendasi Waktu Posting Terbaik (Best Hours Guide) ─── -->
<div style="margin-bottom:12px;">
    <h2 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Waktu Posting Terbaik Per Platform (Best Hours)</h2>
    <p style="font-size:12.5px; color:#64748b; margin:0;">Gunakan acuan jam tayang ini untuk mengoptimalkan jangkauan organik postingan.</p>
</div>

<div class="kt-best-grid">
    <?php foreach ($bestPostingTimes as $b): ?>
    <div class="kt-best-card">
        <div style="font-size:11.5px; font-weight:800; color:#4f46e5; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;"><?= esc($b['platform']) ?></div>
        <div style="font-size:15px; font-weight:800; color:#0f172a; margin-bottom:6px;"><?= esc($b['jam']) ?></div>
        <div style="font-size:12px; color:#64748b; line-height:1.4;"><?= esc($b['catatan']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Visual Month Grid Calendar Card ──────────────────────── -->
<div class="cp-card" style="padding:20px; width:100%; box-sizing:border-box;">

    <!-- Calendar Navigation Header -->
    <div class="kt-cal-header">
        <div style="display:flex; align-items:center; gap:12px;">
            <h2 id="calMonthTitle" style="font-size:17px; font-weight:800; color:#0f172a; margin:0;">Agustus 2026</h2>
            <span style="font-size:12px; font-weight:600; background:#f1f5f9; color:#475569; padding:3px 10px; border-radius:12px;">Total Post: <?= count($events) ?></span>
        </div>
        <div style="display:flex; gap:8px;">
            <button class="cpb cpb-sec" onclick="gantiBulan(-1)" style="padding:6px 14px; font-size:12px; font-weight:600;">
                &larr; Bulan Lalu
            </button>
            <button class="cpb cpb-sec" onclick="gantiBulan(1)" style="padding:6px 14px; font-size:12px; font-weight:600;">
                Bulan Depan &rarr;
            </button>
        </div>
    </div>

    <!-- Day Names Header -->
    <div class="kt-cal-grid" style="margin-bottom:6px;">
        <div class="kt-cal-day-head">Senin</div>
        <div class="kt-cal-day-head">Selasa</div>
        <div class="kt-cal-day-head">Rabu</div>
        <div class="kt-cal-day-head">Kamis</div>
        <div class="kt-cal-day-head">Jumat</div>
        <div class="kt-cal-day-head">Sabtu</div>
        <div class="kt-cal-day-head">Minggu</div>
    </div>

    <!-- Month Days Grid Container -->
    <div class="kt-cal-grid" id="calGridBody">
        <!-- Rendered dynamically by JS -->
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

            <!-- Transition Box -->
            <div class="cp-transition-box" id="transitionBox" style="display:none;margin-top:16px">
                <div class="cp-transition-label" style="display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Publish Konten & Input Link URL
                </div>
                <input type="hidden" id="selTransisi" value="">
                <div class="cp-trans-field" style="margin-bottom:12px;">
                    <label style="margin-bottom:6px; display:block;">Pilih Status Tujuan</label>
                    <div class="cp-status-btn-group" id="statusBtnContainer"></div>
                </div>
                <div class="cp-catatan-wrap">
                    <textarea class="cp-inp" id="txCatatan" placeholder="Catatan (opsional)..." rows="2"></textarea>
                </div>
                <div class="cp-catatan-wrap" id="wrapLinkPost" style="display:none">
                    <input type="url" class="cp-inp" id="inLinkPost" placeholder="Link postingan Instagram / TikTok / Facebook...">
                </div>
                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button class="cpb cpb-pri" id="btnEksekusi" onclick="eksekusiTransisi()" style="width:100%; background:#16a34a; color:#fff;">Simpan Status</button>
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
    window.EVENTS     = <?= json_encode($events) ?>;
    window.ROLE       = <?= json_encode(session('kode_role')) ?>;
    window.USER_ID    = <?= json_encode(session('user_id')) ?>;

    let currentYear = 2026;
    let currentMonth = 7; // 0-indexed (7 = Agustus)

    const namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        currentYear = now.getFullYear();
        currentMonth = now.getMonth();
        renderCalendar();
    });

    function gantiBulan(dir) {
        currentMonth += dir;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        } else if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    }

    function renderCalendar() {
        document.getElementById('calMonthTitle').textContent = namaBulan[currentMonth] + ' ' + currentYear;
        const grid = document.getElementById('calGridBody');
        grid.innerHTML = '';

        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const startOffset = (firstDay === 0) ? 6 : firstDay - 1;
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        const todayStr = (new Date()).toISOString().split('T')[0];

        // Empty padding cells for previous month
        for (let i = 0; i < startOffset; i++) {
            const cell = document.createElement('div');
            cell.className = 'kt-cal-cell other-month';
            grid.appendChild(cell);
        }

        // Active month day cells
        for (let d = 1; d <= daysInMonth; d++) {
            const mStr = String(currentMonth + 1).padStart(2, '0');
            const dStr = String(d).padStart(2, '0');
            const dateKey = `${currentYear}-${mStr}-${dStr}`;

            const cell = document.createElement('div');
            cell.className = 'kt-cal-cell' + (dateKey === todayStr ? ' today' : '');

            const dateNum = document.createElement('div');
            dateNum.className = 'kt-cal-date-num';
            dateNum.textContent = d;
            cell.appendChild(dateNum);

            // Filter events on this date
            const dayEvents = window.EVENTS.filter(ev => ev.tgl === dateKey);
            dayEvents.forEach(ev => {
                const pill = document.createElement('div');
                pill.className = `kt-cal-pill ${ev.status}`;
                pill.textContent = ev.judul;
                pill.title = `${ev.judul} (${ev.platform_str})`;
                pill.onclick = function(e) {
                    e.stopPropagation();
                    bukaDetail(ev.id);
                };
                cell.appendChild(pill);
            });

            grid.appendChild(cell);
        }
    }
</script>
<script src="/js/content-plan.js"></script>
<?= $this->endSection() ?>
