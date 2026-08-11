<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<style>
.al-swatch-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 32px;
}
.al-swatch-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    transition: all 0.22s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.al-swatch-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
}
.al-color-preview {
    height: 110px;
    width: 100%;
    position: relative;
    border-bottom: 1px solid #f1f5f9;
}
.al-swatch-info {
    padding: 16px;
}
.al-color-hex {
    font-family: 'DM Sans', monospace;
    font-weight: 800;
    font-size: 15px;
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.al-asset-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 32px;
}
@media (max-width: 992px) {
    .al-swatch-grid { grid-template-columns: repeat(2, 1fr); }
    .al-asset-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
    .al-swatch-grid { grid-template-columns: 1fr; }
    .al-asset-grid { grid-template-columns: 1fr; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<div class="ik-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); color:#0284c7; box-shadow:0 2px 8px rgba(2, 132, 199, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Asset Library & Brand Kit</h1>
            <p class="ik-header-sub">Pusat penyimpanan warna palette resmi brand dan aset desain. Tambah atau hapus aset kapan saja secara dinamis.</p>
        </div>
    </div>
    <div>
        <button class="cpb cpb-pri" onclick="bukaModal('modalTambahAset')" style="padding:9px 18px; font-weight:600;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px; margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Aset Baru
        </button>
    </div>
</div>

<!-- ── Section 1: Color Palette Grid ──────────────────────── -->
<div style="margin-bottom:14px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Palette Warna Brand</h2>
    <p style="font-size:13px; color:#64748b; margin:0;">Warna resmi brand yang dapat disalin Hex Code-nya atau ditambah & dihapus sesuai kebutuhan.</p>
</div>

<div class="al-swatch-grid">
    <?php if (empty($palettes)): ?>
    <div style="grid-column: 1 / -1;">
        <div class="cp-card" style="padding:32px; text-align:center; color:#64748b;">
            <p style="margin:0; font-size:13.5px; font-weight:600;">Belum ada warna palette yang tersimpan.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($palettes as $p): ?>
    <div class="al-swatch-card">
        <div>
            <div class="al-color-preview" style="background: <?= esc($p['nilai_atau_url']) ?>;"></div>
            <div class="al-swatch-info">
                <div class="al-color-hex">
                    <span><?= esc($p['nilai_atau_url']) ?></span>
                    <button class="cpb cpb-sec" onclick="copyHex('<?= esc($p['nilai_atau_url'], 'js') ?>')" style="padding:3px 9px; font-size:11.5px; font-weight:600;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy
                    </button>
                </div>
                <div style="font-weight:700; font-size:14px; color:#0f172a; margin:8px 0 3px 0;"><?= esc($p['nama_aset']) ?></div>
                <?php if (!empty($p['keterangan'])): ?>
                <div style="font-size:12px; color:#64748b; line-height:1.4; margin-top:4px;"><?= esc($p['keterangan']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div style="padding:12px 16px; background:#fafafa; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end;">
            <form action="/dashboard/asset-library/delete/<?= $p['id'] ?>" method="POST" onsubmit="return confirm('Hapus warna <?= esc($p['nama_aset'], 'js') ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="cpb cpb-sec" style="padding:4px 10px; font-size:11.5px; font-weight:600; background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Hapus
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ── Section 2: Other Brand Assets Grid ──────────────────── -->
<div style="margin-bottom:14px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Koleksi Aset & Tautan Desain</h2>
    <p style="font-size:13px; color:#64748b; margin:0;">Kumpulan file logo, templat, atau dokumen panduan brand yang ditambahkan tim.</p>
</div>

<div class="al-asset-grid">
    <?php if (empty($otherAssets)): ?>
    <div style="grid-column: 1 / -1;">
        <div class="cp-card" style="padding:32px; text-align:center; color:#64748b;">
            <p style="margin:0; font-size:13.5px; font-weight:600;">Belum ada aset tautan yang tersimpan. Klik "Tambah Aset Baru" untuk menambahkan.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($otherAssets as $oa): ?>
    <div class="cp-card" style="padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                <div style="font-weight:700; font-size:14.5px; color:#0f172a;"><?= esc($oa['nama_aset']) ?></div>
                <span class="cp-badge acc" style="font-size:11px; padding:3px 8px; text-transform:capitalize;"><?= esc($oa['kategori']) ?></span>
            </div>
            <?php if (!empty($oa['keterangan'])): ?>
            <p style="font-size:12.5px; color:#64748b; margin:0 0 16px 0; line-height:1.4;"><?= esc($oa['keterangan']) ?></p>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:8px; align-items:center; margin-top:12px;">
            <a href="<?= esc($oa['nilai_atau_url']) ?>" target="_blank" class="cpb cpb-pri" style="text-decoration:none; justify-content:center; padding:7px 14px; font-size:12px; flex:1;">
                Buka Aset ↗
            </a>
            <form action="/dashboard/asset-library/delete/<?= $oa['id'] ?>" method="POST" onsubmit="return confirm('Hapus aset <?= esc($oa['nama_aset'], 'js') ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="cpb cpb-sec" style="padding:7px 10px; font-size:12px; background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah Aset Baru                                   -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="modalTambahAset">
    <div class="cp-modal modal-md" style="max-width:520px;">
        <div class="cp-mh" style="padding:20px 24px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <div class="cp-mt" style="font-size:16.5px; font-weight:700; color:var(--cp-text);">Tambah Aset Baru</div>
                    <div class="cp-ms" style="font-size:12px; color:var(--cp-muted); margin-top:2px;">Tambahkan warna palette atau tautan aset desain baru.</div>
                </div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('modalTambahAset')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="/dashboard/asset-library/store" method="POST">
            <?= csrf_field() ?>
            <div class="cp-mb" style="padding:20px 24px;">
                <div class="cp-row full" style="margin-bottom:14px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Nama Aset <span style="color:#dc2626">*</span></label>
                        <input type="text" name="nama_aset" class="cp-inp" placeholder="Contoh: Primary Royal Blue atau Figma Feed Kit" required style="padding:10px 14px;">
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:14px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Kategori Aset <span style="color:#dc2626">*</span></label>
                        <select name="kategori" id="selKategori" onchange="gantiKategoriForm(this.value)" class="cp-inp" required style="padding:10px 14px;">
                            <option value="palette">Warna Palette (Kode Hex)</option>
                            <option value="template">Templat Desain (Link Canva/Figma)</option>
                            <option value="logo">Logo / Aset Visual (Link URL)</option>
                            <option value="font">Font Tipografi (Link URL)</option>
                        </select>
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:14px;" id="rowHexPicker">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Pilih Warna (Hex Code) <span style="color:#dc2626">*</span></label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="color" id="pickerWarna" onchange="syncHexText(this.value)" value="#2563eb" style="width:48px; height:42px; padding:2px; border-radius:8px; border:1px solid #cbd5e1; cursor:pointer; flex-shrink:0;">
                            <input type="text" name="nilai_atau_url" id="textHex" class="cp-inp" placeholder="#2563eb" value="#2563eb" required style="padding:10px 14px; font-family:monospace; font-weight:700;">
                        </div>
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:14px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Keterangan / Peruntukan</label>
                        <textarea name="keterangan" class="cp-inp" placeholder="Tuliskan petunjuk peruntukan aset ini..." rows="2" style="padding:10px 14px;"></textarea>
                    </div>
                </div>
            </div>
            <div class="cp-mf" style="padding:16px 24px; background:#fafafa; border-top:1px solid var(--cp-border);">
                <button type="button" class="cpb cpb-out" onclick="tutupModal('modalTambahAset')" style="padding:9px 18px; font-weight:600;">Batal</button>
                <button type="submit" class="cpb cpb-pri" style="padding:9px 20px; font-weight:600;">Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function gantiKategoriForm(kat) {
    const textHex = document.getElementById('textHex');
    if (kat === 'palette') {
        textHex.placeholder = '#2563eb';
        document.getElementById('rowHexPicker').style.display = 'block';
    } else {
        textHex.placeholder = 'https://canva.com/... atau https://figma.com/...';
        document.getElementById('rowHexPicker').style.display = 'block';
    }
}

function syncHexText(val) {
    document.getElementById('textHex').value = val;
}

function copyHex(hex) {
    navigator.clipboard.writeText(hex).then(() => {
        alert('Kode Hex ' + hex + ' telah disalin!');
    }).catch(err => {
        alert('Kode Hex: ' + hex);
    });
}
</script>
<script src="/js/content-plan.js"></script>
<?= $this->endSection() ?>
