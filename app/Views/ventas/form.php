<?php
require_once __DIR__ . "/../../Helpers/auth.php";
$esAdmin = (currentUser()['rol'] ?? '') === 'administrador';

if ($esAdmin) {
    header('Location: ' . BASE_URL . '?r=historial-ventas');
    exit;
}
$titulo = 'Nueva Venta';

// Obtener tasa del día
$model = new VentasModel();
$tasaHoy = $model->getTasaHoy();

include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>js/facturaPDF.js?v=1.5"></script>
<script src="<?= BASE_URL ?>js/ventas.js?v=1.4"></script>

<div class="content-body">
    <div class="container">

        <?php if (!$esAdmin): ?>
    <!-- CONTENEDOR PRINCIPAL DE 2/3 COLUMNAS -->
    <div class="ventas-container" id="paso-carrito">
        
        <!-- COLUMNA IZQUIERDA - BÚSQUEDA -->
        <div class="columna-ventas">
            <h3>Buscar Producto</h3>
            <div class="buscador-ventas">
                <input type="text" 
                       id="buscar-producto" 
                       class="buscador-input" 
                       placeholder="Buscar producto"
                       onkeypress="if(event.key === 'Enter') buscarProducto()">
                <button onclick="buscarProducto()" class="btn btn-primary" style="width: 100%">
                    Buscar Producto
                </button>
            </div>    
             
            <!-- Resultados de búsqueda (existente) -->
            <div id="resultados-busqueda" class="lista-productos">
                <!-- Los resultados aparecerán aquí -->
            </div>
        </div>

        <!-- COLUMNA CENTRO - CARRITO -->
        <div class="columna-ventas">
            <div class="carrito-header">
                <h4>Carrito de Compra</h4>
                <button onclick="vaciarCarrito()" class="btn btn-secondary btn-sm">
                    Vaciar Todo
                </button>
            </div>
            
            <div id="carrito-vacio" class="carrito-vacio">
                <p>No hay productos en el carrito</p>
                <small>Busca y agrega productos para comenzar</small>
            </div>
            
            <div id="contenido-carrito" style="display: none;">
                <table class="tabla-carrito">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio bs</th>
                            <th>Precio usd</th>
                            <th>Cantidad</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="items-carrito">
                        <!-- Items del carrito aparecerán aquí -->
                    </tbody>
                </table>
                
                <div class="totales-venta">
                    <div class="total-fila">
                        <span>Subtotal USD:</span>
                        <span id="subtotal-usd">$0.00</span>
                    </div>
                    <div class="total-fila">
                        <span>Subtotal BS:</span>
                        <span id="subtotal-bs">0.00 Bs</span>
                    </div>
                    <div class="total-fila">
                        <span>IVA (16%):</span>
                        <span id="iva-usd">$0.00</span>
                    </div>
                    <div class="total-fila total-final">
                        <span>Total USD:</span>
                        <span id="total-usd">$0.00</span>
                    </div>
                    <div class="total-fila total-final">
                        <span>Total BS:</span>
                        <span id="total-bs">0.00 Bs</span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button onclick="mostrarPasoPago()" 
                            class="btn-procesar-venta" 
                            id="btn-procesar" 
                            disabled
                            style="flex: 2;">
                        PROCESAR VENTA
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PANTALLA DE FINALIZAR PAGO (oculta inicialmente) -->
    <div class="ventas-container" id="paso-pago" style="display: none;">
        
        <!-- COLUMNA IZQUIERDA - RESUMEN DEL PEDIDO -->
        <div class="columna-ventas">
            <h3>Resumen del Pedido</h3>
            <div id="resumen-pedido">
                <!-- Los productos del carrito aparecerán aquí -->
            </div>
            

           <div class="totales-venta">
                <div class="total-fila">
                    <span>Subtotal USD:</span>
                    <span id="resumen-subtotal-usd">$0.00</span>
                </div>
                <div class="total-fila">
                    <span>Subtotal BS:</span>
                    <span id="resumen-subtotal-bs">0.00 Bs</span>
                </div>
                <div class="total-fila">
                    <span>IVA (16%):</span>
                    <span id="resumen-iva-usd">$0.00</span>
                </div>
                <div class="total-fila total-final">
                    <span>Total USD:</span>
                    <span id="resumen-total-usd">$0.00</span>
                </div>
                <div class="total-fila total-final">
                    <span>Total BS:</span>
                    <span id="resumen-total-bs">0.00 Bs</span>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA - MÉTODO DE PAGO Y CLIENTE -->
        <div class="columna-ventas">
            <h3>Método de Pago</h3>
            
            <form id="form-venta" method="POST" action="<?= BASE_URL ?>?r=procesar-venta">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="tasa" id="tasa-actual" value="<?= $tasaHoy['tasa'] ?? '36.50' ?>">
                
                <div class="metodo-pago-opciones">
                    <label class="metodo-opcion">
                        <input type="radio" name="metodo_pago" value="efectivo" required> 
                        Efectivo
                    </label>
                    
                    <label class="metodo-opcion">
                        <input type="radio" name="metodo_pago" value="transferencia"> 
                        Transferencia
                    </label>
                    
                    <label class="metodo-opcion">
                        <input type="radio" name="metodo_pago" value="pago_movil"> 
                        Pago Móvil
                    </label>

                    <label class="metodo-opcion">
                        <input type="radio" name="metodo_pago" value="tarjeta_debito"> 
                        Tarjeta Débito
                    </label>

                    <label class="metodo-opcion">
                        <input type="radio" name="metodo_pago" value="tarjeta_credito"> 
                        Tarjeta Crédito
                    </label>
                </div>

                <div id="grupo-referencia" class="referencia-pago-group" style="display: none;">
                    <label class="form-label">Referencia de Pago:</label>
                    <input type="text" 
                           name="referencia_pago" 
                           class="form-input" 
                           placeholder="Número de referencia">
                </div>

                <div class="form-group">
                    <label class="form-label">Cliente:</label>
                    <select name="id_cliente" class="form-input">
                        <option value="">Cliente ocasional</option>
                        <!-- Opciones de clientes -->
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" 
                            onclick="volverAlCarrito()" 
                            class="btn-procesar-venta" 
                            style="flex: 1;">
                        Volver
                    </button>
                    <button type="button" 
                            class="btn-procesar-venta" 
                            onclick="procesarVentaCompleta()">
                        PROCESAR VENTA
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php endif; ?>
<!-- Modal de Notificación Personalizada -->
<div id="stock-alert-modal" class="modal-personalizado" style="display: none;">
    <div class="modal-contenido">
        <span class="cerrar-modal" onclick="ocultarStockAlertModal()">&times;</span>
        <h3 id="modal-titulo-stock">Stock Insuficiente</h3>
        <p id="modal-mensaje-stock">No hay suficiente stock disponible para este producto.</p>
        <button class="btn btn-primary" onclick="ocultarStockAlertModal()">Aceptar</button>
    </div>
</div>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>


