<?php
// This file replaces jogos/tiger.php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'RaspaPix';
$theme    = preg_replace('/[^a-z]/', '', strtolower($_GET['t'] ?? 'tiger'));
$themes   = [
    'tiger'  => ['name'=>'Fortune Tiger',  'main'=>'🐯','bg1'=>'#1a0000','bg2'=>'#3a0800','accent'=>'#e8a000','line'=>'#ff6600'],
    'rabbit' => ['name'=>'Fortune Rabbit', 'main'=>'🐰','bg1'=>'#10003a','bg2'=>'#2a0050','accent'=>'#cc44ff','line'=>'#9933cc'],
    'dragon' => ['name'=>'Fortune Dragon', 'main'=>'🐉','bg1'=>'#001030','bg2'=>'#002060','accent'=>'#00aaff','line'=>'#0066dd'],
];
$t = $themes[$theme] ?? $themes['tiger'];
$isLogged = isset($_SESSION['usuario_id']);
$saldo = 0;
if ($isLogged) {
    $st = $pdo->prepare("SELECT saldo FROM usuarios WHERE id=? LIMIT 1");
    $st->execute([$_SESSION['usuario_id']]);
    $saldo = $st->fetchColumn() ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($t['name']) ?> — 🍀 RaspaPix</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:linear-gradient(160deg,<?= $t['bg1'] ?>,<?= $t['bg2'] ?>,#000);color:#fff;font-family:'Outfit',sans-serif;min-height:100vh}

/* NAV */
.nav{height:54px;background:rgba(0,0,0,.7);border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;padding:0 20px;gap:14px;position:sticky;top:0;z-index:100;backdrop-filter:blur(10px)}
.nav-back{font-size:.85rem;color:rgba(255,255,255,.5);display:flex;align-items:center;gap:6px}
.nav-back:hover{color:#fff}
.nav-logo-rp{font-size:1rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:5px;letter-spacing:.02em}
.nav-logo-rp .pix{color:#22c55e}
.nav-divider{width:1px;height:20px;background:rgba(255,255,255,.12)}
.nav-title{font-size:1.1rem;font-weight:900;color:<?= $t['accent'] ?>;letter-spacing:.05em}
.nav-balance{margin-left:auto;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:6px 14px;font-size:.8rem;font-weight:700}
.nav-deposit{background:#16a34a;color:#fff;border:none;border-radius:8px;padding:6px 14px;font-size:.8rem;font-weight:800;cursor:pointer;font-family:inherit}

/* LAYOUT */
.layout{max-width:1000px;margin:0 auto;padding:20px 16px;display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start}
@media(max-width:720px){.layout{grid-template-columns:1fr}}

/* MACHINE */
.machine{background:rgba(0,0,0,.5);border:2px solid <?= $t['accent'] ?>55;border-radius:20px;overflow:hidden}
.machine-header{background:linear-gradient(135deg,<?= $t['bg2'] ?>,<?= $t['accent'] ?>33);padding:16px 20px;text-align:center;border-bottom:2px solid <?= $t['accent'] ?>33}
.machine-title{font-size:1.5rem;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:<?= $t['accent'] ?>}
.machine-badge{display:inline-block;background:<?= $t['accent'] ?>;color:#000;font-size:.65rem;font-weight:900;padding:2px 8px;border-radius:4px;margin-left:8px;vertical-align:middle}
.machine-body{padding:20px}

/* REEL CANVAS */
.reels-frame{background:#000;border:3px solid <?= $t['accent'] ?>66;border-radius:14px;padding:4px;margin-bottom:14px;position:relative}
.reels-frame::before,.reels-frame::after{content:'';position:absolute;left:4px;right:4px;height:2px;background:<?= $t['accent'] ?>88;z-index:10}
.reels-frame::before{top:35%}
.reels-frame::after{bottom:35%}
canvas#reelCanvas{display:block;width:100%;border-radius:10px}

/* WIN DISPLAY */
.win-bar{height:44px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;border-radius:10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.06)}
.win-text{font-size:1.4rem;font-weight:900}
.win-amount{color:#ffd700;text-shadow:0 0 20px #ffd70099}
.win-neutral{color:rgba(255,255,255,.3);font-size:.9rem;font-weight:600}

/* LINE BUTTONS */
.line-row{display:flex;gap:6px;margin-bottom:14px}
.line-btn{flex:1;padding:7px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.5);font-size:.8rem;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
.line-btn.active{background:<?= $t['accent'] ?>33;border-color:<?= $t['accent'] ?>;color:<?= $t['accent'] ?>}

/* CONTROLS */
.controls-card{background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:14px}
.ctrl-row{display:flex;gap:8px;align-items:center;margin-bottom:10px}
.ctrl-label{font-size:.75rem;color:rgba(255,255,255,.4);white-space:nowrap;width:54px}
.ctrl-input{flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#fff;border-radius:8px;padding:9px 12px;font-size:1rem;font-weight:700;font-family:inherit;outline:none}
.ctrl-input:focus{border-color:<?= $t['accent'] ?>}
.quick-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;margin-bottom:12px}
.qb{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.6);border-radius:6px;padding:6px 4px;font-size:.75rem;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
.qb:hover{background:rgba(255,255,255,.1);color:#fff}
.spin-btn{width:100%;padding:16px;border-radius:12px;border:none;font-size:1.2rem;font-weight:900;cursor:pointer;letter-spacing:.04em;transition:.2s;font-family:inherit;background:linear-gradient(135deg,<?= $t['line'] ?>,<?= $t['accent'] ?>);color:#fff;position:relative;overflow:hidden}
.spin-btn::after{content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:.4s}
.spin-btn:hover::after{left:150%}
.spin-btn:hover{transform:translateY(-2px);box-shadow:0 8px 30px <?= $t['accent'] ?>55}
.spin-btn:disabled{background:#222;color:#555;cursor:not-allowed;transform:none;box-shadow:none}
.auto-ctrl{display:flex;gap:8px;margin-top:10px}
.auto-btn{flex:1;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7);border-radius:8px;padding:8px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.auto-btn:hover{background:rgba(255,255,255,.1);color:#fff}
.stop-btn{background:rgba(239,68,68,.15)!important;border-color:#ef4444!important;color:#ef4444!important}

/* RIGHT PANEL */
.right{display:flex;flex-direction:column;gap:14px}
.panel{background:rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:14px}
.panel h3{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);margin-bottom:12px}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.stat-box{background:rgba(0,0,0,.3);border-radius:10px;padding:10px;text-align:center}
.stat-val{font-size:1.1rem;font-weight:800;color:<?= $t['accent'] ?>}
.stat-lbl{font-size:.65rem;color:rgba(255,255,255,.35);margin-top:2px}
.hist-list{display:flex;flex-direction:column;gap:5px}
.hist-row{display:flex;align-items:center;justify-content:space-between;padding:6px 8px;background:rgba(0,0,0,.2);border-radius:7px;font-size:.8rem}
.hist-sym{font-size:1rem}
.hist-status-won{color:#10b981;font-weight:700}
.hist-status-lost{color:#ef4444;font-weight:700}
.game-links{display:flex;flex-direction:column;gap:6px}
.game-link{display:flex;align-items:center;gap:10px;padding:9px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:10px;cursor:pointer;transition:.15s;text-decoration:none;color:#fff}
.game-link:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15)}
.gl-icon{font-size:1.4rem}
.gl-name{font-size:.875rem;font-weight:700}
.gl-sub{font-size:.7rem;color:rgba(255,255,255,.35)}
@keyframes shimmer{0%,100%{opacity:1}50%{opacity:.5}}
.spinning-label{animation:shimmer .6s infinite}
</style>
</head>
<body>
<nav class="nav">
  <a href="/jogos/" class="nav-logo-rp">🍀 RASPA<span class="pix">PIX</span></a>
  <div class="nav-divider"></div>
  <a href="/jogos/" class="nav-back">← Lobby</a>
  <div class="nav-title"><?= strtoupper($t['name']) ?></div>
  <div class="nav-balance" id="balDisplay">R$ <?= number_format($saldo,2,',','.') ?></div>
  <button class="nav-deposit" onclick="location.href='/'">+ Dep.</button>
</nav>

<div class="layout">
  <div>
    <div class="machine">
      <div class="machine-header">
        <span class="machine-title"><?= htmlspecialchars($t['name']) ?></span>
        <span class="machine-badge">PG STYLE</span>
      </div>
      <div class="machine-body">
        <div class="reels-frame">
          <canvas id="reelCanvas" height="270"></canvas>
        </div>
        <div class="win-bar">
          <div id="winDisplay" class="win-text win-neutral">Aposte e gire!</div>
        </div>
        <div class="line-row">
          <button class="line-btn" onclick="setL(1,this)">1 Linha</button>
          <button class="line-btn active" onclick="setL(3,this)">3 Linhas</button>
          <button class="line-btn" onclick="setL(5,this)">5 Linhas</button>
        </div>
      </div>
    </div>

    <div class="controls-card" style="margin-top:14px">
      <div class="ctrl-row">
        <span class="ctrl-label">Aposta</span>
        <input type="number" id="betAmt" class="ctrl-input" value="1.00" min="0.10" step="0.5">
      </div>
      <div class="quick-grid">
        <?php foreach([0.5,1,2,5,10,25,50,100] as $v): ?>
        <button class="qb" onclick="document.getElementById('betAmt').value='<?=$v?>'">R$<?=$v<1?'0.5':$v?></button>
        <?php endforeach; ?>
      </div>
      <button class="spin-btn" id="spinBtn" onclick="spin()">🎰 GIRAR</button>
      <div class="auto-ctrl">
        <input type="number" id="autoN" class="ctrl-input" placeholder="Auto giros" min="1" max="500" style="font-size:.85rem">
        <button class="auto-btn" onclick="startAuto()" id="autoStartBtn">▶ Auto</button>
        <button class="auto-btn stop-btn" onclick="stopAuto()" id="autoStopBtn" style="display:none">■ Stop</button>
      </div>
    </div>
  </div>

  <div class="right">
    <div class="panel">
      <h3>Sessão</h3>
      <div class="stat-grid">
        <div class="stat-box"><div class="stat-val" id="sSpins">0</div><div class="stat-lbl">Giros</div></div>
        <div class="stat-box"><div class="stat-val" id="sWins">0</div><div class="stat-lbl">Vitórias</div></div>
        <div class="stat-box"><div class="stat-val" id="sBest">0x</div><div class="stat-lbl">Maior Mult</div></div>
        <div class="stat-box"><div class="stat-val" id="sNet">R$0</div><div class="stat-lbl">Resultado</div></div>
      </div>
    </div>
    <div class="panel">
      <h3>Últimos Giros</h3>
      <div class="hist-list" id="histList"><div style="color:rgba(255,255,255,.25);font-size:.8rem;text-align:center">Nenhum giro ainda</div></div>
    </div>
    <div class="panel">
      <h3>Outros Jogos</h3>
      <div class="game-links">
        <a href="/jogos/tiger.php?t=tiger" class="game-link"><span class="gl-icon">🐯</span><div><div class="gl-name">Fortune Tiger</div><div class="gl-sub">50x max</div></div></a>
        <a href="/jogos/tiger.php?t=rabbit" class="game-link"><span class="gl-icon">🐰</span><div><div class="gl-name">Fortune Rabbit</div><div class="gl-sub">50x max</div></div></a>
        <a href="/jogos/tiger.php?t=dragon" class="game-link"><span class="gl-icon">🐉</span><div><div class="gl-name">Fortune Dragon</div><div class="gl-sub">50x max</div></div></a>
        <a href="/jogos/aviator.php" class="game-link"><span class="gl-icon">✈️</span><div><div class="gl-name">Aviator</div><div class="gl-sub">Ao vivo</div></div></a>
      </div>
    </div>
  </div>
</div>

<script>
const THEME = '<?= $theme ?>';
const ACCENT = '<?= $t['accent'] ?>';
const LINE_CLR = '<?= $t['line'] ?>';

// ── Symbol definitions ───────────────────────────────────────────────
const SYMS = {
  tiger:  [{id:'tiger', label:'TIGER', color:'#ff6600', bg:'#3a0000', emoji:'🐯', mult:50},
           {id:'dragon',label:'DRAGON',color:'#ff2200',bg:'#2a0000',emoji:'🐉',mult:25},
           {id:'gold',  label:'GOLD',  color:'#ffd700', bg:'#2a1800',emoji:'💰',mult:12},
           {id:'gem',   label:'GEM',   color:'#00ccff', bg:'#001a22',emoji:'💎',mult:8},
           {id:'coin',  label:'COIN',  color:'#e8a000', bg:'#1a1200',emoji:'🪙',mult:4},
           {id:'A',     label:'A',     color:'#ff4444', bg:'#1a0000',emoji:'A', mult:2},
           {id:'K',     label:'K',     color:'#cc88ff', bg:'#100018',emoji:'K', mult:1.5}],
  rabbit: [{id:'rabbit',label:'RABBIT',color:'#ff88cc',bg:'#2a0040',emoji:'🐰',mult:50},
           {id:'flower',label:'FLOWER',color:'#ff66aa',bg:'#250030',emoji:'🌸',mult:25},
           {id:'moon',  label:'MOON',  color:'#ddccff', bg:'#0f0022',emoji:'🌙',mult:12},
           {id:'gem',   label:'GEM',   color:'#00ccff', bg:'#001018',emoji:'💎',mult:8},
           {id:'coin',  label:'COIN',  color:'#e8a000', bg:'#12100a',emoji:'🪙',mult:4},
           {id:'A',     label:'A',     color:'#cc44ff', bg:'#0d0018',emoji:'A', mult:2},
           {id:'K',     label:'K',     color:'#aa22ff', bg:'#0a0015',emoji:'K', mult:1.5}],
  dragon: [{id:'dragon',label:'DRAGON',color:'#00ccff',bg:'#001a30',emoji:'🐉',mult:50},
           {id:'fire',  label:'FIRE',  color:'#ff8800', bg:'#1a0c00',emoji:'🔥',mult:25},
           {id:'pearl', label:'PEARL', color:'#88ddff', bg:'#001520',emoji:'🔮',mult:12},
           {id:'gem',   label:'GEM',   color:'#44ffcc', bg:'#001a12',emoji:'💎',mult:8},
           {id:'coin',  label:'COIN',  color:'#e8a000', bg:'#0f0e00',emoji:'🪙',mult:4},
           {id:'A',     label:'A',     color:'#00aaff', bg:'#00101e',emoji:'A', mult:2},
           {id:'K',     label:'K',     color:'#0066dd', bg:'#000a18',emoji:'K', mult:1.5}],
};
const syms = SYMS[THEME] || SYMS.tiger;

// Weighted pool
const WEIGHTS = [2, 4, 8, 12, 22, 30, 22];
const pool = [];
syms.forEach((s, i) => { for (let w = 0; w < WEIGHTS[i]; w++) pool.push(s); });

// ── Canvas Reel Renderer ─────────────────────────────────────────────
const canvas = document.getElementById('reelCanvas');
const ctx    = canvas.getContext('2d');
const COLS = 3, ROWS = 3, CELL = 90;
canvas.width  = CELL * COLS;
canvas.height = CELL * ROWS;

function drawSymbol(ctx, sym, x, y, w, h, highlight) {
    // Background
    ctx.fillStyle = highlight ? sym.color + '33' : sym.bg;
    roundRect(ctx, x+2, y+2, w-4, h-4, 10);
    ctx.fill();

    if (highlight) {
        ctx.strokeStyle = sym.color;
        ctx.lineWidth = 2;
        ctx.shadowColor = sym.color;
        ctx.shadowBlur = 15;
        roundRect(ctx, x+2, y+2, w-4, h-4, 10);
        ctx.stroke();
        ctx.shadowBlur = 0;
    }

    // Emoji / letter
    const isLetter = sym.emoji.length === 1 && 'AKQJ'.includes(sym.emoji);
    if (isLetter) {
        ctx.fillStyle = sym.color;
        ctx.font = `bold 42px Outfit`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = sym.color;
        ctx.shadowBlur = 8;
        ctx.fillText(sym.emoji, x + w/2, y + h/2);
        ctx.shadowBlur = 0;
    } else {
        ctx.font = `${h*0.5}px serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(sym.emoji, x + w/2, y + h/2);
    }

    // Label
    ctx.fillStyle = sym.color + 'aa';
    ctx.font = `600 10px Outfit`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'bottom';
    if (!isLetter) ctx.fillText(sym.label, x + w/2, y + h - 5);
}

function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x+r,y); ctx.lineTo(x+w-r,y); ctx.arcTo(x+w,y,x+w,y+r,r);
    ctx.lineTo(x+w,y+h-r); ctx.arcTo(x+w,y+h,x+w-r,y+h,r);
    ctx.lineTo(x+r,y+h); ctx.arcTo(x,y+h,x,y+h-r,r);
    ctx.lineTo(x,y+r); ctx.arcTo(x,y,x+r,y,r);
    ctx.closePath();
}

function drawGrid(grid, highlights) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#070708';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Dividers
    ctx.strokeStyle = 'rgba(255,255,255,0.06)';
    ctx.lineWidth = 1;
    for (let c = 1; c < COLS; c++) { ctx.beginPath(); ctx.moveTo(c*CELL,0); ctx.lineTo(c*CELL,canvas.height); ctx.stroke(); }
    for (let r = 1; r < ROWS; r++) { ctx.beginPath(); ctx.moveTo(0,r*CELL); ctx.lineTo(canvas.width,r*CELL); ctx.stroke(); }

    for (let r = 0; r < ROWS; r++) {
        for (let c = 0; c < COLS; c++) {
            const hl = highlights && highlights.some(h => h[0]===r && h[1]===c);
            drawSymbol(ctx, grid[r][c], c*CELL, r*CELL, CELL, CELL, hl);
        }
    }
}

// Initial grid
let currentGrid = Array.from({length:ROWS}, () => Array.from({length:COLS}, () => pool[Math.floor(Math.random()*pool.length)]));
drawGrid(currentGrid, []);

// ── Spin animation ───────────────────────────────────────────────────
let spinning = false;
let autoRunning = false, autoLeft = 0;
let lines = 3;
let sess = {spins:0, wins:0, best:0, net:0};
let history = [];

function setL(n, btn) {
    lines = n;
    document.querySelectorAll('.line-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

async function spin() {
    if (spinning) return;
    spinning = true;
    document.getElementById('spinBtn').disabled = true;
    document.getElementById('spinBtn').innerHTML = '<span class="spinning-label">⏳ Girando...</span>';
    document.getElementById('winDisplay').className = 'win-text win-neutral';
    document.getElementById('winDisplay').textContent = 'Girando...';

    const amt = parseFloat(document.getElementById('betAmt').value);
    const fd  = new FormData();
    fd.append('amount', amt); fd.append('lines', lines); fd.append('theme', THEME);
    const r = await fetch('/api/games/slot.php', {method:'POST', body:fd});
    const d = await r.json();

    if (!d.success) {
        spinning = false;
        document.getElementById('spinBtn').disabled = false;
        document.getElementById('spinBtn').textContent = '🎰 GIRAR';
        document.getElementById('winDisplay').textContent = '❌ ' + d.error;
        return;
    }

    // Build result grid
    const resultGrid = Array.from({length:ROWS}, (_, r) =>
        Array.from({length:COLS}, (_, c) => {
            const s = d.grid[r][c];
            return syms.find(x => x.id === s.id) || {id:'K',label:'K',color:'#aaa',bg:'#111',emoji:'K',mult:1.5};
        })
    );

    // Animate: quick spin frames then snap to result
    let frame = 0;
    const frames = 18;
    const spinTimer = setInterval(() => {
        frame++;
        const tempGrid = Array.from({length:ROWS}, () =>
            Array.from({length:COLS}, () => pool[Math.floor(Math.random()*pool.length)])
        );
        drawGrid(tempGrid, []);
        if (frame >= frames) {
            clearInterval(spinTimer);
            showResult(d, resultGrid, amt);
        }
    }, 50);
}

function showResult(d, resultGrid, amt) {
    // Build highlight coords
    const hlCoords = [];
    const WIN_LINES = [
        [[0,0],[0,1],[0,2]],
        [[1,0],[1,1],[1,2]],
        [[2,0],[2,1],[2,2]],
        [[0,0],[1,1],[2,2]],
        [[0,2],[1,1],[2,0]],
    ];
    (d.wins || []).forEach(w => {
        if (w.line < WIN_LINES.length) WIN_LINES[w.line].forEach(c => hlCoords.push(c));
    });

    drawGrid(resultGrid, hlCoords);
    currentGrid = resultGrid;

    const won = d.profit > 0;
    sess.spins++;
    sess.net += d.net;
    if (won) { sess.wins++; if (d.multiplier > sess.best) sess.best = d.multiplier; }

    const wd = document.getElementById('winDisplay');
    if (won) {
        wd.className = 'win-text win-amount';
        wd.textContent = `🏆 +R$ ${d.profit.toFixed(2)} (${d.multiplier}x)`;
    } else {
        wd.className = 'win-text win-neutral';
        wd.textContent = 'Sem sorte...';
    }

    // Stats
    document.getElementById('sSpins').textContent = sess.spins;
    document.getElementById('sWins').textContent  = sess.wins;
    document.getElementById('sBest').textContent  = sess.best + 'x';
    const ne = document.getElementById('sNet');
    ne.textContent = (sess.net >= 0 ? '+' : '') + 'R$' + Math.abs(sess.net).toFixed(2);
    ne.style.color = sess.net >= 0 ? '#10b981' : '#ef4444';

    // History
    history.unshift({won, net: d.net, sym: resultGrid[1][1].emoji, mult: d.multiplier});
    if (history.length > 15) history.pop();
    document.getElementById('histList').innerHTML = history.map(h =>
        `<div class="hist-row"><span class="hist-sym">${h.sym}</span><span class="${h.won?'hist-status-won':'hist-status-lost'}">${h.won?'+':'-'}R$ ${Math.abs(h.net).toFixed(2)}</span><span style="color:rgba(255,255,255,.4);font-size:.75rem">${h.mult}x</span></div>`
    ).join('');

    // Update balance
    fetchBalance();

    spinning = false;
    document.getElementById('spinBtn').disabled = false;
    document.getElementById('spinBtn').textContent = '🎰 GIRAR';

    if (autoRunning && autoLeft > 0) {
        autoLeft--;
        document.getElementById('autoStartBtn').textContent = `▶ (${autoLeft})`;
        if (autoLeft > 0) setTimeout(spin, 600);
        else stopAuto();
    }
}

function startAuto() {
    const n = parseInt(document.getElementById('autoN').value);
    if (!n || n < 1) return;
    autoRunning = true; autoLeft = n;
    document.getElementById('autoStopBtn').style.display = '';
    document.getElementById('autoStartBtn').textContent = `▶ (${n})`;
    spin();
}
function stopAuto() {
    autoRunning = false; autoLeft = 0;
    document.getElementById('autoStopBtn').style.display = 'none';
    document.getElementById('autoStartBtn').textContent = '▶ Auto';
}

async function fetchBalance() {
    try {
        const r = await fetch('/api/get_saldo.php');
        const d = await r.json();
        document.getElementById('balDisplay').textContent = 'R$ ' + parseFloat(d.saldo||0).toLocaleString('pt-BR',{minimumFractionDigits:2});
    } catch(e) {}
}
</script>
</body>
</html>
