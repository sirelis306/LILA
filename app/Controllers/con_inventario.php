<?php
require_once __DIR__ . "/../Models/mod_inventario.php";

class InventarioController {
    public function index() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new InventarioModel();
        $busqueda = $_GET['q'] ?? '';
        $categoria = $_GET['categoria'] ?? '';

         // Obtener categorías únicas
        $categorias = $model->getCategorias();
        
        // Configurar paginación
        $paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
        $productosPorPagina = 10; // Ajusta según necesites
        
        // Obtener productos paginados
        $resultado = $model->getProductosPaginados($busqueda, $paginaActual, $productosPorPagina, $categoria);
        $productos = $resultado['productos'];
        $totalProductos = $resultado['total'];
        $totalPaginas = $resultado['totalPaginas'];
    
    include __DIR__ . '/../Views/inventario/productos.php';
}

    public function formProducto() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new InventarioModel();
        $producto = null;
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $producto = $model->getProductoById($id);
        }

        require_once __DIR__ . "/../Models/mod_ventas.php";
        $ventasModel = new VentasModel();
        $tasaHoy = $ventasModel->getTasaHoy();

        include __DIR__ . '/../Views/inventario/form.php';
    }

    public function guardarProducto() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '?r=login');
        exit;
    }

    $model = new InventarioModel();
    $imagenNombre = null;
    
    try {
        // Crear ruta para debug.log
        $debugFile = __DIR__ . '/../../debug.log';
        
        // Log de FILES
        file_put_contents($debugFile, 
            "[" . date('Y-m-d H:i:s') . "] === INICIO DE GUARDAR PRODUCTO ===\n", 
            FILE_APPEND
        );
        file_put_contents($debugFile, 
            "[" . date('Y-m-d H:i:s') . "] POST: " . print_r($_POST, true) . "\n", 
            FILE_APPEND
        );
        file_put_contents($debugFile, 
            "[" . date('Y-m-d H:i:s') . "] FILES: " . print_r($_FILES, true) . "\n", 
            FILE_APPEND
        );

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            file_put_contents($debugFile, 
                "[" . date('Y-m-d H:i:s') . "] ✅ Imagen recibida: " . $_FILES['imagen']['name'] . "\n", 
                FILE_APPEND
            );
            $imagenNombre = $model->subirImagen($_FILES['imagen']);
            file_put_contents($debugFile, 
                "[" . date('Y-m-d H:i:s') . "] ✅ Imagen guardada como: " . $imagenNombre . "\n", 
                FILE_APPEND
            );
        } elseif (!empty($_POST['mantener_imagen']) && $_POST['mantener_imagen'] == '1') {
            $imagenNombre = $_POST['imagen_actual'] ?? null;
            file_put_contents($debugFile, 
                "[" . date('Y-m-d H:i:s') . "] ℹ️ Manteniendo imagen actual: " . $imagenNombre . "\n", 
                FILE_APPEND
            );
        } else {
            file_put_contents($debugFile, 
                "[" . date('Y-m-d H:i:s') . "] ⚠️ No se subió imagen ni se mantuvo la actual.\n", 
                FILE_APPEND
            );
        }

        $datos = [
            'id_producto' => $_POST['id_producto'] ?? null,
            'nombre_producto' => $_POST['nombre_producto'],
            'descripcion' => $_POST['descripcion'],
            'tamano' => $_POST['tamano'],
            'categoria' => $_POST['categoria'] ?? 'General',
            'cantidad' => $_POST['cantidad'],
            'codigo_barras' => $_POST['codigo_barras'],
            'precio_bs' => $_POST['precio_bs'],
            'precio_usd' => $_POST['precio_usd'],
            'imagen' => $imagenNombre
        ];

        file_put_contents($debugFile, 
            "[" . date('Y-m-d H:i:s') . "] Datos a guardar: " . print_r($datos, true) . "\n", 
            FILE_APPEND
        );

        if ($model->guardarProducto($datos)) {
            $_SESSION['flash'] = "✅ Producto guardado exitosamente";
        } else {
            $_SESSION['flash'] = "❌ Error al guardar el producto";
        }

    } catch (Exception $e) {
        $_SESSION['flash'] = "❌ Error: " . $e->getMessage();
        file_put_contents(__DIR__ . '/../../debug.log', 
            "[" . date('Y-m-d H:i:s') . "] ❌ ERROR: " . $e->getMessage() . "\n", 
            FILE_APPEND
        );
    }

    header('Location: ' . BASE_URL . '?r=inventario');
    exit;
}

    public function eliminarProducto() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new InventarioModel();
            if ($model->eliminarProducto($id)) {
                $_SESSION['flash'] = "✅ Producto eliminado exitosamente";
            } else {
                $_SESSION['flash'] = "❌ Error al eliminar el producto";
            }
        }

        header('Location: ' . BASE_URL . '?r=inventario');
        exit;
    }
}
?>