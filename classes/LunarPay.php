<?php

class LunarPay
{
    private string $baseUrl = 'https://pixghost.site';
    private string $apiKey;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->loadCredentials();
    }

    private function loadCredentials(): void
    {
        $stmt = $this->pdo->query("SELECT api_key FROM lunarpay LIMIT 1");
        $creds = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$creds || empty($creds['api_key'])) {
            throw new Exception("Credenciais LunarPay não configuradas. Acesse Admin → Gateway para configurar.");
        }

        $this->apiKey = $creds['api_key'];
    }

    private function getHeaders(): array
    {
        return [
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
            "Accept: application/json"
        ];
    }

    /**
     * Cria uma cobrança PIX via LunarPay
     */
    public function createPixPayment(float $amount, string $customerName, string $externalId, string $callbackUrl): array
    {
        $payload = [
            'amount'       => $amount,
            'customer'     => ['name' => $customerName],
            'callback_url' => $callbackUrl,
            'external_id'  => $externalId,
        ];

        $ch = curl_init("{$this->baseUrl}/api.php");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $this->getHeaders(),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && !empty($data['success'])) {
            return [
                'success'    => true,
                'pix_id'     => $data['pix_id'],
                'qrcode'     => $data['pix_code'],
                'qrcode_img' => $data['qr_image'] ?? null,
                'expires_in' => $data['expires_in'] ?? 1200,
            ];
        }

        return [
            'success' => false,
            'error'   => $data['error'] ?? 'Erro na API LunarPay (HTTP ' . $httpCode . ')',
        ];
    }

    /**
     * Consulta o status de um PIX pelo pix_id
     */
    public function checkStatus(string $pixId): array
    {
        $url = "{$this->baseUrl}/check_status.php?pix_id=" . urlencode($pixId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->getHeaders(),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? ['success' => false, 'status' => 'unknown'];
    }
}
