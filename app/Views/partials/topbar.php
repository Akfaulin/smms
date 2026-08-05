<!-- ═══════════════════════════════════════════════════════ -->
<!-- TOPBAR COMPONENT                                          -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="topbar">
    <div class="topbar-left">
        <h1><?= esc($judul ?? 'Dashboard') ?></h1>
        <div class="topbar-breadcrumb">Dashboard / <?= esc($judul ?? '') ?></div>
    </div>
    <?= $topbar_right ?? '' ?>
</div>
