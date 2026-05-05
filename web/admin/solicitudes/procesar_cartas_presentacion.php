<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            try {
                $stmt = $conexion->prepare("DELETE FROM solicitudes_cartas_presentacion WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                header("Location: solicitudes_cartas_presentacion.php?mensaje=eliminado");
                exit();
            } catch (Exception $e) {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
                exit();
            }
        } else {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
            exit();
        }
    } 
    
    elseif ($accion === 'guardar') {
        $nombre = $conexion->real_escape_string($_POST['nombre_estudiante']);
        $n_control = $conexion->real_escape_string($_POST['numero_control']);
        $tipo_tramite = $conexion->real_escape_string($_POST['tipo_tramite']);

        $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_presentacion (nombre_estudiante, numero_control, tipo_tramite, fecha_registro) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $nombre, $n_control, $tipo_tramite);
        if ($stmt->execute()) {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=guardado");
        } else {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }

    elseif ($accion === 'actualizar') {
        $id = intval($_POST['id']);
        $nombre = $conexion->real_escape_string($_POST['nombre_estudiante']);
        $n_control = $conexion->real_escape_string($_POST['numero_control']);
        $tipo_tramite = $conexion->real_escape_string($_POST['tipo_tramite']);

        $stmt = $conexion->prepare("UPDATE solicitudes_cartas_presentacion SET nombre_estudiante = ?, numero_control = ?, tipo_tramite = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $n_control, $tipo_tramite, $id);
        if ($stmt->execute()) {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=actualizado");
        } else {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }
}
?>