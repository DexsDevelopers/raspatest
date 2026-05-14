<?php
session_start();
require_once __DIR__.'/../conexao.php';
if(!isset($_SESSION['usuario_id'])){header('Location: /login.php');exit;}
$isLogged=true; $uid=$_SESSION['usuario_id'];
$st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);
$saldo=$u['saldo']??0; $nomeUser=$u['nome']??'Jogador'; $vipLevel=1;
$pageTitle='Dashboard';
include __DIR__.'/../includes/app_head.php';
include __DIR__.'/../includes/app_navbar.php';
include __DIR__.'/../includes/app_sidebar.php';
$games=[
  ['Fortune Tiger','🐯','/jogos/tiger.php?t=tiger','linear-gradient(145deg,#1a0700,#380e00,#1a0000)','rgba(220,80,0,.5)','rgba(255,100,0,.45)','PG STYLE','hot','🔥 HOT',3241],
  ['Fortune Rabbit','🐰','/jogos/tiger.php?t=rabbit','linear-gradient(145deg,#0e0020,#200040,#0a0018)','rgba(150,0,220,.45)','rgba(200,60,255,.4)','PG STYLE','hot','🔥 HOT',2180],
  ['Aviator','✈️','/jogos/aviator.php','linear-gradient(145deg,#1a0300,#350800,#160200)','rgba(220,40,0,.5)','rgba(255,70,0,.45)','SPRIBE','live','🔴 LIVE',5420],
  ['Fortune Dragon','🐉','/jogos/tiger.php?t=dragon','linear-gradient(145deg,#000e2a,#001650,#000818)','rgba(0,80,200,.45)','rgba(50,120,255,.4)','PG STYLE','new','✨ NEW',1560],
  ['Crash','🚀','/jogos/crash.php','linear-gradient(145deg,#001800,#003200,#000d00)','rgba(30,180,0,.45)','rgba(60,220,0,.4)','ORIGINAL','live','🔴 LIVE',847],
  ['Mines','💣','/jogos/mines.php','linear-gradient(145deg,#1a0f00,#2e1c00,#0f0800)','rgba(180,80,0,.45)','rgba(220,110,0,.4)','ORIGINAL','hot','🔥 HOT',1203],
];
?>

<div class="app">
<?php include __DIR__.'/../includes/app_navbar.php'; /* already included above but sidebar needs wrap */ ?>

