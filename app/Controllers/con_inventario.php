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
        $productos = $model->getProductos($busqueda);
        
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
           if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagenNombre = $model->subirImagen($_FILES['imagen']);
            } elseif (!empty($_POST['mantener_imagen']) && $_POST['mantener_imagen'] == '1') {
            $imagenNombre = $_POST['imagen_actual'] ?? null;
        }
        
            $datos = [
                'id_producto' => $_POST['id_producto'] ?? null,
                'nombre_producto' => $_POST['nombre_producto'],
                'descripcion' => $_POST['descripcion'],
                'tamano' => $_POST['tamano'],
                'cantidad' => $_POST['cantidad'],
                'codigo_barras' => $_POST['codigo_barras'],
                'precio_bs' => $_POST['precio_bs'],
                'precio_usd' => $_POST['precio_usd'],
                'imagen' => $imagenNombre
            ];

            if ($model->guardarProducto($datos)) {
                $_SESSION['flash'] = "✅ Producto guardado exitosamente";
            } else {
                $_SESSION['flash'] = "❌ Error al guardar el producto";
            }

        } catch (Exception $e) {
            $_SESSION['flash'] = "❌ Error: " . $e->getMessage();
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