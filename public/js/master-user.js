/* ============================================================
   master-user.js — User Management Module Logic
   Sistem Manajemen Media Sosial
   ============================================================ */

let mode = 'tambah';

function bukaForm() {
    mode = 'tambah';
    const modalTitle = document.getElementById('modalTitle');
    const fId = document.getElementById('fId');
    const fNama = document.getElementById('fNama');
    const fEmail = document.getElementById('fEmail');
    const fPassword = document.getElementById('fPassword');
    const fRole = document.getElementById('fRole');
    const fStatus = document.getElementById('fStatus');
    const fPassNote = document.getElementById('fPassNote');
    const wrapStatus = document.getElementById('wrapStatus');
    const modalUser = document.getElementById('modalUser');

    if (modalTitle) modalTitle.textContent = 'Tambah User Baru';
    if (fId) fId.value = '';
    if (fNama) fNama.value = '';
    if (fEmail) fEmail.value = '';
    if (fPassword) fPassword.value = '';
    if (fRole) fRole.value = '';
    if (fStatus) fStatus.value = 'aktif';

    if (fPassNote) fPassNote.style.display = 'none';
    if (wrapStatus) wrapStatus.style.display = 'none';
    if (modalUser) modalUser.classList.add('show');
}

function editUser(data) {
    mode = 'edit';
    const modalTitle = document.getElementById('modalTitle');
    const fId = document.getElementById('fId');
    const fNama = document.getElementById('fNama');
    const fEmail = document.getElementById('fEmail');
    const fPassword = document.getElementById('fPassword');
    const fRole = document.getElementById('fRole');
    const fStatus = document.getElementById('fStatus');
    const fPassNote = document.getElementById('fPassNote');
    const wrapStatus = document.getElementById('wrapStatus');
    const modalUser = document.getElementById('modalUser');

    if (modalTitle) modalTitle.textContent = 'Edit User';
    if (fId) fId.value = data.id;
    if (fNama) fNama.value = data.nama;
    if (fEmail) fEmail.value = data.email;
    if (fPassword) fPassword.value = ''; // kosongkan
    if (fRole) fRole.value = data.role_id;
    if (fStatus) fStatus.value = data.status;

    if (fPassNote) fPassNote.style.display = 'inline';
    if (wrapStatus) wrapStatus.style.display = 'block';
    if (modalUser) modalUser.classList.add('show');
}

function tutupForm() {
    const modalUser = document.getElementById('modalUser');
    if (modalUser) modalUser.classList.remove('show');
}

async function simpanUser() {
    const id = document.getElementById('fId').value;
    const url = mode === 'tambah' ? '/dashboard/master/user/store' : `/dashboard/master/user/update/${id}`;

    const data = {
        nama: document.getElementById('fNama').value,
        email: document.getElementById('fEmail').value,
        password: document.getElementById('fPassword').value,
        role_id: document.getElementById('fRole').value,
        status: document.getElementById('fStatus').value,
    };

    const btn = document.getElementById('btnSimpan');
    if (btn) {
        btn.textContent = 'Menyimpan...';
        btn.disabled = true;
    }

    try {
        const res = await api(url, 'POST', data);
        if (res.status === 'sukses') {
            toast(res.pesan, 'success');
            tutupForm();
            setTimeout(() => location.reload(), 800);
        } else {
            toast(res.pesan, 'error');
            if (btn) {
                btn.textContent = 'Simpan Data';
                btn.disabled = false;
            }
        }
    } catch (e) {
        toast('Terjadi kesalahan jaringan.', 'error');
        if (btn) {
            btn.textContent = 'Simpan Data';
            btn.disabled = false;
        }
    }
}

function deleteUser(id) {
    konfirmasiHapus({
        title: 'Hapus User?',
        message: 'Apakah Anda yakin ingin menghapus akun pengguna ini dari sistem? Tindakan ini tidak dapat dibatalkan.',
        confirmText: 'Ya, Hapus User',
        onConfirm: async () => {
            try {
                const res = await api(`/dashboard/master/user/delete/${id}`, 'POST');
                if (res.status === 'sukses') {
                    toast(res.pesan, 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    toast(res.pesan, 'error');
                }
            } catch (e) {
                toast('Terjadi kesalahan jaringan.', 'error');
            }
        }
    });
}
