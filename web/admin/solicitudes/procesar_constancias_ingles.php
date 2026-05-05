<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $stmt = $conexion->prepare("SELECT archivo FROM registro_ingles WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($archivo);
            $stmt->fetch();
            $stmt->close();

            if ($archivo) {
                $ruta = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $archivo;
                if (file_exists($ruta)) {
                    unlink($ruta);
                }
            }

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
        $archivo_nombre = '';

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['archivo']['tmp_name'];
            $fileName = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $archivo_nombre = $newFileName;
            }
        }

        $stmt = $conexion->prepare("INSERT INTO registro_ingles (nombre, numero_control, carrera, periodo, archivo, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $nombre, $n_control, $carrera, $periodo, $archivo_nombre);
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

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['archivo']['tmp_name'];
            $fileName = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $archivo_nombre = $newFileName;
                $stmt = $conexion->prepare("UPDATE registro_ingles SET nombre = ?, numero_control = ?, carrera = ?, periodo = ?, archivo = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $nombre, $n_control, $carrera, $periodo, $archivo_nombre, $id);
            }
        } else {
            $stmt = $conexion->prepare("UPDATE registro_ingles SET nombre = ?, numero_control = ?, carrera = ?, periodo = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $n_control, $carrera, $periodo, $id);
        }

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