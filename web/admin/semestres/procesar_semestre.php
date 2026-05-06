<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");

// Procesar inserciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = intval($_POST['numero'] ?? 0);

    // Solo inserta si el número es mayor o igual a 12
    if ($numero >= 12) {
        $stmt = $conexion->prepare("INSERT INTO semestres (numero) VALUES (?)");
        $stmt->bind_param("i", $numero);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: semestre.php");
    exit;
}

// Procesar eliminación (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM semestres WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: semestre.php");
    exit;
}