<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="master-bisnis-wrap">

    <!-- Header -->
    <div class="mb-card mb-header-card">
        <div class="mb-header-info">
            <div class="mb-header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div>
                <h2 class="mb-header-title">Manajemen Bisnis</h2>
                <p class="mb-header-sub">Kelola bisnis yang sosial medianya dikelola oleh tim SMMS (maks. 4 bisnis)</p>
            </div>
        </div>
        <button class="mb-btn mb-btn-primary" onclick="openAddModal()" <?= count($semua_bisnis) >= 4 ? 'disabled title="Maks. 4 bisnis"' : '' ?>>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Bisnis
        </button>
    </div>

    <!-- Bisnis Cards Grid -->
    <div class="mb-grid">
        <?php foreach ($semua_bisnis as $b): ?>
        <div class="mb-card mb-bisnis-card" id="card-bisnis-<?= $b['id'] ?>">
            <div class="mb-card-top">
                <div class="mb-bisnis-avatar" style="background: <?= esc($b['warna']) ?>22; border-color: <?= esc($b['warna']) ?>44;">
                    <?php if ($b['logo_url']): ?>
                        <img src="<?= esc($b['logo_url']) ?>" alt="<?= esc($b['nama_bisnis']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                    <?php else: ?>
                        <span style="color:<?= esc($b['warna']) ?>;font-size:1.5rem;font-weight:800;">
                            <?= strtoupper(substr($b['nama_bisnis'], 0, 1)) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="mb-bisnis-status-wrap">
                    <span class="mb-status-badge <?= $b['status'] === 'aktif' ? 'aktif' : 'nonaktif' ?>">
                        <?= $b['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                    <span class="mb-order-badge">#<?= $b['urutan'] ?></span>
                </div>
            </div>

            <div class="mb-warna-stripe" style="background: <?= esc($b['warna']) ?>; box-shadow: 0 0 20px <?= esc($b['warna']) ?>55;"></div>

            <div class="mb-card-body">
                <h3 class="mb-bisnis-nama"><?= esc($b['nama_bisnis']) ?></h3>
                <p class="mb-bisnis-desc"><?= esc($b['deskripsi'] ?: '—') ?></p>
                <div class="mb-warna-info">
                    <span class="mb-warna-dot" style="background:<?= esc($b['warna']) ?>"></span>
                    <span class="mb-warna-hex"><?= esc($b['warna']) ?></span>
                </div>
            </div>

            <div class="mb-card-actions">
                <button class="mb-btn mb-btn-outline" onclick='openEditModal(<?= json_encode([
                    "id"          => $b["id"],
                    "nama_bisnis" => $b["nama_bisnis"],
                    "deskripsi"   => $b["deskripsi"],
                    "warna"       => $b["warna"],
                    "logo_url"    => $b["logo_url"],
                    "status"      => $b["status"],
                ]) ?>)'>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit
                </button>
                <button class="mb-btn mb-btn-danger" onclick="hapusBisnis(<?= $b['id'] ?>, '<?= esc($b['nama_bisnis'], 'js') ?>')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (count($semua_bisnis) < 4): ?>
        <!-- Slot kosong -->
        <?php for ($i = count($semua_bisnis); $i < 4; $i++): ?>
        <div class="mb-card mb-empty-card" onclick="openAddModal()">
            <div class="mb-empty-inner">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" style="opacity:.3">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Slot Bisnis Kosong</span>
                <small>Klik untuk menambahkan</small>
            </div>
        </div>
        <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ======================================================
     MODAL TAMBAH BISNIS
     ====================================================== -->
<div class="mb-modal-overlay" id="addModal" onclick="closeModal('addModal')">
    <div class="mb-modal" onclick="event.stopPropagation()">
        <div class="mb-modal-header">
            <h3>Tambah Bisnis Baru</h3>
            <button class="mb-modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="submitAddForm(event)">
            <div class="mb-modal-body">
                <div class="mb-form-group">
                    <label>Nama Bisnis <span class="required">*</span></label>
                    <input type="text" name="nama_bisnis" class="mb-input" placeholder="Contoh: Toko Kopi Nusantara" required maxlength="100">
                </div>
                <div class="mb-form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="mb-input" rows="3" placeholder="Deskripsi singkat bisnis..."></textarea>
                </div>
                <div class="mb-form-group">
                    <label>Warna Identitas</label>
                    <div class="mb-color-pick-wrap">
                        <input type="color" name="warna" id="addColorPicker" class="mb-color-input" value="#6C5CE7" oninput="syncColorText('addColorPicker','addColorText')">
                        <input type="text" id="addColorText" class="mb-input mb-color-text" value="#6C5CE7" maxlength="7" placeholder="#RRGGBB" oninput="syncColorPicker('addColorText','addColorPicker')">
                    </div>
                </div>
                <div class="mb-form-group">
                    <label>URL Logo / Ikon <span style="opacity:.5;font-size:.75rem;">(opsional)</span></label>
                    <input type="url" name="logo_url" class="mb-input" placeholder="https://...">
                </div>
            </div>
            <div class="mb-modal-footer">
                <button type="button" class="mb-btn mb-btn-outline" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="mb-btn mb-btn-primary" id="addSubmitBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Bisnis
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================
     MODAL EDIT BISNIS
     ====================================================== -->
