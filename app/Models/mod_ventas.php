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

    public function getTasaById($idTasa) {
        $stmt = $this->pdo->prepare("SELECT tasa FROM tasa_cambio WHERE id_tasa = ?");
        $stmt->execute([$idTasa]);
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }

    private function _insertarCabeceraVenta($totalBs, $totalUsd, $metodoPago, $referenciaPago, $idUsuario, $idCliente, $idTasa) {
        $stmt = $this->pdo->prepare("INSERT INTO ventas 
            (fecha, total_bs, total_usd, metodo_pago, referencia_pago, id_usuario, id_cliente, id_tasa) 
            VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, ?)");
        
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
            // Obtener la tasa REAL usada en la venta
            $tasaHoy = $this->getTasaById($idTasa);
            if (!$tasaHoy) {
                throw new Exception("Tasa de cambio no encontrada para el ID: " . $idTasa);
            }
            $tasaRealParaVenta = (float) $tasaHoy['tasa'];

            // Validar STOCK de todos los productos ===
            foreach ($carrito as $item) {
                $idProducto = $item['id'];
                $cantidadSolicitada = (int) $item['cantidad'];
                $stockActual = $this->getProductoStock($idProducto);
                
                if ($stockActual < $cantidadSolicitada) {
                    throw new Exception('Stock insuficiente para el producto: ' . htmlspecialchars($item['nombre']) . '. Solo quedan ' . $stockActual . ' unidades.');
                }
            }

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

            // Insertar DETALLES y actualizar STOCK
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

    public function getHistorialVentas($fecha_desde = null, $fecha_hasta = null, $limit = 50) {
        
        // Consulta base
        $sql_base = "SELECT v.*, u.usuario, c.nombre as cliente_nombre
                    FROM ventas v 
                    JOIN usuarios u ON v.id_usuario = u.id_usuario 
                    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente";

        $where_conditions = [];
        $parameters = [];

        // Construir el WHERE dinámicamente
        if (!empty($fecha_desde)) {
            $where_conditions[] = "DATE(v.fecha) >= ?";
            $parameters[] = $fecha_desde;
        }

        if (!empty($fecha_hasta)) {
            $where_conditions[] = "DATE(v.fecha) <= ?";
            $parameters[] = $fecha_hasta;
        }

        // Unir la consulta
        $sql = $sql_base;
        if (count($where_conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }

        // Añadir el ORDEN y el LÍMITE
        $sql .= " ORDER BY v.fecha DESC LIMIT ?";

        // Preparar la consulta
        $stmt = $this->pdo->prepare($sql);

        // Vincular los parámetros de fecha 
        $param_index = 1;
        foreach ($parameters as $param) {
            $stmt->bindValue($param_index, $param, PDO::PARAM_STR);
            $param_index++;
        }

        $stmt->bindValue($param_index, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Obtiene los datos principales de una sola venta */
    public function getVentaCompletaPorId($idVenta) {
        $sql = "SELECT 
                    v.*, 
                    u.usuario AS vendedor_nombre, 
                    c.nombre AS cliente_nombre,
                    t.tasa AS tasa_aplicada
                FROM ventas v
                JOIN usuarios u ON v.id_usuario = u.id_usuario
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                JOIN tasa_cambio t ON v.id_tasa = t.id_tasa
                WHERE v.id_venta = ?";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idVenta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*Obtiene los productos de una venta específica.*/
    public function getDetalleVenta($idVenta) {
        $sql = "SELECT 
                    d.cantidad, 
                    d.precio_unitario_usd,
                    p.nombre_producto
                FROM detalle_venta d
                JOIN productos p ON d.id_producto = p.id_producto
                WHERE d.id_venta = ?";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idVenta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ventas de los últimos 7 días (total USD por día)
     */
    public function getVentasSemanales() {
        $sql = "SELECT 
                    DATE(fecha) as fecha,
                    SUM(total_usd) as total_usd
                FROM ventas
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(fecha)
                ORDER BY fecha ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top N productos más vendidos (por cantidad) en un rango reciente (últimos 30 días)
     */
    public function getTopProductosMasVendidos($limit = 5) {
        $sql = "SELECT 
                    p.nombre_producto,
                    SUM(d.cantidad) as total_cantidad,
                    SUM(d.subtotal_usd) as total_usd
                FROM detalle_venta d
                JOIN ventas v ON d.id_venta = v.id_venta
                JOIN productos p ON d.id_producto = p.id_producto
                WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY p.id_producto, p.nombre_producto
                ORDER BY total_cantidad DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>