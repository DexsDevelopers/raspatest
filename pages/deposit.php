<?php
session_start();
require_once __DIR__ . '/../conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit; }
$isLogged = true; $uid = $_SESSION['usuario_id'];
$st = $pdo->prepare("SELECT nome, saldo FROM usuarios WHERE id=? LIMIT 1");
$st->execute([$uid]); $user = $st->fetch(PDO::FETCH_ASSOC);
$saldo = $user['saldo'] ?? 0; $nomeUser = $user['nome'] ?? '';
$pageTitle = 'Depositar';
include __DIR__ . '/../includes/app_head.php';
include __DIR__ . '/../includes/app_navbar.php';
include __DIR__ . '/../includes/app_sidebar.php';
?>
<div class="app"><div class="page-content" id="page-content">

  <div class="page-title" data-aos="fade-right">
    <i data-lucide="arrow-down-circle" class="pt-icon"></i> Depositar via PIX
  </div>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">
  <div>
    <!-- Amount Select -->
    <div class="card mb-3" data-aos="fade-up">
      <div class="section-title"><i data-lucide="banknote" style="width:16px;height:16px;color:var(--red)"></i> Escolha o Valor</div>
      <div class="amount-grid mb-2">
        <?php foreach([20,50,100,200,500,1000,2000,5000] as $v): ?>
        <button class="amount-btn" data-target="depositAmt" data-value="<?=$v?>">R$ <?=number_format($v,0,'.',',')?></button>
        <?php endforeach; ?>
      </div>
      <div class="form-group">
        <label class="form-label">Ou digite o valor</label>
        <div class="input-prefix">
          <span>R$</span>
          <input type="number" id="depositAmt" class="form-input" placeholder="0,00" min="10" step="0.01">
        </div>
        <div class="form-hint">Mínimo: R$ 10,00 &nbsp;·&nbsp; Máximo: R$ 50.000,00</div>
      </div>
      <button class="btn btn-green btn-full btn-lg" onclick="generatePix()">
        <i data-lucide="qr-code" style="width:16px;height:16px"></i> Gerar PIX
      </button>
    </div>

    <!-- PIX QR -->
    <div class="card" id="pixBox" style="display:none" data-aos="fade-up">
      <div class="section-title"><i data-lucide="check-circle" style="width:16px;height:16px;color:var(--green)"></i> PIX Gerado!</div>
      <div class="pix-qr-wrap">
        <div class="pix-qr-box"><span style="font-size:4rem">🟩</span></div>
        <p style="font-size:.85rem;color:var(--muted);margin-bottom:12px">Escaneie o QR Code ou copie o código abaixo</p>
        <div class="pix-code" id="pixCode" onclick="copyText(this.textContent,'Código PIX copiado!')">
          00020126580014br.gov.bcb.pix0136...clique para copiar...
        </div>
        <button class="btn btn-ghost btn-full mt-2" onclick="copyText(document.getElementById('pixCode').textContent,'Código PIX copiado!')">
          <i data-lucide="copy" style="width:14px;height:14px"></i> Copiar Código PIX
        </button>
      </div>
      <div style="margin-top:16px;padding:14px;background:rgba(0,230,118,.06);border:1px solid rgba(0,230,118,.2);border-radius:10px">
        <div style="font-size:.8rem;color:var(--green);font-weight:700;margin-bottom:4px">⏱ Aguardando pagamento...</div>
        <div style="font-size:.78rem;color:var(--muted)">O saldo será creditado automaticamente em até 5 minutos após o pagamento.</div>
      </div>
    </div>
  </div>

  <!-- Right: Info + History -->
  <div>
    <div class="card mb-3" data-aos="fade-left">
      <div class="section-title"><i data-lucide="info" style="width:16px;height:16px;color:var(--red)"></i> Informações</div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach([
          ['check','Processamento imediato','Depósitos aprovados em até 5 min','var(--green)'],
          ['shield','100% Seguro','Transações criptografadas com SSL','var(--blue)'],
          ['zap','Sem taxas','Nenhuma taxa sobre depósitos','var(--gold)'],
          ['clock','Disponível 24/7','Deposite a qualquer hora','var(--muted)'],
        ] as [$icon,$title,$desc,$color]): ?>
        <div style="display:flex;gap:10px;align-items:flex-start">
          <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.04);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i data-lucide="<?=$icon?>" style="width:14px;height:14px;color:<?=$color?>"></i>
          </div>
          <div><div style="font-size:.8rem;font-weight:700"><?=$title?></div><div style="font-size:.75rem;color:var(--muted)"><?=$desc?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card" data-aos="fade-left" data-aos-delay="80">
      <div class="section-title"><i data-lucide="clock" style="width:16px;height:16px;color:var(--red)"></i> Últimos Depósitos</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php for($i=0;$i<3;$i++): $v=rand(50,500); ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
          <div><div style="font-size:.82rem;font-weight:600">PIX</div><div style="font-size:.72rem;color:var(--muted)">há <?=rand(1,48)?>h</div></div>
          <span class="badge badge-green">+R$ <?=number_format($v,2,',','.')?></span>
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
  </div>

</div></div>
<script>
function generatePix() {
  const amt = document.getElementById('depositAmt').value;
  if (!amt || amt < 10) { toast('Valor mínimo: R$ 10,00','warning'); return; }
  document.getElementById('pixBox').style.display = 'block';
  document.getElementById('pixCode').textContent = '00020126580014br.gov.bcb.pix0136' + Math.random().toString(36).substr(2,32).toUpperCase() + '5204000053039865802BR5925RASPADINHA PIX LTDA6009SAO PAULO62070503***6304' + Math.floor(Math.random()*9999).toString().padStart(4,'0');
  document.getElementById('pixBox').scrollIntoView({behavior:'smooth'});
  toast('PIX gerado! Escaneie o QR Code.','success','PIX Pronto');
}
</script>
<?php include __DIR__ . '/../includes/app_footer.php'; ?>
