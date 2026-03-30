<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$usuario = getLoggedUser($pdo);
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'atualizar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';

        if (empty($nome) || empty($email)) {
            $erro = 'Nome e e-mail são obrigatórios.';
        } else {
            // Check if email changed and is available
            if ($email !== $usuario['email']) {
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $stmt->execute([$email, $usuario['id']]);
                if ($stmt->fetch()) {
                    $erro = 'Este e-mail já está em uso.';
                }
            }

            if (!$erro) {
                if (!empty($nova_senha)) {
                    if (empty($senha_atual) || !password_verify($senha_atual, $usuario['senha'])) {
                        $erro = 'Senha atual incorreta.';
                    } elseif (strlen($nova_senha) < 6) {
                        $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
                    } else {
                        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?");
                        $stmt->execute([$nome, $email, $hash, $usuario['id']]);
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
                    $stmt->execute([$nome, $email, $usuario['id']]);
                }

                if (!$erro) {
                    $_SESSION['usuario_nome'] = $nome;
                    setFlash('success', 'Perfil atualizado com sucesso!');
                    header('Location: /ProjetoAstronomia-php/pages/perfil.php');
                    exit;
                }
            }
        }
    } elseif ($acao === 'excluir') {
        $senha_confirma = $_POST['senha_confirma'] ?? '';
        if (password_verify($senha_confirma, $usuario['senha'])) {
            // Delete user's news
            $stmt = $pdo->prepare("DELETE FROM noticias WHERE autor_id = ?");
            $stmt->execute([$usuario['id']]);
            // Delete user
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            session_destroy();
            header('Location: /ProjetoAstronomia-php/login.php');
            exit;
        } else {
            $erro = 'Senha incorreta. Conta não excluída.';
        }
    }

    // Refresh user data
    $usuario = getLoggedUser($pdo);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="profile-card">
        <div class="profile-avatar">
            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
        </div>

        <h1 class="text-center" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; margin-bottom: 4px;">
            <?= htmlspecialchars($usuario['nome']) ?>
        </h1>
        <p class="text-center text-muted mb-3"><?= htmlspecialchars($usuario['email']) ?></p>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="acao" value="atualizar">

            <div class="form-group">
                <label for="nome">Nome</label>
                <div class="form-icon-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="nome" name="nome" class="form-control"
                           value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">E-mail</label>
                <div class="form-icon-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="senha_atual">Senha atual (somente para alterar senha)</label>
                <div class="form-icon-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="senha_atual" name="senha_atual" class="form-control"
                           placeholder="Deixe em branco para manter">
                </div>
            </div>

            <div class="form-group">
                <label for="nova_senha">Nova senha</label>
                <div class="form-icon-group">
                    <i class="fas fa-key"></i>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control"
                           placeholder="Deixe em branco para manter">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>

        <hr style="border: none; border-top: 1px solid var(--border); margin: 32px 0;">

        <h3 style="color: var(--rose); font-size: 1rem; margin-bottom: 12px;">
            <i class="fas fa-exclamation-triangle"></i> Zona de Perigo
        </h3>
        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 16px;">
            Ao excluir sua conta, todas as suas notícias também serão removidas permanentemente.
        </p>

        <form method="POST" action="" id="formExcluir">
            <input type="hidden" name="acao" value="excluir">
            <div class="form-group">
                <label for="senha_confirma">Digite sua senha para confirmar</label>
                <div class="form-icon-group">
                    <i class="fas fa-shield-alt"></i>
                    <input type="password" id="senha_confirma" name="senha_confirma" class="form-control"
                           placeholder="Sua senha atual">
                </div>
            </div>
            <button type="submit" class="btn btn-danger btn-block"
                    onclick="return confirm('Tem certeza? Esta ação é irreversível!')">
                <i class="fas fa-trash"></i> Excluir Minha Conta
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
