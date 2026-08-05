<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/master-data.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="ms-card">
    <div class="ms-header">
        <div class="ms-title">Daftar Pengguna Sistem</div>
        <button class="btn-save" onclick="bukaForm()">+ Tambah User</button>
    </div>

    <table class="ms-table">
        <thead>
            <tr>
                <th>Nama & Email</th>
                <th>Role</th>
                <th>Status</th>
                <th style="text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= esc($u['nama']) ?></div>
                    <div style="font-size:12px;color:var(--cp-muted)"><?= esc($u['email']) ?></div>
                </td>
                <td><span class="ms-badge b-role"><?= esc($u['nama_role']) ?></span></td>
                <td>
                    <span class="ms-badge <?= $u['status'] === 'aktif' ? 'b-aktif' : 'b-nonaktif' ?>">
                        <?= strtoupper($u['status']) ?>
                    </span>
                </td>
                <td style="text-align:right">
                    <button class="btn-act" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">Edit</button>
                    <button class="btn-act" style="color:var(--cp-red); margin-left:8px;" onclick="deleteUser(<?= $u['id'] ?>)">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div class="ms-modal" id="modalUser">
    <div class="ms-modal-content">
        <h3 id="modalTitle" style="margin-bottom:20px;">Tambah User</h3>
        
        <input type="hidden" id="fId">
        
        <div class="ms-group">
            <label>Nama Lengkap</label>
            <input type="text" id="fNama" placeholder="Contoh: Budi Santoso">
        </div>
        
        <div class="ms-group">
            <label>Email / Username</label>
            <input type="email" id="fEmail" placeholder="Contoh: budi@smm.local">
        </div>
        
        <div class="ms-group">
            <label>Password <span id="fPassNote" style="font-weight:400; font-size:11px; color:#ef4444; display:none;">(Kosongkan jika tidak ingin diubah)</span></label>
            <input type="password" id="fPassword" placeholder="Minimal 6 karakter">
        </div>

        <div class="ms-group">
            <label>Role / Akses</label>
            <select id="fRole">
                <option value="">— Pilih Role —</option>
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>"><?= esc($r['nama_role']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="ms-group" id="wrapStatus" style="display:none;">
            <label>Status Akun</label>
            <select id="fStatus">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif (Suspend)</option>
            </select>
        </div>

        <div class="ms-actions">
            <button class="btn-cancel" onclick="tutupForm()">Batal</button>
            <button class="btn-save" id="btnSimpan" onclick="simpanUser()">Simpan Data</button>
        </div>
    </div>
</div>

<script>
let mode = 'tambah';

function bukaForm() {
    mode = 'tambah';
    document.getElementById('modalTitle').textContent = 'Tambah User Baru';
    document.getElementById('fId').value = '';
    document.getElementById('fNama').value = '';
    document.getElementById('fEmail').value = '';
    document.getElementById('fPassword').value = '';
    document.getElementById('fRole').value = '';
    document.getElementById('fStatus').value = 'aktif';
    
    document.getElementById('fPassNote').style.display = 'none';
    document.getElementById('wrapStatus').style.display = 'none';
    
    document.getElementById('modalUser').classList.add('show');
}

function editUser(data) {
    mode = 'edit';
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('fId').value = data.id;
    document.getElementById('fNama').value = data.nama;
    document.getElementById('fEmail').value = data.email;
    document.getElementById('fPassword').value = ''; // kosongkan
    document.getElementById('fRole').value = data.role_id;
    document.getElementById('fStatus').value = data.status;
    
    document.getElementById('fPassNote').style.display = 'inline';
    document.getElementById('wrapStatus').style.display = 'block';
    
    document.getElementById('modalUser').classList.add('show');
}

function tutupForm() {
    document.getElementById('modalUser').classList.remove('show');
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
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    try {
        const res = await api(url, 'POST', data);
        if (res.status === 'sukses') {
            toast(res.pesan, 'success');
            tutupForm();
            setTimeout(() => location.reload(), 800);
        } else {
            toast(res.pesan, 'error');
            btn.textContent = 'Simpan Data';
            btn.disabled = false;
        }
    } catch (e) {
        toast('Terjadi kesalahan jaringan.', 'error');
        btn.textContent = 'Simpan Data';
        btn.disabled = false;
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
</script>

<?= $this->endSection() ?>
