<?php /* ─── app_navbar.php ─── Premium Navbar ─── */ ?>
<nav class="navbar">

  <!-- Hamburger + Logo -->
  <button id="sidebar-toggle" class="icon-btn" style="flex-shrink:0" aria-label="Menu">
    <i data-lucide="menu" style="width:18px;height:18px"></i>
  </button>

  <a href="/pages/dashboard.php" class="navbar-logo">
    <span class="icon">🍀</span>
    <span class="name">RaspaPix</span><span class="dot">.</span>
  </a>

  <!-- Ticker (desktop) -->
  <div style="flex:1;overflow:hidden;display:none;" id="navbar-ticker-wrap" class="d-md-block">
    <div style="white-space:nowrap;overflow:hidden;height:32px;display:flex;align-items:center">
      <div id="live-ticker" style="display:inline-flex;gap:0;animation:ticker-scroll 45s linear infinite;font-size:.72rem"></div>
    </div>
  </div>

  <!-- Search -->
  <div class="navbar-search">
    <i class="fas fa-search search-icon"></i>
    <input type="text" placeholder="Buscar jogos..." id="global-search">
  </div>

  <!-- Right side -->
  <div class="navbar-right">

    <?php if ($isLogged ?? false): ?>
      <!-- Balance -->
      <div class="navbar-balance">
        <i data-lucide="wallet" style="width:15px;height:15px;color:var(--muted)"></i>
        <span class="amount" data-live-balance>
          R$ <?= number_format($saldo ?? 0, 2, ',', '.') ?>
        </span>
      </div>

      <!-- Deposit -->
      <a href="/pages/deposit.php" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:14px;height:14px"></i> Depositar
      </a>

      <!-- User Dropdown -->
      <div class="dropdown">
        <div class="avatar-btn" data-dropdown-trigger
             data-tip="<?= htmlspecialchars(explode(' ', $nomeUser ?? 'U')[0]) ?>">
          <?= strtoupper(substr($nomeUser ?? 'U', 0, 2)) ?>
        </div>
        <div class="dropdown-menu">
          <div class="dropdown-item" style="flex-direction:column;align-items:flex-start;gap:2px;pointer-events:none;border-bottom:1px solid var(--border);padding-bottom:10px;margin-bottom:4px">
            <span style="font-weight:700;color:#fff"><?= htmlspecialchars($nomeUser ?? '') ?></span>
            <span style="font-size:.72rem;color:var(--muted)">Nível VIP <?= $vipLevel ?? 1 ?></span>
          </div>
          <a href="/pages/dashboard.php" class="dropdown-item"><i data-lucide="layout-dashboard" style="width:15px;height:15px"></i> Dashboard</a>
          <a href="/pages/wallet.php"    class="dropdown-item"><i data-lucide="wallet"            style="width:15px;height:15px"></i> Carteira</a>
          <a href="/pages/history.php"   class="dropdown-item"><i data-lucide="history"           style="width:15px;height:15px"></i> Histórico</a>
          <a href="/pages/affiliates.php"class="dropdown-item"><i data-lucide="users"             style="width:15px;height:15px"></i> Afiliados</a>
          <a href="/pages/vip.php"       class="dropdown-item"><i data-lucide="crown"             style="width:15px;height:15px"></i> VIP</a>
          <div class="dropdown-divider"></div>
          <a href="/logout.php"          class="dropdown-item danger"><i data-lucide="log-out"    style="width:15px;height:15px"></i> Sair</a>
        </div>
      </div>

    <?php else: ?>
      <a href="/login.php"    class="btn btn-ghost btn-sm">Entrar</a>
      <a href="/register.php" class="btn btn-primary btn-sm">Cadastrar</a>
    <?php endif; ?>

  </div>
</nav>
