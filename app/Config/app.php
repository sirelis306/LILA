<?php
// app/config/app.php
if (session_status() === PHP_SESSION_NONE) {
  session_start(); // sesiones para autenticación
}

// Cambia esto si despliegas en subcarpeta
define('BASE_URL', 'http://localhost/lila/public/');