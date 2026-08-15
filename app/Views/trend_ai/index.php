<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
<style>
<style>
.ta-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.ta-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.22s ease;
    position: relative;
}
.ta-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
}
.ta-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}
.ta-card-title {
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
}
.ta-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: #eff6ff;
    color: #2563eb;
    white-space: nowrap;
}
.ta-badge-audio {
    background: #fdf2f8;
    color: #db2777;
}
.ta-badge-hook {
    background: #fef3c7;
    color: #d97706;
}
.ta-badge-capcut {
    background: #f3e8ff;
    color: #9333ea;
}
.ta-example {
    background: #f8fafc;
    border-left: 3px solid #2563eb;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 12px;
    color: #334155;
    font-style: italic;
    margin: 12px 0 16px 0;
    line-height: 1.45;
}
.ta-filter-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.ta-filter-btn {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 20px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}
.ta-filter-btn.active, .ta-filter-btn:hover {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
}
@media (max-width: 992px) {
    .ta-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .ta-grid { grid-template-columns: 1fr; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ── Hero Header ─────────────────────────────────────────── -->
<div class="ik-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); color:#16a34a; box-shadow:0 2px 8px rgba(22, 163, 74, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4"/><path d="M12 16V8"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Bank Trend & Inspirasi AI</h1>
            <p class="ik-header-sub">Koleksi format tren audio/visual terkini yang selalu terupdate, kalender promo musiman dinamis, dan AI Viral Hook Generator.</p>
        </div>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <button type="button" class="cpb cpb-sec" onclick="bukaModal('modalTambahTrend')" style="padding:9px 16px; font-size:12.5px; font-weight:700; border-radius:10px; background:#ffffff; border:1.5px solid #cbd5e1; color:#334155; display:inline-flex; align-items:center; gap:6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            + Tambah Temuan Tren
        </button>
        <button type="button" class="cpb cpb-pri" id="btnScanTrends" onclick="scanTrendsAi()" style="padding:9px 18px; font-size:12.5px; font-weight:700; border-radius:10px; background:linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color:#ffffff; display:inline-flex; align-items:center; gap:6px; box-shadow:0 3px 10px rgba(37,99,235,0.25);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            ⚡ Scan Tren AI Terkini
        </button>
    </div>
</div>

<!-- ── Instant AI Hook Generator Section ───────────────────── -->
<div class="cp-card" style="margin-bottom:24px; padding:22px; background:linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border:1.5px solid #cbd5e1;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <div style="width:34px; height:34px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        </div>
        <div>
            <h2 style="font-size:15.5px; font-weight:800; color:#0f172a; margin:0;">Instant Viral Hook Generator AI</h2>
            <p style="font-size:12px; color:#64748b; margin:2px 0 0 0;">Cari ide kalimat pembuka (hook 3 detik pertama) yang menarik perhatian audiens secara otomatis.</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr auto; gap:12px; align-items:end;">
        <div>
            <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Topik / Produk / Penawaran</label>
            <input type="text" id="hookTopik" class="cp-inp" placeholder="Contoh: Promo Diskon Skincare / Launching Menu Kopi Baru" style="padding:10px 14px;">
        </div>
        <div>
            <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Platform Target</label>
            <select id="hookPlatform" class="cp-inp" style="padding:10px 14px;">
                <option value="Instagram Reels">Instagram Reels</option>
                <option value="TikTok">TikTok</option>
                <option value="Carousel IG">Carousel IG</option>
                <option value="YouTube Shorts">YouTube Shorts</option>
                <option value="LinkedIn">LinkedIn</option>
            </select>
        </div>
        <button class="cpb cpb-pri" id="btnGenHook" onclick="generateTrendHook()" style="padding:10px 20px; font-weight:700; height:42px; background:#2563eb; color:#fff;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Hook AI
        </button>
    </div>

    <div id="hookResultBox" style="display:none; margin-top:18px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; font-size:13.5px; color:#1e293b;">
        <div style="font-weight:700; margin-bottom:8px; color:#2563eb; display:flex; align-items:center; gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Rekomendasi Hook AI Siap Pakai:
        </div>
        <div id="hookResultContent" style="line-height:1.6; white-space:pre-wrap;"></div>
    </div>
</div>

<!-- ── Format Tren Audio & Visual (Cards) ──────────────────── -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
    <div>
        <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 2px 0;">Bank Format Tren & Audio Populer</h2>
        <p style="font-size:12.5px; color:#64748b; margin:0;">Pilih format tren yang cocok dan langsung ubah menjadi ide konten terencana.</p>
    </div>
    <!-- Filter Kategori Tren -->
    <div class="ta-filter-bar" style="margin-bottom:0;">
        <button class="ta-filter-btn active" onclick="filterTrendCards('all', this)">Semua (<?= count($audioTrends) ?>)</button>
        <button class="ta-filter-btn" onclick="filterTrendCards('Audio Viral', this)">Audio Viral</button>
        <button class="ta-filter-btn" onclick="filterTrendCards('Format FYP', this)">Format FYP</button>
        <button class="ta-filter-btn" onclick="filterTrendCards('Hook Trend', this)">Hook Trend</button>
        <button class="ta-filter-btn" onclick="filterTrendCards('POV Format', this)">POV Format</button>
        <button class="ta-filter-btn" onclick="filterTrendCards('CapCut Trend', this)">CapCut Trend</button>
    </div>
</div>

<?php if (empty($audioTrends)): ?>
<div class="cp-card" style="padding:48px 24px; text-align:center; margin-bottom:24px;">
    <div style="width:54px; height:54px; border-radius:50%; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
    </div>
    <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin-bottom:6px;">Belum Ada Tren Tersimpan</h3>
    <p style="font-size:13px; color:#64748b; max-width:480px; margin:0 auto 20px auto;">Jalankan AI Trend Radar untuk menemukan dan mengkurasi tren terkini yang sedang viral sesuai brand Anda.</p>
    <button type="button" class="cpb cpb-pri" onclick="scanTrendsAi()" style="padding:10px 22px; font-weight:700; border-radius:10px; background:#2563eb; color:#fff; display:inline-flex; align-items:center; gap:6px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Scan Tren Sekarang dengan AI
    </button>
</div>
<?php else: ?>
<div class="ta-grid" id="trendGridContainer">
    <?php foreach ($audioTrends as $t): ?>
    <?php 
    $badgeClass = match($t['badge']) {
        'Audio Viral'  => 'ta-badge-audio',
        'Hook Trend'   => 'ta-badge-hook',
        'CapCut Trend' => 'ta-badge-capcut',
        default        => ''
    };
    ?>
    <div class="ta-card ta-trend-item" data-badge="<?= esc($t['badge']) ?>" data-cat="<?= esc($t['category']) ?>">
        <div>
            <div class="ta-card-head">
                <div class="ta-card-title"><?= esc($t['judul']) ?></div>
                <span class="ta-badge <?= $badgeClass ?>"><?= esc($t['badge']) ?></span>
            </div>
            <div style="font-size:11px; color:#64748b; font-weight:600; margin-bottom:8px; display:flex; align-items:center; gap:4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?= esc($t['category'] ?: 'TikTok & Reels') ?>
            </div>
            <p style="font-size:12.5px; color:#475569; margin:0; line-height:1.45;"><?= esc($t['desk']) ?></p>
            <?php if (!empty($t['example'])): ?>
            <div class="ta-example">
                <strong>Contoh Konsep:</strong><br>
                <?= esc($t['example']) ?>
            </div>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:8px; align-items:center; margin-top:14px;">
            <button class="cpb cpb-sec" onclick="pakaiTren('<?= esc($t['judul'], 'js') ?>', '<?= esc($t['example'] ?: $t['desk'], 'js') ?>')" style="flex:1; justify-content:center; padding:8px 12px; font-weight:700; font-size:12px; border-radius:8px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px; margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
                Jadikan Ide Konten
            </button>
            <button type="button" class="cpb" onclick="hapusTren(<?= $t['id'] ?>, '<?= esc(addslashes($t['judul'])) ?>')" style="padding:8px 10px; font-size:12px; border-radius:8px; background:#fff; border:1px solid #e2e8f0; color:#94a3b8;" title="Hapus Tren Ini">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Kalender Event & Promo Musiman Dinamis ──────────────── -->
<div style="margin-bottom:14px; margin-top:32px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 2px 0;">Kalender Momen Promo & Event Mendatang</h2>
    <p style="font-size:12.5px; color:#64748b; margin:0;">Otomatis diselaraskan dengan momen nasional, tanggal kembar e-commerce, dan periode gajian terdekat.</p>
</div>

<div class="cp-card" style="padding:0; overflow:hidden; border:1.5px solid #e2e8f0; border-radius:14px;">
    <table class="cp-table">
        <thead>
            <tr>
                <th style="width:40px; text-align:center;">#</th>
                <th style="width:22%;">Tanggal Event</th>
                <th style="width:40%;">Nama Momen / Event Promo</th>
                <th style="width:20%;">Kategori Momen</th>
                <th style="text-align:right; width:18%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventCalendar as $idx => $ev): ?>
            <tr>
                <td style="text-align:center; color:#64748b; font-weight:600;"><?= $idx + 1 ?></td>
                <td style="font-weight:700; color:#0f172a; white-space:nowrap; font-size:13px;">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?= esc($ev['tanggal']) ?>
                    </div>
                </td>
                <td style="font-size:13.5px; color:#334155; font-weight:700;">
                    <?= esc($ev['momen']) ?>
                </td>
                <td>
                    <span style="background:#eff6ff; color:#2563eb; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700;"><?= esc($ev['tag']) ?></span>
                </td>
                <td style="text-align:right;">
                    <button class="cpb cpb-sec" onclick="pakaiEvent('<?= esc($ev['momen'], 'js') ?>', '<?= esc($ev['tanggal'], 'js') ?>')" style="padding:6px 14px; font-size:12px; font-weight:700; border-radius:8px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;">
                        + Buat Ide Promo
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah Temuan Tren Baru (Manual Form)              -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="modalTambahTrend" style="z-index:9999;">
    <div class="cp-modal" style="max-width:540px;">
        <div class="cp-mh" style="padding:18px 22px;">
            <div>
                <div class="cp-mt" style="font-size:16px; font-weight:800;">+ Tambah Temuan Tren Baru</div>
                <div class="cp-ms" style="font-size:12px; color:#64748b;">Simpan sound atau format viral yang Anda temukan ke Bank Tren tim.</div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('modalTambahTrend')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cp-mb" style="padding:20px 22px;">
            <div class="cp-field" style="margin-bottom:14px;">
                <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Nama Tren / Sound Viral <span style="color:#dc2626">*</span></label>
                <input type="text" id="inTrendJudul" class="cp-inp" placeholder="Contoh: Sound 'Gwenchana' / Format POV Transisi...">
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Kategori Format</label>
                    <select id="inTrendBadge" class="cp-inp">
                        <option value="Audio Viral">Audio Viral</option>
                        <option value="Format FYP">Format FYP</option>
                        <option value="Hook Trend">Hook Trend</option>
                        <option value="POV Format">POV Format</option>
                        <option value="CapCut Trend">CapCut Trend</option>
                    </select>
                </div>
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Platform</label>
                    <select id="inTrendCat" class="cp-inp">
                        <option value="TikTok & Reels">TikTok & Reels</option>
                        <option value="Instagram Feed">Instagram Feed</option>
                        <option value="YouTube Shorts">YouTube Shorts</option>
                    </select>
                </div>
            </div>
            <div class="cp-field" style="margin-bottom:14px;">
                <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Penjelasan / Kenapa Sedang Viral</label>
                <textarea id="inTrendDesk" class="cp-inp" rows="2" placeholder="Jelaskan karakteristik tren atau beat musiknya..."></textarea>
            </div>
            <div class="cp-field" style="margin-bottom:14px;">
                <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Contoh Konsep / Implementasi Brand</label>
                <textarea id="inTrendExample" class="cp-inp" rows="2" placeholder="Contoh cara pakai untuk produk atau promosi..."></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="cpb cpb-sec" onclick="tutupModal('modalTambahTrend')" style="padding:8px 16px; font-size:12.5px; font-weight:600;">Batal</button>
                <button type="button" class="cpb cpb-pri" id="btnSimpanTrendManual" onclick="simpanTrendManual()" style="padding:8px 20px; font-size:12.5px; font-weight:700; background:#2563eb; color:#fff;">Simpan ke Bank Tren</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah Ide Konten (Auto Fill From Trend)           -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="cp-back" id="backForm">
    <div class="cp-modal modal-md" style="max-width:580px;">
        <div class="cp-mh" style="padding:20px 24px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); color:#2563eb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                </div>
                <div>
                    <div class="cp-mt" id="formModalTitle" style="font-size:17px; font-weight:700; color:var(--cp-text);">Ajukan Ide Konten</div>
                    <div class="cp-ms" style="font-size:12.5px; color:var(--cp-muted); margin-top:2px;">Status awal: <span class="cp-badge draft" style="font-size:11px; padding:2px 8px; font-weight:600;">Ide Diajukan</span></div>
                </div>
            </div>
            <button class="cp-mcls" onclick="tutupModal('backForm')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="cp-mb" style="padding:20px 24px;">
            <div class="cp-row full" style="margin-bottom:16px;">
                <div class="cp-field">
                    <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Judul Konten <span style="color:#dc2626">*</span></label>
                    <input type="text" id="fJudul" class="cp-inp" placeholder="Contoh: Promo Spesial Kemerdekaan 17 Agustus" maxlength="200" style="padding:10px 14px; font-size:13.5px;">
                </div>
            </div>

            <div class="cp-row cp-row-3col" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Jadwal & Jam Publish</label>
                    <input type="datetime-local" id="fTanggal" class="cp-inp" style="padding:9px 12px;">
                </div>
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Jenis Konten</label>
                    <select id="fJenis" class="cp-inp" style="padding:9px 12px;">
                        <option value="">— Pilih —</option>
                        <?php foreach ($jenisKonten as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= esc($j['nama_jenis']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="cp-field">
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Content Pillar</label>
                    <select id="fPillar" class="cp-inp" style="padding:9px 12px;">
                        <option value="">— Pilih —</option>
                        <?php foreach ($contentTypes as $ct): ?>
                        <option value="<?= $ct['id'] ?>"><?= esc($ct['nama_type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="cp-row full" style="margin-bottom:16px;">
                <div class="cp-field">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:12.5px; font-weight:700; color:#374151; margin:0;">Deskripsi / Brief Ide</label>
                        <button type="button" class="cpb cpb-sec" id="btnAiBrief" style="padding:4px 10px; font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border-radius:6px; background:#e0f2fe; border:1px solid #bae6fd; color:#0284c7;" onclick="generateAiBrief()" title="Bantu tulis brief otomatis dengan AI berdasarkan Judul Konten">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            Bantu Tulis Brief AI
                        </button>
                    </div>
                    <textarea id="fDesc" class="cp-inp" placeholder="Tulis deskripsi singkat ide, poin penting, atau konsep visual..." rows="3" style="padding:10px 14px; min-height:85px;"></textarea>
                </div>
            </div>

            <div class="cp-row full" style="margin-bottom:16px;">
                <div class="cp-field">
                    <label style="font-size:12.5px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Platform Tujuan</label>
                    <div class="cp-plat-wrap" id="fPlatforms">
                        <?php foreach ($platforms as $p): ?>
                        <label class="cp-plat-lbl" id="plat-lbl-<?= $p['id'] ?>">
                            <input type="checkbox" class="plat-cb" value="<?= $p['id'] ?>">
                            <span><?= esc($p['nama_platform']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="cp-mf" style="padding:16px 24px; background:#fafafa; border-top:1px solid var(--cp-border);">
            <button class="cpb cpb-out" onclick="tutupModal('backForm')" style="padding:9px 18px; font-weight:600;">Batal</button>
            <button class="cpb cpb-pri" id="btnSimpanIde" onclick="simpanIde()" style="padding:9px 20px; font-weight:600;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-2px; margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Ajukan Ide Konten
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Filter Kategori Kartu Tren
function filterTrendCards(badge, btn) {
    document.querySelectorAll('.ta-filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const cards = document.querySelectorAll('.ta-trend-item');
    cards.forEach(card => {
        if (badge === 'all') {
            card.style.display = '';
        } else {
            const b = card.getAttribute('data-badge') || '';
            card.style.display = (b.toLowerCase().includes(badge.toLowerCase())) ? '' : 'none';
        }
    });
}

// ⚡ AI Trend Radar Scan (Point 2)
async function scanTrendsAi() {
    const btn = document.getElementById('btnScanTrends');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="cp-spin"></span> Scanning Tren AI...';

    try {
        const csrfToken = (typeof getCsrfToken === 'function' ? getCsrfToken() : (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''));
        const response = await fetch('/dashboard/trend-ai/scan-trends', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: 'platform=TikTok & Reels'
        });

        const res = await response.json();
        if (res.sukses) {
            toast(res.pesan || 'AI Trend Radar berhasil mengupdate tren terbaru!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            toast(res.pesan || 'Gagal melakukan scan tren AI.', 'error');
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi.', 'error');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// Simpan Temuan Tren Manual
async function simpanTrendManual() {
    const judul = document.getElementById('inTrendJudul')?.value.trim();
    if (!judul) {
        toast('Nama tren wajib diisi.', 'error');
        return;
    }

    const badge = document.getElementById('inTrendBadge')?.value;
    const category = document.getElementById('inTrendCat')?.value;
    const desk = document.getElementById('inTrendDesk')?.value.trim();
    const example = document.getElementById('inTrendExample')?.value.trim();

    const btn = document.getElementById('btnSimpanTrendManual');
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    try {
        const csrfToken = (typeof getCsrfToken === 'function' ? getCsrfToken() : (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''));
        const response = await fetch('/dashboard/trend-ai/store-trend', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: `judul=${encodeURIComponent(judul)}&badge=${encodeURIComponent(badge)}&category=${encodeURIComponent(category)}&desk=${encodeURIComponent(desk)}&example=${encodeURIComponent(example)}`
        });

        const res = await response.json();
        if (res.sukses) {
            tutupModal('modalTambahTrend');
            toast('Tren baru berhasil ditambahkan!', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            toast(res.pesan || 'Gagal menyimpan tren.', 'error');
            btn.disabled = false;
            btn.textContent = 'Simpan ke Bank Tren';
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi.', 'error');
        btn.disabled = false;
        btn.textContent = 'Simpan ke Bank Tren';
    }
}

// Hapus Tren
async function hapusTren(id, judul) {
    if (!confirm(`Hapus tren "${judul}" dari Bank Tren?`)) return;

    try {
        const csrfToken = (typeof getCsrfToken === 'function' ? getCsrfToken() : (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''));
        const response = await fetch(`/dashboard/trend-ai/delete-trend/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const res = await response.json();
        if (res.sukses) {
            toast('Tren berhasil dihapus.', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            toast(res.pesan || 'Gagal menghapus tren.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan koneksi.', 'error');
    }
}

// Generate Hook AI
function generateTrendHook() {
    const topik = document.getElementById('hookTopik').value.trim();
    const platform = document.getElementById('hookPlatform').value;

    if (!topik) {
        toast('Silakan masukkan topik terlebih dahulu.', 'error');
        return;
    }

    const btn = document.getElementById('btnGenHook');
    btn.disabled = true;
    btn.textContent = 'Generating...';

    fetch('/dashboard/trend-ai/generate-hook', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (typeof getCsrfToken === 'function' ? getCsrfToken() : (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''))
        },
        body: 'topik=' + encodeURIComponent(topik) + '&platform=' + encodeURIComponent(platform)
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Hook AI';
        if (res.sukses) {
            document.getElementById('hookResultBox').style.display = 'block';
            document.getElementById('hookResultContent').textContent = res.data;
            toast('Rekomendasi viral hook berhasil dibuat!', 'success');
        } else {
            toast(res.pesan || 'Gagal generate hook.', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Hook AI';
        toast('Terjadi kesalahan koneksi.', 'error');
    });
}

function pakaiTren(judul, brief) {
    bukaFormTambah();
    document.getElementById('fJudul').value = judul;
    document.getElementById('fDesc').value = 'Konsep Format: ' + brief;
}

function pakaiEvent(namaEvent, tglEvent) {
    bukaFormTambah();
    document.getElementById('fJudul').value = 'Promo Momen: ' + namaEvent;
    document.getElementById('fDesc').value = 'Kampanye khusus memperingati ' + namaEvent + ' (' + tglEvent + ')';
}
</script>
<script src="/js/content-plan.js"></script>
<?= $this->endSection() ?>
