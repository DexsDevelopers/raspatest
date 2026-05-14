<?php
session_start();
require_once __DIR__.'/conexao.php';
if(isset($_SESSION['usuario_id'])){header('Location: /pages/dashboard.php');exit;}
$error='';$tab='login';

if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'login';
  if($action==='login'){
    $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
    if($email&&$pass){
      $st=$pdo->prepare("SELECT id,nome,senha FROM usuarios WHERE email=? LIMIT 1");
      $st->execute([$email]); $u=$st->fetch(PDO::FETCH_ASSOC);
      if($u&&password_verify($pass,$u['senha'])){
        $_SESSION['usuario_id']=$u['id'];$_SESSION['nome']=$u['nome'];
        header('Location: /pages/dashboard.php');exit;
      } else $error='E-mail ou senha incorretos.';
    } else $error='Preencha todos os campos.';
  } elseif($action==='register'){
    $tab='register';
    $nome=trim($_POST['nome']??'');$email=trim($_POST['email']??'');
    $pass=$_POST['password']??'';$pass2=$_POST['password2']??'';
    if(!$nome||!$email||!$pass) $error='Preencha todos os campos.';
    elseif($pass!==$pass2)      $error='Senhas não coincidem.';
    elseif(strlen($pass)<6)     $error='Senha mínima: 6 caracteres.';
    else {
      $ck=$pdo->prepare("SELECT id FROM usuarios WHERE email=? LIMIT 1");$ck->execute([$email]);
      if($ck->fetch()) $error='E-mail já cadastrado.';
      else {
        $ins=$pdo->prepare("INSERT INTO usuarios(nome,email,senha,saldo,criado_em)VALUES(?,?,?,0,NOW())");
        $ins->execute([$nome,$email,password_hash($pass,PASSWORD_DEFAULT)]);
        $_SESSION['usuario_id']=$pdo->lastInsertId();$_SESSION['nome']=$nome;
        header('Location: /pages/dashboard.php');exit;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Entrar — 🍀 RaspaPix</title>
<meta name="theme-color" content="#07070f">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/main.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<style>
.login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;z-index:2}
.login-bg-orb{position:fixed;border-radius:50%;filter:blur(120px);pointer-events:none}
.login-wrap{width:100%;max-width:420px}
.auth-tabs{display:flex;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.06);border-radius:var(--r);padding:4px;margin-bottom:22px;gap:4px}
.auth-tab{flex:1;padding:9px;border-radius:var(--r-sm);font-size:.84rem;font-weight:700;text-align:center;cursor:pointer;color:var(--muted-2);transition:.2s;background:none;border:none;font-family:var(--font)}
.auth-tab.active{background:rgba(255,23,68,.12);color:var(--red);box-shadow:0 0 16px rgba(255,23,68,.08),inset 0 0 12px rgba(255,23,68,.04)}
.auth-form{display:none}.auth-form.active{display:block}
.trust-row{display:flex;align-items:center;justify-content:center;gap:14px;margin-top:16px;font-size:.72rem;color:var(--muted-2)}
.trust-row span{display:flex;align-items:center;gap:4px}
</style>
</head>
<body>
<canvas id="particles-canvas"></canvas>
<div id="toast-root"></div>
<div class="ambient"><div class="ambient-orb ambient-orb-1"></div><div class="ambient-orb ambient-orb-2"></div></div>

<!-- Page loader -->
<div id="lp-loader">
  <div class="loader-logo">🍀 RaspaPix<span class="dot">.</span></div>
  <div class="loader-bar"><div class="loader-fill"></div></div>
  <div class="loader-text">Carregando...</div>
</div>

<div class="login-page">
<div class="login-wrap">

  <!-- Logo -->
  <div style="text-align:center;margin-bottom:28px" id="login-logo">
    <div style="font-size:1.8rem;font-weight:900;color:#fff;display:flex;align-items:center;justify-content:center;gap:10px;letter-spacing:-.03em">
      🍀 RaspaPix<span style="color:var(--red);text-shadow:0 0 16px var(--red)">.</span>
    </div>
    <div style="font-size:.84rem;color:var(--muted-2);margin-top:6px">Plataforma de apostas premium</div>
  </div>

  <!-- Card -->
  <div class="card" style="background:rgba(15,15,32,.9);border-color:rgba(255,255,255,.07)" id="login-card">

    <!-- Tabs -->
    <div class="auth-tabs">
      <button class="auth-tab <?=$tab==='login'?'active':''?>" onclick="switchTab('login',this)">Entrar</button>
      <button class="auth-tab <?=$tab==='register'?'active':''?>" onclick="switchTab('register',this)">Cadastrar</button>
    </div>

    <?php if($error): ?>
    <div class="alert alert-error mb-2"><i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0"></i><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <!-- LOGIN -->
    <form method="POST" class="auth-form <?=$tab==='login'?'active':''?>" id="f-login">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label class="form-label">E-mail</label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-envelope form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="email" name="email" class="form-ctrl" placeholder="seu@email.com" required autocomplete="email">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" style="display:flex;justify-content:space-between">
          Senha <a href="#" style="color:var(--red);font-size:.72rem;font-weight:500">Esqueci minha senha</a>
        </label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-lock form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="password" name="password" class="form-ctrl" placeholder="••••••••" required autocomplete="current-password">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:6px">
        <i data-lucide="log-in" style="width:16px;height:16px"></i> Entrar na conta
      </button>
      <div style="text-align:center;margin-top:14px;font-size:.78rem;color:var(--muted-2)">── ou continue com ──</div>
      <button type="button" class="btn btn-ghost btn-full btn-md mt-1" style="margin-top:10px">
        <i class="fab fa-google"></i> Google
      </button>
    </form>

    <!-- REGISTER -->
    <form method="POST" class="auth-form <?=$tab==='register'?'active':''?>" id="f-register">
      <input type="hidden" name="action" value="register">
      <div class="form-group">
        <label class="form-label">Nome completo</label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-user form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="text" name="nome" class="form-ctrl" placeholder="Seu nome" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">E-mail</label>
        <div class="form-ctrl-wrap">
          <i class="fas fa-envelope form-ctrl-icon" style="font-size:.8rem"></i>
          <input type="email" name="email" class="form-ctrl" placeholder="seu@email.com" required>
        </div>
      </div>
      <div class="g2" style="gap:10px">
        <div class="form-group">
          <label class="form-label">Senha</label>
          <input type="password" name="password"  class="form-ctrl" placeholder="Mínimo 6" required>
        </div>
        <div class="form-group">
          <label class="form-label">Confirmar</label>
          <input type="password" name="password2" class="form-ctrl" placeholder="Repetir" required>
        </div>
      </div>
      <div style="font-size:.72rem;color:var(--muted-2);line-height:1.5;margin-bottom:14px">
        Ao criar sua conta você concorda com os <a href="#" style="color:var(--red)">Termos de Uso</a>.<br>Plataforma restrita a maiores de 18 anos.
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg">
        <i data-lucide="user-plus" style="width:16px;height:16px"></i> Criar conta grátis
      </button>
    </form>

  </div><!-- /card -->

  <div class="trust-row">
    <span>🔒 SSL Seguro</span>
    <span>🔞 +18 apenas</span>
    <span>🎲 Jogue com responsabilidade</span>
  </div>

</div>
</div>

<script src="/assets/js/main.js"></script>
<script>
function switchTab(id,btn){
  document.querySelectorAll('.auth-tab').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.auth-form').forEach(f=>f.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('f-'+id).classList.add('active');
}
// GSAP entrance
window.addEventListener('load',()=>{
  if(typeof gsap==='undefined') return;
  gsap.from('#login-logo',{opacity:0,y:-20,duration:.6,ease:'power2.out'});
  gsap.from('#login-card',{opacity:0,y:30,scale:.97,duration:.6,delay:.15,ease:'power2.out'});
});
if(typeof lucide!=='undefined') lucide.createIcons();
</script>
</body></html>
