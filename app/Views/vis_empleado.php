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
</div>

<?php
// Bloque de reportes directamente en el dashboard para empleado (solo lectura)
require_once __DIR__ . '/../Models/mod_ventas.php';

$ventasModel = new VentasModel();
$ventasSemanales = $ventasModel->getVentasSemanales();
$topProductos = $ventasModel->getTopProductosMasVendidos(5);
$productoMasVendido = $topProductos[0] ?? null;
?>

<section class="reportes-dashboard" style="margin-top: 30px;">
    <div class="dashboard-grid">
        <div class="dashboard-card" style="grid-column: 1 / -1; padding: 12px 16px;">
            <p class="card-label">
        </div>

        <!-- Ventas semanales -->
        <div class="dashboard-card">
            <h3 class="card-title">Ventas de la última semana</h3>
            <p class="card-label">
                Resumen de las ventas diarias en USD de los últimos 7 días.
            </p>
            <div class="kpi-value">
                <?php
                $totalSemana = array_sum(array_column($ventasSemanales, 'total_usd'));
                ?>
                <span style="font-size: 28px; font-weight: 700; color: var(--azul-oscuro);">
                    <?= number_format($totalSemana, 2) ?> USD
                </span>
            </div>
        </div>

        <!-- Producto más vendido -->
        <div class="dashboard-card">
            <h3 class="card-title">Producto más vendido</h3>
            <?php if ($productoMasVendido): ?>
                <p class="card-label">
                    En los últimos 30 días.
                </p>
                <div style="margin-top: 10px;">
                    <div style="font-size: 18px; font-weight: 600; color: var(--azul-oscuro);">
                        <?= htmlspecialchars($productoMasVendido['nombre_producto']) ?>
                    </div>
                    <div style="margin-top: 6px; color: var(--texto-claro);">
                        Cantidad vendida: <strong><?= (int)$productoMasVendido['total_cantidad'] ?></strong>
                    </div>
                    <div style="margin-top: 4px; color: var(--texto-claro);">
                        Total en ventas: <strong><?= number_format($productoMasVendido['total_usd'], 2) ?> USD</strong>
                    </div>
                </div>
            <?php else: ?>
                <p class="card-label">Aún no hay datos suficientes para mostrar.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráficas -->
    <div style="margin-top: 30px;" class="dashboard-grid">
        <div class="dashboard-card" style="min-height: 320px;">
            <h3 class="card-title">Gráfica de ventas (últimos 7 días)</h3>
            <canvas id="chartVentasSemanales"></canvas>
        </div>

        <div class="dashboard-card" style="min-height: 320px;">
            <h3 class="card-title">Productos más vendidos (top 5)</h3>
            <canvas id="chartTopProductos"></canvas>
        </div>
    </div>
</section>

<script>
    window.__LILA_REPORTES__ = {
        ventasSemanales: <?= json_encode($ventasSemanales) ?>,
        topProductos: <?= json_encode($topProductos) ?>
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>js/reportes_dashboard.js?v=1.0"></script>

<?php include __DIR__ . '/shared/dashboard_end.php'; ?>