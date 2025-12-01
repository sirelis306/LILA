<?php
require_once __DIR__ . "/../Models/mod_cliente.php";
require_once __DIR__ . "/../Helpers/auth.php";
require_once __DIR__ . "/../Helpers/csrf.php";

class ClienteController {
    /**
     * Lista todos los clientes
     */
    public function index() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new ClienteModel();
        $busqueda = $_GET['q'] ?? '';
        $clientes = $model->getClientes($busqueda);
        
        include __DIR__ . '/../Views/clientes/lista.php';
    }

    /**
     * Muestra el formulario para crear/editar cliente
     */
    public function formCliente() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $model = new ClienteModel();
        $cliente = null;
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $cliente = $model->getClienteById($id);
            if (!$cliente) {
                $_SESSION['flash'] = "Cliente no encontrado";
                header('Location: ' . BASE_URL . '?r=clientes');
                exit;
            }
        }

        include __DIR__ . '/../Views/clientes/form.php';
    }

    /**
     * Guarda un cliente (crea o actualiza)
     */
    public function guardarCliente() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        // Validar CSRF token
        if (!csrf_check($_POST['csrf'] ?? '')) {
            $_SESSION['flash'] = "Token CSRF inválido";
            header('Location: ' . BASE_URL . '?r=clientes');
            exit;
        }

        // Validar datos requeridos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $codigoPais = trim($_POST['codigo_pais'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $telefonoCompleto = trim($_POST['telefono_completo'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        // Usar teléfono completo si está disponible, sino concatenar código + número
        if (!empty($telefonoCompleto)) {
            $telefonoFinal = trim($telefonoCompleto);
        } else if (!empty($codigoPais) && !empty($telefono)) {
            $telefonoFinal = trim($codigoPais) . trim($telefono);
        } else if (!empty($telefono)) {
            // Si solo hay teléfono sin código, agregar código de Venezuela por defecto
            $telefonoFinal = '+58' . trim($telefono);
        } else {
            $telefonoFinal = '';
        }

        // Validar que el teléfono tenga al menos 10 dígitos (código + número)
        $telefonoFinal = preg_replace('/[^0-9+]/', '', $telefonoFinal);
        
        if (empty($nombre) || empty($apellido) || empty($telefonoFinal) || strlen($telefonoFinal) < 10) {
            $_SESSION['flash'] = "Por favor complete todos los campos requeridos. El teléfono debe tener al menos 10 dígitos.";
            $id = $_POST['id_cliente'] ?? null;
            if ($id) {
                header('Location: ' . BASE_URL . '?r=form-cliente&id=' . $id);
            } else {
                header('Location: ' . BASE_URL . '?r=form-cliente');
            }
            exit;
        }

        $model = new ClienteModel();
        
        $datos = [
            'id_cliente' => !empty($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefonoFinal,
            'direccion' => $direccion
        ];

        try {
            if ($model->guardarCliente($datos)) {
                $_SESSION['flash'] = "✅ Cliente guardado exitosamente";
            } else {
                $_SESSION['flash'] = "❌ Error al guardar el cliente";
            }
        } catch (Exception $e) {
            $_SESSION['flash'] = "❌ Error: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '?r=clientes');
        exit;
    }

    /**
     * Elimina un cliente
     */
    public function eliminarCliente() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if ($id) {
            $model = new ClienteModel();
            try {
                if ($model->eliminarCliente($id)) {
                    $_SESSION['flash'] = "✅ Cliente eliminado exitosamente";
                } else {
                    $_SESSION['flash'] = "❌ Error al eliminar el cliente";
                }
            } catch (Exception $e) {
                $_SESSION['flash'] = "❌ " . $e->getMessage();
            }
        }

        header('Location: ' . BASE_URL . '?r=clientes');
        exit;
    }
}
?>
