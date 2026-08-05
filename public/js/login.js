/* ============================================================
   login.js — Login Page Logic
   Sistem Manajemen Media Sosial
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const pwInput   = document.getElementById('password');
    const toggleBtn = document.getElementById('btnTogglePass');
    const form      = document.getElementById('loginForm');
    const submitBtn = document.getElementById('btnLogin');

    if (toggleBtn && pwInput) {
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
    }

    if (form && submitBtn) {
        form.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="btn-spinner"></span>
                Memproses...
            `;
        });
    }
});
