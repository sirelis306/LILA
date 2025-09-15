<?php
session_start();
require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

// Carga controladores
require_once __DIR__ . '/../app/controllers/con_auth.php';
require_once __DIR__ . '/../app/controllers/con_dashboard.php';
require_once __DIR__ . '/../app/controllers/con_tasa.php'; 
require_once __DIR__ . '/../app/controllers/con_ventas.php';

$r = $_GET['r'] ?? 'login';

//validar si es AuthController o es otro nombre
switch ($r) {
  case 'login':          (new AuthController)->showLogin(); break;
  case 'login-post':     (new AuthController)->loginPost(); break;
  case 'logout':         (new AuthController)->logout();    break;

  case 'form-tasa':      (new TasaController)->formTasa(); break;  
  case 'guardar-tasa':   (new TasaController)->guardarTasa(); break;  

  case 'admin':          requireRole(['administrador']);  (new DashboardController)->admin(); break;
  case 'empleado':       requireRole(['empleado','administrador']); (new DashboardController)->empleado(); break;

case 'ventas': (new VentasController)->formVenta(); break;
case 'buscar-producto': (new VentasController)->buscarProducto(); break;
case 'procesar-venta': (new VentasController)->procesarVenta(); break;
case 'historial-ventas': (new VentasController)->historialVentas(); break;

  default:
    http_response_code(404);
    echo "Ruta no encontrada";
}