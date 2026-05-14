/* ═══════════════════════════════════════════════════════
   LUNARPAY — Main JavaScript v1.0
   GSAP + AOS + Particles + All Components
═══════════════════════════════════════════════════════ */

/* ── Init ──────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initParticles();
  initSidebar();
  initDropdowns();
  initModals();
  initTabs();
  initCounters();
  initGSAP();
  initFeed();
  initTooltips();
  lucide.createIcons();

  if (typeof AOS !== 'undefined') {
    AOS.init({ duration:600, easing:'ease-out-cubic', once:true, offset:40 });
  }
});

/* ── Particles Background ──────────────────────────── */
function initParticles() {
  const canvas = document.getElementById('particles-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, pts;

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  function build() {
    pts = Array.from({length:70}, () => ({
      x: Math.random()*W, y: Math.random()*H,
      vx:(Math.random()-.5)*.3, vy:(Math.random()-.5)*.3,
      r: Math.random()*1.5+.4,
      a: Math.random()
    }));
  }
  function draw() {
    ctx.clearRect(0,0,W,H);
    pts.forEach(p => {
      p.x+=p.vx; p.y+=p.vy;
      if(p.x<0||p.x>W) p.vx*=-1;
      if(p.y<0||p.y>H) p.vy*=-1;
      ctx.beginPath();
      ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle = `rgba(255,23,68,${p.a*.6})`;
      ctx.fill();
    });
    // Lines between close particles
    for(let i=0;i<pts.length;i++) {
      for(let j=i+1;j<pts.length;j++) {
        const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y;
        const d=Math.sqrt(dx*dx+dy*dy);
        if(d<120){
          ctx.beginPath();
          ctx.moveTo(pts[i].x,pts[i].y);
          ctx.lineTo(pts[j].x,pts[j].y);
          ctx.strokeStyle=`rgba(255,23,68,${(1-d/120)*.12})`;
          ctx.lineWidth=.5; ctx.stroke();
        }
      }
    }
    requestAnimationFrame(draw);
  }
  resize(); build(); draw();
  window.addEventListener('resize', ()=>{ resize(); build(); });
}

/* ── Toast Notifications ───────────────────────────── */
function toast(msg, type='info', title='', duration=4000) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = { success:'✅', error:'❌', warning:'⚠️', info:'ℹ️' };
  const titles = { success:'Sucesso!', error:'Erro!', warning:'Atenção!', info:'Aviso' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `
    <span class="toast-icon">${icons[type]||icons.info}</span>
    <div class="toast-body">
      <div class="toast-title">${title||titles[type]||''}</div>
      <div class="toast-msg">${msg}</div>
    </div>`;
  container.appendChild(el);
  setTimeout(()=>el.classList.add('show'), 10);
  setTimeout(()=>{
    el.classList.remove('show');
    el.classList.add('hide');
    setTimeout(()=>el.remove(), 300);
  }, duration);
}
window.toast = toast;

/* ── Modal System ──────────────────────────────────── */
function initModals() {
  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.modal));
  });
  document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => {
      if (e.target === ov) closeModal(ov.id);
    });
  });
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const ov = btn.closest('.modal-overlay');
      if (ov) closeModal(ov.id);
    });
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(ov => closeModal(ov.id));
    }
  });
}
function openModal(id) {
  const ov = document.getElementById(id);
  if (!ov) return;
  ov.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const ov = document.getElementById(id);
  if (!ov) return;
  ov.classList.remove('open');
  document.body.style.overflow = '';
}
window.openModal = openModal;
window.closeModal = closeModal;

/* ── Sidebar Toggle ────────────────────────────────── */
function initSidebar() {
  const sb   = document.getElementById('sidebar');
  const btn  = document.getElementById('sidebar-toggle');
  const ovl  = document.getElementById('sidebar-overlay');
  if (!sb) return;

  function toggle() {
    const isMobile = window.innerWidth <= 900;
    if (isMobile) {
      sb.classList.toggle('open');
      if (ovl) ovl.classList.toggle('show');
    } else {
      sb.classList.toggle('collapsed');
      const pc = document.getElementById('page-content');
      if (pc) pc.classList.toggle('full');
    }
  }
  if (btn) btn.addEventListener('click', toggle);
  if (ovl) ovl.addEventListener('click', toggle);
}

/* ── Dropdown Menus ────────────────────────────────── */
function initDropdowns() {
  document.querySelectorAll('.dropdown').forEach(dd => {
    const trigger = dd.querySelector('[data-dropdown-trigger]');
    const menu    = dd.querySelector('.dropdown-menu');
    if (!trigger || !menu) return;
    trigger.addEventListener('click', e => {
      e.stopPropagation();
      document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        if (m !== menu) m.classList.remove('open');
      });
      menu.classList.toggle('open');
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
  });
}

