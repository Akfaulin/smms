<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<style>
.al-guideline-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 18px;
    padding: 24px;
    margin-bottom: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
}
.al-guideline-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
}
.al-guideline-empty {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 18px;
    padding: 40px 24px;
    text-align: center;
    margin-bottom: 32px;
    transition: all 0.2s ease;
}
.al-guideline-empty:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}
.al-pdf-frame {
    width: 100%;
    height: 520px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #525659;
}
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
    height: 100px;
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
    .al-pdf-frame { height: 400px; }
}
@media (max-width: 576px) {
    .al-swatch-grid { grid-template-columns: 1fr; }
    .al-asset-grid { grid-template-columns: 1fr; }
    .al-pdf-frame { height: 320px; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Flash Messages ──────────────────────────────────────── -->
<?php if (session()->getFlashdata('sukses')): ?>
<div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; padding:12px 18px; border-radius:12px; font-weight:600; font-size:13.5px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <?= session()->getFlashdata('sukses') ?>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:12px 18px; border-radius:12px; font-weight:600; font-size:13.5px; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<div class="ik-header">
    <div style="display:flex; align-items:center; gap:14px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color:#2563eb; box-shadow:0 2px 10px rgba(37,99,235,0.15);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                <h1 class="ik-header-title">Brand Kit & Asset Library</h1>
                <span class="cp-badge in_progress" style="font-size:11px; padding:3px 9px; font-weight:700; border-radius:20px; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;"><?= esc($namaBisnis) ?></span>
            </div>
            <p class="ik-header-sub">Pedoman resmi Brand Guidelines (PDF Viewer), palet warna hex, dan tautan templat visual untuk tim desain.</p>
        </div>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="cpb cpb-sec" onclick="bukaModal('modalUploadGuideline')" style="padding:9px 16px; font-weight:700; border-radius:10px; background:#f8fafc; border:1.5px solid #cbd5e1; color:#334155; display:inline-flex; align-items:center; gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="12 18 12 12 9 15"/><polyline points="12 12 15 15"/></svg>
            <?= empty($guidelines) ? 'Upload PDF Guidelines' : 'Kelola PDF Guidelines' ?>
        </button>
        <button class="cpb cpb-pri" onclick="bukaModal('modalTambahAset')" style="padding:9px 18px; font-weight:700; border-radius:10px; background:#2563eb; color:#fff; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(37,99,235,0.25);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Aset / Warna
        </button>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- SECTION 1: BRAND GUIDELINES PDF VIEWER (POIN 13)           -->
<!-- ══════════════════════════════════════════════════════════ -->
<div style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <div style="display:flex; align-items:center; gap:8px;">
            <h2 style="font-size:17.5px; font-weight:800; color:#0f172a; margin:0;">Buku Pedoman Brand (PDF Guidelines)</h2>
            <span style="background:#f1f5f9; color:#475569; font-size:11px; font-weight:700; padding:2px 8px; border-radius:6px;">Poin 13</span>
        </div>
        <p style="font-size:13px; color:#64748b; margin:4px 0 0 0;">Dokumen panduan visual resmi (Logo usage, typography, tone of voice) khusus bisnis <strong><?= esc($namaBisnis) ?></strong>.</p>
    </div>
</div>

<?php if (empty($guidelines)): ?>
<!-- Placeholder / Empty Dropzone Upload State -->
<div class="al-guideline-empty">
    <div style="width:64px; height:64px; border-radius:16px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto; box-shadow:0 4px 12px rgba(37,99,235,0.12);">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    </div>
    <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">Belum Ada Dokumen Brand Guidelines PDF</h3>
    <p style="font-size:13px; color:#64748b; max-width:540px; margin:0 auto 20px auto; line-height:1.5;">
        Unggah file PDF pedoman brand resmi (maks. 25MB) atau sematkan link Google Drive agar seluruh Content Creator dan Desainer dapat merujuk standar visual brand <strong><?= esc($namaBisnis) ?></strong>.
    </p>
    <button type="button" class="cpb cpb-pri" onclick="bukaModal('modalUploadGuideline')" style="padding:10px 22px; font-size:13px; font-weight:700; border-radius:10px; background:#2563eb; color:#fff; display:inline-flex; align-items:center; gap:6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload File PDF Guidelines
    </button>
</div>
<?php else: ?>
<!-- Active Brand Guidelines Viewer -->
<?php foreach ($guidelines as $idx => $g): ?>
<div class="al-guideline-card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:12px; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; flex-shrink:0; border:1px solid #fecaca;">
                PDF
            </div>
            <div>
                <div style="font-weight:800; font-size:16px; color:#0f172a;"><?= esc($g['nama_aset']) ?></div>
                <div style="font-size:12.5px; color:#64748b; margin-top:2px;">
                    <?= esc($g['keterangan'] ?: 'Dokumen pedoman identitas brand resmi.') ?> &bull; 
                    <span style="color:#059669; font-weight:600;">Aktif &bull; Diperbarui <?= date('d M Y, H:i', strtotime($g['created_at'])) ?> WIB</span>
                </div>
            </div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <a href="<?= esc($g['nilai_atau_url']) ?>" target="_blank" rel="noopener noreferrer" class="cpb cpb-sec" style="padding:7px 14px; font-size:12.5px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px; border-radius:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Buka Tab Penuh ↗
            </a>
            <a href="<?= esc($g['nilai_atau_url']) ?>" download class="cpb cpb-pri" style="padding:7px 14px; font-size:12.5px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px; background:#059669; border-radius:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download PDF
            </a>
            <form action="/dashboard/asset-library/delete/<?= $g['id'] ?>" method="POST" onsubmit="return confirm('Hapus dokumen Brand Guidelines ini?')">
                <?= csrf_field() ?>
                <button type="submit" class="cpb cpb-sec" style="padding:7px 10px; font-size:12px; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:8px;" title="Hapus Dokumen">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Embedded Interactive PDF Viewer Frame -->
    <div>
        <?php if (str_contains($g['nilai_atau_url'], 'drive.google.com')): ?>
            <iframe src="<?= esc(str_replace('/view', '/preview', str_replace('?usp=sharing', '', $g['nilai_atau_url']))) ?>" class="al-pdf-frame" allow="autoplay"></iframe>
        <?php else: ?>
            <iframe src="<?= esc($g['nilai_atau_url']) ?>#toolbar=1" class="al-pdf-frame"></iframe>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- SECTION 2: COLOR PALETTE GRID                              -->
<!-- ══════════════════════════════════════════════════════════ -->
<div style="margin-bottom:14px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Palet Warna Brand (Color Swatches)</h2>
    <p style="font-size:13px; color:#64748b; margin:0;">Warna resmi brand yang dapat disalin Hex Code-nya untuk materi desain media sosial.</p>
</div>

<div class="al-swatch-grid">
    <?php if (empty($palettes)): ?>
    <div style="grid-column: 1 / -1;">
        <div class="cp-card" style="padding:28px; text-align:center; color:#64748b; background:#fff; border-radius:14px; border:1px dashed #cbd5e1;">
            <p style="margin:0; font-size:13.5px; font-weight:600;">Belum ada warna palet tersimpan untuk bisnis ini. Klik "Tambah Aset / Warna" di atas.</p>
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
                    <button class="cpb cpb-sec" onclick="copyHex('<?= esc($p['nilai_atau_url'], 'js') ?>')" style="padding:3px 9px; font-size:11.5px; font-weight:700; border-radius:6px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy
                    </button>
                </div>
                <div style="font-weight:800; font-size:14px; color:#0f172a; margin:8px 0 3px 0;"><?= esc($p['nama_aset']) ?></div>
                <?php if (!empty($p['keterangan'])): ?>
                <div style="font-size:12px; color:#64748b; line-height:1.4; margin-top:4px;"><?= esc($p['keterangan']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div style="padding:10px 16px; background:#fafafa; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end;">
            <form action="/dashboard/asset-library/delete/<?= $p['id'] ?>" method="POST" onsubmit="return confirm('Hapus warna <?= esc($p['nama_aset'], 'js') ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="cpb cpb-sec" style="padding:4px 10px; font-size:11.5px; font-weight:600; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:6px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1px;margin-right:2px"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Hapus
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- SECTION 3: OTHER BRAND ASSETS (TEMPLATES, LOGOS, FONTS)   -->
<!-- ══════════════════════════════════════════════════════════ -->
<div style="margin-bottom:14px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Koleksi Templat & Tautan Desain</h2>
    <p style="font-size:13px; color:#64748b; margin:0;">Kumpulan tautan Canva, Figma design kit, file logo vektor, dan tipografi resmi.</p>
</div>

<div class="al-asset-grid">
    <?php if (empty($otherAssets)): ?>
    <div style="grid-column: 1 / -1;">
        <div class="cp-card" style="padding:28px; text-align:center; color:#64748b; background:#fff; border-radius:14px; border:1px dashed #cbd5e1;">
            <p style="margin:0; font-size:13.5px; font-weight:600;">Belum ada templat atau link desain tersimpan. Klik "Tambah Aset / Warna" untuk menambahkan.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($otherAssets as $oa): ?>
    <div class="cp-card" style="padding:20px; display:flex; flex-direction:column; justify-content:space-between; border-radius:14px;">
        <div>
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                <div style="font-weight:800; font-size:14.5px; color:#0f172a;"><?= esc($oa['nama_aset']) ?></div>
                <span class="cp-badge acc" style="font-size:11px; padding:3px 8px; text-transform:capitalize; border-radius:6px; font-weight:700;"><?= esc($oa['kategori']) ?></span>
            </div>
            <?php if (!empty($oa['keterangan'])): ?>
            <p style="font-size:12.5px; color:#64748b; margin:0 0 16px 0; line-height:1.4;"><?= esc($oa['keterangan']) ?></p>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:8px; align-items:center; margin-top:12px;">
            <a href="<?= esc($oa['nilai_atau_url']) ?>" target="_blank" rel="noopener noreferrer" class="cpb cpb-pri" style="text-decoration:none; justify-content:center; padding:7px 14px; font-size:12px; font-weight:700; flex:1; border-radius:8px; background:#2563eb;">
                Buka Tautan ↗
            </a>
            <form action="/dashboard/asset-library/delete/<?= $oa['id'] ?>" method="POST" onsubmit="return confirm('Hapus aset <?= esc($oa['nama_aset'], 'js') ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="cpb cpb-sec" style="padding:7px 10px; font-size:12px; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:8px;" title="Hapus Aset">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: UPLOAD BRAND GUIDELINES PDF (POIN 13)               -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="modalUploadGuideline">
    <div class="cp-modal modal-md" style="max-width:540px;">
        <div class="cp-mh" style="padding:20px 24px; border-bottom:1px solid var(--cp-border);">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:38px; height:38px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <div class="cp-mt" style="font-size:16.5px; font-weight:800; color:var(--cp-text);">Upload Brand Guidelines PDF</div>
                    <div class="cp-ms" style="font-size:12px; color:var(--cp-muted); margin-top:2px;">Bisnis Aktif: <strong><?= esc($namaBisnis) ?></strong></div>
                </div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('modalUploadGuideline')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form action="/dashboard/asset-library/upload-guideline" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="cp-mb" style="padding:22px 24px;">
                <div class="cp-row full" style="margin-bottom:16px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Nama Dokumen Panduan</label>
                        <input type="text" name="nama_aset" class="cp-inp" placeholder="Contoh: Brand Guidelines & Visual Identity 2026" value="Brand Guidelines & Visual Identity" required style="padding:10px 14px; font-size:13px;">
                    </div>
                </div>

                <!-- Pilihan 1: Upload File PDF -->
                <div class="cp-row full" style="margin-bottom:16px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">
                            Opsi 1: Upload File PDF Langsung (Maks. 25MB)
                        </label>
                        <input type="file" name="pdf_file" accept="application/pdf" class="cp-inp" style="padding:8px 12px; font-size:12.5px;">
                        <span style="font-size:11px; color:#64748b; margin-top:4px; display:block;">Pilih file dokumen PDF pedoman brand yang ingin disimpan ke server.</span>
                    </div>
                </div>

                <div style="text-align:center; margin:10px 0; color:#94a3b8; font-size:11px; font-weight:700; position:relative;">
                    <span style="background:#fff; padding:0 12px; position:relative; z-index:1;">ATAU</span>
                    <div style="position:absolute; top:50%; left:0; right:0; height:1px; background:#e2e8f0; z-index:0;"></div>
                </div>

                <!-- Pilihan 2: Link Google Drive / Cloud PDF -->
                <div class="cp-row full" style="margin-bottom:16px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">
                            Opsi 2: Link Google Drive / URL Publik PDF
                        </label>
                        <input type="url" name="pdf_url" class="cp-inp" placeholder="https://drive.google.com/file/d/.../view" style="padding:10px 14px; font-size:13px;">
                        <span style="font-size:11px; color:#64748b; margin-top:4px; display:block;">Tempelkan link share Google Drive ("Anyone with the link").</span>
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:6px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Catatan / Keterangan</label>
                        <textarea name="keterangan" class="cp-inp" placeholder="Tuliskan petunjuk atau catatan penggunaan pedoman ini..." rows="2" style="padding:10px 14px; font-size:13px;">Pedoman resmi penggunaan logo, tipografi, warna palet, dan tone of voice media sosial.</textarea>
                    </div>
                </div>
            </div>
            <div class="cp-mf" style="padding:16px 24px; background:#fafafa; border-top:1px solid var(--cp-border);">
                <button type="button" class="cpb cpb-out" onclick="tutupModal('modalUploadGuideline')" style="padding:9px 18px; font-weight:600;">Batal</button>
                <button type="submit" class="cpb cpb-pri" style="padding:9px 22px; font-weight:700; background:#2563eb;">Simpan PDF Guidelines</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: TAMBAH ASET BARU (WARNA / TEMPLAT / LOGO)          -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="modalTambahAset">
    <div class="cp-modal modal-md" style="max-width:520px;">
        <div class="cp-mh" style="padding:20px 24px; border-bottom:1px solid var(--cp-border);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <div class="cp-mt" style="font-size:16.5px; font-weight:800; color:var(--cp-text);">Tambah Aset Baru</div>
                    <div class="cp-ms" style="font-size:12px; color:var(--cp-muted); margin-top:2px;">Bisnis: <strong><?= esc($namaBisnis) ?></strong></div>
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
                        <input type="text" name="nama_aset" class="cp-inp" placeholder="Contoh: Primary Royal Blue atau Figma Feed Kit" required style="padding:10px 14px; font-size:13px;">
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:14px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Kategori Aset <span style="color:#dc2626">*</span></label>
                        <select name="kategori" id="selKategori" onchange="gantiKategoriForm(this.value)" class="cp-inp" required style="padding:10px 14px; font-size:13px;">
                            <option value="palette">Warna Palette (Kode Hex)</option>
                            <option value="template">Templat Desain (Link Canva/Figma)</option>
                            <option value="logo">Logo / Aset Visual (Link URL)</option>
                            <option value="font">Font Tipografi (Link URL)</option>
                            <option value="ikon">Icon Pack / Ilustrasi (Link URL)</option>
                        </select>
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:14px;" id="rowHexPicker">
                    <div class="cp-field">
                        <label id="lblNilaiAset" style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Pilih Warna (Hex Code) <span style="color:#dc2626">*</span></label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="color" id="pickerWarna" onchange="syncHexText(this.value)" value="#2563eb" style="width:48px; height:42px; padding:2px; border-radius:8px; border:1px solid #cbd5e1; cursor:pointer; flex-shrink:0;">
                            <input type="text" name="nilai_atau_url" id="textHex" class="cp-inp" placeholder="#2563eb" value="#2563eb" required style="padding:10px 14px; font-family:monospace; font-weight:700; font-size:13px;">
                        </div>
                    </div>
                </div>

                <div class="cp-row full" style="margin-bottom:6px;">
                    <div class="cp-field">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Keterangan / Peruntukan</label>
                        <textarea name="keterangan" class="cp-inp" placeholder="Tuliskan petunjuk peruntukan aset ini..." rows="2" style="padding:10px 14px; font-size:13px;"></textarea>
                    </div>
                </div>
            </div>
            <div class="cp-mf" style="padding:16px 24px; background:#fafafa; border-top:1px solid var(--cp-border);">
                <button type="button" class="cpb cpb-out" onclick="tutupModal('modalTambahAset')" style="padding:9px 18px; font-weight:600;">Batal</button>
                <button type="submit" class="cpb cpb-pri" style="padding:9px 20px; font-weight:700; background:#2563eb;">Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function gantiKategoriForm(kat) {
    const textHex = document.getElementById('textHex');
    const picker = document.getElementById('pickerWarna');
    const lbl = document.getElementById('lblNilaiAset');

    if (kat === 'palette') {
        lbl.innerHTML = 'Pilih Warna (Hex Code) <span style="color:#dc2626">*</span>';
        picker.style.display = 'block';
        textHex.placeholder = '#2563eb';
        textHex.value = picker.value || '#2563eb';
    } else {
        lbl.innerHTML = 'URL / Tautan Aset (Canva / Figma / Drive / Web) <span style="color:#dc2626">*</span>';
        picker.style.display = 'none';
        textHex.placeholder = 'https://canva.com/... atau https://figma.com/...';
        textHex.value = '';
    }
}

function syncHexText(val) {
    const textHex = document.getElementById('textHex');
    if (textHex) textHex.value = val.toUpperCase();
}

function copyHex(hex) {
    navigator.clipboard.writeText(hex).then(() => {
        toast('Hex code ' + hex + ' berhasil disalin ke clipboard!', 'success');
    }).catch(() => {
        toast('Gagal menyalin kode warna.', 'error');
    });
}

function bukaModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
}

function tutupModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('show');
}
</script>
<?= $this->endSection() ?>
