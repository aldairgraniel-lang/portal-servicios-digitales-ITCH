<?php
session_start();
// 1. Protección: Solo admins pueden ejecutar este script.
// Esto verifica el rol antes de realizar cualquier operación de borrado.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
// Habilitar reporte de errores de mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        try {
            $stmt = $conexion->prepare("DELETE FROM solicitudes_cartas_presentacion WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Si llega aquí es éxito
            header("Location: solicitudes_cartas_presentacion.php?mensaje=eliminado");
            exit();
        } catch (Exception $e) {
            // AQUÍ VERÁS EL ERROR REAL
            die("Error al eliminar: " . $e->getMessage());
        }
    } else {
        die("ID no válido.");
    }
}
?>