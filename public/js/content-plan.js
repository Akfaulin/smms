/* ============================================================
   content-plan.js — Content Plan Module Logic
   Sistem Manajemen Media Sosial
   ============================================================ */

// ─── State ────────────────────────────────────────────────
let calYear       = new Date().getFullYear();
let calMonth      = new Date().getMonth(); // 0-indexed
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
    return s || 'ide_diajukan';
}

// Transisi valid per status & role
const TRANSISI_MAP = {
    ide_diajukan: { manager: ['acc_ide','revisi','ditolak'] },
    revisi:        { creative_team: ['ide_diajukan'], content_creator: ['ide_diajukan'] },
    acc_ide:       { content_creator: ['in_design'] },
    in_design:     { content_creator: ['review_design'] },
    review_design: { manager: ['acc_final','revisi'] },
    acc_final:     { admin_medsos: ['published'] },
};

const CATATAN_WAJIB = { revisi: true, ditolak: true };
const OVERRIDE_ROLES = ['owner','superadmin'];

function getTransisiTersedia(statusNow) {
    const roleNow = window.ROLE || '';
    if (OVERRIDE_ROLES.includes(roleNow)) {
        return Object.keys(STATUS_LABEL).filter(s => s !== statusNow);
    }
    return TRANSISI_MAP[statusNow]?.[roleNow] || [];
}

// ─── Filter Data ──────────────────────────────────────────
function getFilteredData() {
    const status   = document.getElementById('filterStatus')?.value || '';
    const platform = document.getElementById('filterPlatform')?.value || '';
    const sortBy   = document.getElementById('filterSort')?.value || 'publish_mepet';
    const query    = (document.getElementById('searchQuery')?.value || '').toLowerCase().trim();
    const allKonten = (window.ALL_KONTEN || []).slice();
    const now      = new Date();

    const filtered = allKonten.filter(k => {
        let matchS = true;
        if (status === 'overdue') {
            matchS = k.tanggal_publish && (new Date(k.tanggal_publish) < now) && !['published', 'ditolak'].includes(k.status);
        } else if (status) {
            matchS = (k.status === status);
        }

        const matchP = !platform || (k.platforms || []).some(p => String(p.id) === platform);
        const matchQ = !query || (k.judul_konten || '').toLowerCase().includes(query) || (k.deskripsi || '').toLowerCase().includes(query);
        return matchS && matchP && matchQ;
    });

    filtered.sort((a, b) => {
        const pubA = a.tanggal_publish ? new Date(a.tanggal_publish).getTime() : null;
        const pubB = b.tanggal_publish ? new Date(b.tanggal_publish).getTime() : null;
        const creA = a.created_at ? new Date(a.created_at).getTime() : 0;
        const creB = b.created_at ? new Date(b.created_at).getTime() : 0;

        if (sortBy === 'publish_jauh') {
            if (pubA === null && pubB === null) return creB - creA;
            if (pubA === null) return 1;
            if (pubB === null) return -1;
            return pubB - pubA;
        } else if (sortBy === 'diajukan_terbaru') {
            return creB - creA;
        } else if (sortBy === 'diajukan_terlama') {
            return creA - creB;
        } else {
            // default: publish_mepet
            if (pubA === null && pubB === null) return creB - creA;
            if (pubA === null) return 1;
            if (pubB === null) return -1;
            return pubA - pubB;
        }
    });

    return filtered;
}

// ─── View Switch ──────────────────────────────────────────
function switchView(v) {
    activeView = v;
    const vCal = document.getElementById('viewCal');
    const vList = document.getElementById('viewList');
    const tCal = document.getElementById('togCal');
    const tList = document.getElementById('togList');

    if (vCal) vCal.style.display  = v === 'cal'  ? '' : 'none';
    if (vList) vList.style.display = v === 'list' ? '' : 'none';
    if (tCal) tCal.classList.toggle('active', v === 'cal');
    if (tList) tList.classList.toggle('active', v === 'list');
    renderView();
}

function renderView() {
    if (activeView === 'cal') buildCalendar();
    else buildList();
    updateLegendCount();
}

function updateLegendCount() {
    const d = getFilteredData();
    const legendEl = document.getElementById('legendCount');
    if (legendEl) legendEl.textContent = d.length + ' konten ditampilkan';
}

// ─── CALENDAR ─────────────────────────────────────────────
const MONTHS_ID = ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'];

function prevMonth() { if (--calMonth < 0) { calMonth = 11; calYear--; } buildCalendar(); }
function nextMonth() { if (++calMonth > 11) { calMonth = 0; calYear++; } buildCalendar(); }

function buildCalendar() {
    const monthLbl = document.getElementById('monthLabel');
    if (monthLbl) monthLbl.textContent = `${MONTHS_ID[calMonth]} ${calYear}`;

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
    if (!body) return;
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

        const MAX_PILLS = 6;
        const pills = dayKonten.slice(0, MAX_PILLS).map(k => {
            const pillCls = statusClass(k.status) + (isPast ? ' past-pill' : '');
            const timeStr = (k.tanggal_publish && k.tanggal_publish.length > 10) ? k.tanggal_publish.substring(11, 16) : '';
            const timePrefix = (timeStr && timeStr !== '00:00') ? `[${timeStr}] ` : '';
            return `<div class="cp-epill ${pillCls}" onclick="event.stopPropagation();bukaDetail(${k.id})" title="${timePrefix}${escHtml(k.judul_konten)}">${timePrefix}${escHtml(k.judul_konten)}</div>`;
        }).join('');

        const more = dayKonten.length > MAX_PILLS
            ? `<div class="cp-more" onclick="event.stopPropagation();switchView('list')">+${dayKonten.length - MAX_PILLS} lagi</div>` : '';

        const hint = isPast
            ? `<div class="cp-lock-hint" title="Sudah lewat"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>`
            : `<div class="cp-add-hint" onclick="event.stopPropagation();bukaFormTambah('${dateStr}')">+ Tambah</div>`;

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
    if (!tbody) return;

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
    const now = new Date();

    tbody.innerHTML = data.map((k, i) => {
        const sCls   = statusClass(k.status);
        const plat   = k.platform_str || '—';

        let tglHtml = '<span style="color:#94a3b8; font-size:12px; font-style:italic;">Belum dijadwalkan</span>';
        if (k.tanggal_publish) {
            const pubDate = new Date(k.tanggal_publish);
            const pubDay = new Date(pubDate); pubDay.setHours(0,0,0,0);
            const diffDays = Math.round((pubDay - today) / 86400000);
            const isPast = (pubDate < now);
            const tglStr = formatTgl(k.tanggal_publish);
            const timeStr = k.tanggal_publish.length > 10 ? k.tanggal_publish.substring(11, 16) : '';
            const fullTgl = (timeStr && timeStr !== '00:00') ? `${tglStr}, ${timeStr}` : tglStr;

            let badgeHtml = '';
            if (k.status === 'published') {
                badgeHtml = '<span style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Tayang</span>';
            } else if (isPast) {
                badgeHtml = '<span style="background:#fee2e2; color:#dc2626; border:1px solid #fecaca; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Lewat Tenggat</span>';
            } else if (diffDays === 0) {
                badgeHtml = '<span style="background:#fef3c7; color:#d97706; border:1px solid #fde68a; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Hari Ini</span>';
            } else if (diffDays === 1) {
                badgeHtml = '<span style="background:#e0f2fe; color:#0284c7; border:1px solid #bae6fd; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Besok</span>';
            } else {
                badgeHtml = `<span style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px; margin-top:2px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ${diffDays} Hari Lagi</span>`;
            }

            tglHtml = `<div>
                <div style="font-weight:700; font-size:12.5px; color:#0f172a; white-space:nowrap; display:flex; align-items:center; gap:5px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${fullTgl}</div>
                ${badgeHtml}
            </div>`;
        }

        const createdHtml = k.created_at ? `<div style="font-size:11px; color:#64748b; margin-top:4px; white-space:nowrap; display:flex; align-items:center; gap:4px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> Diajukan: ${formatTgl(k.created_at)}</div>` : '';

        return `<tr onclick="bukaDetail(${k.id})" style="cursor:pointer">
            <td style="color:var(--cp-muted); text-align:center;">${i+1}</td>
            <td>
                <div style="font-weight:700;font-size:13px; color:#0f172a;">${escHtml(k.judul_konten)}</div>
                ${k.nama_jenis ? `<div style="font-size:11px;color:var(--cp-muted);margin-top:2px">${escHtml(k.nama_jenis)}${k.nama_pillar ? ' · '+escHtml(k.nama_pillar) : ''}</div>` : ''}
            </td>
            <td><span class="cp-badge ${sCls}">${STATUS_LABEL[k.status]||k.status}</span></td>
            <td style="font-size:12.5px;color:#475569;">${escHtml(plat)}</td>
            <td>
                ${tglHtml}
                ${createdHtml}
            </td>
            <td style="font-size:12px;color:var(--cp-muted)">${escHtml(k.nama_pembuat||'—')}</td>
        </tr>`;
    }).join('');
}

