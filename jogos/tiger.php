<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'Casino';
$theme    = preg_replace('/[^a-z]/', '', strtolower($_GET['t'] ?? 'tiger'));
$themes   = [
    'tiger'  => ['name'=>'Fortune Tiger',  'emoji'=>'🐯', 'color1'=>'#7a0000','color2'=>'#d4af37','accent'=>'#ff6b00','bg'=>'#1a0000'],
    'rabbit' => ['name'=>'Fortune Rabbit', 'emoji'=>'🐰', 'color1'=>'#4a0070','color2'=>'#ff69b4','accent'=>'#c084fc','bg'=>'#0d0018'],
    'dragon' => ['name'=>'Fortune Dragon', 'emoji'=>'🐉', 'color1'=>'#003a7a','color2'=>'#00d4aa','accent'=>'#38bdf8','bg'=>'#00091a'],
];
$t = $themes[$theme] ?? $themes['tiger'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($t['name']) ?> — <?= htmlspecialchars($nomeSite) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:<?= $t['bg'] ?>;color:#fff;font-family:'Outfit',sans-serif;min-height:100vh;overflow-x:hidden}
.bg-radial{position:fixed;inset:0;background:radial-gradient(ellipse at 50% 0%,<?= $t['color1'] ?>88,transparent 70%);pointer-events:none}

/* NAV */
.nav{display:flex;align-items:center;justify-content:space-between;padding:12px 24px;background:rgba(0,0,0,.5);border-bottom:1px solid rgba(255,255,255,.08);backdrop-filter:blur(10px)}
.nav-back{color:rgba(255,255,255,.6);font-size:.875rem;display:flex;align-items:center;gap:8px;text-decoration:none}
.nav-back:hover{color:#fff}
.nav-title{font-size:1.2rem;font-weight:900;letter-spacing:.05em;color:<?= $t['accent'] ?>;text-shadow:0 0 20px <?= $t['accent'] ?>88}
.nav-balance{font-size:.875rem;font-weight:700;color:#fff;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 14px}

/* MAIN LAYOUT */
.wrapper{display:grid;grid-template-columns:1fr 320px;gap:20px;max-width:960px;margin:0 auto;padding:24px 16px}
@media(max-width:700px){.wrapper{grid-template-columns:1fr}}

/* SLOT MACHINE */
.slot-machine{background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.01));border:1px solid rgba(255,255,255,.1);border-radius:24px;overflow:hidden}
.slot-header{background:linear-gradient(135deg,<?= $t['color1'] ?>,<?= $t['color2'] ?>44);padding:20px;text-align:center;position:relative;overflow:hidden}
.slot-header::before{content:'<?= $t['emoji'] ?>';position:absolute;font-size:8rem;opacity:.08;left:50%;top:50%;transform:translate(-50%,-50%)}
.slot-title{font-size:1.6rem;font-weight:900;letter-spacing:.1em;position:relative}
.slot-subtitle{font-size:.8rem;color:rgba(255,255,255,.5);position:relative;margin-top:4px}
.slot-body{padding:20px}

/* REELS */
.reels-wrap{background:#000;border:3px solid <?= $t['color2'] ?>88;border-radius:16px;padding:3px;margin-bottom:16px;position:relative;overflow:hidden}
.reels-inner{display:grid;grid-template-columns:1fr 1fr 1fr;gap:3px}
.reel{background:rgba(0,0,0,.8);border-radius:10px;overflow:hidden;position:relative}
.reel-strip{transition:transform .6s cubic-bezier(.22,1,.36,1)}
.reel-cell{height:90px;display:flex;align-items:center;justify-content:center;font-size:2.8rem;border-bottom:1px solid rgba(255,255,255,.05);user-select:none}
.reel-cell.text-sym{font-size:1.4rem;font-weight:900;letter-spacing:.05em;color:<?= $t['color2'] ?>}

/* Win line overlay */
.win-line{position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;opacity:0;transition:.3s}
.win-cell{position:absolute;background:rgba(255,215,0,.2);border:2px solid #ffd700;border-radius:8px;transition:.3s}

/* WIN DISPLAY */
.win-display{text-align:center;min-height:40px;margin-bottom:12px}
.win-amount{font-size:2rem;font-weight:900;color:#ffd700;text-shadow:0 0 20px #ffd70088;animation:pop .3s ease}
.win-label{font-size:.8rem;color:rgba(255,255,255,.5)}
@keyframes pop{0%{transform:scale(.8);opacity:.5}100%{transform:scale(1);opacity:1}}

/* LINE SELECTOR */
.line-selector{display:flex;gap:6px;margin-bottom:16px;justify-content:center}
.line-btn{flex:1;padding:7px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);font-size:.8rem;font-weight:700;cursor:pointer;transition:.2s;font-family:'Outfit',sans-serif}
.line-btn.active,.line-btn:hover{background:<?= $t['accent'] ?>33;border-color:<?= $t['accent'] ?>;color:#fff}

/* PAYTABLE */
.paytable{background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:14px;margin-bottom:16px}
.paytable h4{font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:10px}
.pay-row{display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.85rem}
.pay-sym{font-size:1.2rem;width:28px;text-align:center}
.pay-name{flex:1;padding:0 8px;color:rgba(255,255,255,.7)}
.pay-mult{color:#ffd700;font-weight:700}

/* CONTROLS */
.controls{background:rgba(0,0,0,.4);border-radius:16px;padding:16px;display:flex;flex-direction:column;gap:12px}
.amount-row{display:flex;align-items:center;gap:8px}
.amount-label{font-size:.75rem;color:rgba(255,255,255,.5);white-space:nowrap}
.amount-input{flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;border-radius:8px;padding:10px 12px;font-size:1rem;font-weight:700;font-family:'Outfit',sans-serif;outline:none}
.amount-input:focus{border-color:<?= $t['accent'] ?>}
.quick-bets{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
.quick-btn{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.7);border-radius:7px;padding:6px;font-size:.8rem;font-weight:700;cursor:pointer;transition:.2s;font-family:'Outfit',sans-serif}
.quick-btn:hover{background:rgba(255,255,255,.12);color:#fff}
.spin-btn{width:100%;padding:18px;border-radius:14px;border:none;font-size:1.2rem;font-weight:900;cursor:pointer;transition:.2s;font-family:'Outfit',sans-serif;letter-spacing:.05em;background:linear-gradient(135deg,<?= $t['color1'] ?>,<?= $t['accent'] ?>);color:#fff;position:relative;overflow:hidden;text-shadow:0 1px 3px rgba(0,0,0,.4)}
.spin-btn::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent,rgba(255,255,255,.15),transparent);transform:translateX(-100%);transition:.4s}
.spin-btn:hover::before{transform:translateX(100%)}
.spin-btn:hover{transform:translateY(-2px);box-shadow:0 8px 30px <?= $t['accent'] ?>66}
.spin-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.auto-row{display:flex;align-items:center;gap:10px}
.auto-label{font-size:.8rem;color:rgba(255,255,255,.5)}
.auto-input{flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#fff;border-radius:8px;padding:8px 10px;font-size:.875rem;font-family:'Outfit',sans-serif;outline:none}
.auto-btn{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:#fff;border-radius:8px;padding:8px 14px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;white-space:nowrap}

/* RIGHT PANEL */
.right-panel{display:flex;flex-direction:column;gap:16px}
.panel-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:16px;padding:16px}
.panel-card h3{font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.4);margin-bottom:12px}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.stat-box{background:rgba(0,0,0,.3);border-radius:10px;padding:12px;text-align:center}
.stat-val{font-size:1.1rem;font-weight:800;color:<?= $t['accent'] ?>}
.stat-lbl{font-size:.7rem;color:rgba(255,255,255,.4);margin-top:2px}
.history-list{space-y:6px;display:flex;flex-direction:column;gap:6px;max-height:240px;overflow-y:auto}
.hist-item{display:flex;align-items:center;justify-content:space-between;background:rgba(0,0,0,.2);border-radius:8px;padding:8px 10px;font-size:.8rem}
.hist-sym{font-size:1rem}
.hist-mult{font-weight:700}
.hist-won{color:#10b981}
.hist-lost{color:#ef4444}
.variant-btns{display:grid;grid-template-columns:1fr;gap:8px}
.var-btn{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:10px 12px;cursor:pointer;transition:.2s;text-decoration:none;color:#fff}
.var-btn:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2)}
.var-icon{font-size:1.5rem}
.var-name{font-size:.875rem;font-weight:700}
.var-sub{font-size:.7rem;color:rgba(255,255,255,.4)}
.spinning .reel-strip{transition:transform .8s cubic-bezier(.17,.84,.44,1)}
</style>
</head>
<body>
<div class="bg-radial"></div>

<nav class="nav">
  <a href="/jogos/" class="nav-back"><i class="fas fa-arrow-left"></i> Lobby</a>
  <div class="nav-title"><?= htmlspecialchars($t['name']) ?></div>
  <div class="nav-balance" id="balanceDisplay">Carregando...</div>
</nav>

<div class="wrapper">
  <!-- SLOT MACHINE -->
  <div>
    <div class="slot-machine">
      <div class="slot-header">
        <div class="slot-title"><?= strtoupper($t['name']) ?></div>
        <div class="slot-subtitle">Provably Fair · <?= htmlspecialchars($nomeSite) ?></div>
      </div>
      <div class="slot-body">

        <!-- Win display -->
        <div class="win-display" id="winDisplay">
          <div class="win-label">Faça sua aposta e gire!</div>
        </div>

        <!-- Reels -->
        <div class="reels-wrap" id="reelsWrap">
          <div class="reels-inner" id="reelsInner">
            <?php for ($c = 0; $c < 3; $c++): ?>
            <div class="reel" id="reel<?= $c ?>">
              <div class="reel-strip" id="strip<?= $c ?>">
                <?php $initSyms = ['🐯','🪙','7','💎','🐉','BAR','💰','🐯','🪙','7']; ?>
                <?php foreach ($initSyms as $s): ?>
                <div class="reel-cell <?= strlen($s) > 2 ? 'text-sym' : '' ?>"><?= $s ?></div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endfor; ?>
          </div>
        </div>

        <!-- Line selector -->
        <div class="line-selector">
          <?php foreach ([1,3,5] as $l): ?>
          <button class="line-btn <?= $l==3?'active':'' ?>" onclick="setLines(<?=$l?>,this)"><?=$l?> Linha<?=$l>1?'s':''?></button>
          <?php endforeach; ?>
        </div>

        <!-- Paytable -->
        <div class="paytable">
          <h4>Tabela de Pagamentos</h4>
          <div id="paytableRows"></div>
        </div>

      </div>
    </div>

    <!-- Controls -->
    <div class="controls" style="margin-top:16px">
      <div class="amount-row">
        <span class="amount-label">Aposta R$</span>
        <input type="number" id="betAmount" class="amount-input" value="1.00" min="0.10" step="0.50">
      </div>
      <div class="quick-bets">
        <?php foreach ([0.5,1,5,10,25,50,100,200] as $v): ?>
        <button class="quick-btn" onclick="setBet(<?=$v?>)"><?=$v<1?'0,50':number_format($v,0,'.',',')?></button>
        <?php endforeach; ?>
      </div>
      <button class="spin-btn" id="spinBtn" onclick="spin()">🎰 GIRAR</button>
      <div class="auto-row">
        <span class="auto-label">Auto:</span>
        <input type="number" id="autoCount" class="auto-input" placeholder="Nº de giros" min="1" max="100">
        <button class="auto-btn" onclick="startAuto()">▶ Auto</button>
        <button class="auto-btn" id="stopAutoBtn" onclick="stopAuto()" style="display:none;background:#ef444433;border-color:#ef4444">■ Stop</button>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <div class="panel-card">
      <h3>Estatísticas da Sessão</h3>
      <div class="stat-grid">
        <div class="stat-box"><div class="stat-val" id="statSpins">0</div><div class="stat-lbl">Giros</div></div>
        <div class="stat-box"><div class="stat-val" id="statWins">0</div><div class="stat-lbl">Ganhos</div></div>
        <div class="stat-box"><div class="stat-val" id="statBest">0x</div><div class="stat-lbl">Melhor</div></div>
        <div class="stat-box"><div class="stat-val" id="statNet">R$0</div><div class="stat-lbl">Resultado</div></div>
      </div>
    </div>

    <div class="panel-card">
      <h3>Últimos Resultados</h3>
      <div class="history-list" id="historyList"><div style="color:rgba(255,255,255,.3);font-size:.8rem;text-align:center;padding:12px">Nenhum giro ainda</div></div>
    </div>

    <div class="panel-card">
      <h3>Outros Jogos Fortune</h3>
      <div class="variant-btns">
        <a href="/jogos/tiger.php?t=tiger" class="var-btn" style="<?= $theme==='tiger'?'border-color:#ff6b00;background:rgba(255,107,0,.1)':'' ?>">
          <span class="var-icon">🐯</span>
          <div><div class="var-name">Fortune Tiger</div><div class="var-sub">50x multiplicador</div></div>
        </a>
        <a href="/jogos/tiger.php?t=rabbit" class="var-btn" style="<?= $theme==='rabbit'?'border-color:#c084fc;background:rgba(192,132,252,.1)':'' ?>">
          <span class="var-icon">🐰</span>
          <div><div class="var-name">Fortune Rabbit</div><div class="var-sub">50x multiplicador</div></div>
        </a>
        <a href="/jogos/tiger.php?t=dragon" class="var-btn" style="<?= $theme==='dragon'?'border-color:#38bdf8;background:rgba(56,189,248,.1)':'' ?>">
          <span class="var-icon">🐉</span>
          <div><div class="var-name">Fortune Dragon</div><div class="var-sub">50x multiplicador</div></div>
        </a>
        <a href="/jogos/crash.php" class="var-btn">
          <span class="var-icon">🚀</span>
          <div><div class="var-name">Aviator / Crash</div><div class="var-sub">Multiplicador ao vivo</div></div>
        </a>
      </div>
    </div>
  </div>
</div>

<script>
const THEME = '<?= $theme ?>';
const symbols = {
  tiger:  [{id:'tiger',l:'🐯',m:50},{id:'dragon',l:'🐉',m:25},{id:'gold',l:'💰',m:12},{id:'gem',l:'💎',m:8},{id:'coin',l:'🪙',m:4},{id:'seven',l:'7',m:2},{id:'bar',l:'BAR',m:1.5}],
  rabbit: [{id:'rabbit',l:'🐰',m:50},{id:'flower',l:'🌸',m:25},{id:'moon',l:'🌙',m:12},{id:'gem',l:'💎',m:8},{id:'coin',l:'🪙',m:4},{id:'seven',l:'7',m:2},{id:'bar',l:'BAR',m:1.5}],
  dragon: [{id:'dragon',l:'🐉',m:50},{id:'fire',l:'🔥',m:25},{id:'pearl',l:'🔮',m:12},{id:'gem',l:'💎',m:8},{id:'coin',l:'🪙',m:4},{id:'seven',l:'7',m:2},{id:'bar',l:'BAR',m:1.5}],
};
const syms = symbols[THEME] || symbols.tiger;

// Paytable
const pt = document.getElementById('paytableRows');
syms.forEach(s => {
  pt.innerHTML += `<div class="pay-row"><span class="pay-sym">${s.l}</span><span class="pay-name">${s.id.charAt(0).toUpperCase()+s.id.slice(1)}</span><span class="pay-mult">${s.m}x</span></div>`;
});

let lines = 3, autoRunning = false, autoLeft = 0;
let stats = {spins:0, wins:0, best:0, net:0};
let history = [];
let spinning = false;

function setBet(v) { document.getElementById('betAmount').value = v.toFixed(2); }
function setLines(n, btn) {
  lines = n;
  document.querySelectorAll('.line-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

function buildStrip(resultCol) {
  const extra = 12;
  let cells = '';
  for (let i = 0; i < extra; i++) {
    const s = syms[Math.floor(Math.random() * syms.length)];
    cells += `<div class="reel-cell ${s.l.length > 2 ? 'text-sym' : ''}">${s.l}</div>`;
  }
  resultCol.forEach(r => {
    cells += `<div class="reel-cell ${r.label.length > 2 ? 'text-sym' : ''}">${r.label}</div>`;
  });
  return cells;
}

async function spin() {
  if (spinning) return;
  spinning = true;
  const btn = document.getElementById('spinBtn');
  btn.disabled = true;
  document.getElementById('winDisplay').innerHTML = '<div class="win-label">Girando...</div>';

  const amount = parseFloat(document.getElementById('betAmount').value);
  const body = new FormData();
  body.append('amount', amount); body.append('lines', lines); body.append('theme', THEME);

  const r = await fetch('/api/games/slot.php', {method:'POST', body});
  const d = await r.json();

  if (!d.success) {
    showToast('❌ ' + d.error, 'red');
    spinning = false; btn.disabled = false; return;
  }

  // Animate reels
  const grid = d.grid; // [row][col] of objects
  for (let col = 0; col < 3; col++) {
    const colData = [grid[0][col], grid[1][col], grid[2][col]];
    const strip = document.getElementById('strip' + col);
    const cellH = 90;
    const extraCells = 12;

    strip.innerHTML = buildStrip(colData);
    strip.style.transition = 'none';
    strip.style.transform = `translateY(${-extraCells * cellH}px)`;
    strip.offsetHeight; // reflow

    const delay = col * 150;
    setTimeout(() => {
      strip.style.transition = `transform ${0.7 + col * 0.1}s cubic-bezier(.22,1,.36,1)`;
      strip.style.transform = `translateY(${-(extraCells - 1) * cellH}px)`;
    }, delay);
  }

  // Show result after animation
  setTimeout(() => {
    showResult(d, amount);
    spinning = false;
    btn.disabled = false;
    if (autoRunning && autoLeft > 0) {
      autoLeft--;
      document.getElementById('spinBtn').textContent = `🎰 AUTO (${autoLeft})`;
      if (autoLeft > 0) setTimeout(spin, 800);
      else stopAuto();
    }
  }, 1200);
}

function showResult(d, amount) {
  const won = d.profit > 0;
  stats.spins++;
  stats.net += d.net;
  if (won) { stats.wins++; if (d.multiplier > stats.best) stats.best = d.multiplier; }

  document.getElementById('statSpins').textContent = stats.spins;
  document.getElementById('statWins').textContent  = stats.wins;
  document.getElementById('statBest').textContent  = stats.best + 'x';
  const netEl = document.getElementById('statNet');
  netEl.textContent = (stats.net >= 0 ? '+' : '') + 'R$' + stats.net.toFixed(2);
  netEl.style.color = stats.net >= 0 ? '#10b981' : '#ef4444';

  const winEl = document.getElementById('winDisplay');
  if (won) {
    winEl.innerHTML = `<div class="win-amount">+R$ ${d.profit.toFixed(2)}</div><div class="win-label">${d.multiplier}x — ${d.wins.length} linha${d.wins.length>1?'s':''} vencedora${d.wins.length>1?'s':''}!</div>`;
    showToast(`🏆 +R$ ${d.profit.toFixed(2)} (${d.multiplier}x)`, 'green');
  } else {
    winEl.innerHTML = '<div class="win-label" style="color:rgba(239,68,68,.7)">Sem sorte desta vez...</div>';
  }

  // History
  history.unshift({won, net: d.net, mult: d.multiplier, sym: d.grid[1][1].label});
  if (history.length > 20) history.pop();
  const hl = document.getElementById('historyList');
  hl.innerHTML = history.map(h =>
    `<div class="hist-item"><span class="hist-sym">${h.sym}</span><span class="${h.won?'hist-won':'hist-lost'}">${h.won?'+':'-'}R$ ${Math.abs(h.net).toFixed(2)}</span><span class="hist-mult" style="color:rgba(255,255,255,.4)">${h.mult}x</span></div>`
  ).join('');

  fetchBalance();
}

function startAuto() {
  const n = parseInt(document.getElementById('autoCount').value);
  if (!n || n < 1) return showToast('Defina o número de giros', 'red');
  autoRunning = true; autoLeft = n;
  document.getElementById('stopAutoBtn').style.display = '';
  document.getElementById('spinBtn').textContent = `🎰 AUTO (${n})`;
  spin();
}
function stopAuto() {
  autoRunning = false; autoLeft = 0;
  document.getElementById('stopAutoBtn').style.display = 'none';
  document.getElementById('spinBtn').textContent = '🎰 GIRAR';
}

async function fetchBalance() {
  try {
    const r = await fetch('/api/get_saldo.php');
    const d = await r.json();
    document.getElementById('balanceDisplay').textContent = 'R$ ' + parseFloat(d.saldo ?? 0).toLocaleString('pt-BR', {minimumFractionDigits:2});
  } catch(e) {}
}

function showToast(msg, color) {
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;top:80px;right:20px;z-index:9999;padding:12px 20px;border-radius:12px;font-weight:700;font-family:Outfit,sans-serif;font-size:.95rem;background:${color==='green'?'#10b981':'#ef4444'};color:#fff;box-shadow:0 8px 30px rgba(0,0,0,.4)`;
  t.textContent = msg; document.body.appendChild(t);
  setTimeout(() => t.style.opacity='0', 2500);
  setTimeout(() => t.remove(), 3000);
}

fetchBalance();
</script>
</body>
</html>
