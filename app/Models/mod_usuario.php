<?php
require_once __DIR__ . "/../config/db.php";

class User {
    private $pdo;

    public function __construct() {
        global $pdo; // usa el mismo PDO de db.php
        $this->pdo = $pdo;
    }

    public function findByUsername($usuario) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY id_usuario ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un nombre de usuario ya existe
     */
    public function usernameExists($usuario, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usuario = ?";
        $params = [$usuario];
        
        if ($excludeId) {
            $sql .= " AND id_usuario != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] > 0;
    }

    /**
     * Actualiza el perfil del usuario
     */
    public function actualizarPerfil($idUsuario, $datos) {
        // Verificar si se está cambiando el usuario y si ya existe
        if (isset($datos['usuario']) && $datos['usuario'] !== '') {
            if ($this->usernameExists($datos['usuario'], $idUsuario)) {
                throw new Exception("El nombre de usuario ya está en uso");
            }
        }

        // Construir la consulta dinámicamente según los campos que se actualicen
        $campos = [];
        $params = [];

        if (isset($datos['usuario']) && $datos['usuario'] !== '') {
            $campos[] = "usuario = ?";
            $params[] = $datos['usuario'];
        }

        if (isset($datos['contrasena']) && $datos['contrasena'] !== '') {
            $campos[] = "contrasena = ?";
            $params[] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        }

        return $this->ejecutarUpdate($idUsuario, $campos, $params);
    }

    /**
     * Actualización genérica para administrador (incluye rol)
     */
    public function actualizarUsuarioAdmin($idUsuario, $datos) {
        // Verificar usuario duplicado
        if (isset($datos['usuario']) && $datos['usuario'] !== '') {
            if ($this->usernameExists($datos['usuario'], $idUsuario)) {
                throw new Exception("El nombre de usuario ya está en uso");
            }
        }

        $campos = [];
        $params = [];

        if (isset($datos['nombre']) && $datos['nombre'] !== '') {
            $campos[] = "nombre = ?";
            $params[] = $datos['nombre'];
        }

        if (isset($datos['usuario']) && $datos['usuario'] !== '') {
            $campos[] = "usuario = ?";
            $params[] = $datos['usuario'];
        }

        if (isset($datos['contrasena']) && $datos['contrasena'] !== '') {
            $campos[] = "contrasena = ?";
            $params[] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
        }

        if (isset($datos['rol']) && $datos['rol'] !== '') {
            $campos[] = "rol = ?";
            $params[] = $datos['rol'];
        }

        return $this->ejecutarUpdate($idUsuario, $campos, $params);
    }

    /**
     * Crea un nuevo usuario
     */
    public function crearUsuario($datos) {
        // Validar que el usuario no exista
        if ($this->usernameExists($datos['usuario'])) {
            throw new Exception("El nombre de usuario ya está en uso");
        }

        // Validar campos requeridos
        if (empty($datos['usuario']) || empty($datos['contrasena']) || empty($datos['rol'])) {
            throw new Exception("Faltan campos requeridos para crear el usuario");
        }

        // Construir la consulta
        $campos = ['usuario', 'contrasena', 'rol'];
        $valores = ['?', '?', '?'];
        $params = [
            $datos['usuario'],
            password_hash($datos['contrasena'], PASSWORD_DEFAULT),
            $datos['rol']
        ];

        $sql = "INSERT INTO usuarios (" . implode(", ", $campos) . ") VALUES (" . implode(", ", $valores) . ")";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Unknown column 'nombre'") !== false) {
                throw new Exception("El campo 'nombre' no existe en la base de datos. Por favor ejecuta el script SQL para agregarlo.");
            }
            throw $e;
        }
    }

    private function ejecutarUpdate($idUsuario, array $campos, array $params) {
        if (empty($campos)) {
            return false;
        }

        $params[] = $idUsuario;
        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id_usuario = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "Unknown column 'nombre'") !== false) {
                throw new Exception("El campo 'nombre' no existe en la base de datos. Por favor ejecuta el script SQL para agregarlo.");
            }
            throw $e;
        }
    }
}

