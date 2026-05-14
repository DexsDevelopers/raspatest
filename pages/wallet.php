<?php
session_start();
require_once __DIR__.'/../conexao.php';
if(!isset($_SESSION['usuario_id'])){header('Location: /login.php');exit;}
$isLogged=true; $uid=$_SESSION['usuario_id'];
$st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);
$saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; $vipLevel=1;

// Stats
$totalDep = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM depositos WHERE user_id=? AND status='PAID'");
$totalDep->execute([$uid]); $totalDeposited = $totalDep->fetchColumn();

$totalSaq = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM saques WHERE user_id=? AND status='PAID'");
$totalSaq->execute([$uid]); $totalWithdrawn = $totalSaq->fetchColumn();

$pendSaq = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM saques WHERE user_id=? AND status='PENDING'");
$pendSaq->execute([$uid]); $pendingWithdraw = $pendSaq->fetchColumn();

// Transactions
$deps = $pdo->prepare("SELECT valor,status,created_at,updated_at FROM depositos WHERE user_id=? ORDER BY created_at DESC LIMIT 30");
$deps->execute([$uid]); $deposits = $deps->fetchAll(PDO::FETCH_ASSOC);

$saqs = $pdo->prepare("SELECT valor,status,created_at,updated_at FROM saques WHERE user_id=? ORDER BY created_at DESC LIMIT 30");
$saqs->execute([$uid]); $withdrawals = $saqs->fetchAll(PDO::FETCH_ASSOC);

$pageTitle='Carteira';
include __DIR__.'/../includes/app_head.php';
include __DIR__.'/../includes/app_navbar.php';
include __DIR__.'/../includes/app_sidebar.php';
?>
<div class="page-wrap" id="page-wrap">

  <div class="page-title"><div class="page-title-icon"><i data-lucide="wallet" style="width:17px;height:17px"></i></div> Minha Carteira</div>

  <!-- Balance cards -->
  <div class="bal-grid" data-aos="fade-up">
    <div class="bal-card card-shine" style="--deco-color:rgba(0,230,118,.08)">
      <div class="bal-card-label"><i data-lucide="wallet" style="width:12px;height:12px"></i> Saldo Disponível</div>
      <div class="bal-card-value" data-live-bal>R$ <?=number_format($saldo,2,',','.')?></div>
      <div style="display:flex;gap:7px;margin-top:12px">
        <a href="/pages/deposit.php"  class="btn btn-green btn-sm" style="flex:1;justify-content:center;text-decoration:none">+ Depositar</a>
        <a href="/pages/withdraw.php" class="btn btn-neon  btn-sm" style="flex:1;justify-content:center;text-decoration:none">Sacar</a>
      </div>
      <div class="bal-card-icon bc-green"><i data-lucide="wallet" style="width:17px;height:17px"></i></div>
    </div>
    <div class="bal-card">
      <div class="bal-card-label"><i data-lucide="arrow-down-circle" style="width:12px;height:12px"></i> Total Depositado</div>
      <div class="bal-card-value" style="color:var(--green)">R$ <?=number_format($totalDeposited,2,',','.')?></div>
      <div class="bal-card-sub muted"><?=count($deposits)?> depósito(s)</div>
      <div class="bal-card-icon bc-green"><i data-lucide="arrow-down-circle" style="width:17px;height:17px"></i></div>
    </div>
    <div class="bal-card">
      <div class="bal-card-label"><i data-lucide="arrow-up-circle" style="width:12px;height:12px"></i> Total Sacado</div>
      <div class="bal-card-value">R$ <?=number_format($totalWithdrawn,2,',','.')?></div>
      <div class="bal-card-sub muted"><?=count($withdrawals)?> saque(s)</div>
      <div class="bal-card-icon bc-red"><i data-lucide="arrow-up-circle" style="width:17px;height:17px"></i></div>
    </div>
    <div class="bal-card">
      <div class="bal-card-label"><i data-lucide="clock" style="width:12px;height:12px"></i> Saques Pendentes</div>
      <div class="bal-card-value" style="color:var(--gold)">R$ <?=number_format($pendingWithdraw,2,',','.')?></div>
      <div class="bal-card-sub muted">Em processamento</div>
      <div class="bal-card-icon bc-gold"><i data-lucide="clock" style="width:17px;height:17px"></i></div>
    </div>
  </div>

  <!-- Transactions table -->
  <div class="card" data-aos="fade-up" style="margin-top:18px">
    <div class="tabs mb-3">
      <button class="tab active" data-tab="tab-deps">Depósitos</button>
      <button class="tab" data-tab="tab-saqs">Saques</button>
    </div>

    <div data-tab-group>
      <!-- Deposits -->
      <div class="tab-panel active" id="tab-deps">
        <?php if(empty($deposits)): ?>
        <div style="text-align:center;padding:40px;color:var(--muted-2)">Nenhum depósito ainda. <a href="/pages/deposit.php" style="color:var(--green)">Fazer primeiro depósito</a></div>
        <?php else: ?>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach($deposits as $d):
              $st2=strtoupper($d['status']??'');
              $cls=$st2==='PAID'?'badge-green':($st2==='PENDING'?'badge-yellow':'badge-red');
              $lbl=$st2==='PAID'?'Pago':($st2==='PENDING'?'Pendente':'Cancelado');
            ?>
            <tr>
              <td style="font-weight:700;color:var(--green)">R$ <?=number_format($d['valor'],2,',','.')?></td>
              <td><span class="badge <?=$cls?>"><?=$lbl?></span></td>
              <td style="color:var(--muted-2);font-size:.75rem"><?=date('d/m/Y H:i',strtotime($d['updated_at']??$d['created_at']??'now'))?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Withdrawals -->
      <div class="tab-panel" id="tab-saqs">
        <?php if(empty($withdrawals)): ?>
        <div style="text-align:center;padding:40px;color:var(--muted-2)">Nenhum saque solicitado ainda.</div>
        <?php else: ?>
        <div class="tbl-wrap">
          <table>
            <thead><tr><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach($withdrawals as $s):
              $st2=strtoupper($s['status']??'');
              $cls=$st2==='PAID'?'badge-green':($st2==='PENDING'?'badge-yellow':'badge-red');
              $lbl=$st2==='PAID'?'Pago':($st2==='PENDING'?'Pendente':'Recusado');
            ?>
            <tr>
              <td style="font-weight:700">R$ <?=number_format($s['valor'],2,',','.')?></td>
              <td><span class="badge <?=$cls?>"><?=$lbl?></span></td>
              <td style="color:var(--muted-2);font-size:.75rem"><?=date('d/m/Y H:i',strtotime($s['updated_at']??$s['created_at']??'now'))?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div><!-- /tab-group -->
  </div>

</div>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
