<?php
$titulo = 'Historial de Tasas';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<div class="content-body">
    <div class="container">
        <div class="inventario-table-container">
            <table id="tabla-historial-tasas" class="inventario-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Fecha de Registro</th>
                        <th>Valor de la Tasa (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasas)): ?>
                        <tr>
                            <td colspan="2">No hay tasas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasas as $tasa): ?>
                            <tr>
                                <td><?= htmlspecialchars(date("d/m/Y", strtotime($tasa['fecha']))) ?></td>
                                
                                <td>
                                    <span class="precio-inventario">
                                        <?= htmlspecialchars(number_format($tasa['tasa'], 2, ',', '.')) ?> Bs.
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div id="paginador-tasas" class="paginador-container"></div>

        </div> <hr style="margin-top: 15px; margin-bottom: 25px;">

        <div class="quick-actions-bottom">
             <a href="<?= BASE_URL ?>?r=form-tasa" class="btn btn-secondary">
                 <i class="fas fa-arrow-left"></i> Volver a Tasa del Día
             </a>
        </div>

    </div>
</div>

<?php
include __DIR__ . '/../shared/dashboard_end.php';
?>