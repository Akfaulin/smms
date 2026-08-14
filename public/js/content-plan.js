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

    const { judul_konten, status, log, konten } = res.data;
    const allKonten = window.ALL_KONTEN || [];
    const k = konten || allKonten.find(x => x.id == id) || {};


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

    const isReadOnlyMode = window.IS_ADMIN_MEDSOS_VIEW ||
                           window.ROLE === 'admin_medsos' ||
                           location.pathname.includes('jadwal-upload');

    // Caption Logic (Manual Typing & AI Assistance)
    const btnAi = document.getElementById('btnAiCaption');
    const inCaptionText = document.getElementById('inCaptionText');
    const capEl = document.getElementById('detCaption');
    const btnSimpanCap = document.getElementById('btnSimpanCaption');
    const statusCaption = document.getElementById('captionStatus');

    if (isReadOnlyMode) {
        // Read-only inspection mode (Manager & Admin Medsos)
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
        // Editable Mode (Creator / Team)
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
            if (['in_design', 'revisi', 'acc_ide'].includes(status) && (roleNow === 'content_creator' || roleNow === 'creative_team' || OVERRIDE_ROLES.includes(roleNow))) {
                btnAi.style.display = 'block';
            } else {
                btnAi.style.display = 'none';
            }
        }
    }

    if (statusCaption) {
        statusCaption.style.display = 'none';
        statusCaption.textContent = '';
    }

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

    // Setup Buka Gambar / Preview Button & Image URL field
    const btnBukaGambar = document.getElementById('btnBukaGambar');
    const inImageUrl = document.getElementById('inImageUrl');
    const namaJenis = (k.nama_jenis || '').toLowerCase();

    if (inImageUrl) {
        inImageUrl.value = k.image_url || '';
        
        // Update label & placeholder dynamically depending on content type (reels/carousel/image)
        const uploadImageBox = document.getElementById('uploadImageBox');
        if (uploadImageBox) {
            const labelDiv = uploadImageBox.querySelector('div > div');
            if (labelDiv) {
                if (namaJenis === 'reels / video' || namaJenis === 'reels') {
                    labelDiv.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/></svg>
                        Link Video Reels & Cover (Opsional)
                    `;
                    inImageUrl.placeholder = "Baris 1: Paste link Google Drive video (.mp4) / URL video publik\nBaris 2: Paste link Google Drive gambar cover/thumbnail (opsional)";
                } else if (namaJenis === 'carousel') {
                    labelDiv.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Link Gambar Carousel
                    `;
                    inImageUrl.placeholder = "Paste link Google Drive (https://drive.google.com/...) atau URL publik lainnya.\nUntuk Carousel, pisahkan tiap link dengan Enter (baris baru).";
                } else {
                    labelDiv.innerHTML = `
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Link Gambar Konten
                    `;
                    inImageUrl.placeholder = "Paste link Google Drive (https://drive.google.com/file/d/.../view) atau URL publik lainnya";
                }
            }
        }

        if (isReadOnlyMode && inImageUrl.parentElement) {
            inImageUrl.parentElement.style.display = 'none'; // Hide edit input & simpan button for Read-Only roles
            const noteEl = inImageUrl.parentElement.nextElementSibling;
            if (noteEl && noteEl.tagName === 'DIV' && noteEl.textContent.includes('Google Drive')) {
                noteEl.style.display = 'none';
            }
        } else if (inImageUrl.parentElement) {
            inImageUrl.parentElement.style.display = 'flex';
        }
    }

    if (btnBukaGambar) {
        if (k.image_url && k.image_url.trim() !== '') {
            const lines = k.image_url.trim().split('\n').map(l => l.trim()).filter(l => l);
            btnBukaGambar.href = lines[0];
            btnBukaGambar.target = '_blank';
            btnBukaGambar.removeAttribute('onclick');
            btnBukaGambar.title = lines.length > 1 ? 'Buka gambar slide pertama di tab baru' : 'Buka gambar di tab baru';
            btnBukaGambar.style.opacity = '';
            btnBukaGambar.style.cursor = '';
            btnBukaGambar.style.pointerEvents = '';
            btnBukaGambar.style.filter = '';
        } else {
            btnBukaGambar.removeAttribute('href');
            btnBukaGambar.removeAttribute('target');
            btnBukaGambar.setAttribute('onclick', "toast('Link gambar belum diisi oleh Creator.', 'error'); return false;");
            btnBukaGambar.title = 'Link gambar belum diisi';
            btnBukaGambar.style.opacity = '0.45';
            btnBukaGambar.style.cursor = 'not-allowed';
            btnBukaGambar.style.pointerEvents = 'auto';
            btnBukaGambar.style.filter = 'grayscale(0.7)';
        }
    }

    if (statusDesignUrl) {
        statusDesignUrl.style.display = 'none';
        statusDesignUrl.textContent = '';
    }

    // Reset status element and populate image URL field
    const statusUpload = document.getElementById('uploadImageStatus');
    if (inImageUrl) inImageUrl.value = k.image_url || '';

    if (statusUpload) {
        statusUpload.style.display = 'none';
        statusUpload.textContent = '';
    }

    // Transition box
    const tersedia = getTransisiTersedia(status);
    const isAutoPublishable = namaJenis === 'static post' || namaJenis === 'foto' || namaJenis === 'carousel' || namaJenis === 'reels / video' || namaJenis === 'reels';

    // Auto Publish Box handling
    const autoPublishBox = document.getElementById('autoPublishBox');
    if (autoPublishBox) {
        if (isAutoPublishable && k.image_url && status !== 'published') {
            autoPublishBox.style.display = 'block';
        } else {
            autoPublishBox.style.display = 'none';
        }
    }

    if (txBox) {
        // Filter out 'published' from transition group if it's a Foto/Static Post/Carousel content
        let filteredTersedia = tersedia;
        if (isAutoPublishable) {
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
            btn.innerHTML = `🚀 Publish ke Instagram Sekarang (Otomatis)`;
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



// ─── Save Image URL (Google Drive Link or Public URL) ─────────────────
async function simpanImageUrl() {
    if (!activeContent) return;
    const inUrl    = document.getElementById('inImageUrl');
    const btn      = document.getElementById('btnSimpanImageUrl');
    const statusEl = document.getElementById('uploadImageStatus');

    const imageUrl = (inUrl?.value || '').trim();

    // Validasi format video mp4 untuk Reels
    const cachedItem = (window.ALL_KONTEN || []).find(x => x.id == activeContent);
    const namaJenis = (cachedItem?.nama_jenis || '').toLowerCase();
    if (imageUrl && (namaJenis === 'reels / video' || namaJenis === 'reels')) {
        const lines = imageUrl.split('\n').map(l => l.trim()).filter(l => l);
        const videoUrl = lines[0] || '';
        const isMp4 = videoUrl.toLowerCase().endsWith('.mp4') || videoUrl.includes('drive.google.com') || videoUrl.includes('googleusercontent.com');
        if (!isMp4) {
            toast('Peringatan: Link pertama disarankan berakhiran .mp4 atau menggunakan Google Drive.', 'warning');
        }
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="cp-spin" style="width:12px;height:12px;border-width:2px;"></span> Menyimpan...`;
    }

    const fd = new FormData();
    fd.append('image_url', imageUrl);

    const res = await api(`/dashboard/content-plan/image-url/${activeContent}`, 'POST', fd);

    if (res && res.status === 'sukses') {
        const savedUrl = res.data.image_url || '';
        toast('Link gambar berhasil disimpan!', 'success');

        // Update in-memory cache
        const allKonten = window.ALL_KONTEN || [];
        const item = allKonten.find(x => x.id == activeContent);
        if (item) item.image_url = savedUrl;

        if (statusEl) {
            const isDrive = savedUrl.includes('drive.google.com/uc');
            statusEl.style.display = 'block';
            statusEl.textContent = savedUrl
                ? '✓ Link gambar tersimpan' + (isDrive ? ' (Drive link dikonversi ✓)' : '')
                : '✓ Link gambar telah dihapus';
            setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 4000);
        }

        // Refresh tombol auto-publish visibility
        const autoPublishBox = document.getElementById('autoPublishBox');
        if (autoPublishBox) {
            const cachedItem = (window.ALL_KONTEN || []).find(x => x.id == activeContent);
            const namaJenis = (cachedItem?.nama_jenis || '').toLowerCase();
            const isAutoPublishable = namaJenis === 'static post' || namaJenis === 'foto' || namaJenis === 'carousel' || namaJenis === 'reels / video' || namaJenis === 'reels';
            autoPublishBox.style.display = (isAutoPublishable && savedUrl) ? 'block' : 'none';
        }

        // Update Buka Gambar / Preview Button state
        const btnBukaGambar = document.getElementById('btnBukaGambar');
        if (btnBukaGambar) {
            if (savedUrl) {
                const lines = savedUrl.trim().split('\n').map(l => l.trim()).filter(l => l);
                btnBukaGambar.href = lines[0];
                btnBukaGambar.target = '_blank';
                btnBukaGambar.removeAttribute('onclick');
                btnBukaGambar.title = lines.length > 1 ? 'Buka gambar slide pertama di tab baru' : 'Buka gambar di tab baru';
                btnBukaGambar.style.opacity = '';
                btnBukaGambar.style.cursor = '';
                btnBukaGambar.style.pointerEvents = '';
                btnBukaGambar.style.filter = '';
            } else {
                btnBukaGambar.removeAttribute('href');
                btnBukaGambar.removeAttribute('target');
                btnBukaGambar.setAttribute('onclick', "toast('Link gambar belum diisi. Paste link Google Drive terlebih dahulu lalu klik Simpan Link Gambar.', 'error'); return false;");
                btnBukaGambar.title = 'Link gambar belum diisi';
                btnBukaGambar.style.opacity = '0.45';
                btnBukaGambar.style.cursor = 'not-allowed';
                btnBukaGambar.style.pointerEvents = 'auto';
                btnBukaGambar.style.filter = 'grayscale(0.7)';
            }
        }
    } else {
        toast(res ? res.pesan : 'Gagal menyimpan link gambar.', 'error');
    }

    if (btn) {
        btn.disabled = false;
        btn.textContent = 'Simpan Link Gambar';
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
        btn.textContent = '✨ Generate Ide Konten';
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


