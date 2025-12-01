<?php
require_once __DIR__ . "/../Models/mod_ventas.php";
require_once __DIR__ . "/../Helpers/auth.php";

class ReportesController {
    public function dashboard() {
        // Admin y empleado pueden ver este dashboard; la diferencia es que
        // solo el administrador verá opciones de gestión (si se agregan).
        requireLogin();

        $ventasModel = new VentasModel();

        // Datos para tarjetas y gráficas
        $ventasSemanales = $ventasModel->getVentasSemanales();
        $topProductos = $ventasModel->getTopProductosMasVendidos(5);

        // Producto más vendido (por cantidad)
        $productoMasVendido = $topProductos[0] ?? null;

        $currentUser = currentUser();
        $esAdmin = ($currentUser['rol'] ?? '') === 'administrador';

        include __DIR__ . '/../Views/reportes/dashboard.php';
    }
}