// ─── MODAL FORM: Ajukan Ide ───────────────────────────────
function bukaFormTambah(tanggal = '') {
    const fJudul    = document.getElementById('fJudul');
    const fDesc     = document.getElementById('fDesc');
    const fJenis    = document.getElementById('fJenis');
    const fPillar   = document.getElementById('fPillar');
    const fDesigner = document.getElementById('fDesigner');
    const fUploader = document.getElementById('fUploader');
    const fTanggal  = document.getElementById('fTanggal');

    if (fJudul)    fJudul.value    = '';
    if (fDesc)     fDesc.value     = '';
    if (fJenis)    fJenis.value    = '';
    if (fPillar)   fPillar.value   = '';
    if (fTanggal) {
        if (tanggal && tanggal.length === 10) {
            fTanggal.value = `${tanggal}T09:00`;
        } else if (tanggal) {
            fTanggal.value = tanggal.replace(' ', 'T').substring(0, 16);
        } else {
            fTanggal.value = '';
        }
    }

    // Uncheck all platforms
    document.querySelectorAll('.plat-cb').forEach(cb => {
        cb.checked = false;
        cb.closest('.cp-plat-lbl')?.classList.remove('on');
    });

    const titleEl = document.getElementById('formModalTitle');
    if (titleEl) titleEl.textContent = 'Ajukan Ide Konten';
    bukaModal('backForm');
}

async function simpanIde() {
    const judul = document.getElementById('fJudul').value.trim();
    if (!judul) { toast('Judul konten wajib diisi.', 'error'); return; }

    const btn = document.getElementById('btnSimpanIde');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin"></span> Menyimpan...`;
    }

    const fd = new FormData();
    fd.append('judul_konten',      judul);
    fd.append('deskripsi',         document.getElementById('fDesc').value);
    fd.append('tanggal_publish',   document.getElementById('fTanggal').value);
    fd.append('jenis_konten_id',   document.getElementById('fJenis').value);
    fd.append('content_type_id',   document.getElementById('fPillar').value);

    document.querySelectorAll('.plat-cb:checked').forEach(cb => fd.append('platforms[]', cb.value));

    const res = await api('/dashboard/content-plan/store', 'POST', fd);

    if (res.status === 'sukses') {
        toast('Ide berhasil diajukan!', 'success');
        tutupModal('backForm');
        setTimeout(() => location.reload(), 800);
    } else {
        toast(res.pesan || 'Gagal menyimpan.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Ajukan Ide';
        }
    }
}

// ─── SMART CANVA LINK HELPERS ─────────────────────────────
/**
 * Matrix lookup untuk menentukan URL landing page kategori resmi Canva
 * berdasarkan jenis konten dan platform utama.
 */
function getCanvaCategoryLink(jenisKonten, platformUtama) {
    const jenis = (jenisKonten || '').toLowerCase().trim();
    const platform = (platformUtama || '').toLowerCase().trim();

    // Fallback default ke homepage Canva jika kombinasi tidak dikenal
    const DEFAULT_URL = 'https://www.canva.com';

    // 1. Instagram
    if (platform.includes('instagram')) {
        if (jenis.includes('story') || jenis.includes('reels') || jenis.includes('video')) {
            return 'https://www.canva.com/create/instagram-stories/';
        }
        if (jenis.includes('carousel') || jenis.includes('static')) {
            return 'https://www.canva.com/create/instagram-posts/';
        }
    }

    // 2. TikTok
    if (platform.includes('tiktok')) {
        if (jenis.includes('reels') || jenis.includes('video') || jenis.includes('story')) {
            return 'https://www.canva.com/create/tiktok-videos/';
        }
    }

    // 3. YouTube
    if (platform.includes('youtube')) {
        if (jenis.includes('reels') || jenis.includes('video')) {
            return 'https://www.canva.com/create/youtube-thumbnails/';
        }
    }

    // 4. Facebook
    if (platform.includes('facebook')) {
        if (jenis.includes('story')) {
            return 'https://www.canva.com/create/instagram-stories/';
        }
        if (jenis.includes('static') || jenis.includes('carousel')) {
            return 'https://www.canva.com/create/facebook-posts/';
        }
    }

    return DEFAULT_URL;
}

