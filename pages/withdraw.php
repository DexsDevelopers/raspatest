<?php
session_start();
require_once __DIR__.'/../conexao.php';
if(!isset($_SESSION['usuario_id'])){header('Location: /login.php');exit;}
$isLogged=true; $uid=$_SESSION['usuario_id'];
$st=$pdo->prepare("SELECT nome,saldo,cpf FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);
$saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; $cpfUser=$u['cpf']??''; $vipLevel=1;
$pageTitle='Sacar';
include __DIR__.'/../includes/app_head.php';
include __DIR__.'/../includes/app_navbar.php';
include __DIR__.'/../includes/app_sidebar.php';
?>
<div class="page-wrap" id="page-wrap">

  <div class="page-title" data-aos="fade-down">
    <div class="page-title-icon"><i data-lucide="arrow-up-circle" style="width:17px;height:17px"></i></div>
    Solicitar Saque
  </div>

  <div style="display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start" data-aos="fade-up">

    <!-- Withdraw Form -->
    <div class="card card-glow">
      <div class="sec-title mb-4"><div class="sec-title-dot"></div> Dados do saque</div>

      <div class="form-group">
        <label class="form-label">Valor do Saque</label>
        <div class="amt-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px">
          <?php foreach([50,100,200,500,1000,2000,5000,10000] as $v): ?>
          <button class="amt-btn btn btn-ghost btn-sm" data-target="withdraw-amount" data-value="<?=$v?>">R$ <?=number_format($v,0,'.',',')?></button>
          <?php endforeach; ?>
        </div>
        <div class="form-ctrl-wrap">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted-2);font-size:.85rem;font-weight:600;pointer-events:none">R$</span>
          <input type="number" id="withdraw-amount" class="form-ctrl" style="padding-left:34px" placeholder="0,00" min="<?=$saqueMin?>" step="0.01" required>
        </div>
        <div style="font-size:.72rem;color:var(--muted-2);margin-top:5px">Mínimo: R$ <?=number_format($saqueMin,2,',','.')?>  ·  Disponível: <span style="color:var(--green);font-weight:700">R$ <?=number_format($saldo,2,',','.')?></span></div>
      </div>

      <div class="form-group">
        <label class="form-label">CPF</label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-id-card form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="text" id="withdraw-cpf" class="form-ctrl" placeholder="000.000.000-00" maxlength="14"
            value="<?=htmlspecialchars($cpfUser)?>" oninput="maskCPF(this)">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Tipo da Chave PIX</label>
        <select id="withdraw-pix-type" class="form-ctrl">
          <option value="cpf">CPF</option>
          <option value="email">E-mail</option>
          <option value="telefone">Telefone</option>
          <option value="aleatoria">Chave Aleatória</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Chave PIX</label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-key form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="text" id="withdraw-pix-key" class="form-ctrl" placeholder="Sua chave PIX">
        </div>
      </div>

      <button id="saque-btn" class="btn btn-neon btn-full btn-lg" onclick="solicitarSaque()">
        <i data-lucide="send" style="width:16px;height:16px"></i> Solicitar Saque
      </button>

      <div id="saque-result" style="display:none;margin-top:14px"></div>
    </div>

    <!-- Info -->
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="card card-solid">
        <div class="sec-title mb-3"><div class="sec-title-dot"></div> Saldo Disponível</div>
        <div style="font-size:1.6rem;font-weight:800;color:var(--green)">R$ <?=number_format($saldo,2,',','.')?></div>
        <a href="/pages/deposit.php" class="btn btn-green btn-sm" style="margin-top:12px;width:100%;justify-content:center;text-decoration:none">+ Depositar mais</a>
      </div>
      <div class="card card-solid">
        <div class="sec-title mb-3"><div class="sec-title-dot"></div> Informações</div>
        <?php foreach(['Saques processados via PIX','Prazo: até 24h úteis','Apenas 1 saque pendente por vez','Mínimo: R$ '.number_format($saqueMin,2,',','.')] as $i): ?>
        <div style="display:flex;gap:8px;align-items:flex-start;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.03)">
          <span style="color:var(--red);margin-top:1px">●</span>
          <span style="font-size:.78rem;color:var(--muted-2)"><?=$i?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<?php
$extraScripts = <<<JS
<script>
function maskCPF(el){
  let v=el.value.replace(/\D/g,'').slice(0,11);
  v=v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2');
  el.value=v;
}
function solicitarSaque(){
  const amount=parseFloat(document.getElementById('withdraw-amount').value);
  const cpf=document.getElementById('withdraw-cpf').value.replace(/\D/g,'');
  const pixKey=document.getElementById('withdraw-pix-key').value.trim();
  const pixType=document.getElementById('withdraw-pix-type').value;
  if(!amount||amount<<?=$saqueMin?>){toast('Valor mínimo: R$ <?=number_format($saqueMin,2,',','.')?>','warning');return;}
  if(cpf.length!==11){toast('CPF inválido','error');return;}
  if(!pixKey){toast('Informe a chave PIX','warning');return;}
  const btn=document.getElementById('saque-btn');
  btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin" style="margin-right:6px"></i> Processando...';
  fetch('/api/withdraw.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({amount,cpf,pix_key:pixKey,pix_type:pixType})
  })
  .then(r=>r.json())
  .then(d=>{
    btn.disabled=false;
    btn.innerHTML='<i data-lucide="send" style="width:16px;height:16px"></i> Solicitar Saque';
    if(typeof lucide!=='undefined') lucide.createIcons();
    const res=document.getElementById('saque-result');
    res.style.display='block';
    if(d.success){
      res.innerHTML='<div class="card" style="background:rgba(0,230,118,.04);border-color:rgba(0,230,118,.2);color:var(--green);text-align:center;padding:16px">✅ Saque solicitado com sucesso!<br><small style="color:var(--muted-2)">Será processado em até 24h úteis.</small></div>';
      toast('Saque solicitado!','success');
    } else {
      res.innerHTML='<div class="alert alert-error">'+( d.message||'Erro ao solicitar saque')+'</div>';
      toast(d.message||'Erro','error');
    }
  })
  .catch(e=>{btn.disabled=false;toast('Erro de conexão','error');console.error(e);});
}
</script>
JS;
include __DIR__.'/../includes/app_footer.php';
?>
