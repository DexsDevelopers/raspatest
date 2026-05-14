/* ═══════════════════════════════════════════════════════════════
   LUNARPAY — Ultra Premium JS v2.0
   GSAP · Particles · Micro-animations · Realtime · Chat
═══════════════════════════════════════════════════════════════ */

/* ── Boot Sequence ─────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  bootLoader();
  initParticles();
  initRipple();
  initSidebar();
  initDropdowns();
  initModals();
  initTabs();
  initCounters();
  initFeed();
  initChat();
  initAmountBtns();
  initTicker();
  initGSAP();
  if (typeof lucide !== 'undefined') lucide.createIcons();
  if (typeof AOS !== 'undefined') AOS.init({ duration:550, easing:'ease-out-quart', once:true, offset:30 });
});

/* ── Page Loader ───────────────────────────────────────────── */
function bootLoader() {
  const loader = document.getElementById('lp-loader');
  if (!loader) return;
  setTimeout(() => {
    loader.classList.add('hidden');
    // GSAP entrance after load
    if (typeof gsap !== 'undefined') {
      gsap.from('.page-title', { opacity:0, y:20, duration:.5, ease:'power2.out', delay:.1 });
      gsap.from('.bal-card, .stat-card', {
        opacity:0, y:24, duration:.5, stagger:.06,
        ease:'power2.out', delay:.15
      });
    }
  }, 1400);
}

/* ── Interactive Particles ─────────────────────────────────── */
function initParticles() {
  const canvas = document.getElementById('particles-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, pts, mouse = { x:-999, y:-999 };

  const resize = () => { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; };
  const build  = () => {
    pts = Array.from({ length:65 }, () => ({
      x: Math.random()*W, y: Math.random()*H,
      vx:(Math.random()-.5)*.25, vy:(Math.random()-.5)*.25,
      r: Math.random()*1.4+.4, a: Math.random()*.8+.2
    }));
  };

  const draw = () => {
    ctx.clearRect(0,0,W,H);
    pts.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x<0||p.x>W) p.vx*=-1;
      if (p.y<0||p.y>H) p.vy*=-1;
      // Mouse repel
      const dx=p.x-mouse.x, dy=p.y-mouse.y, d=Math.sqrt(dx*dx+dy*dy);
      if (d<100) { p.vx += dx/d*.04; p.vy += dy/d*.04; }
      // Cap speed
      const spd=Math.sqrt(p.vx*p.vx+p.vy*p.vy);
      if(spd>.7){p.vx=p.vx/spd*.7;p.vy=p.vy/spd*.7;}
      ctx.beginPath();
      ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle=`rgba(255,23,68,${p.a*.5})`;
      ctx.fill();
    });
    for(let i=0;i<pts.length;i++){
      for(let j=i+1;j<pts.length;j++){
        const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y;
        const d=Math.sqrt(dx*dx+dy*dy);
        if(d<110){
          ctx.beginPath();
          ctx.moveTo(pts[i].x,pts[i].y);
          ctx.lineTo(pts[j].x,pts[j].y);
          ctx.strokeStyle=`rgba(255,23,68,${(1-d/110)*.1})`;
          ctx.lineWidth=.4; ctx.stroke();
        }
      }
    }
    requestAnimationFrame(draw);
  };

  resize(); build(); draw();
  window.addEventListener('resize', () => { resize(); build(); });
  document.addEventListener('mousemove', e => { mouse.x=e.clientX; mouse.y=e.clientY; });
}

/* ── Ripple Effect on Buttons ──────────────────────────────── */
function initRipple() {
  document.addEventListener('click', e => {
    const btn = e.target.closest('.btn');
    if (!btn) return;
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height) * 2;
    const rip  = document.createElement('span');
    rip.className = 'ripple';
    rip.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
    btn.appendChild(rip);
    setTimeout(() => rip.remove(), 600);
  });
}

