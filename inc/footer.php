</div><!-- /page-wrap -->
</main>
</div><!-- /app-layout -->

<footer style="background:rgba(0,0,0,.35);border-top:1px solid rgba(255,255,255,.05);padding:28px 22px;margin-top:40px">
  <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:24px">
    <div>
      <img src="<?=$logoSite??''?>" alt="<?=htmlspecialchars($nomeSite??'RaspaPix')?>" style="height:34px;object-fit:contain;margin-bottom:10px;display:block"
           onerror="this.outerHTML='<div style=font-size:1.2rem;font-weight:900;color:#fff;margin-bottom:10px>🍀 '+<?=json_encode($nomeSite??'RaspaPix')?>+'</div>'">
      <div style="font-size:.73rem;color:var(--muted-2);line-height:1.7">
        © <?=date('Y')?> <?=htmlspecialchars($nomeSite??'RaspaPix')?>.<br>
        Todos os direitos reservados.<br>
        <span style="color:rgba(255,255,255,.3)">Jogue com responsabilidade.</span>
      </div>
    </div>
    <div>
      <div style="font-size:.72rem;font-weight:700;color:var(--text-2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Jogos</div>
      <?php foreach([
        ['🎲 Cassino','/jogos/'],
        ['🎫 Raspadinhas','/cartelas'],
        ['✈️ Aviator','/jogos/aviator.php'],
        ['💣 Mines','/jogos/mines.php'],
        ['🚀 Crash','/jogos/crash.php'],
        ['🐯 Fortune Tiger','/jogos/tiger.php'],
      ] as [$l,$h]): ?>
      <a href="<?=$h?>" style="display:block;font-size:.73rem;color:var(--muted-2);text-decoration:none;padding:4px 0;transition:color .2s" onmouseover="this.style.color='#00e676'" onmouseout="this.style.color=''">
        <?=$l?>
      </a>
      <?php endforeach; ?>
    </div>
    <div>
      <div style="font-size:.72rem;font-weight:700;color:var(--text-2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Conta</div>
      <?php foreach([
        ['Dashboard','/pages/dashboard.php'],
        ['Depositar','/pages/deposit.php'],
        ['Sacar','/pages/withdraw.php'],
        ['Carteira','/pages/wallet.php'],
        ['Histórico','/pages/history.php'],
        ['Perfil','/perfil'],
      ] as [$l,$h]): ?>
      <a href="<?=$h?>" style="display:block;font-size:.73rem;color:var(--muted-2);text-decoration:none;padding:4px 0;transition:color .2s" onmouseover="this.style.color='#00e676'" onmouseout="this.style.color=''">
        <?=$l?>
      </a>
      <?php endforeach; ?>
    </div>
    <div>
      <div style="font-size:.72rem;font-weight:700;color:var(--text-2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">Plataforma</div>
      <?php foreach([
        ['👥 Afiliados','/afiliados'],
        ['👑 VIP','/pages/vip.php'],
        ['🏆 Ranking','/pages/ranking.php'],
        ['💬 Suporte','/pages/support.php'],
        ['📜 Política','/politica'],
      ] as [$l,$h]): ?>
      <a href="<?=$h?>" style="display:block;font-size:.73rem;color:var(--muted-2);text-decoration:none;padding:4px 0;transition:color .2s" onmouseover="this.style.color='#00e676'" onmouseout="this.style.color=''">
        <?=$l?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div style="max-width:1200px;margin:18px auto 0;padding-top:16px;border-top:1px solid rgba(255,255,255,.04);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div style="font-size:.68rem;color:var(--muted-2)">+18 · Jogo Responsável · Licença de Operação</div>
    <div style="display:flex;gap:14px">
      <a href="/jogos/"   style="font-size:.68rem;color:var(--muted-2);text-decoration:none">Cassino</a>
      <a href="/cartelas" style="font-size:.68rem;color:var(--muted-2);text-decoration:none">Raspadinhas</a>
      <a href="/afiliados" style="font-size:.68rem;color:var(--muted-2);text-decoration:none">Afiliados</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js" crossorigin="anonymous"></script>
<script src="/assets/js/main.js?v=3"></script>
<script>if(typeof lucide!=='undefined')lucide.createIcons();</script>
