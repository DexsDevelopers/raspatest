<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'Casino';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Crash — <?= htmlspecialchars($nomeSite) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#0d0d1a; color:#fff; font-family:'Inter',sans-serif; }
.panel { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:16px; }
.btn-primary { background:linear-gradient(135deg,#7c3aed,#4f46e5); color:#fff; font-weight:700; border-radius:10px; padding:12px 24px; width:100%; transition:all .2s; }
.btn-primary:hover { opacity:.9; transform:translateY(-1px); }
.btn-cashout { background:linear-gradient(135deg,#10b981,#059669); }
.btn-disabled { background:#374151; cursor:not-allowed; opacity:.5; }
.mult-display { font-size:5rem; font-weight:900; text-align:center; line-height:1; }
.mult-running { color:#10b981; text-shadow:0 0 30px rgba(16,185,129,.6); }
.mult-crashed { color:#ef4444; text-shadow:0 0 30px rgba(239,68,68,.6); }
.mult-waiting { color:#6b7280; }
.status-badge { padding:4px 12px; border-radius:9999px; font-size:.75rem; font-weight:700; }
.badge-waiting { background:#374151; color:#9ca3af; }
.badge-running { background:#064e3b; color:#10b981; }
.badge-crashed { background:#450a0a; color:#ef4444; }
.history-pill { padding:4px 10px; border-radius:9999px; font-size:.75rem; font-weight:700; display:inline-block; }
.input-field { background:#0d0d1a; border:1px solid #374151; color:#fff; border-radius:8px; padding:10px 14px; width:100%; }
.input-field:focus { outline:none; border-color:#7c3aed; }
#crashChart { max-height:220px; }
</style>
</head>
<body>
<div class="max-w-5xl mx-auto px-4 py-6">
  <div class="flex items-center gap-3 mb-6">
    <a href="/jogos/" class="text-gray-400 hover:text-white"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-2xl font-bold">🚀 Crash</h1>
    <span id="statusBadge" class="status-badge badge-waiting">Aguardando</span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <!-- Game Panel -->
    <div class="lg:col-span-2 panel p-4">
      <!-- Multiplier display -->
      <div class="flex flex-col items-center justify-center py-8">
        <div id="multDisplay" class="mult-display mult-waiting">1.00x</div>
        <div id="statusMsg" class="text-gray-400 mt-2 text-sm">Aguardando apostas...</div>
        <div id="countdown" class="text-yellow-400 font-bold mt-1 text-lg hidden"></div>
      </div>

      <!-- Chart -->
      <div class="mt-2">
        <canvas id="crashChart"></canvas>
      </div>

      <!-- Crash History -->
      <div class="mt-4">
        <p class="text-xs text-gray-500 mb-2">Histórico</p>
        <div id="history" class="flex flex-wrap gap-2"></div>
      </div>
    </div>

    <!-- Bet Panel -->
    <div class="panel p-4 flex flex-col gap-4">
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Valor da Aposta (R$)</label>
        <input type="number" id="betAmount" class="input-field" value="5.00" min="0.10" step="0.50">
        <div class="grid grid-cols-4 gap-1 mt-2">
          <?php foreach([1,5,10,50] as $v): ?>
          <button onclick="setBet(<?=$v?>)" class="bg-gray-800 hover:bg-gray-700 text-xs rounded py-1"><?=$v?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block">Auto Cashout (opcional)</label>
        <input type="number" id="autoCashout" class="input-field" placeholder="ex: 2.00" min="1.01" step="0.01">
      </div>
      <button id="betBtn" class="btn-primary" onclick="placeBet()">Apostar</button>
      <button id="cashoutBtn" class="btn-primary btn-cashout btn-disabled hidden" onclick="doCashout()">💰 Sacar</button>

      <!-- My Bet Info -->
      <div id="myBetInfo" class="hidden bg-gray-800 rounded-xl p-3 text-sm">
        <div class="flex justify-between"><span class="text-gray-400">Aposta</span><span id="myBetAmt">-</span></div>
        <div class="flex justify-between mt-1"><span class="text-gray-400">Ganho atual</span><span id="myBetProfit" class="text-green-400">-</span></div>
      </div>

      <!-- Players -->
      <div>
        <p class="text-xs text-gray-400 mb-2">Jogadores</p>
        <div id="playerList" class="space-y-1 max-h-40 overflow-y-auto text-sm"></div>
      </div>
    </div>
  </div>
</div>

<script>
let chart, chartData = { labels: [], datasets: [{ data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.3, fill: true, pointRadius: 0 }] };
let currentRound = null, myBet = null, polling = null, lastRoundId = null;

// Init chart
const ctx = document.getElementById('crashChart').getContext('2d');
chart = new Chart(ctx, { type: 'line', data: chartData, options: { animation: false, responsive: true, scales: { x: { display: false }, y: { ticks: { color: '#9ca3af' }, grid: { color: '#1f2937' }, min: 1 } }, plugins: { legend: { display: false } } } });

function resetChart() { chartData.labels = []; chartData.datasets[0].data = []; chartData.datasets[0].borderColor = '#10b981'; chart.update('none'); }
function setBet(v) { document.getElementById('betAmount').value = v; }

async function fetchStatus() {
  try {
    const r = await fetch('/api/games/crash.php?action=status');
    const d = await r.json();
    renderStatus(d);
  } catch(e) {}
}

function renderStatus(d) {
  const round = d.round;
  const badge = document.getElementById('statusBadge');
  const multEl = document.getElementById('multDisplay');
  const msgEl  = document.getElementById('statusMsg');
  const cdEl   = document.getElementById('countdown');

  if (!round) return;

  if (lastRoundId && lastRoundId !== round.id) {
    resetChart();
  }
  lastRoundId = round.id;
  myBet = d.my_bet;

  // Status badge
  badge.className = 'status-badge badge-' + round.status;
  badge.textContent = round.status === 'waiting' ? 'Apostas Abertas' : round.status === 'running' ? 'Ao Vivo' : 'Crashou';

  if (round.status === 'waiting') {
    const left = Math.max(0, (round.time_left_ms ?? 5000) / 1000).toFixed(1);
    multEl.className = 'mult-display mult-waiting'; multEl.textContent = '1.00x';
    msgEl.textContent = 'Faça sua aposta!';
    cdEl.classList.remove('hidden'); cdEl.textContent = `⏱ ${left}s`;
    document.getElementById('betBtn').classList.remove('hidden');
    document.getElementById('cashoutBtn').classList.add('hidden');
    document.getElementById('cashoutBtn').classList.add('btn-disabled');
  }

  if (round.status === 'running') {
    const mult = parseFloat(round.current_multiplier ?? 1).toFixed(2);
    multEl.className = 'mult-display mult-running'; multEl.textContent = mult + 'x';
    msgEl.textContent = 'Rodada em andamento!';
    cdEl.classList.add('hidden');

    chartData.labels.push('');
    chartData.datasets[0].data.push(parseFloat(mult));
    if (chartData.labels.length > 80) { chartData.labels.shift(); chartData.datasets[0].data.shift(); }
    chart.update('none');

    if (myBet && myBet.status === 'pending') {
      document.getElementById('betBtn').classList.add('hidden');
      const cashBtn = document.getElementById('cashoutBtn');
      cashBtn.classList.remove('hidden'); cashBtn.classList.remove('btn-disabled');
      document.getElementById('myBetInfo').classList.remove('hidden');
      document.getElementById('myBetAmt').textContent = 'R$ ' + parseFloat(myBet.amount).toFixed(2);
      document.getElementById('myBetProfit').textContent = 'R$ ' + (myBet.amount * mult).toFixed(2);
      if (myBet.cashout_at && parseFloat(mult) >= parseFloat(myBet.cashout_at)) doCashout();
    }
  }

  if (round.status === 'crashed') {
    multEl.className = 'mult-display mult-crashed'; multEl.textContent = parseFloat(round.crash_point).toFixed(2) + 'x';
    msgEl.textContent = '💥 Crashou!';
    cdEl.classList.add('hidden');
    chartData.datasets[0].borderColor = '#ef4444'; chart.update('none');
    document.getElementById('betBtn').classList.remove('hidden');
    document.getElementById('cashoutBtn').classList.add('hidden');
    document.getElementById('myBetInfo').classList.add('hidden');
  }

  // Players
  const pl = document.getElementById('playerList');
  pl.innerHTML = (d.bets ?? []).map(b => `<div class="flex justify-between text-xs py-1"><span class="text-gray-300">${b.nome.split(' ')[0]}</span><span class="text-yellow-400">R$ ${parseFloat(b.amount).toFixed(2)}</span></div>`).join('') || '<div class="text-gray-600 text-xs">Nenhum jogador</div>';

  // History
  const hist = document.getElementById('history');
  hist.innerHTML = (d.history ?? []).map(h => {
    const cp = parseFloat(h.crash_point);
    const color = cp < 1.5 ? 'bg-red-900 text-red-300' : cp < 3 ? 'bg-yellow-900 text-yellow-300' : 'bg-green-900 text-green-300';
    return `<span class="history-pill ${color}">${cp.toFixed(2)}x</span>`;
  }).join('');
}

async function placeBet() {
  const amount = document.getElementById('betAmount').value;
  const auto   = document.getElementById('autoCashout').value;
  const body   = new FormData();
  body.append('action','bet'); body.append('amount', amount);
  if (auto) body.append('auto_cashout', auto);
  const r = await fetch('/api/games/crash.php', { method:'POST', body });
  const d = await r.json();
  if (d.success) { showToast('✅ ' + d.message, 'green'); }
  else showToast('❌ ' + d.error, 'red');
}

async function doCashout() {
  document.getElementById('cashoutBtn').classList.add('btn-disabled');
  const body = new FormData(); body.append('action','cashout');
  const r = await fetch('/api/games/crash.php', { method:'POST', body });
  const d = await r.json();
  if (d.success) { showToast('💰 ' + d.message, 'green'); document.getElementById('myBetInfo').classList.add('hidden'); }
  else showToast('❌ ' + d.error, 'red');
}

function showToast(msg, color='green') {
  const t = document.createElement('div');
  t.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl font-bold text-white ${color==='green'?'bg-green-600':'bg-red-600'}`;
  t.textContent = msg; document.body.appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}

fetchStatus();
setInterval(fetchStatus, 300);
</script>
</body>
</html>
