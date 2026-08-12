<!-- ═══════════════════════════════════════════════════════ -->
<!-- TOPBAR COMPONENT                                          -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php
    $bisnisModel = new \App\Models\BisnisModel();
    $bisnisList  = $bisnisModel->getAktif();
    $bisnisSesi  = session('bisnis_aktif_id');

    // Fallback otomatis jika session bisnis_aktif_id belum ada / invalid
    if ((! $bisnisSesi || ! $bisnisModel->getById((int)$bisnisSesi)) && ! empty($bisnisList)) {
        $bisnisSesi  = $bisnisList[0]['id'];
        $bisnisNama  = $bisnisList[0]['nama_bisnis'];
        $bisnisWarna = $bisnisList[0]['warna'];
        session()->set([
            'bisnis_aktif_id'    => $bisnisSesi,
            'bisnis_aktif_nama'  => $bisnisNama,
            'bisnis_aktif_warna' => $bisnisWarna,
        ]);
    } else {
        $bisnisNama  = session('bisnis_aktif_nama') ?? ($bisnisList[0]['nama_bisnis'] ?? 'Pilih Bisnis');
        $bisnisWarna = session('bisnis_aktif_warna') ?? ($bisnisList[0]['warna'] ?? '#6C5CE7');
    }
?>
<div class="topbar">
    <div class="topbar-left">
        <!-- Business Switcher -->
        <div class="business-switcher" id="businessSwitcher">
            <button type="button" class="business-btn" id="businessBtn" onclick="toggleBusinessDropdown(event)" title="Ganti Bisnis Aktif">
                <span class="business-dot" style="background:<?= esc($bisnisWarna) ?>"></span>
                <span class="business-name" id="businessCurrentName"><?= esc($bisnisNama) ?></span>
                <svg class="business-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
            <!-- Business Dropdown -->
            <div class="business-dropdown" id="businessDropdown">
                <div class="business-drop-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    <span>Pilih Bisnis</span>
                </div>
                <div class="business-drop-list">
                    <?php foreach ($bisnisList as $b): ?>
                    <a href="<?= base_url('dashboard/bisnis/switch/' . $b['id']) ?>"
                       class="business-drop-item <?= ($b['id'] == $bisnisSesi) ? 'active' : '' ?>"
                       id="bisnis-item-<?= $b['id'] ?>"
                       onclick="switchBisnis(event, <?= (int) $b['id'] ?>, <?= json_encode($b['nama_bisnis']) ?>, <?= json_encode($b['warna']) ?>)">
                        <span class="business-item-dot" style="background:<?= esc($b['warna']) ?>"></span>
                        <span class="business-item-name"><?= esc($b['nama_bisnis']) ?></span>
                        <?php if ($b['id'] == $bisnisSesi): ?>
                        <svg class="business-item-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="topbar-divider"></div>

        <div class="topbar-title-wrap">
            <h1><?= esc($judul ?? 'Dashboard') ?></h1>
            <div class="topbar-breadcrumb">Dashboard / <?= esc($judul ?? '') ?></div>
        </div>
    </div>

    <div class="topbar-right">
        <?= $topbar_right ?? '' ?>
        <!-- Notifikasi Bell -->
        <div class="notif-wrap" id="notifWrap">
            <button type="button" class="notif-btn" id="notifBtn" onclick="toggleNotifDropdown()" title="Notifikasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="notif-badge" id="notifBadge" style="display:none">0</span>
            </button>
            <!-- Dropdown -->
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-drop-header">
                    <span>Notifikasi</span>
                    <button type="button" class="notif-baca-semua" onclick="bacaSemuaNotif()">Tandai semua dibaca</button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Memuat...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ======================================================
   BUSINESS SWITCHER & TOPBAR LIGHT THEME STYLES
   ====================================================== */
.topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.topbar-divider {
    width: 1px;
    height: 24px;
    background: #e2e8f0;
    flex-shrink: 0;
}

.topbar-title-wrap h1 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.topbar-title-wrap .topbar-breadcrumb {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 1px;
    font-weight: 500;
}

.business-switcher {
    position: relative;
    z-index: 200;
    flex-shrink: 0;
}

.business-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 12px;
    cursor: pointer;
    color: #0f172a;
    font-size: 0.82rem;
    font-weight: 700;
    font-family: inherit;
    transition: all 0.15s ease;
    white-space: nowrap;
    max-width: 210px;
}

