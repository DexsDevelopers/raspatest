<?php /* ─── app_sidebar.php ─── Cinematic Sidebar ─── */
$cp = basename($_SERVER['PHP_SELF']);
function sba(string $f):string{ global $cp; return $cp===$f?'active':''; }
?>
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Menu principal">
<div class="sidebar-scroll">

  <!-- Main -->
  <div class="sb-section">
    <span class="sb-label">Principal</span>
    <a href="/pages/dashboard.php" class="sb-item <?=sba('dashboard.php')?>">
      <i data-lucide="layout-dashboard" class="sb-icon"></i>
      <span class="sb-text">Dashboard</span>
    </a>
    <a href="/pages/games.php" class="sb-item <?=sba('games.php')?>">
      <i data-lucide="gamepad-2" class="sb-icon"></i>
      <span class="sb-text">Cassino</span>
      <span class="sb-badge sb-badge-green">9</span>
    </a>
    <a href="/cartelas" class="sb-item <?=strpos($_SERVER['REQUEST_URI'],'/cartelas')!==false||strpos($_SERVER['REQUEST_URI'],'/raspadinha')!==false?'active':''?>">
      <i data-lucide="ticket" class="sb-icon"></i>
      <span class="sb-text">Raspadinhas</span>
      <span class="sb-badge sb-badge-gold">HOT</span>
    </a>
  </div>

  <div class="sb-divider"></div>

  <!-- Originals -->
  <div class="sb-section">
    <span class="sb-label">Originais</span>
    <a href="/jogos/aviator.php" class="sb-item">
      <span class="sb-icon" style="width:18px;text-align:center;font-size:1rem">✈️</span>
      <span class="sb-text">Aviator</span>
      <span class="sb-badge sb-badge-red">AO VIVO</span>
    </a>
    <a href="/jogos/crash.php"  class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🚀</span><span class="sb-text">Crash</span></a>
    <a href="/jogos/mines.php"  class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">💣</span><span class="sb-text">Mines</span></a>
    <a href="/jogos/plinko.php" class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🔵</span><span class="sb-text">Plinko</span></a>
    <a href="/jogos/dice.php"   class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🎲</span><span class="sb-text">Dice</span></a>
    <a href="/jogos/limbo.php"  class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🌀</span><span class="sb-text">Limbo</span></a>
  </div>

  <div class="sb-divider"></div>

  <!-- Slots -->
  <div class="sb-section">
    <span class="sb-label">Slots PG</span>
    <a href="/jogos/tiger.php?t=tiger"  class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🐯</span><span class="sb-text">Fortune Tiger</span></a>
    <a href="/jogos/tiger.php?t=rabbit" class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🐰</span><span class="sb-text">Fortune Rabbit</span></a>
    <a href="/jogos/tiger.php?t=dragon" class="sb-item"><span class="sb-icon" style="text-align:center;font-size:1rem">🐉</span><span class="sb-text">Fortune Dragon</span></a>
  </div>

  <div class="sb-divider"></div>

  <!-- Account -->
  <div class="sb-section">
    <span class="sb-label">Conta</span>
    <a href="/pages/wallet.php"    class="sb-item <?=sba('wallet.php')?>">
      <i data-lucide="wallet"           class="sb-icon"></i><span class="sb-text">Carteira</span>
    </a>
    <a href="/pages/deposit.php"   class="sb-item <?=sba('deposit.php')?>">
      <i data-lucide="arrow-down-circle" class="sb-icon"></i><span class="sb-text">Depositar</span>
    </a>
    <a href="/pages/withdraw.php"  class="sb-item <?=sba('withdraw.php')?>">
      <i data-lucide="arrow-up-circle"   class="sb-icon"></i><span class="sb-text">Sacar</span>
    </a>
    <a href="/pages/history.php"   class="sb-item <?=sba('history.php')?>">
      <i data-lucide="clock"             class="sb-icon"></i><span class="sb-text">Histórico</span>
    </a>
  </div>

  <div class="sb-divider"></div>

  <!-- Community -->
  <div class="sb-section">
    <span class="sb-label">Comunidade</span>
    <a href="/pages/ranking.php"    class="sb-item <?=sba('ranking.php')?>">
      <i data-lucide="trophy"  class="sb-icon"></i><span class="sb-text">Ranking</span>
    </a>
    <a href="/pages/vip.php"        class="sb-item <?=sba('vip.php')?>">
      <i data-lucide="crown"   class="sb-icon"></i><span class="sb-text">VIP</span>
      <span class="sb-badge sb-badge-gold">NOVO</span>
    </a>
    <a href="/pages/affiliates.php" class="sb-item <?=sba('affiliates.php')?>">
      <i data-lucide="users"   class="sb-icon"></i><span class="sb-text">Afiliados</span>
    </a>
    <a href="/pages/support.php"    class="sb-item <?=sba('support.php')?>">
      <i data-lucide="headphones" class="sb-icon"></i><span class="sb-text">Suporte</span>
    </a>
  </div>

</div><!-- /sidebar-scroll -->

<!-- Jackpot bottom widget -->
<div class="sb-bottom">
  <div class="sb-jackpot">
    <div class="jk-label">🏆 Jackpot Acumulado</div>
    <div class="jk-value" id="jackpot-value" data-base="188000">R$ 188.000,00</div>
  </div>
</div>

</aside>
