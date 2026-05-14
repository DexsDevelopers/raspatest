<?php
session_start();
require_once __DIR__ . '/../conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit; }
$isLogged  = true;
$uid       = $_SESSION['usuario_id'];
$st        = $pdo->prepare("SELECT nome, saldo, criado_em FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]);
$user      = $st->fetch(PDO::FETCH_ASSOC);
$saldo     = $user['saldo'] ?? 0;
$nomeUser  = $user['nome'] ?? 'Jogador';
$vipLevel  = 1;
$pageTitle = 'Dashboard';
include __DIR__ . '/../includes/app_head.php';
include __DIR__ . '/../includes/app_navbar.php';
include __DIR__ . '/../includes/app_sidebar.php';
?>

<div class="app">
<div class="page-content" id="page-content">

  <div class="page-title" data-aos="fade-right">
    <i data-lucide="layout-dashboard" class="pt-icon"></i>
    Olá, <?= htmlspecialchars(explode(' ', $nomeUser)[0]) ?>! 👋
  </div>

  <!-- Balance Cards -->
  <div class="balance-grid mb-3">
    <div class="balance-card card-shine" data-aos="fade-up" data-aos-delay="0">
      <div class="bc-label">Saldo Disponível</div>
      <div class="bc-value" data-count="<?= $saldo ?>" data-prefix="R$ " data-decimals="2">R$ 0,00</div>
      <div class="bc-change up"><i data-lucide="trending-up" style="width:12px;height:12px"></i> +2.4% hoje</div>
      <div class="bc-icon bc-icon-green"><i data-lucide="wallet" style="width:20px;height:20px"></i></div>
    </div>
    <div class="balance-card" data-aos="fade-up" data-aos-delay="80">
      <div class="bc-label">Ganhos Totais</div>
      <div class="bc-value" data-count="0" data-prefix="R$ " data-decimals="2">R$ 0,00</div>
      <div class="bc-change up"><i data-lucide="trending-up" style="width:12px;height:12px"></i> Histórico</div>
      <div class="bc-icon bc-icon-gold"><i data-lucide="trophy" style="width:20px;height:20px"></i></div>
    </div>
    <div class="balance-card" data-aos="fade-up" data-aos-delay="160">
      <div class="bc-label">Total Apostado</div>
      <div class="bc-value">R$ 0,00</div>
      <div class="bc-change down"><i data-lucide="activity" style="width:12px;height:12px"></i> 0 rodadas</div>
      <div class="bc-icon bc-icon-red"><i data-lucide="zap" style="width:20px;height:20px"></i></div>
    </div>
    <div class="balance-card" data-aos="fade-up" data-aos-delay="240">
      <div class="bc-label">Nível VIP</div>
      <div class="bc-value" style="color:var(--gold)">Bronze</div>
      <div style="margin-top:8px"><div class="progress"><div class="progress-bar gold" style="width:20%"></div></div></div>
      <div class="bc-icon bc-icon-blue"><i data-lucide="crown" style="width:20px;height:20px"></i></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
  <div>

    <!-- Quick Actions -->
    <div class="card mb-3" data-aos="fade-up">
      <div class="section-title"><i data-lucide="zap" style="width:18px;height:18px;color:var(--red)"></i> Ações Rápidas</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="/pages/deposit.php"  class="btn btn-green"><i data-lucide="arrow-down-circle" style="width:15px;height:15px"></i> Depositar</a>
        <a href="/pages/withdraw.php" class="btn btn-neon"><i data-lucide="arrow-up-circle" style="width:15px;height:15px"></i> Sacar</a>
        <a href="/jogos/aviator.php"  class="btn btn-primary"><i data-lucide="plane" style="width:15px;height:15px"></i> Aviator</a>
        <a href="/jogos/tiger.php?t=tiger" class="btn btn-ghost">🐯 Fortune Tiger</a>
        <a href="/pages/affiliates.php" class="btn btn-ghost"><i data-lucide="users" style="width:15px;height:15px"></i> Afiliados</a>
      </div>
    </div>

    <!-- Games -->
    <div class="mb-3" data-aos="fade-up">
      <div class="section-title"><i data-lucide="gamepad-2" style="width:18px;height:18px;color:var(--red)"></i> Jogos em Destaque</div>
      <div class="games-grid">
        <?php
        $featured = [
          ['Fortune Tiger','🐯','/jogos/tiger.php?t=tiger','radial-gradient(at 60% 110%,#c44000,#5a0a00 55%,#1a0000)','rgba(220,80,0,.55)','rgba(255,100,0,.5)','PG STYLE','hot','🔥 HOT'],
          ['Fortune Rabbit','🐰','/jogos/tiger.php?t=rabbit','radial-gradient(at 60% 110%,#8800cc,#44006a 55%,#0f0020)','rgba(160,0,220,.55)','rgba(200,50,255,.5)','PG STYLE','hot','🔥 HOT'],
          ['Aviator','✈️','/jogos/aviator.php','radial-gradient(at 60% 110%,#cc2200,#801100 55%,#1a0000)','rgba(220,40,0,.55)','rgba(255,70,0,.5)','SPRIBE','hot','🔴 AO VIVO'],
          ['Mines','💣','/jogos/mines.php','radial-gradient(at 60% 110%,#5a3000,#2a1400 55%,#0a0500)','rgba(180,80,0,.55)','rgba(220,100,0,.5)','ORIGINAL','hot','🔥 HOT'],
          ['Crash','🚀','/jogos/crash.php','radial-gradient(at 60% 110%,#1a6600,#0d3300 55%,#020d00)','rgba(30,180,0,.55)','rgba(60,220,0,.5)','ORIGINAL','live','🔴 AO VIVO'],
          ['Fortune Dragon','🐉','/jogos/tiger.php?t=dragon','radial-gradient(at 60% 110%,#0050cc,#002880 55%,#000a20)','rgba(0,80,200,.55)','rgba(50,120,255,.5)','PG STYLE','new','✨ NEW'],
        ];
        foreach($featured as [$name,$emoji,$url,$bg,$glow,$shadow,$prov,$tag,$badge]):
        ?>
        <a href="<?= $url ?>" class="game-card">
          <div class="game-thumb">
            <div class="game-thumb-inner">
              <div class="game-thumb-bg" style="background:<?= $bg ?>;--glow:<?= $glow ?>;--shadow:<?= $shadow ?>"></div>
              <div class="game-emoji"><?= $emoji ?></div>
            </div>
            <div class="game-prov"><?= $prov ?></div>
            <div class="game-badge <?= $tag==='new'?'gbadge-new':($tag==='live'?'gbadge-live':'gbadge-hot') ?>"><?= $badge ?></div>
            <div class="game-overlay"><button class="game-play-btn">▶ Jogar</button></div>
          </div>
          <div class="game-info">
            <div class="game-name"><?= $name ?></div>
            <div class="game-players"><span class="live-dot" style="width:4px;height:4px;background:var(--green);border-radius:50%;display:inline-block"></span> <?= rand(300,5000) ?> online</div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recent History -->
    <div class="card" data-aos="fade-up">
      <div class="section-title" style="justify-content:space-between">
        <span style="display:flex;align-items:center;gap:8px"><i data-lucide="clock" style="width:18px;height:18px;color:var(--red)"></i> Últimas Apostas</span>
        <a href="/pages/history.php" class="btn btn-ghost btn-sm">Ver tudo</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Jogo</th><th>Aposta</th><th>Resultado</th><th>Data</th></tr></thead>
          <tbody>
            <tr><td>Fortune Tiger</td><td>R$ 10,00</td><td><span class="badge badge-green">+R$ 80,00</span></td><td style="color:var(--muted);font-size:.78rem">Hoje</td></tr>
            <tr><td>Aviator</td><td>R$ 25,00</td><td><span class="badge badge-red">-R$ 25,00</span></td><td style="color:var(--muted);font-size:.78rem">Hoje</td></tr>
            <tr><td>Crash</td><td>R$ 5,00</td><td><span class="badge badge-green">+R$ 22,00</span></td><td style="color:var(--muted);font-size:.78rem">Ontem</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Right column: Feed + Chat -->
  <div>
    <!-- Stats -->
    <div class="card mb-3" data-aos="fade-left">
      <div class="section-title"><i data-lucide="bar-chart-2" style="width:16px;height:16px;color:var(--red)"></i> Plataforma</div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div><div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px"><span style="color:var(--muted)">Jogadores Online</span><span style="font-weight:700;color:var(--green)" data-count="2021">0</span></div></div>
        <div><div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px"><span style="color:var(--muted)">Ganhos (24h)</span><span style="font-weight:700" data-count="94957" data-prefix="R$ " data-decimals="2">R$ 0</span></div></div>
        <div><div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px"><span style="color:var(--muted)">Rodadas (24h)</span><span style="font-weight:700" data-count="28782">0</span></div></div>
      </div>
    </div>

    <!-- Realtime Feed -->
    <div class="card mb-3" data-aos="fade-left" data-aos-delay="80">
      <div class="section-title">
        <span class="live-badge"><span class="live-dot"></span> Feed Ao Vivo</span>
      </div>
      <div class="feed-wrap"><div class="feed-list" id="feed-list"></div></div>
    </div>

    <!-- Chat -->
    <div class="card" data-aos="fade-left" data-aos-delay="160" style="padding:0;overflow:hidden">
      <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <div class="section-title" style="margin:0"><i data-lucide="message-circle" style="width:16px;height:16px;color:var(--red)"></i> Chat Global</div>
        <span class="live-badge"><span class="live-dot"></span> Online</span>
      </div>
      <div class="chat-wrap" style="height:260px">
        <div class="chat-msgs" id="chat-msgs"></div>
        <div class="chat-input-row">
          <input type="text" class="chat-input" id="chat-input" placeholder="Escreva uma mensagem...">
          <button class="btn btn-primary btn-sm btn-icon" onclick="document.getElementById('chat-input').dispatchEvent(new KeyboardEvent('keydown',{key:'Enter'}))">
            <i data-lucide="send" style="width:14px;height:14px"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  </div>

</div><!-- /page-content -->
</div><!-- /app -->

<?php include __DIR__ . '/../includes/app_footer.php'; ?>
