<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $stmt = $conexion->prepare("SELECT archivo FROM solicitudes_cartas_aceptacion WHERE id = ?");
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

            $stmt = $conexion->prepare("DELETE FROM solicitudes_cartas_aceptacion WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                header("Location: solicitudes_cartas_aceptacion.php?mensaje=eliminado");
            } else {
                header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
            }
            $stmt->close();
        } else {
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
        }
        exit();
    } 
    
    elseif ($accion === 'guardar') {
        $numero_control = $conexion->real_escape_string($_POST['numero_control']);
        $tipo_tramite = $conexion->real_escape_string($_POST['tipo_tramite']);
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

        $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_aceptacion (numero_control, tipo_tramite, archivo, fecha_registro) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $numero_control, $tipo_tramite, $archivo_nombre);
        if ($stmt->execute()) {
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=guardado");
        } else {
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }

    elseif ($accion === 'actualizar') {
        $id = intval($_POST['id']);
        $numero_control = $conexion->real_escape_string($_POST['numero_control']);
        $tipo_tramite = $conexion->real_escape_string($_POST['tipo_tramite']);

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['archivo']['tmp_name'];
            $fileName = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $archivo_nombre = $newFileName;
                $stmt = $conexion->prepare("UPDATE solicitudes_cartas_aceptacion SET numero_control = ?, tipo_tramite = ?, archivo = ? WHERE id = ?");
                $stmt->bind_param("sssi", $numero_control, $tipo_tramite, $archivo_nombre, $id);
            }
        } else {
            $stmt = $conexion->prepare("UPDATE solicitudes_cartas_aceptacion SET numero_control = ?, tipo_tramite = ? WHERE id = ?");
            $stmt->bind_param("ssi", $numero_control, $tipo_tramite, $id);
        }

        if ($stmt->execute()) {
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=actualizado");
        } else {
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }
}
?>