/* ── GSAP Animations ───────────────────────────────────────── */
function initGSAP() {
  if (typeof gsap === 'undefined') return;

  // Register ScrollTrigger if available
  if (typeof ScrollTrigger !== 'undefined') gsap.registerPlugin(ScrollTrigger);

  // Card hover neon glow
  document.querySelectorAll('.bal-card').forEach(card => {
    const color = getComputedStyle(card).getPropertyValue('--deco-color') || 'rgba(255,23,68,.1)';
    card.addEventListener('mouseenter', () => gsap.to(card, { boxShadow:`0 16px 50px rgba(0,0,0,.5), 0 0 0 1px rgba(255,23,68,.18)`, duration:.2 }));
    card.addEventListener('mouseleave', () => gsap.to(card, { boxShadow:'none', duration:.3 }));
  });

  // Game card magnetic tilt
  document.querySelectorAll('.game-card').forEach(card => {
    card.addEventListener('mousemove', e => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX-r.left)/r.width  - .5;
      const y = (e.clientY-r.top) /r.height - .5;
      gsap.to(card, { rotateY:x*8, rotateX:-y*6, transformPerspective:600, duration:.3, ease:'power2.out' });
    });
    card.addEventListener('mouseleave', () => {
      gsap.to(card, { rotateY:0, rotateX:0, duration:.4, ease:'power2.out' });
    });
  });

  // Scroll-triggered card entrances
  document.querySelectorAll('[data-gsap-fade]').forEach(el => {
    const delay = parseFloat(el.dataset.gsapDelay || 0);
    gsap.from(el, {
      opacity:0, y:28, duration:.55, delay,
      ease:'power2.out',
      scrollTrigger: ScrollTrigger ? { trigger:el, start:'top 88%', once:true } : undefined
    });
  });

  // Number pulse when jackpot updates
  window.pulseEl = (el) => {
    gsap.fromTo(el, { scale:1.08, color:'#ffd600' }, { scale:1, duration:.4, ease:'elastic.out(1,.5)' });
  };
}