<div class="mb-modal-overlay" id="editModal" onclick="closeModal('editModal')">
    <div class="mb-modal" onclick="event.stopPropagation()">
        <div class="mb-modal-header">
            <h3>Edit Bisnis</h3>
            <button class="mb-modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="submitEditForm(event)">
            <input type="hidden" name="bisnis_id" id="editBisnisId">
            <div class="mb-modal-body">
                <div class="mb-form-group">
                    <label>Nama Bisnis <span class="required">*</span></label>
                    <input type="text" name="nama_bisnis" id="editNama" class="mb-input" required maxlength="100">
                </div>
                <div class="mb-form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" class="mb-input" rows="3"></textarea>
                </div>
                <div class="mb-form-group">
                    <label>Warna Identitas</label>
                    <div class="mb-color-pick-wrap">
                        <input type="color" name="warna" id="editColorPicker" class="mb-color-input" oninput="syncColorText('editColorPicker','editColorText')">
                        <input type="text" id="editColorText" class="mb-input mb-color-text" maxlength="7" placeholder="#RRGGBB" oninput="syncColorPicker('editColorText','editColorPicker')">
                    </div>
                </div>
                <div class="mb-form-group">
                    <label>URL Logo / Ikon <span style="opacity:.5;font-size:.75rem;">(opsional)</span></label>
                    <input type="url" name="logo_url" id="editLogoUrl" class="mb-input" placeholder="https://...">
                </div>
                <div class="mb-form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus" class="mb-input">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="mb-modal-footer">
                <button type="button" class="mb-btn mb-btn-outline" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="mb-btn mb-btn-primary" id="editSubmitBtn">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ======================================================
   MASTER BISNIS PAGE STYLES (Light Theme)
   ====================================================== */
.master-bisnis-wrap {
    padding: 0;
}

.mb-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.mb-header-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.mb-header-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.mb-header-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #6C5CE7, #a29bfe);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.25);
}

.mb-header-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
}

.mb-header-sub {
    margin: 2px 0 0;
    font-size: 0.78rem;
    color: #64748b;
}

/* Grid */
.mb-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}

/* Bisnis Card */
.mb-bisnis-card {
    display: flex;
    flex-direction: column;
    transition: transform 0.2s, box-shadow 0.2s;
}

.mb-bisnis-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.mb-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 16px 12px;
}

.mb-bisnis-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    border: 2px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8fafc;
}

