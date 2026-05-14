<?php /* ─── app_navbar.php ─── Ultra Premium Navbar ─── */ ?>
<nav class="navbar" role="navigation">

  <!-- Toggle + Logo -->
  <button id="sb-toggle" class="nav-icon-btn" aria-label="Toggle sidebar">
    <i data-lucide="menu" style="width:17px;height:17px"></i>
  </button>

  <a href="/pages/dashboard.php" class="nav-logo" style="text-decoration:none">
    <img src="<?=htmlspecialchars($logoSite)?>" alt="RaspaPix" class="nav-logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
    <span class="nav-logo-fallback" style="display:none"><span class="nav-logo-icon">🍀</span><span class="nav-logo-text">RaspaPix</span></span>
  </a>

  <!-- Nav links -->
  <div class="nav-links" id="nav-links">
    <a href="/jogos/"    class="nav-link <?=strpos($_SERVER['REQUEST_URI'],'/jogos')!==false?'active':''?>"><i class="fas fa-dice" style="font-size:.8rem"></i> Cassino</a>
    <a href="/cartelas" class="nav-link <?=strpos($_SERVER['REQUEST_URI'],'/cartelas')!==false?'active':''?>"><i class="fas fa-ticket-alt" style="font-size:.8rem"></i> Raspadinhas</a>
    <a href="/afiliados" class="nav-link <?=strpos($_SERVER['REQUEST_URI'],'/afiliado')!==false?'active':''?>"><i class="fas fa-users" style="font-size:.8rem"></i> Afiliados</a>
  </div>

  <!-- Search -->
  <div class="nav-search">
    <i class="fas fa-search nav-search-icon"></i>
    <input type="text" class="nav-search-input" placeholder="Buscar jogos..." id="global-search" autocomplete="off">
  </div>

  <!-- Right actions -->
  <div class="nav-right">

    <?php if($isLogged ?? false): ?>
      <!-- Balance -->
      <div class="nav-balance" title="Saldo disponível">
        <i data-lucide="circle-dollar-sign" style="width:14px;height:14px;color:var(--muted)"></i>
        <span class="nav-balance-amount" data-live-bal>
          R$ <?=number_format($saldo??0,2,',','.')?>
        </span>
      </div>

      <!-- Deposit CTA -->
      <a href="/pages/deposit.php" class="nav-btn-deposit">
        <i class="fas fa-plus" style="font-size:.72rem;margin-right:5px"></i>Depositar
      </a>

      <!-- Notifications -->
      <button class="nav-icon-btn" data-tip="Notificações">
        <i data-lucide="bell" style="width:16px;height:16px"></i>
      </button>

      <!-- User dropdown -->
      <div class="dropdown">
        <div class="nav-avatar" data-dd-trigger style="cursor:pointer" title="Minha conta">
          <?=strtoupper(substr($nomeUser??'U',0,2))?>
        </div>
        <div class="dropdown-menu">
          <div class="dd-header">
            <div style="font-weight:800;font-size:.88rem;color:#fff"><?=htmlspecialchars(explode(' ',$nomeUser??'')[0]??'')?></div>
            <div style="font-size:.72rem;color:var(--muted-2);margin-top:1px">VIP Nível <?=$vipLevel??1?> · Ativo</div>
          </div>
          <a href="/pages/dashboard.php"  class="dd-item"><i data-lucide="layout-dashboard" style="width:15px;height:15px"></i> Dashboard</a>
          <a href="/pages/wallet.php"     class="dd-item"><i data-lucide="wallet"            style="width:15px;height:15px"></i> Carteira</a>
          <a href="/pages/history.php"    class="dd-item"><i data-lucide="clock"             style="width:15px;height:15px"></i> Histórico</a>
          <a href="/pages/vip.php"        class="dd-item"><i data-lucide="crown"             style="width:15px;height:15px"></i> VIP</a>
          <a href="/pages/affiliates.php" class="dd-item"><i data-lucide="users"             style="width:15px;height:15px"></i> Afiliados</a>
          <div class="dd-divider"></div>
          <a href="/logout.php" class="dd-item danger"><i data-lucide="log-out" style="width:15px;height:15px"></i> Sair da conta</a>
        </div>
      </div>

    <?php else: ?>
      <a href="/login.php" class="nav-btn-ghost">Entrar</a>
      <a href="/login.php?tab=register" class="nav-btn-deposit" style="text-decoration:none">Cadastrar grátis</a>
    <?php endif; ?>

  </div>
</nav>