<div class="page-wrap" id="page-wrap">

  <!-- Live ticker bar -->
  <div class="ticker-rail mb-3" style="margin:-26px -22px 22px;border-radius:0;border-top:none;border-left:none;border-right:none">
    <div class="ticker-track" id="ticker-track"></div>
  </div>

  <!-- Page title -->
  <div class="page-title" data-gsap-fade>
    <div class="page-title-icon"><i data-lucide="layout-dashboard" style="width:17px;height:17px"></i></div>
    Olá, <?=htmlspecialchars(explode(' ',$nomeUser)[0])?>! 👋
    <span class="badge badge-live" style="margin-left:4px;font-size:.65rem"><span class="live-dot"></span> AO VIVO</span>
  </div>

  <!-- Balance Cards -->
  <div class="bal-grid">
    <div class="bal-card card-shine" style="--deco-color:rgba(0,230,118,.08)" data-gsap-fade data-gsap-delay="0">
      <div class="bal-card-label"><i data-lucide="wallet" style="width:12px;height:12px"></i> Saldo</div>
      <div class="bal-card-value" data-live-bal>R$ <?=number_format($saldo,2,',','.')?></div>
      <div class="bal-card-sub up"><i data-lucide="trending-up" style="width:11px;height:11px"></i> Conta ativa</div>
      <div style="display:flex;gap:7px;margin-top:12px">
        <a href="/pages/deposit.php"  class="btn btn-green btn-sm" style="flex:1;justify-content:center">+&nbsp;Depositar</a>
        <a href="/pages/withdraw.php" class="btn btn-neon  btn-sm" style="flex:1;justify-content:center">Sacar</a>
      </div>
      <div class="bal-card-icon bc-green"><i data-lucide="wallet" style="width:17px;height:17px"></i></div>
    </div>

    <div class="bal-card" style="--deco-color:rgba(255,214,0,.07)" data-gsap-fade data-gsap-delay=".07">
      <div class="bal-card-label"><i data-lucide="trophy" style="width:12px;height:12px"></i> Ganhos Totais</div>
      <div class="bal-card-value" style="color:var(--gold)">R$ 0,00</div>
      <div class="bal-card-sub muted">Acumulado histórico</div>
      <div class="bal-card-icon bc-gold"><i data-lucide="trophy" style="width:17px;height:17px"></i></div>
    </div>

    <div class="bal-card" style="--deco-color:rgba(255,23,68,.06)" data-gsap-fade data-gsap-delay=".14">
      <div class="bal-card-label"><i data-lucide="zap" style="width:12px;height:12px"></i> Total Apostado</div>
      <div class="bal-card-value">R$ 0,00</div>
      <div class="bal-card-sub muted">0 rodadas jogadas</div>
      <div class="bal-card-icon bc-red"><i data-lucide="zap" style="width:17px;height:17px"></i></div>
    </div>

    <div class="bal-card" style="--deco-color:rgba(41,121,255,.06)" data-gsap-fade data-gsap-delay=".21">
      <div class="bal-card-label"><i data-lucide="crown" style="width:12px;height:12px"></i> Nível VIP</div>
      <div class="bal-card-value" style="color:var(--gold);font-size:1.3rem">🥉 Bronze</div>
      <div style="margin-top:10px"><div class="progress"><div class="progress-fill gold" style="width:18%"></div></div></div>
      <div style="font-size:.68rem;color:var(--muted-2);margin-top:5px">20% → Prata (R$ 200 apostados)</div>
      <div class="bal-card-icon bc-blue"><i data-lucide="crown" style="width:17px;height:17px"></i></div>
    </div>
  </div>

  <!-- Main content + right column -->
  <div style="display:grid;grid-template-columns:1fr 310px;gap:18px;align-items:start">
  <div>

    <!-- Stats row -->
    <div class="stats-row" data-aos="fade-up">
      <div class="stat-card"><div class="stat-lbl">🌐 Jogadores Online</div><div class="stat-val" style="color:var(--green)" data-count="2021">0</div><div class="stat-sub">Ao vivo agora</div></div>
      <div class="stat-card"><div class="stat-lbl">💰 Ganhos 24h</div><div class="stat-val" data-count="94957" data-prefix="R$ " data-dec="2">R$ 0</div><div class="stat-sub">Pagos hoje</div></div>
      <div class="stat-card"><div class="stat-lbl">⚡ Rodadas 24h</div><div class="stat-val" data-count="28782">0</div><div class="stat-sub">Apostas realizadas</div></div>
      <div class="stat-card"><div class="stat-lbl">🏆 Jackpot</div><div class="stat-val" style="color:var(--gold)" id="jackpot-value" data-base="188000">R$ 0</div><div class="stat-sub">Acumulado</div></div>
    </div>

    <!-- Featured games -->
    <div class="mb-3" data-aos="fade-up" data-aos-delay="60">
      <div class="sec-head">
        <div class="sec-title"><div class="sec-title-dot"></div> Jogos em Destaque</div>
        <a href="/pages/games.php" class="sec-more">Ver todos <i data-lucide="chevron-right" style="width:14px;height:14px"></i></a>
      </div>
      <div class="games-grid">
        <?php foreach($games as [$name,$emoji,$url,$bg,$glow,$shadow,$prov,$tag,$badge,$cnt]): ?>
        <a href="<?=$url?>" class="game-card" data-name="<?=strtolower($name)?>">
          <div class="gthumb">
            <div class="gthumb-inner">
              <div class="gthumb-bg" style="background:<?=$bg?>;--g-glow:<?=$glow?>;--g-shadow:<?=$shadow?>"></div>
              <div class="g-emoji"><?=$emoji?></div>
            </div>
            <div class="g-prov"><?=$prov?></div>
            <div class="g-badge <?=$tag==='live'?'gbadge-live':($tag==='new'?'gbadge-new':'gbadge-hot')?>"><?=$badge?></div>
            <div class="g-overlay"><button class="g-play-btn">▶&nbsp;Jogar</button></div>
          </div>
          <div class="g-info">
            <div class="g-name"><?=$name?></div>
            <div class="g-players"><span class="g-dot"></span> <?=number_format($cnt+rand(-50,200),0,'.',',')?> online</div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="card card-glow mb-3" data-aos="fade-up">
      <div class="sec-title mb-2"><div class="sec-title-dot"></div> Ações Rápidas</div>
      <div style="display:flex;gap:9px;flex-wrap:wrap">
        <a href="/pages/deposit.php"       class="btn btn-green btn-md"><i data-lucide="plus-circle" style="width:15px;height:15px"></i> Depositar</a>
        <a href="/pages/withdraw.php"      class="btn btn-neon  btn-md"><i data-lucide="send"         style="width:15px;height:15px"></i> Sacar</a>
        <a href="/jogos/aviator.php"       class="btn btn-primary btn-md">✈️ Aviator</a>
        <a href="/jogos/tiger.php?t=tiger" class="btn btn-ghost  btn-md">🐯 Fortune Tiger</a>
        <a href="/pages/affiliates.php"    class="btn btn-ghost  btn-md"><i data-lucide="users" style="width:15px;height:15px"></i> Afiliados</a>
        <a href="/pages/vip.php"           class="btn btn-gold   btn-md">👑 VIP</a>
      </div>
    </div>

    <!-- Recent bets table -->
    <div class="card" data-aos="fade-up">
      <div class="sec-head">
        <div class="sec-title"><div class="sec-title-dot"></div> Últimas Apostas</div>
        <a href="/pages/history.php" class="sec-more">Histórico completo <i data-lucide="chevron-right" style="width:14px;height:14px"></i></a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>Jogo</th><th>Aposta</th><th>Mult.</th><th>Resultado</th><th>Quando</th></tr></thead>
          <tbody>
            <tr><td>Fortune Tiger</td><td>R$ 10,00</td><td style="color:var(--gold);font-weight:700">8.00x</td><td><span class="badge badge-green">+R$ 80,00</span></td><td style="color:var(--muted-2);font-size:.75rem">Hoje</td></tr>
            <tr><td>Aviator</td><td>R$ 25,00</td><td style="color:var(--red-bright);font-weight:700">0.00x</td><td><span class="badge badge-red">-R$ 25,00</span></td><td style="color:var(--muted-2);font-size:.75rem">Hoje</td></tr>
            <tr><td>Crash</td><td>R$ 5,00</td><td style="color:var(--gold);font-weight:700">4.40x</td><td><span class="badge badge-green">+R$ 22,00</span></td><td style="color:var(--muted-2);font-size:.75rem">Ontem</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /left col -->

  <!-- Right column -->
  <div style="display:flex;flex-direction:column;gap:14px">

    <!-- Platform live stats -->
    <div class="card card-solid" data-aos="fade-left">
      <div class="sec-title mb-3"><div class="sec-title-dot"></div> Plataforma</div>
      <?php foreach([
        ['Jogadores online','2.021','var(--green)'],
        ['Ganhos hoje','R$ 94.957','var(--text)'],
        ['Rodadas hoje','28.782','var(--text)'],
        ['Maior ganho hoje','R$ 12.840','var(--gold)'],
      ] as [$l,$v,$c]): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.03)">
        <span style="font-size:.78rem;color:var(--muted-2)"><?=$l?></span>
        <span style="font-size:.84rem;font-weight:700;color:<?=$c?>"><?=$v?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Realtime Feed -->
    <div class="card card-solid" data-aos="fade-left" data-aos-delay="60">
      <div class="sec-head" style="margin-bottom:8px">
        <div class="sec-title"><div class="sec-title-dot"></div> Feed ao Vivo</div>
        <span class="badge badge-live"><span class="live-dot"></span> LIVE</span>
      </div>
      <div class="feed-scroll">
        <div class="feed-list" id="feed-list"></div>
      </div>
    </div>

    <!-- Chat -->
    <div class="card card-solid" style="padding:0;overflow:hidden" data-aos="fade-left" data-aos-delay="120">
      <div style="padding:13px 14px 10px;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;justify-content:space-between">
        <div class="sec-title" style="margin:0"><div class="sec-title-dot"></div> Chat Global</div>
        <span class="badge badge-live" style="font-size:.62rem"><span class="live-dot"></span> Online</span>
      </div>
      <div class="chat-wrap" style="height:280px">
        <div class="chat-body" id="chat-body"></div>
        <div class="chat-footer">
          <input type="text" class="chat-in" id="chat-input" placeholder="Mensagem...">
          <button id="chat-send" class="btn btn-primary btn-sm btn-icon" style="width:32px;height:32px;flex-shrink:0">
            <i data-lucide="send" style="width:13px;height:13px"></i>
          </button>
        </div>
      </div>
    </div>

  </div><!-- /right col -->
  </div><!-- /grid -->

</div><!-- /page-wrap -->
</div><!-- /app -->

<?php include __DIR__.'/../includes/app_footer.php'; ?>
