<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$usuario = getLoggedUser($pdo);

// Handle delete
if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("SELECT imagem FROM noticias WHERE id = ? AND autor_id = ?");
    $stmt->execute([$_GET['excluir'], $usuario['id']]);
    $noticia = $stmt->fetch();

    if ($noticia) {
        if ($noticia['imagem'] && file_exists(__DIR__ . '/../uploads/' . $noticia['imagem'])) {
            unlink(__DIR__ . '/../uploads/' . $noticia['imagem']);
        }
        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ? AND autor_id = ?");
        $stmt->execute([$_GET['excluir'], $usuario['id']]);
        setFlash('success', 'Notícia excluída com sucesso!');
    }
    header('Location: /ProjetoAstronomia-php/pages/minhas-noticias.php');
    exit;
}

// Get user's news
$stmt = $pdo->prepare("
    SELECT n.*, c.nome as categoria_nome, c.icone as categoria_icone
    FROM noticias n
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.autor_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$usuario['id']]);
$noticias = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="manage-header">
        <h1><i class="fas fa-newspaper"></i> Minhas Notícias</h1>
        <a href="/ProjetoAstronomia-php/pages/noticia-form.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nova Notícia
        </a>
    </div>

    <?php if (empty($noticias)): ?>
        <div class="empty-state">
            <i class="fas fa-satellite"></i>
            <h3>Nenhuma notícia publicada</h3>
            <p>Comece a explorar o universo compartilhando sua primeira notícia!</p>
            <a href="/ProjetoAstronomia-php/pages/noticia-form.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Criar Primeira Notícia
            </a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Notícia</th>
                        <th>Categoria</th>
                        <th>Data</th>
                        <th>Destaque</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($noticias as $n): ?>
                    <tr>
                        <td>
                            <a href="/ProjetoAstronomia-php/noticia.php?id=<?= $n['id'] ?>" style="color: var(--text-primary); font-weight: 600;">
                                <?= htmlspecialchars($n['titulo']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($n['categoria_nome']): ?>
                                <span class="badge" style="background: var(--accent-subtle); color: var(--accent-light);">
                                    <i class="fas fa-<?= htmlspecialchars($n['categoria_icone'] ?? 'tag') ?>"></i>
                                    <?= htmlspecialchars($n['categoria_nome']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></td>
                        <td>
                            <?php if ($n['destaque']): ?>
                                <i class="fas fa-star" style="color: var(--amber);"></i>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/ProjetoAstronomia-php/pages/noticia-form.php?id=<?= $n['id'] ?>" class="btn btn-secondary btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger btn-sm"
                                        data-delete="/ProjetoAstronomia-php/pages/minhas-noticias.php?excluir=<?= $n['id'] ?>"
                                        data-name="<?= htmlspecialchars($n['titulo']) ?>"
                                        title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
