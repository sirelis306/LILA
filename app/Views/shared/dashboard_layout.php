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
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Sistema LILA</div>
                <div class="sidebar-subtitle">Panel de Control</div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?= BASE_URL ?>?r=<?= $esAdmin ? 'admin' : 'empleado' ?>" 
                   class="sidebar-link <?= $currentPage === 'admin' || $currentPage === 'empleado' ? 'active' : '' ?>">
                   Inicio
                </a>
                <a href="<?= BASE_URL ?>?r=form-tasa" 
                   class="sidebar-link <?= $currentPage === 'form-tasa' ? 'active' : '' ?>">
                   Tasa del Día
                </a>
                <a href="<?= BASE_URL ?>?r=ventas" 
                   class="sidebar-link <?= $currentPage === 'ventas' ? 'active' : '' ?>">
                   Ventas
                </a>
                <a href="<?= BASE_URL ?>?r=logout" class="sidebar-link">
                   Cerrar Sesión
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