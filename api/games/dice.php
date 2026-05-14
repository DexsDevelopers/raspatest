<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../classes/GameEngine.php';

if (!isset($_SESSION['usuario_id'])) { echo json_encode(['success'=>false,'error'=>'Não autenticado']); exit; }

$engine = new GameEngine($pdo);
$userId = (int)$_SESSION['usuario_id'];

try {
    $amount    = (float)($_POST['amount'] ?? 0);
    $target    = (float)($_POST['target'] ?? 50);
    $direction = $_POST['direction'] ?? 'under';

    if ($amount < 0.10)                         throw new Exception('Aposta mínima: R$ 0,10');
    if ($amount > 500)                          throw new Exception('Aposta máxima: R$ 500,00');
    if ($target < 2 || $target > 98)            throw new Exception('Alvo entre 2 e 98');
    if (!in_array($direction, ['under','over'])) throw new Exception('Direção inválida');

    if (!$engine->deductBalance($userId, $amount)) throw new Exception('Saldo insuficiente');

    $seeds  = $engine->getUserSeed($userId);
    $rolled = $engine->rollDice($seeds['server_seed'], $seeds['client_seed'], $seeds['nonce']);
    $engine->incrementNonce($userId);

    $mult = $engine->getDiceMultiplier($target, $direction);
    $won  = $direction === 'under' ? $rolled < $target : $rolled > $target;

    if ($won) {
        $profit = round($amount * $mult, 2);
        $engine->addBalance($userId, $profit);
        $engine->recordBet($userId, 'dice', $amount, $mult, $profit - $amount, 'won', ['rolled' => $rolled, 'target' => $target, 'direction' => $direction]);
        echo json_encode(['success' => true, 'won' => true, 'rolled' => $rolled, 'multiplier' => $mult, 'profit' => $profit, 'net' => round($profit - $amount, 2)]);
    } else {
        $engine->recordBet($userId, 'dice', $amount, 0, -$amount, 'lost', ['rolled' => $rolled, 'target' => $target, 'direction' => $direction]);
        echo json_encode(['success' => true, 'won' => false, 'rolled' => $rolled, 'multiplier' => $mult, 'profit' => 0, 'net' => -$amount]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