// ─── MODAL DETAIL + TIMELINE ─────────────────────────────
async function bukaDetail(id) {
    activeContent = id;
    bukaModal('backDet');
    gantiTab('info');

    const detJudul = document.getElementById('detJudul');
    const detMeta  = document.getElementById('detMeta');
    const txBox    = document.getElementById('transitionBox');
    const tlWrap   = document.getElementById('timelineWrap');

    if (detJudul) detJudul.textContent = 'Memuat...';
    if (detMeta)  detMeta.innerHTML    = '';
    if (txBox)    txBox.style.display  = 'none';
    if (tlWrap)   tlWrap.innerHTML     = '<div style="text-align:center;padding:32px;color:var(--cp-muted)">Memuat riwayat...</div>';

    // Fetch log
    const res = await api(`/dashboard/content-plan/${id}/log`);
    if (res.status !== 'sukses') { toast('Gagal memuat data.', 'error'); return; }

    const { judul_konten, status, log, konten } = res.data;
    const allKonten = window.ALL_KONTEN || [];
    const k = konten || allKonten.find(x => x.id == id) || {};


    // Header modal
    if (detJudul) detJudul.textContent = judul_konten;
    if (detMeta)  detMeta.innerHTML = `<span class="cp-badge ${statusClass(status)}">${STATUS_LABEL[status]||status}</span> &nbsp; ID #${id}`;

    // Info tab
    const setSafeHtml = (elId, html) => { const el = document.getElementById(elId); if (el) el.innerHTML = html; };
    const setSafeText = (elId, text) => { const el = document.getElementById(elId); if (el) el.textContent = text; };

    setSafeHtml('detStatus', `<span class="cp-badge ${statusClass(status)}">${STATUS_LABEL[status]||status}</span>`);
    setSafeText('detTanggal', k.tanggal_publish ? formatTgl(k.tanggal_publish) : '—');
    setSafeText('detPembuat', k.nama_pembuat || '—');
    setSafeText('detPlatform', k.platform_str || '—');
    setSafeText('detJenis', k.nama_jenis || '—');
    setSafeText('detPillar', k.nama_pillar || '—');
    setSafeText('detDesigner', k.nama_designer || '—');
    setSafeText('detUploader', k.nama_uploader || '—');

    const descEl = document.getElementById('detDesc');
    if (descEl) {
        if (k.deskripsi) {
            descEl.style.display = '';
            descEl.textContent = k.deskripsi;
        } else {
            descEl.style.display = 'none';
        }
    }

    // Hook for Manager Full Revision Form (Point 5)
    if (typeof populateManagerForm === 'function') {
        populateManagerForm(k);
    }

    const isReadOnlyMode = window.IS_ADMIN_MEDSOS_VIEW ||
                           (window.ROLE === 'admin_medsos' && location.pathname.includes('jadwal-upload'));

    // Caption Logic (Manual Typing & AI Assistance)
    const btnAi = document.getElementById('btnAiCaption');
    const inCaptionText = document.getElementById('inCaptionText');
    const capEl = document.getElementById('detCaption');
    const btnSimpanCap = document.getElementById('btnSimpanCaption');
    const statusCaption = document.getElementById('captionStatus');

    if (isReadOnlyMode) {
        // Read-only inspection mode (Admin Medsos view)
        if (inCaptionText && inCaptionText.parentElement) {
            inCaptionText.parentElement.style.display = 'none';
        }
        if (btnAi) btnAi.style.display = 'none';
        if (capEl) {
            capEl.style.display = 'block';
            if (k.caption) {
                capEl.innerHTML = escHtml(k.caption).replace(/\n/g, '<br>');
                capEl.style.color = 'var(--cp-text)';
                capEl.style.fontSize = '14px';
                capEl.style.lineHeight = '1.6';
            } else {
                capEl.textContent = '(Belum ada caption dari Creator)';
                capEl.style.color = 'var(--cp-muted)';
            }
        }
    } else {
        // Editable Mode (Manager, Creator, Creative Team, Superadmin, Owner)
        if (inCaptionText) {
            if (inCaptionText.parentElement) inCaptionText.parentElement.style.display = 'block';
            inCaptionText.value = k.caption || '';
        }
        if (capEl) capEl.style.display = 'none';

        if (btnAi) {
            btnAi.innerHTML = k.caption
                ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI'
                : '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';

            const roleNow = window.ROLE || '';
            btnAi.style.display = 'block';
        }
    }

    if (statusCaption) {
        statusCaption.style.display = 'none';
        statusCaption.textContent = '';
    }

    // Atur visibilitas Form Hasil Desain (Link Canva & Link Gambar):
    // Saat status masih 'ide_diajukan' (Pengajuan Ide dari Tim Creative), form hasil desain TIDAK DITAMPILKAN
    // karena Tim Creative tidak membuat desain (hanya ide/brief). Form hasil desain baru muncul saat Creator mengerjakan/mengajukan desain.
    const isIdeDiajukan = (status === 'ide_diajukan');
    const wrapMateriDesain = document.getElementById('wrapMateriDesain');
    const designBox = document.getElementById('designBox');
    const uploadImageBox = document.getElementById('uploadImageBox');
    const gridDesainLinks = document.getElementById('gridDesainLinks');

    if (wrapMateriDesain) wrapMateriDesain.style.display = isIdeDiajukan ? 'none' : 'grid';
    if (designBox) designBox.style.display = isIdeDiajukan ? 'none' : 'block';
    if (uploadImageBox) uploadImageBox.style.display = isIdeDiajukan ? 'none' : 'block';
    if (gridDesainLinks) gridDesainLinks.style.display = isIdeDiajukan ? 'none' : 'grid';

    // Smart Canva Link Logic
    const inDesignUrl = document.getElementById('inDesignUrl');
    const btnBukaCanva = document.getElementById('btnBukaCanva');
    const statusDesignUrl = document.getElementById('designUrlStatus');

    if (inDesignUrl) {
        inDesignUrl.value = k.design_url || '';
        if (isReadOnlyMode && inDesignUrl.parentElement) {
            inDesignUrl.parentElement.style.display = 'none'; // Hide edit input & simpan button for Read-Only roles
        } else if (inDesignUrl.parentElement) {
            inDesignUrl.parentElement.style.display = 'flex';
        }
    }

    if (btnBukaCanva) {
        if (k.design_url && k.design_url.trim() !== '') {
            btnBukaCanva.href = k.design_url;
            btnBukaCanva.target = '_blank';
            btnBukaCanva.rel = 'noopener noreferrer';
            btnBukaCanva.removeAttribute('onclick');
            btnBukaCanva.title = 'Buka desain Canva tersimpan';
            btnBukaCanva.style.opacity = '';
            btnBukaCanva.style.cursor = '';
            btnBukaCanva.style.pointerEvents = '';
            btnBukaCanva.style.filter = '';
        } else {
            btnBukaCanva.removeAttribute('href');
            btnBukaCanva.removeAttribute('target');
            btnBukaCanva.removeAttribute('rel');
            btnBukaCanva.setAttribute('onclick', "toast('Link desain belum diisi oleh Creator.', 'error'); return false;");
            btnBukaCanva.title = 'Link desain belum diisi';
            btnBukaCanva.style.opacity = '0.45';
            btnBukaCanva.style.cursor = 'not-allowed';
            btnBukaCanva.style.pointerEvents = 'auto';
            btnBukaCanva.style.filter = 'grayscale(0.7)';
        }
    }

    // Render dynamic media box (Single Image/Video or Carousel Multi-Slide)
    renderMediaBox(k, isReadOnlyMode);

    if (statusDesignUrl) {
        statusDesignUrl.style.display = 'none';
        statusDesignUrl.textContent = '';
    }

    // Transition box
    const tersedia = getTransisiTersedia(status);
    const namaJenis = (k.nama_jenis || '').toLowerCase();
    const isFoto = namaJenis === 'static post' || namaJenis === 'foto';

    // Auto Publish Box handling (Instant Manual Publish)
    const autoPublishBox = document.getElementById('autoPublishBox');
    if (autoPublishBox) {
        if (k.image_url && status !== 'published') {
            autoPublishBox.style.display = 'block';
        } else {
            autoPublishBox.style.display = 'none';
        }
    }

    // Schedule Publish Box handling (Background Scheduled Publishing)
    const scheduleBox = document.getElementById('schedulePublishBox');
    const inScheduledAt = document.getElementById('inScheduledAt');
    const btnBatalJadwal = document.getElementById('btnBatalJadwal');
    const scheduleBadge = document.getElementById('scheduleStatusBadge');
    const scheduleErr = document.getElementById('scheduleErrorNote');

    if (scheduleBox) {
        if (status === 'acc_final' || k.auto_publish_status) {
            scheduleBox.style.display = 'block';

            if (inScheduledAt) {
                if (k.scheduled_at) {
                    inScheduledAt.value = k.scheduled_at.replace(' ', 'T').substring(0, 16);
                } else if (k.tanggal_publish) {
                    const dtStr = k.tanggal_publish.replace(' ', 'T').substring(0, 16);
                    inScheduledAt.value = dtStr.includes('T') ? dtStr : dtStr + 'T10:00';
                } else {
                    inScheduledAt.value = '';
                }
            }

            if (btnBatalJadwal) {
                btnBatalJadwal.style.display = (k.auto_publish_status === 'menunggu') ? 'inline-flex' : 'none';
            }

            if (scheduleBadge) {
                if (k.auto_publish_status === 'menunggu') {
                    scheduleBadge.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; color:#4338ca; background:#e0e7ff; padding:3px 10px; border-radius:12px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu Jadwal Auto-Publish</span>`;
                } else if (k.auto_publish_status === 'diproses') {
                    scheduleBadge.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; color:#b45309; background:#fef3c7; padding:3px 10px; border-radius:12px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg> Sedang Diproses Worker</span>`;
                } else if (k.auto_publish_status === 'berhasil') {
                    scheduleBadge.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; color:#15803d; background:#dcfce7; padding:3px 10px; border-radius:12px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Terjadwal Sukses (Published)</span>`;
                } else if (k.auto_publish_status === 'gagal') {
                    scheduleBadge.innerHTML = `<span style="display:inline-flex; align-items:center; gap:4px; font-size:11.5px; font-weight:600; color:#b91c1c; background:#fee2e2; padding:3px 10px; border-radius:12px;"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Gagal (${k.publish_attempts || 0}/3)</span>`;
                } else {
                    scheduleBadge.innerHTML = '';
                }
            }

            if (scheduleErr) {
                if (k.last_error && k.auto_publish_status === 'gagal') {
                    scheduleErr.style.display = 'block';
                    scheduleErr.innerHTML = `<strong>Penyebab Kegagalan:</strong> ${escHtml(k.last_error)}`;
                } else {
                    scheduleErr.style.display = 'none';
                    scheduleErr.textContent = '';
                }
            }
        } else {
            scheduleBox.style.display = 'none';
        }
    }

    if (txBox) {
        // Filter out 'published' from transition group if it's a Foto/Static Post content
        let filteredTersedia = tersedia;
        if (isFoto) {
            filteredTersedia = tersedia.filter(s => s !== 'published');
        }

        if (filteredTersedia.length > 0) {
            txBox.style.display = '';
            const selInput = document.getElementById('selTransisi');
            if (selInput) selInput.value = '';

            const btnContainer = document.getElementById('statusBtnContainer');
            if (btnContainer) {
                btnContainer.innerHTML = filteredTersedia.map(s => {
                    const label = STATUS_LABEL[s] || s;
                    const icon = getStatusIcon(s);
                    const styleClass = getStatusBtnClass(s);
                    return `<button type="button" class="cp-status-btn ${styleClass}" data-status="${s}" onclick="pilihStatusTransisi('${s}')">
                        ${icon} <span>${escHtml(label)}</span>
                    </button>`;
                }).join('');
            }
            document.getElementById('txCatatan').value = '';
            document.getElementById('inLinkPost').value = '';
            document.getElementById('noteWajib')?.classList.remove('show');
            document.getElementById('wrapLinkPost').style.display = 'none';
        } else {
            txBox.style.display = 'none';
        }
    }


    // Timeline
    renderTimeline(log);
}

