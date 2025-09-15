<?php
$currentUser = currentUser() ?? null;
$esAdmin = ($currentUser['rol'] ?? '') === 'administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema LILA - <?= $titulo ?? 'Inicio' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1 class="header-title"> Sistema LILA - Cristalería</h1>
            <?php if (isLoggedIn() && $currentUser): ?>
                <nav class="header-nav">
                    <span class="header-welcome">👋 Hola, <?= htmlspecialchars($currentUser['user']) ?></span>
                    <a href="<?= BASE_URL ?>?r=<?= $esAdmin ? 'admin' : 'empleado' ?>" class="header-link"> Inicio</a>
                    <a href="<?= BASE_URL ?>?r=form-tasa" class="header-link"> Tasa</a>
                    <a href="<?= BASE_URL ?>?r=ventas" class="header-link"> Ventas</a>
                    <a href="<?= BASE_URL ?>?r=logout" class="header-link"> Salir</a>
                </nav>
            <?php endif; ?>
        </header>
        <main class="main-content">