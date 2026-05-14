<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../classes/GameEngine.php';

$engine = new GameEngine($pdo);
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

try {
    switch ($action) {

        case 'status':
            $round = $engine->getOrCreateCrashRound();

            $bets = [];
            if ($round['status'] === 'running' || $round['status'] === 'waiting') {
                $stmt = $pdo->prepare("SELECT cb.*, u.nome FROM crash_bets cb JOIN usuarios u ON cb.user_id = u.id WHERE cb.round_id = ? AND cb.status = 'pending'");
                $stmt->execute([$round['id']]);
                $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $myBet = null;
            if (isset($_SESSION['usuario_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM crash_bets WHERE round_id = ? AND user_id = ? LIMIT 1");
                $stmt->execute([$round['id'], $_SESSION['usuario_id']]);
                $myBet = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $history = [];
            $stmt = $pdo->query("SELECT crash_point, server_seed_hash FROM crash_rounds WHERE status='crashed' ORDER BY id DESC LIMIT 10");
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'round'   => $round,
                'bets'    => $bets,
                'my_bet'  => $myBet,
                'history' => $history,
            ]);
            break;

        case 'bet':
            if (!isset($_SESSION['usuario_id'])) throw new Exception('Não autenticado');
            $userId = (int)$_SESSION['usuario_id'];
            $amount = (float)($_POST['amount'] ?? 0);
            $autoCashout = isset($_POST['auto_cashout']) ? (float)$_POST['auto_cashout'] : null;

            if ($amount < 0.10) throw new Exception('Aposta mínima: R$ 0,10');
            if ($amount > 500)  throw new Exception('Aposta máxima: R$ 500,00');

            $round = $engine->getOrCreateCrashRound();

            if ($round['status'] !== 'waiting') throw new Exception('Apostas encerradas para esta rodada');

            $stmt = $pdo->prepare("SELECT id FROM crash_bets WHERE round_id=? AND user_id=? LIMIT 1");
            $stmt->execute([$round['id'], $userId]);
            if ($stmt->fetch()) throw new Exception('Você já apostou nesta rodada');

            if (!$engine->deductBalance($userId, $amount)) throw new Exception('Saldo insuficiente');

            $stmt = $pdo->prepare("INSERT INTO crash_bets (round_id, user_id, amount, cashout_at, status) VALUES (?,?,?,?,'pending')");
            $stmt->execute([$round['id'], $userId, $amount, $autoCashout]);

            echo json_encode(['success' => true, 'message' => 'Aposta registrada!', 'bet_id' => $pdo->lastInsertId()]);
            break;

        case 'cashout':
            if (!isset($_SESSION['usuario_id'])) throw new Exception('Não autenticado');
            $userId = (int)$_SESSION['usuario_id'];

            $round = $engine->getOrCreateCrashRound();
            if ($round['status'] !== 'running') throw new Exception('Rodada não está em andamento');

            $currentMult = (float)($round['current_multiplier'] ?? 1.00);

            $stmt = $pdo->prepare("SELECT * FROM crash_bets WHERE round_id=? AND user_id=? AND status='pending' LIMIT 1 FOR UPDATE");
            $pdo->beginTransaction();
            $stmt->execute([$round['id'], $userId]);
            $bet = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$bet) { $pdo->rollBack(); throw new Exception('Aposta não encontrada ou já encerrada'); }

            $profit = round($bet['amount'] * $currentMult, 2);
            $engine->addBalance($userId, $profit);
            $pdo->prepare("UPDATE crash_bets SET status='won', cashout_at=?, profit=? WHERE id=?")->execute([$currentMult, $profit - $bet['amount'], $bet['id']]);
            $engine->recordBet($userId, 'crash', $bet['amount'], $currentMult, $profit - $bet['amount'], 'won');
            $pdo->commit();

            echo json_encode(['success' => true, 'multiplier' => $currentMult, 'profit' => $profit, 'message' => "Sacou em {$currentMult}x! +R$ " . number_format($profit, 2, ',', '.')]);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