function getStatusBtnClass(s) {
    if (['acc_ide', 'acc_final', 'published'].includes(s)) return 'cp-status-btn-success';
    if (s === 'revisi') return 'cp-status-btn-warning';
    if (s === 'ditolak') return 'cp-status-btn-danger';
    if (s === 'in_design') return 'cp-status-btn-info';
    if (s === 'review_design') return 'cp-status-btn-purple';
    return 'cp-status-btn-info';
}

function getStatusIcon(s) {
    if (['acc_ide', 'acc_final'].includes(s)) {
        return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`;
    }
    if (s === 'published') {
        return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>`;
    }
    if (s === 'revisi') {
        return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`;
    }
    if (s === 'ditolak') {
        return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`;
    }
    if (s === 'in_design') {
        return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/></svg>`;
    }
    if (s === 'review_design') {
        return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    }
    return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`;
}

function pilihStatusTransisi(targetStatus) {
    const selInput = document.getElementById('selTransisi');
    if (selInput) {
        selInput.value = targetStatus;
    }

    document.querySelectorAll('.cp-status-btn').forEach(btn => {
        if (btn.getAttribute('data-status') === targetStatus) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    onTransisiChange();
}

function onTransisiChange() {
    const val     = document.getElementById('selTransisi').value;
    const roleNow = window.ROLE || '';
    const wajib   = CATATAN_WAJIB[val] && !OVERRIDE_ROLES.includes(roleNow);
    document.getElementById('noteWajib')?.classList.toggle('show', !!wajib);

    const wrapLink = document.getElementById('wrapLinkPost');
    if (wrapLink) {
        wrapLink.style.display = (val === 'published') ? 'block' : 'none';
    }

    if (wajib) {
        const tx = document.getElementById('txCatatan');
        if (tx && !tx.value.trim()) {
            tx.focus();
        }
    } else if (val === 'published') {
        const inL = document.getElementById('inLinkPost');
        if (inL && !inL.value.trim()) {
            inL.focus();
        }
    }
}

function renderTimeline(log) {
    const wrap = document.getElementById('timelineWrap');
    if (!wrap) return;

    if (!log || !log.length) {
        wrap.innerHTML = '<div style="text-align:center;padding:32px;color:var(--cp-muted)">Belum ada riwayat status.</div>';
        return;
    }

    const sorted = [...log].reverse();

    wrap.innerHTML = sorted.map(e => {
        const isAi = !e.user_id && e.catatan && (e.catatan.includes('[🤖 AI') || e.catatan.includes('[AI') || e.catatan.includes('AI Assistant'));
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

let isSubmittingTransisi = false;

async function eksekusiTransisi() {
    if (isSubmittingTransisi) return;

    const statusBaru = document.getElementById('selTransisi').value;
    const catatan    = document.getElementById('txCatatan').value.trim();
    const linkPost   = document.getElementById('inLinkPost').value.trim();
    const roleNow    = window.ROLE || '';

    if (!statusBaru) { toast('Pilih status tujuan terlebih dahulu.', 'error'); return; }

    if (CATATAN_WAJIB[statusBaru] && !OVERRIDE_ROLES.includes(roleNow) && !catatan) {
        toast('Catatan wajib diisi untuk transisi ini.', 'error');
        document.getElementById('txCatatan').focus();
        return;
    }

    if (statusBaru === 'published' && !linkPost) {
        toast('Link postingan wajib diisi.', 'error');
        document.getElementById('inLinkPost').focus();
        return;
    }

    isSubmittingTransisi = true;
    const btn = document.getElementById('btnEksekusi');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin"></span> Mengubah Status...`;
    }

    try {
        const payload = {
            status_baru: statusBaru,
            catatan: catatan,
            link_postingan: linkPost
        };

        const res = await api(`/dashboard/content-plan/transition/${activeContent}`, 'POST', payload);

        if (res && res.status === 'sukses') {
            toast(res.pesan || 'Status konten berhasil diperbarui!', 'success');
            tutupModal('backDet');
            setTimeout(() => location.reload(), 500);
        } else {
            toast(res ? res.pesan : 'Transisi gagal.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi saat mengubah status.', 'error');
    } finally {
        isSubmittingTransisi = false;
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Ubah Status';
        }
    }
}

async function eksekusiPublishOtomatis() {
    if (!activeContent) return;
    const btn = document.getElementById('btnAutoPublish');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:14px;height:14px;border-width:2.5px;"></span> Publishing...`;
    }

    const res = await api(`/dashboard/jadwal-upload/publish-otomatis/${activeContent}`, 'POST');

    if (res && res.status === 'sukses') {
        let msg = 'Berhasil mempublikasikan konten ke Instagram!';
        if (res.warning) {
            msg += '\n\n' + res.warning;
        }
        toast(msg, 'success');
        tutupModal('backDet');
        setTimeout(() => location.reload(), 1000);
    } else {
        toast(res ? res.pesan : 'Publish otomatis gagal.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Publish ke Instagram Sekarang (Instan)`;
        }
    }
}

// ─── Auto-Publish Scheduling (Background Publishing) ──────

async function simpanJadwalAutoPublish() {
    if (!activeContent) return;
    const inScheduledAt = document.getElementById('inScheduledAt');
    const scheduledVal = (inScheduledAt?.value || '').trim();

    if (!scheduledVal) {
        toast('Silakan pilih tanggal dan jam jadwal publish terlebih dahulu.', 'error');
        if (inScheduledAt) inScheduledAt.focus();
        return;
    }

    const btn = document.getElementById('btnSimpanJadwal');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Menyimpan...`;
    }

    const fd = new FormData();
    fd.append('scheduled_at', scheduledVal);

    const res = await api(`/dashboard/jadwal-upload/jadwalkan/${activeContent}`, 'POST', fd);

    if (res && res.status === 'sukses') {
        toast(res.pesan || 'Jadwal auto-publish berhasil disimpan!', 'success');
        tutupModal('backDet');
        setTimeout(() => location.reload(), 800);
    } else {
        toast(res ? res.pesan : 'Gagal menyimpan jadwal.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Simpan Jadwal`;
        }
    }
}

async function batalkanJadwalAutoPublish() {
    if (!activeContent) return;
    if (!confirm('Apakah Anda yakin ingin membatalkan jadwal auto-publish untuk postingan ini?')) {
        return;
    }

    const btn = document.getElementById('btnBatalJadwal');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Membatalkan...`;
    }

    const res = await api(`/dashboard/jadwal-upload/batal-jadwal/${activeContent}`, 'POST');

    if (res && res.status === 'sukses') {
        toast(res.pesan || 'Jadwal auto-publish berhasil dibatalkan.', 'success');
        tutupModal('backDet');
        setTimeout(() => location.reload(), 800);
    } else {
        toast(res ? res.pesan : 'Gagal membatalkan jadwal.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Batalkan Jadwal`;
        }
    }
}

