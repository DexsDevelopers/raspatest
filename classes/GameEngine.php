<?php

class GameEngine {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ==================== PROVABLY FAIR ====================

    public function generateServerSeed(): string {
        return bin2hex(random_bytes(32));
    }

    public function hashSeed(string $seed): string {
        return hash('sha256', $seed);
    }

    public function generateFloat(string $serverSeed, string $clientSeed, int $nonce, int $cursor = 0): float {
        $hash = hash_hmac('sha256', "{$clientSeed}:{$nonce}", $serverSeed);
        $index = $cursor * 8;
        $hex = substr($hash, $index % 64, 8);
        $int = hexdec($hex);
        return $int / 0xFFFFFFFF;
    }

    // ==================== CRASH ====================

    public function generateCrashPoint(string $serverSeed): float {
        $hash = hash('sha256', $serverSeed);
        $h = hexdec(substr($hash, 0, 13));
        $e = pow(2, 52);
        if ($h % 100 === 0) return 1.00;
        $result = floor((100 * $e - $h) / ($e - $h)) / 100;
        return max(1.01, $result);
    }

    public function getCurrentMultiplier(int $startedAtMs, float $crashPoint): array {
        $elapsedMs = (int)(microtime(true) * 1000) - $startedAtMs;
        $elapsed   = max(0, $elapsedMs / 1000);
        $mult      = round(pow(M_E, 0.07 * $elapsed), 2);

        if ($mult >= $crashPoint) {
            return ['multiplier' => $crashPoint, 'crashed' => true, 'elapsed_ms' => $elapsedMs];
        }
        return ['multiplier' => $mult, 'crashed' => false, 'elapsed_ms' => $elapsedMs];
    }

    public function getOrCreateCrashRound(): array {
        $stmt  = $this->pdo->query("SELECT * FROM crash_rounds WHERE status != 'crashed' ORDER BY id DESC LIMIT 1");
        $round = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$round) return $this->createCrashRound();

        $now = microtime(true);

        if ($round['status'] === 'waiting') {
            $elapsed = $now - ($round['created_at_ms'] / 1000);
            if ($elapsed >= 5.0) {
                $startedMs = (int)($now * 1000);
                $this->pdo->prepare("UPDATE crash_rounds SET status='running', started_at_ms=? WHERE id=?")
                    ->execute([$startedMs, $round['id']]);
                $round['status']     = 'running';
                $round['started_at_ms'] = $startedMs;
            }
            $round['current_multiplier'] = 1.00;
            $round['time_left_ms'] = max(0, (int)(5000 - (($now - $round['created_at_ms'] / 1000)) * 1000));
            return $round;
        }

        if ($round['status'] === 'running') {
            $data = $this->getCurrentMultiplier((int)$round['started_at_ms'], (float)$round['crash_point']);
            $round['current_multiplier'] = $data['multiplier'];
            $round['elapsed_ms']         = $data['elapsed_ms'];

            if ($data['crashed']) {
                $this->crashRound((int)$round['id'], (float)$round['crash_point']);
                $this->createCrashRound();
                $round['status'] = 'crashed';
            }
        }

