<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/master-data.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $bisnisNama  = session('bisnis_aktif_nama') ?? 'Default';
    $bisnisWarna = session('bisnis_aktif_warna') ?? '#6C5CE7';
?>

<!-- Header Info Bisnis Aktif -->
<div class="ms-bisnis-header" style="background:#ffffff; border:1.5px solid #e2e8f0; border-radius:14px; padding:14px 20px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between;">
    <div style="display:flex; align-items:center; gap:10px;">
        <span style="width:12px; height:12px; border-radius:50%; background:<?= esc($bisnisWarna) ?>; display:inline-block; box-shadow:0 0 6px rgba(0,0,0,0.15);"></span>
        <span style="font-size:0.88rem; font-weight:700; color:#0f172a;">
            Master Data untuk: <strong style="color:<?= esc($bisnisWarna) ?>;"><?= esc($bisnisNama) ?></strong>
        </span>
    </div>
    <span style="font-size:0.75rem; color:#64748b; font-weight:500;">
        Ganti bisnis aktif di topbar jika ingin mengelola master data bisnis lain.
    </span>
</div>

<!-- Prevent Tab Flash (FOUC) on Page Load -->
<script>
(function() {
    var savedTab = sessionStorage.getItem('active_master_tab') || 'plat';
    document.write('<style>#p_' + savedTab + ' { display: block !important; opacity: 1 !important; } .ms-tab-panel:not(#p_' + savedTab + ') { display: none !important; opacity: 0 !important; }</style>');
})();
</script>

<div class="ms-card">
    <div class="ms-tabs">
        <button class="ms-tab" id="tab_plat" onclick="switchTab('plat', this)">Platform Medsos</button>
        <button class="ms-tab" id="tab_jenis" onclick="switchTab('jenis', this)">Jenis Konten</button>
        <button class="ms-tab" id="tab_pillar" onclick="switchTab('pillar', this)">Content Pillar</button>
    </div>

    <!-- Panel Platform -->
    <div class="ms-tab-panel active" id="p_plat">
        <div class="ms-header">
            <div class="ms-title">Platform Sosial Media (<?= esc($bisnisNama) ?>)</div>
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
            <div class="ms-title">Jenis Konten / Format (<?= esc($bisnisNama) ?>)</div>
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
            <div class="ms-title">Content Pillar / Kategori (<?= esc($bisnisNama) ?>)</div>
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

<?= $this->section('scripts') ?>
<script src="/js/master-data.js"></script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
