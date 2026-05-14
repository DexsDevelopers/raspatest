<?php
session_start();
require_once __DIR__ . '/../conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit; }
$isLogged = true; $uid = $_SESSION['usuario_id'];
$st = $pdo->prepare("SELECT nome, saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $user = $st->fetch(PDO::FETCH_ASSOC);
$saldo = $user['saldo'] ?? 0; $nomeUser = $user['nome'] ?? '';
$msg = ''; $msgType = 'info';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $amt      = floatval($_POST['amount']   ?? 0);
    $pixKey   = trim($_POST['pix_key']      ?? '');
    $pixType  = trim($_POST['pix_type']     ?? '');
    if ($amt < 20)           { $msg='Valor mínimo para saque: R$ 20,00'; $msgType='error'; }
    elseif ($amt > $saldo)   { $msg='Saldo insuficiente.'; $msgType='error'; }
    elseif (!$pixKey)        { $msg='Informe a chave PIX.'; $msgType='error'; }
    else {
        $upd = $pdo->prepare("UPDATE usuarios SET saldo=saldo-? WHERE id=? AND saldo>=?");
        $upd->execute([$amt,$uid,$amt]);
        if ($upd->rowCount()) {
            $msg='Saque solicitado! Processamento em até 24h.'; $msgType='success';
            $saldo -= $amt;
        } else { $msg='Erro ao processar.'; $msgType='error'; }
    }
}
$pageTitle = 'Sacar';
include __DIR__ . '/../includes/app_head.php';
include __DIR__ . '/../includes/app_navbar.php';
include __DIR__ . '/../includes/app_sidebar.php';
?>
<div class="app"><div class="page-content" id="page-content">

  <div class="page-title" data-aos="fade-right">
    <i data-lucide="arrow-up-circle" class="pt-icon"></i> Solicitar Saque
  </div>

  <?php if ($msg): ?>
  <div style="padding:12px 16px;border-radius:10px;border:1px solid;margin-bottom:20px;
    <?= $msgType==='success'?'background:rgba(0,230,118,.08);border-color:rgba(0,230,118,.25);color:var(--green)':'background:rgba(255,23,68,.08);border-color:rgba(255,23,68,.25);color:var(--red)' ?>">
    <?= $msgType==='success'?'✅':'❌' ?> <?= htmlspecialchars($msg) ?>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">
  <form method="POST">
    <div class="card" data-aos="fade-up">
      <div class="section-title"><i data-lucide="banknote" style="width:16px;height:16px;color:var(--red)"></i> Dados do Saque</div>

      <div style="background:rgba(255,214,0,.06);border:1px solid rgba(255,214,0,.2);border-radius:10px;padding:14px;margin-bottom:18px">
        <div style="font-size:.75rem;color:var(--muted);margin-bottom:2px">Saldo disponível</div>
        <div style="font-size:1.6rem;font-weight:900;color:var(--gold)">R$ <?=number_format($saldo,2,',','.')?></div>
      </div>

      <div class="form-group">
        <label class="form-label">Tipo de Chave PIX</label>
        <select name="pix_type" class="form-input">
          <option value="cpf">CPF</option>
          <option value="email">E-mail</option>
          <option value="phone">Celular</option>
          <option value="random">Chave Aleatória</option>
          <option value="cnpj">CNPJ</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Chave PIX</label>
        <input type="text" name="pix_key" class="form-input" placeholder="Sua chave PIX" required>
      </div>

      <div class="amount-grid mb-2">
        <?php foreach([20,50,100,200,500,1000] as $v): ?>
        <button type="button" class="amount-btn" data-target="saqAmt" data-value="<?=$v?>">R$ <?=$v?></button>
        <?php endforeach; ?>
      </div>
      <div class="form-group">
        <label class="form-label">Valor a Sacar</label>
        <div class="input-prefix">
          <span>R$</span>
          <input type="number" id="saqAmt" name="amount" class="form-input" placeholder="0,00" min="20" step="0.01" max="<?=$saldo?>" required>
        </div>
        <div class="form-hint">Mínimo: R$ 20,00 · Taxa: Grátis · Prazo: até 24h</div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">
        <i data-lucide="arrow-up-circle" style="width:16px;height:16px"></i> Solicitar Saque
      </button>
    </div>
  </form>

  <div>
    <div class="card" data-aos="fade-left">
      <div class="section-title"><i data-lucide="shield-check" style="width:16px;height:16px;color:var(--red)"></i> Informações</div>
      <div style="display:flex;flex-direction:column;gap:12px;font-size:.82rem">
        <div style="padding:10px;background:rgba(255,255,255,.03);border-radius:8px">⏱ Prazo de processamento: até 24 horas úteis.</div>
        <div style="padding:10px;background:rgba(255,255,255,.03);border-radius:8px">💳 Depósito mínimo de R$ 10 antes de sacar.</div>
        <div style="padding:10px;background:rgba(255,255,255,.03);border-radius:8px">🔐 Conta verificada obrigatória para saques.</div>
        <div style="padding:10px;background:rgba(255,255,255,.03);border-radius:8px">✅ Sem taxas sobre saques PIX.</div>
      </div>
    </div>
  </div>
  </div>

</div></div>
<?php include __DIR__ . '/../includes/app_footer.php'; ?>