        return $round;
    }

    public function createCrashRound(): array {
        $serverSeed  = $this->generateServerSeed();
        $crashPoint  = $this->generateCrashPoint($serverSeed);
        $hash        = $this->hashSeed($serverSeed);
        $createdAtMs = (int)(microtime(true) * 1000);

        $stmt = $this->pdo->prepare("INSERT INTO crash_rounds (server_seed, server_seed_hash, crash_point, status, created_at_ms) VALUES (?,?,?,'waiting',?)");
        $stmt->execute([$serverSeed, $hash, $crashPoint, $createdAtMs]);

        return [
            'id'               => (int)$this->pdo->lastInsertId(),
            'server_seed_hash' => $hash,
            'crash_point'      => $crashPoint,
            'status'           => 'waiting',
            'created_at_ms'    => $createdAtMs,
            'current_multiplier' => 1.00,
            'time_left_ms'     => 5000,
        ];
    }

    public function crashRound(int $roundId, float $crashPoint): void {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("UPDATE crash_rounds SET status='crashed', crashed_at=NOW() WHERE id=?")
                ->execute([$roundId]);

            $stmt = $this->pdo->prepare("SELECT * FROM crash_bets WHERE round_id=? AND status='pending' FOR UPDATE");
            $stmt->execute([$roundId]);
            $bets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($bets as $bet) {
                $this->pdo->prepare("UPDATE crash_bets SET status='lost', profit=? WHERE id=?")
                    ->execute([-$bet['amount'], $bet['id']]);
                $this->recordBet($bet['user_id'], 'crash', $bet['amount'], 0, -$bet['amount'], 'lost');
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
        }
    }

    // ==================== MINES ====================

    public function generateMinesGrid(int $minesCount, string $serverSeed, string $clientSeed, int $nonce): array {
        $positions = range(0, 24);
        for ($i = 24; $i > 0; $i--) {
            $r = $this->generateFloat($serverSeed, $clientSeed, $nonce, 24 - $i);
            $j = (int)floor($r * ($i + 1));
            [$positions[$i], $positions[$j]] = [$positions[$j], $positions[$i]];
        }
        return array_slice($positions, 0, $minesCount);
    }

    public function getMinesMultiplier(int $revealed, int $minesCount): float {
        $total = 25;
        $safe  = $total - $minesCount;
        $mult  = 1.0;
        for ($i = 0; $i < $revealed; $i++) {
            $mult *= ($total - $i) / ($safe - $i);
        }
        return max(1.0, round($mult * 0.97, 4));
    }

    // ==================== PLINKO ====================

    public function dropPlinko(int $rows, string $risk, string $serverSeed, string $clientSeed, int $nonce): array {
        $position = 0;
        $path     = [];
        for ($row = 0; $row < $rows; $row++) {
            $dir = $this->generateFloat($serverSeed, $clientSeed, $nonce, $row) < 0.5 ? 'L' : 'R';
            $path[] = $dir;
            if ($dir === 'R') $position++;
        }
        $multipliers = $this->getPlinkoMultipliers($rows, $risk);
        return ['path' => $path, 'position' => $position, 'multiplier' => $multipliers[$position] ?? 1.0];
    }

    public function getPlinkoMultipliers(int $rows, string $risk): array {
        $table = [
            8  => [
                'low'    => [5.6,  2.1, 1.1, 1.0, 0.5,  1.0,  1.1, 2.1, 5.6],
                'medium' => [13.0, 3.0, 1.3, 0.7, 0.4,  0.7,  1.3, 3.0, 13.0],
                'high'   => [29.0, 4.0, 1.5, 0.3, 0.2,  0.3,  1.5, 4.0, 29.0],
            ],
            12 => [
                'low'    => [10.0, 3.0,  1.6, 1.4, 1.1, 1.0, 0.5, 1.0, 1.1, 1.4, 1.6, 3.0,  10.0],
                'medium' => [33.0, 11.0, 4.0, 2.0, 1.1, 0.6, 0.3, 0.6, 1.1, 2.0, 4.0, 11.0, 33.0],
                'high'   => [170.0,24.0, 8.1, 2.0, 0.7, 0.2, 0.2, 0.2, 0.7, 2.0, 8.1, 24.0, 170.0],
            ],
            16 => [
                'low'    => [16.0,9.0,2.0,1.4,1.4,1.2,1.1,1.0,0.5,1.0,1.1,1.2,1.4,1.4,2.0,9.0,16.0],
                'medium' => [110.0,41.0,10.0,5.0,3.0,1.5,1.0,0.5,0.3,0.5,1.0,1.5,3.0,5.0,10.0,41.0,110.0],
                'high'   => [1000.0,130.0,26.0,9.0,4.0,2.0,0.2,0.2,0.2,0.2,0.2,2.0,4.0,9.0,26.0,130.0,1000.0],
            ],
        ];
        return $table[$rows][$risk] ?? $table[8]['medium'];
    }

    // ==================== DICE ====================

    public function rollDice(string $serverSeed, string $clientSeed, int $nonce): float {
        return round($this->generateFloat($serverSeed, $clientSeed, $nonce) * 100, 2);
    }

    public function getDiceMultiplier(float $target, string $direction): float {
        $chance = $direction === 'under' ? $target / 100 : (100 - $target) / 100;
        if ($chance <= 0) return 0;
        return round(0.99 / $chance, 4);
    }

    // ==================== LIMBO ====================

    public function spinLimbo(string $serverSeed, string $clientSeed, int $nonce): float {
        $f = $this->generateFloat($serverSeed, $clientSeed, $nonce);
        if ($f >= 0.99) $f = 0.99;
        return max(1.0, round(0.99 / (1.0 - $f), 2));
    }

    // ==================== WALLET ====================

    public function deductBalance(int $userId, float $amount): bool {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE id = ? AND saldo >= ?");
        $stmt->execute([$amount, $userId, $amount]);
        return $stmt->rowCount() > 0;
    }

    public function addBalance(int $userId, float $amount): void {
        $this->pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?")->execute([$amount, $userId]);
    }

    public function recordBet(int $userId, string $game, float $amount, float $multiplier, float $profit, string $status, ?array $betData = null): int {
        $stmt = $this->pdo->prepare("INSERT INTO bets (user_id, game, amount, multiplier, profit, status, bet_data) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$userId, $game, $amount, $multiplier, $profit, $status, $betData ? json_encode($betData) : null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getUserSeed(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM user_seeds WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $seed = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seed) {
            $serverSeed = $this->generateServerSeed();
            $clientSeed = bin2hex(random_bytes(8));
            $hash       = $this->hashSeed($serverSeed);
            $this->pdo->prepare("INSERT INTO user_seeds (user_id, server_seed, server_seed_hash, client_seed, nonce) VALUES (?,?,?,?,0)")
                ->execute([$userId, $serverSeed, $hash, $clientSeed]);
            return ['server_seed' => $serverSeed, 'server_seed_hash' => $hash, 'client_seed' => $clientSeed, 'nonce' => 0];
        }
        return $seed;
    }

    public function incrementNonce(int $userId): void {
        $this->pdo->prepare("UPDATE user_seeds SET nonce = nonce + 1 WHERE user_id = ?")->execute([$userId]);
    }
}
