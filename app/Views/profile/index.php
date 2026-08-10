<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/profile.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pf-wrap">

    <!-- Avatar Header -->
    <div class="pf-header">
        <div class="pf-avatar"><?= strtoupper(substr($user['nama'] ?? 'U', 0, 2)) ?></div>
        <div class="pf-header-info">
            <h1 class="pf-name"><?= esc($user['nama']) ?></h1>
            <span class="pf-role-badge"><?= esc($user['nama_role'] ?? '') ?></span>
            <p class="pf-email"><?= esc($user['email']) ?></p>
        </div>
    </div>

    <div class="pf-grid">

        <!-- Form Update Profil -->
        <div class="pf-card">
            <div class="pf-card-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Informasi Profil
            </div>
            <div class="pf-card-body">
                <div class="pf-form-group">
                    <label class="pf-label">Nama Lengkap</label>
                    <input type="text" id="pfNama" class="pf-input" value="<?= esc($user['nama']) ?>" placeholder="Nama lengkap...">
                </div>
                <div class="pf-form-group">
                    <label class="pf-label">Email</label>
                    <input type="email" class="pf-input" value="<?= esc($user['email']) ?>" disabled style="opacity:.5;cursor:not-allowed">
                    <p class="pf-hint">Email tidak dapat diubah sendiri. Hubungi Superadmin untuk mengubah email.</p>
                </div>
                <div class="pf-form-group">
                    <label class="pf-label">Role</label>
                    <input type="text" class="pf-input" value="<?= esc($user['nama_role'] ?? '') ?>" disabled style="opacity:.5;cursor:not-allowed">
                </div>
                <button class="pf-btn" id="btnSimpanProfil" onclick="simpanProfil()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>

        <!-- Form Ganti Password -->
        <div class="pf-card">
            <div class="pf-card-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Ganti Password
            </div>
            <div class="pf-card-body">
                <div class="pf-form-group">
                    <label class="pf-label">Password Lama</label>
                    <div class="pf-pass-wrap">
                        <input type="password" id="pfPassLama" class="pf-input" placeholder="Masukkan password saat ini...">
                        <button class="pf-eye" type="button" onclick="togglePass('pfPassLama', this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="pf-form-group">
                    <label class="pf-label">Password Baru</label>
                    <div class="pf-pass-wrap">
                        <input type="password" id="pfPassBaru" class="pf-input" placeholder="Minimal 6 karakter...">
                        <button class="pf-eye" type="button" onclick="togglePass('pfPassBaru', this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="pf-form-group">
                    <label class="pf-label">Konfirmasi Password Baru</label>
                    <div class="pf-pass-wrap">
                        <input type="password" id="pfPassKonfirm" class="pf-input" placeholder="Ulangi password baru...">
                        <button class="pf-eye" type="button" onclick="togglePass('pfPassKonfirm', this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="pf-pass-strength" id="passStrength" style="display:none">
                    <div class="pf-strength-bar" id="strengthBar"></div>
                    <span id="strengthLabel"></span>
                </div>
                <button class="pf-btn pf-btn-danger" id="btnGantiPass" onclick="gantiPassword()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ubah Password
                </button>
            </div>
        </div>

    </div><!-- /.pf-grid -->
</div><!-- /.pf-wrap -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
async function simpanProfil() {
    const nama = document.getElementById('pfNama').value.trim();
    if (!nama) { toast('Nama tidak boleh kosong.', 'error'); return; }

    const btn = document.getElementById('btnSimpanProfil');
    btn.disabled = true;
    btn.innerHTML = '<span class="pf-spin"></span> Menyimpan...';

    const fd = new FormData();
    fd.append('nama', nama);

    const res = await api('/dashboard/profil/update', 'POST', fd);
    if (res.status === 'sukses') {
        toast(res.pesan, 'success');
        // Update nama di sidebar tanpa reload penuh
        document.querySelectorAll('.user-name-text').forEach(el => el.textContent = nama);
        document.querySelectorAll('.pf-name').forEach(el => el.textContent = nama);
        // Update avatar inisial
        const inisial = nama.substring(0,2).toUpperCase();
        document.querySelectorAll('.user-avatar, .pf-avatar').forEach(el => el.textContent = inisial);
    } else {
        toast(res.pesan || 'Gagal memperbarui profil.', 'error');
    }
    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Perubahan';
}

async function gantiPassword() {
    const lama    = document.getElementById('pfPassLama').value;
    const baru    = document.getElementById('pfPassBaru').value;
    const konfirm = document.getElementById('pfPassKonfirm').value;

    if (!lama || !baru || !konfirm) { toast('Semua field password wajib diisi.', 'error'); return; }
    if (baru.length < 6) { toast('Password baru minimal 6 karakter.', 'error'); return; }
    if (baru !== konfirm) { toast('Konfirmasi password tidak cocok.', 'error'); return; }

    const btn = document.getElementById('btnGantiPass');
    btn.disabled = true;
    btn.innerHTML = '<span class="pf-spin"></span> Memproses...';

    const fd = new FormData();
    fd.append('password_lama', lama);
    fd.append('password_baru', baru);
    fd.append('password_konfirmasi', konfirm);

    const res = await api('/dashboard/profil/ganti-password', 'POST', fd);
    if (res.status === 'sukses') {
        toast(res.pesan, 'success');
        document.getElementById('pfPassLama').value = '';
        document.getElementById('pfPassBaru').value = '';
        document.getElementById('pfPassKonfirm').value = '';
        document.getElementById('passStrength').style.display = 'none';
    } else {
        toast(res.pesan || 'Gagal mengganti password.', 'error');
    }
    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Ubah Password';
}

function togglePass(id, btn) {
    const input = document.getElementById(id);
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.style.opacity = isPass ? '1' : '0.5';
}

// Password strength indicator
document.getElementById('pfPassBaru')?.addEventListener('input', function() {
    const v = this.value;
    const wrap = document.getElementById('passStrength');
    const bar  = document.getElementById('strengthBar');
    const lbl  = document.getElementById('strengthLabel');
    if (!v) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'flex';

    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const levels = [
        { color: '#ef4444', w: '25%', text: 'Lemah' },
        { color: '#f59e0b', w: '50%', text: 'Sedang' },
        { color: '#3b82f6', w: '75%', text: 'Kuat' },
        { color: '#22c55e', w: '100%', text: 'Sangat Kuat' },
    ];
    const l = levels[score - 1] || levels[0];
    bar.style.width = l.w;
    bar.style.background = l.color;
    lbl.textContent = l.text;
    lbl.style.color = l.color;
});
</script>
<?= $this->endSection() ?>
