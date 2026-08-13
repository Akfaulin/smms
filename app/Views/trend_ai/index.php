<?= $this->extend('layout') ?>

<?= $this->section('head_css') ?>
<link rel="stylesheet" href="/css/content-plan.css">
<link rel="stylesheet" href="/css/ide-konten.css">
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
}
.ta-card:hover {
    transform: translateY(-3px);
    border-color: #cbd5e1;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
}
.ta-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.ta-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}
.ta-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: #eff6ff;
    color: #2563eb;
}
.ta-example {
    background: #f8fafc;
    border-left: 3px solid #2563eb;
    padding: 10px 12px;
    border-radius: 6px;
    font-size: 12.5px;
    color: #334155;
    font-style: italic;
    margin: 12px 0 16px 0;
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
<div class="ik-header">
    <div style="display:flex; align-items:center; gap:12px;">
        <div class="ik-header-icon-badge" style="background:linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); color:#16a34a; box-shadow:0 2px 8px rgba(22, 163, 74, 0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m16 12-4-4-4 4"/><path d="M12 16V8"/></svg>
        </div>
        <div>
            <h1 class="ik-header-title">Bank Trend & Inspirasi AI</h1>
            <p class="ik-header-sub">Koleksi format tren audio/visual terkini, kalender promo musiman, dan Instant Viral Hook Generator AI.</p>
        </div>
    </div>
</div>

<!-- ── Instant AI Hook Generator Section ───────────────────── -->
<div class="cp-card" style="margin-bottom:24px; padding:24px; background:linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border:1.5px solid #cbd5e1;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <div style="width:34px; height:34px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        </div>
        <div>
            <h2 style="font-size:16px; font-weight:800; color:#0f172a; margin:0;">Instant Viral Hook Generator AI</h2>
            <p style="font-size:12.5px; color:#64748b; margin:2px 0 0 0;">Cari ide kalimat pembuka (hook 3 detik pertama) yang menarik perhatian audiens secara otomatis.</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1fr auto; gap:12px; align-items:end;">
        <div>
            <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Topik / Produk / Layanan</label>
            <input type="text" id="hookTopik" class="cp-inp" placeholder="Contoh: Promo Diskon Skincare Kemerdekaan" style="padding:10px 14px;">
        </div>
        <div>
            <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Platform Target</label>
            <select id="hookPlatform" class="cp-inp" style="padding:10px 14px;">
                <option value="Instagram Reels">Instagram Reels</option>
                <option value="TikTok">TikTok</option>
                <option value="Carousel IG">Carousel IG</option>
                <option value="LinkedIn">LinkedIn</option>
            </select>
        </div>
        <button class="cpb cpb-pri" id="btnGenHook" onclick="generateTrendHook()" style="padding:10px 20px; font-weight:600; height:42px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Hook AI
        </button>
    </div>

    <div id="hookResultBox" style="display:none; margin-top:18px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:18px; font-size:13.5px; color:#1e293b;">
        <div style="font-weight:700; margin-bottom:8px; color:#2563eb; display:flex; align-items:center; gap:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Rekomendasi Hook AI Siap Pakai:
        </div>
        <div id="hookResultContent" style="line-height:1.6;"></div>
    </div>
</div>

<!-- ── Format Tren Audio & Visual (Cards) ──────────────────── -->
<div style="margin-bottom:16px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Format Tren Audio & Visual Populer</h2>
    <p style="font-size:13px; color:#64748b; margin:0;">Pilih format tren yang cocok dan langsung buat ide kontennya.</p>
</div>

<div class="ta-grid">
    <?php foreach ($audioTrends as $t): ?>
    <div class="ta-card">
        <div>
            <div class="ta-card-head">
                <div class="ta-card-title"><?= esc($t['judul']) ?></div>
                <span class="ta-badge"><?= esc($t['badge']) ?></span>
            </div>
            <p style="font-size:12.5px; color:#64748b; margin:0; line-height:1.4;"><?= esc($t['desk']) ?></p>
            <div class="ta-example">
                <?= esc($t['example']) ?>
            </div>
        </div>
        <button class="cpb cpb-sec" onclick="pakaiTren('<?= esc($t['judul'], 'js') ?>', '<?= esc($t['example'], 'js') ?>')" style="width:100%; justify-content:center; padding:8px 12px; font-weight:600; font-size:12px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Gunakan Jadi Ide Konten
        </button>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Kalender Event & Promo Musiman ─────────────────────── -->
<div style="margin-bottom:16px; margin-top:32px;">
    <h2 style="font-size:17px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">Kalender Momen Promo & Event Mendatang</h2>
    <p style="font-size:13.5px; color:#64748b; margin:0;">Persiapkan ide konten promo lebih awal sebelum tanggal pelaksanaan.</p>
</div>

<div class="cp-card">
    <table class="cp-table">
        <thead>
            <tr>
                <th style="width:50px; text-align:center;">#</th>
                <th>Tanggal Event</th>
                <th>Nama Momen / Event Promo</th>
                <th>Kategori Promo</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventCalendar as $idx => $ev): ?>
            <tr>
                <td style="text-align:center; color:#64748b; font-weight:600;"><?= $idx + 1 ?></td>
                <td style="font-weight:700; color:#0f172a; white-space:nowrap;"><?= esc($ev['tanggal']) ?></td>
                <td style="font-size:13.5px; color:#334155; font-weight:600;"><?= esc($ev['momen']) ?></td>
                <td>
                    <span style="background:#f1f5f9; color:#334155; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600;"><?= esc($ev['tag']) ?></span>
                </td>
                <td style="text-align:right;">
                    <button class="cpb cpb-sec" onclick="pakaiEvent('<?= esc($ev['momen'], 'js') ?>')" style="padding:6px 14px; font-size:12px; font-weight:600;">
                        + Buat Ide Promo
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
                    <label style="font-size:12px; font-weight:700; color:#374151; margin-bottom:6px; display:block;">Tanggal Publish</label>
                    <input type="date" id="fTanggal" class="cp-inp" style="padding:9px 12px;">
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
function generateTrendHook() {
    const topik = document.getElementById('hookTopik').value.trim();
    const platform = document.getElementById('hookPlatform').value;

    if (!topik) {
        alert('Silakan masukkan topik terlebih dahulu.');
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
            document.getElementById('hookResultContent').innerHTML = renderMarkdown(res.data);
        } else {
            alert('Gagal: ' + (res.pesan || 'Terjadi kesalahan.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:4px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Generate Hook AI';
        alert('Terjadi kesalahan koneksi.');
    });
}

function pakaiTren(judul, brief) {
    bukaFormTambah();
    document.getElementById('fJudul').value = judul;
    document.getElementById('fDesc').value = 'Konsep Format: ' + brief;
}

function pakaiEvent(namaEvent) {
    bukaFormTambah();
    document.getElementById('fJudul').value = 'Promo Momen: ' + namaEvent;
    document.getElementById('fDesc').value = 'Kampanye khusus memperingati ' + namaEvent;
}
</script>
<script src="/js/content-plan.js"></script>
<?= $this->endSection() ?>
