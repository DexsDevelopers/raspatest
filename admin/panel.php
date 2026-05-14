<?php
session_start();
require_once __DIR__ . '/../conexao.php';
// Simple admin auth
if (!isset($_SESSION['admin']) && ($_POST['admin_pass']??'')!=='admin123') {
    if (!isset($_SESSION['admin'])) { ?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin — RaspaPix</title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/main.css"></head><body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px">
<div class="card" style="width:100%;max-width:360px">
  <div style="text-align:center;margin-bottom:20px"><div style="font-size:2rem">🔐</div><h2 style="font-weight:900">Admin RaspaPix</h2></div>
  <form method="POST"><input type="hidden" name="admin_pass" value="">
    <div class="form-group"><label class="form-label">Senha</label><input type="password" name="admin_pass" class="form-input" required autofocus></div>
    <button class="btn btn-primary btn-full">Entrar</button>
  </form>
</div></div></body></html>
<?php exit; }
} else { $_SESSION['admin'] = true; }

$users    = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$saldoSum = $pdo->query("SELECT COALESCE(SUM(saldo),0) FROM usuarios")->fetchColumn();
$usersWk  = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE criado_em >= DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();
$pageTitle = 'Admin Panel';
$isLogged  = false; $saldo = 0; $nomeUser = 'Admin';
?>
<!DOCTYPE html><html lang="pt-BR"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin — 🍀 RaspaPix</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<link rel="stylesheet" href="/assets/css/main.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<style>
.admin-nav{height:56px;background:var(--bg2);border-bottom:1px solid rgba(255,23,68,.15);display:flex;align-items:center;padding:0 20px;gap:12px;position:sticky;top:0;z-index:100}
.admin-badge{background:rgba(255,23,68,.15);border:1px solid rgba(255,23,68,.3);color:var(--red);border-radius:5px;padding:2px 8px;font-size:.65rem;font-weight:800}
.sidebar-admin{width:220px;background:var(--bg2);border-right:1px solid var(--border);padding:16px 0 40px;position:fixed;top:56px;bottom:0;overflow-y:auto}
.adm-content{margin-left:220px;padding:24px;min-height:calc(100vh - 56px)}
@media(max-width:768px){.sidebar-admin{display:none}.adm-content{margin-left:0}}
</style>
</head><body>
<canvas id="particles-canvas"></canvas>

<nav class="admin-nav">
  <span style="font-size:1.1rem;font-weight:900">🍀 RaspaPix</span>
  <span class="admin-badge">ADMIN</span>
  <div style="margin-left:auto;display:flex;gap:8px">
    <a href="/pages/dashboard.php" class="btn btn-ghost btn-sm"><i data-lucide="external-link" style="width:13px;height:13px"></i> Ver Site</a>
    <a href="/logout.php" class="btn btn-neon btn-sm"><i data-lucide="log-out" style="width:13px;height:13px"></i> Sair</a>
  </div>
</nav>

<aside class="sidebar-admin">
  <?php $items = [
    ['layout-dashboard','Dashboard','#dash'],
    ['users','Usuários','#users'],
    ['arrow-down-circle','Depósitos','#deposits'],
    ['arrow-up-circle','Saques','#withdrawals'],
    ['gamepad-2','Jogos','#games'],
    ['bar-chart-2','Relatórios','#reports'],
    ['settings','Configurações','#settings'],
  ];
  foreach($items as [$ic,$lb,$hr]): ?>
  <a href="<?=$hr?>" class="sidebar-item"><i data-lucide="<?=$ic?>" class="si-icon"></i> <?=$lb?></a>
  <?php endforeach; ?>
</aside>

