<?php require_once __DIR__ . '/../inc/header.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Limbo — <?= $nomeSite ?? 'Casino' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#0d0d1a; color:#fff; font-family:'Inter',sans-serif; }
.panel { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:16px; }
.btn-primary { background:linear-gradient(135deg,#7c3aed,#4f46e5); color:#fff; font-weight:700; border-radius:10px; padding:14px; width:100%; font-size:1.1rem; transition:all .2s; }
.btn-primary:hover { opacity:.9; }
.input-field { background:#0d0d1a; border:1px solid #374151; color:#fff; border-radius:8px; padding:10px 14px; width:100%; }
.big-result { font-size:6rem; font-weight:900; text-align:center; transition: all .4s; }
.spin-anim { animation: spin-count .6s ease; }
@keyframes spin-count { 0%{transform:scale(.8); opacity:.5} 50%{transform:scale(1.1);opacity:1} 100%{transform:scale(1);opacity:1} }
.bar-container { height:10px; background:#1f2937; border-radius:999px; overflow:hidden; }
.bar-fill { height:100%; border-radius:999px; transition:width .5s; }
</style>
</head>
<body>
<div class="max-w-xl mx-auto px-4 py-6">
  <div class="flex items-center gap-3 mb-6">
    <a href="/jogos/" class="text-gray-400 hover:text-white"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-2xl font-bold">🌀 Limbo</h1>
  </div>

  <div class="panel p-6 flex flex-col gap-6">
    <!-- Big result -->
    <div>
      <div id="bigResult" class="big-result text-gray-500">-</div>
      <div id="resultMsg" class="text-center text-sm text-gray-400 mt-1">Defina seu alvo e aposte</div>
    </div>

    <!-- Target vs Result bar -->
    <div class="hidden" id="barSection">
      <div class="flex justify-between text-xs text-gray-400 mb-1">
        <span>Alvo: <span id="barTarget" class="text-white font-bold">2.00x</span></span>
        <span>Resultado: <span id="barResult" class="text-white font-bold">-</span></span>
      </div>
      <div class="bar-container">
        <div id="barFill" class="bar-fill bg-gray-500" style="width:0%"></div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-3 text-center">
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="text-xs text-gray-400 mb-1">Chance</div>
        <div id="chanceVal" class="text-base font-bold text-blue-400">49.5%</div>
      </div>
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="text-xs text-gray-400 mb-1">Multiplicador</div>
        <div id="multVal" class="text-base font-bold text-yellow-400">2.00x</div>
      </div>
      <div class="bg-gray-800 rounded-xl p-3">
        <div class="text-xs text-gray-400 mb-1">Ganho</div>
        <div id="profitVal" class="text-base font-bold text-green-400">R$ 10.00</div>
      </div>
    </div>

    <!-- Inputs -->
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Valor (R$)</label>
        <input type="number" id="betAmount" class="input-field" value="5.00" min="0.10" step="0.50" oninput="updateStats()">
        <div class="grid grid-cols-4 gap-1 mt-2">
          <?php foreach([1,5,10,50] as $v): ?>
          <button onclick="setBet(<?=$v?>)" class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1"><?=$v?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Alvo (x)</label>
        <input type="number" id="targetInput" class="input-field" value="2.00" min="1.01" max="1000" step="0.01" oninput="updateStats()">
        <div class="grid grid-cols-2 gap-1 mt-2">
          <button onclick="setTarget(1.5)"  class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1">1.5x</button>
          <button onclick="setTarget(2)"    class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1">2x</button>
          <button onclick="setTarget(5)"    class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1">5x</button>
          <button onclick="setTarget(100)"  class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1">100x</button>
        </div>
      </div>
    </div>

    <button id="spinBtn" class="btn-primary" onclick="spinLimbo()">🎯 Apostar</button>

    <!-- History -->
    <div>
      <p class="text-xs text-gray-400 mb-2">Últimos resultados</p>
      <div id="history" class="flex flex-wrap gap-1"></div>
    </div>
  </div>
</div>

<script>
let history = [];
function setBet(v) { document.getElementById('betAmount').value = v; updateStats(); }
function setTarget(v) { document.getElementById('targetInput').value = v; updateStats(); }

function updateStats() {
  const target = parseFloat(document.getElementById('targetInput').value) || 2;
  const bet    = parseFloat(document.getElementById('betAmount').value) || 0;
  const chance = Math.min(99, (0.99 / target) * 100);

  document.getElementById('chanceVal').textContent  = chance.toFixed(2) + '%';
  document.getElementById('multVal').textContent    = target.toFixed(2) + 'x';
  document.getElementById('profitVal').textContent  = 'R$ ' + (bet * target).toFixed(2);
  document.getElementById('barTarget').textContent  = target.toFixed(2) + 'x';
}

async function spinLimbo() {
  const amount = document.getElementById('betAmount').value;
  const target = document.getElementById('targetInput').value;
  const btn    = document.getElementById('spinBtn');

  btn.disabled = true; btn.textContent = '⏳ Girando...';

  const body = new FormData();
  body.append('amount', amount); body.append('target', target);
  const r = await fetch('/api/games/limbo.php', {method:'POST', body});
  const d = await r.json();

  btn.disabled = false; btn.textContent = '🎯 Apostar';

  if (!d.success) { showToast('❌ ' + d.error, 'red'); return; }

  const el = document.getElementById('bigResult');
  el.className = 'big-result spin-anim ' + (d.won ? 'text-green-400' : 'text-red-400');
  el.textContent = d.result.toFixed(2) + 'x';

  document.getElementById('resultMsg').textContent = d.won
    ? `✅ GANHOU! Resultado ${d.result.toFixed(2)}x ≥ Alvo ${d.target.toFixed(2)}x`
    : `❌ PERDEU! Resultado ${d.result.toFixed(2)}x < Alvo ${d.target.toFixed(2)}x`;

  // Bar
  const barSection = document.getElementById('barSection');
  barSection.classList.remove('hidden');
  const pct = Math.min(100, (d.result / (d.target * 2)) * 100);
  const barFill = document.getElementById('barFill');
  barFill.style.width = pct + '%';
  barFill.className = 'bar-fill ' + (d.won ? 'bg-green-500' : 'bg-red-500');
  document.getElementById('barResult').textContent = d.result.toFixed(2) + 'x';

  history.unshift(d);
  if (history.length > 12) history.pop();
  document.getElementById('history').innerHTML = history.map(h =>
    `<span class="px-2 py-0.5 rounded text-xs font-bold ${h.won?'bg-green-900 text-green-300':'bg-red-900 text-red-300'}">${parseFloat(h.result).toFixed(2)}x</span>`
  ).join('');

  if (d.won) showToast(`💰 +R$ ${(d.profit - amount).toFixed(2)} em ${d.result.toFixed(2)}x!`, 'green');
  else showToast(`❌ Perdeu R$ ${parseFloat(amount).toFixed(2)}`, 'red');
}

function showToast(msg, color='green') {
  const t = document.createElement('div');
  t.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl font-bold text-white ${color==='green'?'bg-green-600':'bg-red-600'}`;
  t.textContent = msg; document.body.appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}

updateStats();
</script>
</body>
</html>
