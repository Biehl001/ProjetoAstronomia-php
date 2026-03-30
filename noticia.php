<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    setFlash('danger', 'Notícia não encontrada.');
    header('Location: /ProjetoAstronomia-php/');
    exit;
}

// Get news
$stmt = $pdo->prepare("
    SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.icone as categoria_icone, c.id as cat_id
    FROM noticias n
    JOIN usuarios u ON n.autor_id = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    setFlash('danger', 'Notícia não encontrada.');
    header('Location: /ProjetoAstronomia-php/');
    exit;
}

// Increment views
$pdo->prepare("UPDATE noticias SET visualizacoes = visualizacoes + 1 WHERE id = ?")->execute([$id]);

// Related news
$stmtRelated = $pdo->prepare("
    SELECT n.*, u.nome as autor_nome, c.nome as categoria_nome, c.icone as categoria_icone
    FROM noticias n
    JOIN usuarios u ON n.autor_id = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.id != ? AND (n.categoria_id = ? OR n.categoria_id IS NULL)
    ORDER BY n.created_at DESC
    LIMIT 3
");
$stmtRelated->execute([$id, $noticia['categoria_id']]);
$relacionadas = $stmtRelated->fetchAll();

function timeAgoSingle($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'agora';
    if ($diff < 3600) return floor($diff / 60) . ' min atrás';
    if ($diff < 86400) return floor($diff / 3600) . 'h atrás';
    if ($diff < 604800) return floor($diff / 86400) . ' dias atrás';
    return date('d/m/Y', $time);
}

// Now safe to include header (all redirects done)
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="news-single">
        <div class="news-single-header">
            <?php if ($noticia['categoria_nome']): ?>
                <a href="/ProjetoAstronomia-php/categorias.php?id=<?= $noticia['cat_id'] ?>" class="news-single-category">
                    <i class="fas fa-<?= htmlspecialchars($noticia['categoria_icone'] ?? 'tag') ?>"></i>
                    <?= htmlspecialchars($noticia['categoria_nome']) ?>
                </a>
            <?php endif; ?>

            <h1><?= htmlspecialchars($noticia['titulo']) ?></h1>

            <div class="news-single-meta">
                <div class="meta-item">
                    <div class="author-avatar" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--blue));display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:white;">
                        <?= strtoupper(substr($noticia['autor_nome'], 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($noticia['autor_nome']) ?></span>
                </div>
                <div class="meta-item">
                    <i class="far fa-calendar-alt"></i>
                    <?= date('d/m/Y \à\s H:i', strtotime($noticia['created_at'])) ?>
                </div>
                <div class="meta-item">
                    <i class="far fa-eye"></i>
                    <?= $noticia['visualizacoes'] + 1 ?> visualizações
                </div>
            </div>
        </div>

        <?php if ($noticia['imagem']): ?>
        <div class="news-single-image">
            <img src="/ProjetoAstronomia-php/uploads/<?= htmlspecialchars($noticia['imagem']) ?>"
                 alt="<?= htmlspecialchars($noticia['titulo']) ?>">
        </div>
        <?php endif; ?>

        <div class="news-single-content">
            <?= nl2br(htmlspecialchars($noticia['noticia'])) ?>
        </div>

        <!-- Actions if author -->
        <?php if (isLoggedIn() && $_SESSION['usuario_id'] == $noticia['autor_id']): ?>
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border);">
            <div class="btn-group">
                <a href="/ProjetoAstronomia-php/pages/noticia-form.php?id=<?= $noticia['id'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-edit"></i> Editar Notícia
                </a>
                <button class="btn btn-danger btn-sm"
                        data-delete="/ProjetoAstronomia-php/pages/minhas-noticias.php?excluir=<?= $noticia['id'] ?>"
                        data-name="<?= htmlspecialchars($noticia['titulo']) ?>">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Related News -->
    <?php if (!empty($relacionadas)): ?>
    <div style="max-width: 800px; margin: 0 auto;">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-link"></i> Notícias Relacionadas</h2>
        </div>
        <div class="news-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
            <?php foreach ($relacionadas as $r): ?>
            <a href="/ProjetoAstronomia-php/noticia.php?id=<?= $r['id'] ?>" style="text-decoration: none; color: inherit;">
                <div class="news-card">
                    <div class="news-card-img" style="height: 150px;">
                        <?php if ($r['imagem']): ?>
                            <img src="/ProjetoAstronomia-php/uploads/<?= htmlspecialchars($r['imagem']) ?>"
                                 alt="<?= htmlspecialchars($r['titulo']) ?>">
                        <?php else: ?>
                            <div class="no-image"><i class="fas fa-star"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="news-card-body">
                        <h3 style="font-size: 1rem;"><?= htmlspecialchars($r['titulo']) ?></h3>
                        <div class="news-card-meta">
                            <div class="date"><i class="far fa-clock"></i> <?= timeAgoSingle($r['created_at']) ?></div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
