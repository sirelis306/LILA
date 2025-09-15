<?php
$esAdmin = (currentUser()['rol'] ?? '') === 'administrador';
$titulo = $esAdmin ? 'Administrar Tasa del Día' : 'Registrar Tasa del Día';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<div class="content-body">
    <div class="dashboard-header">
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message success">
            <span class="flash-icon">✅</span>
            <?= $_SESSION['flash'] ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (isset($tasaHoy) && $tasaHoy && !$esAdmin): ?>
        <div class="status-card success">
            <div class="status-content">
                <h3>Tasa del Día Registrada</h3>
                <p class="tasa-value">$1 = <?= number_format($tasaHoy['tasa'], 2) ?> Bs</p>
                <p class="status-time">Registrada hoy a las <?= date('h:i A', strtotime($tasaHoy['fecha_creacion'])) ?></p>
            </div>
            <div class="status-actions">
                <a href="<?= BASE_URL ?>?r=ventas" class="btn btn-primary">Ir a Ventas</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Formulario Principal Simplificado -->
        <div class="form-simplified">
            <div class="form-card">
                <div class="form-header">
                    <h2>Registrar Tasa</h2>
                    <p>Ingresa el valor actual del dólar en Bolívares</p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>?r=guardar-tasa" class="tasa-form">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label for="tasa" class="form-label">
                            Tasa de Cambio (Bs por $1)
                        </label>
                        <div class="input-currency">
                            <span class="currency-prefix">$1 =</span>
                            <input type="number" step="0.01" min="0.01" name="tasa" id="tasa" 
                                   value="<?= isset($tasaHoy['tasa']) ? number_format($tasaHoy['tasa'], 2) : '' ?>" 
                                   required class="form-input"
                                   placeholder="36.50">
                            <span class="currency-suffix">Bs</span>
                        </div>
                        <p class="input-help">Ej: 36.50 (treinta y seis con cincuenta céntimos)</p>
                    </div>
                    
                    <div class="form-actions-center">
                        <button type="submit" class="btn btn-primary btn-large">
                            <?= $esAdmin ? 'Actualizar Tasa' : 'Registrar Tasa' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Acciones Rápidas Debajo del Formulario -->
        <div class="quick-actions-bottom">
            <div class="action-buttons-grid">
                <?php if ($esAdmin): ?>
                    <a href="<?= BASE_URL ?>?r=admin" class="btn btn-secondary">
                        ← Volver al Panel
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?r=ventas" class="btn btn-secondary">
                        ↗ Ir a Ventas
                    </a>
                <?php endif; ?>
                
                <?php if ($esAdmin): ?>
                    <a href="#" class="btn btn-outline" disabled>
                        📋 Ver Historial
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Solo mostrar estadísticas si es admin y si hay datos -->
    <?php if ($esAdmin && isset($stats) && !empty($stats)): ?>
    <div class="stats-section">
        <h3>📊 Estadísticas de Tasas</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['promedio'], 2) ?></div>
                <div class="stat-label">Promedio 30 días</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['maxima'], 2) ?></div>
                <div class="stat-label">Máxima</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['minima'], 2) ?></div>
                <div class="stat-label">Mínima</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>