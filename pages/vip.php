<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$isLogged = isset($_SESSION['usuario_id']); $saldo=0; $nomeUser='';
if ($isLogged) { $st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1"); $st->execute([$_SESSION['usuario_id']]); $u=$st->fetch(PDO::FETCH_ASSOC); $saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; }
$pageTitle = 'VIP'; include __DIR__.'/../includes/app_head.php'; include __DIR__.'/../includes/app_navbar.php'; include __DIR__.'/../includes/app_sidebar.php';
$tiers = [
  ['Bronze','🥉','var(--text)','bronze','rgba(150,80,0,.3)','R$ 0','R$ 999','0.1% Cashback · Suporte Padrão'],
  ['Prata','🥈','#b0bec5','silver','rgba(180,180,200,.2)','R$ 1.000','R$ 4.999','0.3% Cashback · Bônus Mensal'],
  ['Ouro','🏆','var(--gold)','gold','rgba(255,214,0,.25)','R$ 5.000','R$ 24.999','0.5% Cashback · Gerente VIP'],
  ['Diamante','💎','#60a5fa','diamond','rgba(96,165,250,.25)','R$ 25.000','R$ 99.999','1% Cashback · Benefícios Exclusivos'],
  ['Elite','👑','var(--red)','elite','rgba(255,23,68,.25)','R$ 100.000','+','2% Cashback · Atendimento 24/7 Dedicado'],
];
?>
<div class="app"><div class="page-content" id="page-content">
<div class="page-title" data-aos="fade-right"><i data-lucide="crown" class="pt-icon"></i> Programa VIP</div>

<!-- Hero -->
<div class="card mb-3" style="background:linear-gradient(135deg,#1a0a00,#2a1200,#1a0000);border-color:rgba(255,214,0,.2);text-align:center;padding:40px 24px" data-aos="fade-up">
  <div style="font-size:3rem;margin-bottom:8px">👑</div>
  <h2 style="font-size:1.6rem;font-weight:900;margin-bottom:8px">Programa VIP RaspaPix</h2>
  <p style="color:var(--muted);max-width:480px;margin:0 auto 20px;font-size:.9rem">Quanto mais você joga, mais recompensas exclusivas recebe. Suba de nível e desbloqueie benefícios incríveis.</p>
  <div style="display:flex;align-items:center;justify-content:center;gap:20px;flex-wrap:wrap">
    <div style="text-align:center"><div style="font-size:1.4rem;font-weight:900;color:var(--gold)">5%</div><div style="font-size:.75rem;color:var(--muted)">Cashback Máx.</div></div>
    <div style="width:1px;height:30px;background:var(--border)"></div>
    <div style="text-align:center"><div style="font-size:1.4rem;font-weight:900;color:var(--green)">24/7</div><div style="font-size:.75rem;color:var(--muted)">Suporte Elite</div></div>
    <div style="width:1px;height:30px;background:var(--border)"></div>
    <div style="text-align:center"><div style="font-size:1.4rem;font-weight:900;color:var(--red)">∞</div><div style="font-size:.75rem;color:var(--muted)">Bônus Mensais</div></div>
  </div>
</div>

<!-- Tiers -->
<div class="vip-grid mb-3">
<?php foreach($tiers as $i=>[$name,$icon,$color,$cls,$bc,$min,$max,$perks]): ?>
<div class="vip-card vip-<?=$cls?>" style="border-color:<?=$bc?>" data-aos="fade-up" data-aos-delay="<?=$i*60?>">
  <div class="vip-tier" style="color:<?=$color?>"><?=$name?></div>
  <div class="vip-name" style="color:<?=$color?>;font-size:1.2rem"><?=$icon?> <?=$name?></div>
  <div class="vip-req">Apostado: <?=$min?> – <?=$max?></div>
  <div style="margin-top:10px;font-size:.75rem;color:var(--muted)"><?=$perks?></div>
  <div class="vip-icon"><?=$icon?></div>
</div>
<?php endforeach; ?>
</div>

<!-- My Progress -->
<div class="card" data-aos="fade-up">
  <div class="section-title"><i data-lucide="trending-up" style="width:16px;height:16px;color:var(--red)"></i> Meu Progresso VIP</div>
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
    <div style="font-size:2rem">🥉</div>
    <div style="flex:1">
      <div style="font-weight:700;margin-bottom:4px">Bronze → Prata</div>
      <div class="progress"><div class="progress-bar" style="width:20%"></div></div>
      <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--muted);margin-top:4px">
        <span>R$ 0 apostados</span><span>Precisa: R$ 1.000</span>
      </div>
    </div>
  </div>
  <div style="font-size:.8rem;color:var(--muted);padding:10px;background:rgba(255,255,255,.02);border-radius:8px">
    💡 Aposte mais R$ 1.000,00 para subir para o nível <strong style="color:#b0bec5">Prata</strong> e desbloquear cashback de 0.3% e bônus mensais!
  </div>
</div>

</div></div>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
