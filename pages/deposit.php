<?php
session_start();
require_once __DIR__.'/../conexao.php';
if(!isset($_SESSION['usuario_id'])){header('Location: /login.php');exit;}
$isLogged=true; $uid=$_SESSION['usuario_id'];
$st=$pdo->prepare("SELECT nome,saldo,cpf FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);
$saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; $cpfUser=$u['cpf']??''; $vipLevel=1;
$pageTitle='Depositar';
include __DIR__.'/../includes/app_head.php';
include __DIR__.'/../includes/app_navbar.php';
include __DIR__.'/../includes/app_sidebar.php';
?>
<div class="page-wrap" id="page-wrap">

  <div class="page-title" data-aos="fade-down">
    <div class="page-title-icon"><i data-lucide="arrow-down-circle" style="width:17px;height:17px"></i></div>
    Depositar via PIX
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start" data-aos="fade-up">

    <!-- Deposit Form -->
    <div class="card card-glow">
      <div class="sec-title mb-4"><div class="sec-title-dot"></div> Gerar cobrança PIX</div>

      <div class="form-group">
        <label class="form-label">Valor do Depósito</label>
        <div class="amt-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px">
          <?php foreach([10,25,50,100,250,500,1000,2000] as $v): ?>
          <button class="amt-btn btn btn-ghost btn-sm" data-target="amount-input" data-value="<?=$v?>">R$ <?=number_format($v,0,'.',',')?></button>
          <?php endforeach; ?>
        </div>
        <div class="form-ctrl-wrap">
          <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted-2);font-size:.85rem;font-weight:600;pointer-events:none">R$</span>
          <input type="number" id="amount-input" class="form-ctrl" style="padding-left:34px" placeholder="0,00" min="<?=$depositoMin?>" step="0.01" required>
        </div>
        <div style="font-size:.72rem;color:var(--muted-2);margin-top:5px">Mínimo: R$ <?=number_format($depositoMin,2,',','.')?></div>
      </div>

      <div class="form-group">
        <label class="form-label">CPF</label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-id-card form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="text" id="cpf-input" class="form-ctrl" placeholder="000.000.000-00" maxlength="14"
            value="<?=htmlspecialchars($cpfUser)?>" oninput="maskCPF(this)">
        </div>
      </div>

      <button id="gerar-btn" class="btn btn-green btn-full btn-lg" onclick="gerarPix()">
        <i data-lucide="zap" style="width:16px;height:16px"></i> Gerar PIX
      </button>

      <!-- PIX Result -->
      <div id="pix-result" style="display:none;margin-top:20px">
        <div class="card" style="background:rgba(0,230,118,.04);border-color:rgba(0,230,118,.15);text-align:center">
          <div style="font-size:.8rem;color:var(--muted-2);margin-bottom:8px">QR Code PIX</div>
          <img id="pix-qr" src="" alt="QR Code" style="width:160px;height:160px;margin:0 auto;display:block;border-radius:8px">
          <div style="margin-top:14px">
            <div style="font-size:.72rem;color:var(--muted-2);margin-bottom:6px">Copia e Cola</div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-sm);padding:10px;font-size:.72rem;word-break:break-all;font-family:monospace;color:var(--text-2)" id="pix-code"></div>
            <button class="btn btn-ghost btn-sm mt-2" onclick="copyPix()" style="width:100%;margin-top:8px">
              <i data-lucide="copy" style="width:13px;height:13px"></i> Copiar Código
            </button>
          </div>
          <div style="margin-top:14px;padding:10px;background:rgba(0,230,118,.06);border-radius:var(--r-sm);font-size:.78rem;color:var(--green)">
            ✅ Após o pagamento, seu saldo será creditado em até <strong>1 minuto</strong>.
          </div>
          <div id="pix-timer" style="font-size:.72rem;color:var(--muted-2);margin-top:8px"></div>
        </div>
      </div>
    </div>

    <!-- Info column -->
    <div style="display:flex;flex-direction:column;gap:14px">
      <div class="card card-solid">
        <div class="sec-title mb-3"><div class="sec-title-dot"></div> Saldo Atual</div>
        <div style="font-size:1.6rem;font-weight:800;color:var(--green)">R$ <?=number_format($saldo,2,',','.')?></div>
      </div>
      <div class="card card-solid">
        <div class="sec-title mb-3"><div class="sec-title-dot"></div> Como funciona</div>
        <?php foreach(['Escolha o valor e insira seu CPF','Clique em Gerar PIX','Escaneie o QR ou copie o código','Pagamento confirmado em até 1 min'] as $i=>$s): ?>
        <div style="display:flex;gap:10px;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.03)">
          <div style="width:22px;height:22px;border-radius:50%;background:rgba(0,230,118,.1);border:1px solid rgba(0,230,118,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.72rem;font-weight:700;color:var(--green)"><?=$i+1?></div>
          <div style="font-size:.78rem;color:var(--muted-2);line-height:1.4"><?=$s?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="card card-solid" style="background:rgba(0,230,118,.03);border-color:rgba(0,230,118,.1)">
        <div style="display:flex;gap:10px;align-items:flex-start">
          <span style="font-size:1.2rem">🔒</span>
          <div style="font-size:.76rem;color:var(--muted-2);line-height:1.5">Depósitos processados via PIX instantâneo. 100% seguro e criptografado.</div>
        </div>
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
function gerarPix(){
  const amount=document.getElementById('amount-input').value;
  const cpf=document.getElementById('cpf-input').value.replace(/\D/g,'');
  if(!amount||parseFloat(amount)<<?=$depositoMin?>){toast('Valor mínimo: R$ <?=number_format($depositoMin,2,',','.')?>','warning');return;}
  if(cpf.length!==11){toast('CPF inválido','error');return;}
  const btn=document.getElementById('gerar-btn');
  btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin" style="margin-right:6px"></i> Gerando...';
  const fd=new FormData(); fd.append('amount',amount); fd.append('cpf',cpf);
  fetch('/api/payment.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(d=>{
      btn.disabled=false; btn.innerHTML='<i data-lucide="zap" style="width:16px;height:16px"></i> Gerar PIX';
      if(typeof lucide!=='undefined') lucide.createIcons();
      if(d.error||!d.success){toast(d.error||d.message||'Erro ao gerar PIX','error');return;}
      const res=document.getElementById('pix-result');
      res.style.display='block';
      const qr=d.qrcode||d.pix_qr||d.imagemQrcode||'';
      const code=d.code||d.pix_code||d.qrCode||d.copiaCola||'';
      if(qr) document.getElementById('pix-qr').src=qr.startsWith('http')?qr:'data:image/png;base64,'+qr;
      document.getElementById('pix-code').textContent=code;
      toast('PIX gerado! Escaneie e pague.','success');
      startTimer(600);
    })
    .catch(e=>{btn.disabled=false;toast('Erro de conexão','error');console.error(e);});
}
function copyPix(){
  const code=document.getElementById('pix-code').textContent;
  if(code) copyText(code,'Código PIX copiado!');
}
function startTimer(s){
  const el=document.getElementById('pix-timer');
  const end=Date.now()+s*1000;
  const tick=()=>{
    const rem=Math.max(0,Math.round((end-Date.now())/1000));
    const m=String(Math.floor(rem/60)).padStart(2,'0');
    const sec=String(rem%60).padStart(2,'0');
    el.textContent=rem>0?'PIX expira em '+m+':'+sec:'PIX expirado';
    if(rem>0) setTimeout(tick,1000);
  };
  tick();
}
</script>
JS;
include __DIR__.'/../includes/app_footer.php';
?>
