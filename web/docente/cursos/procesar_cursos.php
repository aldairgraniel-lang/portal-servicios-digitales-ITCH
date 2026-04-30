<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Asegúrate de que la ruta a auth_docente sea correcta
require_once(__DIR__ . "/../../conexion.php");

$accion = $_GET['accion'] ?? '';

// --- CREAR ---
if ($accion == 'crear' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    if (!empty($nombre)) {
        // mysqli preparación
        $stmt = $conexion->prepare("INSERT INTO cursos (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre); // "s" significa string
        $stmt->execute();
        $stmt->close();
    }
    header("Location: cursos.php"); // Redirigir a cursos.php
    exit;
}
// --- ACTUALIZAR ---
if ($accion == 'actualizar' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    
    if (!empty($nombre) && !empty($id)) {
        $stmt = $conexion->prepare("UPDATE cursos SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nombre, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: cursos.php");
    exit;
}

// --- ELIMINAR ---
if ($accion == 'eliminar' && isset($_GET['id'])) {
    // mysqli preparación
    $stmt = $conexion->prepare("DELETE FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']); // "i" significa integer
    $stmt->execute();
    $stmt->close();
    header("Location: cursos.php"); // Redirigir a cursos.php
    exit;
}



?>