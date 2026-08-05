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

<?= $this->section('scripts') ?>
<script src="/js/master-user.js"></script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