<main class="adm-content">
  <div class="page-title" data-aos="fade-right">
    <i data-lucide="layout-dashboard" class="pt-icon"></i> Dashboard Admin
  </div>

  <!-- KPI Cards -->
  <div class="balance-grid mb-3">
    <?php
    $kpis = [
      ['Usuários Total', $users, '+'.($usersWk??0).' esta semana', 'bc-icon-blue', 'users'],
      ['Saldo Plataforma', 'R$ '.number_format($saldoSum,2,',','.'), 'Soma de todos os saldos', 'bc-icon-green', 'wallet'],
      ['Depósitos Hoje', 'R$ 0,00', '0 transações', 'bc-icon-gold', 'arrow-down-circle'],
      ['Saques Pendentes', '0', 'Aguardando aprovação', 'bc-icon-red', 'clock'],
    ];
    foreach($kpis as [$l,$v,$s,$ic,$ico]): ?>
    <div class="balance-card" data-aos="fade-up">
      <div class="bc-label"><?=$l?></div>
      <div class="bc-value"><?=$v?></div>
      <div style="font-size:.72rem;color:var(--muted);margin-top:4px"><?=$s?></div>
      <div class="bc-icon <?=$ic?>"><i data-lucide="<?=$ico?>" style="width:20px;height:20px"></i></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Users Table -->
  <div class="card mb-3" id="users" data-aos="fade-up">
    <div class="section-title" style="justify-content:space-between">
      <span style="display:flex;align-items:center;gap:8px"><i data-lucide="users" style="width:18px;height:18px;color:var(--red)"></i> Usuários Recentes</span>
      <input type="text" class="form-input" style="width:200px" placeholder="🔍 Buscar usuário..." id="userSearch" oninput="filterUsers(this.value)">
    </div>
    <div class="table-wrap">
      <table id="usersTable">
        <thead><tr><th>ID</th><th>Nome</th><th>Saldo</th><th>Cadastro</th><th>Ações</th></tr></thead>
        <tbody>
          <?php
          $ustmt = $pdo->query("SELECT id,nome,saldo,criado_em FROM usuarios ORDER BY id DESC LIMIT 20");
          while($u=$ustmt->fetch(PDO::FETCH_ASSOC)):
          ?>
          <tr class="user-row">
            <td style="color:var(--muted);font-size:.78rem">#<?=$u['id']?></td>
            <td><div style="font-weight:600"><?=htmlspecialchars($u['nome']??'—')?></div></td>
            <td style="color:var(--gold);font-weight:700">R$ <?=number_format($u['saldo']??0,2,',','.')?></td>
            <td style="color:var(--muted);font-size:.78rem"><?=date('d/m/Y',strtotime($u['criado_em']??'now'))?></td>
            <td>
              <button class="btn btn-ghost btn-sm" onclick="editUser(<?=$u['id']?>,<?=$u['saldo']?>)"><i data-lucide="edit" style="width:12px;height:12px"></i></button>
              <button class="btn btn-sm" style="background:rgba(255,23,68,.1);border:1px solid rgba(255,23,68,.25);color:var(--red)" onclick="if(confirm('Banir?')){}"><i data-lucide="ban" style="width:12px;height:12px"></i></button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Withdrawals -->
  <div class="card" id="withdrawals" data-aos="fade-up">
    <div class="section-title"><i data-lucide="arrow-up-circle" style="width:18px;height:18px;color:var(--red)"></i> Saques Pendentes</div>
    <?php
    $saqStmt = $pdo->query("SELECT s.*,u.nome FROM saques s LEFT JOIN usuarios u ON u.id=s.usuario_id WHERE s.status='pendente' ORDER BY s.id DESC LIMIT 20 " );
    $saqs = $saqStmt->fetchAll(PDO::FETCH_ASSOC);
    if(empty($saqs)): ?>
    <div style="text-align:center;padding:28px;color:var(--muted)"><i data-lucide="check-circle" style="width:32px;height:32px;margin-bottom:8px;opacity:.3"></i><p>Nenhum saque pendente.</p></div>
    <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>#</th><th>Usuário</th><th>Valor</th><th>Chave PIX</th><th>Data</th><th>Ações</th></tr></thead>
      <tbody>
        <?php foreach($saqs as $s): ?>
        <tr>
          <td style="color:var(--muted)">#<?=$s['id']?></td>
          <td><?=htmlspecialchars($s['nome']??'—')?></td>
          <td style="color:var(--red);font-weight:700">R$ <?=number_format($s['valor']??0,2,',','.')?></td>
          <td style="font-size:.8rem;color:var(--muted)"><?=htmlspecialchars($s['chave_pix']??'—')?></td>
          <td style="font-size:.78rem;color:var(--muted)"><?=date('d/m H:i',strtotime($s['criado_em']??'now'))?></td>
          <td style="display:flex;gap:6px">
            <form method="POST" action="/admin/saques.php" style="display:inline"><input type="hidden" name="id" value="<?=$s['id']?>"><input type="hidden" name="action" value="aprovar"><button class="btn btn-green btn-sm" type="submit">✓</button></form>
            <form method="POST" action="/admin/saques.php" style="display:inline"><input type="hidden" name="id" value="<?=$s['id']?>"><input type="hidden" name="action" value="rejeitar"><button class="btn btn-sm" style="background:rgba(255,23,68,.1);border:1px solid rgba(255,23,68,.3);color:var(--red)" type="submit">✕</button></form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php endif; ?>
  </div>
</main>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">✏️ Editar Saldo</div><button class="modal-close" onclick="closeModal('editUserModal')">×</button></div>
    <div class="modal-body">
      <form method="POST" action="?act=editSaldo">
        <input type="hidden" name="uid" id="editUid">
        <div class="form-group"><label class="form-label">Novo Saldo (R$)</label><input type="number" name="saldo" id="editSaldo" class="form-input" step="0.01" min="0" required></div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('editUserModal')">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
      </form>
    </div>
  </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
function editUser(id,saldo){document.getElementById('editUid').value=id;document.getElementById('editSaldo').value=saldo;openModal('editUserModal');}
function filterUsers(q){const s=q.toLowerCase();document.querySelectorAll('.user-row').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(s)?'':'none'});}
</script>
</body></html>
