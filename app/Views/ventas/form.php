<?php
$esAdmin = (currentUser()['rol'] ?? '') === 'administrador';
$titulo = $esAdmin ? 'Historial de Ventas' : 'Nueva Venta';
include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<div class="content-body">
    <div class="container">
        <h2><?= $titulo ?></h2>

        <?php if (!$esAdmin): ?>
            <!-- FORMULARIO DE VENTA PARA EMPLEADOS -->
            <div class="venta-container">
                <div class="busqueda-producto">
                    <h3>🛒 Buscar Producto</h3>
                    <div class="search-box">
                        <input type="text" id="buscar-producto" placeholder="Código o nombre del producto..." 
                               class="form-input">
                        <button onclick="buscarProducto()" class="btn btn-primary">Buscar</button>
                    </div>
                    <div id="resultados-busqueda" class="resultados-busqueda"></div>
                </div>

                <div class="carrito-venta">
                    <h3>Carrito de Compra</h3>
                    <div id="carrito-vacio" class="alert alert-info">
                        No hay productos en el carrito
                    </div>
                    <table id="tabla-carrito" class="tabla-carrito" style="display: none;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="items-carrito">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"><strong>Total BS:</strong></td>
                                <td id="total-bs">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Total USD:</strong></td>
                                <td id="total-usd">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="info-venta">
                    <h3>💳 Información de Pago</h3>
                    <form id="form-venta" method="POST" action="<?= BASE_URL ?>?r=procesar-venta">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                        
                        <div class="form-group">
                            <label>Método de Pago:</label>
                            <select name="metodo_pago" id="metodo-pago" class="form-input" required>
                                <option value="">Seleccionar...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="pago_movil">Pago Móvil</option>
                                <option value="tarjeta_debito">Tarjeta Débito</option>
                                <option value="tarjeta_credito">Tarjeta Crédito</option>
                            </select>
                        </div>

                        <div class="form-group" id="grupo-referencia" style="display: none;">
                            <label>Referencia de Pago:</label>
                            <input type="text" name="referencia_pago" class="form-input" 
                                   placeholder="Número de referencia...">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success" disabled id="btn-procesar">
                                💰 Procesar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <!-- HISTORIAL DE VENTAS PARA ADMIN -->
            <div class="alert alert-info">
                <p>Funcionalidad de historial de ventas en desarrollo...</p>
                <a href="<?= BASE_URL ?>?r=form-tasa" class="btn btn-primary">Gestionar Tasas</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Mostrar/ocultar campo de referencia según método de pago
document.getElementById('metodo-pago').addEventListener('change', function() {
    const grupoReferencia = document.getElementById('grupo-referencia');
    const metodosConReferencia = ['transferencia', 'pago_movil', 'tarjeta_debito', 'tarjeta_credito'];
    
    grupoReferencia.style.display = metodosConReferencia.includes(this.value) ? 'block' : 'none';
});

function buscarProducto() {
    // Implementaremos esto después
    alert('Funcionalidad de búsqueda en desarrollo...');
}
</script>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>