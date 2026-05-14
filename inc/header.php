<?php
@session_start();

// Load DB connection from wherever we are
foreach(['./conexao.php','../conexao.php','../../conexao.php'] as $_p){
  if(file_exists($_p)){ require_once $_p; break; }
}

// Buscar configuraÃ§Ãµes do site
try {
   $stmt = $pdo->prepare("SELECT * FROM config WHERE id = 1 LIMIT 1");
   $stmt->execute();
   $config = $stmt->fetch(PDO::FETCH_ASSOC);
   
   $nomeSite = $config['nome_site'] ?? 'Raspadinha';
   $logoSite = $config['logo'] ?? null;
   $depositoMin = $config['deposito_min'] ?? 0;
   $saqueMin = $config['saque_min'] ?? 0;
   $cpaPadrao = $config['cpa_padrao'] ?? 0;
   $revshare_padrao = $config['revshare_padrao'] ?? 0;
} catch (PDOException $e) {
   $nomeSite = 'Raspadinha';
   $logoSite = null;
   $depositoMin = 0;
   $saqueMin = 0;
   $cpaPadrao = 0;
   $revshare_padrao = 0;
}

if (isset($_SESSION['usuario_id'])) {
   $usuario_id = $_SESSION['usuario_id'];

   try {
       $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
       $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
       $stmt->execute();

       $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

       if (!$usuario) {
           $_SESSION['message'] = ['type' => 'failure', 'text' => 'UsuÃ¡rio NÃ£o existe!'];
       }

      if($usuario['banido'] == 1){
        session_destroy(); header('Location: /login.php'); exit;
      }
   } catch (PDOException $e) { }
}
$isLogged = isset($_SESSION['usuario_id']) && isset($usuario) && $usuario;
$saldo    = $usuario['saldo'] ?? 0;
$nomeUser = $usuario['nome']  ?? '';
$vipLevel = 1;
?>
<link rel="stylesheet" href="/assets/css/main.css?v=3">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<style>
body{background:var(--bg-primary,#07070f)!important;color:var(--text,#e8eaf0)!important;font-family:Inter,sans-serif!important;margin:0!important;padding:0!important}
.app-download-banner,.close-banner,.mobile-menu-btn{display:none!important}
</style>

<div class="ambient" aria-hidden="true"><div class="ambient-orb ambient-orb-1"></div><div class="ambient-orb ambient-orb-2"></div></div>
<canvas id="particles-canvas" aria-hidden="true"></canvas>
<div id="toast-root"></div>
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<nav class="navbar" role="navigation">
  <button id="sb-toggle" class="nav-icon-btn" aria-label="Toggle sidebar">
    <i data-lucide="menu" style="width:17px;height:17px"></i>
  </button>

  <a href="<?=$isLogged?'/pages/dashboard.php':'/'?>" class="nav-logo" style="text-decoration:none">
    <img src="<?=htmlspecialchars($logoSite??'')?>" alt="<?=htmlspecialchars($nomeSite??'RaspaPix')?>" class="nav-logo-img"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
    <span class="nav-logo-fallback" style="display:none">
      <span class="nav-logo-icon">ðŸ€</span>
      <span class="nav-logo-text"><?=htmlspecialchars($nomeSite??'RaspaPix')?></span>
    </span>
  </a>

  <div class="nav-links">
    <a href="/jogos/"    class="nav-link <?=strpos($_SERVER['REQUEST_URI'],'/jogos')!==false?'active':''?>">
      <i class="fas fa-dice" style="font-size:.8rem"></i> Cassino
    </a>
    <a href="/cartelas" class="nav-link <?=(strpos($_SERVER['REQUEST_URI'],'/cartelas')!==false||strpos($_SERVER['REQUEST_URI'],'/raspadinha')!==false)?'active':''?>">
      <i class="fas fa-ticket-alt" style="font-size:.8rem"></i> Raspadinhas
    </a>
    <a href="/afiliados" class="nav-link <?=strpos($_SERVER['REQUEST_URI'],'/afiliado')!==false?'active':''?>">
      <i class="fas fa-users" style="font-size:.8rem"></i> Afiliados
    </a>
  </div>

  <div class="nav-search">
    <i class="fas fa-search nav-search-icon"></i>
    <input type="text" class="nav-search-input" placeholder="Buscar jogos..." id="global-search" autocomplete="off">
  </div>

  <div class="nav-right">
  <?php if($isLogged): ?>
    <div class="nav-balance">
      <i data-lucide="circle-dollar-sign" style="width:14px;height:14px;color:var(--muted)"></i>
      <span class="nav-balance-amount" data-live-bal>R$ <?=number_format($saldo,2,',','.')?></span>
    </div>
    <a href="/pages/deposit.php" class="nav-btn-deposit" style="text-decoration:none">
      <i class="fas fa-plus" style="font-size:.72rem;margin-right:5px"></i>Depositar
    </a>
    <div class="dropdown">
      <div class="nav-avatar" data-dd-trigger style="cursor:pointer" title="Minha conta">
        <?=strtoupper(substr($nomeUser??'U',0,2))?>
      </div>
      <div class="dropdown-menu">
        <div class="dd-header">
          <div style="font-weight:800;font-size:.88rem;color:#fff"><?=htmlspecialchars(explode(' ',$nomeUser??'')[0]??'')?></div>
          <div style="font-size:.72rem;color:var(--muted-2)">VIP NÃ­vel <?=$vipLevel?> Â· Ativo</div>
        </div>
        <a href="/pages/dashboard.php" class="dd-item"><i data-lucide="layout-dashboard" style="width:15px;height:15px"></i> Dashboard</a>
        <a href="/pages/wallet.php"    class="dd-item"><i data-lucide="wallet"            style="width:15px;height:15px"></i> Carteira</a>
        <a href="/pages/history.php"   class="dd-item"><i data-lucide="clock"             style="width:15px;height:15px"></i> HistÃ³rico</a>
        <a href="/cartelas"            class="dd-item"><i data-lucide="ticket"             style="width:15px;height:15px"></i> Raspadinhas</a>
        <a href="/perfil"              class="dd-item"><i data-lucide="user"               style="width:15px;height:15px"></i> Perfil</a>
        <a href="/afiliados"           class="dd-item"><i data-lucide="users"              style="width:15px;height:15px"></i> Afiliados</a>
        <?php if(($usuario['admin']??0)==1): ?>
        <div class="dd-divider"></div>
        <a href="/admin/index.php" class="dd-item" style="color:var(--red)"><i data-lucide="shield" style="width:15px;height:15px"></i> Admin Panel</a>
        <?php endif; ?>
        <div class="dd-divider"></div>
        <a href="/logout.php" class="dd-item danger"><i data-lucide="log-out" style="width:15px;height:15px"></i> Sair da conta</a>
      </div>
    </div>
  <?php else: ?>
    <a href="/login.php" class="nav-btn-ghost">Entrar</a>
    <a href="/login.php?tab=register" class="nav-btn-deposit" style="text-decoration:none">Cadastrar grÃ¡tis</a>
  <?php endif; ?>
  </div>
</nav>

<?php
$_sbPath = __DIR__.'/../includes/app_sidebar.php';
if(file_exists($_sbPath)) include $_sbPath;
?>
<div class="app-layout">
<main class="main-content" id="main-content">
<div class="page-wrap" id="page-wrap">
