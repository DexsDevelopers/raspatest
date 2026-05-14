<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../classes/GameEngine.php';

if (!isset($_SESSION['usuario_id'])) { echo json_encode(['success'=>false,'error'=>'Não autenticado']); exit; }

$engine = new GameEngine($pdo);
$userId = (int)$_SESSION['usuario_id'];
$theme  = preg_replace('/[^a-z]/', '', strtolower($_POST['theme'] ?? 'tiger'));

// ── Symbol tables per theme ─────────────────────────────────────────
$themes = [
    'tiger'  => [
        ['id'=>'tiger',  'label'=>'🐯', 'mult'=>50, 'w'=>2],
        ['id'=>'dragon', 'label'=>'🐉', 'mult'=>25, 'w'=>4],
        ['id'=>'gold',   'label'=>'💰', 'mult'=>12, 'w'=>8],
        ['id'=>'gem',    'label'=>'💎', 'mult'=>8,  'w'=>12],
        ['id'=>'coin',   'label'=>'🪙', 'mult'=>4,  'w'=>22],
        ['id'=>'seven',  'label'=>'7',  'mult'=>2,  'w'=>30],
        ['id'=>'bar',    'label'=>'BAR','mult'=>1.5,'w'=>22],
    ],
    'rabbit' => [
        ['id'=>'rabbit', 'label'=>'🐰', 'mult'=>50, 'w'=>2],
        ['id'=>'flower', 'label'=>'🌸', 'mult'=>25, 'w'=>4],
        ['id'=>'moon',   'label'=>'🌙', 'mult'=>12, 'w'=>8],
        ['id'=>'gem',    'label'=>'💎', 'mult'=>8,  'w'=>12],
        ['id'=>'coin',   'label'=>'🪙', 'mult'=>4,  'w'=>22],
        ['id'=>'seven',  'label'=>'7',  'mult'=>2,  'w'=>30],
        ['id'=>'bar',    'label'=>'BAR','mult'=>1.5,'w'=>22],
    ],
    'dragon' => [
        ['id'=>'dragon', 'label'=>'🐉', 'mult'=>50, 'w'=>2],
        ['id'=>'fire',   'label'=>'🔥', 'mult'=>25, 'w'=>4],
        ['id'=>'pearl',  'label'=>'🔮', 'mult'=>12, 'w'=>8],
        ['id'=>'gem',    'label'=>'💎', 'mult'=>8,  'w'=>12],
        ['id'=>'coin',   'label'=>'🪙', 'mult'=>4,  'w'=>22],
        ['id'=>'seven',  'label'=>'7',  'mult'=>2,  'w'=>30],
        ['id'=>'bar',    'label'=>'BAR','mult'=>1.5,'w'=>22],
    ],
];

$syms = $themes[$theme] ?? $themes['tiger'];

// Build weighted pool
$pool = [];
foreach ($syms as $sym) {
    for ($i = 0; $i < $sym['w']; $i++) $pool[] = $sym;
}

try {
    $amount = (float)($_POST['amount'] ?? 0);
    $lines  = min(5, max(1, (int)($_POST['lines'] ?? 3)));

    if ($amount < 0.10) throw new Exception('Aposta mínima: R$ 0,10');
    if ($amount > 500)  throw new Exception('Aposta máxima: R$ 500,00');

    if (!$engine->deductBalance($userId, $amount)) throw new Exception('Saldo insuficiente');

    $seeds = $engine->getUserSeed($userId);
    $ss    = $seeds['server_seed'];
    $cs    = $seeds['client_seed'];
    $nonce = (int)$seeds['nonce'];

    // Generate 3x3 grid using provably fair floats
    $grid = [];
    $cursor = 0;
    for ($row = 0; $row < 3; $row++) {
        $grid[$row] = [];
        for ($col = 0; $col < 3; $col++) {
            $f = $engine->generateFloat($ss, $cs, $nonce, $cursor++);
            $idx = (int)floor($f * count($pool));
            $grid[$row][$col] = $pool[min($idx, count($pool)-1)];
        }
    }

    $engine->incrementNonce($userId);

    // Win lines definition: [row,col] triples
    $winLines = [
        [[0,0],[0,1],[0,2]], // top row
        [[1,0],[1,1],[1,2]], // middle row
        [[2,0],[2,1],[2,2]], // bottom row
        [[0,0],[1,1],[2,2]], // diagonal ↘
        [[0,2],[1,1],[2,0]], // diagonal ↙
    ];

    $totalMult = 0;
    $wins      = [];
    $betPerLine = $amount / $lines;

    foreach (array_slice($winLines, 0, $lines) as $li => $line) {
        $ids = [$grid[$line[0][0]][$line[0][1]]['id'],
                $grid[$line[1][0]][$line[1][1]]['id'],
                $grid[$line[2][0]][$line[2][1]]['id']];

        if ($ids[0] === $ids[1] && $ids[1] === $ids[2]) {
            $m = $grid[$line[0][0]][$line[0][1]]['mult'];
            $totalMult += $m;
            $wins[] = ['line' => $li, 'symbol' => $ids[0], 'mult' => $m, 'coords' => $line];
        }
    }

    $profit = round($betPerLine * $totalMult * $lines * 0.96, 2); // 4% house edge
    if ($profit > 0) $engine->addBalance($userId, $profit);

    $status = $profit > 0 ? 'won' : 'lost';
    $engine->recordBet($userId, 'slot_'.$theme, $amount, $totalMult, $profit - $amount, $status, [
        'theme' => $theme, 'lines' => $lines, 'wins' => count($wins)
    ]);

    echo json_encode([
        'success'    => true,
        'grid'       => $grid,
        'wins'       => $wins,
        'multiplier' => $totalMult,
        'profit'     => $profit,
        'net'        => round($profit - $amount, 2),
        'seed_hash'  => $seeds['server_seed_hash'],
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