// ─── AI & Manual Caption Assistant ─────────────────────────

async function generateAiCaption() {
    if (!activeContent) return;
    const btn = document.getElementById('btnAiCaption');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Generating...`;
    }

    const platformEls = document.querySelectorAll('#detPlatform span');
    let platforms = [];
    platformEls.forEach(el => platforms.push(el.textContent));
    const platform = platforms.length > 0 ? platforms.join(', ') : 'Instagram';

    const fd = new FormData();
    fd.append('platform', platform);

    const res = await api(`/dashboard/content-plan/ai-caption/${activeContent}`, 'POST', fd);

    if (res.status === 'sukses') {
        toast('Caption AI berhasil dibuat!', 'success');
        const inCaptionText = document.getElementById('inCaptionText');
        const capEl = document.getElementById('detCaption');
        if (inCaptionText) inCaptionText.value = res.data.caption;
        if (capEl) capEl.innerHTML = escHtml(res.data.caption).replace(/\n/g, '<br>');
        
        // Update local memory state
        const allKonten = window.ALL_KONTEN || [];
        const item = allKonten.find(x => x.id == activeContent);
        if (item) item.caption = res.data.caption;

        if (btn) btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI';
    } else {
        toast(res.pesan || 'Gagal generate caption.', 'error');
        if (btn) btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';
    }
    if (btn) btn.disabled = false;
}

async function simpanCaptionManual() {
    if (!activeContent) return;
    const inCaptionText = document.getElementById('inCaptionText');
    const btn = document.getElementById('btnSimpanCaption');
    const statusEl = document.getElementById('captionStatus');

    const captionVal = (inCaptionText?.value || '').trim();

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    }

    try {
        const csrfToken = (typeof getCsrfToken === 'function' ? getCsrfToken() : (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''));
        const response = await fetch('/dashboard/content-plan/update-caption/' + activeContent, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: 'caption=' + encodeURIComponent(captionVal)
        });

        const res = await response.json();

        if (res.status === 'sukses') {
            toast('Caption berhasil disimpan!', 'success');
            if (statusEl) {
                statusEl.style.display = 'block';
                statusEl.textContent = captionVal ? '✓ Caption tersimpan' : '✓ Caption telah dihapus';
                setTimeout(() => { statusEl.style.display = 'none'; }, 3000);
            }
            // Update local memory state
            const allKonten = window.ALL_KONTEN || [];
            const item = allKonten.find(x => x.id == activeContent);
            if (item) item.caption = captionVal;

            const btnAi = document.getElementById('btnAiCaption');
            if (btnAi) {
                btnAi.innerHTML = captionVal
                    ? '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI'
                    : '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';
            }
        } else {
            toast(res.pesan || 'Gagal menyimpan caption.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi saat menyimpan caption.', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Simpan Caption';
        }
    }
}

// ─── Save Design URL (Canva / Figma Link) ─────────────────
async function simpanDesignUrl() {
    if (!activeContent) return;
    const inUrl = document.getElementById('inDesignUrl');
    const btn = document.getElementById('btnSimpanDesignUrl');
    const btnBukaCanva = document.getElementById('btnBukaCanva');
    const statusEl = document.getElementById('designUrlStatus');

    const designUrl = (inUrl?.value || '').trim();

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Menyimpan...`;
    }

    const fd = new FormData();
    fd.append('design_url', designUrl);

    const res = await api(`/dashboard/content-plan/design-url/${activeContent}`, 'POST', fd);

    if (res && res.status === 'sukses') {
        toast('Link desain berhasil disimpan!', 'success');
        // Update in-memory window.ALL_KONTEN array
        const allKonten = window.ALL_KONTEN || [];
        const item = allKonten.find(x => x.id == activeContent);
        if (item) {
            item.design_url = designUrl;
            if (res.data && res.data.status) item.status = res.data.status;
        }

        if (res.data && res.data.status_changed) {
            const detMeta = document.getElementById('detMeta');
            const detStatus = document.getElementById('detStatus');
            const st = res.data.status;
            if (detMeta) detMeta.innerHTML = `<span class="cp-badge ${statusClass(st)}">${STATUS_LABEL[st]||st}</span> &nbsp; ID #${activeContent}`;
            if (detStatus) detStatus.innerHTML = `<span class="cp-badge ${statusClass(st)}">${STATUS_LABEL[st]||st}</span>`;
            if (typeof renderView === 'function') renderView();
            if (typeof renderIdeList === 'function') renderIdeList();
        }

        if (btnBukaCanva) {
            if (designUrl) {
                // Link baru disimpan — aktifkan tombol
                btnBukaCanva.href = designUrl;
                btnBukaCanva.target = '_blank';
                btnBukaCanva.rel = 'noopener noreferrer';
                btnBukaCanva.removeAttribute('onclick');
                btnBukaCanva.title = 'Buka desain Canva tersimpan';
                btnBukaCanva.style.opacity = '';
                btnBukaCanva.style.cursor = '';
                btnBukaCanva.style.pointerEvents = '';
                btnBukaCanva.style.filter = '';
            } else {
                // Link dihapus — disable tombol kembali
                btnBukaCanva.removeAttribute('href');
                btnBukaCanva.removeAttribute('target');
                btnBukaCanva.removeAttribute('rel');
                btnBukaCanva.setAttribute('onclick', "toast('Link desain belum diisi. Paste link Canva/Figma terlebih dahulu lalu klik Simpan Link.', 'error'); return false;");
                btnBukaCanva.title = 'Link desain belum diisi';
                btnBukaCanva.style.opacity = '0.45';
                btnBukaCanva.style.cursor = 'not-allowed';
                btnBukaCanva.style.pointerEvents = 'auto';
                btnBukaCanva.style.filter = 'grayscale(0.7)';
            }
        }

        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.textContent = designUrl ? '✓ Link desain tersimpan' : '✓ Link desain telah dihapus';
            setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 3000);
        }
    } else {
        toast(res ? res.pesan : 'Gagal menyimpan link desain.', 'error');
    }

    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Simpan Link';
    }
}



// ─── Dynamic Media Box (Single Image/Video & Carousel Multi-Slide) ───────────

function parseSavedMediaUrls(raw) {
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    raw = String(raw).trim();
    if (raw.startsWith('[') && raw.endsWith(']')) {
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) return parsed.map(x => String(x).trim()).filter(Boolean);
        } catch (e) {}
    }
    if (raw.includes('\n')) {
        return raw.split(/[\r\n]+/).map(x => x.trim()).filter(Boolean);
    }
    return raw ? [raw] : [];
}

function bukaPreviewSlide(idx) {
    const row = document.querySelector(`.carousel-slide-row[data-index="${idx}"]`);
    const inp = row ? row.querySelector('.carousel-slide-inp') : null;
    let url = (inp?.value || '').trim();
    if (!url) {
        toast(`Link Slide ${idx + 1} belum diisi. Paste link Google Drive terlebih dahulu.`, 'error');
        return;
    }
    window.open(url, '_blank', 'noopener,noreferrer');
}

