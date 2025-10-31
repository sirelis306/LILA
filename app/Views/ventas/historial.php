<?php
$titulo = 'Historial de Ventas'; 
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>js/facturaPDF.js?v=1.7"></script> 
<script src="<?= BASE_URL ?>js/ventas.js?v=1.6"></script>

<div class="content-body">
    <div class="container">
        <div class="inventario-table-container"> 
            <table id="tabla-historial-ventas" class="inventario-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Factura #</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Total USD</th>
                        <th>Total BS</th>
                        <th>Acciones</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="7">No hay ventas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?= htmlspecialchars($venta['id_venta']) ?></td>
                                <td><?= htmlspecialchars(date("d/m/Y", strtotime($venta['fecha']))) ?></td>
                                <td>
                                    <div class="producto-nombre-principal">
                                        <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente Ocasional') ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($venta['usuario']) ?></td>
                                <td>
                                    <span class="precio-inventario">
                                        $<?= htmlspecialchars(number_format($venta['total_usd'], 2)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="precio-inventario">
                                        <?= htmlspecialchars(number_format($venta['total_bs'], 2, ',', '.')) ?> Bs.
                                    </span>
                                </td>

                                <td>
                                    <button 
                                       onclick="descargarFacturaHistorial(<?= $venta['id_venta'] ?>)" 
                                       class="btn btn-sm btn-secondary"
                                       title="Descargar Factura">
                                        <i class="fi fi-rr-download"></i> Descargar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        
            <div id="paginador-ventas" class="paginador-container"></div>

        </div> </div> </div> <div id="stock-alert-modal" class="modal-personalizado" style="display: none;">
    <div class="modal-contenido">
        <span class="cerrar-modal" onclick="ocultarStockAlertModal()">&times;</span>
        <h3 id="modal-titulo-stock">Cargando</h3>
        <p id="modal-mensaje-stock">Por favor espere...</p>
        <button class="btn btn-primary" onclick="ocultarStockAlertModal()">Aceptar</button>
    </div>
</div>

<div id="pdf-preview-modal" class="modal-personalizado" style="display: none;">
    <div class="modal-contenido-pdf">
        <div class="pdf-header">
            <h3>Vista Previa de Factura</h3>
            <div>
                <button id="btn-descargar-pdf-modal" class="btn btn-primary">
                    <i class="fas fa-download"></i> Descargar
                </button>
                <span class="cerrar-modal" onclick="ocultarPdfModal()">&times;</span>
            </div>
        </div>
        <iframe id="pdf-iframe" src=""></iframe>
    </div>
</div>


<?php
include __DIR__ . '/../shared/dashboard_end.php';
?>