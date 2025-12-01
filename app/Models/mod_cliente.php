<?php
require_once __DIR__ . "/../Config/db.php";

class ClienteModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los clientes con búsqueda opcional
     */
    public function getClientes($busqueda = '') {
        $sql = "SELECT * FROM clientes WHERE 1=1";
        $params = [];
        
        if ($busqueda) {
            $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR telefono LIKE ? OR direccion LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql .= " ORDER BY nombre, apellido";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un cliente por ID
     */
    public function getClienteById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guarda un cliente (crea o actualiza)
     */
    public function guardarCliente($datos) {
        if (isset($datos['id_cliente']) && !empty($datos['id_cliente'])) {
            // Actualizar
            $stmt = $this->pdo->prepare("
                UPDATE clientes 
                SET nombre = ?, apellido = ?, telefono = ?, direccion = ?
                WHERE id_cliente = ?
            ");
            return $stmt->execute([
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'],
                $datos['direccion'],
                $datos['id_cliente']
            ]);
        } else {
            // Crear
            $stmt = $this->pdo->prepare("
                INSERT INTO clientes (nombre, apellido, telefono, direccion)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'],
                $datos['direccion']
            ]);
        }
    }

    /**
     * Elimina un cliente por ID
     */
    public function eliminarCliente($id) {
        // Verificar si el cliente tiene ventas asociadas
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM ventas WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar el cliente porque tiene ventas asociadas.");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtiene todos los clientes para un select (usado en ventas)
     */
    public function getClientesParaSelect() {
        $stmt = $this->pdo->query("SELECT id_cliente, CONCAT(nombre, ' ', apellido) as nombre_completo FROM clientes ORDER BY nombre, apellido");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
