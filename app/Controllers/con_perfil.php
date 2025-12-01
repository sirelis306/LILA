<?php
require_once __DIR__ . "/../Models/mod_usuario.php";
require_once __DIR__ . "/../Helpers/auth.php";
require_once __DIR__ . "/../Helpers/csrf.php";

class PerfilController {
    /**
     * Muestra el formulario de perfil
     */
    public function mostrarPerfil() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        $userModel = new User();
        $usuario = $userModel->findById(currentUser()['id']);
        
        if (!$usuario) {
            $_SESSION['flash'] = "Usuario no encontrado";
            header('Location: ' . BASE_URL . '?r=' . (currentUser()['rol'] === 'administrador' ? 'admin' : 'empleado'));
            exit;
        }

        include __DIR__ . '/../Views/perfil/form.php';
    }

    /**
     * Actualiza el perfil del usuario
     */
    public function actualizarPerfil() {
        if (!isLoggedIn()) {
            header('Location: ' . BASE_URL . '?r=login');
            exit;
        }

        // Validar CSRF token
        if (!csrf_check($_POST['csrf'] ?? '')) {
            $_SESSION['flash'] = "Token CSRF inválido";
            header('Location: ' . BASE_URL . '?r=perfil');
            exit;
        }

        $userModel = new User();
        $idUsuario = currentUser()['id'];
        
        // Obtener datos del formulario
        $datos = [];
        
        // Usuario (nombre de usuario)
        if (!empty($_POST['usuario'])) {
            $datos['usuario'] = trim($_POST['usuario']);
        }
        
        // Contraseña (solo si se proporciona una nueva)
        if (!empty($_POST['nueva_contrasena'])) {
            // Verificar contraseña actual
            $usuarioActual = $userModel->findById($idUsuario);
            if (!password_verify($_POST['contrasena_actual'] ?? '', $usuarioActual['contrasena'])) {
                $_SESSION['flash'] = "❌ La contraseña actual es incorrecta";
                header('Location: ' . BASE_URL . '?r=perfil');
                exit;
            }
            
            // Validar que las contraseñas nuevas coincidan
            if ($_POST['nueva_contrasena'] !== $_POST['confirmar_contrasena']) {
                $_SESSION['flash'] = "❌ Las contraseñas nuevas no coinciden";
                header('Location: ' . BASE_URL . '?r=perfil');
                exit;
            }
            
            // Validar longitud mínima
            if (strlen($_POST['nueva_contrasena']) < 6) {
                $_SESSION['flash'] = "❌ La contraseña debe tener al menos 6 caracteres";
                header('Location: ' . BASE_URL . '?r=perfil');
                exit;
            }
            
            $datos['contrasena'] = $_POST['nueva_contrasena'];
        }

        try {
            if (empty($datos)) {
                $_SESSION['flash'] = "⚠️ No se proporcionaron datos para actualizar";
            } else {
                if ($userModel->actualizarPerfil($idUsuario, $datos)) {
                    // Si se cambió el usuario, actualizar la sesión
                    if (isset($datos['usuario'])) {
                        $usuarioActualizado = $userModel->findById($idUsuario);
                        loginUser($usuarioActualizado);
                    }
                    
                    $_SESSION['flash'] = "✅ Perfil actualizado exitosamente";
                } else {
                    $_SESSION['flash'] = "❌ Error al actualizar el perfil";
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash'] = "❌ " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '?r=perfil');
        exit;
    }
}
?>

