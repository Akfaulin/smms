/* ============================================================
   app.js — Global JavaScript Helpers & Utilities
   Sistem Manajemen Media Sosial
   ============================================================ */

let smmsConfirmCallback = null;

/**
 * konfirmasiHapus({ title, message, confirmText, onConfirm })
 * Modal konfirmasi hapus kustom yang profesional
 */
function konfirmasiHapus({ title, message, confirmText, onConfirm }) {
    const titleEl = document.getElementById('smmsConfirmTitle');
    const descEl  = document.getElementById('smmsConfirmDesc');
    const btnText = document.getElementById('smmsConfirmBtnText');
    const modal   = document.getElementById('smmsConfirmBackdrop');

    if (titleEl) titleEl.textContent = title || 'Hapus Data?';
    if (descEl)  descEl.textContent  = message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
    if (btnText) btnText.textContent = confirmText || 'Hapus Data';

    smmsConfirmCallback = onConfirm;
    if (modal) modal.classList.add('show');
}

/**
 * tutupConfirmDialog() — menutup modal konfirmasi
 */
function tutupConfirmDialog() {
    const modal = document.getElementById('smmsConfirmBackdrop');
    if (modal) modal.classList.remove('show');
    smmsConfirmCallback = null;
}

/**
 * toast(msg, type) — tampilkan notifikasi pop-up
 * type: 'success' | 'error' | 'info'
 */
function toast(msg, type = 'success') {
    const icons = {
        success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>',
        error:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        info:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    };
    const container = document.getElementById('cp-toast');
    if (!container) return;

    const el = document.createElement('div');
    el.className = `cp-toast ${type}`;
    el.innerHTML = `<span style="display:inline-flex;align-items:center;">${icons[type]||''}</span> ${msg}`;
    container.appendChild(el);

    setTimeout(() => {
        el.style.transition = 'opacity .3s, transform .3s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(8px)';
        setTimeout(() => el.remove(), 300);
    }, 4000);
}

/**
 * getCsrfToken() — ambil CSRF token dari meta tag di head
 * Token di-inject oleh layout.php: <meta name="csrf-token" content="...">
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

/**
 * api(url, method, data) — fetch helper dengan CSRF + JSON
 */
async function api(url, method = 'GET', data = null) {
    const opts = {
        method,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    };

    // Sertakan CSRF token untuk semua request yang mengubah data
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase())) {
        opts.headers['X-CSRF-TOKEN'] = getCsrfToken();
    }

    if (data) {
        if (data instanceof FormData) {
            opts.body = data;
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(data);
        }
    }
    try {
        const res = await fetch(url, opts);
        const json = await res.json();
        return json;
    } catch (e) {
        return { status: 'gagal', pesan: 'Terjadi kesalahan sistem saat memproses respon.' };
    }
}

// Inisialisasi event listener modal konfirmasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('smmsConfirmBtn')?.addEventListener('click', async () => {
        if (smmsConfirmCallback) {
            const cb = smmsConfirmCallback;
            tutupConfirmDialog();
            await cb();
        }
    });

    document.getElementById('smmsConfirmBackdrop')?.addEventListener('click', e => {
        if (e.target === document.getElementById('smmsConfirmBackdrop')) {
            tutupConfirmDialog();
        }
    });

    // Init notifikasi
    initNotifikasi();
});

// ─── Notifikasi System ────────────────────────────────────────

let notifDropdownOpen = false;

function initNotifikasi() {
    // Poll badge count setiap 60 detik
    updateNotifBadge();
    setInterval(updateNotifBadge, 60000);

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', (e) => {
        if (!document.getElementById('notifWrap')?.contains(e.target)) {
            tutupNotifDropdown();
        }
    });
}

async function updateNotifBadge() {
    try {
        const res = await fetch('/dashboard/notifikasi/unread-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        const count = json.count || 0;
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    } catch (e) { /* silent fail */ }
}

async function toggleNotifDropdown() {
    if (notifDropdownOpen) {
        tutupNotifDropdown();
    } else {
        bukaNotifDropdown();
    }
}

async function bukaNotifDropdown() {
    notifDropdownOpen = true;
    document.getElementById('notifDropdown')?.classList.add('open');

    // Load notifikasi
    const listEl = document.getElementById('notifList');
    if (listEl) listEl.innerHTML = '<div class="notif-empty">Memuat...</div>';

    try {
        const res = await fetch('/dashboard/notifikasi/list', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        renderNotifList(json.data || []);
        // Reset badge setelah dibuka
        const badge = document.getElementById('notifBadge');
        if (badge) badge.style.display = 'none';
    } catch (e) {
        if (listEl) listEl.innerHTML = '<div class="notif-empty">Gagal memuat notifikasi.</div>';
    }
}

function tutupNotifDropdown() {
    notifDropdownOpen = false;
    document.getElementById('notifDropdown')?.classList.remove('open');
}

function renderNotifList(notifs) {
    const listEl = document.getElementById('notifList');
    if (!listEl) return;

    if (!notifs.length) {
        listEl.innerHTML = '<div class="notif-empty">Tidak ada notifikasi baru 🎉</div>';
        return;
    }

    listEl.innerHTML = notifs.map(n => {
        const isUnread = !n.is_read || n.is_read == 0;
        const waktu = formatRelativeTime(n.created_at);
        return `
        <a href="${n.url || '/dashboard/content-plan'}" class="notif-item ${isUnread ? 'unread' : ''}">
            <div class="notif-dot"></div>
            <div class="notif-item-body">
                <div class="notif-item-title">${escHtmlNotif(n.judul)}</div>
                <div class="notif-item-msg">${escHtmlNotif(n.pesan)}</div>
                <div class="notif-item-time">${waktu}</div>
            </div>
        </a>`;
    }).join('');
}

async function bacaSemuaNotif() {
    await api('/dashboard/notifikasi/baca-semua', 'POST');
    tutupNotifDropdown();
    const badge = document.getElementById('notifBadge');
    if (badge) badge.style.display = 'none';
}

function formatRelativeTime(dtStr) {
    if (!dtStr) return '';
    const dt   = new Date(dtStr.replace(' ', 'T'));
    const diff = Math.floor((Date.now() - dt.getTime()) / 1000);
    if (diff < 60)      return 'Baru saja';
    if (diff < 3600)    return Math.floor(diff / 60) + ' menit lalu';
    if (diff < 86400)   return Math.floor(diff / 3600) + ' jam lalu';
    if (diff < 604800)  return Math.floor(diff / 86400) + ' hari lalu';
    return dt.toLocaleDateString('id-ID');
}

function escHtmlNotif(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
