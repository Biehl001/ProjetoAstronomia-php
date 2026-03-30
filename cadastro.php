<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (isLoggedIn()) {
    header('Location: /ProjetoAstronomia-php/');
    exit;
}

$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar) {
        $erro = 'As senhas não coincidem.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$nome, $email, $hash]);

            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            setFlash('success', 'Conta criada com sucesso! Bem-vindo ao CosmosNews!');
            header('Location: /ProjetoAstronomia-php/');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - CosmosNews</title>
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
                    <i class="fas fa-user-astronaut"></i>
                </div>
                <h1>Criar Conta</h1>
                <p>Junte-se à nossa comunidade espacial</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nome" name="nome" class="form-control"
                               placeholder="Seu nome" value="<?= htmlspecialchars($nome) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="form-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="seu@email.com" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="form-icon-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="senha" name="senha" class="form-control"
                               placeholder="Mínimo 6 caracteres" required>
                    </div>
                    <span class="form-hint">Use pelo menos 6 caracteres</span>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar senha</label>
                    <div class="form-icon-group">
                        <i class="fas fa-shield-alt"></i>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control"
                               placeholder="Repita a senha" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-rocket"></i> Criar Conta
                </button>
            </form>

            <div class="auth-footer">
                Já tem uma conta? <a href="/ProjetoAstronomia-php/login.php">Entrar</a>
            </div>

            <div class="auth-footer mt-1">
                <a href="/ProjetoAstronomia-php/"><i class="fas fa-arrow-left"></i> Voltar ao portal</a>
            </div>
        </div>
    </div>

    <script src="/ProjetoAstronomia-php/assets/js/main.js"></script>
</body>
</html>
