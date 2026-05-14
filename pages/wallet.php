<?php
session_start();
require_once __DIR__ . '/../conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit; }
$isLogged = true; $uid = $_SESSION['usuario_id'];
$st = $pdo->prepare("SELECT nome, saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $user = $st->fetch(PDO::FETCH_ASSOC);
$saldo = $user['saldo'] ?? 0; $nomeUser = $user['nome'] ?? '';
$pageTitle = 'Carteira';
include __DIR__ . '/../includes/app_head.php';
include __DIR__ . '/../includes/app_navbar.php';
include __DIR__ . '/../includes/app_sidebar.php';
?>
<div class="app"><div class="page-content" id="page-content">

  <div class="page-title" data-aos="fade-right">
    <i data-lucide="wallet" class="pt-icon"></i> Carteira
  </div>

  <!-- Balance Cards -->
  <div class="balance-grid mb-3">
    <div class="balance-card card-shine" data-aos="fade-up">
      <div class="bc-label">Saldo Disponível</div>
      <div class="bc-value">R$ <?= number_format($saldo,2,',','.') ?></div>
      <div style="display:flex;gap:8px;margin-top:12px">
        <a href="/pages/deposit.php"  class="btn btn-green btn-sm" style="flex:1;justify-content:center">+ Depositar</a>
        <a href="/pages/withdraw.php" class="btn btn-neon  btn-sm" style="flex:1;justify-content:center">Sacar</a>
      </div>
      <div class="bc-icon bc-icon-green"><i data-lucide="wallet" style="width:20px;height:20px"></i></div>
    </div>
    <div class="balance-card" data-aos="fade-up" data-aos-delay="80">
      <div class="bc-label">Total Depositado</div>
      <div class="bc-value">R$ 0,00</div>
      <div class="bc-change up">Histórico completo</div>
      <div class="bc-icon bc-icon-blue"><i data-lucide="arrow-down-circle" style="width:20px;height:20px"></i></div>
    </div>
    <div class="balance-card" data-aos="fade-up" data-aos-delay="160">
      <div class="bc-label">Total Sacado</div>
      <div class="bc-value">R$ 0,00</div>
      <div class="bc-change down">Histórico completo</div>
      <div class="bc-icon bc-icon-red"><i data-lucide="arrow-up-circle" style="width:20px;height:20px"></i></div>
    </div>
    <div class="balance-card" data-aos="fade-up" data-aos-delay="240">
      <div class="bc-label">Bônus Disponível</div>
      <div class="bc-value" style="color:var(--gold)">R$ 0,00</div>
      <div class="bc-change up">Faça 1º depósito</div>
      <div class="bc-icon bc-icon-gold"><i data-lucide="gift" style="width:20px;height:20px"></i></div>
    </div>
  </div>

  <!-- Transactions -->
  <div class="card" data-aos="fade-up">
    <div class="section-title" style="justify-content:space-between">
      <span style="display:flex;align-items:center;gap:8px"><i data-lucide="list" style="width:18px;height:18px;color:var(--red)"></i> Movimentações</span>
      <div class="tabs" style="margin:0;border:none;padding:0">
        <button class="tab-btn active" data-tab="t-all">Todas</button>
        <button class="tab-btn" data-tab="t-dep">Depósitos</button>
        <button class="tab-btn" data-tab="t-saq">Saques</button>
      </div>
    </div>
    <div data-tab-group>
      <div class="tab-panel active" id="t-all">
        <div class="table-wrap">
          <table>
            <thead><tr><th>Tipo</th><th>Valor</th><th>Método</th><th>Status</th><th>Data</th></tr></thead>
            <tbody>
              <?php
              $types = [['Depósito','R$ 100,00','PIX','approved'],['Aposta','R$ -25,00','Fortune Tiger','completed'],['Ganho','R$ +180,00','Fortune Tiger','completed'],['Saque','R$ -50,00','PIX','pending']];
              foreach($types as [$tp,$v,$m,$s]): $isPos = str_contains($v,'+') || $tp==='Depósito'; ?>
              <tr>
                <td><span class="badge <?=$isPos?'badge-green':'badge-red'?>"><?=$tp?></span></td>
                <td style="font-weight:700;color:<?=$isPos?'var(--green)':'var(--red)'?>"><?=$v?></td>
                <td style="color:var(--muted)"><?=$m?></td>
                <td><span class="badge <?=$s==='approved'||$s==='completed'?'badge-green':'badge-gold'?>"><?=$s==='approved'?'Aprovado':($s==='completed'?'Concluído':'Pendente')?></span></td>
                <td style="color:var(--muted);font-size:.78rem">Hoje</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="tab-panel" id="t-dep"><div class="table-wrap"><table><thead><tr><th>Valor</th><th>Status</th><th>Data</th></tr></thead><tbody><tr><td style="color:var(--green);font-weight:700">R$ 100,00</td><td><span class="badge badge-green">Aprovado</span></td><td style="color:var(--muted);font-size:.78rem">Hoje</td></tr></tbody></table></div></div>
      <div class="tab-panel" id="t-saq"><div class="table-wrap"><table><thead><tr><th>Valor</th><th>Chave PIX</th><th>Status</th><th>Data</th></tr></thead><tbody><tr><td style="color:var(--red);font-weight:700">R$ 50,00</td><td style="color:var(--muted)">***@email.com</td><td><span class="badge badge-gold">Pendente</span></td><td style="color:var(--muted);font-size:.78rem">Hoje</td></tr></tbody></table></div></div>
    </div>
  </div>

</div></div>
<?php include __DIR__ . '/../includes/app_footer.php'; ?>
