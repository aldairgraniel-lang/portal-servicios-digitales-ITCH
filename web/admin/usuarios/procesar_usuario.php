<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Procesar inserciones y actualizaciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = trim($_POST['accion'] ?? '');
    $id = intval($_POST['id'] ?? 0);
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = trim($_POST['rol'] ?? '');

    if ($accion === 'editar' && $id > 0) {
        if (!empty($password)) {
            // Actualizar con contraseña encriptada en MD5
            $hashed_password = md5($password);
            $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, password=?, rol=? WHERE id=?");
            $stmt->bind_param("sssi", $usuario, $hashed_password, $rol, $id);
        } else {
            // Actualizar sin modificar la contraseña
            $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, rol=? WHERE id=?");
            $stmt->bind_param("ssi", $usuario, $rol, $id);
        }
        $stmt->execute();
        $stmt->close();
    } elseif ($accion === 'agregar') {
        if (!empty($password)) {
            $hashed_password = md5($password);
            $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password, rol) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $usuario, $hashed_password, $rol);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: usuarios.php");
    exit;
}

// Procesar eliminación (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: usuarios.php");
    exit;
}
?>