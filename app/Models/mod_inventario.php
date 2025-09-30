<?php
require_once __DIR__ . "/../Config/db.php";

class InventarioModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getProductos($busqueda = '') {
        $sql = "SELECT * FROM productos WHERE 1=1";
        $params = [];
        
        if ($busqueda) {
            $sql .= " AND (nombre_producto LIKE ? OR codigo_barras LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql .= " ORDER BY nombre_producto";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductoById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardarProducto($datos) {
    if (isset($datos['id_producto']) && $datos['id_producto']) {
        // Mantener imagen si no se sube nueva
        if (empty($datos['imagen'])) {
            $productoActual = $this->getProductoById($datos['id_producto']);
            $datos['imagen'] = $productoActual['imagen'] ?? null;
        }

        $sql = "UPDATE productos SET 
                nombre_producto = ?, descripcion = ?, tamano = ?, 
                cantidad = ?, codigo_barras = ?, precio_bs = ?, precio_usd = ?, 
                imagen = ? 
                WHERE id_producto = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $datos['nombre_producto'], $datos['descripcion'], $datos['tamano'],
            $datos['cantidad'], $datos['codigo_barras'], $datos['precio_bs'],
            $datos['precio_usd'], $datos['imagen'], $datos['id_producto']
        ]);
        
    } else {
        $sql = "INSERT INTO productos 
                (nombre_producto, descripcion, tamano, cantidad, codigo_barras, precio_bs, precio_usd, imagen) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $datos['nombre_producto'], $datos['descripcion'], $datos['tamano'],
            $datos['cantidad'], $datos['codigo_barras'], $datos['precio_bs'],
            $datos['precio_usd'], $datos['imagen']
        ]);
    }
}


    public function eliminarProducto($id) {
        // Primero obtener info de la imagen para eliminarla
        $producto = $this->getProductoById($id);
        if ($producto && $producto['imagen']) {
            $rutaImagen = __DIR__ . '/../../public/img/productos/' . $producto['imagen'];
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
        return $stmt->execute([$id]);
    }

    // Función para subir imagen
    public function subirImagen($archivo) {
        $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validar tipo de archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $permitidos)) {
            throw new Exception('Formato no permitido. Use JPG, PNG, GIF o WebP.');
        }
        
        // Validar tamaño
        if ($archivo['size'] > $maxSize) {
            throw new Exception('La imagen es muy grande. Máximo 2MB.');
        }
        
        // Validar que sea una imagen real
        if (!getimagesize($archivo['tmp_name'])) {
            throw new Exception('El archivo no es una imagen válida.');
        }
        
        // Generar nombre único
        $nombreUnico = uniqid() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../../public/img/productos/'  . $nombreUnico;
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new Exception('Error al subir la imagen.');
        }
        
        return $nombreUnico;
    }
}
?>