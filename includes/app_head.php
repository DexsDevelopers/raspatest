<?php /* ─── app_head.php ─── Shared HTML head ─── */
if(!isset($pageTitle)) $pageTitle='LunarPay';
if(!isset($bodyClass)) $bodyClass='';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=htmlspecialchars($pageTitle)?> — 🍀 RaspaPix</title>
<meta name="theme-color" content="#07070f">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<link rel="stylesheet" href="/assets/css/main.css">
<!-- GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<!-- AOS + Lucide + PixiJS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.3.2/pixi.min.js" defer></script>
<?php if(isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?=htmlspecialchars($bodyClass)?>">

<!-- Ambient bg -->
<div class="ambient" aria-hidden="true">
  <div class="ambient-orb ambient-orb-1"></div>
  <div class="ambient-orb ambient-orb-2"></div>
</div>

<!-- Particles -->
<canvas id="particles-canvas" aria-hidden="true"></canvas>

<!-- Page Loader -->
<div id="lp-loader" aria-hidden="true">
  <div class="loader-logo">
    <img src="<?=htmlspecialchars($logoSite??'')?>" alt="RaspaPix" style="height:44px;object-fit:contain" onerror="this.outerHTML='<span style=font-weight:900;font-size:1.4rem>🍀 RaspaPix</span>'">
  </div>
  <div class="loader-bar"><div class="loader-fill"></div></div>
  <div class="loader-text">Carregando plataforma...</div>
</div>

<!-- Toasts -->
<div id="toast-root"></div>

<!-- Sidebar mobile overlay -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>
