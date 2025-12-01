<?php
require_once __DIR__ . "/../Models/mod_usuario.php";
require_once __DIR__ . "/../Helpers/auth.php";
require_once __DIR__ . "/../Helpers/csrf.php";

class UsuariosAdminController {
    public function index() {
        requireRole(['administrador']);

        $userModel = new User();
        $usuarios = $userModel->getAll();

        include __DIR__ . '/../Views/usuarios/index.php';
    }

    public function editar() {
        requireRole(['administrador']);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('usuarios');
        }

        $userModel = new User();
        $usuario = $userModel->findById($id);

        if (!$usuario) {
            $_SESSION['flash'] = "Usuario no encontrado";
            redirect('usuarios');
        }

        include __DIR__ . '/../Views/usuarios/form.php';
    }

    public function actualizar() {
        requireRole(['administrador']);

        if (!csrf_check($_POST['csrf'] ?? '')) {
            $_SESSION['flash'] = "Token CSRF inválido";
            redirect('usuarios');
        }

        $id = $_POST['id_usuario'] ?? null;
        if (!$id) {
            redirect('usuarios');
        }

        $userModel = new User();

        $datos = [];
        if (!empty($_POST['nombre'])) {
            $datos['nombre'] = trim($_POST['nombre']);
        }
        if (!empty($_POST['usuario'])) {
            $datos['usuario'] = trim($_POST['usuario']);
        }
        if (!empty($_POST['rol'])) {
            $datos['rol'] = $_POST['rol'];
        }

        // Cambio de contraseña opcional (admin no necesita contraseña actual)
        if (!empty($_POST['nueva_contrasena'])) {
            if ($_POST['nueva_contrasena'] !== ($_POST['confirmar_contrasena'] ?? '')) {
                $_SESSION['flash'] = "❌ Las contraseñas nuevas no coinciden";
                redirect('editar-usuario&id=' . $id);
            }
            if (strlen($_POST['nueva_contrasena']) < 6) {
                $_SESSION['flash'] = "❌ La contraseña debe tener al menos 6 caracteres";
                redirect('editar-usuario&id=' . $id);
            }
            $datos['contrasena'] = $_POST['nueva_contrasena'];
        }

        try {
            if (empty($datos)) {
                $_SESSION['flash'] = "⚠️ No se proporcionaron datos para actualizar";
            } else {
                if ($userModel->actualizarUsuarioAdmin($id, $datos)) {
                    $_SESSION['flash'] = "✅ Usuario actualizado correctamente";
                } else {
                    $_SESSION['flash'] = "❌ Error al actualizar el usuario";
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash'] = "❌ " . $e->getMessage();
        }

        redirect('usuarios');
    }
}


