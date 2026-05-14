<?php
session_start();
require_once __DIR__ . '/../conexao.php';
$isLogged = isset($_SESSION['usuario_id']);
$saldo = 0; $nomeUser = '';
if ($isLogged) {
    $st = $pdo->prepare("SELECT nome,saldo FROM usuarios WHERE id=? LIMIT 1");
    $st->execute([$_SESSION['usuario_id']]);
    $u = $st->fetch(PDO::FETCH_ASSOC); $saldo = $u['saldo']??0; $nomeUser = $u['nome']??'';
}
$pageTitle = 'Ranking';
include __DIR__ . '/../includes/app_head.php';
include __DIR__ . '/../includes/app_navbar.php';
include __DIR__ . '/../includes/app_sidebar.php';
$names = ['Lucas S.','Ana P.','Carlos R.','Beatriz M.','Felipe A.','Mariana L.','João V.','Camila F.','Pedro H.','Gabriel T.'];
$colors= ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#f97316','#84cc16','#a78bfa'];
?>
<div class="app"><div class="page-content" id="page-content">

  <div class="page-title" data-aos="fade-right">
    <i data-lucide="trophy" class="pt-icon"></i> Ranking Semanal
  </div>

  <!-- Top 3 Podium -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:24px;align-items:end" data-aos="fade-up">
    <?php
    $podium = [
      [2,'#b0bec5','🥈','Segundo Lugar','R$ '.number_format(rand(8000,15000),2,',','.')],
      [1,'var(--gold)','🥇','Campeão','R$ '.number_format(rand(20000,50000),2,',','.')],
      [3,'#a1887f','🥉','Terceiro Lugar','R$ '.number_format(rand(3000,8000),2,',','.')],
    ];
    foreach($podium as [$pos,$color,$medal,$label,$val]):
    $h = $pos===1?'180px':($pos===2?'140px':'120px');
    $nm = $names[$pos-1]; $cl = $colors[$pos-1];
    ?>
    <div class="card" style="text-align:center;padding:20px;height:<?=$h?>;display:flex;flex-direction:column;align-items:center;justify-content:center;border-color:<?=$color?>33">
      <div style="font-size:1.8rem;margin-bottom:4px"><?=$medal?></div>
      <div style="width:44px;height:44px;border-radius:50%;background:<?=$cl?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;margin:0 auto 6px;box-shadow:0 0 16px <?=$cl?>66"><?=strtoupper(substr($nm,0,2))?></div>
      <div style="font-weight:700;font-size:.85rem"><?=$nm?></div>
      <div style="font-size:.78rem;color:var(--muted);margin-bottom:4px"><?=$label?></div>
      <div style="font-size:1rem;font-weight:900;color:<?=$color?>"><?=$val?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 280px;gap:20px">
  <div class="card" data-aos="fade-up">
    <div class="tabs mb-2">
      <button class="tab-btn active" data-tab="rk-ganhos">🏆 Maiores Ganhos</button>
      <button class="tab-btn" data-tab="rk-apostas">🎰 Mais Apostas</button>
      <button class="tab-btn" data-tab="rk-mult">⚡ Maior Mult.</button>
    </div>
    <div data-tab-group>
      <?php foreach(['rk-ganhos','rk-apostas','rk-mult'] as $tid): ?>
      <div class="tab-panel <?=$tid==='rk-ganhos'?'active':''?>" id="<?=$tid?>">
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Jogador</th><th>Jogo Fav.</th><th>Total</th></tr></thead>
            <tbody>
              <?php
              $games = ['Fortune Tiger','Aviator','Crash','Mines','Plinko'];
              for($i=0;$i<10;$i++):
                $n=$names[$i%count($names)]; $c=$colors[$i%count($colors)];
                $v='R$ '.number_format(rand(500,50000),2,',','.');
                $g=$games[array_rand($games)];
              ?>
              <tr class="rank-row <?=$i<3?'rank-'.($i+1):''?>">
                <td><?=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':($i+1)))?></td>
                <td><div style="display:flex;align-items:center;gap:8px">
                  <div class="rank-avatar" style="background:<?=$c?>"><?=strtoupper(substr($n,0,2))?></div><?=$n?>
                </div></td>
                <td style="color:var(--muted);font-size:.8rem"><?=$g?></td>
                <td style="font-weight:700;color:var(--gold)"><?=$v?></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div>
    <div class="card mb-3" data-aos="fade-left">
      <div class="section-title"><i data-lucide="calendar" style="width:16px;height:16px;color:var(--red)"></i> Premiação</div>
      <?php foreach([['🥇 1º Lugar','R$ 500,00','var(--gold)'],['🥈 2º Lugar','R$ 200,00','#b0bec5'],['🥉 3º Lugar','R$ 100,00','#a1887f'],['4º - 10º','R$ 20,00','var(--muted)']] as [$p,$v,$c]): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border)">
        <span style="font-size:.85rem"><?=$p?></span>
        <span style="font-weight:800;color:<?=$c?>"><?=$v?></span>
      </div>
      <?php endforeach; ?>
      <div style="margin-top:12px;font-size:.75rem;color:var(--muted)">Reset toda segunda-feira às 00:00 BRT.</div>
    </div>

    <div class="card" data-aos="fade-left" data-aos-delay="80">
      <div class="section-title"><i data-lucide="clock" style="width:16px;height:16px;color:var(--red)"></i> Tempo Restante</div>
      <div id="countdown" style="font-size:2rem;font-weight:900;text-align:center;color:var(--red);text-shadow:0 0 16px rgba(255,23,68,.4)">00:00:00</div>
      <div style="font-size:.75rem;color:var(--muted);text-align:center;margin-top:4px">Até o reset</div>
    </div>
  </div>
  </div>

</div></div>
<script>
(function countdown(){
  const el=document.getElementById('countdown');
  function update(){
    const now=new Date(), next=new Date(now);
    next.setDate(next.getDate()+(7-next.getDay())%7||7);
    next.setHours(0,0,0,0);
    const diff=next-now;
    const h=Math.floor(diff/3600000),m=Math.floor(diff%3600000/60000),s=Math.floor(diff%60000/1000);
    el.textContent=`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }
  update(); setInterval(update,1000);
})();
</script>
<?php include __DIR__ . '/../includes/app_footer.php'; ?>