/* ── Tabs ──────────────────────────────────────────── */
function initTabs() {
  document.querySelectorAll('.tabs').forEach(tabs => {
    tabs.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        tabs.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const parent = tabs.closest('[data-tab-group]') || document;
        parent.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        const panel = parent.querySelector(`#${target}`);
        if (panel) panel.classList.add('active');
      });
    });
  });
}

/* ── Animated Counters ─────────────────────────────── */
function animateCount(el, target, duration=1400, prefix='', suffix='', decimals=0) {
  const start = performance.now();
  const from  = 0;
  function step(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const value = from + (target - from) * eased;
    el.textContent = prefix + value.toLocaleString('pt-BR', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }) + suffix;
    if (progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}
function initCounters() {
  const els = document.querySelectorAll('[data-count]');
  if (!els.length) return;
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el  = e.target;
      const val = parseFloat(el.dataset.count);
      const dec = parseInt(el.dataset.decimals||0);
      const pre = el.dataset.prefix||'';
      const suf = el.dataset.suffix||'';
      animateCount(el, val, 1600, pre, suf, dec);
      obs.unobserve(el);
    });
  }, {threshold:.3});
  els.forEach(el => obs.observe(el));
}
window.animateCount = animateCount;

/* ── GSAP Animations ───────────────────────────────── */
function initGSAP() {
  if (typeof gsap === 'undefined') return;

  // Card entrance on scroll
  const cards = document.querySelectorAll('.card, .balance-card, .game-card, .stat-card, .vip-card');
  gsap.from(cards, {
    opacity:0, y:30, duration:.6, stagger:.07,
    ease:'power3.out', clearProps:'all',
    scrollTrigger: cards.length ? {
      trigger: cards[0], start:'top 85%'
    } : undefined
  });

  // Page title
  const title = document.querySelector('.page-title');
  if (title) {
    gsap.from(title, { opacity:0, x:-20, duration:.5, ease:'power2.out' });
  }

  // Balance cards: neon pulse on hover
  document.querySelectorAll('.balance-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      gsap.to(card, { boxShadow:'0 0 40px rgba(255,23,68,.2)', duration:.2 });
    });
    card.addEventListener('mouseleave', () => {
      gsap.to(card, { boxShadow:'none', duration:.3 });
    });
  });
}

/* ── Fake Realtime Feed ────────────────────────────── */
const FEED_NAMES  = ['Lucas S.','Ana P.','Carlos R.','Beatriz M.','Felipe A.','Mariana L.','João V.','Camila F.','Pedro H.','Gabriel T.','Letícia O.','Thiago M.','Rafael N.'];
const FEED_GAMES  = ['Fortune Tiger','Aviator','Fortune Rabbit','Mines','Fortune Dragon','Crash','Plinko','Dice','Limbo'];
const FEED_COLORS = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#f97316'];
let feedInterval;

function initFeed() {
  const list = document.getElementById('feed-list');
  if (!list) return;
  addFeedItem(list);
  feedInterval = setInterval(() => addFeedItem(list), 2200 + Math.random()*1800);
}
function addFeedItem(list) {
  const name   = FEED_NAMES[Math.floor(Math.random()*FEED_NAMES.length)];
  const game   = FEED_GAMES[Math.floor(Math.random()*FEED_GAMES.length)];
  const color  = FEED_COLORS[Math.floor(Math.random()*FEED_COLORS.length)];
  const isWin  = Math.random() > .42;
  const mult   = (Math.random()*18+1.1).toFixed(2);
  const amount = (Math.random()*2800+80).toFixed(2).replace('.',',');
  const initial = name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase();

  const item = document.createElement('div');
  item.className = 'feed-item';
  item.innerHTML = `
    <div class="feed-avatar" style="background:${color}">${initial}</div>
    <div class="feed-name">${name}</div>
    <div class="feed-game">${game} ${mult}x</div>
    <div class="feed-amount ${isWin?'win':'lose'}">${isWin?'+':'-'}R$ ${amount}</div>`;
  list.insertBefore(item, list.firstChild);

  // Keep max 12 items
  while (list.children.length > 12) list.removeChild(list.lastChild);
}

/* ── Live Ticker ───────────────────────────────────── */
function buildTicker() {
  const ticker = document.getElementById('live-ticker');
  if (!ticker) return;
  let html = '';
  for (let i=0; i<22; i++) {
    const n = FEED_NAMES[Math.floor(Math.random()*FEED_NAMES.length)];
    const g = FEED_GAMES[Math.floor(Math.random()*FEED_GAMES.length)];
    const m = (Math.random()*18+1.2).toFixed(2);
    const v = (Math.random()*3500+120).toFixed(2).replace('.',',');
    html += `<span class="ticker-item"><strong>${n}</strong><span class="game-tag"> ${g} ${m}x </span><span class="amount">+R$ ${v}</span></span>`;
  }
  ticker.innerHTML = html + html;
}
buildTicker();

/* ── Copy to Clipboard ─────────────────────────────── */
function copyText(text, label='Copiado!') {
  navigator.clipboard.writeText(text).then(() => {
    toast(label, 'success', 'Copiado', 2000);
  }).catch(() => {
    toast('Erro ao copiar', 'error');
  });
}
window.copyText = copyText;

