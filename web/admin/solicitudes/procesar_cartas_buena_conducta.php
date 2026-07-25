<?php
// 1. Protección de sesión administrativa con ruta absoluta
include(__DIR__ . '/../includes/auth.php');

// 2. Conexión a la base de datos
include(__DIR__ . "/../../conexion.php");

// Verificamos que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: buena_conducta.php");
    exit;
}

// Capturamos los parámetros de control del formulario
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0 || empty($action)) {
    header("Location: buena_conducta.php?mensaje=error");
    exit;
}

// ==========================================
// 1. ACCIÓN: ACTUALIZAR REGISTRO (UPDATE)
// ==========================================
if ($action === 'update') {
    $numero_control = trim($_POST['numero_control']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $carrera = trim($_POST['carrera']);
    $fecha_solicitud = trim($_POST['fecha_solicitud']);

    // Validación de campos obligatorios
    if (empty($numero_control) || empty($nombre_completo) || empty($carrera) || empty($fecha_solicitud)) {
        header("Location: buena_conducta.php?mensaje=error");
        exit;
    }

    // Preparar la consulta para evitar inyecciones SQL
    $stmt = $conexion->prepare("UPDATE solicitudes_cartas_buena_conducta SET numero_control = ?, nombre_completo = ?, carrera = ?, fecha_solicitud = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $numero_control, $nombre_completo, $carrera, $fecha_solicitud, $id);

    if ($stmt->execute()) {
        header("Location: buena_conducta.php?mensaje=actualizado");
    } else {
        header("Location: buena_conducta.php?mensaje=error");
    }
    $stmt->close();
    exit;
}

// ==========================================
// 2. ACCIÓN: ELIMINAR REGISTRO (DELETE)
// ==========================================
if ($action === 'delete') {
    // Preparar la consulta de eliminación
    $stmt = $conexion->prepare("DELETE FROM solicitudes_cartas_buena_conducta WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: buena_conducta.php?mensaje=eliminado");
    } else {
        header("Location: buena_conducta.php?mensaje=error");
    }
    $stmt->close();
    exit;
}

// Si llega aquí por alguna acción no contemplada, se devuelve a la pantalla principal
header("Location: buena_conducta.php");
exit;
?>