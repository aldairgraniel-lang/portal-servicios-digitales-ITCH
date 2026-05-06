<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Procesar inserciones y actualizaciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim($_POST['accion'] ?? '');
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    // AGREGAR
    if ($accion === 'agregar') {
        if (!empty($nombre)) {
            $stmt = $conexion->prepare("INSERT INTO periodos (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: periodos.php");
        exit;
    } 
    
    // ACTUALIZAR (EDITAR)
    elseif ($accion === 'actualizar') {
        if ($id > 0 && !empty($nombre)) {
            $stmt = $conexion->prepare("UPDATE periodos SET nombre=? WHERE id=?");
            $stmt->bind_param("si", $nombre, $id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: periodos.php");
        exit;
    }
}

// Procesar eliminación (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM periodos WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: periodos.php");
    exit;
}
?>