<?php
require_once __DIR__ . "/../Models/mod_ventas.php";
require_once __DIR__ . "/../Models/mod_tasa.php";
require_once __DIR__ . "/../Helpers/auth.php"; 
require_once __DIR__ . "/../Helpers/csrf.php"; 

class VentasController {
    public function formVenta() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $tasaModel = new TasaModel();
        $tasaHoy = $tasaModel->getTasaHoy();

        // Puedes descomentar este bloque si quieres forzar al usuario a registrar la tasa antes de vender
        /*
        if (!$tasaHoy) {
            $_SESSION['flash'] = "Debe registrar la tasa del día primero";
            header("Location: " . BASE_URL . "?r=form-tasa");
            exit;
        }
        */

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
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }

        // Asegúrate de que la petición sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        // Validar CSRF token
        if (!csrf_check($_POST['csrf'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Token CSRF inválido']);
            exit;
        }

        $model = new VentasModel();
        $tasaModel = new TasaModel();

        // Recuperar datos del POST
        $carritoJson = $_POST['carrito'] ?? '[]';
        $carrito = json_decode($carritoJson, true);
        $tasaValorFrontend = floatval($_POST['tasa'] ?? '0'); 
        $metodoPago = $_POST['metodo_pago'] ?? '';
        $referenciaPago = $_POST['referencia_pago'] ?? null;
        $idCliente = !empty($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null;
        $idUsuario = currentUser()['id']; 

        if (empty($carrito) || $tasaValorFrontend <= 0 || empty($metodoPago)) {
            echo json_encode(['success' => false, 'error' => 'Datos de venta incompletos o inválidos.']);
            exit;
        }

        // Obtener la tasa del día actual de la base de datos para asegurar su validez
        $tasaHoy = $tasaModel->getTasaHoy();
        if (!$tasaHoy) {
             echo json_encode(['success' => false, 'error' => 'No hay una tasa de cambio registrada para hoy.']);
             exit;
        }
        
        // Comprobar que la tasa del frontend sea razonablemente cercana a la de la DB
        if (abs($tasaHoy['tasa'] - $tasaValorFrontend) > 0.01) { 
             echo json_encode(['success' => false, 'error' => 'La tasa de cambio proporcionada no coincide con la del día.']);
             exit;
        }
        
        // Usar la tasa y el ID de tasa REALES de la base de datos para los cálculos y el registro
        $tasaRealParaVenta = $tasaHoy['tasa']; 
        $idTasaParaVenta = $tasaHoy['id_tasa'];

        $subtotalUsd = 0; 
        
        foreach ($carrito as $item) {
            $subtotalUsd += $item['precio'] * (int) $item['cantidad'];
        }

        // Calcular IVA y total final en USD y BS
        $ivaUsd = $subtotalUsd * 0.16;
        $totalVentaUsd = $subtotalUsd + $ivaUsd;
        $totalVentaBs = $totalVentaUsd * $tasaRealParaVenta; // Usamos la tasa real de la DB

        // Llamar al nuevo método transaccional del modelo
        try {
            $idVenta = $model->completarProcesoVenta(
                $carrito, 
                $totalVentaBs, 
                $totalVentaUsd, 
                $metodoPago, 
                $referenciaPago, 
                $idUsuario, 
                $idCliente, 
                $idTasaParaVenta
            );

            if ($idVenta) {
                echo json_encode(['success' => true, 'message' => 'Venta procesada exitosamente', 'id_venta' => $idVenta]);
            } else {
                // Esto solo se ejecutaría si completarProcesoVenta retorna false y no lanza excepción
                echo json_encode(['success' => false, 'error' => 'Error desconocido al completar la venta.']);
            }
        } catch (Exception $e) {
            // Captura cualquier excepción lanzada desde el modelo (ej. stock insuficiente, error DB)
            echo json_encode(['success' => false, 'error' => 'Error al procesar la venta: ' . $e->getMessage()]);
        }
        exit;
    }

    public function historialVentas() {
        if (!isLoggedIn() || ($_SESSION['user']['rol'] ?? '') != 'administrador') {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new VentasModel();
        $ventas = $model->getHistorialVentas();
        
        include __DIR__ . '/../Views/ventas/historial.php';
    }
}
?>