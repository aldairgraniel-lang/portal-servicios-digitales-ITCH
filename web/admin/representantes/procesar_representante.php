<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");

// Procesar inserciones y actualizaciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim($_POST['accion'] ?? '');
    $id = intval($_POST['id'] ?? 0);
    $numero_control = trim($_POST['numero_control'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');

    if ($accion === 'editar' && $id > 0) {
        // Actualizar
        $stmt = $conexion->prepare("UPDATE representantes SET numero_control=?, nombre=? WHERE id=?");
        $stmt->bind_param("ssi", $numero_control, $nombre, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($accion === 'agregar') {
        // Insertar
        $stmt = $conexion->prepare("INSERT INTO representantes (numero_control, nombre) VALUES (?, ?)");
        $stmt->bind_param("ss", $numero_control, $nombre);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: representantes.php");
    exit;
}

// Procesar eliminación (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM representantes WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: representantes.php");
    exit;
}
?>