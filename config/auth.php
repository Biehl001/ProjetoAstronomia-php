<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function requireLogin($pdo = null) {
    if (!isLoggedIn()) {
        header('Location: /ProjetoAstronomia-php/login.php');
        exit;
    }
    // If PDO provided, validate user still exists in DB
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        if (!$stmt->fetch()) {
            session_destroy();
            header('Location: /ProjetoAstronomia-php/login.php');
            exit;
        }
    }
}

function getLoggedUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $user = $stmt->fetch();
    if (!$user) {
        // Session has invalid user, clean up
        session_destroy();
        header('Location: /ProjetoAstronomia-php/login.php');
        exit;
    }
    return $user;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
