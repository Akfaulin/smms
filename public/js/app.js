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
 * api(url, method, data) — fetch helper dengan CSRF + JSON
 */
async function api(url, method = 'GET', data = null) {
    const opts = {
        method,
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    };
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
});
