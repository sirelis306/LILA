<?php
require_once __DIR__ . "/../Config/db.php";

class VentasModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getTasaHoy() {
        $stmt = $this->pdo->prepare("SELECT * FROM tasa_cambio WHERE fecha = CURDATE()");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarProducto($busqueda) {
        $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE (nombre_producto LIKE ? OR codigo_barras LIKE ? OR id_producto = ?)");
        $stmt->execute(["%$busqueda%", "%$busqueda%", $busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductoStock($idProducto) {
        $stmt = $this->pdo->prepare("SELECT cantidad FROM productos WHERE id_producto = ?");
        $stmt->execute([$idProducto]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['cantidad'] : 0;
    }

    private function _insertarCabeceraVenta($totalBs, $totalUsd, $metodoPago, $referenciaPago, $idUsuario, $idCliente, $idTasa) {
        $stmt = $this->pdo->prepare("INSERT INTO ventas 
            (total_bs, total_usd, metodo_pago, referencia_pago, id_usuario, id_cliente, id_tasa) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $totalBs, 
            $totalUsd, 
            $metodoPago, 
            $referenciaPago, 
            $idUsuario, 
            $idCliente, 
            $idTasa
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function _agregarDetalleVenta($idVenta, $idProducto, $cantidad, $precioUnitarioUsd, $precioUnitarioBs, $subtotalUsd, $subtotalBs) {
        $stmt = $this->pdo->prepare("
            INSERT INTO detalle_venta 
            (id_venta, id_producto, cantidad, precio_unitario_usd, precio_unitario_bs, subtotal_usd, subtotal_bs)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $idVenta, 
            $idProducto, 
            $cantidad, 
            $precioUnitarioUsd, 
            $precioUnitarioBs, 
            $subtotalUsd, 
            $subtotalBs
        ]);
    }

    private function _actualizarStockProducto($idProducto, $cantidadReducir) {
        $stmt = $this->pdo->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id_producto = ?");
        return $stmt->execute([$cantidadReducir, $idProducto]);
    }

    public function completarProcesoVenta(
        $carrito, 
        $totalVentaBs, 
        $totalVentaUsd, 
        $metodoPago, 
        $referenciaPago, 
        $idUsuario, 
        $idCliente, 
        $idTasa
    ) {
        $this->pdo->beginTransaction();
        
        try {
            // === PASO 1: Obtener la tasa REAL usada en la venta ===
            $tasaHoy = $this->getTasaById($idTasa);
            if (!$tasaHoy) {
                throw new Exception("Tasa de cambio no encontrada para el ID: " . $idTasa);
            }
            $tasaRealParaVenta = (float) $tasaHoy['tasa'];

            // === PASO 2: Validar STOCK de todos los productos ===
            foreach ($carrito as $item) {
                $idProducto = $item['id'];
                $cantidadSolicitada = (int) $item['cantidad'];
                $stockActual = $this->getProductoStock($idProducto);
                
                if ($stockActual < $cantidadSolicitada) {
                    throw new Exception('Stock insuficiente para el producto: ' . htmlspecialchars($item['nombre']) . '. Solo quedan ' . $stockActual . ' unidades.');
                }
            }

            // === PASO 3: Insertar la CABECERA de la venta ===
            $idVenta = $this->_insertarCabeceraVenta(
                $totalVentaBs, 
                $totalVentaUsd, 
                $metodoPago, 
                $referenciaPago, 
                $idUsuario, 
                $idCliente, 
                $idTasa
            );

            if (!$idVenta) {
                throw new Exception("Error al insertar la cabecera de la venta.");
            }

            // === PASO 4: Insertar DETALLES y actualizar STOCK ===
            foreach ($carrito as $item) {
                $idProducto = $item['id'];
                $cantidad = (int) $item['cantidad'];
                $precioUnitarioUsd = (float) $item['precio'];
                $precioUnitarioBs = $precioUnitarioUsd * $tasaRealParaVenta;
                $subtotalUsd = $precioUnitarioUsd * $cantidad;
                $subtotalBs = $precioUnitarioBs * $cantidad;

                // Agregar detalle
                if (!$this->_agregarDetalleVenta(
                    $idVenta, 
                    $idProducto, 
                    $cantidad, 
                    $precioUnitarioUsd, 
                    $precioUnitarioBs, 
                    $subtotalUsd, 
                    $subtotalBs
                )) {
                    throw new Exception("Error al agregar detalle para producto ID: " . $idProducto);
                }

                // Reducir stock
                if (!$this->_actualizarStockProducto($idProducto, $cantidad)) {
                    throw new Exception("Error al actualizar stock para producto ID: " . $idProducto);
                }
            }
            
            $this->pdo->commit();
            return $idVenta;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error en completarProcesoVenta: " . $e->getMessage());
            throw $e;
        }
    }

    public function getHistorialVentas($limit = 50) {
        $stmt = $this->pdo->prepare("SELECT v.*, u.usuario, c.nombre as cliente_nombre
                                   FROM ventas v 
                                   JOIN usuarios u ON v.id_usuario = u.id_usuario 
                                   LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                                   ORDER BY v.fecha DESC 
                                   LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientes() {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE activo = 1 ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasaById($idTasa) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasa_cambio WHERE id_tasa = ?");
        $stmt->execute([$idTasa]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>