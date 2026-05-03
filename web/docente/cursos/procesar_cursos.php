<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once(__DIR__ . "/../../conexion.php");

$accion = $_GET['accion'] ?? '';

// --- CREAR ---
if ($accion == 'crear' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $clave = trim($_POST['clave']);
    
    if (!empty($nombre) && !empty($clave)) {
        $stmt = $conexion->prepare("INSERT INTO cursos (nombre, clave) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $clave);
        $stmt->execute();
        $stmt->close();
        $_SESSION['mensaje'] = 'Curso creado correctamente.';
    }
    header("Location: cursos.php");
    exit;
}

// --- ACTUALIZAR ---
if ($accion == 'actualizar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $clave = trim($_POST['clave']);
    
    if (!empty($nombre) && !empty($clave) && !empty($id)) {
        $stmt = $conexion->prepare("UPDATE cursos SET nombre = ?, clave = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $clave, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['mensaje'] = 'Curso actualizado correctamente.';
    }
    header("Location: cursos.php");
    exit;
}

// --- ELIMINAR ---
if ($accion == 'eliminar' && isset($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $stmt->close();
    $_SESSION['mensaje'] = 'Curso eliminado correctamente.';
    header("Location: cursos.php");
    exit;
}
?>