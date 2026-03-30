<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (isLoggedIn()) {
    header('Location: /ProjetoAstronomia-php/');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            setFlash('success', 'Bem-vindo de volta, ' . $user['nome'] . '!');
            header('Location: /ProjetoAstronomia-php/');
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - CosmosNews</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/ProjetoAstronomia-php/assets/css/style.css">
</head>
<body>
    <div class="stars-container">
        <div id="stars"></div>
        <div id="stars2"></div>
        <div id="stars3"></div>
    </div>

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h1>Entrar no CosmosNews</h1>
                <p>Explore o universo das notícias</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="form-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="seu@email.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="form-icon-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" class="form-control"
                               placeholder="Sua senha" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>

            <div class="auth-footer">
                Não tem uma conta? <a href="/ProjetoAstronomia-php/cadastro.php">Cadastre-se</a>
            </div>

            <div class="auth-footer mt-1">
                <a href="/ProjetoAstronomia-php/"><i class="fas fa-arrow-left"></i> Voltar ao portal</a>
            </div>
        </div>
    </div>

    <script src="/ProjetoAstronomia-php/assets/js/main.js"></script>
</body>
</html>
