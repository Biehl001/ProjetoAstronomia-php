<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$usuario = getLoggedUser($pdo);
$erro = '';
$editando = false;
$noticia = [
    'id' => '',
    'titulo' => '',
    'resumo' => '',
    'noticia' => '',
    'categoria_id' => '',
    'imagem' => '',
    'destaque' => 0
];

// If editing
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ? AND autor_id = ?");
    $stmt->execute([$_GET['id'], $usuario['id']]);
    $found = $stmt->fetch();
    if ($found) {
        $noticia = $found;
        $editando = true;
    } else {
        setFlash('danger', 'Notícia não encontrada ou sem permissão.');
        header('Location: /ProjetoAstronomia-php/pages/minhas-noticias.php');
        exit;
    }
}

// Get categories
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $resumo = trim($_POST['resumo'] ?? '');
    $conteudo = trim($_POST['noticia'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $destaque = isset($_POST['destaque']) ? 1 : 0;

    if (empty($titulo) || empty($conteudo)) {
        $erro = 'Título e conteúdo são obrigatórios.';
    } else {
        // Handle image upload
        $imagem = $noticia['imagem'] ?? '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('news_') . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadDir . $filename);
                // Remove old image
                if ($imagem && file_exists($uploadDir . $imagem)) {
                    unlink($uploadDir . $imagem);
                }
                $imagem = $filename;
            } else {
                $erro = 'Formato de imagem não suportado. Use JPG, PNG, GIF ou WebP.';
            }
        }

        if (!$erro) {
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(
                iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $titulo)
            ));
            $slug = trim($slug, '-');

            if ($editando) {
                $stmt = $pdo->prepare("UPDATE noticias SET titulo = ?, slug = ?, resumo = ?, noticia = ?, categoria_id = ?, imagem = ?, destaque = ?, updated_at = NOW() WHERE id = ? AND autor_id = ?");
                $stmt->execute([$titulo, $slug, $resumo, $conteudo, $categoria_id ?: null, $imagem, $destaque, $noticia['id'], $usuario['id']]);
                setFlash('success', 'Notícia atualizada com sucesso!');
            } else {
                $stmt = $pdo->prepare("INSERT INTO noticias (titulo, slug, resumo, noticia, categoria_id, autor_id, imagem, destaque, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$titulo, $slug, $resumo, $conteudo, $categoria_id ?: null, $usuario['id'], $imagem, $destaque]);
                setFlash('success', 'Notícia publicada com sucesso!');
            }
            header('Location: /ProjetoAstronomia-php/pages/minhas-noticias.php');
            exit;
        }
    }

    // Keep form data on error
    $noticia['titulo'] = $titulo;
    $noticia['resumo'] = $resumo;
    $noticia['noticia'] = $conteudo;
    $noticia['categoria_id'] = $categoria_id;
    $noticia['destaque'] = $destaque;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="manage-header">
        <h1>
            <i class="fas fa-<?= $editando ? 'edit' : 'plus-circle' ?>"></i>
            <?= $editando ? 'Editar Notícia' : 'Nova Notícia' ?>
        </h1>
        <a href="/ProjetoAstronomia-php/pages/minhas-noticias.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div style="max-width: 800px;">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título da Notícia *</label>
                <input type="text" id="titulo" name="titulo" class="form-control"
                       placeholder="Ex: Telescópio James Webb revela novas galáxias"
                       value="<?= htmlspecialchars($noticia['titulo']) ?>" required>
            </div>

            <div class="form-group">
                <label for="resumo">Resumo (aparece na listagem)</label>
                <textarea id="resumo" name="resumo" class="form-control" rows="3"
                          placeholder="Um breve resumo da notícia..."><?= htmlspecialchars($noticia['resumo'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="noticia">Conteúdo da Notícia *</label>
                <textarea id="noticia" name="noticia" class="form-control" rows="12"
                          placeholder="Escreva sua notícia aqui..." required><?= htmlspecialchars($noticia['noticia']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    <option value="">Sem categoria</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($noticia['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Imagem de capa</label>
                <div class="image-upload">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Clique ou arraste uma imagem</p>
                    <span class="form-hint">JPG, PNG, GIF ou WebP</span>
                    <input type="file" name="imagem" accept="image/*">
                    <div class="image-preview" style="<?= empty($noticia['imagem']) ? 'display:none' : '' ?>">
                        <?php if (!empty($noticia['imagem'])): ?>
                            <img src="/ProjetoAstronomia-php/uploads/<?= htmlspecialchars($noticia['imagem']) ?>" alt="Preview">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="destaque" value="1" <?= !empty($noticia['destaque']) ? 'checked' : '' ?>
                           style="width: 18px; height: 18px; accent-color: var(--accent);">
                    <span><i class="fas fa-star" style="color: var(--amber);"></i> Marcar como destaque</span>
                </label>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-<?= $editando ? 'save' : 'paper-plane' ?>"></i>
                    <?= $editando ? 'Salvar Alterações' : 'Publicar Notícia' ?>
                </button>
                <a href="/ProjetoAstronomia-php/pages/minhas-noticias.php" class="btn btn-secondary btn-lg">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