function renderMediaBox(k, isReadOnlyMode) {
    const box = document.getElementById('uploadImageBox');
    if (!box) return;

    const namaJenis = (k.nama_jenis || '').toLowerCase();
    const isCarousel = namaJenis.includes('carousel') || namaJenis.includes('slider');
    const urls = parseSavedMediaUrls(k.image_url);

    if (isCarousel) {
        // Render Carousel multi-link UI
        let slides = urls.length > 0 ? urls : [''];

        let slidesHtml = slides.map((url, idx) => `
            <div class="carousel-slide-row" data-index="${idx}" style="display:flex; gap:6px; align-items:center; margin-bottom:6px;">
                <span class="slide-num-label" style="font-size:12px; font-weight:600; color:#4f46e5; min-width:54px;">Slide ${idx + 1}:</span>
                <input type="text" class="cp-inp carousel-slide-inp" value="${escHtml(url)}" placeholder="Paste link Google Drive slide ${idx + 1}..." style="flex:1; font-size:12.5px; padding:7px 10px; border-radius:6px;" oninput="refreshCarouselSlideNumbers()" ${isReadOnlyMode ? 'readonly disabled' : ''}>
                <button type="button" class="cpb cpb-sec btn-preview-slide" onclick="bukaPreviewSlide(${idx})" style="padding:6px 10px; font-size:11px; text-decoration:none; white-space:nowrap; border-radius:6px; display:inline-flex; align-items:center; gap:4px; ${url ? 'background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600;' : 'opacity:0.45; filter:grayscale(0.7); cursor:pointer;'}" title="Preview Slide ${idx + 1}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Preview
                </button>
                ${(!isReadOnlyMode && slides.length > 1) ? `<button type="button" class="cpb btn-del-slide" onclick="hapusSlideCarousel(${idx})" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:5px 8px; font-size:11px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Hapus Slide"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>` : ''}
            </div>
        `).join('');

        box.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px; font-size:13.5px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    Link Media Carousel (Multi-Slide)
                </div>
                <span id="carouselCountBadge" style="font-size:11.5px; font-weight:600; background:#e0e7ff; color:#4338ca; padding:2px 10px; border-radius:12px;">
                    ${slides.filter(Boolean).length} / ${slides.length} Slide
                </span>
            </div>
            <p style="font-size:11.5px; color:var(--cp-muted); margin-bottom:10px; line-height:1.4;">
                Konten Carousel mendukung multi-gambar/video (hingga 10 slide). Masukkan link Google Drive per slide.
            </p>
            <div id="carouselSlidesList">
                ${slidesHtml}
            </div>
            ${!isReadOnlyMode ? `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; flex-wrap:wrap; gap:8px;">
                <button type="button" class="cpb cpb-sec" id="btnTambahSlide" onclick="tambahSlideCarousel()" style="padding:6px 12px; font-size:12px; font-weight:600; border-radius:8px; display:inline-flex; align-items:center; gap:5px; background:#f8fafc; border:1px solid #cbd5e1; color:#334155;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Slide
                </button>
                <button type="button" class="cpb cpb-pri" id="btnSimpanCarousel" onclick="simpanCarouselUrls()" style="padding:7px 16px; font-size:12px; font-weight:600; border-radius:8px; display:inline-flex; align-items:center; gap:5px; margin-left:auto;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Semua Slide
                </button>
            </div>
            ` : ''}
            <div id="uploadImageStatus" style="font-size:12px; color:#16a34a; margin-top:8px; display:none; font-weight:500;"></div>
        `;
    } else {
        // Render Single Media UI (Photo / Video / Reels / Story)
        const singleUrl = urls[0] || '';
        box.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-weight:600; color:var(--cp-text); display:flex; align-items:center; gap:6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Link Media Konten (Foto / Video)
                </div>
                <a id="btnBukaGambar" class="cpb cpb-sec" target="_blank" rel="noopener noreferrer" style="padding:6px 12px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; font-weight:600; border-radius:8px; ${singleUrl ? '' : 'opacity:0.45; cursor:not-allowed; filter:grayscale(0.7);'}" ${singleUrl ? `href="${escHtml(singleUrl)}"` : `onclick="toast('Link media belum diisi.', 'error'); return false;"`}>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Preview
                </a>
            </div>
            ${!isReadOnlyMode ? `
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="text" id="inImageUrl" class="cp-inp" value="${escHtml(singleUrl)}" placeholder="Paste link Google Drive (https://drive.google.com/file/d/.../view) atau URL publik lainnya" style="flex:1; font-size:13px; padding:8px 12px; border-radius:8px;">
                <button type="button" class="cpb cpb-pri" id="btnSimpanImageUrl" onclick="simpanImageUrl()" style="padding:8px 16px; font-size:12px; font-weight:600; white-space:nowrap; border-radius:8px;">
                    Simpan Link
                </button>
            </div>
            <div style="font-size:11px; color:var(--cp-muted); margin-top:6px; display:flex; align-items:flex-start; gap:4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Pastikan file di Google Drive sudah di-share dengan akses <strong>"Anyone with the link"</strong> agar bisa diakses sistem. Link Drive akan otomatis dikonversi ke format direct-access.
            </div>
            ` : (singleUrl ? `<div style="font-size:13px; color:var(--cp-text); background:#f8fafc; padding:8px 12px; border-radius:8px; word-break:break-all;">${escHtml(singleUrl)}</div>` : `<div style="font-size:13px; color:var(--cp-muted); font-style:italic;">(Belum ada file media)</div>`)}
            <div id="uploadImageStatus" style="font-size:12px; color:#16a34a; margin-top:8px; display:none; font-weight:500;"></div>
        `;
    }
}

function tambahSlideCarousel() {
    const container = document.getElementById('carouselSlidesList');
    if (!container) return;
    const currentRows = container.querySelectorAll('.carousel-slide-row');
    if (currentRows.length >= 10) {
        toast('Batas maksimal Carousel adalah 10 slide.', 'warning');
        return;
    }

    const nextIdx = currentRows.length;
    const newRow = document.createElement('div');
    newRow.className = 'carousel-slide-row';
    newRow.dataset.index = nextIdx;
    newRow.style.cssText = 'display:flex; gap:6px; align-items:center; margin-bottom:6px;';
    newRow.innerHTML = `
        <span class="slide-num-label" style="font-size:12px; font-weight:600; color:#4f46e5; min-width:54px;">Slide ${nextIdx + 1}:</span>
        <input type="text" class="cp-inp carousel-slide-inp" placeholder="Paste link Google Drive slide ${nextIdx + 1}..." style="flex:1; font-size:12.5px; padding:7px 10px; border-radius:6px;" oninput="refreshCarouselSlideNumbers()">
        <button type="button" class="cpb cpb-sec btn-preview-slide" onclick="bukaPreviewSlide(${nextIdx})" style="padding:6px 10px; font-size:11px; text-decoration:none; white-space:nowrap; border-radius:6px; display:inline-flex; align-items:center; gap:4px; opacity:0.45; filter:grayscale(0.7); cursor:pointer;" title="Preview Slide ${nextIdx + 1}">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Preview
        </button>
        <button type="button" class="cpb btn-del-slide" onclick="hapusSlideCarousel(${nextIdx})" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:5px 8px; font-size:11px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:center;" title="Hapus Slide"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    `;
    container.appendChild(newRow);

    refreshCarouselSlideNumbers();
}

function hapusSlideCarousel(idx) {
    const container = document.getElementById('carouselSlidesList');
    if (!container) return;
    const rows = container.querySelectorAll('.carousel-slide-row');
    if (rows.length <= 1) {
        const inp = container.querySelector('.carousel-slide-inp');
        if (inp) inp.value = '';
        return;
    }
    if (rows[idx]) {
        rows[idx].remove();
    }
    refreshCarouselSlideNumbers();
}

function refreshCarouselSlideNumbers() {
    const container = document.getElementById('carouselSlidesList');
    if (!container) return;
    const rows = container.querySelectorAll('.carousel-slide-row');
    const badge = document.getElementById('carouselCountBadge');

    let filledCount = 0;
    rows.forEach((row, i) => {
        row.dataset.index = i;
        const span = row.querySelector('.slide-num-label');
        if (span) span.textContent = `Slide ${i + 1}:`;

        const previewBtn = row.querySelector('.btn-preview-slide');
        if (previewBtn) {
            previewBtn.setAttribute('onclick', `bukaPreviewSlide(${i})`);
            previewBtn.title = `Preview Slide ${i + 1}`;
        }

        const delBtn = row.querySelector('.btn-del-slide');
        if (delBtn) {
            delBtn.setAttribute('onclick', `hapusSlideCarousel(${i})`);
            delBtn.style.display = rows.length > 1 ? 'inline-block' : 'none';
        }

        const inp = row.querySelector('.carousel-slide-inp');
        const val = (inp?.value || '').trim();
        if (val) {
            filledCount++;
            if (previewBtn) {
                previewBtn.style.opacity = '1';
                previewBtn.style.filter = 'none';
                previewBtn.style.background = '#f0fdf4';
                previewBtn.style.border = '1px solid #bbf7d0';
                previewBtn.style.color = '#16a34a';
                previewBtn.style.fontWeight = '600';
            }
        } else {
            if (previewBtn) {
                previewBtn.style.opacity = '0.45';
                previewBtn.style.filter = 'grayscale(0.7)';
                previewBtn.style.background = '';
                previewBtn.style.border = '';
                previewBtn.style.color = '';
                previewBtn.style.fontWeight = '';
            }
        }
    });

    if (badge) {
        badge.textContent = `${filledCount} / ${rows.length} Slide`;
    }
}

async function simpanCarouselUrls() {
    if (!activeContent) return;
    const inps = document.querySelectorAll('.carousel-slide-inp');
    const urls = Array.from(inps).map(inp => inp.value.trim()).filter(Boolean);

    const btn = document.getElementById('btnSimpanCarousel');
    const statusEl = document.getElementById('uploadImageStatus');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Menyimpan...`;
    }

    const fd = new FormData();
    urls.forEach(u => fd.append('image_urls[]', u));
    if (urls.length === 0) {
        fd.append('image_url', '');
    }

    const res = await api(`/dashboard/content-plan/image-url/${activeContent}`, 'POST', fd);

    if (res && res.status === 'sukses') {
        const savedUrl = res.data.image_url || '';
        toast(`Berhasil menyimpan ${res.data.slide_count || urls.length} slide Carousel!`, 'success');

        const allKonten = window.ALL_KONTEN || [];
        const item = allKonten.find(x => x.id == activeContent);
        if (item) item.image_url = savedUrl;

        // Refresh auto publish box
        const autoPublishBox = document.getElementById('autoPublishBox');
        if (autoPublishBox) {
            autoPublishBox.style.display = savedUrl ? 'block' : 'none';
        }

        // Re-render media box
        if (item) {
            renderMediaBox(item, window.IS_ADMIN_MEDSOS_VIEW || false);
        }

        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.textContent = `✓ ${urls.length} link slide Carousel tersimpan!`;
            setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 4000);
        }
    } else {
        toast(res ? res.pesan : 'Gagal menyimpan link Carousel.', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Semua Slide`;
        }
    }
}

