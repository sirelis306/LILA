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
        $stmt = $this->pdo->prepare("SELECT * FROM productos  WHERE (nombre LIKE ? OR id_producto = ?) AND activo = 1");
        $stmt->execute(["%$busqueda%", $busqueda]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearVenta($totalBs, $totalUsd, $metodoPago, $referenciaPago, $idUsuario, $idCliente, $idTasa) {
        $this->pdo->beginTransaction();
        
        try {
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
            
            $idVenta = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $idVenta;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function agregarDetalleVenta($idVenta, $idProducto, $cantidad, $precioUnitario, $subtotal) {
        $stmt = $this->pdo->prepare("INSERT INTO detalle_venta 
            (id_venta, id_producto, cantidad, precio_unitario, subtotal)
            VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$idVenta, $idProducto, $cantidad, $precioUnitario, $subtotal]);
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