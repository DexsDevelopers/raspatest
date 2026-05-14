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
    $target = (float)($_POST['target'] ?? 2.0);

    if ($amount < 0.10)  throw new Exception('Aposta mínima: R$ 0,10');
    if ($amount > 500)   throw new Exception('Aposta máxima: R$ 500,00');
    if ($target < 1.01)  throw new Exception('Alvo mínimo: 1.01x');
    if ($target > 1000)  throw new Exception('Alvo máximo: 1000x');

    if (!$engine->deductBalance($userId, $amount)) throw new Exception('Saldo insuficiente');

    $seeds  = $engine->getUserSeed($userId);
    $result = $engine->spinLimbo($seeds['server_seed'], $seeds['client_seed'], $seeds['nonce']);
    $engine->incrementNonce($userId);

    $won = $result >= $target;

    if ($won) {
        $profit = round($amount * $target, 2);
        $engine->addBalance($userId, $profit);
        $engine->recordBet($userId, 'limbo', $amount, $target, $profit - $amount, 'won', ['result' => $result, 'target' => $target]);
        echo json_encode(['success' => true, 'won' => true, 'result' => $result, 'target' => $target, 'profit' => $profit, 'net' => round($profit - $amount, 2)]);
    } else {
        $engine->recordBet($userId, 'limbo', $amount, 0, -$amount, 'lost', ['result' => $result, 'target' => $target]);
        echo json_encode(['success' => true, 'won' => false, 'result' => $result, 'target' => $target, 'profit' => 0, 'net' => -$amount]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
