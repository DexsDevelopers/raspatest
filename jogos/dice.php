<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'RaspaPix';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dice — 🍀 RaspaPix</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#0d0d1a; color:#fff; font-family:'Inter',sans-serif; }
.panel { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:16px; }
.btn-primary { background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; font-weight:700; border-radius:10px; padding:12px; width:100%; transition:all .2s; }
.btn-under { background:linear-gradient(135deg,#3b82f6,#1d4ed8); }
.btn-over  { background:linear-gradient(135deg,#ef4444,#b91c1c); }
.input-field { background:#0d0d1a; border:1px solid #374151; color:#fff; border-radius:8px; padding:10px 14px; width:100%; }
.rp-nav{height:50px;background:#0a0a14;border-bottom:1px solid #1a1a2e;display:flex;align-items:center;padding:0 18px;gap:12px}
.rp-logo{font-size:.95rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:4px;text-decoration:none}
.rp-logo .pix{color:#22c55e}
.rp-back{font-size:.8rem;color:#6b7280;font-weight:600;text-decoration:none;padding:4px 8px;border-radius:6px}
.rp-back:hover{color:#fff;background:rgba(255,255,255,.06)}
.result-num { font-size:5rem; font-weight:900; text-align:center; transition: color .3s; }
input[type=range]::-webkit-slider-thumb { width:20px; height:20px; background:#7c3aed; }
input[type=range] { accent-color: #7c3aed; }
</style>
</head>
<body>
<nav class="rp-nav">
  <a href="/jogos/" class="rp-logo">🍀 RASPA<span class="pix">PIX</span></a>
  <span style="color:#1e1e35">|</span>
  <a href="/jogos/" class="rp-back">← Lobby</a>
</nav>
<div class="max-w-2xl mx-auto px-4 py-6">
  <div class="flex items-center gap-3 mb-6">
    <a href="/jogos/" class="text-gray-400 hover:text-white"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-2xl font-bold">🎲 Dice</h1>
  </div>

  <div class="panel p-6 flex flex-col gap-6">
    <!-- Result display -->
    <div id="resultNum" class="result-num text-gray-500">-</div>

    <!-- Slider bar -->
    <div class="relative pt-2">
      <div class="relative h-4 rounded-full overflow-hidden">
        <div id="underZone" class="absolute inset-y-0 left-0 bg-blue-600 rounded-l-full transition-all" style="width:50%"></div>
        <div id="overZone"  class="absolute inset-y-0 right-0 bg-red-600 rounded-r-full transition-all" style="width:50%"></div>
        <div id="targetMarker" class="absolute inset-y-0 w-1 bg-white" style="left:50%"></div>
        <div id="rolledMarker" class="absolute inset-y-0 w-2 bg-yellow-400 rounded hidden transition-all"></div>
      </div>
      <input type="range" id="targetSlider" min="2" max="98" value="50" class="w-full mt-2" oninput="updateTarget()">
      <div class="flex justify-between text-xs text-gray-500 mt-1"><span>0</span><span>50</span><span>100</span></div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-3 gap-4 text-center">
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="text-xs text-gray-400 mb-1">Chance</div>
        <div id="chanceVal" class="text-lg font-bold text-blue-400">50.00%</div>
      </div>
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="text-xs text-gray-400 mb-1">Multiplicador</div>
        <div id="multVal" class="text-lg font-bold text-yellow-400">1.98x</div>
      </div>
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="text-xs text-gray-400 mb-1">Ganho</div>
        <div id="profitVal" class="text-lg font-bold text-green-400">R$ 9.90</div>
      </div>
    </div>

    <!-- Controls -->
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Valor (R$)</label>
        <input type="number" id="betAmount" class="input-field" value="5.00" min="0.10" step="0.50" oninput="updateTarget()">
        <div class="grid grid-cols-4 gap-1 mt-2">
          <?php foreach([1,5,10,50] as $v): ?>
          <button onclick="setBet(<?=$v?>)" class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1"><?=$v?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Alvo</label>
        <input type="number" id="targetInput" class="input-field" value="50" min="2" max="98" oninput="syncSlider()">
        <div class="grid grid-cols-2 gap-1 mt-2">
          <button onclick="setTarget(25)" class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1">25</button>
          <button onclick="setTarget(75)" class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1">75</button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <button class="btn-primary btn-under" onclick="roll('under')">⬇ Abaixo de <span id="underLabel">50</span></button>
      <button class="btn-primary btn-over"  onclick="roll('over')">⬆ Acima de <span id="overLabel">50</span></button>
    </div>

    <!-- History -->
    <div>
      <p class="text-xs text-gray-400 mb-2">Últimas rodadas</p>
      <div id="history" class="flex flex-wrap gap-1"></div>
    </div>
  </div>
</div>

<script>
let history = [];
function setBet(v) { document.getElementById('betAmount').value = v; updateTarget(); }
function setTarget(v) { document.getElementById('targetSlider').value = v; document.getElementById('targetInput').value = v; updateTarget(); }
function syncSlider() { document.getElementById('targetSlider').value = document.getElementById('targetInput').value; updateTarget(); }

function updateTarget() {
  const t = parseFloat(document.getElementById('targetSlider').value);
  document.getElementById('targetInput').value = t;
  document.getElementById('underLabel').textContent = t;
  document.getElementById('overLabel').textContent  = t;
  document.getElementById('targetMarker').style.left = t + '%';
  document.getElementById('underZone').style.width   = t + '%';
  document.getElementById('overZone').style.width    = (100 - t) + '%';

  const chance = t / 100;
  const mult   = (0.99 / chance).toFixed(4);
  const bet    = parseFloat(document.getElementById('betAmount').value) || 0;

  document.getElementById('chanceVal').textContent  = (chance * 100).toFixed(2) + '%';
  document.getElementById('multVal').textContent    = mult + 'x';
  document.getElementById('profitVal').textContent  = 'R$ ' + (bet * mult).toFixed(2);
}

async function roll(direction) {
  const amount = document.getElementById('betAmount').value;
  const target = document.getElementById('targetSlider').value;
  const body = new FormData();
  body.append('amount', amount); body.append('target', target); body.append('direction', direction);

  const r = await fetch('/api/games/dice.php', {method:'POST', body});
  const d = await r.json();
  if (!d.success) { showToast('❌ ' + d.error, 'red'); return; }

  const resultEl = document.getElementById('resultNum');
  resultEl.textContent = d.rolled.toFixed(2);
  resultEl.style.color = d.won ? '#10b981' : '#ef4444';

  // Show rolled marker on bar
  const marker = document.getElementById('rolledMarker');
  marker.classList.remove('hidden');
  marker.style.left = d.rolled + '%';

  history.unshift({ rolled: d.rolled, won: d.won });
  if (history.length > 12) history.pop();
  document.getElementById('history').innerHTML = history.map(h =>
    `<span class="px-2 py-0.5 rounded text-xs font-bold ${h.won?'bg-green-900 text-green-300':'bg-red-900 text-red-300'}">${h.rolled.toFixed(1)}</span>`
  ).join('');

  if (d.won) showToast(`✅ Ganhou! ${d.multiplier}x — +R$ ${(d.profit - amount).toFixed(2)}`, 'green');
  else showToast(`❌ Perdeu! Resultado: ${d.rolled.toFixed(2)}`, 'red');
}

function showToast(msg, color='green') {
  const t = document.createElement('div');
  t.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl font-bold text-white ${color==='green'?'bg-green-600':'bg-red-600'}`;
  t.textContent = msg; document.body.appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}

updateTarget();
</script>
</body>
</html>
