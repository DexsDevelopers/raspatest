<?php
/* ─── app_head.php ─── Shared <head> for all premium pages ─── */
if (!isset($pageTitle)) $pageTitle = 'LunarPay';
if (!isset($bodyClass)) $bodyClass = '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — 🍀 RaspaPix</title>
<meta name="theme-color" content="#07070f">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- AOS -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

<!-- Main CSS -->
<link rel="stylesheet" href="/assets/css/main.css">

<!-- GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>

<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

<!-- PixiJS (available for games) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.3.2/pixi.min.js" defer></script>

<?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<!-- Particles Canvas -->
<canvas id="particles-canvas"></canvas>

<!-- Toast Container -->
<div id="toast-container"></div>

<!-- Sidebar Overlay (mobile) -->
<div class="overlay-sidebar" id="sidebar-overlay"></div>
