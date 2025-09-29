<?php
class DashboardController {
    public function admin() {
        // Debe apuntar a vis_admin.php en la raíz de views/
        include __DIR__ . '/../views/vis_admin.php';
    }
    
    public function empleado() {
        // Debe apuntar a vis_empleado.php en la raíz de views/
        include __DIR__ . '/../views/vis_empleado.php';
    }
}
?>