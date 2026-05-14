<?php
session_start();
require_once __DIR__.'/../conexao.php';
if(!isset($_SESSION['usuario_id'])){header('Location: /login.php');exit;}
$isLogged=true; $uid=$_SESSION['usuario_id'];

// User data
$st=$pdo->prepare("SELECT nome,saldo,admin FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);
$saldo=$u['saldo']??0; $nomeUser=$u['nome']??'Jogador'; $vipLevel=1;

// Real platform stats
$onlineUsers = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$paidToday   = $pdo->query("SELECT COALESCE(SUM(valor),0) FROM depositos WHERE status='PAID' AND DATE(updated_at)=CURDATE()")->fetchColumn();
$betsToday   = 0; // apostas table may not exist yet
try { $betsToday = $pdo->query("SELECT COUNT(*) FROM apostas WHERE DATE(created_at)=CURDATE()")->fetchColumn(); } catch(Exception $e){}
$jackpot     = $pdo->query("SELECT COALESCE(SUM(saldo),0) FROM usuarios")->fetchColumn();

// User financials
$userDeps = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM depositos WHERE user_id=? AND status='PAID'");
$userDeps->execute([$uid]); $totalDep=$userDeps->fetchColumn();

$userWins = 0; $userBets = 0; $userWagered = 0;
try {
  $ubets=$pdo->prepare("SELECT COUNT(*),COALESCE(SUM(valor_ganho),0),COALESCE(SUM(valor_aposta),0) FROM apostas WHERE user_id=?");
  $ubets->execute([$uid]); [$userBets,$userWins,$userWagered]=$ubets->fetch(PDO::FETCH_NUM);
} catch(Exception $e){}

// Recent bets
$recentBets=[];
try {
  $rb=$pdo->prepare("SELECT jogo,valor_aposta,valor_ganho,multiplicador,resultado,created_at FROM apostas WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
  $rb->execute([$uid]); $recentBets=$rb->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e){}

// Recent deposits
$recentDeps=$pdo->prepare("SELECT valor,status,updated_at FROM depositos WHERE user_id=? ORDER BY updated_at DESC LIMIT 3");
$recentDeps->execute([$uid]); $recentDepsData=$recentDeps->fetchAll(PDO::FETCH_ASSOC);

$games=[
  ['Fortune Tiger','🐯','/jogos/tiger.php?t=tiger','linear-gradient(145deg,#1a0700,#380e00)','hot','🔥'],
  ['Aviator','✈️','/jogos/aviator.php','linear-gradient(145deg,#1a0300,#350800)','live','🔴'],
  ['Fortune Rabbit','🐰','/jogos/tiger.php?t=rabbit','linear-gradient(145deg,#0e0020,#200040)','hot','🔥'],
  ['Crash','🚀','/jogos/crash.php','linear-gradient(145deg,#001800,#003200)','live','🔴'],
  ['Mines','💣','/jogos/mines.php','linear-gradient(145deg,#1a0f00,#2e1c00)','hot','🔥'],
  ['Fortune Dragon','🐉','/jogos/tiger.php?t=dragon','linear-gradient(145deg,#000e2a,#001650)','new','✨'],
];
$pageTitle='Dashboard';
include __DIR__.'/../includes/app_head.php';
include __DIR__.'/../includes/app_navbar.php';
include __DIR__.'/../includes/app_sidebar.php';
?>

<div class="page-wrap" id="page-wrap">

  <!-- Live ticker -->
  <div class="ticker-rail mb-3" style="margin:-26px -22px 22px;border-radius:0;border-top:none;border-left:none;border-right:none">
    <div class="ticker-track" id="ticker-track"></div>
  </div>

  <div class="page-title">
    <div class="page-title-icon"><i data-lucide="layout-dashboard" style="width:17px;height:17px"></i></div>
    Olá, <?=htmlspecialchars(explode(' ',$nomeUser)[0])?>! 👋
    <?php if($u['admin']??0): ?>
    <a href="/admin/index.php" class="badge badge-red" style="margin-left:8px;text-decoration:none;font-size:.65rem">⚡ ADMIN PANEL</a>
    <?php endif; ?>
  </div>

  <!-- Balance Cards -->
  <div class="bal-grid">
    <div class="bal-card card-shine" style="--deco-color:rgba(0,230,118,.08)">
      <div class="bal-card-label"><i data-lucide="wallet" style="width:12px;height:12px"></i> Saldo</div>
      <div class="bal-card-value" data-live-bal>R$ <?=number_format($saldo,2,',','.')?></div>
      <div class="bal-card-sub up"><i data-lucide="trending-up" style="width:11px;height:11px"></i> Conta ativa</div>
      <div style="display:flex;gap:7px;margin-top:12px">
        <a href="/pages/deposit.php"  class="btn btn-green btn-sm" style="flex:1;justify-content:center;text-decoration:none">+ Depositar</a>
        <a href="/pages/withdraw.php" class="btn btn-neon  btn-sm" style="flex:1;justify-content:center;text-decoration:none">Sacar</a>
      </div>
      <div class="bal-card-icon bc-green"><i data-lucide="wallet" style="width:17px;height:17px"></i></div>
    </div>

    <div class="bal-card" style="--deco-color:rgba(255,214,0,.07)">
      <div class="bal-card-label"><i data-lucide="arrow-down-circle" style="width:12px;height:12px"></i> Total Depositado</div>
      <div class="bal-card-value" style="color:var(--green)">R$ <?=number_format($totalDep,2,',','.')?></div>
      <div class="bal-card-sub muted">Histórico acumulado</div>
      <div class="bal-card-icon bc-gold"><i data-lucide="arrow-down-circle" style="width:17px;height:17px"></i></div>
    </div>

    <div class="bal-card" style="--deco-color:rgba(255,23,68,.06)">
      <div class="bal-card-label"><i data-lucide="zap" style="width:12px;height:12px"></i> Total Apostado</div>
      <div class="bal-card-value">R$ <?=number_format($userWagered,2,',','.')?></div>
      <div class="bal-card-sub muted"><?=number_format($userBets,0,'.',',')?> rodadas</div>
      <div class="bal-card-icon bc-red"><i data-lucide="zap" style="width:17px;height:17px"></i></div>
    </div>

    <div class="bal-card" style="--deco-color:rgba(41,121,255,.06)">
      <div class="bal-card-label"><i data-lucide="crown" style="width:12px;height:12px"></i> Nível VIP</div>
      <div class="bal-card-value" style="color:var(--gold);font-size:1.3rem">🥉 Bronze</div>
      <div style="margin-top:10px"><div class="progress"><div class="progress-fill gold" style="width:<?=min(100,($totalDep/200)*100)?>%"></div></div></div>
      <div style="font-size:.68rem;color:var(--muted-2);margin-top:5px">R$ <?=number_format($totalDep,0,',','.')?> / R$ 200 → Prata</div>
      <div class="bal-card-icon bc-blue"><i data-lucide="crown" style="width:17px;height:17px"></i></div>
    </div>
  </div>

  <!-- Main 2-col -->
  <div style="display:grid;grid-template-columns:1fr 310px;gap:18px;align-items:start">
  <div>

    <!-- Platform stats -->
    <div class="stats-row" data-aos="fade-up">
      <div class="stat-card"><div class="stat-lbl">🌐 Usuários</div><div class="stat-val" style="color:var(--green)"><?=number_format($onlineUsers,0,'.',',')?></div><div class="stat-sub">Cadastrados</div></div>
      <div class="stat-card"><div class="stat-lbl">💰 Pago Hoje</div><div class="stat-val">R$ <?=number_format($paidToday,2,',','.')?></div><div class="stat-sub">Depósitos confirmados</div></div>
      <div class="stat-card"><div class="stat-lbl">⚡ Rodadas</div><div class="stat-val"><?=number_format($betsToday,0,'.',',')?></div><div class="stat-sub">Apostas hoje</div></div>
      <div class="stat-card"><div class="stat-lbl">🏦 Saldo Total</div><div class="stat-val" style="color:var(--gold)">R$ <?=number_format($jackpot,2,',','.')?></div><div class="stat-sub">Em carteiras</div></div>
    </div>

    <!-- Featured games -->
    <div class="mb-3" data-aos="fade-up" data-aos-delay="60">
      <div class="sec-head">
        <div class="sec-title"><div class="sec-title-dot"></div> Jogos em Destaque</div>
        <a href="/pages/games.php" class="sec-more">Ver todos <i data-lucide="chevron-right" style="width:14px;height:14px"></i></a>
      </div>
      <div class="games-grid">
        <?php foreach($games as [$name,$emoji,$url,$bg,$tag,$badge]): ?>
        <a href="<?=$url?>" class="game-card" data-name="<?=strtolower($name)?>">
          <div class="gthumb">
            <div class="gthumb-inner">
              <div class="gthumb-bg" style="background:<?=$bg?>"></div>
              <div class="g-emoji"><?=$emoji?></div>
            </div>
            <div class="g-badge <?=$tag==='live'?'gbadge-live':($tag==='new'?'gbadge-new':'gbadge-hot')?>"><?=$badge?> <?=strtoupper($tag)?></div>
            <div class="g-overlay"><button class="g-play-btn">▶&nbsp;Jogar</button></div>
          </div>
          <div class="g-info">
            <div class="g-name"><?=$name?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="card mb-3" data-aos="fade-up">
      <div class="sec-title mb-2"><div class="sec-title-dot"></div> Ações Rápidas</div>
      <div style="display:flex;gap:9px;flex-wrap:wrap">
        <a href="/pages/deposit.php"       class="btn btn-green  btn-md" style="text-decoration:none"><i data-lucide="plus-circle" style="width:15px;height:15px"></i> Depositar</a>
        <a href="/pages/withdraw.php"      class="btn btn-neon   btn-md" style="text-decoration:none"><i data-lucide="send" style="width:15px;height:15px"></i> Sacar</a>
        <a href="/jogos/aviator.php"       class="btn btn-primary btn-md" style="text-decoration:none">✈️ Aviator</a>
        <a href="/jogos/tiger.php?t=tiger" class="btn btn-ghost   btn-md" style="text-decoration:none">🐯 Fortune Tiger</a>
        <a href="/pages/vip.php"           class="btn btn-gold    btn-md" style="text-decoration:none">👑 VIP</a>
        <?php if($u['admin']??0): ?><a href="/admin/index.php" class="btn btn-primary btn-md" style="text-decoration:none;background:rgba(255,23,68,.2)">⚡ Admin</a><?php endif; ?>
      </div>
    </div>

    <!-- Recent bets -->
    <div class="card" data-aos="fade-up">
      <div class="sec-head">
        <div class="sec-title"><div class="sec-title-dot"></div> Últimas Apostas</div>
        <a href="/pages/history.php" class="sec-more">Ver tudo <i data-lucide="chevron-right" style="width:14px;height:14px"></i></a>
      </div>
      <?php if(empty($recentBets)): ?>
      <div style="text-align:center;padding:30px;color:var(--muted-2)">Nenhuma aposta ainda. <a href="/pages/games.php" style="color:var(--red)">Jogar agora!</a></div>
      <?php else: ?>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>Jogo</th><th>Aposta</th><th>Mult.</th><th>Resultado</th><th>Quando</th></tr></thead>
          <tbody>
          <?php foreach($recentBets as $b):
            $win=($b['resultado']??'')==='win';
            $profit=$win?('+R$ '.number_format($b['valor_ganho']-$b['valor_aposta'],2,',','.')):('-R$ '.number_format($b['valor_aposta'],2,',','.'));
          ?>
          <tr>
            <td><?=htmlspecialchars($b['jogo']??'—')?></td>
            <td>R$ <?=number_format($b['valor_aposta'],2,',','.')?></td>
            <td style="color:var(--gold);font-weight:700"><?=number_format($b['multiplicador']??0,2,',','.')?>x</td>
            <td><span class="badge <?=$win?'badge-green':'badge-red'?>"><?=$profit?></span></td>
            <td style="color:var(--muted-2);font-size:.75rem"><?=date('d/m H:i',strtotime($b['created_at']??'now'))?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- /left -->

  <!-- Right column -->
  <div style="display:flex;flex-direction:column;gap:14px">

    <!-- Recent deposits -->
    <div class="card card-solid" data-aos="fade-left">
      <div class="sec-head" style="margin-bottom:10px">
        <div class="sec-title"><div class="sec-title-dot"></div> Depósitos Recentes</div>
        <a href="/pages/wallet.php" class="sec-more" style="font-size:.7rem">Ver mais</a>
      </div>
      <?php if(empty($recentDepsData)): ?>
      <div style="text-align:center;padding:20px;color:var(--muted-2);font-size:.78rem">Nenhum depósito ainda</div>
      <?php else: foreach($recentDepsData as $d):
        $s2=strtoupper($d['status']??'');
        $cls=$s2==='PAID'?'badge-green':'badge-yellow';
      ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.03)">
        <div>
          <div style="font-weight:700;font-size:.85rem;color:var(--green)">R$ <?=number_format($d['valor'],2,',','.')?></div>
          <div style="font-size:.68rem;color:var(--muted-2)"><?=date('d/m H:i',strtotime($d['updated_at']??'now'))?></div>
        </div>
        <span class="badge <?=$cls?>" style="font-size:.65rem"><?=$s2?></span>
      </div>
      <?php endforeach; endif; ?>
      <a href="/pages/deposit.php" class="btn btn-green btn-sm" style="width:100%;margin-top:12px;justify-content:center;text-decoration:none">+ Novo Depósito</a>
    </div>

    <!-- Realtime feed -->
    <div class="card card-solid" data-aos="fade-left" data-aos-delay="60">
      <div class="sec-head" style="margin-bottom:8px">
        <div class="sec-title"><div class="sec-title-dot"></div> Feed ao Vivo</div>
        <span class="badge badge-live"><span class="live-dot"></span> LIVE</span>
      </div>
      <div class="feed-scroll"><div class="feed-list" id="feed-list"></div></div>
    </div>

    <!-- Chat -->
    <div class="card card-solid" style="padding:0;overflow:hidden" data-aos="fade-left" data-aos-delay="120">
      <div style="padding:13px 14px 10px;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;justify-content:space-between">
        <div class="sec-title" style="margin:0"><div class="sec-title-dot"></div> Chat Global</div>
        <span class="badge badge-live" style="font-size:.62rem"><span class="live-dot"></span> Online</span>
      </div>
      <div class="chat-wrap" style="height:260px">
        <div class="chat-body" id="chat-body"></div>
        <div class="chat-footer">
          <input type="text" class="chat-in" id="chat-input" placeholder="Mensagem...">
          <button id="chat-send" class="btn btn-primary btn-sm btn-icon" style="width:32px;height:32px;flex-shrink:0">
            <i data-lucide="send" style="width:13px;height:13px"></i>
          </button>
        </div>
      </div>
    </div>

  </div><!-- /right -->
  </div><!-- /grid -->

</div>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
