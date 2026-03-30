<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
$usuario = getLoggedUser($pdo);

// Buscar categorias para o menu
$stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categoriasMenu = $stmtCat->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cosmos News - Portal de Astronomia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/ProjetoAstronomia-php/assets/css/style.css">
</head>
<body>
    <!-- Stars background -->
    <div class="stars-container">
        <div id="stars"></div>
        <div id="stars2"></div>
        <div id="stars3"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="/ProjetoAstronomia-php/" class="navbar-brand">
                <i class="fas fa-meteor"></i>
                <span>Cosmos<strong>News</strong></span>
            </a>

            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-menu" id="navbarMenu">
                <a href="/ProjetoAstronomia-php/" class="nav-link">
                    <i class="fas fa-home"></i> Início
                </a>

                <div class="nav-dropdown">
                    <button class="nav-link dropdown-toggle">
                        <i class="fas fa-layer-group"></i> Categorias <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/ProjetoAstronomia-php/categorias.php" class="dropdown-item">
                            <i class="fas fa-th-large"></i> Todas as Categorias
                        </a>
                        <div class="dropdown-divider"></div>
                        <?php foreach ($categoriasMenu as $cat): ?>
                        <a href="/ProjetoAstronomia-php/categorias.php?id=<?= $cat['id'] ?>" class="dropdown-item">
                            <i class="fas fa-<?= htmlspecialchars($cat['icone']) ?>"></i>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="/ProjetoAstronomia-php/busca.php" class="nav-link">
                    <i class="fas fa-search"></i> Buscar
                </a>

                <?php if (isLoggedIn()): ?>
                    <div class="nav-dropdown">
                        <button class="nav-link dropdown-toggle user-toggle">
                            <div class="user-avatar-small">
                                <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($usuario['nome']) ?>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a href="/ProjetoAstronomia-php/pages/minhas-noticias.php" class="dropdown-item">
                                <i class="fas fa-newspaper"></i> Minhas Notícias
                            </a>
                            <a href="/ProjetoAstronomia-php/pages/noticia-form.php" class="dropdown-item">
                                <i class="fas fa-plus-circle"></i> Nova Notícia
                            </a>
                            <a href="/ProjetoAstronomia-php/pages/categorias-gerenciar.php" class="dropdown-item">
                                <i class="fas fa-tags"></i> Gerenciar Categorias
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="/ProjetoAstronomia-php/pages/perfil.php" class="dropdown-item">
                                <i class="fas fa-user-cog"></i> Meu Perfil
                            </a>
                            <a href="/ProjetoAstronomia-php/logout.php" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/ProjetoAstronomia-php/login.php" class="nav-link btn-login">
                        <i class="fas fa-rocket"></i> Entrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    <?php $flash = getFlash(); if ($flash): ?>
    <div class="container">
        <div class="alert alert-<?= $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
