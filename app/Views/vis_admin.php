<?php
$titulo = "Panel de Administrador";
include __DIR__ . '/shared/dashboard_layout.php';
?>

<div class="dashboard-grid">
    <!-- Tarjeta 1: Gestión de Tasas -->
    <div class="dashboard-card">
        <h3 class="card-title">Gestión de Tasas</h3>
        <p class="card-label">Administra la tasa de cambio del día.</p>
        <a href="<?= BASE_URL ?>?r=form-tasa" class="btn btn-primary">Gestionar Tasas</a>
    </div>

    <!-- Tarjeta 2: Módulo de Ventas -->
    <div class="dashboard-card">
        <h3 class="card-title">Módulo de Ventas</h3>
        <p class="card-label">Revisa el historial de transacciones.</p>
        <a href="<?= BASE_URL ?>?r=ventas" class="btn btn-success">Ir a Ventas</a>
    </div>

    <!-- Tarjeta 3: Gestión de Inventario -->
    <div class="dashboard-card">
        <h3 class="card-title">Gestión de Inventario</h3>
        <p class="card-label">Administra productos y stock.</p>
        <a href="<?= BASE_URL ?>?r=inventario" class="btn btn-success">Ir a Inventario</a>
    </div>

    <!-- Tarjeta 4: Reportes -->
    <div class="dashboard-card">
        <h3 class="card-title">Reportes y Estadísticas</h3>
        <p class="card-label">Próximamente: Genera reportes de ventas y análisis de datos.</p>
        <button class="btn btn-secondary" disabled>Próximamente</button>
    </div>
</div>

<?php include __DIR__ . '/shared/dashboard_end.php'; ?>