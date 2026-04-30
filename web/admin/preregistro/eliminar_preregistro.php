<?php
// 1. Protección: Solo administradores pueden realizar eliminaciones
// auth.php verifica la sesión y el rol de admin automáticamente
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión
include('../../conexion.php');

// 3. Validación y ejecución segura
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Usamos prepared statements para mayor seguridad contra inyección SQL
    $stmt = $conexion->prepare("DELETE FROM VERANO WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// 4. Redirección final
header("Location: preregistro.php");
exit();
?>