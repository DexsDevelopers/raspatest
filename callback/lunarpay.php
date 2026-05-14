<?php
require_once __DIR__ . '/../conexao.php';
date_default_timezone_set('America/Sao_Paulo');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/webhook_error.log');

$json    = file_get_contents('php://input');
$payload = json_decode($json, true);

if (!$payload) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$event      = $payload['event']       ?? '';
$pixId      = $payload['pix_id']      ?? '';
$externalId = $payload['external_id'] ?? '';
$status     = $payload['status']      ?? '';
$amount     = isset($payload['amount']) ? floatval($payload['amount']) : 0;

if ($event === 'payment.confirmed' && $status === 'paid') {

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT * FROM depositos
            WHERE idempotency_key = :ext OR transactionId = :pid
            LIMIT 1 FOR UPDATE
        ");
        $stmt->execute([':ext' => $externalId, ':pid' => $pixId]);
        $deposito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deposito) {
            http_response_code(404);
            echo json_encode(['status' => 'ignore', 'message' => 'Depósito não encontrado']);
            $pdo->rollBack();
            exit;
        }

        if ($deposito['status'] === 'PAID') {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Pagamento já processado anteriormente.']);
            $pdo->rollBack();
            exit;
        }

        $pdo->prepare("
            UPDATE depositos
            SET status = 'PAID', webhook_data = ?, updated_at = NOW()
            WHERE id = ?
        ")->execute([$json, $deposito['id']]);

        $stmtUser = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id FOR UPDATE");
        $stmtUser->execute([':id' => $deposito['user_id']]);
        $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            throw new Exception("Usuário ID {$deposito['user_id']} não encontrado.");
        }

        $stmtRoll = $pdo->prepare("SELECT * FROM config_rollover WHERE id = 1");
        $stmtRoll->execute();
        $configRollover = $stmtRoll->fetch(PDO::FETCH_ASSOC);

        $bonus = 0;
        if (!empty($configRollover['porcentagem_bonus']) && (float)$configRollover['porcentagem_bonus'] > 0) {
            $bonus = ($amount * (float)$configRollover['porcentagem_bonus']) / 100;
        }

        $totalCredito = $amount + $bonus;
        $novoSaldo    = (float)$usuario['saldo'] + $totalCredito;

        $pdo->prepare("UPDATE usuarios SET saldo = :saldo WHERE id = :id")
            ->execute([':saldo' => $novoSaldo, ':id' => $usuario['id']]);

        $pdo->prepare("
            INSERT INTO transacoes
                (user_id, tipo, valor, saldo_anterior, saldo_posterior, status, referencia, gateway, descricao)
            VALUES
                (:user_id, 'DEPOSIT', :valor, :saldo_anterior, :saldo_posterior, 'COMPLETED', :referencia, 'lunarpay', :descricao)
        ")->execute([
            ':user_id'         => $usuario['id'],
            ':valor'           => $amount,
            ':saldo_anterior'  => $usuario['saldo'],
            ':saldo_posterior' => $novoSaldo,
            ':referencia'      => $pixId,
            ':descricao'       => 'Depósito PIX confirmado via LunarPay',
        ]);

        $pdo->commit();

        include_once __DIR__ . '/processar_comissao.php';
        processarComissao((int)$deposito['id'], $pdo);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Pagamento aprovado e saldo atualizado.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("LunarPay webhook error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro interno', 'details' => $e->getMessage()]);
    }

} else {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'message' => 'Evento ou status não processado: ' . $event . '/' . $status]);
}