.business-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.business-btn.open {
    background: #ffffff;
    border-color: #2d6cdf;
    box-shadow: 0 0 0 3px rgba(45, 108, 223, 0.12);
}

.business-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 6px rgba(0,0,0,0.15);
    transition: background 0.3s;
}

.business-name {
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #0f172a;
}

.business-chevron {
    color: #64748b;
    transition: transform 0.2s;
    flex-shrink: 0;
}

.business-btn.open .business-chevron {
    transform: rotate(180deg);
}

/* Dropdown Light Theme */
.business-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 220px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
    padding: 6px;
    display: none;
    animation: dropIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}

.business-dropdown.show {
    display: block;
}

@keyframes dropIn {
    from { opacity: 0; transform: translateY(-6px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.business-drop-header {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px 8px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #94a3b8;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 4px;
}

.business-drop-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.business-drop-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: none;
    border: none;
    border-radius: 8px;
    padding: 8px 10px;
    cursor: pointer;
    color: #334155;
    font-size: 0.83rem;
    font-family: inherit;
    font-weight: 600;
    text-align: left;
    text-decoration: none;
    transition: all 0.12s;
    box-sizing: border-box;
}

.business-drop-item:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.business-drop-item.active {
    background: #eff6ff;
    color: #2563eb;
    font-weight: 700;
}

.business-drop-item.switching {
    opacity: 0.5;
    pointer-events: none;
}

.business-item-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.business-item-name {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.business-item-check {
    color: #2563eb;
    flex-shrink: 0;
}
</style>

<script>
// ======================================================
// BUSINESS SWITCHER LOGIC
// ======================================================

function getAppBaseUrl() {
    let b = document.querySelector('meta[name="base-url"]')?.content || '';
    if (!b) return '/';
    return b.endsWith('/') ? b : b + '/';
}

function buildSiteUrl(path) {
    const cleanPath = path.replace(/^\//, '');
    return getAppBaseUrl() + cleanPath;
}

function toggleBusinessDropdown(e) {
    if (e) e.stopPropagation();
    const btn  = document.getElementById('businessBtn');
    const drop = document.getElementById('businessDropdown');
    const isOpen = drop.classList.contains('show');

    // Tutup semua dropdown lain
    document.querySelectorAll('.business-dropdown.show').forEach(d => {
        d.classList.remove('show');
        document.getElementById('businessBtn')?.classList.remove('open');
    });

    if (!isOpen) {
        drop.classList.add('show');
        btn.classList.add('open');
    }
}

async function switchBisnis(e, id, nama, warna) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    const items = document.querySelectorAll('.business-drop-item');
    items.forEach(i => i.classList.add('switching'));

    // Optimistic UI update
    const currentNameEl = document.getElementById('businessCurrentName');
    const currentDotEl  = document.querySelector('.business-dot');
    if (currentNameEl) currentNameEl.textContent = nama;
    if (currentDotEl) currentDotEl.style.background = warna;

    const targetUrl = buildSiteUrl('dashboard/bisnis/switch/' + id);

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const data = await response.json().catch(() => null);
            if (data && data.status === 'sukses') {
                document.querySelectorAll('.business-drop-item').forEach(item => {
                    item.classList.remove('active');
                });

                const activeItem = document.getElementById('bisnis-item-' + id);
                if (activeItem) {
                    activeItem.classList.add('active');
                }

                document.getElementById('businessDropdown')?.classList.remove('show');
                document.getElementById('businessBtn')?.classList.remove('open');

                if (typeof toast === 'function') {
                    toast('Bisnis aktif diganti ke: ' + (data.data?.nama || nama), 'success');
                }

                setTimeout(() => {
                    window.location.reload();
                }, 300);
                return;
            }
        }
        // Fallback navigasi langsung jika AJAX response tidak 200 atau JSON tidak sukses
        window.location.href = targetUrl;
    } catch (err) {
        console.error('AJAX switch failed, redirecting directly:', err);
        window.location.href = targetUrl;
    }
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    const switcher = document.getElementById('businessSwitcher');
    if (switcher && !switcher.contains(e.target)) {
        document.getElementById('businessDropdown')?.classList.remove('show');
        document.getElementById('businessBtn')?.classList.remove('open');
    }
});
</script>
