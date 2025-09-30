<?php
$esAdmin = (currentUser()['rol'] ?? '') === 'administrador';
$titulo = $esAdmin ? 'Historial de Ventas' : 'Nueva Venta';

// Obtener tasa del día
$model = new VentasModel();
$tasaHoy = $model->getTasaHoy();

include __DIR__ . '/../shared/dashboard_layout.php';
?>

<?php include __DIR__ . '/../shared/flash.php'; ?>

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
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
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
                
                <button onclick="mostrarPasoPago()" 
                        class="btn-procesar-venta" 
                        id="btn-procesar" 
                        disabled>
                    PROCESAR VENTA
                </button>
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
                    <span>Subtotal:</span>
                    <span id="resumen-subtotal">$0.00</span>
                </div>
                <div class="total-fila">
                    <span>IVA (16%):</span>
                    <span id="resumen-iva">$0.00</span>
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
                            class="btn btn-secondary" 
                            style="flex: 1;">
                        Volver
                    </button>
                    <button type="submit" 
                            class="btn-procesar-venta" 
                            style="flex: 2;">
                        PROCESAR VENTA
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- VISTA ADMIN - HISTORIAL -->
    <div class="alert alert-info">
        <p>Funcionalidad de historial de ventas en desarrollo...</p>
        <a href="<?= BASE_URL ?>?r=form-tasa" class="btn btn-primary">Gestionar Tasas</a>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../shared/dashboard_end.php'; ?>

<!-- Incluir el archivo JavaScript externo -->
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>js/ventas.js?v=1.0"></script>
