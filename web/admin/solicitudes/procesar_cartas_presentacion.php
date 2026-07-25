<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // 1. ACCIÓN: ELIMINAR
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
    
    // 2. ACCIÓN: GUARDAR NUEVO
    elseif ($accion === 'guardar') {
        try {
            // Sanitización de los datos reales del alumno
            $nombre         = $conexion->real_escape_string(trim($_POST['nombre'] ?? ''));
            $numero_control = $conexion->real_escape_string(trim($_POST['numero_control'] ?? ''));
            $dirigido_a     = $conexion->real_escape_string(trim($_POST['dirigido_a'] ?? ''));
            $materia        = $conexion->real_escape_string(trim($_POST['materia'] ?? ''));
            $semestre       = intval($_POST['semestre'] ?? 0);
            $periodo        = $conexion->real_escape_string(trim($_POST['periodo'] ?? ''));
            $fecha_inicio   = $conexion->real_escape_string($_POST['fecha_inicio'] ?? '');
            $fecha_final    = $conexion->real_escape_string($_POST['fecha_final'] ?? '');

            if (empty($nombre) || empty($numero_control) || empty($materia) || $semestre === 0) {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
                exit();
            }

            // Preparación del INSERT con la estructura correcta de campos
            $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_presentacion (nombre, numero_control, dirigido_a, materia, semestre, periodo, fecha_inicio, fecha_final, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssisss", $nombre, $numero_control, $dirigido_a, $materia, $semestre, $periodo, $fecha_inicio, $fecha_final);
            
            if ($stmt->execute()) {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=guardado");
            } else {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
            }
            $stmt->close();
            exit();
        } catch (Exception $e) {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
            exit();
        }
    }

    // 3. ACCIÓN: ACTUALIZAR REGISTRO
    elseif ($accion === 'actualizar') {
        try {
            $id             = intval($_POST['id'] ?? 0);
            $nombre         = $conexion->real_escape_string(trim($_POST['nombre'] ?? ''));
            $numero_control = $conexion->real_escape_string(trim($_POST['numero_control'] ?? ''));
            $dirigido_a     = $conexion->real_escape_string(trim($_POST['dirigido_a'] ?? ''));
            $materia        = $conexion->real_escape_string(trim($_POST['materia'] ?? ''));
            $semestre       = intval($_POST['semestre'] ?? 0);
            $periodo        = $conexion->real_escape_string(trim($_POST['periodo'] ?? ''));
            $fecha_inicio   = $conexion->real_escape_string($_POST['fecha_inicio'] ?? '');
            $fecha_final    = $conexion->real_escape_string($_POST['fecha_final'] ?? '');

            if ($id <= 0 || empty($nombre) || empty($numero_control) || empty($materia) || $semestre === 0) {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
                exit();
            }

            // Preparación del UPDATE con la estructura correcta de campos
            $stmt = $conexion->prepare("UPDATE solicitudes_cartas_presentacion SET nombre = ?, numero_control = ?, dirigido_a = ?, materia = ?, semestre = ?, periodo = ?, fecha_inicio = ?, fecha_final = ? WHERE id = ?");
            $stmt->bind_param("ssssisssi", $nombre, $numero_control, $dirigido_a, $materia, $semestre, $periodo, $fecha_inicio, $fecha_final, $id);
            
            if ($stmt->execute()) {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=actualizado");
            } else {
                header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
            }
            $stmt->close();
            exit();
        } catch (Exception $e) {
            header("Location: solicitudes_cartas_presentacion.php?mensaje=error");
            exit();
        }
    }
}

// Redirección de seguridad si acceden directamente al script sin POST
header("Location: solicitudes_cartas_presentacion.php");
exit();
?>