<?php
session_start();
require_once __DIR__ . '/../conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit; }
$isLogged=true; $uid=$_SESSION['usuario_id'];
$st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1"); $st->execute([$uid]);
$u=$st->fetch(PDO::FETCH_ASSOC); $saldo=$u['saldo']??0; $nomeUser=$u['nome']??'';
$refCode = strtoupper(substr(md5($uid),0,8));
$refLink = 'https://'.($_SERVER['HTTP_HOST']??'raspadinha.com').'/register.php?ref='.$refCode;
$pageTitle='Afiliados'; include __DIR__.'/../includes/app_head.php'; include __DIR__.'/../includes/app_navbar.php'; include __DIR__.'/../includes/app_sidebar.php';
?>
<div class="app"><div class="page-content" id="page-content">
<div class="page-title" data-aos="fade-right"><i data-lucide="users" class="pt-icon"></i> Programa de Afiliados</div>

<!-- Stats -->
<div class="balance-grid mb-3">
  <?php foreach([
    ['Indicados','0','Cadastros via link','bc-icon-blue','users'],
    ['Comissão Total','R$ 0,00','Ganhos acumulados','bc-icon-green','dollar-sign'],
    ['Comissão Pendente','R$ 0,00','A receber','bc-icon-gold','clock'],
    ['Taxa de Conversão','0%','Indicados que depositaram','bc-icon-red','percent'],
  ] as [$l,$v,$s,$ic,$ico]): ?>
  <div class="balance-card" data-aos="fade-up">
    <div class="bc-label"><?=$l?></div>
    <div class="bc-value"><?=$v?></div>
    <div class="bc-change up" style="font-size:.72rem;color:var(--muted)"><?=$s?></div>
    <div class="bc-icon <?=$ic?>"><i data-lucide="<?=$ico?>" style="width:20px;height:20px"></i></div>
  </div>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px">
<div>
  <!-- Link -->
  <div class="card mb-3" data-aos="fade-up">
    <div class="section-title"><i data-lucide="link" style="width:16px;height:16px;color:var(--red)"></i> Seu Link de Indicação</div>
    <div style="display:flex;gap:8px;margin-bottom:12px">
      <input type="text" class="form-input" value="<?=htmlspecialchars($refLink)?>" id="refLinkInput" readonly>
      <button class="btn btn-primary" onclick="copyText(document.getElementById('refLinkInput').value,'Link copiado!')">
        <i data-lucide="copy" style="width:14px;height:14px"></i> Copiar
      </button>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <button class="btn btn-ghost btn-sm" onclick="copyText(document.getElementById('refLinkInput').value,'Link copiado!')">
        <i data-lucide="share-2" style="width:13px;height:13px"></i> Compartilhar
      </button>
      <span style="background:rgba(255,214,0,.1);border:1px solid rgba(255,214,0,.25);border-radius:6px;padding:5px 12px;font-size:.75rem;font-weight:700;color:var(--gold)">
        Código: <?=$refCode?>
      </span>
    </div>
  </div>

  <!-- Commission tiers -->
  <div class="card mb-3" data-aos="fade-up">
    <div class="section-title"><i data-lucide="bar-chart" style="width:16px;height:16px;color:var(--red)"></i> Comissões</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Nível</th><th>Condição</th><th>Comissão</th><th>Bônus</th></tr></thead>
        <tbody>
          <?php foreach([
            ['Básico','0–9 indicados','5% GGR','—'],
            ['Prata','10–49 indicados','7% GGR','R$ 50 bônus'],
            ['Ouro','50–199 indicados','10% GGR','R$ 200 bônus'],
            ['Elite','200+ indicados','15% GGR','R$ 1.000 bônus'],
          ] as [$t,$c,$p,$b]): ?>
          <tr><td><span class="badge badge-gray"><?=$t?></span></td><td style="color:var(--muted);font-size:.8rem"><?=$c?></td><td style="font-weight:700;color:var(--green)"><?=$p?></td><td style="color:var(--gold);font-size:.8rem"><?=$b?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sub-affiliates -->
  <div class="card" data-aos="fade-up">
    <div class="section-title"><i data-lucide="git-branch" style="width:16px;height:16px;color:var(--red)"></i> Seus Indicados</div>
    <div style="text-align:center;padding:28px;color:var(--muted)">
      <i data-lucide="users" style="width:32px;height:32px;margin-bottom:8px;opacity:.3"></i>
      <p style="font-size:.875rem">Nenhum indicado ainda.<br>Compartilhe seu link!</p>
    </div>
  </div>
</div>

<div>
  <div class="card mb-3" data-aos="fade-left">
    <div class="section-title"><i data-lucide="help-circle" style="width:16px;height:16px;color:var(--red)"></i> Como Funciona</div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <?php foreach([
        ['1','Compartilhe','Envie seu link para amigos','var(--blue)'],
        ['2','Eles Cadastram','Seus amigos se registram','var(--green)'],
        ['3','Eles Depositam','Quando fazem o 1º depósito','var(--gold)'],
        ['4','Você Ganha','Comissão automática por GGR','var(--red)'],
      ] as [$n,$t,$d,$c]): ?>
      <div style="display:flex;gap:10px;align-items:flex-start">
        <div style="width:26px;height:26px;border-radius:50%;background:<?=$c?>;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:900;flex-shrink:0;color:#000"><?=$n?></div>
        <div><div style="font-size:.82rem;font-weight:700"><?=$t?></div><div style="font-size:.75rem;color:var(--muted)"><?=$d?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card" data-aos="fade-left" data-aos-delay="80">
    <div class="section-title"><i data-lucide="wallet" style="width:16px;height:16px;color:var(--red)"></i> Sacar Comissão</div>
    <div style="font-size:2rem;font-weight:900;text-align:center;color:var(--gold);margin-bottom:12px">R$ 0,00</div>
    <button class="btn btn-ghost btn-full" disabled>Mínimo: R$ 20,00</button>
  </div>
</div>
</div>

</div></div>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
