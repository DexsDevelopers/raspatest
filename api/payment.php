<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$amount = isset($_POST['amount']) ? floatval(str_replace(',', '.', $_POST['amount'])) : 0;
$cpf    = isset($_POST['cpf'])    ? preg_replace('/\D/', '', $_POST['cpf'])          : '';

if ($amount <= 0 || strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../classes/LunarPay.php';

try {
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception("Usuário não autenticado.");
    }

    $usuario_id = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch();
    if (!$usuario) {
        throw new Exception("Usuário não encontrado.");
    }

    $protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host        = $_SERVER['HTTP_HOST'];
    $external_id = uniqid('DEP_') . '_' . time();
    $callbackUrl = $protocol . $host . '/callback/lunarpay.php';

    $lunarPay        = new LunarPay($pdo);
    $paymentResponse = $lunarPay->createPixPayment(
        $amount,
        $usuario['nome'],
        $external_id,
        $callbackUrl
    );

    if (!$paymentResponse['success']) {
        throw new Exception("Erro ao gerar PIX: " . ($paymentResponse['error'] ?? 'Desconhecido'));
    }

    $transactionId = $paymentResponse['pix_id'];
    $qrcodeEmit    = $paymentResponse['qrcode'];
    $qrcodeImg     = $paymentResponse['qrcode_img'];

    if (empty($qrcodeImg)) {
        $qrcodeImg = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . rawurlencode($qrcodeEmit);
    }

    $stmt = $pdo->prepare("
        INSERT INTO depositos (transactionId, user_id, nome, cpf, valor, status, qrcode, gateway, idempotency_key)
        VALUES (:transactionId, :user_id, :nome, :cpf, :valor, 'PENDING', :qrcode, 'lunarpay', :idempotency_key)
    ");
    $stmt->execute([
        ':transactionId'   => $transactionId,
        ':user_id'         => $usuario_id,
        ':nome'            => $usuario['nome'],
        ':cpf'             => $cpf,
        ':valor'           => $amount,
        ':qrcode'          => $qrcodeEmit,
        ':idempotency_key' => $external_id,
    ]);

    $_SESSION['transactionId'] = $transactionId;

    echo json_encode([
        'qrcode'     => $qrcodeEmit,
        'qrcode_img' => $qrcodeImg,
        'gateway'    => 'lunarpay',
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}