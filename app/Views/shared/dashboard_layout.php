<?php
$currentUser = currentUser() ?? null;
$esAdmin = ($currentUser['rol'] ?? '') === 'administrador';
$currentPage = $_GET['r'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema LILA - <?= $titulo ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css?v=3.0">
    <!-- UIcons Flaticon CDN -->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.1.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="<?= BASE_URL ?>img/logo_lila.png" alt="Logo LILA" class="sidebar-logo">
                <div class="logo-text">
                    <div class="sidebar-title">Sistema LILA</div>
                    <div class="sidebar-subtitle">Panel de Control</div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= BASE_URL ?>?r=<?= $esAdmin ? 'admin' : 'empleado' ?>" 
                class="sidebar-link <?= $currentPage === 'admin' || $currentPage === 'empleado' ? 'active' : '' ?>">
                <i class="fi fi-rr-home"></i> <span>Inicio</span>
                </a>
                <a href="<?= BASE_URL ?>?r=form-tasa" 
                class="sidebar-link <?= $currentPage === 'form-tasa' ? 'active' : '' ?>">
                <i class="fi fi-rr-money-bill-wave"></i> <span>Tasa del Día</span>
                </a>
                <a href="<?= BASE_URL ?>?r=ventas" 
                class="sidebar-link <?= $currentPage === 'ventas' ? 'active' : '' ?>">
                <i class="fi fi-rr-shopping-cart"></i> <span>Ventas</span>
                </a>
                <?php if ($esAdmin): ?>
                <a href="<?= BASE_URL ?>?r=inventario" 
                class="sidebar-link <?= $currentPage === 'inventario' ? 'active' : '' ?>">
                <i class="fi fi-rr-box"></i> <span>Inventario</span>
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>?r=logout" class="sidebar-link">
                <i class="fi fi-rr-exit"></i> <span>Cerrar Sesión</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1 class="content-title"><?= $titulo ?? 'Dashboard' ?></h1>
                <p class="content-subtitle">Bienvenido, <?= htmlspecialchars($currentUser['user']) ?></p>
            </header>
            
            <div class="content-body">