/* ── Amount Selector ───────────────────────────────── */
function initAmountBtns() {
  document.querySelectorAll('.amount-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.amount-grid');
      if (group) group.querySelectorAll('.amount-btn').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const target = btn.dataset.target;
      const val    = btn.dataset.value;
      if (target) {
        const input = document.getElementById(target);
        if (input) input.value = val;
      }
    });
  });
}
initAmountBtns();

/* ── Skeleton Loading ──────────────────────────────── */
function showSkeleton(el) {
  el.innerHTML = `<div class="skeleton" style="height:20px;width:60%;margin-bottom:8px"></div>
    <div class="skeleton" style="height:14px;width:90%"></div>`;
}
function hideSkeleton(el, content) {
  el.innerHTML = content;
}
window.showSkeleton = showSkeleton;
window.hideSkeleton = hideSkeleton;

/* ── Tooltips ──────────────────────────────────────── */
function initTooltips() {
  document.querySelectorAll('[data-tip]').forEach(el => {
    const tip = document.createElement('div');
    tip.style.cssText = 'position:absolute;background:#1a1a2e;border:1px solid rgba(255,23,68,.2);color:#e8e9f8;font-size:.75rem;padding:5px 10px;border-radius:6px;white-space:nowrap;z-index:999;pointer-events:none;opacity:0;transition:opacity .15s;transform:translateX(-50%);top:-34px;left:50%';
    tip.textContent = el.dataset.tip;
    el.style.position='relative';
    el.appendChild(tip);
    el.addEventListener('mouseenter', ()=>tip.style.opacity='1');
    el.addEventListener('mouseleave', ()=>tip.style.opacity='0');
  });
}

/* ── Balance Live Update ───────────────────────────── */
async function refreshBalance() {
  try {
    const r = await fetch('/api/get_saldo.php');
    const d = await r.json();
    document.querySelectorAll('[data-live-balance]').forEach(el => {
      el.textContent = 'R$ ' + parseFloat(d.saldo||0).toLocaleString('pt-BR',{minimumFractionDigits:2});
    });
  } catch(e) {}
}
setInterval(refreshBalance, 15000);

/* ── Jackpot Ticker ────────────────────────────────── */
const jpEl = document.getElementById('jackpot-value');
if (jpEl) {
  let jp = parseFloat(jpEl.dataset.base||'185000') + Math.random()*15000;
  jpEl.textContent = 'R$ ' + jp.toLocaleString('pt-BR',{minimumFractionDigits:2});
  setInterval(()=>{
    jp += Math.random()*4+.5;
    jpEl.textContent = 'R$ ' + jp.toLocaleString('pt-BR',{minimumFractionDigits:2});
  }, 2000);
}

/* ── Chat ──────────────────────────────────────────── */
const chatInput = document.getElementById('chat-input');
const chatList  = document.getElementById('chat-msgs');
if (chatInput && chatList) {
  chatInput.addEventListener('keydown', e => {
    if (e.key==='Enter' && chatInput.value.trim()) {
      appendChatMsg('Você', chatInput.value.trim(), '#ff1744');
      chatInput.value = '';
      setTimeout(()=>{
        const replies = ['🔥 Boa sorte!','Vamos nessa!','🐯 Fortune Tiger pagando!','Alguém ganhou em cima?','Crash não vai passar de 1.5x','Aviator explodiu kkk','Qual jogo tá pagando hoje?'];
        appendChatMsg(FEED_NAMES[Math.floor(Math.random()*FEED_NAMES.length)], replies[Math.floor(Math.random()*replies.length)], FEED_COLORS[Math.floor(Math.random()*FEED_COLORS.length)]);
      }, 1000+Math.random()*1500);
    }
  });
  function appendChatMsg(name, text, color) {
    const msg = document.createElement('div');
    msg.className='chat-msg';
    const init = name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase();
    msg.innerHTML=`<div class="chat-avatar" style="background:${color}">${init}</div><div class="chat-content"><div class="chat-name" style="color:${color}">${name}</div><div class="chat-text">${text}</div></div>`;
    chatList.appendChild(msg);
    chatList.scrollTop=chatList.scrollHeight;
    while(chatList.children.length>40) chatList.removeChild(chatList.firstChild);
  }
  // Seed chat
  const chatSeed = [
    ['Lucas S.','🔥 Fortune Tiger pagando 12x!','#ef4444'],
    ['Ana P.','Consegui 50x no Aviator 🚀','#10b981'],
    ['Carlos R.','Alguém joga Mines?','#3b82f6'],
    ['Beatriz M.','Primeiro depósito feito!','#f59e0b'],
    ['Felipe A.','Boa sorte pra todos! 🍀','#8b5cf6'],
  ];
  chatSeed.forEach(([n,t,c])=>appendChatMsg(n,t,c));
}
