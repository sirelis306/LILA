<?php
require_once __DIR__ . "/../Models/mod_ventas.php";

class VentasController {
    public function formVenta() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new VentasModel();
        $tasaHoy = $model->getTasaHoy();
        
        /*if (!$tasaHoy) {
            $_SESSION['flash'] = "Debe registrar la tasa del día primero";
            header("Location: " . BASE_URL . "?r=form-tasa");
            exit;
        }*/

        include __DIR__ . '/../Views/ventas/form.php';
    }

    public function buscarProducto() {
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        $busqueda = $_GET['q'] ?? '';
        $model = new VentasModel();
        $productos = $model->buscarProducto($busqueda);
        
        header('Content-Type: application/json');
        echo json_encode($productos);
        exit;
    }

    public function procesarVenta() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        // Lógica temporal para prueba
        $_SESSION['flash'] = "Venta procesada exitosamente (modo prueba)";
        header("Location: " . BASE_URL . "?r=ventas");
        exit;
    }

    public function historialVentas() {
        if (!isLoggedIn() || $_SESSION['user']['rol'] != 'administrador') {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new VentasModel();
        $ventas = $model->getHistorialVentas();
        
        include __DIR__ . '/../Views/ventas/historial.php';
    }
}
?>