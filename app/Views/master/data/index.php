<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/master-data.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="ms-card">
    <div class="ms-tabs">
        <button class="ms-tab active" onclick="switchTab('plat', this)">Platform Medsos</button>
        <button class="ms-tab" onclick="switchTab('jenis', this)">Jenis Konten</button>
        <button class="ms-tab" onclick="switchTab('pillar', this)">Content Pillar</button>
    </div>

    <!-- Panel Platform -->
    <div class="ms-tab-panel active" id="p_plat">
        <div class="ms-header">
            <div class="ms-title">Platform Sosial Media</div>
            <button class="btn-save" onclick="bukaForm('plat')">+ Tambah Platform</button>
        </div>
        <table class="ms-table">
            <thead>
                <tr>
                    <th>Nama Platform</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($platforms as $p): ?>
                <tr>
                    <td style="font-weight:600"><?= esc($p['nama_platform']) ?></td>
                    <td>
                        <span class="ms-badge <?= $p['status'] === 'aktif' ? 'b-aktif' : 'b-nonaktif' ?>">
                            <?= strtoupper($p['status']) ?>
                        </span>
                    </td>
                    <td style="text-align:right">
                        <button class="btn-act" onclick='editData("plat", <?= json_encode($p) ?>)'>Edit</button>
                        <button class="btn-act" style="color:var(--cp-red); margin-left:8px;" onclick='deleteData("plat", <?= $p['id'] ?>)'>Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Panel Jenis Konten -->
    <div class="ms-tab-panel" id="p_jenis">
        <div class="ms-header">
            <div class="ms-title">Jenis Konten (Format)</div>
            <button class="btn-save" onclick="bukaForm('jenis')">+ Tambah Jenis</button>
        </div>
        <table class="ms-table">
            <thead>
                <tr>
                    <th>Nama Jenis Konten</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jenisKonten as $j): ?>
                <tr>
                    <td style="font-weight:600"><?= esc($j['nama_jenis']) ?></td>
                    <td style="text-align:right">
                        <button class="btn-act" onclick='editData("jenis", <?= json_encode($j) ?>)'>Edit</button>
                        <button class="btn-act" style="color:var(--cp-red); margin-left:8px;" onclick='deleteData("jenis", <?= $j['id'] ?>)'>Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Panel Content Pillar -->
    <div class="ms-tab-panel" id="p_pillar">
        <div class="ms-header">
            <div class="ms-title">Content Pillar (Kategori)</div>
            <button class="btn-save" onclick="bukaForm('pillar')">+ Tambah Pillar</button>
        </div>
        <table class="ms-table">
            <thead>
                <tr>
                    <th>Nama Content Pillar</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pillars as $cp): ?>
                <tr>
                    <td style="font-weight:600"><?= esc($cp['nama_type']) ?></td>
                    <td style="text-align:right">
                        <button class="btn-act" onclick='editData("pillar", <?= json_encode($cp) ?>)'>Edit</button>
                        <button class="btn-act" style="color:var(--cp-red); margin-left:8px;" onclick='deleteData("pillar", <?= $cp['id'] ?>)'>Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Universal untuk Master Data -->
<div class="ms-modal" id="modalMaster">
    <div class="ms-modal-content">
        <h3 id="modalTitle" style="margin-bottom:20px;">Tambah Data</h3>
        
        <input type="hidden" id="fId">
        <input type="hidden" id="fTipe"> <!-- 'plat', 'jenis', atau 'pillar' -->
        
        <div class="ms-group">
            <label id="lblNama">Nama Data</label>
            <input type="text" id="fNama">
        </div>
        
        <div class="ms-group" id="wrapStatus" style="display:none;">
            <label>Status</label>
            <select id="fStatus">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="ms-actions">
            <button class="btn-cancel" onclick="tutupForm()">Batal</button>
            <button class="btn-save" id="btnSimpan" onclick="simpanData()">Simpan Data</button>
        </div>
    </div>
</div>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.ms-tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.ms-tab-panel').forEach(el => el.classList.remove('active'));
    
    btn.classList.add('active');
    document.getElementById('p_' + tabId).classList.add('active');
}

let mode = 'tambah';

const LABELS = {
    'plat': 'Nama Platform',
    'jenis': 'Nama Jenis Konten',
    'pillar': 'Nama Content Pillar'
};

const URLS = {
    'plat': { store: '/dashboard/master/platform/store', update: '/dashboard/master/platform/update/' },
    'jenis': { store: '/dashboard/master/jenis/store', update: '/dashboard/master/jenis/update/' },
    'pillar': { store: '/dashboard/master/pillar/store', update: '/dashboard/master/pillar/update/' },
};

function bukaForm(tipe) {
    mode = 'tambah';
    document.getElementById('fTipe').value = tipe;
    document.getElementById('modalTitle').textContent = `Tambah ${LABELS[tipe]}`;
    document.getElementById('lblNama').textContent = LABELS[tipe];
    
    document.getElementById('fId').value = '';
    document.getElementById('fNama').value = '';
    
    if (tipe === 'plat') {
        document.getElementById('wrapStatus').style.display = 'block';
        document.getElementById('fStatus').value = 'aktif';
    } else {
        document.getElementById('wrapStatus').style.display = 'none';
    }
    
    document.getElementById('modalMaster').classList.add('show');
}

function editData(tipe, data) {
    mode = 'edit';
    document.getElementById('fTipe').value = tipe;
    document.getElementById('modalTitle').textContent = `Edit ${LABELS[tipe]}`;
    document.getElementById('lblNama').textContent = LABELS[tipe];
    
    document.getElementById('fId').value = data.id;
    
    if (tipe === 'plat') {
        document.getElementById('fNama').value = data.nama_platform;
        document.getElementById('wrapStatus').style.display = 'block';
        document.getElementById('fStatus').value = data.status;
    } else if (tipe === 'jenis') {
        document.getElementById('fNama').value = data.nama_jenis;
        document.getElementById('wrapStatus').style.display = 'none';
    } else if (tipe === 'pillar') {
        document.getElementById('fNama').value = data.nama_type;
        document.getElementById('wrapStatus').style.display = 'none';
    }
    
    document.getElementById('modalMaster').classList.add('show');
}

function tutupForm() {
    document.getElementById('modalMaster').classList.remove('show');
}

async function simpanData() {
    const tipe = document.getElementById('fTipe').value;
    const id = document.getElementById('fId').value;
    const url = mode === 'tambah' ? URLS[tipe].store : URLS[tipe].update + id;
    
    const data = {};
    if (tipe === 'plat') {
        data.nama_platform = document.getElementById('fNama').value;
        data.status = document.getElementById('fStatus').value;
    } else if (tipe === 'jenis') {
        data.nama_jenis = document.getElementById('fNama').value;
    } else if (tipe === 'pillar') {
        data.nama_type = document.getElementById('fNama').value;
    }

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

function deleteData(tipe, id) {
    const labelMap = { plat: 'Platform Medsos', jenis: 'Jenis Konten', pillar: 'Content Pillar' };
    const namaData = labelMap[tipe] || 'Data Master';

    konfirmasiHapus({
        title: `Hapus ${namaData}?`,
        message: `Apakah Anda yakin ingin menghapus ${namaData.toLowerCase()} ini? Tindakan ini tidak dapat dibatalkan.`,
        confirmText: 'Ya, Hapus Data',
        onConfirm: async () => {
            const endpoint = `/dashboard/master/${tipe === 'plat' ? 'platform' : tipe}/delete/${id}`;
            try {
                const res = await api(endpoint, 'POST');
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
