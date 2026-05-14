<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../classes/GameEngine.php';

if (!isset($_SESSION['usuario_id'])) { echo json_encode(['success'=>false,'error'=>'Não autenticado']); exit; }

$engine = new GameEngine($pdo);
$userId = (int)$_SESSION['usuario_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'start':
            $amount     = (float)($_POST['amount'] ?? 0);
            $minesCount = (int)($_POST['mines'] ?? 3);

            if ($amount < 0.10)       throw new Exception('Aposta mínima: R$ 0,10');
            if ($amount > 500)        throw new Exception('Aposta máxima: R$ 500,00');
            if ($minesCount < 1 || $minesCount > 24) throw new Exception('Minas: entre 1 e 24');

            $active = $pdo->prepare("SELECT id FROM mines_games WHERE user_id=? AND status='active' LIMIT 1");
            $active->execute([$userId]);
            if ($active->fetch()) throw new Exception('Você já tem um jogo ativo. Saque ou perca primeiro.');

            if (!$engine->deductBalance($userId, $amount)) throw new Exception('Saldo insuficiente');

            $seeds = $engine->getUserSeed($userId);
            $mines = $engine->generateMinesGrid($minesCount, $seeds['server_seed'], $seeds['client_seed'], $seeds['nonce']);

            $stmt = $pdo->prepare("INSERT INTO mines_games (user_id, amount, mines_count, mines_grid, revealed, status) VALUES (?,?,?,?,'[]','active')");
            $stmt->execute([$userId, $amount, $minesCount, json_encode($mines)]);
            $gameId = $pdo->lastInsertId();
            $engine->incrementNonce($userId);

            echo json_encode(['success' => true, 'game_id' => $gameId, 'multiplier' => 1.00, 'message' => 'Jogo iniciado!']);
            break;

        case 'reveal':
            $tile = (int)($_POST['tile'] ?? -1);
            if ($tile < 0 || $tile > 24) throw new Exception('Tile inválido');

            $stmt = $pdo->prepare("SELECT * FROM mines_games WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId]);
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$game) throw new Exception('Nenhum jogo ativo');

            $mines    = json_decode($game['mines_grid'], true);
            $revealed = json_decode($game['revealed'] ?? '[]', true);

            if (in_array($tile, $revealed)) throw new Exception('Tile já revelado');

            if (in_array($tile, $mines)) {
                $pdo->prepare("UPDATE mines_games SET status='lost', revealed=? WHERE id=?")->execute([json_encode(array_merge($revealed, [$tile])), $game['id']]);
                $engine->recordBet($userId, 'mines', $game['amount'], 0, -$game['amount'], 'lost');
                echo json_encode(['success' => true, 'hit' => true, 'mines' => $mines, 'message' => 'Você bateu em uma mina!']);
            } else {
                $revealed[] = $tile;
                $safeTiles  = 25 - $game['mines_count'];
                $mult       = $engine->getMinesMultiplier(count($revealed), $game['mines_count']);
                $pdo->prepare("UPDATE mines_games SET revealed=? WHERE id=?")->execute([json_encode($revealed), $game['id']]);

                $won = (count($revealed) >= $safeTiles);
                if ($won) {
                    $profit = round($game['amount'] * $mult, 2);
                    $engine->addBalance($userId, $profit);
                    $pdo->prepare("UPDATE mines_games SET status='won' WHERE id=?")->execute([$game['id']]);
                    $engine->recordBet($userId, 'mines', $game['amount'], $mult, $profit - $game['amount'], 'won');
                }

                echo json_encode(['success' => true, 'hit' => false, 'multiplier' => $mult, 'revealed' => $revealed, 'auto_won' => $won, 'profit' => $won ? round($game['amount'] * $mult, 2) : null]);
            }
            break;

        case 'cashout':
            $stmt = $pdo->prepare("SELECT * FROM mines_games WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $pdo->beginTransaction();
            $stmt->execute([$userId]);
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$game) { $pdo->rollBack(); throw new Exception('Nenhum jogo ativo'); }

            $revealed = json_decode($game['revealed'] ?? '[]', true);
            if (empty($revealed)) { $pdo->rollBack(); throw new Exception('Revele pelo menos 1 tile antes de sacar'); }

            $mult   = $engine->getMinesMultiplier(count($revealed), $game['mines_count']);
            $profit = round($game['amount'] * $mult, 2);

            $engine->addBalance($userId, $profit);
            $pdo->prepare("UPDATE mines_games SET status='won' WHERE id=?")->execute([$game['id']]);
            $engine->recordBet($userId, 'mines', $game['amount'], $mult, $profit - $game['amount'], 'won');
            $pdo->commit();

            echo json_encode(['success' => true, 'multiplier' => $mult, 'profit' => $profit, 'message' => "Sacou em {$mult}x! +R$ " . number_format($profit, 2, ',', '.')]);
            break;

        case 'state':
            $stmt = $pdo->prepare("SELECT * FROM mines_games WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId]);
            $game = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$game) { echo json_encode(['active' => false]); break; }

            $revealed = json_decode($game['revealed'] ?? '[]', true);
            $mult     = count($revealed) > 0 ? $engine->getMinesMultiplier(count($revealed), $game['mines_count']) : 1.00;
            echo json_encode(['active' => true, 'game' => $game, 'revealed' => $revealed, 'multiplier' => $mult]);
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
