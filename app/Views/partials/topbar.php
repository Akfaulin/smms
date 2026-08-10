<!-- ═══════════════════════════════════════════════════════ -->
<!-- TOPBAR COMPONENT                                          -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="topbar">
    <div class="topbar-left">
        <h1><?= esc($judul ?? 'Dashboard') ?></h1>
        <div class="topbar-breadcrumb">Dashboard / <?= esc($judul ?? '') ?></div>
    </div>
    <div class="topbar-right">
        <?= $topbar_right ?? '' ?>
        <!-- Notifikasi Bell -->
        <div class="notif-wrap" id="notifWrap">
            <button class="notif-btn" id="notifBtn" onclick="toggleNotifDropdown()" title="Notifikasi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="notif-badge" id="notifBadge" style="display:none">0</span>
            </button>
            <!-- Dropdown -->
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-drop-header">
                    <span>Notifikasi</span>
                    <button class="notif-baca-semua" onclick="bacaSemuaNotif()">Tandai semua dibaca</button>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Memuat...</div>
                </div>
            </div>
        </div>
    </div>
</div>