// ─── Save Single Image URL (Google Drive Link or Public URL) ─────────────────
async function simpanImageUrl() {
    if (!activeContent) return;
    const inUrl    = document.getElementById('inImageUrl');
    const btn      = document.getElementById('btnSimpanImageUrl');
    const statusEl = document.getElementById('uploadImageStatus');

    const imageUrl = (inUrl?.value || '').trim();

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Menyimpan...`;
    }

    const fd = new FormData();
    fd.append('image_url', imageUrl);

    const res = await api(`/dashboard/content-plan/image-url/${activeContent}`, 'POST', fd);

    if (res && res.status === 'sukses') {
        const savedUrl = res.data.image_url || '';
        toast('Link media berhasil disimpan!', 'success');

        // Update in-memory cache
        const allKonten = window.ALL_KONTEN || [];
        const item = allKonten.find(x => x.id == activeContent);
        if (item) {
            item.image_url = savedUrl;
            if (res.data && res.data.status) item.status = res.data.status;
        }

        if (res.data && res.data.status_changed) {
            const detMeta = document.getElementById('detMeta');
            const detStatus = document.getElementById('detStatus');
            const st = res.data.status;
            if (detMeta) detMeta.innerHTML = `<span class="cp-badge ${statusClass(st)}">${STATUS_LABEL[st]||st}</span> &nbsp; ID #${activeContent}`;
            if (detStatus) detStatus.innerHTML = `<span class="cp-badge ${statusClass(st)}">${STATUS_LABEL[st]||st}</span>`;
            if (typeof renderView === 'function') renderView();
            if (typeof renderIdeList === 'function') renderIdeList();
        }

        if (statusEl) {
            const isDrive = savedUrl.includes('drive.google.com/uc');
            statusEl.style.display = 'block';
            statusEl.textContent = savedUrl
                ? '✓ Link media tersimpan' + (isDrive ? ' (Drive link dikonversi ✓)' : '')
                : '✓ Link media telah dihapus';
            setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 4000);
        }

        // Refresh tombol auto-publish visibility
        const autoPublishBox = document.getElementById('autoPublishBox');
        if (autoPublishBox) {
            autoPublishBox.style.display = savedUrl ? 'block' : 'none';
        }

        // Update Buka Gambar / Preview Button state
        const btnBukaGambar = document.getElementById('btnBukaGambar');
        if (btnBukaGambar) {
            if (savedUrl) {
                btnBukaGambar.href = savedUrl;
                btnBukaGambar.target = '_blank';
                btnBukaGambar.removeAttribute('onclick');
                btnBukaGambar.title = 'Buka media di tab baru';
                btnBukaGambar.style.opacity = '';
                btnBukaGambar.style.cursor = '';
                btnBukaGambar.style.pointerEvents = '';
                btnBukaGambar.style.filter = '';
            } else {
                btnBukaGambar.removeAttribute('href');
                btnBukaGambar.removeAttribute('target');
                btnBukaGambar.setAttribute('onclick', "toast('Link media belum diisi. Paste link Google Drive terlebih dahulu lalu klik Simpan Link.', 'error'); return false;");
                btnBukaGambar.title = 'Link media belum diisi';
                btnBukaGambar.style.opacity = '0.45';
                btnBukaGambar.style.cursor = 'not-allowed';
                btnBukaGambar.style.pointerEvents = 'auto';
                btnBukaGambar.style.filter = 'grayscale(0.7)';
            }
        }
    } else {
        toast(res ? res.pesan : 'Gagal menyimpan link media.', 'error');
    }

    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Simpan Link';
    }
}

