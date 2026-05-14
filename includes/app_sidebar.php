<?php
/* ─── app_sidebar.php ─── Premium Sidebar ─── */
$currentPage = basename($_SERVER['PHP_SELF']);
function sbActive(string $file): string {
    global $currentPage;
    return $currentPage === $file ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">

  <div class="sidebar-section">
    <span class="sidebar-label">Principal</span>
    <a href="/pages/dashboard.php" class="sidebar-item <?= sbActive('dashboard.php') ?>">
      <i data-lucide="layout-dashboard" class="si-icon"></i> Dashboard
    </a>
    <a href="/jogos/" class="sidebar-item <?= sbActive('index.php') ?>">
      <i data-lucide="gamepad-2" class="si-icon"></i> Cassino
      <span class="si-badge green">9</span>
    </a>
    <a href="/" class="sidebar-item">
      <i data-lucide="ticket" class="si-icon"></i> Raspadinhas
    </a>
  </div>

  <div class="sidebar-divider"></div>

  <div class="sidebar-section">
    <span class="sidebar-label">Jogos</span>
    <a href="/jogos/tiger.php?t=tiger" class="sidebar-item">
      <span class="si-icon" style="text-align:center">🐯</span> Fortune Tiger
    </a>
    <a href="/jogos/tiger.php?t=rabbit" class="sidebar-item">
      <span class="si-icon" style="text-align:center">🐰</span> Fortune Rabbit
    </a>
    <a href="/jogos/tiger.php?t=dragon" class="sidebar-item">
      <span class="si-icon" style="text-align:center">🐉</span> Fortune Dragon
    </a>
    <a href="/jogos/aviator.php" class="sidebar-item">
      <span class="si-icon" style="text-align:center">✈️</span> Aviator
      <span class="si-badge" style="background:rgba(239,68,68,.2);color:#ef4444;border:1px solid rgba(239,68,68,.3)">AO VIVO</span>
    </a>
    <a href="/jogos/crash.php"  class="sidebar-item"><span class="si-icon">🚀</span> Crash</a>
    <a href="/jogos/mines.php"  class="sidebar-item"><span class="si-icon">💣</span> Mines</a>
    <a href="/jogos/plinko.php" class="sidebar-item"><span class="si-icon">🔵</span> Plinko</a>
    <a href="/jogos/dice.php"   class="sidebar-item"><span class="si-icon">🎲</span> Dice</a>
    <a href="/jogos/limbo.php"  class="sidebar-item"><span class="si-icon">🌀</span> Limbo</a>
  </div>

  <div class="sidebar-divider"></div>

  <div class="sidebar-section">
    <span class="sidebar-label">Conta</span>
    <a href="/pages/wallet.php"     class="sidebar-item <?= sbActive('wallet.php') ?>">
      <i data-lucide="wallet" class="si-icon"></i> Carteira
    </a>
    <a href="/pages/deposit.php"    class="sidebar-item <?= sbActive('deposit.php') ?>">
      <i data-lucide="arrow-down-circle" class="si-icon"></i> Depositar
    </a>
    <a href="/pages/withdraw.php"   class="sidebar-item <?= sbActive('withdraw.php') ?>">
      <i data-lucide="arrow-up-circle" class="si-icon"></i> Sacar
    </a>
    <a href="/pages/history.php"    class="sidebar-item <?= sbActive('history.php') ?>">
      <i data-lucide="clock" class="si-icon"></i> Histórico
    </a>
  </div>

  <div class="sidebar-divider"></div>

  <div class="sidebar-section">
    <span class="sidebar-label">Comunidade</span>
    <a href="/pages/ranking.php"    class="sidebar-item <?= sbActive('ranking.php') ?>">
      <i data-lucide="trophy" class="si-icon"></i> Ranking
    </a>
    <a href="/pages/vip.php"        class="sidebar-item <?= sbActive('vip.php') ?>">
      <i data-lucide="crown" class="si-icon"></i> VIP
      <span class="si-badge gold">NOVO</span>
    </a>
    <a href="/pages/affiliates.php" class="sidebar-item <?= sbActive('affiliates.php') ?>">
      <i data-lucide="users" class="si-icon"></i> Afiliados
    </a>
    <a href="/pages/support.php"    class="sidebar-item <?= sbActive('support.php') ?>">
      <i data-lucide="headphones" class="si-icon"></i> Suporte
    </a>
  </div>

  <div class="sidebar-divider"></div>

  <!-- Jackpot display in sidebar -->
  <div style="padding:12px 14px;margin:0 10px;background:rgba(255,23,68,.06);border:1px solid rgba(255,23,68,.15);border-radius:10px">
    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:4px">🏆 Jackpot</div>
    <div id="jackpot-value" data-base="188000" style="font-size:1.1rem;font-weight:900;color:var(--gold);text-shadow:0 0 12px rgba(255,214,0,.3)">R$ 188.000,00</div>
  </div>

</aside>
