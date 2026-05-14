<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'Casino';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Jogos — <?= htmlspecialchars($nomeSite) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  body { background: #0d0d1a; color: #fff; font-family: 'Inter', sans-serif; }
  .game-card { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: 1px solid #2a2a4a; transition: all .3s; }
  .game-card:hover { transform: translateY(-6px); border-color: #7c3aed; box-shadow: 0 20px 40px rgba(124,58,237,.3); }
  .badge-hot { background: linear-gradient(135deg, #f59e0b, #ef4444); }
  .badge-new { background: linear-gradient(135deg, #10b981, #059669); }
</style>
</head>
<body>
<div class="max-w-6xl mx-auto px-4 py-10">
  <div class="text-center mb-10">
    <h1 class="text-4xl font-bold mb-2">🎰 Casa de Apostas</h1>
    <p class="text-gray-400">Jogos provably fair — resultados verificáveis</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Crash -->
    <a href="/jogos/crash.php" class="game-card rounded-2xl p-6 block">
      <div class="flex justify-between items-start mb-4">
        <span class="text-6xl">🚀</span>
        <span class="badge-hot text-xs font-bold px-2 py-1 rounded-full">HOT</span>
      </div>
      <h2 class="text-2xl font-bold mb-1">Crash</h2>
      <p class="text-gray-400 text-sm mb-4">Multiplicador cresce até crashar. Saque antes!</p>
      <div class="flex gap-2 text-xs text-gray-500">
        <span>⚡ Ao vivo</span>
        <span>•</span>
        <span>Multiplayer</span>
      </div>
    </a>

    <!-- Mines -->
    <a href="/jogos/mines.php" class="game-card rounded-2xl p-6 block">
      <div class="flex justify-between items-start mb-4">
        <span class="text-6xl">💣</span>
        <span class="badge-hot text-xs font-bold px-2 py-1 rounded-full">HOT</span>
      </div>
      <h2 class="text-2xl font-bold mb-1">Mines</h2>
      <p class="text-gray-400 text-sm mb-4">Evite as minas e multiplique seus ganhos.</p>
      <div class="flex gap-2 text-xs text-gray-500">
        <span>🎮 Solo</span>
        <span>•</span>
        <span>Estratégia</span>
      </div>
    </a>

    <!-- Plinko -->
    <a href="/jogos/plinko.php" class="game-card rounded-2xl p-6 block">
      <div class="flex justify-between items-start mb-4">
        <span class="text-6xl">🔴</span>
        <span class="badge-new text-xs font-bold px-2 py-1 rounded-full">NEW</span>
      </div>
      <h2 class="text-2xl font-bold mb-1">Plinko</h2>
      <p class="text-gray-400 text-sm mb-4">Solte a bolinha e veja onde ela cai.</p>
      <div class="flex gap-2 text-xs text-gray-500">
        <span>🎯 Chance</span>
        <span>•</span>
        <span>Visual</span>
      </div>
    </a>

    <!-- Dice -->
    <a href="/jogos/dice.php" class="game-card rounded-2xl p-6 block">
      <div class="flex justify-between items-start mb-4">
        <span class="text-6xl">🎲</span>
        <span class="badge-new text-xs font-bold px-2 py-1 rounded-full">NEW</span>
      </div>
      <h2 class="text-2xl font-bold mb-1">Dice</h2>
      <p class="text-gray-400 text-sm mb-4">Aposte acima ou abaixo de um número.</p>
      <div class="flex gap-2 text-xs text-gray-500">
        <span>🎯 Preciso</span>
        <span>•</span>
        <span>Rápido</span>
      </div>
    </a>

    <!-- Limbo -->
    <a href="/jogos/limbo.php" class="game-card rounded-2xl p-6 block">
      <div class="flex justify-between items-start mb-4">
        <span class="text-6xl">🌀</span>
        <span class="badge-new text-xs font-bold px-2 py-1 rounded-full">NEW</span>
      </div>
      <h2 class="text-2xl font-bold mb-1">Limbo</h2>
      <p class="text-gray-400 text-sm mb-4">Defina um alvo. Se o resultado passar, você ganha.</p>
      <div class="flex gap-2 text-xs text-gray-500">
        <span>⚡ Rápido</span>
        <span>•</span>
        <span>Alto risco</span>
      </div>
    </a>

    <!-- Raspadinhas -->
    <a href="/" class="game-card rounded-2xl p-6 block">
      <div class="flex justify-between items-start mb-4">
        <span class="text-6xl">🎟️</span>
      </div>
      <h2 class="text-2xl font-bold mb-1">Raspadinhas</h2>
      <p class="text-gray-400 text-sm mb-4">Os clássicos bilhetes raspadinha virtuais.</p>
      <div class="flex gap-2 text-xs text-gray-500">
        <span>🏆 Clássico</span>
      </div>
    </a>

  </div>
</div>
</body>
</html>
