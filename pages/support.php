<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$isLogged = isset($_SESSION['usuario_id']); $saldo=0; $nomeUser='';
if ($isLogged) { $st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1"); $st->execute([$_SESSION['usuario_id']]); $u=$st->fetch(PDO::FETCH_ASSOC); $saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; }
$pageTitle='Suporte'; include __DIR__.'/../includes/app_head.php'; include __DIR__.'/../includes/app_navbar.php'; include __DIR__.'/../includes/app_sidebar.php';
?>
<div class="app"><div class="page-content" id="page-content">
<div class="page-title" data-aos="fade-right"><i data-lucide="headphones" class="pt-icon"></i> Central de Suporte</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:24px">
  <?php foreach([
    ['message-circle','Chat ao Vivo','Resposta em até 5 minutos','var(--green)','Iniciar Chat','#chat-modal'],
    ['mail','E-mail','Resposta em até 24 horas','var(--blue)','Enviar E-mail','mailto:suporte@raspadinha.com'],
    ['book-open','FAQ','Respostas rápidas','var(--gold)','Ver FAQ','#faq'],
  ] as [$icon,$title,$desc,$color,$btn,$href]): ?>
  <div class="card" style="text-align:center;padding:28px 20px;border-color:<?=$color?>22" data-aos="fade-up">
    <div style="width:48px;height:48px;border-radius:12px;background:<?=$color?>18;border:1px solid <?=$color?>33;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
      <i data-lucide="<?=$icon?>" style="width:22px;height:22px;color:<?=$color?>"></i>
    </div>
    <div style="font-weight:700;margin-bottom:4px"><?=$title?></div>
    <div style="font-size:.78rem;color:var(--muted);margin-bottom:14px"><?=$desc?></div>
    <a href="<?=$href?>" class="btn btn-ghost btn-sm btn-full"><?=$btn?></a>
  </div>
  <?php endforeach; ?>
</div>

<!-- FAQ -->
<div class="card mb-3" id="faq" data-aos="fade-up">
  <div class="section-title"><i data-lucide="help-circle" style="width:16px;height:16px;color:var(--red)"></i> Perguntas Frequentes</div>
  <?php $faqs = [
    ['Como faço um depósito?','Vá em Depositar, escolha o valor mínimo de R$ 10 e gere o PIX. O saldo é creditado em até 5 minutos.'],
    ['Quanto tempo leva o saque?','Saques PIX são processados em até 24 horas úteis. Não há taxas.'],
    ['Os jogos são justos?','Sim! Usamos Provably Fair — você pode verificar qualquer resultado.'],
    ['Como funciona o programa de afiliados?','Compartilhe seu link, ganhe 5–15% de comissão sobre o GGR dos seus indicados.'],
    ['Posso cancelar um saque?','Sim, enquanto estiver "Pendente" você pode cancelar na sua Carteira.'],
    ['Qual o limite de saque diário?','Sem limite para usuários verificados. Verificação simples via CPF.'],
  ]; ?>
  <div id="faq-list" style="display:flex;flex-direction:column;gap:0">
    <?php foreach($faqs as $i=>[$q,$a]): ?>
    <div style="border-bottom:1px solid var(--border)">
      <button onclick="toggleFaq(<?=$i?>)" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:14px 0;background:none;border:none;color:var(--text);font-weight:600;font-size:.875rem;cursor:pointer;font-family:inherit;text-align:left;gap:12px">
        <?=$q?>
        <i data-lucide="chevron-down" style="width:16px;height:16px;flex-shrink:0;transition:.2s" id="faq-icon-<?=$i?>"></i>
      </button>
      <div id="faq-ans-<?=$i?>" style="display:none;padding-bottom:14px;font-size:.85rem;color:var(--muted);line-height:1.6"><?=$a?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Contact Form -->
<div class="card" data-aos="fade-up">
  <div class="section-title"><i data-lucide="send" style="width:16px;height:16px;color:var(--red)"></i> Enviar Mensagem</div>
  <form onsubmit="submitSupport(event)" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="form-group"><label class="form-label">Nome</label><input type="text" class="form-input" placeholder="Seu nome" required></div>
    <div class="form-group"><label class="form-label">E-mail</label><input type="email" class="form-input" placeholder="seu@email.com" required></div>
    <div class="form-group" style="grid-column:1/-1">
      <label class="form-label">Assunto</label>
      <select class="form-input"><option>Depósito</option><option>Saque</option><option>Conta</option><option>Jogo</option><option>Outro</option></select>
    </div>
    <div class="form-group" style="grid-column:1/-1"><label class="form-label">Mensagem</label><textarea class="form-input" rows="4" placeholder="Descreva sua dúvida..." style="resize:vertical" required></textarea></div>
    <div style="grid-column:1/-1"><button type="submit" class="btn btn-primary btn-lg"><i data-lucide="send" style="width:15px;height:15px"></i> Enviar Mensagem</button></div>
  </form>
</div>

</div></div>
<script>
function toggleFaq(i) {
  const ans=document.getElementById('faq-ans-'+i);
  const ico=document.getElementById('faq-icon-'+i);
  const open=ans.style.display==='block';
  ans.style.display=open?'none':'block';
  ico.style.transform=open?'':'rotate(180deg)';
}
function submitSupport(e) {
  e.preventDefault();
  toast('Mensagem enviada! Responderemos em até 24h.','success','Suporte');
  e.target.reset();
}
</script>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
