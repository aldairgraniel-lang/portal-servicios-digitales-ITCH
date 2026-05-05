<?php
// 1. Protección: Solo administradores pueden pasar de aquí.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // --- ACCIÓN: ELIMINAR ---
    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id > 0) {
            $stmt = $conexion->prepare("SELECT archivo_ruta FROM justificantes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($archivo_ruta);
            $stmt->fetch();
            $stmt->close();

            if (!empty($archivo_ruta)) {
                // Construimos la ruta completa del archivo en el servidor
                $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . $archivo_ruta;
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }

            $stmt = $conexion->prepare("DELETE FROM justificantes WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                header("Location: justificantes.php?mensaje=eliminado");
            } else {
                header("Location: justificantes.php?mensaje=error");
            }
            $stmt->close();
        } else {
            header("Location: justificantes.php?mensaje=error");
        }
        exit();
    } 

    // --- ACCIÓN: GUARDAR ---
    elseif ($accion === 'guardar') {
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $n_control = $conexion->real_escape_string($_POST['n_control']);
        $motivo = $conexion->real_escape_string($_POST['motivo']);
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $archivo_ruta = '';

        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['archivo']['tmp_name'];
            $fileName = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            
            // Directorio de subida con subcarpeta
            $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/justificantes/';

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if(move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                // Almacenamos la ruta relativa para la web
                $archivo_ruta = '/uploads/justificantes/' . $newFileName;
            }
        }

        $stmt = $conexion->prepare("INSERT INTO justificantes (nombre, n_control, motivo, fecha_inicio, fecha_fin, archivo_ruta, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $nombre, $n_control, $motivo, $fecha_inicio, $fecha_fin, $archivo_ruta);
        
        if ($stmt->execute()) {
            header("Location: justificantes.php?mensaje=guardado");
        } else {
            header("Location: justificantes.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }

    // --- ACCIÓN: ACTUALIZAR ---
    elseif ($accion === 'actualizar') {
        $id = intval($_POST['id']);
        $nombre = $conexion->real_escape_string($_POST['nombre']);
        $n_control = $conexion->real_escape_string($_POST['n_control']);
        $motivo = $conexion->real_escape_string($_POST['motivo']);
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        $archivo_ruta = '';
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['archivo']['tmp_name'];
            $fileName = $_FILES['archivo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/justificantes/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if(move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                $archivo_ruta = '/uploads/justificantes/' . $newFileName;
                
                // Actualizamos incluyendo el archivo
                $stmt = $conexion->prepare("UPDATE justificantes SET nombre = ?, n_control = ?, motivo = ?, fecha_inicio = ?, fecha_fin = ?, archivo_ruta = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $nombre, $n_control, $motivo, $fecha_inicio, $fecha_fin, $archivo_ruta, $id);
            }
        } else {
            // Actualizamos sin modificar el archivo
            $stmt = $conexion->prepare("UPDATE justificantes SET nombre = ?, n_control = ?, motivo = ?, fecha_inicio = ?, fecha_fin = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $nombre, $n_control, $motivo, $fecha_inicio, $fecha_fin, $id);
        }

        if ($stmt->execute()) {
            header("Location: justificantes.php?mensaje=actualizado");
        } else {
            header("Location: justificantes.php?mensaje=error");
        }
        $stmt->close();
        exit();
    }
}
?>