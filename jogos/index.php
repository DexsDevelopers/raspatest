<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite   = $nomeSite ?? 'BetPlay';
$isLogged   = isset($_SESSION['usuario_id']);
$saldo      = 0;
$nomeUser   = '';
if ($isLogged) {
    $s = $pdo->prepare("SELECT nome, saldo FROM usuarios WHERE id = ? LIMIT 1");
    $s->execute([$_SESSION['usuario_id']]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    $saldo    = $u['saldo'] ?? 0;
    $nomeUser = explode(' ', $u['nome'] ?? '')[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($nomeSite) ?> — Casino</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#080b14;--bg2:#0e1220;--card:#111827;--card2:#1a2235;
  --border:#1e293b;--purple:#7c3aed;--purple2:#6d28d9;
  --green:#10b981;--gold:#f59e0b;--red:#ef4444;
  --text:#e2e8f0;--muted:#64748b;
}
body{background:var(--bg);color:var(--text);font-family:'Outfit',sans-serif;min-height:100vh;overflow-x:hidden}
a{text-decoration:none;color:inherit}

/* NAVBAR */
.nav{position:sticky;top:0;z-index:100;background:rgba(8,11,20,.9);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:0 24px;height:64px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.nav-logo{font-size:1.4rem;font-weight:900;background:linear-gradient(135deg,#a78bfa,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;white-space:nowrap}
.nav-logo span{-webkit-text-fill-color:#10b981}
.nav-links{display:flex;gap:4px}
.nav-link{padding:8px 14px;border-radius:8px;font-size:.875rem;font-weight:600;color:var(--muted);transition:.2s}
.nav-link:hover,.nav-link.active{background:var(--card2);color:#fff}
.nav-link.active{color:#a78bfa}
.nav-right{display:flex;align-items:center;gap:10px}
.balance-pill{background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:8px 14px;font-size:.875rem;font-weight:700;color:var(--green);display:flex;align-items:center;gap:8px}
.btn-deposit{background:linear-gradient(135deg,var(--green),#059669);color:#fff;font-weight:700;border-radius:10px;padding:8px 18px;font-size:.875rem;border:none;cursor:pointer;transition:.2s;white-space:nowrap}
.btn-deposit:hover{opacity:.9;transform:translateY(-1px)}
.btn-login{background:var(--card2);border:1px solid var(--border);color:#fff;font-weight:600;border-radius:10px;padding:8px 16px;font-size:.875rem;cursor:pointer;transition:.2s}
.btn-login:hover{border-color:var(--purple)}

/* HERO */
.hero{position:relative;padding:48px 24px 32px;max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:center}
.hero-text h1{font-size:3rem;font-weight:900;line-height:1.1;margin-bottom:16px}
.hero-text h1 .accent{background:linear-gradient(135deg,#a78bfa,#7c3aed,#10b981);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero-text p{color:var(--muted);font-size:1.1rem;margin-bottom:28px;line-height:1.6}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap}
.btn-hero-primary{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;font-weight:700;border-radius:12px;padding:14px 28px;font-size:1rem;border:none;cursor:pointer;transition:.2s}
.btn-hero-primary:hover{opacity:.9;transform:translateY(-2px)}
.btn-hero-sec{background:var(--card2);border:1px solid var(--border);color:#fff;font-weight:600;border-radius:12px;padding:14px 28px;font-size:1rem;cursor:pointer;transition:.2s}
.hero-card{background:linear-gradient(135deg,#0e1220,#1a1f35);border:1px solid var(--border);border-radius:24px;padding:28px;position:relative;overflow:hidden}
.hero-card::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 30%,rgba(124,58,237,.15),transparent 60%)}
.live-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#ef4444;font-size:.75rem;font-weight:700;padding:4px 10px;border-radius:999px;margin-bottom:16px}
.live-dot{width:7px;height:7px;border-radius:50%;background:#ef4444;animation:pulse 1.5s infinite}
.mult-hero{font-size:4rem;font-weight:900;color:#10b981;text-shadow:0 0 40px rgba(16,185,129,.4);margin:8px 0}
.crash-chart-mini{height:80px;width:100%;background:linear-gradient(to right,transparent,rgba(16,185,129,.1));border-radius:8px;position:relative;overflow:hidden;margin:16px 0}
.crash-line{position:absolute;bottom:0;left:0;width:100%;height:2px;background:linear-gradient(to right,#10b981,#059669);box-shadow:0 0 8px #10b981}

/* STATS TICKER */
.ticker{background:var(--card2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:10px 0;overflow:hidden;margin-bottom:0}
.ticker-inner{display:flex;gap:48px;animation:scroll 30s linear infinite;white-space:nowrap}
.ticker-item{display:flex;align-items:center;gap:10px;font-size:.8rem;font-weight:600}
.ticker-name{color:var(--muted)}
.ticker-win{color:var(--green)}
.ticker-game{color:var(--purple);opacity:.8;font-size:.75rem}
@keyframes scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* STATS BAR */
.stats-bar{display:flex;gap:16px;margin-bottom:40px;flex-wrap:wrap}
.stat-chip{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:12px 20px;display:flex;align-items:center;gap:10px;font-size:.875rem}
.stat-icon{font-size:1.1rem}
.stat-val{font-weight:700;color:#fff}
.stat-label{color:var(--muted);font-size:.75rem}

/* SECTION */
.section{max-width:1280px;margin:0 auto;padding:32px 24px}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.section-title{font-size:1.3rem;font-weight:800;display:flex;align-items:center;gap:10px}
.section-title .icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem}
.see-all{color:var(--purple);font-size:.875rem;font-weight:600}

/* FILTER TABS */
.filter-tabs{display:flex;gap:8px;margin-bottom:28px;overflow-x:auto;pb-2}
.filter-tab{padding:8px 18px;border-radius:999px;font-size:.875rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--muted);transition:.2s;white-space:nowrap}
.filter-tab:hover{border-color:var(--purple);color:#fff}
.filter-tab.active{background:var(--purple);border-color:var(--purple);color:#fff}

/* GAME CARDS */
.games-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}
.game-card{background:var(--card);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:.3s;cursor:pointer;position:relative;group}
.game-card:hover{transform:translateY(-6px);border-color:var(--purple);box-shadow:0 20px 50px rgba(124,58,237,.25)}
.game-thumb{height:140px;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
.game-thumb .emoji{font-size:4rem;position:relative;z-index:1;transition:.3s}
.game-card:hover .emoji{transform:scale(1.15)}
.game-overlay{position:absolute;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;opacity:0;transition:.3s}
.game-card:hover .game-overlay{opacity:1}
.play-btn{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;font-weight:700;border-radius:10px;padding:10px 24px;font-size:.875rem;border:none;cursor:pointer}
.game-info{padding:14px}
.game-name{font-size:1rem;font-weight:700;margin-bottom:4px}
.game-meta{display:flex;align-items:center;justify-content:space-between}
.game-players{font-size:.75rem;color:var(--muted);display:flex;align-items:center;gap:4px}
.game-badge{font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:999px}
.badge-hot{background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff}
.badge-new{background:linear-gradient(135deg,#10b981,#059669);color:#fff}
.badge-exclusive{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff}

/* Crash thumb */
.thumb-crash{background:linear-gradient(135deg,#0a1628,#0d2240)}
.thumb-crash::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 60% 70%,rgba(16,185,129,.3),transparent 60%)}
/* Mines thumb */
.thumb-mines{background:linear-gradient(135deg,#1a0f0a,#2d1810)}
.thumb-mines::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 40% 50%,rgba(239,68,68,.2),transparent 60%)}
/* Plinko thumb */
.thumb-plinko{background:linear-gradient(135deg,#120a2a,#1e0f40)}
.thumb-plinko::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 30%,rgba(124,58,237,.3),transparent 60%)}
/* Dice thumb */
.thumb-dice{background:linear-gradient(135deg,#0a1a28,#0f2035)}
.thumb-dice::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 50%,rgba(59,130,246,.25),transparent 60%)}
/* Limbo thumb */
.thumb-limbo{background:linear-gradient(135deg,#1a0a20,#2a1040)}
.thumb-limbo::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 50%,rgba(167,139,250,.2),transparent 60%)}
/* Raspadinha thumb */
.thumb-raspa{background:linear-gradient(135deg,#1a1208,#2a1e08)}
.thumb-raspa::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 50%,rgba(245,158,11,.2),transparent 60%)}

/* PROMO BANNER */
.promo-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:32px}
.promo-card{border-radius:20px;padding:28px;position:relative;overflow:hidden;min-height:130px;display:flex;flex-direction:column;justify-content:flex-end}
.promo-card h3{font-size:1.2rem;font-weight:800;margin-bottom:4px;position:relative}
.promo-card p{font-size:.8rem;color:rgba(255,255,255,.7);position:relative}
.promo-1{background:linear-gradient(135deg,#1e1040,#7c3aed)}
.promo-2{background:linear-gradient(135deg,#0a2818,#10b981)}
.promo-badge{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.15);border-radius:8px;padding:6px 12px;font-size:.75rem;font-weight:700}

/* JACKPOT BAR */
.jackpot-bar{background:linear-gradient(135deg,#1a0f30,#2d1f50);border:1px solid rgba(124,58,237,.3);border-radius:16px;padding:20px 28px;display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;gap:16px;flex-wrap:wrap}
.jackpot-label{font-size:.8rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em}
.jackpot-value{font-size:2rem;font-weight:900;color:var(--gold);text-shadow:0 0 20px rgba(245,158,11,.4)}
.jackpot-ticker{font-size:.75rem;color:rgba(255,255,255,.5);margin-top:2px}

/* FOOTER */
footer{background:var(--bg2);border-top:1px solid var(--border);padding:40px 24px 24px;margin-top:48px}
.footer-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:32px}
.footer-brand .logo{font-size:1.3rem;font-weight:900;background:linear-gradient(135deg,#a78bfa,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:12px}
.footer-brand p{color:var(--muted);font-size:.85rem;line-height:1.7;max-width:240px}
.footer-col h4{font-size:.875rem;font-weight:700;margin-bottom:14px;color:#fff}
.footer-col a{display:block;color:var(--muted);font-size:.85rem;margin-bottom:8px;transition:.2s}
.footer-col a:hover{color:#fff}
.footer-bottom{display:flex;align-items:center;justify-content:space-between;padding-top:24px;border-top:1px solid var(--border);flex-wrap:gap;gap:12px}
.footer-bottom p{color:var(--muted);font-size:.8rem}
.age-badge{background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:.8rem;font-weight:700;color:var(--muted)}
.resp-icons{display:flex;gap:8px}
.resp-icon{width:32px;height:32px;background:var(--card2);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.75rem;color:var(--muted)}

/* MOBILE */
@media(max-width:768px){
  .hero{grid-template-columns:1fr;gap:24px}
  .hero-text h1{font-size:2rem}
  .promo-grid{grid-template-columns:1fr}
  .footer-inner{grid-template-columns:1fr 1fr;gap:24px}
  .nav-links{display:none}
  .games-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr))}
}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
@keyframes count-up{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="nav">
  <a href="/jogos/" class="nav-logo"><?= htmlspecialchars($nomeSite) ?><span>.</span></a>
  <div class="nav-links">
    <a href="/jogos/" class="nav-link active"><i class="fas fa-gamepad" style="margin-right:6px"></i>Cassino</a>
    <a href="/"       class="nav-link"><i class="fas fa-ticket-alt" style="margin-right:6px"></i>Raspadinhas</a>
    <a href="/afiliados/" class="nav-link"><i class="fas fa-users" style="margin-right:6px"></i>Afiliados</a>
  </div>
  <div class="nav-right">
    <?php if ($isLogged): ?>
      <div class="balance-pill"><i class="fas fa-wallet"></i> R$ <?= number_format($saldo,2,',','.') ?></div>
      <a href="/" class="btn-deposit">+ Depositar</a>
      <span style="color:var(--muted);font-size:.875rem"><?= htmlspecialchars($nomeUser) ?></span>
    <?php else: ?>
      <a href="/login/" class="btn-login">Entrar</a>
      <a href="/cadastro/" class="btn-deposit">Cadastrar</a>
    <?php endif; ?>
  </div>
</nav>

<!-- WIN TICKER -->
<div class="ticker">
  <div class="ticker-inner" id="tickerInner"></div>
</div>

<div class="section">

  <!-- JACKPOT -->
  <div class="jackpot-bar">
    <div>
      <div class="jackpot-label">🏆 Jackpot Acumulado</div>
      <div class="jackpot-value" id="jackpotVal">R$ 0,00</div>
      <div class="jackpot-ticker">Atualizado em tempo real</div>
    </div>
    <div style="display:flex;gap:32px;flex-wrap:wrap">
      <div><div class="jackpot-label">Jogadores Online</div><div style="font-size:1.5rem;font-weight:800;color:#a78bfa" id="onlineCount">-</div></div>
      <div><div class="jackpot-label">Ganhos (24h)</div><div style="font-size:1.5rem;font-weight:800;color:var(--green)" id="winsToday">-</div></div>
      <div><div class="jackpot-label">Rodadas (24h)</div><div style="font-size:1.5rem;font-weight:800;color:var(--gold)" id="roundsToday">-</div></div>
    </div>
  </div>

  <!-- PROMO BANNERS -->
  <div class="promo-grid">
    <div class="promo-card promo-1">
      <div style="position:absolute;right:20px;top:50%;transform:translateY(-50%);font-size:5rem;opacity:.3">🚀</div>
      <span class="promo-badge">⚡ AO VIVO</span>
      <h3>Bônus de 100% no 1º Depósito</h3>
      <p>Até R$ 500 em créditos + 50 giros grátis</p>
    </div>
    <div class="promo-card promo-2">
      <div style="position:absolute;right:20px;top:50%;transform:translateY(-50%);font-size:5rem;opacity:.3">💎</div>
      <span class="promo-badge">🎁 OFERTA</span>
      <h3>Cashback de 10% Todo Domingo</h3>
      <p>Recupere parte das suas perdas semanais</p>
    </div>
  </div>

  <!-- FILTER TABS -->
  <div class="filter-tabs">
    <button class="filter-tab active" onclick="filterGames('all',this)">🎮 Todos</button>
    <button class="filter-tab" onclick="filterGames('hot',this)">🔥 Populares</button>
    <button class="filter-tab" onclick="filterGames('new',this)">✨ Novos</button>
    <button class="filter-tab" onclick="filterGames('exclusive',this)">💜 Exclusivos</button>
    <button class="filter-tab" onclick="filterGames('raspa',this)">🎟️ Raspadinhas</button>
  </div>

  <!-- GAMES GRID -->
  <div class="games-grid" id="gamesGrid">

    <a href="/jogos/tiger.php?t=tiger" class="game-card" data-tags="hot exclusive">
      <div class="game-thumb" style="background:linear-gradient(135deg,#3a0000,#7a0000)">
        <span class="emoji">🐯</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        <div style="position:absolute;top:10px;left:10px;font-size:.65rem;font-weight:700;background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.5);color:#f59e0b;padding:3px 8px;border-radius:999px">PG STYLE</div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Fortune Tiger</div>
          <span class="game-badge badge-hot">HOT</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="3241">3.241</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/tiger.php?t=rabbit" class="game-card" data-tags="hot">
      <div class="game-thumb" style="background:linear-gradient(135deg,#2a0050,#7a0080)">
        <span class="emoji">🐰</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        <div style="position:absolute;top:10px;left:10px;font-size:.65rem;font-weight:700;background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.5);color:#f59e0b;padding:3px 8px;border-radius:999px">PG STYLE</div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Fortune Rabbit</div>
          <span class="game-badge badge-hot">HOT</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="2180">2.180</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/tiger.php?t=dragon" class="game-card" data-tags="new exclusive">
      <div class="game-thumb" style="background:linear-gradient(135deg,#001a4a,#002080)">
        <span class="emoji">🐉</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        <div style="position:absolute;top:10px;left:10px;font-size:.65rem;font-weight:700;background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.5);color:#f59e0b;padding:3px 8px;border-radius:999px">PG STYLE</div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Fortune Dragon</div>
          <span class="game-badge badge-new">NEW</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="1560">1.560</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/crash.php" class="game-card" data-tags="hot exclusive">
      <div class="game-thumb thumb-crash">
        <span class="emoji">🚀</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        <div style="position:absolute;top:10px;left:10px;display:flex;align-items:center;gap:5px;background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.4);color:#ef4444;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:999px">
          <span style="width:5px;height:5px;border-radius:50%;background:#ef4444;animation:pulse 1.5s infinite;display:block"></span>AO VIVO
        </div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Aviator / Crash</div>
          <span class="game-badge badge-hot">HOT</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="847">847</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/mines.php" class="game-card" data-tags="hot">
      <div class="game-thumb thumb-mines">
        <span class="emoji">💣</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Mines</div>
          <span class="game-badge badge-hot">HOT</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="1243">1.243</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/plinko.php" class="game-card" data-tags="new exclusive">
      <div class="game-thumb thumb-plinko">
        <span class="emoji">🎯</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Plinko</div>
          <span class="game-badge badge-new">NEW</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="432">432</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/dice.php" class="game-card" data-tags="new">
      <div class="game-thumb thumb-dice">
        <span class="emoji">🎲</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Dice</div>
          <span class="game-badge badge-new">NEW</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="615">615</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/jogos/limbo.php" class="game-card" data-tags="new exclusive">
      <div class="game-thumb thumb-limbo">
        <span class="emoji">🌀</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Limbo</div>
          <span class="game-badge badge-exclusive">EXCLUSIVO</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="289">289</span> jogando</div>
        </div>
      </div>
    </a>

    <a href="/" class="game-card" data-tags="raspa hot">
      <div class="game-thumb thumb-raspa">
        <span class="emoji">🎟️</span>
        <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
      </div>
      <div class="game-info">
        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:4px">
          <div class="game-name">Raspadinhas</div>
          <span class="game-badge badge-hot">HOT</span>
        </div>
        <div class="game-meta">
          <div class="game-players"><i class="fas fa-users" style="font-size:.65rem"></i> <span class="player-count" data-base="2100">2.100</span> jogando</div>
        </div>
      </div>
    </a>

  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="logo"><?= htmlspecialchars($nomeSite) ?><span style="-webkit-text-fill-color:#10b981">.</span></div>
      <p>Plataforma de jogos online com resultados provably fair e auditáveis. Jogue com responsabilidade.</p>
    </div>
    <div class="footer-col">
      <h4>Jogos</h4>
      <a href="/jogos/tiger.php?t=tiger">Fortune Tiger</a>
      <a href="/jogos/tiger.php?t=rabbit">Fortune Rabbit</a>
      <a href="/jogos/tiger.php?t=dragon">Fortune Dragon</a>
      <a href="/jogos/crash.php">Aviator / Crash</a>
      <a href="/jogos/mines.php">Mines</a>
      <a href="/jogos/plinko.php">Plinko</a>
      <a href="/jogos/dice.php">Dice</a>
      <a href="/jogos/limbo.php">Limbo</a>
    </div>
    <div class="footer-col">
      <h4>Conta</h4>
      <a href="/perfil/">Meu Perfil</a>
      <a href="/transacoes/">Transações</a>
      <a href="/afiliados/">Afiliados</a>
      <a href="/logout/">Sair</a>
    </div>
    <div class="footer-col">
      <h4>Suporte</h4>
      <a href="#">Central de Ajuda</a>
      <a href="#">Jogo Responsável</a>
      <a href="/politica/">Privacidade</a>
      <a href="#">Termos de Uso</a>
    </div>
  </div>
  <div class="footer-bottom" style="max-width:1280px;margin:0 auto">
    <p>© 2025 <?= htmlspecialchars($nomeSite) ?>. Todos os direitos reservados. +18</p>
    <div style="display:flex;align-items:center;gap:12px">
      <span class="age-badge">🔞 +18</span>
      <span class="age-badge">🎲 Jogo Responsável</span>
    </div>
  </div>
</footer>

<script>
// ── Win Ticker ──────────────────────────────────────────────────────
const names  = ['Lucas S.','Ana P.','Carlos R.','Beatriz M.','Felipe A.','Mariana L.','João V.','Camila F.','Pedro H.','Letícia O.'];
const games  = ['Crash','Mines','Plinko','Dice','Limbo','Raspadinha'];
const colors = ['#10b981','#f59e0b','#a78bfa','#ef4444','#60a5fa'];

function buildTicker() {
  const container = document.getElementById('tickerInner');
  let html = '';
  for (let i = 0; i < 16; i++) {
    const name  = names[Math.floor(Math.random()*names.length)];
    const game  = games[Math.floor(Math.random()*games.length)];
    const win   = (Math.random()*4800+20).toFixed(2);
    const mult  = (Math.random()*18+1.5).toFixed(2);
    const color = colors[Math.floor(Math.random()*colors.length)];
    html += `<div class="ticker-item"><span class="ticker-name">${name}</span><span class="ticker-win" style="color:${color}">+R$ ${parseFloat(win).toLocaleString('pt-BR',{minimumFractionDigits:2})}</span><span class="ticker-game">${game} ${mult}x</span></div>`;
  }
  container.innerHTML = html + html;
}
buildTicker();

// ── Jackpot & Stats ──────────────────────────────────────────────────
let jackpot  = 187432.50 + Math.random()*2000;
let online   = 1847 + Math.floor(Math.random()*200);
let wins24   = 94832;
let rounds24 = 28741;

function fmt(n) { return n.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function fmtInt(n){ return n.toLocaleString('pt-BR'); }

function updateStats() {
  jackpot  += Math.random() * 15;
  online   += Math.floor(Math.random()*6 - 3);
  wins24   += Math.random() * 8;
  rounds24 += Math.floor(Math.random()*3);
  document.getElementById('jackpotVal').textContent  = 'R$ ' + fmt(jackpot);
  document.getElementById('onlineCount').textContent = fmtInt(online);
  document.getElementById('winsToday').textContent   = 'R$ ' + fmt(wins24);
  document.getElementById('roundsToday').textContent = fmtInt(rounds24);
}
updateStats();
setInterval(updateStats, 2000);

// ── Player Counts ────────────────────────────────────────────────────
document.querySelectorAll('.player-count').forEach(el => {
  const base = parseInt(el.dataset.base);
  setInterval(() => {
    const v = base + Math.floor(Math.random()*40 - 20);
    el.textContent = v.toLocaleString('pt-BR');
  }, 3000 + Math.random()*2000);
});

// ── Filter ───────────────────────────────────────────────────────────
function filterGames(tag, btn) {
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.game-card').forEach(card => {
    const tags = card.dataset.tags || '';
    card.style.display = (tag === 'all' || tags.includes(tag)) ? '' : 'none';
  });
}
</script>
</body>
</html>
