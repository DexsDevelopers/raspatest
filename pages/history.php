<?php
session_start();
require_once __DIR__.'/../conexao.php';
if(!isset($_SESSION['usuario_id'])){header('Location: /login.php');exit;}
$isLogged=true; $uid=$_SESSION['usuario_id'];
$st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);
$saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; $vipLevel=1;

// Apostas
$bets = $pdo->prepare("SELECT jogo,valor_aposta,valor_ganho,multiplicador,resultado,created_at FROM apostas WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
try { $bets->execute([$uid]); $betsData=$bets->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e){ $betsData=[]; }

// Depósitos
$deps = $pdo->prepare("SELECT valor,status,updated_at FROM depositos WHERE user_id=? ORDER BY updated_at DESC LIMIT 30");
$deps->execute([$uid]); $depsData=$deps->fetchAll(PDO::FETCH_ASSOC);

// Saques
$saqs = $pdo->prepare("SELECT valor,status,updated_at FROM saques WHERE user_id=? ORDER BY updated_at DESC LIMIT 30");
$saqs->execute([$uid]); $saqsData=$saqs->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalBets   = count($betsData);
$totalWon    = array_sum(array_column($betsData,'valor_ganho'));
$totalWagered= array_sum(array_column($betsData,'valor_aposta'));
$wins        = array_filter($betsData, fn($b)=>($b['resultado']??'loss')==='win');
$winRate     = $totalBets>0 ? round(count($wins)/$totalBets*100,1) : 0;
$biggestWin  = $totalBets>0 ? max(array_column($betsData,'valor_ganho')) : 0;

$pageTitle='Histórico';
include __DIR__.'/../includes/app_head.php';
include __DIR__.'/../includes/app_navbar.php';
include __DIR__.'/../includes/app_sidebar.php';
?>
<div class="page-wrap" id="page-wrap">

  <div class="page-title"><div class="page-title-icon"><i data-lucide="clock" style="width:17px;height:17px"></i></div> Histórico</div>

  <!-- Stats row -->
  <div class="stats-row" data-aos="fade-up">
    <div class="stat-card"><div class="stat-lbl">🎲 Total Apostas</div><div class="stat-val"><?=number_format($totalBets,0,'.',',')?></div></div>
    <div class="stat-card"><div class="stat-lbl">💰 Total Apostado</div><div class="stat-val">R$ <?=number_format($totalWagered,2,',','.')?></div></div>
    <div class="stat-card"><div class="stat-lbl">🏆 Maior Ganho</div><div class="stat-val" style="color:var(--gold)">R$ <?=number_format($biggestWin,2,',','.')?></div></div>
    <div class="stat-card"><div class="stat-lbl">📈 Taxa de Vitória</div><div class="stat-val" style="color:var(--green)"><?=$winRate?>%</div></div>
  </div>

  <!-- Tabs -->
  <div class="card" data-aos="fade-up" style="margin-top:18px">
    <div class="tabs mb-3">
      <button class="tab active" data-tab="tab-bets">Apostas</button>
      <button class="tab" data-tab="tab-hdeps">Depósitos</button>
      <button class="tab" data-tab="tab-hsaqs">Saques</button>
    </div>

    <div data-tab-group>
      <!-- Bets -->
      <div class="tab-panel active" id="tab-bets">
        <?php if(empty($betsData)): ?>
        <div style="text-align:center;padding:40px;color:var(--muted-2)">Nenhuma aposta registrada ainda. <a href="/pages/games.php" style="color:var(--red)">Jogar agora</a></div>
        <?php else: ?>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Jogo</th><th>Apostado</th><th>Mult.</th><th>Resultado</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach($betsData as $b):
              $win=($b['resultado']??'')=='win';
              $gain=$b['valor_ganho']??0;
              $bet=$b['valor_aposta']??0;
              $profit=$win?'+'.number_format($gain-$bet,2,',','.'):'-'.number_format($bet,2,',','.');
            ?>
            <tr>
              <td><?=htmlspecialchars($b['jogo']??'—')?></td>
              <td>R$ <?=number_format($bet,2,',','.')?></td>
              <td style="color:var(--gold);font-weight:700"><?=number_format($b['multiplicador']??0,2,',','.')?>x</td>
              <td><span class="badge <?=$win?'badge-green':'badge-red'?>">R$ <?=$profit?></span></td>
              <td style="color:var(--muted-2);font-size:.75rem"><?=date('d/m/Y H:i',strtotime($b['created_at']??'now'))?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Deposits history -->
      <div class="tab-panel" id="tab-hdeps">
        <?php if(empty($depsData)): ?>
        <div style="text-align:center;padding:40px;color:var(--muted-2)">Nenhum depósito.</div>
        <?php else: ?>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach($depsData as $d):
              $s2=strtoupper($d['status']??'');
              $cls=$s2==='PAID'?'badge-green':($s2==='PENDING'?'badge-yellow':'badge-red');
            ?>
            <tr>
              <td style="color:var(--green);font-weight:700">R$ <?=number_format($d['valor'],2,',','.')?></td>
              <td><span class="badge <?=$cls?>"><?=$s2?></span></td>
              <td style="color:var(--muted-2);font-size:.75rem"><?=date('d/m/Y H:i',strtotime($d['updated_at']??'now'))?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Withdrawals history -->
      <div class="tab-panel" id="tab-hsaqs">
        <?php if(empty($saqsData)): ?>
        <div style="text-align:center;padding:40px;color:var(--muted-2)">Nenhum saque.</div>
        <?php else: ?>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach($saqsData as $s):
              $s2=strtoupper($s['status']??'');
              $cls=$s2==='PAID'?'badge-green':($s2==='PENDING'?'badge-yellow':'badge-red');
            ?>
            <tr>
              <td style="font-weight:700">R$ <?=number_format($s['valor'],2,',','.')?></td>
              <td><span class="badge <?=$cls?>"><?=$s2?></span></td>
              <td style="color:var(--muted-2);font-size:.75rem"><?=date('d/m/Y H:i',strtotime($s['updated_at']??'now'))?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
