<?php
class DashboardController {
    public function admin() {
        // Debe apuntar a panel_admin.php en la raíz de views/
        include __DIR__ . '/../views/panel_admin.php';
    }
    
    public function empleado() {
        // Debe apuntar a panel_empleado.php en la raíz de views/
        include __DIR__ . '/../views/panel_empleado.php';
    }
}
?>