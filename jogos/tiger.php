<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'RaspaPix';
$theme    = preg_replace('/[^a-z]/', '', strtolower($_GET['t'] ?? 'tiger'));
$themes   = [
  'tiger'  => ['name'=>'Fortune Tiger',  'emoji'=>'🐯','bg1'=>'#0d0000','bg2'=>'#2a0500','accent'=>'#e8a000','line'=>'#ff6600','glow'=>'255,120,0'],
  'rabbit' => ['name'=>'Fortune Rabbit', 'emoji'=>'🐰','bg1'=>'#0a0020','bg2'=>'#1e0038','accent'=>'#cc44ff','line'=>'#9933cc','glow'=>'180,50,255'],
  'dragon' => ['name'=>'Fortune Dragon', 'emoji'=>'🐉','bg1'=>'#000c20','bg2'=>'#001540','accent'=>'#00aaff','line'=>'#0066dd','glow'=>'0,150,255'],
];
$t = $themes[$theme] ?? $themes['tiger'];
$isLogged = isset($_SESSION['usuario_id']);
$saldo = 0;
if ($isLogged) {
  $st = $pdo->prepare("SELECT saldo FROM usuarios WHERE id=? LIMIT 1");
  $st->execute([$_SESSION['usuario_id']]);
  $saldo = $st->fetchColumn() ?? 0;
}
// Try to load logo
$logoSite='';
try { $cfg=$pdo->query("SELECT logo FROM config LIMIT 1")->fetch(PDO::FETCH_ASSOC); $logoSite=$cfg['logo']??''; } catch(Exception $e){}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1">
<title><?= htmlspecialchars($t['name']) ?> — <?= htmlspecialchars($nomeSite) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--accent:<?= $t['accent'] ?>;--line:<?= $t['line'] ?>;--glow:<?= $t['glow'] ?>}
*{margin:0;padding:0;box-sizing:border-box}
body{background:radial-gradient(ellipse at top,<?= $t['bg2'] ?> 0%,<?= $t['bg1'] ?> 40%,#000 100%);color:#fff;font-family:'Outfit',sans-serif;min-height:100vh;overflow-x:hidden}

/* AMBIENT */
.ambient-layer{position:fixed;inset:0;pointer-events:none;z-index:0}
.ambient-layer::before{content:'';position:absolute;top:-20%;left:-20%;width:60%;height:60%;background:radial-gradient(circle,rgba(var(--glow),.12) 0%,transparent 70%);animation:drift1 8s ease-in-out infinite alternate}
.ambient-layer::after{content:'';position:absolute;bottom:-20%;right:-20%;width:50%;height:50%;background:radial-gradient(circle,rgba(var(--glow),.08) 0%,transparent 70%);animation:drift2 10s ease-in-out infinite alternate}
@keyframes drift1{0%{transform:translate(0,0)}100%{transform:translate(5%,10%)}}
@keyframes drift2{0%{transform:translate(0,0)}100%{transform:translate(-8%,-5%)}}

/* NAV */
.nav{height:52px;background:rgba(0,0,0,.75);border-bottom:1px solid rgba(var(--glow),.15);display:flex;align-items:center;padding:0 16px;gap:12px;position:sticky;top:0;z-index:200;backdrop-filter:blur(12px)}
.nav-logo{display:flex;align-items:center;gap:8px;text-decoration:none}
.nav-logo img{height:30px;object-fit:contain}
.nav-logo-txt{font-size:.95rem;font-weight:900;color:#fff}
.nav-div{width:1px;height:20px;background:rgba(255,255,255,.1)}
.nav-back{font-size:.8rem;color:rgba(255,255,255,.45);text-decoration:none;display:flex;align-items:center;gap:4px;transition:.15s}
.nav-back:hover{color:#fff}
.nav-game-title{font-size:1rem;font-weight:900;color:var(--accent);letter-spacing:.05em;text-shadow:0 0 20px rgba(var(--glow),.6)}
.nav-r{margin-left:auto;display:flex;align-items:center;gap:8px}
.nav-bal{background:rgba(255,255,255,.07);border:1px solid rgba(var(--glow),.2);border-radius:8px;padding:5px 12px;font-size:.82rem;font-weight:700;white-space:nowrap}
.nav-dep{background:linear-gradient(135deg,var(--line),var(--accent));color:#fff;border:none;border-radius:8px;padding:5px 14px;font-size:.8rem;font-weight:800;cursor:pointer;font-family:inherit;white-space:nowrap}

/* BIG WIN OVERLAY */
.bigwin-overlay{position:fixed;inset:0;z-index:999;display:none;align-items:center;justify-content:center;flex-direction:column;gap:12px;background:rgba(0,0,0,.85);backdrop-filter:blur(4px)}
.bigwin-overlay.show{display:flex}
.bigwin-label{font-size:3.5rem;font-weight:900;letter-spacing:.08em;text-align:center;text-shadow:0 0 40px currentColor;animation:bw-pulse 0.7s ease-in-out infinite alternate}
.bigwin-amount{font-size:2.2rem;font-weight:900;color:#ffd700;text-shadow:0 0 30px #ffd70099}
.bigwin-close{margin-top:16px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;padding:10px 32px;border-radius:50px;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit}
@keyframes bw-pulse{0%{transform:scale(1)}100%{transform:scale(1.05)}}

/* FREE SPINS BANNER */
.fs-banner{display:none;background:linear-gradient(135deg,rgba(var(--glow),.3),rgba(var(--glow),.1));border:2px solid var(--accent);border-radius:12px;padding:10px 16px;text-align:center;margin-bottom:12px;animation:fs-glow 1.2s ease-in-out infinite alternate}
.fs-banner.show{display:block}
.fs-banner-title{font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;color:var(--accent);font-weight:700}
.fs-banner-count{font-size:2rem;font-weight:900;color:#ffd700;line-height:1}
@keyframes fs-glow{0%{box-shadow:0 0 8px rgba(var(--glow),.3)}100%{box-shadow:0 0 24px rgba(var(--glow),.7)}}

/* LAYOUT */
.wrap{position:relative;z-index:1;max-width:1020px;margin:0 auto;padding:14px 12px;display:grid;grid-template-columns:1fr 270px;gap:16px;align-items:start}
@media(max-width:700px){.wrap{grid-template-columns:1fr;padding:10px 8px}}

/* MACHINE */
.machine{background:rgba(0,0,0,.55);border:2px solid rgba(var(--glow),.25);border-radius:20px;overflow:hidden;box-shadow:0 0 40px rgba(var(--glow),.1)}
.mach-header{background:linear-gradient(135deg,rgba(var(--glow),.15),rgba(0,0,0,0));padding:14px 18px;text-align:center;border-bottom:1px solid rgba(var(--glow),.15)}
.mach-title{font-size:1.4rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:var(--accent);text-shadow:0 0 20px rgba(var(--glow),.5)}
.mach-rtp{font-size:.65rem;color:rgba(255,255,255,.3);margin-top:2px;letter-spacing:.08em}
.mach-body{padding:14px 14px 10px}

/* CANVAS */
.reel-frame{position:relative;background:#040406;border:3px solid rgba(var(--glow),.35);border-radius:16px;padding:3px;margin-bottom:10px;box-shadow:inset 0 0 30px rgba(0,0,0,.6),0 0 20px rgba(var(--glow),.15)}
.reel-frame::before,.reel-frame::after{content:'';position:absolute;left:3px;right:3px;height:2px;background:linear-gradient(90deg,transparent,rgba(var(--glow),.6),transparent);z-index:5;pointer-events:none}
.reel-frame::before{top:33.33%}
.reel-frame::after{bottom:33.33%}
.win-line-indicator{position:absolute;left:-6px;right:-6px;height:2px;background:linear-gradient(90deg,transparent,var(--accent),transparent);z-index:6;pointer-events:none;opacity:0;top:50%;transform:translateY(-50%)}
canvas#reelCanvas{display:block;width:100%;border-radius:13px}

/* WIN BAR */
.win-bar{height:46px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.06);margin-bottom:10px;overflow:hidden;position:relative}
.win-bar-fill{position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(var(--glow),.12),transparent);opacity:0;transition:.3s}
.win-bar.won .win-bar-fill{opacity:1}
.win-txt{font-size:1.25rem;font-weight:900;position:relative;z-index:1}
.win-neutral{color:rgba(255,255,255,.3);font-size:.9rem;font-weight:600}
.win-amount{color:#ffd700;text-shadow:0 0 20px #ffd70099}

/* LINE SELECT */
.line-row{display:flex;gap:6px;margin-bottom:12px}
.line-btn{flex:1;padding:7px 4px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(255,255,255,.45);font-size:.78rem;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
.line-btn.active{background:rgba(var(--glow),.15);border-color:var(--accent);color:var(--accent)}

/* CONTROLS */
.ctrl-card{background:rgba(0,0,0,.45);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:14px;margin-top:12px}
.ctrl-row{display:flex;gap:8px;align-items:center;margin-bottom:10px}
.ctrl-lbl{font-size:.72rem;color:rgba(255,255,255,.35);width:50px;white-space:nowrap}
.ctrl-in{flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#fff;border-radius:8px;padding:9px 12px;font-size:1rem;font-weight:700;font-family:inherit;outline:none;transition:.2s}
.ctrl-in:focus{border-color:var(--accent);box-shadow:0 0 0 2px rgba(var(--glow),.15)}
.qgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;margin-bottom:12px}
.qb{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.55);border-radius:6px;padding:6px 2px;font-size:.72rem;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
.qb:hover{background:rgba(var(--glow),.15);color:var(--accent);border-color:var(--accent)}
.spin-btn{width:100%;padding:16px;border-radius:12px;border:none;font-size:1.15rem;font-weight:900;cursor:pointer;letter-spacing:.04em;transition:.2s;font-family:inherit;background:linear-gradient(135deg,var(--line),var(--accent));color:#fff;position:relative;overflow:hidden;box-shadow:0 4px 20px rgba(var(--glow),.35)}
.spin-btn::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent,rgba(255,255,255,.15),transparent);opacity:0;transition:.3s}
.spin-btn:hover::before{opacity:1}
.spin-btn:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(var(--glow),.5)}
.spin-btn:disabled{background:#1a1a1a;color:#444;cursor:not-allowed;transform:none!important;box-shadow:none!important}
.spin-btn:active:not(:disabled){transform:translateY(0);box-shadow:0 2px 10px rgba(var(--glow),.3)}
.auto-row{display:flex;gap:6px;margin-top:8px}
.auto-in{flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#fff;border-radius:8px;padding:8px 10px;font-size:.82rem;font-weight:700;font-family:inherit;outline:none}
.auto-btn{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.65);border-radius:8px;padding:8px 12px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s;white-space:nowrap}
.auto-btn:hover{background:rgba(255,255,255,.12)}
.stop-btn{background:rgba(239,68,68,.12)!important;border-color:#ef444466!important;color:#ef4444!important}
.turbo-btn{background:rgba(var(--glow),.1)!important;border-color:rgba(var(--glow),.3)!important}
.turbo-btn.on{background:rgba(var(--glow),.25)!important;border-color:var(--accent)!important;color:var(--accent)!important}

/* RIGHT PANEL */
.right{display:flex;flex-direction:column;gap:12px}
.panel{background:rgba(0,0,0,.45);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:14px}
.panel-h{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.25);margin-bottom:10px}
.sgrid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.sbox{background:rgba(0,0,0,.3);border-radius:10px;padding:10px;text-align:center}
.sv{font-size:1.05rem;font-weight:900;color:var(--accent)}
.sl{font-size:.62rem;color:rgba(255,255,255,.3);margin-top:2px}
.hlist{display:flex;flex-direction:column;gap:4px;max-height:200px;overflow-y:auto}
.hrow{display:flex;align-items:center;justify-content:space-between;padding:5px 8px;background:rgba(0,0,0,.25);border-radius:7px;font-size:.78rem;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}
.hw{color:#10b981;font-weight:700}
.hl{color:#ef4444;font-weight:700}
.glinks{display:flex;flex-direction:column;gap:5px}
.glink{display:flex;align-items:center;gap:10px;padding:8px 10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:10px;text-decoration:none;color:#fff;transition:.15s}
.glink:hover{background:rgba(255,255,255,.08);border-color:rgba(var(--glow),.2)}
.gl-em{font-size:1.3rem}
.gl-n{font-size:.83rem;font-weight:700}
.gl-s{font-size:.67rem;color:rgba(255,255,255,.3)}

/* PARTICLE CANVAS */
#particleCanvas{position:fixed;inset:0;pointer-events:none;z-index:100;display:none}
#particleCanvas.show{display:block}

/* SCATTER FLASH */
@keyframes scatter-pop{0%{transform:scale(1)}50%{transform:scale(1.3)}100%{transform:scale(1)}}
</style>

</head>
<body>
<div class="ambient-layer"></div>
<canvas id="particleCanvas"></canvas>

<!-- BIG WIN OVERLAY -->
<div class="bigwin-overlay" id="bwOverlay">
  <div class="bigwin-label" id="bwLabel">🏆 BIG WIN!</div>
  <div class="bigwin-amount" id="bwAmount"></div>
  <div style="font-size:.85rem;color:rgba(255,255,255,.4);margin-top:4px" id="bwMult"></div>
  <button class="bigwin-close" onclick="closeBigWin()">Continuar</button>
</div>

<!-- NAV -->
<nav class="nav">
  <a href="/jogos/" class="nav-logo" style="text-decoration:none">
    <?php if($logoSite): ?>
    <img src="<?=htmlspecialchars($logoSite)?>" alt="<?=htmlspecialchars($nomeSite)?>" onerror="this.style.display='none'">
    <?php endif; ?>
    <span class="nav-logo-txt"><?=htmlspecialchars($nomeSite)?></span>
  </a>
  <div class="nav-div"></div>
  <a href="/jogos/" class="nav-back">← Lobby</a>
  <span class="nav-game-title"><?= htmlspecialchars($t['name']) ?></span>
  <div class="nav-r">
    <div class="nav-bal" id="balDisplay">R$ <?= number_format($saldo,2,',','.') ?></div>
    <button class="nav-dep" onclick="location.href='/pages/deposit.php'">+ Dep.</button>
  </div>
</nav>

<div class="wrap">
  <!-- LEFT: MACHINE -->
  <div>
    <!-- Free Spins Banner -->
    <div class="fs-banner" id="fsBanner">
      <div class="fs-banner-title">⭐ FREE SPINS ⭐</div>
      <div class="fs-banner-count" id="fsCount">10</div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.5);margin-top:2px">giros restantes</div>
    </div>

    <div class="machine">
      <div class="mach-header">
        <div class="mach-title"><?= htmlspecialchars($t['name']) ?> <?= $t['emoji'] ?></div>
        <div class="mach-rtp">RTP 96.81% · Volatilidade Média · Máx 2500x</div>
      </div>
      <div class="mach-body">
        <div class="reel-frame" id="reelFrame">
          <div class="win-line-indicator" id="winLine"></div>
          <canvas id="reelCanvas"></canvas>
        </div>
        <div class="win-bar" id="winBar">
          <div class="win-bar-fill"></div>
          <div class="win-txt win-neutral" id="winDisplay">Aposte e gire!</div>
        </div>
        <div class="line-row">
          <button class="line-btn" onclick="setL(1,this)">1 Linha</button>
          <button class="line-btn active" onclick="setL(3,this)">3 Linhas</button>
          <button class="line-btn" onclick="setL(5,this)">5 Linhas</button>
        </div>
      </div>
    </div>

    <div class="ctrl-card">
      <div class="ctrl-row">
        <span class="ctrl-lbl">Aposta</span>
        <input type="number" id="betAmt" class="ctrl-in" value="1.00" min="0.10" step="0.50">
      </div>
      <div class="qgrid">
        <?php foreach([0.5,1,2,5,10,25,50,100] as $v): ?>
        <button class="qb" onclick="setBet(<?=$v?>)">R$<?=$v<1?'0.5':$v?></button>
        <?php endforeach; ?>
      </div>
      <button class="spin-btn" id="spinBtn" onclick="spin()">🎰 GIRAR</button>
      <div class="auto-row">
        <input type="number" id="autoN" class="auto-in" placeholder="Nº de auto giros" min="1" max="1000">
        <button class="auto-btn" onclick="startAuto()" id="autoStartBtn">▶ Auto</button>
        <button class="auto-btn stop-btn" onclick="stopAuto()" id="autoStopBtn" style="display:none">■ Stop</button>
        <button class="auto-btn turbo-btn" id="turboBtn" onclick="toggleTurbo()" title="Turbo (giro rápido)">⚡</button>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right">
    <div class="panel">
      <div class="panel-h">Sessão</div>
      <div class="sgrid">
        <div class="sbox"><div class="sv" id="sSpins">0</div><div class="sl">Giros</div></div>
        <div class="sbox"><div class="sv" id="sWins">0</div><div class="sl">Vitórias</div></div>
        <div class="sbox"><div class="sv" id="sBest">0x</div><div class="sl">Maior Mult</div></div>
        <div class="sbox"><div class="sv" id="sNet">R$0</div><div class="sl">Resultado</div></div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-h">Últimos Giros</div>
      <div class="hlist" id="histList"><div style="color:rgba(255,255,255,.2);font-size:.78rem;text-align:center;padding:8px">Nenhum giro ainda</div></div>
    </div>
    <div class="panel">
      <div class="panel-h">Outros Jogos</div>
      <div class="glinks">
        <a href="/jogos/tiger.php?t=tiger"  class="glink"><span class="gl-em">🐯</span><div><div class="gl-n">Fortune Tiger</div><div class="gl-s">2500x max</div></div></a>
        <a href="/jogos/tiger.php?t=rabbit" class="glink"><span class="gl-em">🐰</span><div><div class="gl-n">Fortune Rabbit</div><div class="gl-s">2500x max</div></div></a>
        <a href="/jogos/tiger.php?t=dragon" class="glink"><span class="gl-em">🐉</span><div><div class="gl-n">Fortune Dragon</div><div class="gl-s">2500x max</div></div></a>
        <a href="/jogos/aviator.php"        class="glink"><span class="gl-em">✈️</span><div><div class="gl-n">Aviator</div><div class="gl-s">Ao vivo</div></div></a>
        <a href="/jogos/crash.php"          class="glink"><span class="gl-em">🚀</span><div><div class="gl-n">Crash</div><div class="gl-s">Ao vivo</div></div></a>
        <a href="/cartelas"                 class="glink"><span class="gl-em">🎫</span><div><div class="gl-n">Raspadinhas</div><div class="gl-s">Ganhe na hora</div></div></a>
      </div>
    </div>
  </div>
</div>

<script>
// ══════════════════════════════════════════════════════
//  CONFIG
// ══════════════════════════════════════════════════════
const THEME   = '<?= $theme ?>';
const ACCENT  = '<?= $t['accent'] ?>';
const LINE_C  = '<?= $t['line'] ?>';
const GLOW    = '<?= $t['glow'] ?>';
const IS_LOGGED = <?= $isLogged ? 'true' : 'false' ?>;

// ══════════════════════════════════════════════════════
//  SOUND ENGINE (Web Audio API — zero CDN)
// ══════════════════════════════════════════════════════
let audioCtx = null;
function getAudio() {
  if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
  return audioCtx;
}
function playTone(freq, type, duration, vol=0.15, delay=0) {
  try {
    const ac = getAudio();
    const o = ac.createOscillator();
    const g = ac.createGain();
    o.connect(g); g.connect(ac.destination);
    o.type = type; o.frequency.value = freq;
    const t = ac.currentTime + delay;
    g.gain.setValueAtTime(vol, t);
    g.gain.exponentialRampToValueAtTime(0.001, t + duration);
    o.start(t); o.stop(t + duration);
  } catch(e) {}
}
function playClick() { playTone(220, 'triangle', 0.04, 0.1); }
function playReelStop(col) {
  const freqs = [180, 200, 220];
  playTone(freqs[col], 'square', 0.06, 0.08, col * 0.08);
}
function playWin(mult) {
  const notes = mult >= 10 ? [523,659,784,1047] : [440,554,659];
  notes.forEach((f, i) => playTone(f, 'sine', 0.2, 0.12, i * 0.1));
}
function playBigWin() {
  [523,659,784,1047,1319].forEach((f,i) => playTone(f,'sine',0.35,0.18,i*0.12));
}
function playScatter() {
  [800,1000,1200].forEach((f,i) => playTone(f,'triangle',0.15,0.15,i*0.08));
}
function playSpinStart() { playTone(150,'sawtooth',0.08,0.08); }

// ══════════════════════════════════════════════════════
//  SYMBOL DEFINITIONS
// ══════════════════════════════════════════════════════
const SYMS_DEF = {
  tiger:[
    {id:'tiger',  label:'TIGER',  color:'#ff8800',bg:'#280800',emoji:'🐯',mult:50,  scatter:false},
    {id:'dragon', label:'DRAGON', color:'#ff3300',bg:'#200000',emoji:'🐉',mult:25,  scatter:false},
    {id:'star',   label:'SCATTER',color:'#ffd700',bg:'#181000',emoji:'⭐',mult:0,   scatter:true},
    {id:'gold',   label:'GOLD',   color:'#ffd700',bg:'#201000',emoji:'💰',mult:12,  scatter:false},
    {id:'gem',    label:'GEM',    color:'#00ddff',bg:'#001520',emoji:'💎',mult:8,   scatter:false},
    {id:'coin',   label:'COIN',   color:'#e8a000',bg:'#140e00',emoji:'🪙',mult:4,   scatter:false},
    {id:'A',      label:'A',      color:'#ff6644',bg:'#160000',emoji:'A', mult:2,   scatter:false},
    {id:'K',      label:'K',      color:'#cc88ff',bg:'#0e0018',emoji:'K', mult:1.5, scatter:false},
  ],
  rabbit:[
    {id:'rabbit', label:'RABBIT', color:'#ff88cc',bg:'#200030',emoji:'🐰',mult:50,  scatter:false},
    {id:'flower', label:'FLOWER', color:'#ff55aa',bg:'#1c0028',emoji:'🌸',mult:25,  scatter:false},
    {id:'star',   label:'SCATTER',color:'#ffd700',bg:'#181000',emoji:'⭐',mult:0,   scatter:true},
    {id:'moon',   label:'MOON',   color:'#ddccff',bg:'#0c001e',emoji:'🌙',mult:12,  scatter:false},
    {id:'gem',    label:'GEM',    color:'#00ddff',bg:'#001018',emoji:'💎',mult:8,   scatter:false},
    {id:'coin',   label:'COIN',   color:'#e8a000',bg:'#100e00',emoji:'🪙',mult:4,   scatter:false},
    {id:'A',      label:'A',      color:'#cc44ff',bg:'#0c0016',emoji:'A', mult:2,   scatter:false},
    {id:'K',      label:'K',      color:'#9922ff',bg:'#080012',emoji:'K', mult:1.5, scatter:false},
  ],
  dragon:[
    {id:'dragon', label:'DRAGON', color:'#00ccff',bg:'#001428',emoji:'🐉',mult:50,  scatter:false},
    {id:'fire',   label:'FIRE',   color:'#ff8800',bg:'#160a00',emoji:'🔥',mult:25,  scatter:false},
    {id:'star',   label:'SCATTER',color:'#ffd700',bg:'#181000',emoji:'⭐',mult:0,   scatter:true},
    {id:'pearl',  label:'PEARL',  color:'#88ddff',bg:'#001020',emoji:'🔮',mult:12,  scatter:false},
    {id:'gem',    label:'GEM',    color:'#44ffcc',bg:'#001410',emoji:'💎',mult:8,   scatter:false},
    {id:'coin',   label:'COIN',   color:'#e8a000',bg:'#0e0c00',emoji:'🪙',mult:4,   scatter:false},
    {id:'A',      label:'A',      color:'#00aaff',bg:'#000e1c',emoji:'A', mult:2,   scatter:false},
    {id:'K',      label:'K',      color:'#0066dd',bg:'#000816',emoji:'K', mult:1.5, scatter:false},
  ],
};
const syms = SYMS_DEF[THEME] || SYMS_DEF.tiger;
const WEIGHTS = [1, 2, 4, 6, 12, 20, 28, 27]; // sum=100, scatter at 4%
const pool = [];
syms.forEach((s,i) => { for(let w=0;w<WEIGHTS[i];w++) pool.push(s); });

// ══════════════════════════════════════════════════════
//  CANVAS ENGINE
// ══════════════════════════════════════════════════════
const canvas = document.getElementById('reelCanvas');
const ctx    = canvas.getContext('2d');
const COLS=3, ROWS=3;
let CELL = 100;

function resize() {
  const fw = canvas.parentElement.clientWidth - 6;
  CELL = Math.floor(fw / COLS);
  canvas.width  = CELL * COLS;
  canvas.height = CELL * ROWS;
  drawGrid(currentGrid, []);
}
window.addEventListener('resize', resize);

function rr(x,y,w,h,r) {
  ctx.beginPath();
  ctx.moveTo(x+r,y); ctx.lineTo(x+w-r,y); ctx.arcTo(x+w,y,x+w,y+r,r);
  ctx.lineTo(x+w,y+h-r); ctx.arcTo(x+w,y+h,x+w-r,y+h,r);
  ctx.lineTo(x+r,y+h); ctx.arcTo(x,y+h,x,y+h-r,r);
  ctx.lineTo(x,y+r); ctx.arcTo(x,y,x+r,y,r);
  ctx.closePath();
}

function drawSym(sym, x, y, w, h, hl, alpha=1) {
  ctx.globalAlpha = alpha;
  const pad=3, r=12;

  // Gradient background
  const grad = ctx.createLinearGradient(x+pad,y+pad,x+pad,y+h-pad);
  grad.addColorStop(0, hl ? sym.color+'44' : sym.bg+'ee');
  grad.addColorStop(1, hl ? sym.color+'22' : '#00000088');
  ctx.fillStyle = grad;
  rr(x+pad,y+pad,w-pad*2,h-pad*2,r); ctx.fill();

  if (hl) {
    ctx.strokeStyle = sym.color;
    ctx.lineWidth = 2.5;
    ctx.shadowColor = sym.color;
    ctx.shadowBlur = 18;
    rr(x+pad,y+pad,w-pad*2,h-pad*2,r); ctx.stroke();
    ctx.shadowBlur = 0;
  }

  // Emoji or letter
  const isLetter = sym.emoji.length===1 && 'AKQJ10'.includes(sym.emoji);
  if(isLetter) {
    ctx.fillStyle = sym.color;
    ctx.font = `900 ${Math.floor(h*0.48)}px Outfit,sans-serif`;
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.shadowColor = sym.color; ctx.shadowBlur = hl?16:6;
    ctx.fillText(sym.emoji, x+w/2, y+h/2+2);
    ctx.shadowBlur=0;
  } else {
    ctx.font = `${Math.floor(h*0.48)}px serif`;
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.shadowColor='rgba(0,0,0,.6)'; ctx.shadowBlur=4;
    ctx.fillText(sym.emoji, x+w/2, y+h*0.46);
    ctx.shadowBlur=0;
    // Label
    ctx.fillStyle = sym.color+'cc';
    ctx.font = `700 ${Math.max(9,Math.floor(h*0.11))}px Outfit,sans-serif`;
    ctx.textBaseline='bottom';
    if(!sym.scatter)
      ctx.fillText(sym.label, x+w/2, y+h-4);
    else {
      ctx.fillStyle='#ffd700cc';
      ctx.fillText('SCATTER', x+w/2, y+h-4);
    }
  }

  // Multiplier badge (non-scatter)
  if(!sym.scatter && sym.mult>1 && !isLetter) {
    ctx.fillStyle='rgba(0,0,0,.55)';
    rr(x+w-30,y+4,26,14,4); ctx.fill();
    ctx.fillStyle='#ffd700';
    ctx.font=`700 ${Math.max(8,Math.floor(h*0.12))}px Outfit,sans-serif`;
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText(sym.mult+'x', x+w-30+13, y+11);
  }
  ctx.globalAlpha=1;
}

function drawGrid(grid, hlCoords, reelOffsets=[0,0,0]) {
  ctx.clearRect(0,0,canvas.width,canvas.height);
  // BG
  ctx.fillStyle='#030305';
  ctx.fillRect(0,0,canvas.width,canvas.height);
  // Grid lines
  ctx.strokeStyle='rgba(255,255,255,.05)';
  ctx.lineWidth=1;
  for(let c=1;c<COLS;c++){ctx.beginPath();ctx.moveTo(c*CELL,0);ctx.lineTo(c*CELL,canvas.height);ctx.stroke();}
  for(let r=1;r<ROWS;r++){ctx.beginPath();ctx.moveTo(0,r*CELL);ctx.lineTo(canvas.width,r*CELL);ctx.stroke();}

  for(let c=0;c<COLS;c++) {
    const off = reelOffsets[c];
    ctx.save();
    ctx.beginPath();
    ctx.rect(c*CELL, 0, CELL, canvas.height);
    ctx.clip();
    for(let r=-1;r<ROWS+1;r++) {
      const sym = grid[((r%ROWS)+ROWS)%ROWS][c];
      const hl = hlCoords.some(h=>h[0]===r&&h[1]===c);
      drawSym(sym, c*CELL, r*CELL + off, CELL, CELL, hl);
    }
    ctx.restore();
  }
}

// ══════════════════════════════════════════════════════
//  PARTICLE SYSTEM
// ══════════════════════════════════════════════════════
const pc = document.getElementById('particleCanvas');
const pctx = pc.getContext('2d');
let particles = [], pAnimId = null;

function spawnParticles(count=60) {
  pc.width = window.innerWidth; pc.height = window.innerHeight;
  pc.classList.add('show');
  particles = Array.from({length:count}, () => ({
    x: Math.random()*pc.width, y: pc.height*0.3 + Math.random()*pc.height*0.4,
    vx: (Math.random()-0.5)*6, vy: -Math.random()*8 - 2,
    r: Math.random()*6+3,
    color: ['#ffd700','#ff8800','#ffcc00','#fff','#ff6600'][Math.floor(Math.random()*5)],
    life: 1, decay: Math.random()*0.02+0.012
  }));
  if(pAnimId) cancelAnimationFrame(pAnimId);
  animParticles();
  setTimeout(()=>{ particles=[]; pc.classList.remove('show'); }, 3500);
}
function animParticles() {
  pctx.clearRect(0,0,pc.width,pc.height);
  particles = particles.filter(p=>p.life>0);
  particles.forEach(p=>{
    p.x+=p.vx; p.y+=p.vy; p.vy+=0.25; p.life-=p.decay;
    pctx.globalAlpha=p.life;
    pctx.fillStyle=p.color;
    pctx.beginPath(); pctx.arc(p.x,p.y,p.r,0,Math.PI*2); pctx.fill();
  });
  pctx.globalAlpha=1;
  if(particles.length>0) pAnimId=requestAnimationFrame(animParticles);
}

// ══════════════════════════════════════════════════════
//  GAME STATE
// ══════════════════════════════════════════════════════
let currentGrid = Array.from({length:ROWS},()=>Array.from({length:COLS},()=>pool[Math.floor(Math.random()*pool.length)]));
let spinning=false, autoRunning=false, autoLeft=0, turbo=false;
let freeSpins=0, inFreeSpins=false;
let lines=3;
let sess={spins:0,wins:0,best:0,net:0};
let history=[];
let hlCoords=[], hlPhase=0, hlTimer=null;

resize();

// ══════════════════════════════════════════════════════
//  REEL ANIMATION — column-by-column stop
// ══════════════════════════════════════════════════════
function animateReels(targetGrid, onDone) {
  const speed    = turbo ? 40 : 70;
  const stopDelay= turbo ? 150: 280;
  const minSpins = turbo ? 4  : 8;

  let offsets = [0,0,0];
  let stopped = [false,false,false];
  let spinCounts = [0,0,0];
  let startTimes = [0, stopDelay, stopDelay*2];
  const startT = performance.now();

  function frame(now) {
    const elapsed = now - startT;
    for(let c=0;c<COLS;c++) {
      if(stopped[c]) continue;
      if(elapsed < startTimes[c]) continue;
      offsets[c] = (offsets[c] + speed/16) % CELL;
      if(offsets[c] > CELL*0.5) spinCounts[c]++;

      // Column stops after minSpins
      if(spinCounts[c] >= minSpins + c*3 && elapsed >= startTimes[c] + minSpins*CELL) {
        offsets[c]=0;
        stopped[c]=true;
        // Update that column in currentGrid
        for(let r=0;r<ROWS;r++) currentGrid[r][c] = targetGrid[r][c];
        playReelStop(c);
      }
    }
    drawGrid(currentGrid, [], offsets.map((o,c)=>stopped[c]?0:o));
    if(stopped.every(s=>s)) onDone();
    else requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}

// ══════════════════════════════════════════════════════
//  WIN LINE FLASH
// ══════════════════════════════════════════════════════
function startWinFlash(coords) {
  hlCoords=coords; hlPhase=0;
  if(hlTimer) clearInterval(hlTimer);
  hlTimer = setInterval(()=>{
    hlPhase = 1 - hlPhase;
    drawGrid(currentGrid, hlPhase?coords:[]);
  }, 400);
  setTimeout(()=>{ clearInterval(hlTimer); hlTimer=null; drawGrid(currentGrid,coords); },3000);
}

// ══════════════════════════════════════════════════════
//  BIG WIN OVERLAY
// ══════════════════════════════════════════════════════
const WIN_TIERS = [{min:50,label:'🏆 ULTRA WIN!',color:'#ffd700'},{min:20,label:'🌟 MEGA WIN!',color:'#ff8800'},{min:5,label:'🎉 BIG WIN!',color:'#22c55e'}];
function showBigWin(profit, mult, onClose) {
  const tier = WIN_TIERS.find(t=>mult>=t.min) || null;
  if(!tier) { if(onClose) onClose(); return; }
  const ov = document.getElementById('bwOverlay');
  const lbl = document.getElementById('bwLabel');
  lbl.textContent = tier.label;
  lbl.style.color = tier.color;
  document.getElementById('bwAmount').textContent = 'R$ '+profit.toFixed(2);
  document.getElementById('bwMult').textContent = mult+'x';
  ov.classList.add('show');
  spawnParticles(tier.min>=50?120:80);
  playBigWin();
  window._bwClose = ()=>{ ov.classList.remove('show'); if(onClose) onClose(); };
}
function closeBigWin() { if(window._bwClose) window._bwClose(); }

// ══════════════════════════════════════════════════════
//  FREE SPINS
// ══════════════════════════════════════════════════════
function startFreeSpins(count) {
  freeSpins=count; inFreeSpins=true;
  document.getElementById('fsBanner').classList.add('show');
  document.getElementById('fsCount').textContent=count;
  playScatter();
  showBigWin(0, 0, ()=>{ document.getElementById('bwLabel').textContent='⭐ FREE SPINS!'; document.getElementById('bwLabel').style.color='#ffd700'; document.getElementById('bwAmount').textContent=count+' giros grátis!'; document.getElementById('bwMult').textContent='Sem custo'; document.getElementById('bwOverlay').classList.add('show'); window._bwClose=()=>{ document.getElementById('bwOverlay').classList.remove('show'); setTimeout(()=>spin(),400); }; });
}
function updateFSBanner() {
  document.getElementById('fsCount').textContent=freeSpins;
  if(freeSpins<=0){ inFreeSpins=false; document.getElementById('fsBanner').classList.remove('show'); }
}

// ══════════════════════════════════════════════════════
//  SPIN
// ══════════════════════════════════════════════════════
function setL(n,btn){ lines=n; document.querySelectorAll('.line-btn').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); }
function setBet(v){ document.getElementById('betAmt').value=v; }
function toggleTurbo(){ turbo=!turbo; document.getElementById('turboBtn').classList.toggle('on',turbo); }

async function spin() {
  if(spinning) return;
  spinning=true;
  if(hlTimer){ clearInterval(hlTimer); hlTimer=null; }

  const spinBtn = document.getElementById('spinBtn');
  spinBtn.disabled=true;
  spinBtn.textContent='Girando...';
  const wd = document.getElementById('winDisplay');
  wd.className='win-txt win-neutral'; wd.textContent='Girando...';
  document.getElementById('winBar').classList.remove('won');
  playSpinStart();

  const amt = inFreeSpins ? 0 : parseFloat(document.getElementById('betAmt').value)||1;

  let d;
  if(!IS_LOGGED) {
    // Demo mode — client-side only
    d = demoSpin(amt, lines, THEME);
  } else {
    try {
      const fd=new FormData();
      fd.append('amount',amt); fd.append('lines',lines); fd.append('theme',THEME);
      if(inFreeSpins) fd.append('free',1);
      const resp = await fetch('/api/games/slot.php',{method:'POST',body:fd});
      d = await resp.json();
    } catch(e) { d={success:false,error:'Erro de conexão'}; }
  }

  if(!d.success) {
    spinning=false; spinBtn.disabled=false; spinBtn.textContent='🎰 GIRAR';
    wd.textContent='❌ '+(d.error||'Erro'); return;
  }

  // Build result grid from server (or demo)
  const targetGrid = Array.from({length:ROWS},(_,r)=>
    Array.from({length:COLS},(_,c)=>{
      const s=d.grid[r][c];
      return syms.find(x=>x.id===s.id)||syms[syms.length-1];
    })
  );

  animateReels(targetGrid, ()=>afterSpin(d, targetGrid, amt));
}

function afterSpin(d, resultGrid, amt) {
  currentGrid = resultGrid;

  // Count scatters
  let scatterCount=0;
  resultGrid.flat().forEach(s=>{ if(s.scatter) scatterCount++; });

  // Win line highlights
  const WIN_LINES=[[[0,0],[0,1],[0,2]],[[1,0],[1,1],[1,2]],[[2,0],[2,1],[2,2]],[[0,0],[1,1],[2,2]],[[0,2],[1,1],[2,0]]];
  const hlC=[];
  (d.wins||[]).forEach(w=>{ if(w.line<WIN_LINES.length) WIN_LINES[w.line].forEach(c=>hlC.push(c)); });
  if(hlC.length) startWinFlash(hlC);
  else drawGrid(currentGrid,[]);

  const won = d.profit>0;
  sess.spins++; sess.net+=d.net||0;
  if(won){ sess.wins++; if(d.multiplier>sess.best) sess.best=d.multiplier; }

  const wd=document.getElementById('winDisplay');
  const wb=document.getElementById('winBar');
  if(won){
    wd.className='win-txt win-amount';
    wd.textContent=`🏆 +R$ ${d.profit.toFixed(2)} (${d.multiplier}x)`;
    wb.classList.add('won');
    playWin(d.multiplier);
    if(d.multiplier>=5) setTimeout(()=>showBigWin(d.profit,d.multiplier,()=>afterBigWin(scatterCount)),300);
    else afterBigWin(scatterCount);
  } else {
    wd.className='win-txt win-neutral';
    wd.textContent=scatterCount>=2?`${scatterCount} ⭐ — mais 1 para free spins!`:'Sem sorte...';
    afterBigWin(scatterCount);
  }

  updateStats(); addHistory(resultGrid,d,won);
  fetchBalance();

  if(inFreeSpins){ freeSpins--; updateFSBanner(); }
}

function afterBigWin(scatterCount) {
  spinning=false;
  const spinBtn=document.getElementById('spinBtn');
  spinBtn.disabled=false; spinBtn.textContent='🎰 GIRAR';

  if(scatterCount>=3 && !inFreeSpins) { startFreeSpins(10); return; }
  if(inFreeSpins && freeSpins>0) { setTimeout(()=>spin(), turbo?300:700); return; }
  if(autoRunning && autoLeft>0){
    autoLeft--;
    document.getElementById('autoStartBtn').textContent=`▶ (${autoLeft})`;
    if(autoLeft>0) setTimeout(()=>spin(), turbo?200:500);
    else stopAuto();
  }
}

// ══════════════════════════════════════════════════════
//  DEMO MODE (client-side, no login needed)
// ══════════════════════════════════════════════════════
function demoSpin(amt, lines, theme) {
  const grid=Array.from({length:ROWS},()=>Array.from({length:COLS},()=>pool[Math.floor(Math.random()*pool.length)]));
  const WIN_LINES=[[[0,0],[0,1],[0,2]],[[1,0],[1,1],[1,2]],[[2,0],[2,1],[2,2]],[[0,0],[1,1],[2,2]],[[0,2],[1,1],[2,0]]];
  const wins=[]; let totalMult=0;
  for(let l=0;l<lines;l++){
    const line=WIN_LINES[l]; const rowCols=line.map(([r,c])=>grid[r][c]);
    const first=rowCols[0];
    if(!first.scatter && rowCols.every(s=>s.id===first.id)){
      wins.push({line:l,sym:first.id,mult:first.mult}); totalMult+=first.mult;
    }
  }
  const profit=totalMult>0?(amt*totalMult):0;
  return {success:true,grid:grid.map(r=>r.map(s=>({id:s.id}))),wins,profit,multiplier:totalMult,net:profit-amt,saldo:0};
}

// ══════════════════════════════════════════════════════
//  AUTO SPIN
// ══════════════════════════════════════════════════════
function startAuto(){
  const n=parseInt(document.getElementById('autoN').value);
  if(!n||n<1) return;
  autoRunning=true; autoLeft=n;
  document.getElementById('autoStopBtn').style.display='';
  document.getElementById('autoStartBtn').textContent=`▶ (${n})`;
  spin();
}
function stopAuto(){
  autoRunning=false; autoLeft=0;
  document.getElementById('autoStopBtn').style.display='none';
  document.getElementById('autoStartBtn').textContent='▶ Auto';
}

// ══════════════════════════════════════════════════════
//  STATS & HISTORY
// ══════════════════════════════════════════════════════
function updateStats(){
  document.getElementById('sSpins').textContent=sess.spins;
  document.getElementById('sWins').textContent=sess.wins;
  document.getElementById('sBest').textContent=sess.best+'x';
  const ne=document.getElementById('sNet');
  ne.textContent=(sess.net>=0?'+':'')+'R$'+Math.abs(sess.net).toFixed(2);
  ne.style.color=sess.net>=0?'#10b981':'#ef4444';
}
function addHistory(grid,d,won){
  history.unshift({won,net:d.net||0,sym:grid[1][1].emoji,mult:d.multiplier||0});
  if(history.length>15) history.pop();
  document.getElementById('histList').innerHTML=history.map(h=>
    `<div class="hrow"><span style="font-size:1.1rem">${h.sym}</span><span class="${h.won?'hw':'hl'}">${h.won?'+':'-'}R$${Math.abs(h.net).toFixed(2)}</span><span style="color:rgba(255,255,255,.35);font-size:.72rem">${h.mult?h.mult+'x':'-'}</span></div>`
  ).join('');
}
async function fetchBalance(){
  try{
    const r=await fetch('/api/get_saldo.php');
    const d=await r.json();
    document.getElementById('balDisplay').textContent='R$ '+parseFloat(d.saldo||0).toLocaleString('pt-BR',{minimumFractionDigits:2});
  }catch(e){}
}

// Init
playClick();
resize();
</script>
</body>
</html>
