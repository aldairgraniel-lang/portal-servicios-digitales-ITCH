<?php
// 1. Protección: Solo administradores pueden pasar de aquí.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Luego incluyes la conexión
include(__DIR__ . "/../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $numero_control = isset($_POST['numero_control']) ? trim($_POST['numero_control']) : '';
    $tipo_tramite = isset($_POST['tipo_tramite']) ? trim($_POST['tipo_tramite']) : '';

    if ($accion === 'guardar') {
        $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_aceptacion (numero_control, tipo_tramite) VALUES (?, ?)");
        $stmt->bind_param("ss", $numero_control, $tipo_tramite);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=guardado");
            exit();
        } else {
            $stmt->close();
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
            exit();
        }
    } elseif ($accion === 'actualizar') {
        $stmt = $conexion->prepare("UPDATE solicitudes_cartas_aceptacion SET numero_control = ?, tipo_tramite = ? WHERE id = ?");
        $stmt->bind_param("ssi", $numero_control, $tipo_tramite, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=actualizado");
            exit();
        } else {
            $stmt->close();
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
            exit();
        }
    } elseif ($accion === 'eliminar') {
        // 1. Obtener el nombre del archivo antes de borrar el registro
        $stmt_select = $conexion->prepare("SELECT archivo_pdf FROM solicitudes_cartas_aceptacion WHERE id = ?");
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $resultado = $stmt_select->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt_select->close();

        if ($fila && !empty($fila['archivo_pdf'])) {
            $archivo = $fila['archivo_pdf'];
            $ruta_archivo = __DIR__ . '/../../uploads/' . $archivo; 

            // 2. Eliminar el archivo físico del servidor si existe
            if (file_exists($ruta_archivo)) {
                unlink($ruta_archivo);
            }
        }

        // 3. Eliminar el registro en la base de datos
        $stmt_delete = $conexion->prepare("DELETE FROM solicitudes_cartas_aceptacion WHERE id = ?");
        $stmt_delete->bind_param("i", $id);

        if ($stmt_delete->execute()) {
            $stmt_delete->close();
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=eliminado");
            exit();
        } else {
            $stmt_delete->close();
            header("Location: solicitudes_cartas_aceptacion.php?mensaje=error");
            exit();
        }
    }
}
?>