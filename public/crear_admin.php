<?php
require_once __DIR__ . "/../app/config/db.php";  // tu archivo de conexión

$usuario = "admin";
$passwordPlano = "admin123";
$rol = "administrador";

// Crear hash de la contraseña
$hash = password_hash($passwordPlano, PASSWORD_BCRYPT);

// Insertar en la tabla usuarios
$stmt = $pdo->prepare("INSERT INTO usuarios (usuario, contraseña, rol) VALUES (?, ?, ?)");
$stmt->execute([$usuario, $hash, $rol]);

echo "Usuario administrador creado con éxito";

 //preguntar porque no dio en la base de datos en rol: admin