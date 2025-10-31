<?php
$esAdmin = (currentUser()['rol'] ?? '') === 'administrador';
$titulo = $esAdmin ? 'Administrar Tasa del Día' : 'Registrar Tasa del Día';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<div class="content-body">
    <div class="dashboard-header">
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message">
            <span class="flash-icon">✅</span>
            <?= $_SESSION['flash'] ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (isset($tasaHoy) && $tasaHoy && !$esAdmin): ?>
        <!-- Estado de tasa registrada -->
        <div class="card text-center">
            <div class="card-header">
                <h2 class="card-title">Tasa del Día Registrada</h2>
            </div>
            <div class="card-value">$1 = <?= number_format($tasaHoy['tasa'] ?? 0, 2) ?> Bs</div>
            <p class="card-label">Registrada hoy a las <?= date('h:i A', strtotime($tasaHoy['fecha'])) ?></p>
            <div class="mt-20">
                <a href="<?= BASE_URL ?>?r=ventas" class="btn btn-primary">Ir a Ventas</a>
            </div>
        </div>
    <?php else: ?>
        <div class="form-simplified">
            <div class="form-card">
                <div class="form-header">
                    <h2>Registrar Tasa</h2>
                    <p>Ingresa el valor actual del dólar en Bolívares</p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>?r=guardar-tasa">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Tasa de Cambio (Bs por $1)</label>
                        <div class="input-currency">
                            <span class="currency-prefix">$1 =</span>
                            <input type="number" step="0.01" min="0.01" name="tasa" 
                                   value="<?= isset($tasaHoy['tasa']) ? number_format($tasaHoy['tasa'], 2) : '' ?>" 
                                   required class="form-input">
                            <span class="currency-suffix">Bs</span>
                        </div>
                    </div>
                    
                    <div class="form-actions-center">
                        <button type="submit" class="btn btn-primary">
                            <?= $esAdmin ? 'Actualizar Tasa' : 'Registrar Tasa' ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Botones debajo del formulario (FUERA del form-card) -->
            <div class="quick-actions-bottom">
                <div class="action-buttons-grid">
                    <?php if ($esAdmin): ?>
                        <a href="<?= BASE_URL ?>?r=admin" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Panel
                        </a>
                        <a href="<?= BASE_URL ?>?r=historial-tasas" class="btn btn-secondary">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>?r=ventas" class="btn btn-secondary">
                            <i class="fas fa-shopping-cart"></i> Ir a Ventas
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>