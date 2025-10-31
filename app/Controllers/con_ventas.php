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

        if (!$tasaHoy) {
            $_SESSION['flash'] = "Debe registrar la tasa del día primero";
            header("Location: " . BASE_URL . "?r=form-tasa");
            exit;
        }

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
        $totalVentaBs = $totalVentaUsd * $tasaRealParaVenta; 

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
                echo json_encode(['success' => false, 'error' => 'Error desconocido al completar la venta.']);
            }
        } catch (Exception $e) {
            // Captura cualquier excepción lanzada desde el modelo 
            echo json_encode(['success' => false, 'error' => 'Error al procesar la venta: ' . $e->getMessage()]);
        }
        exit;
    }

    public function historialVentas() {
        if (!isLoggedIn() || ($_SESSION['user']['rol'] ?? '') != 'administrador') {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $fecha_desde = $_GET['fecha_desde'] ?? null;
        $fecha_hasta = $_GET['fecha_hasta'] ?? null;

        if (empty($fecha_desde) && !empty($fecha_hasta)) {
            $fecha_desde = $fecha_hasta;
        }

        $model = new VentasModel();

        $ventas = $model->getHistorialVentas($fecha_desde, $fecha_hasta); 
        
        include __DIR__ . '/../Views/ventas/historial.php';
    }

    public function getDatosFacturaJson() {
        if (!isLoggedIn() || ($_SESSION['user']['rol'] ?? '') != 'administrador') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $idVenta = $_GET['id'] ?? 0;
        if ($idVenta == 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'ID de venta inválido']);
            exit;
        }

        $model = new VentasModel();
        $venta = $model->getVentaCompletaPorId($idVenta);
        $items = $model->getDetalleVenta($idVenta);

        if (!$venta || empty($items)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Venta no encontrada o sin productos']);
            exit;
        }
        
        // Asumimos IVA 16% para recalcular el subtotal
        $subtotalUSD = $venta['total_usd'] / 1.16;
        $ivaUSD = $venta['total_usd'] - $subtotalUSD;
        $subtotalBS = $venta['total_bs'] / 1.16;

        // Formatear los items
        $itemsFormateados = [];
        foreach ($items as $item) {
            $itemsFormateados[] = [
                'nombre' => $item['nombre_producto'],
                'cantidad' => $item['cantidad'],         
                'precio' => $item['precio_unitario_usd']
            ];
        }

        // Objeto final que espera facturaPDF.js
        $datosFactura = [
            'numeroFactura' => $venta['id_venta'],
            'fecha' => date("d/m/Y", strtotime($venta['fecha'])),
            'cliente' => [
                'nombre' => $venta['cliente_nombre'] ?? 'Cliente Ocasional',
                'idCliente' => $venta['id_cliente'] ?? 'N/A'
            ],
            'items' => $itemsFormateados,
            
            'subtotalUSD' => $subtotalUSD,
            'ivaUSD' => $ivaUSD,
            'totalUSD' => $venta['total_usd'],
            
            'subtotalBS' => $subtotalBS,
            'totalBS' => $venta['total_bs'],
            'tasa' => $venta['tasa_aplicada'],

            'metodoPago' => $venta['metodo_pago'],
            'referencia' => $venta['referencia_pago'] ?? 'N/A'
        ];

        header('Content-Type: application/json');
        echo json_encode($datosFactura);
        exit;
    }
}
?>