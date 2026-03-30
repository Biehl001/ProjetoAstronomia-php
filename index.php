<?php
include __DIR__ . '/includes/header.php';

// Notícia em destaque
$stmtDestaque = $pdo->query("
    SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.icone as categoria_icone
    FROM noticias n
    JOIN usuarios u ON n.autor_id = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.destaque = 1
    ORDER BY n.created_at DESC
    LIMIT 1
");
$destaque = $stmtDestaque->fetch();

// Últimas notícias
$offset = $destaque ? 0 : 0;
$stmtRecentes = $pdo->prepare("
    SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.icone as categoria_icone
    FROM noticias n
    JOIN usuarios u ON n.autor_id = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    " . ($destaque ? "WHERE n.id != ?" : "") . "
    ORDER BY n.created_at DESC
    LIMIT 6
");
if ($destaque) {
    $stmtRecentes->execute([$destaque['id']]);
} else {
    $stmtRecentes->execute();
}
$recentes = $stmtRecentes->fetchAll();

// Mais lidas (by views) for sidebar
$stmtPopulares = $pdo->query("
    SELECT n.*, u.nome as autor_nome
    FROM noticias n
    JOIN usuarios u ON n.autor_id = u.id
    ORDER BY n.visualizacoes DESC, n.created_at DESC
    LIMIT 5
");
$populares = $stmtPopulares->fetchAll();

// Categories with count
$stmtCats = $pdo->query("
    SELECT c.*, COUNT(n.id) as total
    FROM categorias c
    LEFT JOIN noticias n ON n.categoria_id = c.id
    GROUP BY c.id
    ORDER BY total DESC
");
$cats = $stmtCats->fetchAll();

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'agora';
    if ($diff < 3600) return floor($diff / 60) . ' min atrás';
    if ($diff < 86400) return floor($diff / 3600) . 'h atrás';
    if ($diff < 604800) return floor($diff / 86400) . ' dias atrás';
    return date('d/m/Y', $time);
}
?>

<div class="container">
    <!-- Hero -->
    <div class="hero">
        <div class="hero-badge">
            <i class="fas fa-satellite"></i> Portal de Astronomia
        </div>
        <h1>Explore o <span class="gradient-text">Universo</span><br>Uma Notícia de Cada Vez</h1>
        <p>Seu portal com as últimas descobertas, missões espaciais e mistérios do cosmos.</p>
    </div>

    <?php if ($destaque): ?>
    <!-- Featured News -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-fire"></i> Em Destaque</h2>
    </div>

    <a href="/ProjetoAstronomia-php/noticia.php?id=<?= $destaque['id'] ?>" style="text-decoration: none; color: inherit;">
        <div class="news-card featured-card">
            <div class="news-card-img">
                <?php if ($destaque['imagem']): ?>
                    <img src="/ProjetoAstronomia-php/uploads/<?= htmlspecialchars($destaque['imagem']) ?>"
                         alt="<?= htmlspecialchars($destaque['titulo']) ?>">
                <?php else: ?>
                    <div class="no-image"><i class="fas fa-galaxy"></i></div>
                <?php endif; ?>
            </div>
            <div class="news-card-body">
                <div class="featured-badge">
                    <i class="fas fa-bolt"></i> Destaque
                </div>
                <?php if ($destaque['categoria_nome']): ?>
                    <div class="card-category" style="position: static; display: inline-flex; margin-bottom: 12px;">
                        <i class="fas fa-<?= htmlspecialchars($destaque['categoria_icone'] ?? 'tag') ?>"></i>
                        <?= htmlspecialchars($destaque['categoria_nome']) ?>
                    </div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($destaque['titulo']) ?></h3>
                <p class="excerpt"><?= htmlspecialchars($destaque['resumo'] ?: mb_substr(strip_tags($destaque['noticia']), 0, 200) . '...') ?></p>
                <div class="news-card-meta">
                    <div class="author">
                        <div class="author-avatar"><?= strtoupper(substr($destaque['autor_nome'], 0, 1)) ?></div>
                        <?= htmlspecialchars($destaque['autor_nome']) ?>
                    </div>
                    <div class="date">
                        <i class="far fa-clock"></i> <?= timeAgo($destaque['created_at']) ?>
                    </div>
                </div>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <!-- Latest News + Sidebar -->
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-clock"></i> Últimas Notícias</h2>
        <a href="/ProjetoAstronomia-php/busca.php" class="section-link">
            Ver todas <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($recentes) && !$destaque): ?>
        <div class="empty-state">
            <i class="fas fa-telescope"></i>
            <h3>Nenhuma notícia publicada ainda</h3>
            <p>O universo está esperando suas descobertas!</p>
            <?php if (isLoggedIn()): ?>
                <a href="/ProjetoAstronomia-php/pages/noticia-form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Publicar Primeira Notícia
                </a>
            <?php else: ?>
                <a href="/ProjetoAstronomia-php/login.php" class="btn btn-primary">
                    <i class="fas fa-rocket"></i> Entrar para Publicar
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="layout-with-sidebar">
            <div>
                <div class="news-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php foreach ($recentes as $n): ?>
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
                                        <i class="far fa-clock"></i> <?= timeAgo($n['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Most Popular -->
                <?php if (!empty($populares)): ?>
                <div class="sidebar-widget">
                    <h3><i class="fas fa-chart-line"></i> Mais Lidas</h3>
                    <?php foreach ($populares as $i => $p): ?>
                    <div class="sidebar-news-item">
                        <span class="sni-number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                        <div>
                            <h4><a href="/ProjetoAstronomia-php/noticia.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></a></h4>
                            <span class="sni-meta"><?= timeAgo($p['created_at']) ?> &middot; <?= $p['visualizacoes'] ?> views</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Categories -->
                <?php if (!empty($cats)): ?>
                <div class="sidebar-widget">
                    <h3><i class="fas fa-layer-group"></i> Categorias</h3>
                    <div class="category-pills">
                        <?php foreach ($cats as $c): ?>
                        <a href="/ProjetoAstronomia-php/categorias.php?id=<?= $c['id'] ?>" class="category-pill">
                            <i class="fas fa-<?= htmlspecialchars($c['icone']) ?>"></i>
                            <?= htmlspecialchars($c['nome']) ?>
                            <span class="count"><?= $c['total'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- CTA Widget -->
                <?php if (!isLoggedIn()): ?>
                <div class="sidebar-widget" style="background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(59,130,246,0.2)); border-color: rgba(124,58,237,0.3);">
                    <h3><i class="fas fa-rocket"></i> Junte-se a nós!</h3>
                    <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 16px;">
                        Crie sua conta e comece a publicar notícias sobre o universo.
                    </p>
                    <a href="/ProjetoAstronomia-php/cadastro.php" class="btn btn-primary btn-block btn-sm">
                        <i class="fas fa-user-astronaut"></i> Criar Conta
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