// ─── Poin 11: Simpan Desain & Caption Sekaligus (Unified Batch Save & Auto-Save) ───
async function simpanDesainDanCaption(autoSubmit = false) {
    if (!activeContent) return;
    const inCaptionText = document.getElementById('inCaptionText');
    const inDesignUrl   = document.getElementById('inDesignUrl');
    const inImageUrl    = document.getElementById('inImageUrl');
    const btnUnified    = document.getElementById('btnSimpanUnified');
    const statusUnified = document.getElementById('unifiedSaveStatus');
    const btnBukaCanva  = document.getElementById('btnBukaCanva');
    const btnBukaGambar = document.getElementById('btnBukaGambar');

    const captionVal   = (inCaptionText?.value || '').trim();
    const designUrlVal = (inDesignUrl?.value || '').trim();
    const imageUrlVal  = (inImageUrl?.value || '').trim();

    if (btnUnified) {
        btnUnified.disabled = true;
        btnUnified.innerHTML = `<span class="cp-spin" style="width:13px;height:13px;border-width:2px;display:inline-block;margin-right:6px;"></span> Menyimpan Desain & Caption...`;
    }

    try {
        const fd = new FormData();
        fd.append('caption', captionVal);
        fd.append('design_url', designUrlVal);
        fd.append('image_url', imageUrlVal);
        if (autoSubmit) fd.append('auto_submit', '1');

        const res = await api('/dashboard/content-plan/update-details/' + activeContent, 'POST', fd);

        if (res && res.status === 'sukses') {
            toast(res.pesan || 'Desain & Caption berhasil disimpan sekaligus!', 'success');

            // Update in-memory state
            const allKonten = window.ALL_KONTEN || [];
            const item = allKonten.find(x => x.id == activeContent);
            if (item) {
                item.caption = captionVal;
                item.design_url = designUrlVal;
                if (res.data && res.data.image_url) item.image_url = res.data.image_url;
                if (res.data && res.data.status) item.status = res.data.status;
            }

            // If status changed to review_design, update modal status header & badge
            if (res.data && res.data.status_changed) {
                const detMeta = document.getElementById('detMeta');
                const detStatus = document.getElementById('detStatus');
                const st = res.data.status;
                if (detMeta) detMeta.innerHTML = `<span class="cp-badge ${statusClass(st)}">${STATUS_LABEL[st]||st}</span> &nbsp; ID #${activeContent}`;
                if (detStatus) detStatus.innerHTML = `<span class="cp-badge ${statusClass(st)}">${STATUS_LABEL[st]||st}</span>`;
                if (typeof renderView === 'function') renderView();
                if (typeof renderIdeList === 'function') renderIdeList();
            }

            // Sync visual buttons
            if (btnBukaCanva) {
                if (designUrlVal) {
                    btnBukaCanva.href = designUrlVal;
                    btnBukaCanva.target = '_blank';
                    btnBukaCanva.rel = 'noopener noreferrer';
                    btnBukaCanva.removeAttribute('onclick');
                    btnBukaCanva.title = 'Buka desain Canva tersimpan';
                    btnBukaCanva.style.opacity = '';
                    btnBukaCanva.style.cursor = '';
                    btnBukaCanva.style.pointerEvents = '';
                    btnBukaCanva.style.filter = '';
                } else {
                    btnBukaCanva.removeAttribute('href');
                    btnBukaCanva.setAttribute('onclick', "toast('Link desain belum diisi. Paste link Canva/Figma terlebih dahulu lalu klik Simpan.', 'error'); return false;");
                    btnBukaCanva.style.opacity = '0.45';
                    btnBukaCanva.style.cursor = 'not-allowed';
                    btnBukaCanva.style.filter = 'grayscale(0.7)';
                }
            }

            if (btnBukaGambar) {
                const finalImgUrl = res.data?.image_url || imageUrlVal;
                if (finalImgUrl) {
                    btnBukaGambar.href = finalImgUrl;
                    btnBukaGambar.target = '_blank';
                    btnBukaGambar.rel = 'noopener noreferrer';
                    btnBukaGambar.removeAttribute('onclick');
                    btnBukaGambar.title = 'Preview gambar konten';
                    btnBukaGambar.style.opacity = '';
                    btnBukaGambar.style.cursor = '';
                    btnBukaGambar.style.filter = '';
                }
            }

            if (statusUnified) {
                statusUnified.style.display = 'inline-flex';
                statusUnified.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg> Seluruh Link Desain & Caption Tersimpan Rapi!';
                setTimeout(() => { if (statusUnified) statusUnified.style.display = 'none'; }, 4000);
            }
        } else {
            toast(res ? res.pesan : 'Gagal menyimpan data.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi saat menyimpan.', 'error');
    } finally {
        if (btnUnified) {
            btnUnified.disabled = false;
            btnUnified.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Desain & Caption Sekaligus`;
        }
    }
}

// ─── Modal Helpers ────────────────────────────────────────
function bukaModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('show');
    if (id === 'backDet') activeContent = null;
}

function gantiTab(tab) {
    ['info','timeline'].forEach(t => {
        const tabEl = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
        const panelEl = document.getElementById('panel' + t.charAt(0).toUpperCase() + t.slice(1));
        if (tabEl) tabEl.classList.toggle('active', t === tab);
        if (panelEl) panelEl.classList.toggle('active', t === tab);
    });
}

// ─── Format Helpers ───────────────────────────────────────
function formatTgl(str) {
    if (!str) return '—';
    const [y,m,d] = str.substring(0,10).split('-');
    const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const timeStr = (str.length > 10) ? str.substring(11, 16) : '';
    const timeSuffix = (timeStr && timeStr !== '00:00') ? `, ${timeStr} WIB` : '';
    return `${parseInt(d)} ${bulan[parseInt(m)-1]} ${y}${timeSuffix}`;
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

// ─── Init Event Listeners & Rendering ─────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Toggle platform checkbox style
    document.querySelectorAll('.plat-cb').forEach(cb => {
        cb.addEventListener('change', () => {
            cb.closest('.cp-plat-lbl')?.classList.toggle('on', cb.checked);
        });
    });

    // Close modals on backdrop click
    ['backForm','backDet','backAiIdeas'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', e => {
            if (e.target.id === id) tutupModal(id);
        });
    });

    // Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') ['backDet','backForm','backAiIdeas'].forEach(tutupModal);
    });

    buildCalendar();
    buildList();
    updateLegendCount();

    // Auto background trigger for scheduled posts (Localhost / Browser fallback)
    if (window.IS_ADMIN_MEDSOS_VIEW || location.pathname.includes('jadwal-upload')) {
        const pingScheduledPosts = async () => {
            try {
                const res = await api('/dashboard/jadwal-upload/check-scheduled', 'POST');
                if (res && res.status === 'sukses' && res.data && (res.data.sukses > 0 || res.data.gagal > 0)) {
                    toast(`Auto-publish scheduler: ${res.data.sukses} postingan berhasil dipublish otomatis!`, res.data.sukses > 0 ? 'success' : 'warning');
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (e) {
                // Silent catch
            }
        };

        // Run on load and every 30 seconds
        setTimeout(pingScheduledPosts, 1000);
        setInterval(pingScheduledPosts, 30000);
    }
});

// ─── AI Idea Generator (Uses global renderMarkdown from app.js) ───────────

async function generateAiIdeas() {
    const topik    = document.getElementById('aiTopik')?.value.trim();
    const platform = document.getElementById('aiPlatform')?.value || 'Instagram';

    if (!topik) { toast('Topik / Produk wajib diisi.', 'error'); return; }

    const btn = document.getElementById('btnGenIde');
    const resBox = document.getElementById('aiIdeasResult');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin"></span> Generating Ide AI...`;
    }

    const fd = new FormData();
    fd.append('topik', topik);
    fd.append('platform', platform);

    const res = await api('/dashboard/content-plan/ai-ideas', 'POST', fd);

    if (res.status === 'sukses') {
        toast('Saran ide AI berhasil dibuat!', 'success');
        if (resBox) {
            resBox.style.display = 'block';
            resBox.innerHTML = renderMarkdown(res.data.hasil);
        }
    } else {
        toast(res.pesan || 'Gagal generate ide.', 'error');
    }

    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Generate Ide Konten';
    }
}

// ─── AI Brief Generator ───────────────────────────────────────────────────

async function generateAiBrief() {
    const judulInput  = document.getElementById('fJudul');
    const descInput   = document.getElementById('fDesc');
    const jenisInput  = document.getElementById('fJenis');
    const pillarInput = document.getElementById('fPillar');
    const btn         = document.getElementById('btnAiBrief');

    const judul  = (judulInput?.value || '').trim();
    const jenis  = (jenisInput && jenisInput.selectedIndex >= 0) ? jenisInput.options[jenisInput.selectedIndex].text : '';
    const pillar = (pillarInput && pillarInput.selectedIndex >= 0) ? pillarInput.options[pillarInput.selectedIndex].text : '';

    if (!judul) {
        toast('Silakan isi Judul Konten terlebih dahulu.', 'error');
        if (judulInput) judulInput.focus();
        return;
    }

    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-1px; margin-right:3px; animation:spin 1s linear infinite;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generating...`;
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
            if (descInput) {
                descInput.value = res.data.brief;
            }
            toast('Brief ide berhasil di-generate AI!', 'success');
        } else {
            toast(res.pesan || 'Gagal membuat brief AI.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi saat membuat brief AI.', 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml || `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-1px; margin-right:3px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Brief AI`;
        }
    }
}

// ─── Initial Render ───────────────────────────────────────
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof renderView === 'function') renderView();
    });
} else {
    if (typeof renderView === 'function') renderView();
}


