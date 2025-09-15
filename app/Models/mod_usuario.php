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
}

