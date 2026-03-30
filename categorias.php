<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$categoria_id = intval($_GET['id'] ?? 0);

// Handle redirect before any HTML output
if ($categoria_id) {
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$categoria_id]);
    $categoria = $stmt->fetch();

    if (!$categoria) {
        setFlash('danger', 'Categoria não encontrada.');
        header('Location: /ProjetoAstronomia-php/categorias.php');
        exit;
    }
}

// Now safe to include header
include __DIR__ . '/includes/header.php';

function timeAgoCat($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'agora';
    if ($diff < 3600) return floor($diff / 60) . ' min atrás';
    if ($diff < 86400) return floor($diff / 3600) . 'h atrás';
    if ($diff < 604800) return floor($diff / 86400) . ' dias atrás';
    return date('d/m/Y', $time);
}

if ($categoria_id) {
    // Pagination
    $porPagina = 9;
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $porPagina;

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM noticias WHERE categoria_id = ?");
    $stmtTotal->execute([$categoria_id]);
    $total = $stmtTotal->fetchColumn();
    $totalPaginas = ceil($total / $porPagina);

    $stmt = $pdo->prepare("
        SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.icone as categoria_icone
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        LEFT JOIN categorias c ON n.categoria_id = c.id
        WHERE n.categoria_id = ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $categoria_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $noticias = $stmt->fetchAll();
    ?>

    <div class="container">
        <div class="page-header">
            <div style="display: inline-flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div class="category-card-icon" style="margin: 0;">
                    <i class="fas fa-<?= htmlspecialchars($categoria['icone']) ?>"></i>
                </div>
            </div>
            <h1><?= htmlspecialchars($categoria['nome']) ?></h1>
            <?php if ($categoria['descricao']): ?>
                <p><?= htmlspecialchars($categoria['descricao']) ?></p>
            <?php endif; ?>
            <p class="text-muted mt-1"><?= $total ?> notícia(s) encontrada(s)</p>
        </div>

        <?php if (empty($noticias)): ?>
            <div class="empty-state">
                <i class="fas fa-satellite"></i>
                <h3>Nenhuma notícia nesta categoria</h3>
                <p>Ainda não há publicações aqui.</p>
                <a href="/ProjetoAstronomia-php/categorias.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Ver Todas as Categorias
                </a>
            </div>
        <?php else: ?>
            <div class="news-grid">
                <?php foreach ($noticias as $n): ?>
                <a href="/ProjetoAstronomia-php/noticia.php?id=<?= $n['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="news-card">
                        <div class="news-card-img">
                            <?php if ($n['imagem']): ?>
                                <img src="/ProjetoAstronomia-php/uploads/<?= htmlspecialchars($n['imagem']) ?>"
                                     alt="<?= htmlspecialchars($n['titulo']) ?>">
                            <?php else: ?>
                                <div class="no-image"><i class="fas fa-star"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="news-card-body">
                            <h3><?= htmlspecialchars($n['titulo']) ?></h3>
                            <p class="excerpt"><?= htmlspecialchars($n['resumo'] ?: mb_substr(strip_tags($n['noticia']), 0, 120) . '...') ?></p>
                            <div class="news-card-meta">
                                <div class="author">
                                    <div class="author-avatar"><?= strtoupper(substr($n['autor_nome'], 0, 1)) ?></div>
                                    <?= htmlspecialchars($n['autor_nome']) ?>
                                </div>
                                <div class="date">
                                    <i class="far fa-clock"></i> <?= timeAgoCat($n['created_at']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina > 1): ?>
                    <a href="?id=<?= $categoria_id ?>&pagina=<?= $pagina - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php if ($i == $pagina): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?id=<?= $categoria_id ?>&pagina=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?id=<?= $categoria_id ?>&pagina=<?= $pagina + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php } else {
    // Show all categories
    $categorias = $pdo->query("
        SELECT c.*, COUNT(n.id) as total
        FROM categorias c
        LEFT JOIN noticias n ON n.categoria_id = c.id
        GROUP BY c.id
        ORDER BY c.nome ASC
    ")->fetchAll();
    ?>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-layer-group" style="color: var(--accent-light);"></i> Categorias</h1>
            <p>Explore as notícias por área de interesse</p>
        </div>

        <?php if (empty($categorias)): ?>
            <div class="empty-state">
                <i class="fas fa-layer-group"></i>
                <h3>Nenhuma categoria criada</h3>
                <p>As categorias aparecerão aqui quando forem criadas.</p>
            </div>
        <?php else: ?>
            <div class="categories-grid">
                <?php foreach ($categorias as $cat): ?>
                <a href="/ProjetoAstronomia-php/categorias.php?id=<?= $cat['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="category-card">
                        <div class="category-card-icon">
                            <i class="fas fa-<?= htmlspecialchars($cat['icone']) ?>"></i>
                        </div>
                        <h3><span><?= htmlspecialchars($cat['nome']) ?></span></h3>
                        <?php if (!empty($cat['descricao'])): ?>
                            <p style="margin-bottom: 8px;"><?= htmlspecialchars(mb_substr($cat['descricao'], 0, 60)) ?></p>
                        <?php endif; ?>
                        <p><?= $cat['total'] ?> notícia(s)</p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php } ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
