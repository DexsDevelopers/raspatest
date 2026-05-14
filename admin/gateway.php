<?php
include '../includes/session.php';
include '../conexao.php';
include '../includes/notiflix.php';

$usuarioId = $_SESSION['usuario_id'];
$admin = ($stmt = $pdo->prepare("SELECT admin FROM usuarios WHERE id = ?"))->execute([$usuarioId]) ? $stmt->fetchColumn() : null;

if ($admin != 1) {
    $_SESSION['message'] = ['type' => 'warning', 'text' => 'Você não é um administrador!'];
    header("Location: /");
    exit;
}

// Processar formulários
if (isset($_POST['salvar_gateway'])) {
    $gateway_ativa = $_POST['gateway_ativa'];

    $stmt = $pdo->prepare("UPDATE gateway SET active = ?");
    if ($stmt->execute([$gateway_ativa])) {
        $_SESSION['success'] = 'Gateway Alterada!';
    } else {
        $_SESSION['failure'] = 'Erro ao alterar a Gateway!';
    }
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['salvar_lunarpay'])) {
    $api_key = trim($_POST['lunarpay_api_key']);

    $stmt = $pdo->prepare("UPDATE lunarpay SET api_key = ? WHERE id = 1");
    if ($stmt->execute([$api_key])) {
        $_SESSION['success'] = 'API Key LunarPay salva com sucesso!';
    } else {
        $_SESSION['failure'] = 'Erro ao salvar a API Key LunarPay!';
    }
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

// Buscar dados
$nome = ($stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?"))->execute([$usuarioId]) ? $stmt->fetchColumn() : null;
$nome = $nome ? explode(' ', $nome)[0] : null;

$stmt = $pdo->query("SELECT active FROM gateway");
$gateway = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar dados LunarPay
try {
    $stmt = $pdo->query("SELECT api_key FROM lunarpay WHERE id = 1");
    $lunarpay = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lunarpay = null;
}
if (!$lunarpay) {
    $lunarpay = ['api_key' => ''];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeSite ?? 'Admin'; ?> - Configuração de Gateway</title>
            <?php 
    // Se as variáveis não estiverem definidas, buscar do banco
    if (!isset($faviconSite)) {
        try {
            $stmt = $pdo->prepare("SELECT favicon FROM config WHERE id = 1 LIMIT 1");
            $stmt->execute();
            $config_favicon = $stmt->fetch(PDO::FETCH_ASSOC);
            $faviconSite = $config_favicon['favicon'] ?? null;
            
            // Se $nomeSite não estiver definido, buscar também
            if (!isset($nomeSite)) {
                $stmt = $pdo->prepare("SELECT nome_site FROM config WHERE id = 1 LIMIT 1");
                $stmt->execute();
                $config_nome = $stmt->fetch(PDO::FETCH_ASSOC);
                $nomeSite = $config_nome['nome_site'] ?? 'Raspadinha';
            }
        } catch (PDOException $e) {
            $faviconSite = null;
            $nomeSite = $nomeSite ?? 'Raspadinha';
        }
    }
    ?>
    <?php if ($faviconSite && file_exists($_SERVER['DOCUMENT_ROOT'] . $faviconSite)): ?>
        <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($faviconSite) ?>"/>
        <link rel="shortcut icon" href="<?= htmlspecialchars($faviconSite) ?>"/>
        <link rel="apple-touch-icon" href="<?= htmlspecialchars($faviconSite) ?>"/>
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<?= urlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="#22c55e"/><text x="50" y="50" text-anchor="middle" dominant-baseline="middle" fill="white" font-family="Arial" font-size="40" font-weight="bold">' . strtoupper(substr($nomeSite, 0, 1)) . '</text></svg>') ?>"/>
    <?php endif; ?>
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Notiflix -->
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/dist/notiflix-aio-3.2.8.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/notiflix@3.2.8/src/notiflix.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #000000;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 320px;
            height: 100vh;
            background: linear-gradient(145deg, #0a0a0a 0%, #141414 25%, #1a1a1a 50%, #0f0f0f 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(34, 197, 94, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            box-shadow: 
                0 0 50px rgba(34, 197, 94, 0.1),
                inset 1px 0 0 rgba(255, 255, 255, 0.05);
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(34, 197, 94, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            opacity: 0.8;
            pointer-events: none;
        }
        
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            position: relative;
            padding: 2.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, transparent 100%);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            position: relative;
            z-index: 2;
        }
        
        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            box-shadow: 
                0 8px 20px rgba(34, 197, 94, 0.3),
                0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .logo-icon::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, #22c55e, #16a34a, #22c55e);
            border-radius: 18px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .logo:hover .logo-icon::after {
            opacity: 1;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        
        .logo-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
        }
        
        .nav-menu {
            padding: 2rem 0;
            position: relative;
        }
        
        .nav-section {
            margin-bottom: 2rem;
        }
        
        .nav-section-title {
            padding: 0 2rem 0.75rem 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            color: #a1a1aa;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin: 0.25rem 1rem;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 0 4px 4px 0;
            transform: scaleY(0);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .nav-item:hover::before,
        .nav-item.active::before {
            transform: scaleY(1);
        }
        
        .nav-item:hover,
        .nav-item.active {
            color: #ffffff;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
            border: 1px solid rgba(34, 197, 94, 0.2);
            transform: translateX(4px);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
        }
        
        .nav-icon {
            width: 24px;
            height: 24px;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            position: relative;
        }
        
        .nav-text {
            font-size: 0.95rem;
            flex: 1;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 320px;
            min-height: 100vh;
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: 
                radial-gradient(circle at 10% 20%, rgba(34, 197, 94, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.02) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.01) 0%, transparent 50%);
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        .header {
            position: sticky;
            top: 0;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem 2.5rem;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .menu-toggle {
            display: none;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #22c55e;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .menu-toggle:hover {
            background: rgba(34, 197, 94, 0.2);
            transform: scale(1.05);
        }
        
        .page-content {
            padding: 2.5rem;
        }
        
        .welcome-section {
            margin-bottom: 3rem;
        }
        
        .welcome-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, #ffffff 0%, #fff 50%, #fff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        
        .welcome-subtitle {
            font-size: 1.25rem;
            color: #6b7280;
            font-weight: 400;
            margin-bottom: 2rem;
        }
        
        /* Gateway Navbar */
        .gateway-navbar {
            background: linear-gradient(135deg, rgba(20, 20, 20, 0.95) 0%, rgba(10, 10, 10, 0.98) 100%);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .gateway-navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .gateway-current {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 2;
        }
        
        .gateway-current-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
        }
        
        .gateway-current-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .gateway-current-label {
            font-size: 0.875rem;
            color: #9ca3af;
            font-weight: 500;
        }
        
        .gateway-current-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: #22c55e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .gateway-switch-form {
            position: relative;
            z-index: 2;
        }
        
        .gateway-select {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            color: white;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
        .gateway-select:focus {
            outline: none;
            border-color: rgba(34, 197, 94, 0.5);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            background: rgba(0, 0, 0, 0.6);
        }
        
        .gateway-select:hover {
            border-color: rgba(34, 197, 94, 0.3);
            background: rgba(0, 0, 0, 0.5);
        }
        
        .gateway-select option {
            background: #1f2937;
            color: white;
            padding: 0.75rem;
        }
        
        /* Forms Grid */
        .forms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 2rem;
        }
        
        /* Form Container */
        .form-container {
            background: linear-gradient(135deg, rgba(20, 20, 20, 0.8) 0%, rgba(10, 10, 10, 0.9) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: fit-content;
        }
        
        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .form-container:hover::before {
            opacity: 1;
        }
        
        .form-container:hover {
            transform: translateY(-4px);
            border-color: rgba(34, 197, 94, 0.2);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
        }
        
        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-title i {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2) 0%, rgba(34, 197, 94, 0.1) 100%);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #22c55e;
            font-size: 1rem;
        }
        
        /* Gateway Status */
        .gateway-status {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .gateway-status i {
            color: #22c55e;
            font-size: 1.5rem;
        }
        
        .gateway-status-text {
            color: #22c55e;
            font-weight: 600;
            font-size: 1rem;
        }

        /* Security Warning */
        .security-warning {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0.05) 100%);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .security-warning i {
            color: #fbbf24;
            font-size: 1.25rem;
            margin-top: 0.2rem;
        }
        
        .security-warning-content {
            color: #fbbf24;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .security-warning strong {
            font-weight: 700;
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            color: #e5e7eb;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-label i {
            color: #22c55e;
            font-size: 0.875rem;
        }
        
        .form-input, .form-select {
            width: 100%;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            color: white;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .form-input.with-toggle {
            padding-right: 3.5rem;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: rgba(34, 197, 94, 0.5);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            background: rgba(0, 0, 0, 0.6);
        }
        
        .form-input::placeholder {
            color: #6b7280;
        }
        
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.5em 1.5em;
            cursor: pointer;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 2.75rem;
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            transition: color 0.3s ease;
            font-size: 1.1rem;
            padding: 0.5rem;
        }
        
        .password-toggle:hover {
            color: #22c55e;
        }
        
        /* Submit Button */
        .submit-button {
            width: 100%;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1.25rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 8px 20px rgba(34, 197, 94, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .submit-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        
        .submit-button:hover::before {
            transform: translateX(0);
        }
        
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(34, 197, 94, 0.3);
            background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
        }
        
        .submit-button:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
        }
        
        /* Instructions List */
        .instructions-list {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .instructions-title {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .instructions-title i {
            color: #3b82f6;
        }
        
        .instruction-step {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .step-number {
            width: 24px;
            height: 24px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }
        
        .step-text {
            color: #9ca3af;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .step-text code {
            background: rgba(0, 0, 0, 0.5);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            color: #60a5fa;
            font-family: monospace;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Mobile Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Responsive tweaks... */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar:not(.hidden) {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            .gateway-navbar {
                flex-direction: column;
                align-items: stretch;
                gap: 1.5rem;
            }
            .gateway-switch-form {
                width: 100%;
            }
            .gateway-select {
                width: 100%;
            }
            .forms-grid {
                grid-template-columns: 1fr;
            }
            .form-container {
                padding: 1.5rem;
            }
            .welcome-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="logo-text">
                    <span class="logo-title">Admin</span>
                </div>
            </a>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <a href="index.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-home"></i></div>
                    <div class="nav-text">Dashboard</div>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Gestão</div>
                <a href="usuarios.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-user"></i></div>
                    <div class="nav-text">Usuários</div>
                </a>
                <a href="afiliados.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="nav-text">Afiliados</div>
                </a>
                <a href="depositos.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="nav-text">Depósitos</div>
                </a>
                <a href="saques.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="nav-text">Saques</div>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Sistema</div>
                <a href="config.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-cogs"></i></div>
                    <div class="nav-text">Configurações</div>
                </a>
                <a href="gateway.php" class="nav-item active">
                    <div class="nav-icon"><i class="fas fa-usd"></i></div>
                    <div class="nav-text">Gateway</div>
                </a>
                <a href="banners.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-images"></i></div>
                    <div class="nav-text">Banners</div>
                </a>
                <a href="cartelas.php" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-diamond"></i></div>
                    <div class="nav-text">Raspadinhas</div>
                </a>
                <a href="../logout" class="nav-item">
                    <div class="nav-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <div class="nav-text">Sair</div>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="color: #a1a1aa; font-size: 0.9rem;">Bem-vindo, <?= htmlspecialchars($nome) ?></span>
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #22c55e, #16a34a); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #ffffff; font-size: 1rem;">
                        <?= strtoupper(substr($nome, 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <div class="page-content">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h2 class="welcome-title">Gateway de Pagamento</h2>
                <p class="welcome-subtitle">Configure e gerencie os Gateways de pagamento da plataforma</p>
                
                <!-- Gateway Status Navbar -->
                <div class="gateway-navbar">
                    <div class="gateway-current">
                        <div class="gateway-current-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="gateway-current-info">
                            <span class="gateway-current-label">Gateway Ativo:</span>
                            <span class="gateway-current-value"><?= strtoupper($gateway['active']) ?></span>
                        </div>
                    </div>
                    
                    <form method="POST" class="gateway-switch-form">
                        <select name="gateway_ativa" class="gateway-select" onchange="this.form.submit()">
                            <option value="lunarpay" selected>
                                LunarPay
                            </option>
                        </select>
                        <input type="hidden" name="salvar_gateway" value="1">
                    </form>
                </div>
            </section>
            
            <!-- Forms Grid -->
            <div class="forms-grid">
                <!-- LunarPay Credentials -->
                <div class="form-container">
                    <form method="POST">
                        <h2 class="form-title">
                            <i class="fas fa-moon"></i>
                            Credenciais LunarPay
                        </h2>

                        <div class="security-warning">
                            <i class="fas fa-shield-alt"></i>
                            <div class="security-warning-content">
                                <strong>Segurança:</strong> Mantenha sua API Key apenas no backend.
                                Nunca a exponha publicamente.
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-key"></i>
                                API Key (Bearer Token)
                            </label>
                            <input type="password" name="lunarpay_api_key"
                                value="<?= htmlspecialchars($lunarpay['api_key'] ?? '') ?>"
                                class="form-input with-toggle"
                                placeholder="Sua API Key do painel LunarPay"
                                id="lunarpayKey">
                            <button type="button" class="password-toggle" onclick="togglePassword('lunarpayKey', 'lunarToggleIcon')">
                                <i class="fas fa-eye" id="lunarToggleIcon"></i>
                            </button>
                        </div>

                        <button type="submit" name="salvar_lunarpay" class="submit-button">
                            <i class="fas fa-save"></i>
                            Salvar API Key
                        </button>

                        <div class="instructions-list">
                            <h3 class="instructions-title">
                                <i class="fas fa-info-circle"></i>
                                Como Configurar o Webhook
                            </h3>
                            <div class="instruction-step">
                                <div class="step-number">1</div>
                                <div class="step-text">Acesse seu painel em <a href="https://lunarpay.site" target="_blank" style="color:#60a5fa;">lunarpay.site</a> → Configurações → Desenvolvedor / API.</div>
                            </div>
                            <div class="instruction-step">
                                <div class="step-number">2</div>
                                <div class="step-text">Copie sua API Key e cole no campo acima.</div>
                            </div>
                            <div class="instruction-step">
                                <div class="step-number">3</div>
                                <?php
                                $protocol = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https://" : "http://";
                                $webhookLunar = $protocol . $_SERVER['HTTP_HOST'] . "/callback/lunarpay.php";
                                ?>
                                <div class="step-text">Configure o Webhook no painel com a URL:<br><code style="word-break:break-all;"><?= $webhookLunar ?></code></div>
                            </div>
                            <div class="instruction-step">
                                <div class="step-number">4</div>
                                <div class="step-text">Selecione o evento <strong>payment.confirmed</strong>.</div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>
    
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const overlay = document.getElementById('overlay');
        
        menuToggle.addEventListener('click', () => {
            const isHidden = sidebar.classList.contains('hidden');
            
            if (isHidden) {
                sidebar.classList.remove('hidden');
                overlay.classList.add('active');
            } else {
                sidebar.classList.add('hidden');
                overlay.classList.add('active');
            }
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.add('hidden');
            overlay.classList.remove('active');
        });
        
        // Close sidebar on window resize if it's mobile
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                sidebar.classList.add('hidden');
                overlay.classList.remove('active');
            } else {
                sidebar.classList.remove('hidden');
                overlay.classList.remove('active');
            }
        });
        
        // Password toggle function
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        <?php if (isset($_SESSION['success'])): ?>
            Notiflix.Notify.success('<?= $_SESSION['success'] ?>', {
                position: 'right-bottom',
                timeout: 3000,
                borderRadius: '12px',
                cssAnimationStyle: 'zoom',
                fontFamily: 'Inter'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['failure'])): ?>
            Notiflix.Notify.failure('<?= $_SESSION['failure'] ?>', {
                position: 'right-bottom',
                timeout: 3000,
                borderRadius: '12px',
                cssAnimationStyle: 'zoom',
                fontFamily: 'Inter'
            });
            <?php unset($_SESSION['failure']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            Notiflix.Notify.<?= $_SESSION['message']['type'] ?>('<?= $_SESSION['message']['text'] ?>', {
                position: 'right-bottom',
                timeout: 3000,
                borderRadius: '12px',
                cssAnimationStyle: 'zoom',
                fontFamily: 'Inter'
            });
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>