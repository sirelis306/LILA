<?php
$titulo = "Panel de Empleado";
include __DIR__ . '/shared/dashboard_layout.php';
?>

<div class="dashboard-grid">
    <!-- Tarjeta 1: Registrar Tasa -->
    <div class="dashboard-card">
        <h3 class="card-title">Tasa del Día</h3>
        <p class="card-label">Registra la tasa antes de gestionar ventas.</p>
        <a href="<?= BASE_URL ?>?r=form-tasa" class="btn btn-primary">Registrar Tasa</a>
    </div>

    <!-- Tarjeta 2: Realizar Ventas -->
    <div class="dashboard-card">
        <h3 class="card-title">Realizar Ventas</h3>
        <p class="card-label">Accede a ventas para registrar nuevas transacciones.</p>
        <a href="<?= BASE_URL ?>?r=ventas" class="btn btn-success">Nueva Venta</a>
    </div>

    <!-- Tarjeta 3: Mi Perfil -->
    <div class="dashboard-card">
        <h3 class="card-title">Mi Perfil</h3>
        <p class="card-label">Gestiona tu información personal y cambia tu contraseña.</p>
        <button class="btn btn-secondary" disabled>Próximamente</button>
    </div>
</div>

<?php include __DIR__ . '/shared/dashboard_end.php'; ?>