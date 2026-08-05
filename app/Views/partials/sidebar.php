<!-- ═══════════════════════════════════════════════════════ -->
<!-- SIDEBAR COMPONENT                                         -->
<!-- ═══════════════════════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="/dashboard/content-plan" class="brand-wrap">
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

        <a href="/dashboard/content-plan" class="nav-item <?= str_contains(current_url(), 'content-plan') ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Content Plan
        </a>

        <?php $role = session('kode_role'); ?>
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
        <div class="user-card">
            <div class="user-avatar">
                <?= strtoupper(substr(session('nama') ?? 'U', 0, 2)) ?>
            </div>
            <div>
                <div class="user-name-text"><?= esc(session('nama')) ?></div>
                <div class="user-role-text"><?= esc(session('nama_role')) ?></div>
            </div>
        </div>
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
