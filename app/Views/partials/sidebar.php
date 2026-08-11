<!-- ═══════════════════════════════════════════════════════ -->
<!-- SIDEBAR COMPONENT                                         -->
<!-- ═══════════════════════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="/dashboard" class="brand-wrap">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24"><path d="M17 2H7C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5zm1 12c0 .6-.4 1-1 1h-2v2c0 .6-.4 1-1 1s-1-.4-1-1v-2h-2c-.6 0-1-.4-1-1s.4-1 1-1h2v-2c0-.6.4-1 1-1s1 .4 1 1v2h2c.6 0 1 .4 1 1zM8 9c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm4 0c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z"/></svg>
            </div>
            <div>
                <div class="brand-text-name">SMMS</div>
                <div class="brand-text-sub">Social Media Management</div>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu</div>

        <a href="/dashboard" class="nav-item <?= (current_url() === base_url('dashboard') || current_url() === base_url('dashboard/')) ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            Dashboard
        </a>

        <?php $role = session('kode_role'); ?>
        <?php if (in_array($role, ['creative_team', 'superadmin', 'owner'], true)): ?>
        <a href="/dashboard/ide-konten" class="nav-item <?= str_contains(current_url(), 'ide-konten') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 18h6m-4 4h2m-6-8c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3-2 3.8v1.2H10v-1.2C8.8 16 8 14.6 8 13z"/>
            </svg>
            Ide Konten
        </a>
        <a href="/dashboard/trend-ai" class="nav-item <?= str_contains(current_url(), 'trend-ai') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4"/><path d="M12 16V8"/>
            </svg>
            Bank Trend AI
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['content_creator', 'superadmin', 'owner'], true)): ?>
        <a href="/dashboard/tugas-creator" class="nav-item <?= str_contains(current_url(), 'tugas-creator') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/>
            </svg>
            Tugas Desain
        </a>
        <a href="/dashboard/asset-library" class="nav-item <?= str_contains(current_url(), 'asset-library') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            Brand Kit & Aset
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['manager', 'superadmin', 'owner'], true)): ?>
        <a href="/dashboard/approval-manager" class="nav-item <?= str_contains(current_url(), 'approval-manager') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Approval & Review
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['admin_medsos', 'superadmin', 'owner'], true)): ?>
        <a href="/dashboard/jadwal-upload" class="nav-item <?= str_contains(current_url(), 'jadwal-upload') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/>
            </svg>
            Jadwal & Upload
        </a>
        <a href="/dashboard/kalender-tayang" class="nav-item <?= str_contains(current_url(), 'kalender-tayang') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Kalender Tayang
        </a>
        <?php endif; ?>

        <a href="/dashboard/content-plan" class="nav-item <?= str_contains(current_url(), 'content-plan') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Content Plan
        </a>
        <?php if (in_array($role, ['superadmin', 'owner', 'manager'], true)): ?>
        <a href="/dashboard/laporan" class="nav-item <?= str_contains(current_url(), 'laporan') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                <line x1="6" y1="20" x2="6" y2="14"/>
            </svg>
            Laporan
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['superadmin', 'owner'], true)): ?>
        <div class="nav-section-label" style="margin-top:14px">Master Data</div>
        
        <?php if ($role === 'superadmin'): ?>
        <a href="/dashboard/master/user" class="nav-item <?= str_contains(current_url(), 'master/user') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
            Manajemen User
        </a>
        <?php endif; ?>

        <a href="/dashboard/master/data" class="nav-item <?= str_contains(current_url(), 'master/data') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
            Platform & Jenis
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <a href="/dashboard/profil" class="user-card" style="text-decoration:none;display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;transition:background .18s;" onmouseover="this.style.background='rgba(255,255,255,0.06)'" onmouseout="this.style.background=''">
            <div class="user-avatar">
                <?= strtoupper(substr(session('nama') ?? 'U', 0, 2)) ?>
            </div>
            <div>
                <div class="user-name-text"><?= esc(session('nama')) ?></div>
                <div class="user-role-text"><?= esc(session('nama_role')) ?></div>
            </div>
        </a>
        <a href="/logout">
            <button class="btn-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Keluar
            </button>
        </a>
    </div>
</aside>
