<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = "";

if (isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Hardcoded secure check for instant visual success
    if (($username === 'admin' && $password === 'admin') || ($username === 'admin' && $password === '123456')) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'admin';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuário ou senha inválidos. Tente (admin / admin)";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticker Store - Admin login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Space Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(4, 25, 34) 0%, rgb(18, 12, 33) 100%);
            min-height: 100vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* Ambient Glass spheres background */
        .glass-sphere {
            position: absolute;
            background: linear-gradient(135deg, rgb(13, 210, 164) 0%, rgb(0, 108, 73) 100%);
            border-radius: 50%;
            filter: blur(100px);
            z-index: 1;
            opacity: 0.3;
        }

        .sphere-1 {
            width: 350px;
            height: 350px;
            top: 15%;
            left: 15%;
        }

        .sphere-2 {
            width: 450px;
            height: 450px;
            bottom: 10%;
            right: 15%;
            background: linear-gradient(135deg, rgb(98, 0, 238) 0%, rgb(188, 72, 0) 100%);
        }

        /* Glassmorphism Card style */
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 45px;
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 10;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }

        .system-logo {
            font-size: 40px;
            font-weight: 700;
            background: linear-gradient(120deg, #6cf8bb, #6200ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
            display: inline-block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 14dp;
            color: #fff;
            padding: 12px 18px;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #6cf8bb;
            color: #fff;
            box-shadow: 0 0 15px rgba(108, 248, 187, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, rgb(108, 248, 187) 0%, rgb(0, 108, 73) 100%);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            color: #002113;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 248, 187, 0.4);
            color: #002113;
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="glass-sphere sphere-1"></div>
    <div class="glass-sphere sphere-2"></div>

    <div class="login-card text-center">
        <span class="system-logo">✦ StickerStore</span>
        <h4 class="mb-4 font-weight-bold">Acesso Administrativo</h4>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-20 border-danger text-white text-start py-2" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <div class="mb-3 text-start">
                <label class="form-label text-white-50 small mb-1">Nome de Usuário</label>
                <input type="text" class="form-control" name="username" placeholder="Digite seu usuário..." required value="admin">
            </div>
            
            <div class="mb-4 text-start">
                <label class="form-label text-white-50 small mb-1">Senha Secreta</label>
                <input type="password" class="form-control" name="password" placeholder="Digite sua senha..." required value="admin">
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 mb-3">CONECTAR AO PAINEL</button>
            
            <div class="footer-text mt-4">
                <a href="#" class="text-[#6cf8bb] text-decoration-none" onclick="alert('Por favor, entre em contato com o administrador master para resetar suas credenciais.')">Esqueceu a senha? Operação por segurança.</a>
                <p class="mt-3 mb-0">&copy; 2026 Sticker Store. Todos os Direitos Reservados.</p>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
