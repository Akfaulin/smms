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
    if (s === 'published') return 'published';
    if (s === 'ditolak')   return 'ditolak';
    if (['ide_diajukan','revisi'].includes(s)) return 'draft';
    return 'acc';
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
    const query    = (document.getElementById('searchQuery')?.value || '').toLowerCase().trim();
    const allKonten = window.ALL_KONTEN || [];

    return allKonten.filter(k => {
        const matchS = !status || k.status === status;
        const matchP = !platform || (k.platforms || []).some(p => String(p.id) === platform);
        const matchQ = !query || (k.judul_konten || '').toLowerCase().includes(query) || (k.deskripsi || '').toLowerCase().includes(query);
        return matchS && matchP && matchQ;
    });
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
    if (fTanggal)  fTanggal.value  = tanggal;

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

    const { judul_konten, status, log } = res.data;
    const allKonten = window.ALL_KONTEN || [];
    const k = allKonten.find(x => x.id == id) || {};

    // Header modal
    if (detJudul) detJudul.textContent = judul_konten;
    if (detMeta)  detMeta.innerHTML = `<span class="cp-badge ${statusClass(status)}">${STATUS_LABEL[status]||status}</span> &nbsp; ID #${id}`;

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
    if (descEl) {
        if (k.deskripsi) {
            descEl.style.display = '';
            descEl.textContent = k.deskripsi;
        } else {
            descEl.style.display = 'none';
        }
    }

    // AI Caption Logic
    const btnAi = document.getElementById('btnAiCaption');
    const capEl = document.getElementById('detCaption');
    if (capEl && btnAi) {
        if (k.caption) {
            capEl.innerHTML = escHtml(k.caption).replace(/\n/g, '<br>');
            btnAi.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI';
        } else {
            capEl.textContent = '(Belum ada caption)';
            btnAi.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';
        }

        const roleNow = window.ROLE || '';
        if (status === 'in_design' && (roleNow === 'content_creator' || OVERRIDE_ROLES.includes(roleNow))) {
            btnAi.style.display = 'block';
        } else {
            btnAi.style.display = 'none';
        }
    }

    // Smart Canva Link Logic
    const inDesignUrl = document.getElementById('inDesignUrl');
    const btnBukaCanva = document.getElementById('btnBukaCanva');
    const statusDesignUrl = document.getElementById('designUrlStatus');

    if (inDesignUrl) {
        inDesignUrl.value = k.design_url || '';
    }

    if (btnBukaCanva) {
        if (k.design_url && k.design_url.trim() !== '') {
            // Link tersimpan — aktifkan tombol dan arahkan ke link spesifik
            btnBukaCanva.href = k.design_url;
            btnBukaCanva.removeAttribute('onclick');
            btnBukaCanva.title = 'Buka desain Canva tersimpan';
            btnBukaCanva.style.opacity = '';
            btnBukaCanva.style.cursor = '';
            btnBukaCanva.style.pointerEvents = '';
            btnBukaCanva.style.filter = '';
        } else {
            // Belum ada link — disable tombol dan tampilkan toast saat diklik
            btnBukaCanva.removeAttribute('href');
            btnBukaCanva.setAttribute('onclick', "toast('Link desain belum diisi. Paste link Canva/Figma terlebih dahulu lalu klik Simpan Link.', 'error'); return false;");
            btnBukaCanva.title = 'Link desain belum diisi';
            btnBukaCanva.style.opacity = '0.45';
            btnBukaCanva.style.cursor = 'not-allowed';
            btnBukaCanva.style.pointerEvents = 'auto';
            btnBukaCanva.style.filter = 'grayscale(0.7)';
        }
    }

    if (statusDesignUrl) {
        statusDesignUrl.style.display = 'none';
        statusDesignUrl.textContent = '';
    }

    // Media Image Preview Handling
    const imgPreview = document.getElementById('imgPreview');
    const imgPreviewEmpty = document.getElementById('imgPreviewEmpty');
    const statusUpload = document.getElementById('uploadImageStatus');
    const fileInput = document.getElementById('inImageFile');

    if (fileInput) fileInput.value = '';

    if (imgPreview && imgPreviewEmpty) {
        if (k.image_url) {
            imgPreview.src = k.image_url;
            imgPreview.style.display = 'block';
            imgPreviewEmpty.style.display = 'none';
        } else {
            imgPreview.src = '';
            imgPreview.style.display = 'none';
            imgPreviewEmpty.style.display = 'block';
        }
    }
    if (statusUpload) {
        statusUpload.style.display = 'none';
        statusUpload.textContent = '';
    }

    // Transition box
    const tersedia = getTransisiTersedia(status);
    if (txBox) {
        if (tersedia.length > 0) {
            txBox.style.display = '';
            const selInput = document.getElementById('selTransisi');
            if (selInput) selInput.value = '';

            const btnContainer = document.getElementById('statusBtnContainer');
            if (btnContainer) {
                btnContainer.innerHTML = tersedia.map(s => {
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

    const btn = document.getElementById('btnEksekusi');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin"></span>`;
    }

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
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Ubah Status';
        }
    }
}

// ─── AI Caption Assistant ─────────────────────────────────
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
        const capEl = document.getElementById('detCaption');
        if (capEl) capEl.innerHTML = escHtml(res.data.caption).replace(/\n/g, '<br>');
        if (btn) btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Ulang AI';
    } else {
        toast(res.pesan || 'Gagal generate caption.', 'error');
        if (btn) btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:3px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Bantu Tulis Caption AI';
    }
    if (btn) btn.disabled = false;
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
        }

        if (btnBukaCanva) {
            if (designUrl) {
                // Link baru disimpan — aktifkan tombol
                btnBukaCanva.href = designUrl;
                btnBukaCanva.removeAttribute('onclick');
                btnBukaCanva.title = 'Buka desain Canva tersimpan';
                btnBukaCanva.style.opacity = '';
                btnBukaCanva.style.cursor = '';
                btnBukaCanva.style.pointerEvents = '';
                btnBukaCanva.style.filter = '';
            } else {
                // Link dihapus — disable tombol kembali
                btnBukaCanva.removeAttribute('href');
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

// ─── Upload Media Gambar Publik ─────────────────────────────
async function uploadGambarKonten() {
    if (!activeContent) return;
    const fileInput = document.getElementById('inImageFile');
    const btn = document.getElementById('btnUploadImage');
    const statusEl = document.getElementById('uploadImageStatus');
    const imgPreview = document.getElementById('imgPreview');
    const imgPreviewEmpty = document.getElementById('imgPreviewEmpty');

    const file = fileInput?.files?.[0];
    if (!file) {
        toast('Pilih file gambar terlebih dahulu.', 'error');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Uploading...`;
    }

    const fd = new FormData();
    fd.append('image_file', file);

    const res = await api(`/dashboard/content-plan/upload-image/${activeContent}`, 'POST', fd);

    if (res && res.status === 'sukses') {
        toast('Gambar konten berhasil diunggah!', 'success');

        const imageUrl = res.data.image_url;

        // Update in-memory window.ALL_KONTEN array
        const allKonten = window.ALL_KONTEN || [];
        const item = allKonten.find(x => x.id == activeContent);
        if (item) {
            item.image_url = imageUrl;
        }

        if (imgPreview && imgPreviewEmpty) {
            imgPreview.src = imageUrl;
            imgPreview.style.display = 'block';
            imgPreviewEmpty.style.display = 'none';
        }

        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.textContent = '✓ Media gambar tersimpan (Siap untuk Publishing Meta API)';
            setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 4000);
        }

        if (fileInput) fileInput.value = '';
    } else {
        toast(res ? res.pesan : 'Gagal mengunggah gambar.', 'error');
    }

    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Upload Gambar';
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
});

// ─── AI Idea Generator ────────────────────────────────────
function renderMarkdown(txt) {
    if (!txt) return '';

    let html = txt
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    // Headings (### Header)
    html = html.replace(/^### (.*$)/gim, '<h4 style="font-weight:700;font-size:15px;margin:12px 0 6px 0;color:#6d28d9;border-bottom:1px solid #e9d5ff;padding-bottom:4px;">$1</h4>');
    html = html.replace(/^## (.*$)/gim, '<h3 style="font-weight:700;font-size:16px;margin:14px 0 6px 0;color:#6d28d9;">$1</h3>');
    html = html.replace(/^# (.*$)/gim, '<h2 style="font-weight:700;font-size:18px;margin:16px 0 8px 0;color:#6d28d9;">$1</h2>');

    // Horizontal Rule (---)
    html = html.replace(/^---$/gim, '<hr style="border:0;border-top:1px solid #cbd5e1;margin:12px 0;">');

    // Bold (**text** or __text__)
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong style="font-weight:700;color:#0f172a;">$1</strong>');
    html = html.replace(/__(.*?)__/g, '<strong style="font-weight:700;color:#0f172a;">$1</strong>');

    // Italic (*text*)
    html = html.replace(/\*([^\*]+)\*/g, '<em style="font-style:italic;color:#475569;">$1</em>');

    // Line breaks
    html = html.replace(/\n/g, '<br>');

    // Clean extra breaks after block elements
    html = html.replace(/(<\/h[234]>|<hr>)\s*<br>/g, '$1');

    return html;
}

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
        btn.textContent = '✨ Generate Ide Konten';
    }
}

