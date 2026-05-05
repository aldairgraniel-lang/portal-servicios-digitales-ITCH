<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $stmt = $conexion->prepare("DELETE FROM VERANO WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                header("Location: preregistro.php?mensaje=eliminado");
            } else {
                header("Location: preregistro.php?mensaje=error");
            }
            $stmt->close();
        } else {
            header("Location: preregistro.php?mensaje=error");
        }
        exit();
    } 
    
    elseif ($accion === 'guardar') {
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $apellidos = $conexion->real_escape_string($_POST['apellidos']);
        $numero_celular = $conexion->real_escape_string($_POST['numero_celular']);
        $numero_control = $conexion->real_escape_string($_POST['numero_control']);
        $carrera = $conexion->real_escape_string($_POST['carrera']);
        $semestre = intval($_POST['semestre']);
        $curso_interes = $conexion->real_escape_string($_POST['curso_interes']);
        $representante_1 = $conexion->real_escape_string($_POST['representante_1']);
        $representante_2 = $conexion->real_escape_string($_POST['representante_2']);

        $stmt = $conexion->prepare("INSERT INTO VERANO (nombre, apellidos, numero_celular, numero_control, carrera, semestre, curso_interes, representante_1, representante_2, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssisss", $nombre, $apellidos, $numero_celular, $numero_control, $carrera, $semestre, $curso_interes, $representante_1, $representante_2);
        
        if ($stmt->execute()) {
            header("Location: preregistro.php?mensaje=guardado");
        } else {
            header("Location: preregistro.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }

    elseif ($accion === 'actualizar') {
        $id = intval($_POST['id']);
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $apellidos = $conexion->real_escape_string($_POST['apellidos']);
        $numero_celular = $conexion->real_escape_string($_POST['numero_celular']);
        $numero_control = $conexion->real_escape_string($_POST['numero_control']);
        $carrera = $conexion->real_escape_string($_POST['carrera']);
        $semestre = intval($_POST['semestre']);
        $curso_interes = $conexion->real_escape_string($_POST['curso_interes']);
        $representante_1 = $conexion->real_escape_string($_POST['representante_1']);
        $representante_2 = $conexion->real_escape_string($_POST['representante_2']);

        $stmt = $conexion->prepare("UPDATE VERANO SET nombre = ?, apellidos = ?, numero_celular = ?, numero_control = ?, carrera = ?, semestre = ?, curso_interes = ?, representante_1 = ?, representante_2 = ? WHERE id = ?");
        $stmt->bind_param("sssssisssi", $nombre, $apellidos, $numero_celular, $numero_control, $carrera, $semestre, $curso_interes, $representante_1, $representante_2, $id);

        if ($stmt->execute()) {
            header("Location: preregistro.php?mensaje=actualizado");
        } else {
            header("Location: preregistro.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }
}
?>