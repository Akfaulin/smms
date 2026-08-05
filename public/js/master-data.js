/* ============================================================
   master-data.js — Master Data Module Logic
   Sistem Manajemen Media Sosial
   ============================================================ */

function switchTab(tabId, btn) {
    document.querySelectorAll('.ms-tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.ms-tab-panel').forEach(el => el.classList.remove('active'));

    btn.classList.add('active');
    const panel = document.getElementById('p_' + tabId);
    if (panel) panel.classList.add('active');
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
    const fTipe = document.getElementById('fTipe');
    const modalTitle = document.getElementById('modalTitle');
    const lblNama = document.getElementById('lblNama');
    const fId = document.getElementById('fId');
    const fNama = document.getElementById('fNama');
    const wrapStatus = document.getElementById('wrapStatus');
    const fStatus = document.getElementById('fStatus');
    const modalMaster = document.getElementById('modalMaster');

    if (fTipe) fTipe.value = tipe;
    if (modalTitle) modalTitle.textContent = `Tambah ${LABELS[tipe]}`;
    if (lblNama) lblNama.textContent = LABELS[tipe];

    if (fId) fId.value = '';
    if (fNama) fNama.value = '';

    if (tipe === 'plat') {
        if (wrapStatus) wrapStatus.style.display = 'block';
        if (fStatus) fStatus.value = 'aktif';
    } else {
        if (wrapStatus) wrapStatus.style.display = 'none';
    }

    if (modalMaster) modalMaster.classList.add('show');
}

function editData(tipe, data) {
    mode = 'edit';
    const fTipe = document.getElementById('fTipe');
    const modalTitle = document.getElementById('modalTitle');
    const lblNama = document.getElementById('lblNama');
    const fId = document.getElementById('fId');
    const fNama = document.getElementById('fNama');
    const wrapStatus = document.getElementById('wrapStatus');
    const fStatus = document.getElementById('fStatus');
    const modalMaster = document.getElementById('modalMaster');

    if (fTipe) fTipe.value = tipe;
    if (modalTitle) modalTitle.textContent = `Edit ${LABELS[tipe]}`;
    if (lblNama) lblNama.textContent = LABELS[tipe];

    if (fId) fId.value = data.id;

    if (tipe === 'plat') {
        if (fNama) fNama.value = data.nama_platform;
        if (wrapStatus) wrapStatus.style.display = 'block';
        if (fStatus) fStatus.value = data.status;
    } else if (tipe === 'jenis') {
        if (fNama) fNama.value = data.nama_jenis;
        if (wrapStatus) wrapStatus.style.display = 'none';
    } else if (tipe === 'pillar') {
        if (fNama) fNama.value = data.nama_type;
        if (wrapStatus) wrapStatus.style.display = 'none';
    }

    if (modalMaster) modalMaster.classList.add('show');
}

function tutupForm() {
    const modalMaster = document.getElementById('modalMaster');
    if (modalMaster) modalMaster.classList.remove('show');
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
