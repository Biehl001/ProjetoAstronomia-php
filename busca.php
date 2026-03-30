<?php
include __DIR__ . '/includes/header.php';

$busca = trim($_GET['q'] ?? '');
$noticias = [];
$total = 0;

function timeAgoBusca($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'agora';
    if ($diff < 3600) return floor($diff / 60) . ' min atrás';
    if ($diff < 86400) return floor($diff / 3600) . 'h atrás';
    if ($diff < 604800) return floor($diff / 86400) . ' dias atrás';
    return date('d/m/Y', $time);
}

if ($busca !== '') {
    $porPagina = 12;
    $pagina = max(1, intval($_GET['pagina'] ?? 1));
    $offset = ($pagina - 1) * $porPagina;
    $termo = "%{$busca}%";

    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM noticias WHERE titulo LIKE ? OR noticia LIKE ? OR resumo LIKE ?");
    $stmtTotal->execute([$termo, $termo, $termo]);
    $total = $stmtTotal->fetchColumn();
    $totalPaginas = ceil($total / $porPagina);

    $stmt = $pdo->prepare("
        SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.icone as categoria_icone
        FROM noticias n
        JOIN usuarios u ON n.autor_id = u.id
        LEFT JOIN categorias c ON n.categoria_id = c.id
        WHERE n.titulo LIKE ? OR n.noticia LIKE ? OR n.resumo LIKE ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $termo, PDO::PARAM_STR);
    $stmt->bindValue(2, $termo, PDO::PARAM_STR);
    $stmt->bindValue(3, $termo, PDO::PARAM_STR);
    $stmt->bindValue(4, $porPagina, PDO::PARAM_INT);
    $stmt->bindValue(5, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $noticias = $stmt->fetchAll();
}
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-search" style="color: var(--accent-light);"></i> Buscar Notícias</h1>
        <p>Encontre notícias sobre qualquer tema do universo</p>
    </div>

    <form method="GET" action="">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="form-control"
                   placeholder="Buscar por título, conteúdo..."
                   value="<?= htmlspecialchars($busca) ?>" autofocus>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <?php if ($busca !== ''): ?>
        <p class="text-center text-muted mb-3">
            <?= $total ?> resultado(s) para "<strong><?= htmlspecialchars($busca) ?></strong>"
        </p>

        <?php if (empty($noticias)): ?>
            <div class="empty-state">
                <i class="fas fa-telescope"></i>
                <h3>Nenhum resultado encontrado</h3>
                <p>Tente buscar com outros termos.</p>
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
                            <?php if ($n['categoria_nome']): ?>
                                <div class="card-category">
                                    <i class="fas fa-<?= htmlspecialchars($n['categoria_icone'] ?? 'tag') ?>"></i>
                                    <?= htmlspecialchars($n['categoria_nome']) ?>
                                </div>
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
                                    <i class="far fa-clock"></i> <?= timeAgoBusca($n['created_at']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina > 1): ?>
                    <a href="?q=<?= urlencode($busca) ?>&pagina=<?= $pagina - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php if ($i == $pagina): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?q=<?= urlencode($busca) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?q=<?= urlencode($busca) ?>&pagina=<?= $pagina + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
