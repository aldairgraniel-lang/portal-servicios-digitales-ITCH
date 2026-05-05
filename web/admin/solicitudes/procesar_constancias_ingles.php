<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $stmt = $conexion->prepare("DELETE FROM registro_ingles WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                header("Location: solicitudes_ingles_constancias.php?mensaje=eliminado");
            } else {
                header("Location: solicitudes_ingles_constancias.php?mensaje=error");
            }
            $stmt->close();
        } else {
            header("Location: solicitudes_ingles_constancias.php?mensaje=error");
        }
        exit();
    } 
    
    elseif ($accion === 'guardar') {
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $n_control = $conexion->real_escape_string($_POST['numero_control']);
        $carrera = $conexion->real_escape_string($_POST['carrera']);
        $periodo = $conexion->real_escape_string($_POST['periodo']);
        $tipo_alumno = $conexion->real_escape_string($_POST['tipo_alumno']);
        $semestre = !empty($_POST['semestre']) ? $conexion->real_escape_string($_POST['semestre']) : null;

        $stmt = $conexion->prepare("INSERT INTO registro_ingles (nombre, numero_control, carrera, periodo, tipo_alumno, semestre, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $nombre, $n_control, $carrera, $periodo, $tipo_alumno, $semestre);
        if ($stmt->execute()) {
            header("Location: solicitudes_ingles_constancias.php?mensaje=guardado");
        } else {
            header("Location: solicitudes_ingles_constancias.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }

    elseif ($accion === 'actualizar') {
        $id = intval($_POST['id']);
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $n_control = $conexion->real_escape_string($_POST['numero_control']);
        $carrera = $conexion->real_escape_string($_POST['carrera']);
        $periodo = $conexion->real_escape_string($_POST['periodo']);
        $tipo_alumno = $conexion->real_escape_string($_POST['tipo_alumno']);
        $semestre = !empty($_POST['semestre']) ? $conexion->real_escape_string($_POST['semestre']) : null;

        $stmt = $conexion->prepare("UPDATE registro_ingles SET nombre = ?, numero_control = ?, carrera = ?, periodo = ?, tipo_alumno = ?, semestre = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nombre, $n_control, $carrera, $periodo, $tipo_alumno, $semestre, $id);

        if ($stmt->execute()) {
            header("Location: solicitudes_ingles_constancias.php?mensaje=actualizado");
        } else {
            header("Location: solicitudes_ingles_constancias.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }
}
?>