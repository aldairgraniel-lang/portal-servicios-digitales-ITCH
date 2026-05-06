<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Procesar inserciones y actualizaciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim($_POST['accion'] ?? '');
    $id = intval($_POST['id'] ?? 0);
    $nombre_tramite = trim($_POST['nombre_tramite'] ?? '');

    // AGREGAR
    if ($accion === 'agregar') {
        if (!empty($nombre_tramite)) {
            $stmt = $conexion->prepare("INSERT INTO tipos_tramite (nombre_tramite) VALUES (?)");
            $stmt->bind_param("s", $nombre_tramite);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: tramites.php");
        exit;
    } 
    
    // ACTUALIZAR (EDITAR)
    elseif ($accion === 'actualizar') {
        if ($id > 0 && !empty($nombre_tramite)) {
            $stmt = $conexion->prepare("UPDATE tipos_tramite SET nombre_tramite=? WHERE id=?");
            $stmt->bind_param("si", $nombre_tramite, $id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: tramites.php");
        exit;
    }
}

// Procesar eliminación (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM tipos_tramite WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: tramites.php");
    exit;
}
?>