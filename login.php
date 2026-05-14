<?php
session_start();
require_once __DIR__ . '/conexao.php';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if ($email && $pass) {
            $st = $pdo->prepare("SELECT id, nome, senha, saldo FROM usuarios WHERE email=? LIMIT 1");
            $st->execute([$email]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
            if ($u && password_verify($pass, $u['senha'])) {
                $_SESSION['usuario_id'] = $u['id'];
                $_SESSION['nome']       = $u['nome'];
                header('Location: /pages/dashboard.php'); exit;
            } else { $error = 'E-mail ou senha incorretos.'; }
        } else { $error = 'Preencha todos os campos.'; }

    } elseif ($action === 'register') {
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (!$nome || !$email || !$pass)    { $error = 'Preencha todos os campos.'; }
        elseif ($pass !== $pass2)           { $error = 'Senhas não coincidem.'; }
        elseif (strlen($pass) < 6)          { $error = 'Senha mínima: 6 caracteres.'; }
        else {
            $check = $pdo->prepare("SELECT id FROM usuarios WHERE email=? LIMIT 1");
            $check->execute([$email]);
            if ($check->fetch()) { $error = 'E-mail já cadastrado.'; }
            else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $ins  = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, saldo, criado_em) VALUES (?,?,?,0,NOW())");
                $ins->execute([$nome, $email, $hash]);
                $_SESSION['usuario_id'] = $pdo->lastInsertId();
                $_SESSION['nome']       = $nome;
                header('Location: /pages/dashboard.php'); exit;
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/main.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<style>
.login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative}
.login-bg{position:fixed;inset:0;background:radial-gradient(ellipse at 20% 50%,rgba(255,23,68,.12),transparent 50%),radial-gradient(ellipse at 80% 20%,rgba(255,23,68,.08),transparent 40%),var(--bg);z-index:0}
.login-card{width:100%;max-width:420px;position:relative;z-index:2}
.login-logo{text-align:center;margin-bottom:28px}
.login-logo .lg{font-size:2rem;font-weight:900;color:#fff;display:flex;align-items:center;justify-content:center;gap:8px}
.login-logo .dot{color:var(--red);text-shadow:0 0 16px var(--red)}
.login-logo p{color:var(--muted);font-size:.9rem;margin-top:6px}
.tabs-auth{display:flex;background:rgba(255,255,255,.04);border-radius:10px;padding:4px;margin-bottom:24px;border:1px solid var(--border)}
.tab-auth{flex:1;padding:9px;border-radius:7px;font-size:.875rem;font-weight:700;text-align:center;cursor:pointer;color:var(--muted);transition:.2s;background:none;border:none;font-family:inherit}
.tab-auth.active{background:rgba(255,23,68,.15);color:var(--red);box-shadow:0 0 12px rgba(255,23,68,.1)}
.auth-form{display:none}.auth-form.active{display:block}
.alert-error{background:rgba(255,23,68,.08);border:1px solid rgba(255,23,68,.25);border-radius:8px;padding:10px 14px;font-size:.85rem;color:var(--red);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.divider-or{display:flex;align-items:center;gap:12px;margin:16px 0;color:var(--muted);font-size:.8rem}
.divider-or::before,.divider-or::after{content:'';flex:1;height:1px;background:var(--border)}
.social-btn{width:100%;padding:11px;border-radius:var(--radius-sm);background:rgba(255,255,255,.04);border:1px solid var(--border2);color:rgba(255,255,255,.7);font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;margin-bottom:8px}
.social-btn:hover{background:rgba(255,255,255,.08);color:#fff}
</style>
</head>
<body>
<canvas id="particles-canvas"></canvas>
<div id="toast-container"></div>
<div class="login-bg"></div>

<div class="login-page">
  <div class="login-card">

    <div class="login-logo">
      <div class="lg">🍀 RaspaPix<span class="dot">.</span></div>
      <p>Plataforma de apostas premium</p>
    </div>

    <div class="card card-glow">
      <div class="tabs-auth">
        <button class="tab-auth active" onclick="switchAuth('login',this)">Entrar</button>
        <button class="tab-auth" onclick="switchAuth('register',this)">Cadastrar</button>
      </div>

      <?php if ($error): ?>
      <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- LOGIN -->
      <form method="POST" class="auth-form active" id="form-login">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <div class="form-input-icon">
            <i class="fas fa-envelope fi-icon"></i>
            <input type="email" name="email" class="form-input" placeholder="seu@email.com" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" style="display:flex;justify-content:space-between">
            Senha <a href="#" style="color:var(--red);font-size:.78rem">Esqueci a senha</a>
          </label>
          <div class="form-input-icon">
            <i class="fas fa-lock fi-icon"></i>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:4px">
          <i class="fas fa-sign-in-alt"></i> Entrar
        </button>
        <div class="divider-or">ou</div>
        <button type="button" class="social-btn"><i class="fab fa-google"></i> Entrar com Google</button>
      </form>

      <!-- REGISTER -->
      <form method="POST" class="auth-form" id="form-register">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
          <label class="form-label">Nome Completo</label>
          <div class="form-input-icon">
            <i class="fas fa-user fi-icon"></i>
            <input type="text" name="nome" class="form-input" placeholder="Seu nome" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <div class="form-input-icon">
            <i class="fas fa-envelope fi-icon"></i>
            <input type="email" name="email" class="form-input" placeholder="seu@email.com" required>
          </div>
        </div>
        <div class="grid-2" style="gap:10px">
          <div class="form-group">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-input" placeholder="••••••" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirmar</label>
            <input type="password" name="password2" class="form-input" placeholder="••••••" required>
          </div>
        </div>
        <div style="font-size:.75rem;color:var(--muted);margin-bottom:14px">
          Ao cadastrar você concorda com nossos <a href="#" style="color:var(--red)">Termos de Uso</a>. +18 apenas.
        </div>
        <button type="submit" class="btn btn-primary btn-full btn-lg">
          <i class="fas fa-user-plus"></i> Criar Conta Grátis
        </button>
      </form>
    </div>

    <p style="text-align:center;font-size:.75rem;color:var(--muted);margin-top:16px">
      🔒 Conexão segura SSL &nbsp;·&nbsp; 🔞 +18 &nbsp;·&nbsp; 🎲 Jogue com responsabilidade
    </p>
  </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
function switchAuth(tab, btn) {
  document.querySelectorAll('.tab-auth').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.auth-form').forEach(f=>f.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('form-'+tab).classList.add('active');
}
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body></html>
