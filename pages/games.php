<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$isLogged = isset($_SESSION['usuario_id']); $saldo=0; $nomeUser='';
if ($isLogged) { $st=$pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1"); $st->execute([$_SESSION['usuario_id']]); $u=$st->fetch(PDO::FETCH_ASSOC); $saldo=$u['saldo']??0; $nomeUser=$u['nome']??''; }
$pageTitle='Jogos'; include __DIR__.'/../includes/app_head.php'; include __DIR__.'/../includes/app_navbar.php'; include __DIR__.'/../includes/app_sidebar.php';

$games = [
  ['Fortune Tiger','🐯','/jogos/tiger.php?t=tiger','radial-gradient(at 60% 110%,#c44000,#5a0a00 55%,#1a0000)','rgba(220,80,0,.55)','rgba(255,100,0,.5)','PG STYLE','hot','🔥 HOT',3241,'slots exclusive'],
  ['Fortune Rabbit','🐰','/jogos/tiger.php?t=rabbit','radial-gradient(at 60% 110%,#8800cc,#44006a 55%,#0f0020)','rgba(160,0,220,.55)','rgba(200,50,255,.5)','PG STYLE','hot','🔥 HOT',2180,'slots hot'],
  ['Fortune Dragon','🐉','/jogos/tiger.php?t=dragon','radial-gradient(at 60% 110%,#0050cc,#002880 55%,#000a20)','rgba(0,80,200,.55)','rgba(50,120,255,.5)','PG STYLE','new','✨ NEW',1560,'slots new'],
  ['Aviator','✈️','/jogos/aviator.php','radial-gradient(at 60% 110%,#cc2200,#801100 55%,#1a0000)','rgba(220,40,0,.55)','rgba(255,70,0,.5)','SPRIBE','live','🔴 AO VIVO',5420,'crash live exclusive hot'],
  ['Crash','🚀','/jogos/crash.php','radial-gradient(at 60% 110%,#1a6600,#0d3300 55%,#020d00)','rgba(30,180,0,.55)','rgba(60,220,0,.5)','ORIGINAL','live','🔴 AO VIVO',847,'crash live'],
  ['Mines','💣','/jogos/mines.php','radial-gradient(at 60% 110%,#5a3000,#2a1400 55%,#0a0500)','rgba(180,80,0,.55)','rgba(220,100,0,.5)','ORIGINAL','hot','🔥 HOT',1203,'originals hot'],
  ['Plinko','🔵','/jogos/plinko.php','radial-gradient(at 60% 110%,#004466,#002233 55%,#000810)','rgba(0,120,200,.55)','rgba(0,160,255,.5)','ORIGINAL','new','✨ NEW',634,'originals new'],
  ['Dice','🎲','/jogos/dice.php','radial-gradient(at 60% 110%,#0a2a5a,#041530 55%,#000510)','rgba(60,100,220,.55)','rgba(80,130,255,.5)','ORIGINAL','','',512,'originals exclusive'],
  ['Limbo','🌀','/jogos/limbo.php','radial-gradient(at 60% 110%,#3a0066,#1a0033 55%,#060010)','rgba(120,0,200,.55)','rgba(160,50,255,.5)','ORIGINAL','','',389,'originals exclusive'],
  ['Raspadinha','🎟️','/','radial-gradient(at 60% 110%,#6a4a00,#3a2600 55%,#0d0900)','rgba(200,140,0,.55)','rgba(240,170,0,.5)','ORIGINAL','new','🎟 RASPA',2941,'raspa new'],
];
?>
<div class="app"><div class="page-content" id="page-content">
<div class="page-title" data-aos="fade-right"><i data-lucide="gamepad-2" class="pt-icon"></i> Todos os Jogos</div>

<!-- Jackpot Banner -->
<div class="card mb-3" style="background:linear-gradient(135deg,#1a0000,#2a0800,#0a0014);border-color:rgba(255,23,68,.2);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" data-aos="fade-up">
  <div>
    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:4px">🏆 JACKPOT ACUMULADO</div>
    <div id="jackpot-value" data-base="188000" style="font-size:2.2rem;font-weight:900;color:var(--gold);text-shadow:0 0 16px rgba(255,214,0,.4)">R$ 188.000,00</div>
  </div>
  <div style="display:flex;gap:24px">
    <?php foreach([['2.021','Jogadores Online','var(--green)'],['R$ 94.957','Ganhos (24h)','var(--text)'],['28.782','Rodadas (24h)','var(--text)']] as [$v,$l,$c]): ?>
    <div style="text-align:center"><div style="font-size:1.2rem;font-weight:900;color:<?=$c?>"><?=$v?></div><div style="font-size:.7rem;color:var(--muted)"><?=$l?></div></div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Filters -->
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap" data-aos="fade-up">
  <div style="display:flex;gap:6px;flex-wrap:wrap;flex:1">
    <?php foreach([['all','🎮 Todos'],['slots','🎰 Slots'],['crash','🚀 Crash'],['originals','⚡ Originais'],['live','🔴 Ao Vivo'],['new','✨ Novos'],['exclusive','💎 Exclusivos'],['raspa','🎟 Raspadinhas']] as [$tag,$label]): ?>
    <button class="btn btn-ghost btn-sm ftag <?=$tag==='all'?'active':''?>" style="<?=$tag==='all'?'background:rgba(255,23,68,.12);border-color:rgba(255,23,68,.3);color:var(--red)':''?>" data-tag="<?=$tag?>"><?=$label?></button>
    <?php endforeach; ?>
  </div>
  <div style="position:relative">
    <i class="fas fa-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.8rem"></i>
    <input type="text" class="form-input" style="padding-left:32px;width:200px" placeholder="Buscar..." id="gSearch" oninput="filterGSearch(this.value)">
  </div>
</div>

<!-- Grid -->
<div class="games-grid" id="gGrid">
  <?php foreach($games as [$name,$emoji,$url,$bg,$glow,$shadow,$prov,$tag,$badge,$count,$tags]): ?>
  <a href="<?=$url?>" class="game-card" data-tags="<?=$tags?>" data-name="<?=strtolower($name)?>">
    <div class="game-thumb">
      <div class="game-thumb-inner">
        <div class="game-thumb-bg" style="background:<?=$bg?>;--glow:<?=$glow?>;--shadow:<?=$shadow?>"></div>
        <div class="game-emoji"><?=$emoji?></div>
      </div>
      <div class="game-prov"><?=$prov?></div>
      <?php if($badge): ?><div class="game-badge <?=$tag==='new'?'gbadge-new':($tag==='live'?'gbadge-live':'gbadge-hot')?>"><?=$badge?></div><?php endif; ?>
      <div class="game-overlay"><button class="game-play-btn">▶ Jogar</button></div>
    </div>
    <div class="game-info">
      <div class="game-name"><?=$name?></div>
      <div class="game-players"><span style="width:4px;height:4px;background:var(--green);border-radius:50%;display:inline-block"></span> <?=number_format($count,0,'.',',')?> online</div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

</div></div>
<script>
document.querySelectorAll('.ftag').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.ftag').forEach(b=>{b.classList.remove('active');b.style.background='';b.style.borderColor='';b.style.color=''});
    btn.classList.add('active'); btn.style.background='rgba(255,23,68,.12)'; btn.style.borderColor='rgba(255,23,68,.3)'; btn.style.color='var(--red)';
    const tag=btn.dataset.tag;
    document.querySelectorAll('.game-card').forEach(c=>{
      c.style.display=(tag==='all'||c.dataset.tags.includes(tag))?'block':'none';
    });
    document.getElementById('gSearch').value='';
  });
});
function filterGSearch(q){
  document.querySelectorAll('.ftag').forEach(b=>{b.classList.remove('active');b.style.cssText=''});
  const s=q.toLowerCase();
  document.querySelectorAll('.game-card').forEach(c=>{c.style.display=(!s||c.dataset.name.includes(s))?'block':'none'});
}
</script>
<?php include __DIR__.'/../includes/app_footer.php'; ?>
