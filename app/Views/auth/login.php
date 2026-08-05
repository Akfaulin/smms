<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul ?? 'Login') ?> — Sistem Manajemen Sosmed</title>
    <meta name="description" content="Login ke Sistem Manajemen Media Sosial internal tim.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>

<div class="login-wrapper">
    <div class="brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24">
                <path d="M17 2H7C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5zm1 12c0 .6-.4 1-1 1h-2v2c0 .6-.4 1-1 1s-1-.4-1-1v-2h-2c-.6 0-1-.4-1-1s.4-1 1-1h2v-2c0-.6.4-1 1-1s1 .4 1 1v2h2c.6 0 1 .4 1 1zM8 9c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm4 0c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z"/>
            </svg>
        </div>
        <h1 class="brand-title">SMMS</h1>
        <div class="brand-sub">Social Media Management</div>
    </div>

    <div class="card">
        <h2 class="card-title">Masuk ke Akun Anda</h2>
        <p class="card-sub">Silakan login untuk mengakses dashboard.</p>

        <?php if (session('error')): ?>
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div><?= esc(session('error')) ?></div>
            </div>
        <?php endif; ?>

        <?php if (session('pesan')): ?>
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div><?= esc(session('pesan')) ?></div>
            </div>
        <?php endif; ?>

        <form action="/login" method="post" id="loginForm">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="username">Username / Email</label>
                <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" placeholder="Masukkan username atau email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pass" id="btnTogglePass" title="Tampilkan password">
                        <!-- Icon Eye -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">Masuk</button>
        </form>
    </div>

    <div class="login-note">
        Sistem Manajemen Media Sosial &copy; <?= date('Y') ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pwInput = document.getElementById('password');
    const toggleBtn = document.getElementById('btnTogglePass');
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('btnLogin');

    // Toggle password visibility
    toggleBtn.addEventListener('click', () => {
        const type = pwInput.getAttribute('type') === 'password' ? 'text' : 'password';
        pwInput.setAttribute('type', type);
        
        if (type === 'text') {
            toggleBtn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `;
            toggleBtn.title = "Sembunyikan password";
        } else {
            toggleBtn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
            toggleBtn.title = "Tampilkan password";
        }
    });

    // Loading state on submit
    form.addEventListener('submit', () => {
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = 'Memproses...';
    });
});
</script>

</body>
</html>
