<?php
require_once __DIR__ . "/../models/mod_usuario.php";

class AuthController {
    public function showLogin() {
        require __DIR__ . "/../views/vis_login.php";
    }

    public function loginPost() {
        session_start();

        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['contrasena'] ?? '';

        $userModel = new User();
        $user = $userModel->findByUsername($usuario);

        if ($user && password_verify($password, $user['contrasena'])) {
            loginUser($user);

            if ($user['rol'] == 'administrador') {
                header("Location:" . BASE_URL . "?r=admin");
            } else {
                header("Location:" . BASE_URL . "?r=empleado");
            }
            exit;
        } else {
            $_SESSION['flash'] = "Usuario o contraseña incorrectos.";
            header("Location:" . BASE_URL . "?r=login");
            exit;
            echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit;
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header("Location: index.php?r=login");
        exit;
    }
}
?>