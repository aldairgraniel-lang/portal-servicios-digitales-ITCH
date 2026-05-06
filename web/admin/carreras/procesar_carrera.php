<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");

// Procesar inserciones y actualizaciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if ($id > 0) {
        // Actualizar
        $stmt = $conexion->prepare("UPDATE carreras SET nombre=? WHERE id=?");
        $stmt->bind_param("si", $nombre, $id);
    } else {
        // Insertar
        $stmt = $conexion->prepare("INSERT INTO carreras(nombre) VALUES(?)");
        $stmt->bind_param("s", $nombre);
    }
    
    $stmt->execute();
    $stmt->close();
    
    header("Location: carreras.php");
    exit;
}

// Procesar eliminación (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM carreras WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: carreras.php");
    exit;
}