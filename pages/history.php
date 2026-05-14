<?php
session_start();
require_once __DIR__ . '/../conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit; }
$isLogged = true; $uid = $_SESSION['usuario_id'];
$st = $pdo->prepare("SELECT nome, saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $user = $st->fetch(PDO::FETCH_ASSOC);
$saldo = $user['saldo'] ?? 0; $nomeUser = $user['nome'] ?? '';
$pageTitle = 'Histórico';
include __DIR__ . '/../includes/app_head.php';
include __DIR__ . '/../includes/app_navbar.php';
include __DIR__ . '/../includes/app_sidebar.php';
?>
<div class="app"><div class="page-content" id="page-content">

  <div class="page-title" data-aos="fade-right">
    <i data-lucide="clock" class="pt-icon"></i> Histórico
  </div>

  <!-- Stats Row -->
  <div class="stats-row mb-3">
    <?php foreach([
      ['Apostas','0','Rodadas totais','var(--text)'],
      ['Maior Ganho','R$ 0,00','Maior pagamento','var(--gold)'],
      ['Maior Sequência','0x','Multiplicador máx','var(--green)'],
      ['Win Rate','0%','Taxa de vitória','var(--blue)'],
    ] as [$l,$v,$s,$c]): ?>
    <div class="stat-card" data-aos="fade-up">
      <div class="s-label"><?=$l?></div>
      <div class="s-value" style="color:<?=$c?>"><?=$v?></div>
      <div class="s-sub"><?=$s?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Filter bar -->
  <div class="card mb-3" data-aos="fade-up" style="padding:14px 18px">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <div class="tabs" style="margin:0;border:none;padding:0">
        <button class="tab-btn active" data-tab="h-bets">Apostas</button>
        <button class="tab-btn" data-tab="h-deps">Depósitos</button>
        <button class="tab-btn" data-tab="h-saqs">Saques</button>
      </div>
      <div style="margin-left:auto;display:flex;gap:8px">
        <select class="form-input" style="width:auto;padding:7px 12px;font-size:.8rem">
          <option>Todos os jogos</option>
          <option>Fortune Tiger</option>
          <option>Aviator</option>
          <option>Crash</option>
          <option>Mines</option>
        </select>
        <select class="form-input" style="width:auto;padding:7px 12px;font-size:.8rem">
          <option>Últimos 7 dias</option>
          <option>Últimos 30 dias</option>
          <option>Este mês</option>
          <option>Todos</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Tables -->
  <div class="card" data-aos="fade-up" data-tab-group>
    <div class="tab-panel active" id="h-bets">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Jogo</th><th>Aposta</th><th>Multiplicador</th><th>Resultado</th><th>Data</th></tr></thead>
          <tbody id="bets-tbody">
            <?php
            $games = ['Fortune Tiger','Aviator','Fortune Rabbit','Crash','Mines','Plinko','Dice'];
            for ($i=1;$i<=12;$i++):
              $g    = $games[array_rand($games)];
              $bet  = [5,10,15,20,25,50][rand(0,5)];
              $mult = round(rand(0,250)/100,2);
              $win  = round($bet*$mult,2);
              $isWin= $mult >= 1.0;
            ?>
            <tr>
              <td style="color:var(--muted);font-size:.78rem">#<?=$i?></td>
              <td><?=$g?></td>
              <td>R$ <?=number_format($bet,2,',','.')?></td>
              <td style="font-weight:700;color:<?=$isWin?'var(--green)':'var(--red)'?>"><?=$mult?>x</td>
              <td><span class="badge <?=$isWin?'badge-green':'badge-red'?>"><?=$isWin?'+R$ '.number_format($win,2,',','.'):'-R$ '.number_format($bet,2,',','.')?></span></td>
              <td style="color:var(--muted);font-size:.78rem">há <?=rand(1,72)?>h</td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-panel" id="h-deps">
      <div class="table-wrap"><table><thead><tr><th>Valor</th><th>Método</th><th>Status</th><th>Data</th></tr></thead>
      <tbody>
        <tr><td style="color:var(--green);font-weight:700">R$ 100,00</td><td>PIX</td><td><span class="badge badge-green">Aprovado</span></td><td style="color:var(--muted);font-size:.78rem">Hoje</td></tr>
      </tbody></table></div>
    </div>
    <div class="tab-panel" id="h-saqs">
      <div class="table-wrap"><table><thead><tr><th>Valor</th><th>Chave PIX</th><th>Status</th><th>Data</th></tr></thead>
      <tbody>
        <tr><td style="color:var(--red);font-weight:700">R$ 50,00</td><td style="color:var(--muted)">***email</td><td><span class="badge badge-gold">Processando</span></td><td style="color:var(--muted);font-size:.78rem">Hoje</td></tr>
      </tbody></table></div>
    </div>
  </div>

</div></div>
<?php include __DIR__ . '/../includes/app_footer.php'; ?>
