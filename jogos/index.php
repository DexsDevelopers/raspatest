<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$nomeSite = $nomeSite ?? 'RaspaPix';
$isLogged = isset($_SESSION['usuario_id']);
$saldo = 0; $nomeUser = '';
if ($isLogged) {
    $s = $pdo->prepare("SELECT nome, saldo FROM usuarios WHERE id=? LIMIT 1");
    $s->execute([$_SESSION['usuario_id']]);
    $u = $s->fetch(PDO::FETCH_ASSOC);
    $saldo = $u['saldo'] ?? 0;
    $nomeUser = explode(' ', $u['nome'] ?? '')[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>🍀 RaspaPix — Cassino</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#0e0e16;--bg2:#13131e;--card:#1a1b28;--card2:#22233a;
  --border:rgba(255,255,255,.07);--green:#22c55e;--green2:#16a34a;
  --text:#e2e4f0;--muted:#6b7280;--gold:#f0c040;--red:#ef4444;
}
body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;overflow-x:hidden}
a{text-decoration:none;color:inherit}
img{max-width:100%}

/* ═══ ANNOUNCE ═══ */
.announce{background:#16a34a;text-align:center;padding:8px 40px;font-size:.8rem;font-weight:600;position:relative;display:flex;align-items:center;justify-content:center;gap:10px}
.announce-btn{background:rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:20px;padding:3px 14px;font-size:.75rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.15s}
.announce-btn:hover{background:rgba(0,0,0,.35)}
.announce-x{position:absolute;right:14px;background:none;border:none;color:rgba(255,255,255,.6);font-size:1.1rem;cursor:pointer;line-height:1;padding:4px}

/* ═══ NAV ═══ */
.nav{height:60px;background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 20px;gap:14px;position:sticky;top:0;z-index:200}
.logo{font-size:1.3rem;font-weight:800;color:#fff;white-space:nowrap;letter-spacing:-.01em;display:flex;align-items:center;gap:4px}
.logo .dot{color:var(--green);font-size:1.6rem;line-height:0;position:relative;top:3px}
.nav-sep{width:1px;height:24px;background:var(--border);flex-shrink:0}
.search-wrap{flex:1;max-width:320px;position:relative}
.search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem}
.search-input{width:100%;background:var(--card);border:1px solid var(--border);border-radius:8px;padding:8px 12px 8px 34px;color:var(--text);font-size:.85rem;font-family:inherit;outline:none;transition:.2s}
.search-input:focus{border-color:rgba(34,197,94,.4);background:var(--card2)}
.search-input::placeholder{color:var(--muted)}
.nav-links{display:flex;gap:2px;margin-left:4px}
.nav-link{padding:7px 13px;border-radius:7px;font-size:.85rem;font-weight:600;color:var(--muted);transition:.2s;white-space:nowrap;display:flex;align-items:center;gap:6px}
.nav-link:hover{color:#fff;background:rgba(255,255,255,.06)}
.nav-link.active{color:#fff;background:rgba(34,197,94,.12);color:var(--green)}
.nav-right{margin-left:auto;display:flex;align-items:center;gap:8px;flex-shrink:0}
.user-pill{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:.8rem;font-weight:600;display:flex;align-items:center;gap:8px}
.user-balance{color:var(--green);font-weight:700}
.btn-enter{background:transparent;border:1px solid rgba(255,255,255,.15);color:var(--text);border-radius:8px;padding:7px 16px;font-size:.85rem;font-weight:600;cursor:pointer;font-family:inherit;transition:.2s}
.btn-enter:hover{border-color:rgba(255,255,255,.3);color:#fff}
.btn-register{background:var(--green);color:#000;border:none;border-radius:8px;padding:7px 18px;font-size:.85rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.2s}
.btn-register:hover{background:#1eb356}
.btn-deposit{background:var(--green);color:#000;border:none;border-radius:8px;padding:7px 18px;font-size:.85rem;font-weight:700;cursor:pointer;font-family:inherit;transition:.2s}
.btn-deposit:hover{background:#1eb356}

/* ═══ TICKER ═══ */
.ticker-wrap{background:var(--bg2);border-bottom:1px solid var(--border);overflow:hidden;height:36px;display:flex;align-items:center}
.ticker-track{display:flex;gap:0;animation:ticker-scroll 40s linear infinite;white-space:nowrap}
.ticker-track:hover{animation-play-state:paused}
.ticker-item{display:inline-flex;align-items:center;gap:6px;padding:0 20px;font-size:.75rem;font-weight:600;color:var(--muted);border-right:1px solid var(--border)}
.ticker-item .game-tag{color:rgba(255,255,255,.5);font-weight:500}
.ticker-item .amount{color:var(--green);font-weight:700}
@keyframes ticker-scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* ═══ LAYOUT ═══ */
.page{display:grid;grid-template-columns:220px 1fr;min-height:calc(100vh - 96px)}
@media(max-width:900px){.page{grid-template-columns:1fr}.sidebar{display:none}}

/* ═══ SIDEBAR ═══ */
.sidebar{background:var(--bg2);border-right:1px solid var(--border);padding:16px 0;position:sticky;top:60px;height:calc(100vh - 60px);overflow-y:auto}
.sidebar::-webkit-scrollbar{width:4px}
.sidebar::-webkit-scrollbar-track{background:transparent}
.sidebar::-webkit-scrollbar-thumb{background:var(--card2);border-radius:4px}
.sb-section{padding:0 10px 14px}
.sb-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);padding:0 8px 8px;display:block}
.sb-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;font-size:.85rem;font-weight:500;color:rgba(255,255,255,.6);cursor:pointer;transition:.15s;text-decoration:none}
.sb-item:hover{background:rgba(255,255,255,.06);color:#fff}
.sb-item.active{background:rgba(34,197,94,.1);color:var(--green)}
.sb-item i{width:18px;text-align:center;font-size:.9rem}
.sb-divider{height:1px;background:var(--border);margin:8px 10px}
.sb-count{margin-left:auto;background:rgba(255,255,255,.08);border-radius:20px;padding:1px 7px;font-size:.68rem;font-weight:700;color:rgba(255,255,255,.4)}

/* ═══ MAIN ═══ */
.main{padding:24px 20px;overflow-x:hidden}

/* ═══ STATS BAR ═══ */
.stats-bar{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px 28px;display:flex;align-items:center;gap:0;margin-bottom:24px;overflow:hidden}
.stats-jackpot{flex:1;border-right:1px solid var(--border);padding-right:28px}
.sj-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);display:flex;align-items:center;gap:6px;margin-bottom:4px}
.sj-label i{color:var(--gold)}
.sj-value{font-size:2.2rem;font-weight:900;color:var(--gold);letter-spacing:-.03em;line-height:1}
.sj-sub{font-size:.72rem;color:var(--muted);margin-top:4px}
.stats-grid{display:flex;gap:0;flex:1;padding-left:28px}
.stat-item{flex:1;text-align:center;border-right:1px solid var(--border);padding:0 20px}
.stat-item:last-child{border-right:none}
.stat-lbl{font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:4px}
.stat-val{font-size:1.4rem;font-weight:800;color:#fff}
.stat-val.green{color:var(--green)}
@media(max-width:700px){.stats-bar{flex-direction:column;gap:16px}.stats-jackpot{border-right:none;padding-right:0;border-bottom:1px solid var(--border);padding-bottom:16px;width:100%}.stats-grid{padding-left:0;width:100%}}

/* ═══ PROMO CARDS ═══ */
.promo-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px}
@media(max-width:600px){.promo-grid{grid-template-columns:1fr}}
.promo-card{border-radius:14px;padding:24px 22px;position:relative;overflow:hidden;min-height:110px;display:flex;flex-direction:column;justify-content:flex-end;cursor:pointer;transition:transform .2s}
.promo-card:hover{transform:translateY(-2px)}
.promo-card h3{font-size:1.05rem;font-weight:800;margin-bottom:3px;position:relative;z-index:2}
.promo-card p{font-size:.78rem;color:rgba(255,255,255,.65);position:relative;z-index:2;line-height:1.4}
.promo-badge{position:absolute;top:14px;right:14px;border-radius:6px;padding:4px 10px;font-size:.68rem;font-weight:800;z-index:2;letter-spacing:.03em}
.promo-deco{position:absolute;right:-10px;bottom:-10px;font-size:6rem;opacity:.15;z-index:1;line-height:1}
.p1{background:linear-gradient(135deg,#1a0a40,#4a1a8a,#6d28d9)}
.p1 .promo-badge{background:rgba(255,255,255,.15);color:#fff}
.p2{background:linear-gradient(135deg,#052e16,#0d5c2e,#16a34a)}
.p2 .promo-badge{background:rgba(0,0,0,.2);color:var(--green)}

/* ═══ GAME SECTION ═══ */
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.section-title{font-size:1rem;font-weight:700;display:flex;align-items:center;gap:8px}
.section-more{font-size:.8rem;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:4px;cursor:pointer;transition:.15s}
.section-more:hover{color:#fff}

/* Filter tabs */
.filter-tabs{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap}
.ftab{display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:8px;border:1px solid var(--border);background:var(--card);color:var(--muted);font-size:.82rem;font-weight:600;cursor:pointer;transition:.2s;font-family:inherit;white-space:nowrap}
.ftab:hover{color:#fff;border-color:rgba(255,255,255,.18)}
.ftab.active{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.3);color:var(--green)}
.ftab i{font-size:.75rem}

/* ═══ GAME GRID ═══ */
.games-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
@media(max-width:600px){.games-grid{grid-template-columns:repeat(2,1fr);gap:10px}}

/* ═══ GAME CARD ═══ */
.game-card{border-radius:12px;overflow:hidden;background:var(--card);border:1px solid var(--border);cursor:pointer;transition:transform .2s,box-shadow .2s;text-decoration:none;display:block}
.game-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.1)}
.game-card[data-hidden="true"]{display:none}
/* Thumbnail */
.game-thumb{position:relative;padding-top:110%;overflow:hidden}
.game-thumb-inner{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;overflow:hidden}
.game-bg{position:absolute;inset:0;background:var(--thumb-bg,#111)}
/* Bottom glow source */
.game-bg::after{content:'';position:absolute;bottom:-20px;left:50%;transform:translateX(-50%);width:140%;height:80%;border-radius:50%;background:radial-gradient(circle,var(--glow-color,rgba(255,255,255,.1)),transparent 70%);filter:blur(20px)}
/* Top vignette */
.game-bg::before{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.35) 0%,transparent 40%,rgba(0,0,0,.2) 100%);z-index:1}
.game-emoji{position:relative;z-index:2;font-size:72px;line-height:1;filter:drop-shadow(0 4px 20px var(--shadow-color,rgba(255,255,255,.3)));transition:transform .3s}
.game-card:hover .game-emoji{transform:scale(1.1) translateY(-4px)}
/* Provider badge */
.game-provider{position:absolute;top:8px;left:8px;z-index:5;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);border-radius:5px;padding:2px 8px;font-size:.6rem;font-weight:700;color:rgba(255,255,255,.7);letter-spacing:.04em}
/* Badge */
.game-badge{position:absolute;top:8px;right:8px;z-index:5;border-radius:5px;padding:3px 8px;font-size:.62rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.badge-hot{background:#ef4444;color:#fff}
.badge-new{background:#f59e0b;color:#000}
.badge-live{background:rgba(239,68,68,.2);color:#ef4444;border:1px solid rgba(239,68,68,.4);display:flex;align-items:center;gap:4px}
.live-dot{width:5px;height:5px;border-radius:50%;background:#ef4444;animation:live-pulse 1.4s infinite}
@keyframes live-pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.8)}}
/* Hover overlay */
.game-overlay{position:absolute;inset:0;z-index:10;background:rgba(0,0,0,.65);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s}
.game-card:hover .game-overlay{opacity:1}
.play-btn{background:var(--green);color:#000;border:none;border-radius:8px;padding:9px 22px;font-size:.85rem;font-weight:800;cursor:pointer;font-family:inherit;transform:translateY(6px);transition:transform .2s}
.game-card:hover .play-btn{transform:translateY(0)}
/* Info bar */
.game-info{padding:10px 12px 12px}
.game-name{font-size:.85rem;font-weight:700;color:#fff;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.game-meta{display:flex;align-items:center;justify-content:space-between}
.game-live-count{font-size:.7rem;color:var(--muted);display:flex;align-items:center;gap:4px}
.live-dot-sm{width:4px;height:4px;border-radius:50%;background:var(--green);display:inline-block;animation:live-pulse 1.4s infinite}

/* ═══ PROVIDERS ═══ */
.providers-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:28px}
.provider-card{background:var(--card);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;cursor:pointer;transition:.2s}
.provider-card:hover{background:var(--card2);border-color:rgba(255,255,255,.15)}
.provider-card .prov-icon{font-size:1.8rem;margin-bottom:4px}
.provider-card .prov-name{font-size:.72rem;font-weight:600;color:var(--muted)}

/* ═══ FOOTER ═══ */
footer{background:var(--bg2);border-top:1px solid var(--border);padding:40px 20px 24px;margin-top:40px}
.footer-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:32px}
@media(max-width:700px){.footer-grid{grid-template-columns:1fr 1fr;gap:20px}}
.f-logo{font-size:1.2rem;font-weight:800;color:#fff;margin-bottom:10px;display:flex;align-items:center;gap:4px}
.f-logo .dot{color:var(--green);font-size:1.5rem;line-height:0;top:3px;position:relative}
.f-desc{font-size:.82rem;color:var(--muted);line-height:1.7;max-width:220px}
.f-col h4{font-size:.8rem;font-weight:700;color:#fff;margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em}
.f-col a{display:block;font-size:.82rem;color:var(--muted);margin-bottom:7px;transition:.15s}
.f-col a:hover{color:#fff}
.footer-bottom{max-width:1200px;margin:0 auto;border-top:1px solid var(--border);padding-top:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.footer-bottom p{font-size:.78rem;color:var(--muted)}
.footer-badges{display:flex;gap:8px}
.fbadge{background:var(--card);border:1px solid var(--border);border-radius:6px;padding:5px 10px;font-size:.72rem;font-weight:700;color:var(--muted)}

/* Scrollbar */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--card2);border-radius:4px}
</style>
</head>
<body>

<!-- ANNOUNCE BAR -->
<div class="announce" id="abar">
  <i class="fas fa-download" style="font-size:.75rem"></i>
  <span>Baixe nosso app e ganhe muitos pontos!</span>
  <button class="announce-btn">Baixar</button>
  <button class="announce-x" onclick="document.getElementById('abar').remove()">×</button>
</div>

<!-- NAV -->
<nav class="nav">
  <a href="/jogos/" class="logo"><img src="<?=htmlspecialchars($logoSite??'')?>" alt="RaspaPix" style="height:38px;object-fit:contain;display:block"></a>
  <div class="nav-sep"></div>
  <div class="search-wrap">
    <i class="fas fa-search"></i>
    <input type="text" class="search-input" placeholder="Buscar jogos..." id="searchInput" oninput="filterSearch(this.value)">
  </div>
  <div class="nav-links">
    <a href="/jogos/" class="nav-link active"><i class="fas fa-dice"></i> Cassino</a>
    <a href="/" class="nav-link"><i class="fas fa-ticket-alt"></i> Raspadinhas</a>
    <a href="/afiliados/" class="nav-link"><i class="fas fa-users"></i> Afiliados</a>
  </div>
  <div class="nav-right">
    <?php if ($isLogged): ?>
      <div class="user-pill">
        <i class="fas fa-wallet" style="color:var(--muted);font-size:.8rem"></i>
        <span class="user-balance" id="navBal">R$ <?= number_format($saldo,2,',','.') ?></span>
        <span style="color:var(--muted);font-size:.8rem"><?= htmlspecialchars($nomeUser) ?></span>
      </div>
      <button class="btn-deposit" onclick="location.href='/pages/deposit.php'">+ Depositar</button>
    <?php else: ?>
      <button class="btn-enter" onclick="location.href='/login.php'">Entrar</button>
      <button class="btn-register" onclick="location.href='/login.php?tab=register'">Cadastrar</button>
    <?php endif; ?>
  </div>
</nav>

<!-- TICKER -->
<div class="ticker-wrap">
  <div class="ticker-track" id="ticker"></div>
</div>

<div class="page">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sb-section">
      <span class="sb-label">Cassino</span>
      <a href="/jogos/" class="sb-item active"><i class="fas fa-home"></i> Início <span class="sb-count">9</span></a>
      <a href="#" class="sb-item" onclick="filterGames('hot',null);return false"><i class="fas fa-fire" style="color:#ef4444"></i> Populares</a>
      <a href="#" class="sb-item" onclick="filterGames('new',null);return false"><i class="fas fa-star" style="color:#f59e0b"></i> Novos</a>
      <a href="#" class="sb-item" onclick="filterGames('exclusive',null);return false"><i class="fas fa-gem" style="color:#a78bfa"></i> Exclusivos</a>
    </div>
    <div class="sb-divider"></div>
    <div class="sb-section">
      <span class="sb-label">Jogos Originais</span>
      <a href="/jogos/tiger.php?t=tiger" class="sb-item"><span style="font-size:1rem">🐯</span> Fortune Tiger</a>
      <a href="/jogos/tiger.php?t=rabbit" class="sb-item"><span style="font-size:1rem">🐰</span> Fortune Rabbit</a>
      <a href="/jogos/tiger.php?t=dragon" class="sb-item"><span style="font-size:1rem">🐉</span> Fortune Dragon</a>
      <a href="/jogos/aviator.php" class="sb-item"><span style="font-size:1rem">✈️</span> Aviator</a>
      <a href="/jogos/crash.php" class="sb-item"><span style="font-size:1rem">🚀</span> Crash</a>
      <a href="/jogos/mines.php" class="sb-item"><span style="font-size:1rem">💣</span> Mines</a>
      <a href="/jogos/plinko.php" class="sb-item"><span style="font-size:1rem">🔴</span> Plinko</a>
      <a href="/jogos/dice.php" class="sb-item"><span style="font-size:1rem">🎲</span> Dice</a>
      <a href="/jogos/limbo.php" class="sb-item"><span style="font-size:1rem">🌀</span> Limbo</a>
    </div>
    <div class="sb-divider"></div>
    <div class="sb-section">
      <span class="sb-label">Provedores</span>
      <a href="#" class="sb-item"><i class="fas fa-circle" style="color:#f59e0b;font-size:.5rem"></i> PG Style</a>
      <a href="#" class="sb-item"><i class="fas fa-shield-alt" style="color:var(--green);font-size:.75rem"></i> Provably Fair</a>
    </div>
    <div class="sb-divider"></div>
    <div class="sb-section">
      <a href="/" class="sb-item"><i class="fas fa-ticket-alt" style="color:var(--gold)"></i> Raspadinhas</a>
      <a href="/afiliados/" class="sb-item"><i class="fas fa-users" style="color:#60a5fa"></i> Afiliados</a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main">

    <!-- STATS BAR -->
    <div class="stats-bar">
      <div class="stats-jackpot">
        <div class="sj-label"><i class="fas fa-trophy"></i> JACKPOT ACUMULADO</div>
        <div class="sj-value" id="jackpotVal">R$ 0,00</div>
        <div class="sj-sub">Atualizado em tempo real</div>
      </div>
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-lbl">Jogadores Online</div>
          <div class="stat-val green" id="statOnline">—</div>
        </div>
        <div class="stat-item">
          <div class="stat-lbl">Ganhos (24h)</div>
          <div class="stat-val" id="statGanhos">—</div>
        </div>
        <div class="stat-item">
          <div class="stat-lbl">Rodadas (24h)</div>
          <div class="stat-val" id="statRodadas">—</div>
        </div>
      </div>
    </div>

    <!-- PROMO BANNERS -->
    <div class="promo-grid">
      <div class="promo-card p1">
        <div class="promo-badge">⚡ AO VIVO</div>
        <div class="promo-deco">🚀</div>
        <h3>Bônus de 100% no 1º Depósito</h3>
        <p>Até R$ 500 em créditos + 50 giros grátis</p>
      </div>
      <div class="promo-card p2">
        <div class="promo-badge">💎 OFERTA</div>
        <div class="promo-deco">💎</div>
        <h3>Cashback de 10% Todo Domingo</h3>
        <p>Recupere parte das suas perdas semanais</p>
      </div>
    </div>

    <!-- FILTER TABS -->
    <div class="filter-tabs">
      <button class="ftab active" onclick="filterGames('all',this)"><i class="fas fa-gamepad"></i> Todos</button>
      <button class="ftab" onclick="filterGames('hot',this)"><i class="fas fa-fire"></i> Populares</button>
      <button class="ftab" onclick="filterGames('new',this)"><i class="fas fa-star"></i> Novos</button>
      <button class="ftab" onclick="filterGames('exclusive',this)"><i class="fas fa-gem"></i> Exclusivos</button>
      <button class="ftab" onclick="filterGames('raspa',this)"><i class="fas fa-ticket-alt"></i> Raspadinhas</button>
    </div>

    <!-- GAMES GRID -->
    <div class="games-grid" id="gamesGrid">

      <!-- Fortune Tiger -->
      <a href="/jogos/tiger.php?t=tiger" class="game-card" data-tags="hot exclusive" data-name="fortune tiger">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#c44000,#5a0a00 55%,#1a0000);--glow-color:rgba(220,80,0,.55);--shadow-color:rgba(255,100,0,.5)"></div>
            <div class="game-emoji">🐯</div>
          </div>
          <div class="game-provider">PG STYLE</div>
          <div class="game-badge badge-hot">🔥 HOT</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Fortune Tiger</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="3241">3.241</span></div></div>
        </div>
      </a>

      <!-- Fortune Rabbit -->
      <a href="/jogos/tiger.php?t=rabbit" class="game-card" data-tags="hot" data-name="fortune rabbit">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#8800cc,#44006a 55%,#0f0020);--glow-color:rgba(160,0,220,.55);--shadow-color:rgba(200,50,255,.5)"></div>
            <div class="game-emoji">🐰</div>
          </div>
          <div class="game-provider">PG STYLE</div>
          <div class="game-badge badge-hot">🔥 HOT</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Fortune Rabbit</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="2180">2.180</span></div></div>
        </div>
      </a>

      <!-- Fortune Dragon -->
      <a href="/jogos/tiger.php?t=dragon" class="game-card" data-tags="new exclusive" data-name="fortune dragon">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#0050cc,#002880 55%,#000a20);--glow-color:rgba(0,80,200,.55);--shadow-color:rgba(50,120,255,.5)"></div>
            <div class="game-emoji">🐉</div>
          </div>
          <div class="game-provider">PG STYLE</div>
          <div class="game-badge badge-new">✨ NEW</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Fortune Dragon</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="1560">1.560</span></div></div>
        </div>
      </a>

      <!-- Aviator -->
      <a href="/jogos/aviator.php" class="game-card" data-tags="hot exclusive" data-name="aviator">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#cc2200,#801100 55%,#1a0000);--glow-color:rgba(220,40,0,.55);--shadow-color:rgba(255,70,0,.5)"></div>
            <div class="game-emoji">✈️</div>
          </div>
          <div class="game-provider">SPRIBE</div>
          <div class="game-badge badge-live"><span class="live-dot"></span> AO VIVO</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Aviator</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="5420">5.420</span></div></div>
        </div>
      </a>

      <!-- Crash -->
      <a href="/jogos/crash.php" class="game-card" data-tags="hot exclusive" data-name="crash">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#1a6600,#0d3300 55%,#020d00);--glow-color:rgba(30,180,0,.55);--shadow-color:rgba(60,220,0,.5)"></div>
            <div class="game-emoji">🚀</div>
          </div>
          <div class="game-provider">ORIGINAL</div>
          <div class="game-badge badge-live"><span class="live-dot"></span> AO VIVO</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Crash</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="847">847</span></div></div>
        </div>
      </a>

      <!-- Mines -->
      <a href="/jogos/mines.php" class="game-card" data-tags="hot" data-name="mines">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#5a3000,#2a1400 55%,#0a0500);--glow-color:rgba(180,80,0,.55);--shadow-color:rgba(220,100,0,.5)"></div>
            <div class="game-emoji">💣</div>
          </div>
          <div class="game-provider">ORIGINAL</div>
          <div class="game-badge badge-hot">🔥 HOT</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Mines</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="1203">1.203</span></div></div>
        </div>
      </a>

      <!-- Plinko -->
      <a href="/jogos/plinko.php" class="game-card" data-tags="new" data-name="plinko">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#004466,#002233 55%,#000810);--glow-color:rgba(0,120,200,.55);--shadow-color:rgba(0,160,255,.5)"></div>
            <div class="game-emoji">🔵</div>
          </div>
          <div class="game-provider">ORIGINAL</div>
          <div class="game-badge badge-new">✨ NEW</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Plinko</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="634">634</span></div></div>
        </div>
      </a>

      <!-- Dice -->
      <a href="/jogos/dice.php" class="game-card" data-tags="exclusive" data-name="dice">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#0a2a5a,#041530 55%,#000510);--glow-color:rgba(60,100,220,.55);--shadow-color:rgba(80,130,255,.5)"></div>
            <div class="game-emoji">🎲</div>
          </div>
          <div class="game-provider">ORIGINAL</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Dice</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="512">512</span></div></div>
        </div>
      </a>

      <!-- Limbo -->
      <a href="/jogos/limbo.php" class="game-card" data-tags="exclusive" data-name="limbo">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#3a0066,#1a0033 55%,#060010);--glow-color:rgba(120,0,200,.55);--shadow-color:rgba(160,50,255,.5)"></div>
            <div class="game-emoji">🌀</div>
          </div>
          <div class="game-provider">ORIGINAL</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Limbo</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="389">389</span></div></div>
        </div>
      </a>

      <!-- Raspadinha -->
      <a href="/" class="game-card" data-tags="raspa" data-name="raspadinha">
        <div class="game-thumb">
          <div class="game-thumb-inner">
            <div class="game-bg" style="--thumb-bg:radial-gradient(at 60% 110%,#6a4a00,#3a2600 55%,#0d0900);--glow-color:rgba(200,140,0,.55);--shadow-color:rgba(240,170,0,.5)"></div>
            <div class="game-emoji">🎟️</div>
          </div>
          <div class="game-provider">ORIGINAL</div>
          <div class="game-badge badge-new">🎟 RASPA</div>
          <div class="game-overlay"><button class="play-btn">▶ Jogar</button></div>
        </div>
        <div class="game-info">
          <div class="game-name">Raspadinha</div>
          <div class="game-meta"><div class="game-live-count"><span class="live-dot-sm"></span> <span class="pcount" data-b="2941">2.941</span></div></div>
        </div>
      </a>

    </div><!-- /games-grid -->

  </main>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div>
      <div class="f-logo">🍀 RaspaPix<span class="dot">.</span></div>
      <p class="f-desc">Plataforma de jogos com resultados provably fair e auditáveis. Jogue com responsabilidade. +18.</p>
    </div>
    <div class="f-col">
      <h4>Jogos</h4>
      <a href="/jogos/tiger.php?t=tiger">Fortune Tiger</a>
      <a href="/jogos/tiger.php?t=rabbit">Fortune Rabbit</a>
      <a href="/jogos/tiger.php?t=dragon">Fortune Dragon</a>
      <a href="/jogos/aviator.php">Aviator</a>
      <a href="/jogos/crash.php">Crash</a>
      <a href="/jogos/mines.php">Mines</a>
    </div>
    <div class="f-col">
      <h4>Conta</h4>
      <a href="/">Meu Perfil</a>
      <a href="/">Depositar</a>
      <a href="/">Sacar</a>
      <a href="/afiliados/">Afiliados</a>
    </div>
    <div class="f-col">
      <h4>Suporte</h4>
      <a href="#">Central de Ajuda</a>
      <a href="#">Jogo Responsável</a>
      <a href="#">Privacidade</a>
      <a href="#">Termos de Uso</a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 RaspaPix. Todos os direitos reservados.</p>
    <div class="footer-badges">
      <span class="fbadge">🔞 +18</span>
      <span class="fbadge">🎲 Jogo Responsável</span>
      <span class="fbadge">🔒 SSL Seguro</span>
    </div>
  </div>
</footer>

<script>
// ── Live player counts ───────────────────────────────────────────────
document.querySelectorAll('.pcount').forEach(el => {
  const base = parseInt(el.dataset.b);
  setInterval(() => {
    const v = base + Math.floor((Math.random()-.48)*40);
    el.textContent = v.toLocaleString('pt-BR');
  }, 3200 + Math.random()*2000);
});

// ── Jackpot counter ─────────────────────────────────────────────────
let jackpot = 180000 + Math.random()*20000;
const jEl = document.getElementById('jackpotVal');
setInterval(() => {
  jackpot += Math.random() * 5.5 + .5;
  jEl.textContent = 'R$ ' + jackpot.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
}, 2200);
jEl.textContent = 'R$ ' + jackpot.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});

// ── Animated stats ──────────────────────────────────────────────────
let online = 1800 + Math.floor(Math.random()*500);
let ganhos = 88000 + Math.random()*12000;
let rodadas = 25000 + Math.floor(Math.random()*5000);
function animStats() {
  online += Math.floor((Math.random()-.46)*8);
  ganhos += Math.random()*28 + 2;
  rodadas += Math.floor(Math.random()*4+1);
  document.getElementById('statOnline').textContent = online.toLocaleString('pt-BR');
  document.getElementById('statGanhos').textContent = 'R$ ' + ganhos.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('statRodadas').textContent = rodadas.toLocaleString('pt-BR');
}
animStats();
setInterval(animStats, 3000);

// ── Ticker ──────────────────────────────────────────────────────────
const tkNames = ['Lucas S.','Ana P.','Carlos R.','Beatriz M.','Felipe A.','Mariana L.','João V.','Camila F.','Pedro H.','Gabriel T.','Letícia O.','Thiago M.'];
const tkGames = ['Fortune Tiger','Aviator','Fortune Rabbit','Mines','Fortune Dragon','Crash','Plinko','Dice','Limbo','Raspadinha'];
function buildTicker() {
  let items = '';
  for (let i = 0; i < 24; i++) {
    const name = tkNames[Math.floor(Math.random()*tkNames.length)];
    const game = tkGames[Math.floor(Math.random()*tkGames.length)];
    const mult = (Math.random()*18+1.2).toFixed(2);
    const val = (Math.random()*4800+80).toFixed(2).replace('.',',');
    items += `<span class="ticker-item"><strong>${name}</strong><span class="game-tag">${game} ${mult}x</span><span class="amount">+R$ ${val}</span></span>`;
  }
  return items + items;
}
document.getElementById('ticker').innerHTML = buildTicker();

// ── Filter ──────────────────────────────────────────────────────────
function filterGames(tag, btn) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  document.querySelectorAll('.game-card').forEach(c => {
    if (tag === 'all') c.dataset.hidden = 'false';
    else c.dataset.hidden = c.dataset.tags.includes(tag) ? 'false' : 'true';
  });
  document.getElementById('searchInput').value = '';
}

// ── Search ──────────────────────────────────────────────────────────
function filterSearch(q) {
  document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
  const s = q.toLowerCase().trim();
  document.querySelectorAll('.game-card').forEach(c => {
    c.dataset.hidden = (!s || c.dataset.name.includes(s)) ? 'false' : 'true';
  });
}
</script>
</body>
</html>
