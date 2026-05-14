<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'RaspaPix';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Plinko — 🍀 RaspaPix</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#0d0d1a; color:#fff; font-family:'Inter',sans-serif; }
.panel { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:16px; }
.btn-primary { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-weight:700; border-radius:10px; padding:12px; width:100%; transition:all .2s; }
.input-field { background:#0d0d1a; border:1px solid #374151; color:#fff; border-radius:8px; padding:10px 14px; width:100%; }
.rp-nav{height:50px;background:#0a0a14;border-bottom:1px solid #1a1a2e;display:flex;align-items:center;padding:0 18px;gap:12px}
.rp-logo{font-size:.95rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:4px;text-decoration:none}
.rp-logo .pix{color:#22c55e}
.rp-back{font-size:.8rem;color:#6b7280;font-weight:600;text-decoration:none;padding:4px 8px;border-radius:6px}
.rp-back:hover{color:#fff;background:rgba(255,255,255,.06)}
canvas { display:block; margin:0 auto; }
.result-flash { animation: flash .6s ease; }
@keyframes flash { 0%,100%{opacity:1} 50%{opacity:.3} }
</style>
</head>
<body>
<nav class="rp-nav">
  <a href="/jogos/" class="rp-logo">🍀 RASPA<span class="pix">PIX</span></a>
  <span style="color:#1e1e35">|</span>
  <a href="/jogos/" class="rp-back">← Lobby</a>
</nav>
<div class="max-w-4xl mx-auto px-4 py-6">
  <div class="flex items-center gap-3 mb-6">
    <a href="/jogos/" class="text-gray-400 hover:text-white"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-2xl font-bold">🔴 Plinko</h1>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Canvas -->
    <div class="lg:col-span-2 panel p-4">
      <canvas id="plinkoCanvas" width="420" height="460"></canvas>
      <div id="resultMsg" class="text-center mt-3 text-xl font-bold hidden"></div>
    </div>

    <!-- Controls -->
    <div class="panel p-4 flex flex-col gap-4">
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Valor (R$)</label>
        <input type="number" id="betAmount" class="input-field" value="5.00" min="0.10" step="0.50">
        <div class="grid grid-cols-4 gap-1 mt-2">
          <?php foreach([1,5,10,50] as $v): ?>
          <button onclick="setBet(<?=$v?>)" class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1"><?=$v?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Linhas</label>
        <select id="rowsSelect" class="input-field" onchange="drawBoard()">
          <option value="8">8 linhas</option>
          <option value="12">12 linhas</option>
          <option value="16">16 linhas</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Risco</label>
        <select id="riskSelect" class="input-field" onchange="drawBoard()">
          <option value="low">Baixo</option>
          <option value="medium" selected>Médio</option>
          <option value="high">Alto</option>
        </select>
      </div>
      <button id="dropBtn" class="btn-primary" onclick="dropBall()">🔴 Soltar</button>

      <div id="statsBox" class="bg-gray-800 rounded-xl p-3 text-sm space-y-1 hidden">
        <div class="flex justify-between"><span class="text-gray-400">Multiplicador</span><span id="statMult" class="text-yellow-400">-</span></div>
        <div class="flex justify-between"><span class="text-gray-400">Retorno</span><span id="statProfit">-</span></div>
      </div>

      <!-- Last 10 results -->
      <div>
        <p class="text-xs text-gray-400 mb-2">Últimos resultados</p>
        <div id="lastResults" class="flex flex-wrap gap-1"></div>
      </div>
    </div>
  </div>
</div>

<script>
const canvas = document.getElementById('plinkoCanvas');
const ctx = canvas.getContext('2d');
const pegRadius = 5, ballRadius = 8;
let multipliers = [], lastResults = [];

function setBet(v) { document.getElementById('betAmount').value = v; }

function getRows() { return parseInt(document.getElementById('rowsSelect').value); }
function getRisk() { return document.getElementById('riskSelect').value; }

function getMultiplierColor(mult) {
  if (mult >= 10) return '#ef4444';
  if (mult >= 3)  return '#f59e0b';
  if (mult >= 1.5) return '#10b981';
  return '#6b7280';
}

function drawBoard(highlightPos = null, mult = null) {
  const rows = getRows();
  const W = canvas.width, H = canvas.height;
  ctx.clearRect(0, 0, W, H);

  const topPad = 40, botPad = 60;
  const usableH = H - topPad - botPad;
  const rowH = usableH / rows;

  // Draw pegs
  for (let row = 0; row < rows; row++) {
    const pegsInRow = row + 2;
    const rowW = W * 0.8;
    const startX = (W - rowW * (pegsInRow - 1) / (rows + 1)) / 2;
    const y = topPad + row * rowH + rowH / 2;
    for (let p = 0; p < pegsInRow; p++) {
      const x = W / 2 + (p - (pegsInRow - 1) / 2) * (rowW / (rows + 1));
      ctx.beginPath(); ctx.arc(x, y, pegRadius, 0, Math.PI * 2);
      ctx.fillStyle = '#4b5563'; ctx.fill();
    }
  }

  // Draw multiplier slots
  const slotCount = rows + 1;
  const slotW = W / (slotCount + 0.5);
  for (let i = 0; i < slotCount; i++) {
    const x = (W / slotCount) * i + slotW * 0.25;
    const y = H - botPad + 4;
    const m = multipliers[i] ?? 1;
    const color = getMultiplierColor(m);
    const isHit = highlightPos === i;

    ctx.fillStyle = isHit ? color : color + '44';
    ctx.beginPath(); ctx.roundRect(x, y, slotW * 0.9, 36, 6); ctx.fill();

    ctx.fillStyle = isHit ? '#fff' : color;
    ctx.font = isHit ? 'bold 13px Inter' : '11px Inter';
    ctx.textAlign = 'center';
    ctx.fillText(m + 'x', x + slotW * 0.45, y + 24);
  }
}

async function animateBall(path, endPos, onDone) {
  const rows = getRows();
  const W = canvas.width, H = canvas.height;
  const topPad = 40, botPad = 60;
  const usableH = H - topPad - botPad;
  const rowH = usableH / rows;
  const rowW = W * 0.8;

  let ballX = W / 2, ballY = topPad - ballRadius;
  let step = 0;

  function frame() {
    drawBoard();
    if (step >= path.length) {
      // Final position
      const slotCount = rows + 1;
      const slotW = W / (slotCount + 0.5);
      const finalX = (W / slotCount) * endPos + slotW * 0.7;
      ctx.beginPath(); ctx.arc(finalX, H - botPad - ballRadius - 4, ballRadius, 0, Math.PI * 2);
      ctx.fillStyle = '#ef4444'; ctx.fill();
      drawBoard(endPos);
      onDone(); return;
    }

    const targetRow = step;
    const pegsInRow  = targetRow + 2;
    const dir        = path[step] === 'R' ? 1 : -1;
    const targetX    = W / 2 + (endPos - (rows) / 2 - (path.length - step - 1) * 0.5 * dir) * (rowW / (rows + 1));
    const targetY    = topPad + targetRow * rowH + rowH / 2;

    ballX += (targetX - ballX) * 0.3;
    ballY += (targetY - ballY) * 0.3;

    if (Math.abs(ballY - targetY) < 2) step++;

    ctx.beginPath(); ctx.arc(ballX, ballY, ballRadius, 0, Math.PI * 2);
    ctx.fillStyle = '#ef4444'; ctx.shadowColor='#ef4444'; ctx.shadowBlur=10; ctx.fill();
    ctx.shadowBlur = 0;
    requestAnimationFrame(frame);
  }
  requestAnimationFrame(frame);
}

async function dropBall() {
  const amount = document.getElementById('betAmount').value;
  const rows   = getRows();
  const risk   = getRisk();

  document.getElementById('dropBtn').disabled = true;
  document.getElementById('dropBtn').textContent = '⏳ Aguarde...';

  const body = new FormData();
  body.append('amount', amount); body.append('rows', rows); body.append('risk', risk);
  const r = await fetch('/api/games/plinko.php', {method:'POST', body});
  const d = await r.json();

  document.getElementById('dropBtn').disabled = false;
  document.getElementById('dropBtn').textContent = '🔴 Soltar';

  if (!d.success) { showToast('❌ ' + d.error, 'red'); return; }

  multipliers = d.multipliers;
  const won = d.net >= 0;

  animateBall(d.path, d.position, () => {
    const resultEl = document.getElementById('resultMsg');
    resultEl.classList.remove('hidden');
    resultEl.className = `text-center mt-3 text-xl font-bold result-flash ${won?'text-green-400':'text-red-400'}`;
    resultEl.textContent = `${d.multiplier}x — ${won?'+':''}R$ ${d.net.toFixed(2)}`;

    document.getElementById('statsBox').classList.remove('hidden');
    document.getElementById('statMult').textContent = d.multiplier + 'x';
    document.getElementById('statProfit').textContent = (d.net>=0?'+':'') + 'R$ ' + d.net.toFixed(2);
    document.getElementById('statProfit').className = d.net >= 0 ? 'text-green-400' : 'text-red-400';

    lastResults.unshift(d.multiplier);
    if (lastResults.length > 10) lastResults.pop();
    const lr = document.getElementById('lastResults');
    lr.innerHTML = lastResults.map(m => `<span class="px-2 py-0.5 rounded text-xs font-bold" style="background:${getMultiplierColor(m)}33;color:${getMultiplierColor(m)}">${m}x</span>`).join('');
  });
}

function showToast(msg, color='green') {
  const t = document.createElement('div');
  t.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl font-bold text-white ${color==='green'?'bg-green-600':'bg-red-600'}`;
  t.textContent = msg; document.body.appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}

// Init
const defaultMults = [5.6,2.1,1.1,1.0,0.5,1.0,1.1,2.1,5.6];
multipliers = defaultMults;
drawBoard();
</script>
</body>
</html>
