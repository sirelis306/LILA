<?php

require_once __DIR__ . "/../Config/db.php";

class TasaModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getTasaHoy() {
        $stmt = $this->pdo->prepare("SELECT * FROM tasa_cambio WHERE fecha = CURDATE()");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardarTasa($tasa, $fecha, $idUsuario) {
        // Verificar si ya existe una tasa para esta fecha
        $stmt = $this->pdo->prepare("SELECT id_tasa FROM tasa_cambio WHERE fecha = ?");
        $stmt->execute([$fecha]);
        $existente = $stmt->fetch();
        
        if ($existente) {
            // Actualizar tasa existente
            $stmt = $this->pdo->prepare("UPDATE tasa_cambio SET tasa = ?, id_usuario = ? WHERE fecha = ?");
            return $stmt->execute([$tasa, $idUsuario, $fecha]);
        } else {
            // Insertar nueva tasa
            $stmt = $this->pdo->prepare("INSERT INTO tasa_cambio (tasa, fecha, id_usuario) VALUES (?, ?, ?)");
            return $stmt->execute([$tasa, $fecha, $idUsuario]);
        }
    }

    public function getTasaByDate($fecha) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasa_cambio WHERE fecha = ?");
        $stmt->execute([$fecha]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUltimaTasa() {
        $stmt = $this->pdo->prepare("SELECT * FROM tasa_cambio ORDER BY fecha DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 
?>