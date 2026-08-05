<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul ?? 'Dashboard') ?> — SMMS</title>
    <meta name="description" content="Sistem Manajemen Media Sosial Internal Tim">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
    <?= $this->renderSection('head_css') ?>
</head>
<body>

<!-- Sidebar Partial -->
<?= $this->include('partials/sidebar') ?>

<!-- Main Container -->
<main class="main">
    <!-- Topbar Partial -->
    <?= $this->include('partials/topbar') ?>

    <div class="page-content">
        <?= $this->renderSection('content') ?>
    </div>
</main>

<!-- Custom Confirm Modal Partial -->
<?= $this->include('partials/modal_confirm') ?>

<!-- Toast container -->
<div id="cp-toast"></div>

<script>
// ─── Global Helpers ──────────────────────────────────────

let smmsConfirmCallback = null;

/**
 * konfirmasiHapus({ title, message, confirmText, onConfirm })
 * Modal konfirmasi hapus kustom yang profesional
 */
function konfirmasiHapus({ title, message, confirmText, onConfirm }) {
    document.getElementById('smmsConfirmTitle').textContent = title || 'Hapus Data?';
    document.getElementById('smmsConfirmDesc').textContent = message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('smmsConfirmBtnText').textContent = confirmText || 'Hapus Data';
    smmsConfirmCallback = onConfirm;
    document.getElementById('smmsConfirmBackdrop').classList.add('show');
}

function tutupConfirmDialog() {
    document.getElementById('smmsConfirmBackdrop').classList.remove('show');
    smmsConfirmCallback = null;
}

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

/**
 * toast(msg, type) — tampilkan notifikasi
 * type: 'success' | 'error' | 'info'
 */
function toast(msg, type = 'success') {
    const icons = {
        success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>',
        error:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        info:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    };
    const el = document.createElement('div');
    el.className = `cp-toast ${type}`;
    el.innerHTML = `<span style="display:inline-flex;align-items:center;">${icons[type]||''}</span> ${msg}`;
    document.getElementById('cp-toast').appendChild(el);
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

// Flash messages dari session
<?php if (session('sukses')): ?>
document.addEventListener('DOMContentLoaded', () => toast(<?= json_encode(session('sukses')) ?>, 'success'));
<?php endif; ?>
<?php if (session('error')): ?>
document.addEventListener('DOMContentLoaded', () => toast(<?= json_encode(session('error')) ?>, 'error'));
<?php endif; ?>
</script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
