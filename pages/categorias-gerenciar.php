<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$usuario = getLoggedUser($pdo);
$erro = '';
$editando = false;
$categoria_edit = ['id' => '', 'nome' => '', 'icone' => 'star', 'descricao' => ''];

// Handle delete
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    // Set news in this category to null
    $stmt = $pdo->prepare("UPDATE noticias SET categoria_id = NULL WHERE categoria_id = ?");
    $stmt->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Categoria excluída com sucesso!');
    header('Location: /ProjetoAstronomia-php/pages/categorias-gerenciar.php');
    exit;
}

// Handle edit load
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $found = $stmt->fetch();
    if ($found) {
        $categoria_edit = $found;
        $editando = true;
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $icone = trim($_POST['icone'] ?? 'star');
    $descricao = trim($_POST['descricao'] ?? '');
    $id = intval($_POST['id'] ?? 0);

    if (empty($nome)) {
        $erro = 'O nome da categoria é obrigatório.';
    } else {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(
            iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome)
        ));
        $slug = trim($slug, '-');

        if ($id) {
            $stmt = $pdo->prepare("UPDATE categorias SET nome = ?, slug = ?, icone = ?, descricao = ? WHERE id = ?");
            $stmt->execute([$nome, $slug, $icone, $descricao, $id]);
            setFlash('success', 'Categoria atualizada!');
        } else {
            $stmt = $pdo->prepare("INSERT INTO categorias (nome, slug, icone, descricao, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$nome, $slug, $icone, $descricao]);
            setFlash('success', 'Categoria criada com sucesso!');
        }
        header('Location: /ProjetoAstronomia-php/pages/categorias-gerenciar.php');
        exit;
    }

    $categoria_edit = ['id' => $id, 'nome' => $nome, 'icone' => $icone, 'descricao' => $descricao];
    $editando = $id > 0;
}

// Get all categories with news count
$categorias = $pdo->query("
    SELECT c.*, COUNT(n.id) as total_noticias
    FROM categorias c
    LEFT JOIN noticias n ON n.categoria_id = c.id
    GROUP BY c.id
    ORDER BY c.nome ASC
")->fetchAll();

$icones_disponiveis = [
    'star' => 'Estrela', 'sun' => 'Sol', 'moon' => 'Lua',
    'globe' => 'Planeta', 'meteor' => 'Meteoro', 'satellite' => 'Satélite',
    'satellite-dish' => 'Antena', 'rocket' => 'Foguete', 'shuttle-space' => 'Nave',
    'user-astronaut' => 'Astronauta', 'binoculars' => 'Telescópio', 'bolt' => 'Energia',
    'explosion' => 'Explosão', 'atom' => 'Átomo', 'microscope' => 'Microscópio',
    'flask' => 'Ciência', 'earth-americas' => 'Terra', 'cloud-moon' => 'Céu Noturno'
];

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="manage-header">
        <h1><i class="fas fa-tags"></i> Gerenciar Categorias</h1>
    </div>

    <?php if ($erro): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="layout-with-sidebar" style="margin-top: 0;">
        <!-- Form -->
        <div>
            <div class="sidebar-widget">
                <h3>
                    <i class="fas fa-<?= $editando ? 'edit' : 'plus-circle' ?>"></i>
                    <?= $editando ? 'Editar Categoria' : 'Nova Categoria' ?>
                </h3>

                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?= $categoria_edit['id'] ?>">

                    <div class="form-group">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" class="form-control"
                               placeholder="Ex: Galáxias" value="<?= htmlspecialchars($categoria_edit['nome']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="icone">Ícone</label>
                        <select id="icone" name="icone" class="form-control">
                            <?php foreach ($icones_disponiveis as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $categoria_edit['icone'] === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-hint">
                            Preview: <i class="fas fa-<?= htmlspecialchars($categoria_edit['icone']) ?>" style="color: var(--accent-light);"></i>
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" rows="3"
                                  placeholder="Breve descrição da categoria..."><?= htmlspecialchars($categoria_edit['descricao'] ?? '') ?></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?= $editando ? 'save' : 'plus' ?>"></i>
                            <?= $editando ? 'Salvar' : 'Criar Categoria' ?>
                        </button>
                        <?php if ($editando): ?>
                            <a href="/ProjetoAstronomia-php/pages/categorias-gerenciar.php" class="btn btn-secondary">
                                Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- List -->
        <div>
            <?php if (empty($categorias)): ?>
                <div class="empty-state">
                    <i class="fas fa-layer-group"></i>
                    <h3>Nenhuma categoria</h3>
                    <p>Crie a primeira categoria para organizar as notícias.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Notícias</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td>
                                    <span style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fas fa-<?= htmlspecialchars($cat['icone']) ?>" style="color: var(--accent-light);"></i>
                                        <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                                    </span>
                                </td>
                                <td><?= $cat['total_noticias'] ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?editar=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm"
                                                data-delete="/ProjetoAstronomia-php/pages/categorias-gerenciar.php?excluir=<?= $cat['id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['nome']) ?>">
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
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
