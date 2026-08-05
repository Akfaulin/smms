<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($judul ?? 'Dashboard') ?> — SMMS</title>
    <meta name="description" content="Sistem Manajemen Media Sosial Internal Tim">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
    <?= $this->renderSection('head_css') ?>
</head>
<body>

<!-- Sidebar Partial -->
<?= $this->include('partials/sidebar') ?>

<!-- Main Container -->
<main class="main">
    <!-- Topbar Partial -->
    <?= $this->include('partials/topbar') ?>

    <div class="page-content">
        <?= $this->renderSection('content') ?>
    </div>
</main>

<!-- Custom Confirm Modal Partial -->
<?= $this->include('partials/modal_confirm') ?>

<!-- Toast container -->
<div id="cp-toast"></div>

<!-- Global Application Script -->
<script src="/js/app.js"></script>

<!-- Session Flash Messages -->
<script>
<?php if (session('sukses')): ?>
document.addEventListener('DOMContentLoaded', () => toast(<?= json_encode(session('sukses')) ?>, 'success'));
<?php endif; ?>
<?php if (session('error')): ?>
document.addEventListener('DOMContentLoaded', () => toast(<?= json_encode(session('error')) ?>, 'error'));
<?php endif; ?>
</script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
