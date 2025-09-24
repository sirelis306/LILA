<?php
require_once __DIR__ . "/../Models/mod_tasa.php";

class TasaController {
    public function formTasa() {
        // Verificar sesión
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new TasaModel();
        $tasaHoy = $model->getTasaHoy();
        
        // Pasar variables a la vista
        $esAdmin = ($_SESSION['user']['rol'] ?? '') === 'administrador';
        
        // Incluir la vista
        include __DIR__ . '/../Views/tasa/form.php';
    }
    
    public function guardarTasa() {
        // Verificar sesión
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        // Validar CSRF
        if (!csrf_check($_POST['csrf'] ?? '')) {
            $_SESSION['flash'] = "Error de seguridad. Intente nuevamente.";
            header("Location: " . BASE_URL . "?r=form-tasa");
            exit;
        }

        // Validar rol
        if ($_SESSION['user']['rol'] != 'administrador' && 
            $_SESSION['user']['rol'] != 'empleado') {
            $_SESSION['flash'] = "No tiene permisos para esta acción";
            header("Location: " . BASE_URL . "?r=login");
            exit;
        }
        
        // Validar datos
        $tasa = floatval($_POST['tasa'] ?? 0);
        if ($tasa <= 0) {
            $_SESSION['flash'] = "La tasa debe ser mayor a cero";
            header("Location: " . BASE_URL . "?r=form-tasa");
            exit;
        }
        
        $fecha = date('Y-m-d');
        $idUsuario = $_SESSION['user']['id'];
        
        $model = new TasaModel();
        
        // Empleados solo pueden registrar una tasa por día
        if ($_SESSION['user']['rol'] == 'empleado') {
            $tasaExistente = $model->getTasaByDate($fecha);
            if ($tasaExistente) {
                $_SESSION['flash'] = "Ya registró la tasa del día hoy";
                header("Location: " . BASE_URL . "?r=ventas");
                exit;
            }
        }
        
        // Guardar tasa
        if ($model->guardarTasa($tasa, $fecha, $idUsuario)) {
            $_SESSION['flash'] = "Tasa registrada exitosamente (Modo Prueba)";
            header("Location: " . BASE_URL . "?r=ventas");
        } else {
            $_SESSION['flash'] = "Error al registrar la tasa";
            header("Location: " . BASE_URL . "?r=form-tasa");
        }
        exit;
    }
}
?>