/* ── Toast Notifications ───────────────────────────────────── */
function toast(msg, type='info', title='', duration=4000) {
  let root = document.getElementById('toast-root');
  if (!root) { root=document.createElement('div'); root.id='toast-root'; document.body.appendChild(root); }
  const icons = { success:'✅', error:'❌', warning:'⚠️', info:'💡' };
  const titles= { success:'Sucesso', error:'Erro', warning:'Atenção', info:'Aviso' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span class="toast-icon">${icons[type]}</span><div><div class="toast-title">${title||titles[type]}</div><div class="toast-msg">${msg}</div></div>`;
  root.appendChild(el);
  requestAnimationFrame(() => el.classList.add('in'));
  const dismiss = () => {
    el.classList.remove('in'); el.classList.add('out');
    setTimeout(() => el.remove(), 300);
  };
  setTimeout(dismiss, duration);
  el.addEventListener('click', dismiss);
}
window.toast = toast;

/* ── Modal System ──────────────────────────────────────────── */
function initModals() {
  document.querySelectorAll('[data-modal]').forEach(t => t.addEventListener('click', () => openModal(t.dataset.modal)));
  document.querySelectorAll('.modal-backdrop').forEach(b => {
    b.addEventListener('click', e => { if(e.target===b) closeModal(b.id); });
  });
  document.querySelectorAll('.modal-close').forEach(c => c.addEventListener('click', () => closeModal(c.closest('.modal-backdrop').id)));
  document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal-backdrop.open').forEach(b=>closeModal(b.id)); });
}
function openModal(id) {
  const b = document.getElementById(id);
  if(b){ b.classList.add('open'); document.body.style.overflow='hidden'; }
}
function closeModal(id) {
  const b = document.getElementById(id);
  if(b){ b.classList.remove('open'); document.body.style.overflow=''; }
}
window.openModal = openModal;
window.closeModal = closeModal;

/* ── Sidebar ───────────────────────────────────────────────── */
function initSidebar() {
  const sb  = document.getElementById('sidebar');
  const btn = document.getElementById('sb-toggle');
  const ovl = document.getElementById('sidebar-overlay');
  const pw  = document.getElementById('page-wrap');
  if(!sb) return;

  function toggle() {
    if(window.innerWidth<=900) {
      sb.classList.toggle('open');
      if(ovl) ovl.classList.toggle('show');
    } else {
      sb.classList.toggle('collapsed');
      if(pw) pw.classList.toggle('sidebar-collapsed');
    }
  }
  if(btn) btn.addEventListener('click', toggle);
  if(ovl) ovl.addEventListener('click', toggle);
  // Apply saved state
  if(localStorage.getItem('sb-collapsed')==='1' && window.innerWidth>900) {
    sb.classList.add('collapsed');
    if(pw) pw.classList.add('sidebar-collapsed');
  }
  sb.addEventListener('transitionend', () => {
    localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0');
  });
}

/* ── Dropdown Menus ────────────────────────────────────────── */
function initDropdowns() {
  document.querySelectorAll('.dropdown').forEach(dd => {
    const t = dd.querySelector('[data-dd-trigger]');
    const m = dd.querySelector('.dropdown-menu');
    if(!t||!m) return;
    t.addEventListener('click', e => {
      e.stopPropagation();
      document.querySelectorAll('.dropdown-menu.open').forEach(x=>{ if(x!==m) x.classList.remove('open'); });
      m.classList.toggle('open');
    });
  });
  document.addEventListener('click', () => document.querySelectorAll('.dropdown-menu.open').forEach(m=>m.classList.remove('open')));
}

/* ── Tabs ──────────────────────────────────────────────────── */
function initTabs() {
  document.querySelectorAll('.tabs').forEach(tabs => {
    tabs.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', () => {
        const id = tab.dataset.tab;
        tabs.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
        tab.classList.add('active');
        const scope = tabs.closest('[data-tab-group]') || document;
        scope.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
        const panel = scope.querySelector('#'+id) || scope.querySelector('[data-tab-id="'+id+'"]');
        if(panel) panel.classList.add('active');
      });
    });
  });
}

/* ── Animated Counters ─────────────────────────────────────── */
function animateCount(el, to, dur=1600, pre='', suf='', dec=0) {
  if(typeof gsap!=='undefined') {
    const obj={val:0};
    gsap.to(obj, { val:to, duration:dur/1000, ease:'power2.out',
      onUpdate() { el.textContent=pre+obj.val.toLocaleString('pt-BR',{minimumFractionDigits:dec,maximumFractionDigits:dec})+suf; }
    });
  } else {
    const start=performance.now();
    const step=(now)=>{ const t=Math.min((now-start)/dur,1); const e=1-Math.pow(1-t,3); const v=to*e; el.textContent=pre+v.toLocaleString('pt-BR',{minimumFractionDigits:dec,maximumFractionDigits:dec})+suf; if(t<1)requestAnimationFrame(step); };
    requestAnimationFrame(step);
  }
}
function initCounters() {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if(!e.isIntersecting) return;
      const el  = e.target;
      animateCount(el, parseFloat(el.dataset.count), 1600, el.dataset.prefix||'', el.dataset.suffix||'', parseInt(el.dataset.dec||0));
      obs.unobserve(el);
    });
  }, { threshold:.3 });
  document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));
}
window.animateCount = animateCount;

/* ── Fake Realtime Feed ────────────────────────────────────── */
const NAMES  = ['Lucas S.','Ana P.','Carlos R.','Beatriz M.','Felipe A.','Mariana L.','João V.','Camila F.','Pedro H.','Gabriel T.','Letícia O.','Thiago M.','Rafael N.','Isabela F.'];
const GAMES  = ['Fortune Tiger','Aviator','Fortune Rabbit','Mines','Fortune Dragon','Crash','Plinko','Dice','Limbo','Raspadinha'];
const COLORS = ['#ef4444','#f59e0b','#10b981','#3b82f6','#8b5cf6','#ec4899','#06b6d4','#f97316','#84cc16'];

function initFeed() {
  const list = document.getElementById('feed-list');
  if(!list) return;
  for(let i=0;i<8;i++) appendFeedItem(list);
  setInterval(() => appendFeedItem(list), 2000+Math.random()*2000);
}
function appendFeedItem(list) {
  const n = NAMES[Math.random()*NAMES.length|0];
  const g = GAMES[Math.random()*GAMES.length|0];
  const c = COLORS[Math.random()*COLORS.length|0];
  const w = Math.random()>.42;
  const m = (Math.random()*20+1).toFixed(2);
  const v = (Math.random()*3000+50).toFixed(2).replace('.',',');
  const init = n.split(' ').map(x=>x[0]).join('').toUpperCase();
  const item = document.createElement('div');
  item.className='feed-item';
  item.innerHTML=`<div class="feed-avatar" style="background:${c}">${init}</div><div class="feed-name">${n}</div><div class="feed-game">${g} ${m}x</div><div class="${w?'feed-win':'feed-loss'}">${w?'+':'-'}R$ ${v}</div>`;
  list.insertBefore(item,list.firstChild);
  while(list.children.length>14) list.lastChild.remove();
}

/* ── Ticker ────────────────────────────────────────────────── */
function initTicker() {
  const track = document.getElementById('ticker-track');
  if(!track) return;
  let html='';
  for(let i=0;i<24;i++) {
    const n=NAMES[Math.random()*NAMES.length|0];
    const g=GAMES[Math.random()*GAMES.length|0];
    const m=(Math.random()*18+1.2).toFixed(2);
    const v=(Math.random()*3200+80).toFixed(2).replace('.',',');
    html+=`<div class="ticker-item"><b>${n}</b> <span style="color:rgba(255,255,255,.35)">${g} ${m}x</span> <span class="t-win">+R$ ${v}</span></div>`;
  }
  track.innerHTML=html+html;
}

/* ── Live Chat ─────────────────────────────────────────────── */
function initChat() {
  const inp  = document.getElementById('chat-input');
  const body = document.getElementById('chat-body');
  if(!inp||!body) return;
  const seed=[['Lucas S.','🔥 Fortune Tiger pagando 12x!','#ef4444'],['Ana P.','Aviator 50x! Inacreditável 🚀','#10b981'],['Carlos R.','Alguém sabe quando sai novo jogo?','#3b82f6'],['Beatriz M.','Primeiro depósito aprovado ✅','#f59e0b'],['Felipe A.','Boa sorte pra todos 🍀','#8b5cf6']];
  seed.forEach(([n,t,c])=>appendChat(n,t,c));
  const sendMsg=()=>{
    const txt=inp.value.trim(); if(!txt) return;
    appendChat('Você',txt,'#ff1744'); inp.value='';
    setTimeout(()=>{
      const replies=['🔥 Boa sorte!','Vamos nessa!','Tiger pagando bem hoje','Crash foi embora kkkk','Aviator tá instável','Quem ganhou mais hoje?'];
      appendChat(NAMES[Math.random()*NAMES.length|0],replies[Math.random()*replies.length|0],COLORS[Math.random()*COLORS.length|0]);
    },800+Math.random()*1500);
  };
  inp.addEventListener('keydown',e=>{ if(e.key==='Enter') sendMsg(); });
  document.getElementById('chat-send')?.addEventListener('click',sendMsg);
}
function appendChat(name,text,color) {
  const body=document.getElementById('chat-body'); if(!body) return;
  const init=name.split(' ').map(x=>x[0]).join('').slice(0,2).toUpperCase();
  const el=document.createElement('div'); el.className='chat-msg';
  el.innerHTML=`<div class="chat-av" style="background:${color}">${init}</div><div><div class="chat-name" style="color:${color}">${name}</div><div class="chat-txt">${text}</div></div>`;
  body.appendChild(el); body.scrollTop=body.scrollHeight;
  while(body.children.length>40) body.firstChild.remove();
}
window.appendChat=appendChat;

/* ── Amount Quick Buttons ──────────────────────────────────── */
function initAmountBtns() {
  document.querySelectorAll('.amt-btn').forEach(btn => {
    btn.addEventListener('click',()=>{
      const g=btn.closest('.amt-grid'); if(g) g.querySelectorAll('.amt-btn').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const inp=document.getElementById(btn.dataset.target);
      if(inp) inp.value=btn.dataset.value;
    });
  });
}

/* ── Jackpot Ticker ────────────────────────────────────────── */
const jpEl=document.getElementById('jackpot-value');
if(jpEl){
  let jp=parseFloat(jpEl.dataset.base||'188000')+Math.random()*12000;
  jpEl.textContent='R$ '+jp.toLocaleString('pt-BR',{minimumFractionDigits:2});
  setInterval(()=>{
    jp+=Math.random()*6+.5;
    const fmt='R$ '+jp.toLocaleString('pt-BR',{minimumFractionDigits:2});
    jpEl.textContent=fmt;
    if(typeof gsap!=='undefined') gsap.fromTo(jpEl,{scale:1.04},{scale:1,duration:.35,ease:'power2.out'});
  },1800);
}

/* ── Clipboard ─────────────────────────────────────────────── */
function copyText(text,label='Copiado!'){
  navigator.clipboard?.writeText(text).then(()=>toast(label,'success','',2000)).catch(()=>toast('Erro ao copiar','error'));
}
window.copyText=copyText;

/* ── Balance Refresh ───────────────────────────────────────── */
async function refreshBalance(){
  try{
    const r=await fetch('/api/get_saldo.php');
    const d=await r.json();
    document.querySelectorAll('[data-live-bal]').forEach(el=>{
      const fmt='R$ '+parseFloat(d.saldo||0).toLocaleString('pt-BR',{minimumFractionDigits:2});
      if(el.textContent!==fmt){
        el.textContent=fmt;
        if(typeof gsap!=='undefined') gsap.fromTo(el,{color:'#00e676'},{color:'',duration:1.5});
      }
    });
  }catch(e){}
}
setInterval(refreshBalance,15000);

/* ── Global Search ─────────────────────────────────────────── */
const gSearchInput=document.getElementById('global-search');
if(gSearchInput){
  gSearchInput.addEventListener('input',e=>{
    const q=e.target.value.toLowerCase();
    document.querySelectorAll('.game-card[data-name]').forEach(c=>{
      c.style.display=(!q||c.dataset.name.includes(q))?'':'none';
    });
  });
}

/* ── Skeleton Helpers ──────────────────────────────────────── */
window.showSkel=(el,lines=2)=>{
  el.innerHTML=Array.from({length:lines},(_,i)=>`<div class="skel mb-1" style="height:${i?13:18}px;width:${i?'70%':'45%'}"></div>`).join('');
};
