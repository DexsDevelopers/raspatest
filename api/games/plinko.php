<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../classes/GameEngine.php';

if (!isset($_SESSION['usuario_id'])) { echo json_encode(['success'=>false,'error'=>'Não autenticado']); exit; }

$engine = new GameEngine($pdo);
$userId = (int)$_SESSION['usuario_id'];

try {
    $amount = (float)($_POST['amount'] ?? 0);
    $rows   = (int)($_POST['rows'] ?? 8);
    $risk   = $_POST['risk'] ?? 'medium';

    if ($amount < 0.10)              throw new Exception('Aposta mínima: R$ 0,10');
    if ($amount > 500)               throw new Exception('Aposta máxima: R$ 500,00');
    if (!in_array($rows, [8,12,16])) throw new Exception('Linhas inválidas');
    if (!in_array($risk, ['low','medium','high'])) throw new Exception('Risco inválido');

    if (!$engine->deductBalance($userId, $amount)) throw new Exception('Saldo insuficiente');

    $seeds  = $engine->getUserSeed($userId);
    $result = $engine->dropPlinko($rows, $risk, $seeds['server_seed'], $seeds['client_seed'], $seeds['nonce']);
    $engine->incrementNonce($userId);

    $mult   = (float)$result['multiplier'];
    $profit = round($amount * $mult, 2);
    $engine->addBalance($userId, $profit);

    $status = $profit >= $amount ? 'won' : 'lost';
    $engine->recordBet($userId, 'plinko', $amount, $mult, $profit - $amount, $status, ['rows' => $rows, 'risk' => $risk, 'position' => $result['position']]);

    echo json_encode([
        'success'    => true,
        'path'       => $result['path'],
        'position'   => $result['position'],
        'multiplier' => $mult,
        'profit'     => $profit,
        'net'        => round($profit - $amount, 2),
        'multipliers'=> $engine->getPlinkoMultipliers($rows, $risk),
        'message'    => "{$mult}x — R$ " . number_format($profit, 2, ',', '.'),
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
