<?php

/**
 * Processa comissões CPA para afiliados quando um depósito é confirmado.
 * CPA é pago apenas no primeiro depósito do usuário.
 * Chamado pelo webhook de depósito (lunarpay.php).
 */
function processarComissao(int $depositoId, PDO $pdo): void
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM depositos WHERE id = ?");
        $stmt->execute([$depositoId]);
        $deposito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deposito) return;

        $userId = (int)$deposito['user_id'];
        $valor  = (float)$deposito['valor'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || empty($usuario['indicacao'])) return;

        $afiliadoId = (int)$usuario['indicacao'];
        if ($afiliadoId <= 0) return;

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$afiliadoId]);
        $afiliado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$afiliado) return;

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM depositos
            WHERE user_id = ? AND status = 'PAID' AND id != ?
        ");
        $stmt->execute([$userId, $depositoId]);
        $isFirstDeposit = ((int)$stmt->fetchColumn() === 0);

        if (!$isFirstDeposit) return;

        $cpaPadrao    = (float)$pdo->query("SELECT cpa_padrao FROM config LIMIT 1")->fetchColumn();
        $comissaoCpa  = (float)$afiliado['comissao_cpa'] > 0 ? (float)$afiliado['comissao_cpa'] : $cpaPadrao;

        if ($comissaoCpa <= 0) return;

        $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?")
            ->execute([$comissaoCpa, $afiliadoId]);

        $pdo->prepare("
            INSERT INTO transacoes_afiliados (afiliado_id, usuario_id, deposito_id, valor, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ")->execute([$afiliadoId, $userId, $depositoId, $comissaoCpa]);

        $pdo->prepare("
            INSERT INTO comissoes_hierarquia
                (usuario_id, afiliado_id, valor, valor_original, tipo, descricao, data_registro)
            VALUES (?, ?, ?, ?, 'CPA', 'CPA - primeiro depósito', NOW())
        ")->execute([$userId, $afiliadoId, $comissaoCpa, $valor]);

        if (!empty($usuario['indicacao_gerente']) && (int)$usuario['indicacao_gerente'] > 0) {
            $gerenteId = (int)$usuario['indicacao_gerente'];

            $stmt = $pdo->prepare("SELECT porcentagem_gerente FROM usuarios WHERE id = ?");
            $stmt->execute([$gerenteId]);
            $gerente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($gerente && (float)$gerente['porcentagem_gerente'] > 0) {
                $comissaoGerente = ($comissaoCpa * (float)$gerente['porcentagem_gerente']) / 100;

                if ($comissaoGerente > 0) {
                    $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?")
                        ->execute([$comissaoGerente, $gerenteId]);

                    $pdo->prepare("
                        INSERT INTO comissoes_hierarquia
                            (usuario_id, afiliado_id, gerente_id, valor, valor_original, tipo, descricao, data_registro)
                        VALUES (?, ?, ?, ?, ?, 'CPA', 'Comissão gerente sobre CPA', NOW())
                    ")->execute([$userId, $afiliadoId, $gerenteId, $comissaoGerente, $comissaoCpa]);
                }
            }
        }

        if (!empty($usuario['indicacao_subgerente']) && (int)$usuario['indicacao_subgerente'] > 0) {
            $subgerenteId = (int)$usuario['indicacao_subgerente'];

            $stmt = $pdo->prepare("SELECT porcentagem_subgerente FROM usuarios WHERE id = ?");
            $stmt->execute([$subgerenteId]);
            $subgerente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($subgerente && (float)$subgerente['porcentagem_subgerente'] > 0) {
                $comissaoSub = ($comissaoCpa * (float)$subgerente['porcentagem_subgerente']) / 100;

                if ($comissaoSub > 0) {
                    $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?")
                        ->execute([$comissaoSub, $subgerenteId]);

                    $pdo->prepare("
                        INSERT INTO comissoes_hierarquia
                            (usuario_id, afiliado_id, subgerente_id, valor, valor_original, tipo, descricao, data_registro)
                        VALUES (?, ?, ?, ?, ?, 'CPA', 'Comissão subgerente sobre CPA', NOW())
                    ")->execute([$userId, $afiliadoId, $subgerenteId, $comissaoSub, $comissaoCpa]);
                }
            }
        }

    } catch (PDOException $e) {
        error_log("processarComissao erro: " . $e->getMessage());
    }
}