.mb-bisnis-status-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.mb-status-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mb-status-badge.aktif {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.mb-status-badge.nonaktif {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.mb-order-badge {
    font-size: 0.68rem;
    color: #94a3b8;
    font-weight: 700;
}

.mb-warna-stripe {
    height: 3px;
    margin: 0;
    transition: box-shadow 0.3s;
}

.mb-card-body {
    padding: 14px 16px;
    flex: 1;
}

.mb-bisnis-nama {
    margin: 0 0 6px;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
}

.mb-bisnis-desc {
    margin: 0 0 10px;
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.mb-warna-info {
    display: flex;
    align-items: center;
    gap: 6px;
}

.mb-warna-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
}

.mb-warna-hex {
    font-size: 0.72rem;
    font-family: monospace;
    color: #64748b;
    font-weight: 600;
}

.mb-card-actions {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    border-top: 1.5px solid #f1f5f9;
}

/* Empty card */
.mb-empty-card {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    border-style: dashed;
    border-color: #cbd5e1;
    background: #f8fafc;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}

.mb-empty-card:hover {
    border-color: #6C5CE7;
    background: #f5f3ff;
}

.mb-empty-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 700;
}

.mb-empty-inner small {
    font-size: 0.7rem;
    font-weight: 500;
    color: #94a3b8;
}

/* Buttons */
.mb-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    font-family: inherit;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}

.mb-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.mb-btn-primary {
    background: linear-gradient(135deg, #6C5CE7, #8c7ae6);
    color: #fff;
    box-shadow: 0 4px 14px rgba(108,92,231,0.3);
}

.mb-btn-primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(108,92,231,0.4);
}

.mb-btn-outline {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    color: #334155;
}

.mb-btn-outline:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.mb-btn-danger {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #dc2626;
}

.mb-btn-danger:hover {
    background: #fee2e2;
}

/* Forms */
.mb-form-group {
    margin-bottom: 14px;
}

.mb-form-group label {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}

.required { color: #dc2626; }

.mb-input {
    width: 100%;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 0.85rem;
    color: #0f172a;
    font-family: inherit;
    box-sizing: border-box;
    transition: border-color 0.18s;
}

.mb-input:focus {
    outline: none;
    background: #ffffff;
    border-color: #6C5CE7;
    box-shadow: 0 0 0 3px rgba(108,92,231,0.15);
}

.mb-input::placeholder { color: #94a3b8; }

.mb-color-pick-wrap {
    display: flex;
    gap: 8px;
    align-items: center;
}

.mb-color-input {
    width: 44px;
    height: 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 2px;
    cursor: pointer;
    background: #f8fafc;
    flex-shrink: 0;
}

.mb-color-text {
    flex: 1;
    font-family: monospace;
}

/* Modal */
.mb-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.mb-modal-overlay.open {
    display: flex;
    animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.mb-modal {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    width: 100%;
    max-width: 480px;
    overflow: hidden;
    animation: slideUp 0.2s ease;
    box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.mb-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1.5px solid #f1f5f9;
}

.mb-modal-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
}

.mb-modal-close {
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #64748b;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.mb-modal-close:hover {
    background: #fef2f2;
    color: #dc2626;
}

.mb-modal-body {
    padding: 20px 22px;
}

.mb-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 22px;
    border-top: 1.5px solid #f1f5f9;
}
</style>

<?= $this->section('scripts') ?>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function openAddModal() {
    document.getElementById('addModal').classList.add('open');
}

function openEditModal(data) {
    document.getElementById('editBisnisId').value = data.id;
    document.getElementById('editNama').value = data.nama_bisnis;
    document.getElementById('editDeskripsi').value = data.deskripsi ?? '';
    document.getElementById('editColorPicker').value = data.warna;
    document.getElementById('editColorText').value = data.warna;
    document.getElementById('editLogoUrl').value = data.logo_url ?? '';
    document.getElementById('editStatus').value = data.status;
    document.getElementById('editModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function syncColorText(pickerId, textId) {
    document.getElementById(textId).value = document.getElementById(pickerId).value;
}

function syncColorPicker(textId, pickerId) {
    const val = document.getElementById(textId).value;
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        document.getElementById(pickerId).value = val;
    }
}

async function submitAddForm(e) {
    e.preventDefault();
    const btn  = document.getElementById('addSubmitBtn');
    const form = document.getElementById('addForm');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const fd = new FormData(form);
    // Sync warna dari teks
    fd.set('warna', document.getElementById('addColorText').value || document.getElementById('addColorPicker').value);

    try {
        const r = await fetch('/dashboard/master/bisnis/store', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: fd,
        });
        const d = await r.json();
        if (d.status === 'sukses') {
            toast('Bisnis baru berhasil ditambahkan!', 'success');
            closeModal('addModal');
            setTimeout(() => location.reload(), 700);
        } else {
            toast(d.pesan || 'Gagal menambahkan bisnis.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan. Coba lagi.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tambah Bisnis';
    }
}

async function submitEditForm(e) {
    e.preventDefault();
    const btn = document.getElementById('editSubmitBtn');
    const id  = document.getElementById('editBisnisId').value;
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const form = document.getElementById('editForm');
    const fd   = new FormData(form);
    fd.set('warna', document.getElementById('editColorText').value || document.getElementById('editColorPicker').value);

    try {
        const r = await fetch('/dashboard/master/bisnis/update/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: fd,
        });
        const d = await r.json();
        if (d.status === 'sukses') {
            toast('Bisnis berhasil diperbarui!', 'success');
            closeModal('editModal');
            setTimeout(() => location.reload(), 700);
        } else {
            toast(d.pesan || 'Gagal memperbarui bisnis.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan. Coba lagi.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Perubahan';
    }
}

async function hapusBisnis(id, nama) {
    const ok = await customConfirm(`Hapus bisnis "<strong>${nama}</strong>"?<br><small style="opacity:.7">Bisnis hanya dapat dihapus jika tidak memiliki content plan.</small>`);
    if (!ok) return;

    try {
        const r = await fetch('/dashboard/master/bisnis/delete/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        const d = await r.json();
        if (d.status === 'sukses') {
            toast('Bisnis berhasil dihapus.', 'success');
            document.getElementById('card-bisnis-' + id)?.remove();
        } else {
            toast(d.pesan || 'Gagal menghapus bisnis.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan. Coba lagi.', 'error');
    }
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
