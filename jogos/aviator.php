<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'Casino';
$isLogged = isset($_SESSION['usuario_id']);
$saldo = 0;
if ($isLogged) {
    $st = $pdo->prepare("SELECT saldo FROM usuarios WHERE id = ? LIMIT 1");
    $st->execute([$_SESSION['usuario_id']]);
    $saldo = $st->fetchColumn() ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Aviator — <?= htmlspecialchars($nomeSite) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#1b1c2b;--panel:#252636;--panel2:#1e1f30;--border:#2e2f45;--red:#ff3d3d;--green:#23d160;--text:#e8e9f0;--muted:#6b7080}
body{background:var(--bg);color:var(--text);font-family:'Montserrat',sans-serif;height:100vh;overflow:hidden;display:flex;flex-direction:column}
a{color:inherit;text-decoration:none}

/* NAV */
.nav{height:52px;background:#131420;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 16px;gap:16px;flex-shrink:0;z-index:10}
.nav-logo{font-size:1.1rem;font-weight:900;color:#ff4655;letter-spacing:.08em}
.nav-logo span{color:#fff}
.nav-sep{width:1px;height:24px;background:var(--border)}
.nav-link{font-size:.8rem;color:var(--muted);font-weight:600;padding:4px 10px;border-radius:6px;transition:.15s}
.nav-link:hover{color:#fff;background:rgba(255,255,255,.05)}
.nav-link.active{color:#fff}
.nav-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.nav-balance{background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:8px;padding:6px 14px;font-size:.8rem;font-weight:700;color:var(--green)}
.nav-deposit{background:var(--red);color:#fff;font-weight:700;border-radius:8px;padding:6px 14px;font-size:.8rem;border:none;cursor:pointer;font-family:inherit}

/* MAIN */
.main{flex:1;display:grid;grid-template-columns:320px 1fr;overflow:hidden}
@media(max-width:800px){.main{grid-template-columns:1fr;grid-template-rows:auto 1fr}}

/* LEFT PANEL */
.left{background:var(--panel);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden}
.bet-tabs{display:flex;border-bottom:1px solid var(--border);flex-shrink:0}
.bet-tab{flex:1;padding:10px;font-size:.75rem;font-weight:700;text-align:center;cursor:pointer;color:var(--muted);border-bottom:2px solid transparent;transition:.15s}
.bet-tab.active{color:#fff;border-bottom-color:var(--red)}
.bet-panel{padding:14px;border-bottom:1px solid var(--border);flex-shrink:0}
.bet-amount-row{display:flex;align-items:center;background:var(--panel2);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:10px}
.bet-amount-row button{width:38px;height:38px;background:none;border:none;color:var(--muted);font-size:1.2rem;cursor:pointer;flex-shrink:0;transition:.15s}
.bet-amount-row button:hover{color:#fff;background:rgba(255,255,255,.05)}
.bet-amount-input{flex:1;background:none;border:none;color:#fff;font-size:1.05rem;font-weight:700;text-align:center;font-family:inherit;outline:none}
.quick-row{display:grid;grid-template-columns:repeat(4,1fr);gap:5px;margin-bottom:10px}
.quick-btn{background:var(--panel2);border:1px solid var(--border);color:var(--muted);border-radius:7px;padding:6px 4px;font-size:.75rem;font-weight:700;cursor:pointer;transition:.15s;font-family:inherit}
.quick-btn:hover{color:#fff;border-color:#555}
.auto-row{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.auto-label{font-size:.75rem;color:var(--muted);white-space:nowrap}
.auto-input{flex:1;background:var(--panel2);border:1px solid var(--border);color:#fff;border-radius:7px;padding:6px 8px;font-size:.8rem;font-family:inherit;outline:none}
.auto-input:focus{border-color:#555}
.main-btn{width:100%;padding:14px;border-radius:10px;border:none;font-size:1rem;font-weight:800;cursor:pointer;font-family:inherit;letter-spacing:.03em;transition:.2s}
.btn-bet{background:linear-gradient(135deg,#ff4655,#c0392b);color:#fff}
.btn-bet:hover{opacity:.9}
.btn-bet:disabled{background:#333;color:#666;cursor:not-allowed}
.btn-cashout{background:linear-gradient(135deg,#23d160,#1a9f4a);color:#fff}
.btn-cashout:hover{opacity:.9}
.btn-cashout:disabled{background:#333;color:#666;cursor:not-allowed}

/* PLAYERS TABLE */
.players-header{padding:10px 14px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);border-bottom:1px solid var(--border);flex-shrink:0;display:flex;justify-content:space-between}
.players-list{overflow-y:auto;flex:1}
.player-row{display:grid;grid-template-columns:1fr 70px 60px 70px;align-items:center;padding:7px 14px;border-bottom:1px solid rgba(255,255,255,.03);font-size:.75rem}
.player-row:hover{background:rgba(255,255,255,.02)}
.p-name{color:var(--muted);font-weight:600}
.p-bet{color:#fff;font-weight:700}
.p-mult{font-weight:700}
.p-profit{font-weight:700;text-align:right}
.p-cashout{color:var(--green)}
.p-flying{color:var(--muted);font-style:italic}

/* GAME AREA */
.game-area{display:flex;flex-direction:column;overflow:hidden;position:relative}
.history-bar{height:38px;background:#131420;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 12px;gap:6px;overflow-x:hidden;flex-shrink:0}
.hist-pill{padding:5px 10px;border-radius:999px;font-size:.72rem;font-weight:800;white-space:nowrap;flex-shrink:0}
.hist-high{background:rgba(35,209,96,.15);color:var(--green);border:1px solid rgba(35,209,96,.3)}
.hist-mid{background:rgba(255,166,0,.15);color:#ffa600;border:1px solid rgba(255,166,0,.3)}
.hist-low{background:rgba(255,61,61,.15);color:var(--red);border:1px solid rgba(255,61,61,.3)}
.canvas-wrap{flex:1;position:relative;overflow:hidden}
canvas{width:100%;height:100%;display:block;background:#0d0e17}
.mult-overlay{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none}
.mult-value{font-size:5rem;font-weight:900;color:#fff;text-shadow:0 0 60px rgba(255,255,255,.3);letter-spacing:-.02em;line-height:1}
.mult-sub{font-size:.9rem;color:rgba(255,255,255,.4);font-weight:600;margin-top:6px;letter-spacing:.05em}
.crash-overlay{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,61,61,.08);opacity:0;pointer-events:none;transition:.3s}
.crash-overlay.show{opacity:1}
.crash-flew{font-size:2rem;font-weight:900;color:var(--red);text-shadow:0 0 30px rgba(255,61,61,.6)}
.crash-mult{font-size:4rem;font-weight:900;color:var(--red);text-shadow:0 0 50px rgba(255,61,61,.5)}
.waiting-overlay{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(13,14,23,.7);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:.3s}
.waiting-overlay.show{opacity:1;pointer-events:all}
.waiting-title{font-size:1.1rem;font-weight:700;color:rgba(255,255,255,.5);margin-bottom:8px}
.waiting-count{font-size:3rem;font-weight:900;color:#fff}
</style>
</head>
<body>
<nav class="nav">
  <div class="nav-logo">AVIA<span>TOR</span></div>
  <div class="nav-sep"></div>
  <a href="/jogos/" class="nav-link">← Lobby</a>
  <a href="/jogos/aviator.php" class="nav-link active">Aviator</a>
  <a href="/jogos/tiger.php?t=tiger" class="nav-link">Fortune Tiger</a>
  <div class="nav-right">
    <div class="nav-balance" id="navBalance">R$ <?= number_format($saldo,2,',','.') ?></div>
    <?php if (!$isLogged): ?><a href="/"><button class="nav-deposit">Entrar</button></a><?php else: ?>
    <button class="nav-deposit" onclick="openDeposit()">+ Depositar</button><?php endif; ?>
  </div>
</nav>

<div class="main">
  <!-- LEFT -->
  <div class="left">
    <div class="bet-tabs">
      <div class="bet-tab active">Aposta 1</div>
      <div class="bet-tab" style="opacity:.4">Aposta 2</div>
    </div>
    <div class="bet-panel">
      <div class="bet-amount-row">
        <button onclick="changeBet(-1)">−</button>
        <input type="number" class="bet-amount-input" id="betAmt" value="5.00" min="0.10" step="0.50">
        <button onclick="changeBet(1)">+</button>
      </div>
      <div class="quick-row">
        <button class="quick-btn" onclick="setBet(1)">1</button>
        <button class="quick-btn" onclick="setBet(5)">5</button>
        <button class="quick-btn" onclick="setBet(10)">10</button>
        <button class="quick-btn" onclick="setBet(50)">50</button>
        <button class="quick-btn" onclick="setBet(100)">100</button>
        <button class="quick-btn" onclick="setBet(200)">200</button>
        <button class="quick-btn" onclick="setBet(500)">500</button>
        <button class="quick-btn" onclick="setBet(0,'x2')">x2</button>
      </div>
      <div class="auto-row">
        <span class="auto-label">Auto Cash Out</span>
        <input type="number" class="auto-input" id="autoCashout" placeholder="ex: 2.00" step="0.10" min="1.01">
      </div>
      <button class="main-btn btn-bet" id="mainBtn" onclick="handleMain()">APOSTAR R$ <span id="btnAmt">5,00</span></button>
    </div>
    <div class="players-header">
      <span>Jogador</span><span>Aposta</span><span>Mult</span><span style="text-align:right">Ganho</span>
    </div>
    <div class="players-list" id="playersList"></div>
  </div>

  <!-- GAME -->
  <div class="game-area">
    <div class="history-bar" id="histBar"></div>
    <div class="canvas-wrap">
      <canvas id="gc"></canvas>
      <div class="mult-overlay" id="multOverlay">
        <div class="mult-value" id="multVal">1.00<span style="font-size:3rem">x</span></div>
        <div class="mult-sub" id="multSub">Fazendo apostas...</div>
      </div>
      <div class="crash-overlay" id="crashOvl">
        <div class="crash-flew">VOOU EMBORA!</div>
        <div class="crash-mult" id="crashMult">0.00x</div>
      </div>
      <div class="waiting-overlay" id="waitOvl">
        <div class="waiting-title">Próxima rodada em</div>
        <div class="waiting-count" id="waitCount">5</div>
      </div>
    </div>
  </div>
</div>

<script>
// ── State ───────────────────────────────────────────────────────────
let state = {status:'waiting', mult:1, crashed:false, bet:false, betAmt:0, roundId:null};
let history = [];
let fakePlayers = [];
let points = [];
let startTime = 0;
let crashPoint = 0;
let animId = null;
let planeSmokeParticles = [];

// ── Canvas setup ────────────────────────────────────────────────────
const canvas = document.getElementById('gc');
const ctx = canvas.getContext('2d');
function resize() {
    canvas.width  = canvas.offsetWidth  * window.devicePixelRatio;
    canvas.height = canvas.offsetHeight * window.devicePixelRatio;
    ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
}
window.addEventListener('resize', () => { resize(); });
resize();
const W = () => canvas.offsetWidth;
const H = () => canvas.offsetHeight;

// ── Drawing ─────────────────────────────────────────────────────────
function drawFrame(mult, crashed) {
    const w = W(), h = H();
    ctx.clearRect(0, 0, w, h);

    // bg
    ctx.fillStyle = '#0d0e17';
    ctx.fillRect(0, 0, w, h);

    // grid
    ctx.strokeStyle = 'rgba(255,255,255,0.04)';
    ctx.lineWidth = 1;
    for (let x = 0; x < w; x += w/8) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,h); ctx.stroke(); }
    for (let y = 0; y < h; y += h/5) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(w,y); ctx.stroke(); }

    if (points.length < 2) return;

    const pad = {l:60, b:40, r:20, t:20};
    const gw = w - pad.l - pad.r;
    const gh = h - pad.t - pad.b;

    // Scale
    const maxMult = Math.max(mult * 1.2, 2);
    const elapsed = (Date.now() - startTime) / 1000;
    const maxTime = Math.max(elapsed * 1.15, 5);

    function px(t, m) {
        return pad.l + (t / maxTime) * gw;
    }
    function py(t, m) {
        const logM = Math.log(m);
        const logMax = Math.log(maxMult);
        return h - pad.b - (logM / logMax) * gh;
    }

    // Draw fill
    const grad = ctx.createLinearGradient(0, 0, 0, h);
    grad.addColorStop(0, crashed ? 'rgba(255,50,50,0.3)' : 'rgba(35,209,96,0.3)');
    grad.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.beginPath();
    ctx.moveTo(px(0, 1), h - pad.b);
    points.forEach(p => ctx.lineTo(px(p.t, p.m), py(p.t, p.m)));
    ctx.lineTo(px(points[points.length-1].t, 1), h - pad.b);
    ctx.closePath();
    ctx.fillStyle = grad;
    ctx.fill();

    // Draw line
    ctx.beginPath();
    points.forEach((p, i) => {
        if (i === 0) ctx.moveTo(px(p.t, p.m), py(p.t, p.m));
        else ctx.lineTo(px(p.t, p.m), py(p.t, p.m));
    });
    ctx.strokeStyle = crashed ? '#ff3d3d' : '#23d160';
    ctx.lineWidth = 3;
    ctx.lineJoin = 'round';
    ctx.stroke();

    // Glow on line tip
    if (!crashed) {
        const lp = points[points.length-1];
        const lx = px(lp.t, lp.m), ly = py(lp.t, lp.m);
        const glow = ctx.createRadialGradient(lx, ly, 0, lx, ly, 30);
        glow.addColorStop(0, 'rgba(35,209,96,0.6)');
        glow.addColorStop(1, 'rgba(35,209,96,0)');
        ctx.beginPath(); ctx.arc(lx, ly, 30, 0, Math.PI*2);
        ctx.fillStyle = glow; ctx.fill();
    }

    // Draw plane
    if (!crashed && points.length >= 2) {
        const lp = points[points.length-1];
        const pp = points[Math.max(0, points.length-3)];
        const lx = px(lp.t, lp.m), ly = py(lp.t, lp.m);
        const ppx2 = px(pp.t, pp.m), ppy2 = py(pp.t, pp.m);
        const angle = Math.atan2(ly - ppy2, lx - ppx2);
        drawPlane(ctx, lx, ly, angle);
    }
}

function drawPlane(ctx, x, y, angle) {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(angle - Math.PI / 2);
    ctx.shadowColor = '#fff';
    ctx.shadowBlur = 12;

    const sc = 1.1;
    ctx.scale(sc, sc);

    // Body
    ctx.fillStyle = '#e8e9f0';
    ctx.beginPath();
    ctx.ellipse(0, 4, 7, 18, 0, 0, Math.PI * 2);
    ctx.fill();

    // Nose
    ctx.beginPath();
    ctx.moveTo(-4, -13); ctx.lineTo(4, -13); ctx.lineTo(0, -22);
    ctx.closePath(); ctx.fill();

    // Wings
    ctx.fillStyle = '#c0c2d0';
    ctx.beginPath();
    ctx.moveTo(-5, 2); ctx.lineTo(-22, 10); ctx.lineTo(-18, 14); ctx.lineTo(-5, 8);
    ctx.closePath(); ctx.fill();
    ctx.beginPath();
    ctx.moveTo(5, 2); ctx.lineTo(22, 10); ctx.lineTo(18, 14); ctx.lineTo(5, 8);
    ctx.closePath(); ctx.fill();

    // Tail
    ctx.beginPath();
    ctx.moveTo(-4, 14); ctx.lineTo(-12, 22); ctx.lineTo(-8, 22); ctx.lineTo(-2, 16);
    ctx.closePath(); ctx.fill();
    ctx.beginPath();
    ctx.moveTo(4, 14); ctx.lineTo(12, 22); ctx.lineTo(8, 22); ctx.lineTo(2, 16);
    ctx.closePath(); ctx.fill();

    // Engine accent
    ctx.fillStyle = '#ff4655';
    ctx.beginPath(); ctx.arc(0, 6, 3.5, 0, Math.PI * 2); ctx.fill();

    ctx.restore();
}

// ── Animation loop ──────────────────────────────────────────────────
function animate() {
    if (state.status === 'running') {
        const elapsed = (Date.now() - startTime) / 1000;
        const m = Math.pow(Math.E, 0.07 * elapsed);
        state.mult = m;
        points.push({t: elapsed, m});
        if (points.length > 500) points = points.slice(-500);

        drawFrame(m, false);

        document.getElementById('multVal').innerHTML = m.toFixed(2) + '<span style="font-size:3rem">x</span>';
        document.getElementById('multSub').textContent = 'Voando!';
        document.getElementById('multVal').style.color = '#fff';

        // Auto cashout
        const autoCO = parseFloat(document.getElementById('autoCashout').value);
        if (state.bet && autoCO >= 1.01 && m >= autoCO) cashOut();
    }
    animId = requestAnimationFrame(animate);
}

// ── API polling ─────────────────────────────────────────────────────
async function poll() {
    try {
        const r = await fetch('/api/games/crash.php?action=status');
        const d = await r.json();
        if (!d.round) return;

        const prev = state.status;

        if (d.round.status === 'waiting') {
            state.status = 'waiting';
            const tl = Math.max(0, Math.round((d.round.time_left_ms || 5000) / 1000));
            document.getElementById('waitCount').textContent = tl;
            document.getElementById('waitOvl').classList.add('show');
            document.getElementById('crashOvl').classList.remove('show');
            document.getElementById('multSub').textContent = 'Fazendo apostas...';
            document.getElementById('multVal').innerHTML = '1.00<span style="font-size:3rem">x</span>';
            document.getElementById('multVal').style.color = 'rgba(255,255,255,0.3)';
            if (prev !== 'waiting') { points = []; drawFrame(1, false); }
            if (d.round.id !== state.roundId) { state.roundId = d.round.id; state.bet = false; updateBtn(); }
        }

        if (d.round.status === 'running') {
            state.status = 'running';
            document.getElementById('waitOvl').classList.remove('show');
            document.getElementById('crashOvl').classList.remove('show');
            if (prev !== 'running') { startTime = Date.now() - (d.round.elapsed_ms || 0); points = []; }
        }

        if (d.round.status === 'crashed') {
            if (state.status !== 'crashed') {
                state.status = 'crashed';
                const cp = parseFloat(d.round.crash_point || d.round.current_multiplier);
                document.getElementById('crashMult').textContent = cp.toFixed(2) + 'x';
                document.getElementById('crashOvl').classList.add('show');
                document.getElementById('multSub').textContent = 'Voou embora!';
                addHistory(cp);
                state.bet = false; updateBtn();
                drawFrame(cp, true);
                setTimeout(() => { points = []; }, 3000);
            }
        }
    } catch(e) {}
}

// ── Fake players list ────────────────────────────────────────────────
const fakeNames = ['Lucas S.','Ana P.','Carlos R.','Beatriz','Felipe','Maria L.','João V.','Gabriel','Camila','Pedro H.','Thiago','Luiza','Rafael','Juliana','Marcos'];
function initFakePlayers() {
    fakePlayers = fakeNames.map(n => ({
        name: n,
        bet: (Math.random() * 195 + 5).toFixed(2),
        mult: null,
        profit: null,
    }));
    renderPlayers();
}
function cashOutFakePlayers(crashedAt) {
    fakePlayers.forEach(p => {
        if (!p.mult) {
            const survived = Math.random() < 0.45;
            if (survived) {
                p.mult = (1.01 + Math.random() * (crashedAt - 1.02)).toFixed(2);
                p.profit = (p.bet * p.mult).toFixed(2);
            } else {
                p.mult = null; p.profit = '-' + p.bet;
            }
        }
    });
    renderPlayers();
}
function renderPlayers() {
    document.getElementById('playersList').innerHTML = fakePlayers.map(p => `
        <div class="player-row">
          <span class="p-name">${p.name}</span>
          <span class="p-bet">R$ ${p.bet}</span>
          <span class="p-mult ${p.mult ? 'p-cashout' : 'p-flying'}">${p.mult ? p.mult + 'x' : '🛩'}</span>
          <span class="p-profit" style="color:${p.profit && p.profit[0] !== '-' ? '#23d160' : '#ff3d3d'};text-align:right">${p.profit ? 'R$ ' + p.profit : ''}</span>
        </div>`).join('');
}

// ── History ──────────────────────────────────────────────────────────
function addHistory(m) {
    history.unshift(m);
    if (history.length > 20) history.pop();
    const bar = document.getElementById('histBar');
    bar.innerHTML = history.map(v => {
        const cls = v >= 10 ? 'hist-high' : v >= 2 ? 'hist-mid' : 'hist-low';
        return `<span class="hist-pill ${cls}">${v.toFixed(2)}x</span>`;
    }).join('');
}

// ── Bet controls ─────────────────────────────────────────────────────
function setBet(v, mod) {
    const cur = parseFloat(document.getElementById('betAmt').value) || 0;
    document.getElementById('betAmt').value = mod === 'x2' ? (cur * 2).toFixed(2) : parseFloat(v).toFixed(2);
    updateBtnLabel();
}
function changeBet(dir) {
    const cur = parseFloat(document.getElementById('betAmt').value) || 0;
    document.getElementById('betAmt').value = Math.max(0.10, cur + dir).toFixed(2);
    updateBtnLabel();
}
document.getElementById('betAmt').addEventListener('input', updateBtnLabel);
function updateBtnLabel() {
    const v = parseFloat(document.getElementById('betAmt').value) || 0;
    document.getElementById('btnAmt').textContent = v.toLocaleString('pt-BR', {minimumFractionDigits:2});
}
updateBtnLabel();

function updateBtn() {
    const btn = document.getElementById('mainBtn');
    if (state.bet) {
        btn.className = 'main-btn btn-cashout';
        const profit = (state.betAmt * state.mult).toFixed(2);
        btn.innerHTML = `RETIRAR R$ ${parseFloat(profit).toLocaleString('pt-BR',{minimumFractionDigits:2})}`;
        btn.disabled = state.status !== 'running';
    } else {
        btn.className = 'main-btn btn-bet';
        btn.innerHTML = 'APOSTAR R$ <span id="btnAmt">'+parseFloat(document.getElementById('betAmt').value).toLocaleString('pt-BR',{minimumFractionDigits:2})+'</span>';
        btn.disabled = state.status === 'running';
    }
}

async function handleMain() {
    if (state.bet) { cashOut(); return; }
    if (state.status === 'running') return;
    const amt = parseFloat(document.getElementById('betAmt').value);
    if (!amt || amt < 0.10) return alert('Aposta mínima: R$ 0,10');

    const fd = new FormData();
    fd.append('action', 'bet'); fd.append('amount', amt);
    const r = await fetch('/api/games/crash.php', {method:'POST', body:fd});
    const d = await r.json();
    if (!d.success) return showToast('❌ ' + d.error, 'red');
    state.bet = true; state.betAmt = amt;
    showToast('✅ Aposta de R$ ' + amt.toFixed(2) + ' confirmada!', 'green');
    document.getElementById('navBalance').textContent = 'R$ ' + parseFloat(d.balance ?? 0).toLocaleString('pt-BR',{minimumFractionDigits:2});
    updateBtn();
}

async function cashOut() {
    if (!state.bet) return;
    const fd = new FormData();
    fd.append('action', 'cashout');
    const r = await fetch('/api/games/crash.php', {method:'POST', body:fd});
    const d = await r.json();
    if (!d.success) return showToast('❌ ' + d.error, 'red');
    state.bet = false;
    showToast(`🏆 Retirada: R$ ${parseFloat(d.profit).toLocaleString('pt-BR',{minimumFractionDigits:2})} (${d.multiplier}x)`, 'green');
    if (d.balance !== undefined) document.getElementById('navBalance').textContent = 'R$ ' + parseFloat(d.balance).toLocaleString('pt-BR',{minimumFractionDigits:2});
    updateBtn();
}

function openDeposit() { window.location.href = '/'; }

function showToast(msg, color) {
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;top:70px;right:16px;z-index:9999;padding:12px 20px;border-radius:10px;font-weight:700;font-family:Montserrat,sans-serif;font-size:.9rem;background:${color==='green'?'#23d160':'#ff3d3d'};color:#fff;box-shadow:0 8px 30px rgba(0,0,0,.5);transition:opacity .4s`;
    t.textContent = msg; document.body.appendChild(t);
    setTimeout(() => t.style.opacity = '0', 2500);
    setTimeout(() => t.remove(), 3000);
}

// ── Init ─────────────────────────────────────────────────────────────
initFakePlayers();
animate();
setInterval(poll, 600);
setInterval(() => {
    if (state.status === 'crashed') cashOutFakePlayers(parseFloat(document.getElementById('crashMult').textContent));
    else if (state.status === 'waiting') initFakePlayers();
    updateBtn();
}, 3000);
poll();
</script>
</body>
</html>
