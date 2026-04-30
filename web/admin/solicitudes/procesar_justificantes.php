<?php
// 1. Protección: Solo admins pueden ejecutar este script.
// Esto verifica el rol antes de realizar cualquier operación de borrado.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Primero obtenemos el archivo asociado al registro
        $stmt = $conexion->prepare("SELECT archivo FROM justificantes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($archivo);
        $stmt->fetch();
        $stmt->close();

        // Si existe archivo y está en el servidor, lo eliminamos
        if ($archivo) {
            // Caso 1: si en la BD guardas solo el nombre del archivo
            $ruta = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $archivo;
            if (file_exists($ruta)) {
                unlink($ruta);
            }

            // Caso 2: si en la BD guardas la ruta completa
            // if (file_exists($archivo)) { unlink($archivo); }
        }

        // Ahora eliminamos el registro de la base de datos
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
?>
