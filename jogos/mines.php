<?php require_once __DIR__ . '/../inc/header.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Mines — <?= $nomeSite ?? 'Casino' ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { background:#0d0d1a; color:#fff; font-family:'Inter',sans-serif; }
.panel { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:16px; }
.btn-primary { background:linear-gradient(135deg,#7c3aed,#4f46e5); color:#fff; font-weight:700; border-radius:10px; padding:12px; width:100%; transition:all .2s; }
.btn-cashout { background:linear-gradient(135deg,#10b981,#059669); }
.btn-danger  { background:linear-gradient(135deg,#ef4444,#b91c1c); }
.input-field { background:#0d0d1a; border:1px solid #374151; color:#fff; border-radius:8px; padding:10px 14px; width:100%; }
.tile { aspect-ratio:1; border-radius:10px; background:#0d0d1a; border:2px solid #2a2a4a; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; font-size:1.8rem; }
.tile:hover:not(.revealed):not(.mine):not(.disabled) { background:#1f2937; border-color:#7c3aed; transform:scale(1.05); }
.tile.revealed { background:#064e3b; border-color:#10b981; cursor:default; }
.tile.mine { background:#450a0a; border-color:#ef4444; cursor:default; animation: shake .3s; }
.tile.disabled { cursor:not-allowed; opacity:.7; }
@keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-4px)} 75%{transform:translateX(4px)} }
.mult-big { font-size:3rem; font-weight:900; color:#10b981; text-shadow:0 0 20px rgba(16,185,129,.5); }
</style>
</head>
<body>
<div class="max-w-4xl mx-auto px-4 py-6">
  <div class="flex items-center gap-3 mb-6">
    <a href="/jogos/" class="text-gray-400 hover:text-white"><i class="fas fa-arrow-left"></i></a>
    <h1 class="text-2xl font-bold">💣 Mines</h1>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Grid -->
    <div class="lg:col-span-2 panel p-4">
      <div class="text-center mb-4">
        <div id="multDisplay" class="mult-big">1.00x</div>
        <div id="gameMsg" class="text-gray-400 text-sm mt-1">Configure e inicie o jogo</div>
      </div>
      <div id="minesGrid" class="grid grid-cols-5 gap-2"></div>
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
        <label class="text-xs text-gray-400 mb-1 block">Nº de Minas</label>
        <select id="minesCount" class="input-field">
          <?php foreach([1,3,5,10,15,20,24] as $m): ?>
          <option value="<?=$m?>" <?=$m==3?'selected':''?>><?=$m?> mina<?=$m>1?'s':''?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button id="startBtn" class="btn-primary" onclick="startGame()">🎮 Iniciar</button>
      <button id="cashoutBtn" class="btn-primary btn-cashout hidden" onclick="cashout()">💰 Sacar</button>

      <div id="statsBox" class="hidden bg-gray-800 rounded-xl p-3 text-sm space-y-1">
        <div class="flex justify-between"><span class="text-gray-400">Aposta</span><span id="statBet">-</span></div>
        <div class="flex justify-between"><span class="text-gray-400">Multiplicador</span><span id="statMult" class="text-green-400">-</span></div>
        <div class="flex justify-between"><span class="text-gray-400">Potencial</span><span id="statProfit" class="text-yellow-400">-</span></div>
      </div>
    </div>
  </div>
</div>

<script>
let gameActive = false, currentBet = 0, revealed = [], totalTiles = 25;

function setBet(v) { document.getElementById('betAmount').value = v; }

function buildGrid(revealedTiles = [], mines = [], showMines = false) {
  const grid = document.getElementById('minesGrid');
  grid.innerHTML = '';
  for (let i = 0; i < totalTiles; i++) {
    const tile = document.createElement('div');
    tile.className = 'tile';
    tile.dataset.index = i;
    if (revealedTiles.includes(i)) {
      tile.classList.add('revealed'); tile.textContent = '💎';
    } else if (showMines && mines.includes(i)) {
      tile.classList.add('mine'); tile.textContent = '💣';
    } else if (gameActive) {
      tile.onclick = () => revealTile(i);
    } else {
      tile.classList.add('disabled'); tile.textContent = '❓';
    }
    grid.appendChild(tile);
  }
}

async function startGame() {
  const amount = document.getElementById('betAmount').value;
  const mines  = document.getElementById('minesCount').value;
  const body = new FormData();
  body.append('action','start'); body.append('amount', amount); body.append('mines', mines);
  const r = await fetch('/api/games/mines.php', {method:'POST', body});
  const d = await r.json();
  if (!d.success) { showToast('❌ ' + d.error, 'red'); return; }

  gameActive = true; currentBet = parseFloat(amount); revealed = [];
  document.getElementById('startBtn').classList.add('hidden');
  document.getElementById('cashoutBtn').classList.remove('hidden');
  document.getElementById('statsBox').classList.remove('hidden');
  document.getElementById('statBet').textContent = 'R$ ' + parseFloat(amount).toFixed(2);
  document.getElementById('multDisplay').textContent = '1.00x';
  document.getElementById('gameMsg').textContent = 'Clique nos tiles para revelar!';
  buildGrid([], [], false);
}

async function revealTile(index) {
  if (!gameActive) return;
  const body = new FormData();
  body.append('action','reveal'); body.append('tile', index);
  const r = await fetch('/api/games/mines.php', {method:'POST', body});
  const d = await r.json();
  if (!d.success) { showToast('❌ ' + d.error, 'red'); return; }

  if (d.hit) {
    gameActive = false;
    buildGrid(revealed, d.mines, true);
    document.getElementById('multDisplay').textContent = '💥';
    document.getElementById('multDisplay').style.color = '#ef4444';
    document.getElementById('gameMsg').textContent = 'Você bateu em uma mina!';
    document.getElementById('cashoutBtn').classList.add('hidden');
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('statsBox').classList.add('hidden');
    showToast('💥 Você bateu em uma mina!', 'red');
  } else {
    revealed = d.revealed;
    const mult = d.multiplier;
    document.getElementById('multDisplay').textContent = mult + 'x';
    document.getElementById('multDisplay').style.color = '#10b981';
    document.getElementById('statMult').textContent = mult + 'x';
    document.getElementById('statProfit').textContent = 'R$ ' + (currentBet * mult).toFixed(2);
    buildGrid(revealed, [], false);
    if (d.auto_won) {
      gameActive = false;
      document.getElementById('cashoutBtn').classList.add('hidden');
      document.getElementById('startBtn').classList.remove('hidden');
      showToast('🏆 Você revelou todos os tiles seguros! +R$ ' + d.profit.toFixed(2), 'green');
    }
  }
}

async function cashout() {
  const body = new FormData(); body.append('action','cashout');
  const r = await fetch('/api/games/mines.php', {method:'POST', body});
  const d = await r.json();
  if (d.success) {
    gameActive = false;
    document.getElementById('cashoutBtn').classList.add('hidden');
    document.getElementById('startBtn').classList.remove('hidden');
    document.getElementById('statsBox').classList.add('hidden');
    document.getElementById('gameMsg').textContent = '✅ Saque realizado!';
    showToast('💰 ' + d.message, 'green');
    buildGrid(revealed, [], false);
  } else showToast('❌ ' + d.error, 'red');
}

async function checkState() {
  const body = new FormData(); body.append('action','state');
  const r = await fetch('/api/games/mines.php', {method:'POST', body});
  const d = await r.json();
  if (d.active) {
    gameActive = true; revealed = d.revealed; currentBet = parseFloat(d.game.amount);
    document.getElementById('startBtn').classList.add('hidden');
    document.getElementById('cashoutBtn').classList.remove('hidden');
    document.getElementById('statsBox').classList.remove('hidden');
    document.getElementById('statBet').textContent = 'R$ ' + currentBet.toFixed(2);
    document.getElementById('multDisplay').textContent = d.multiplier + 'x';
    document.getElementById('statMult').textContent = d.multiplier + 'x';
    document.getElementById('statProfit').textContent = 'R$ ' + (currentBet * d.multiplier).toFixed(2);
    document.getElementById('gameMsg').textContent = 'Jogo ativo — continue revelando!';
    buildGrid(revealed, [], false);
  } else {
    buildGrid([], [], false);
  }
}

function showToast(msg, color='green') {
  const t = document.createElement('div');
  t.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-xl font-bold text-white ${color==='green'?'bg-green-600':'bg-red-600'}`;
  t.textContent = msg; document.body.appendChild(t);
  setTimeout(()=>t.remove(), 3500);
}

checkState();
</script>
</body>
</html>
