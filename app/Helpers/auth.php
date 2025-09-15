<?php
function isLoggedIn(): bool {
  return isset($_SESSION['user']);
}

function currentUser() {
  return $_SESSION['user'] ?? null;
}

function loginUser(array $user): void {
  // Guarda solo lo necesario
  $_SESSION['user'] = [
    'id'   => $user['id_usuario'],
    'user' => $user['usuario'],
    'rol'  => $user['rol'], // 'admin' o 'empleado' (o enum equivalente)
  ];
}

function logoutUser(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
}

function requireLogin(): void {
  if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '?r=login');
    exit;
  }
}

function requireRole(array $roles): void {
  requireLogin();
  $user = currentUser();
  if (!in_array($user['rol'], $roles)) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
  }
}

function redirect(string $route): void {
  header('Location: ' . BASE_URL . '?r=' . $route);
  exit;
}