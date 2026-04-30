<?php
// 1. Protección: Solo admins pueden ejecutar este script.
// Esto verifica el rol antes de realizar cualquier operación de borrado.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// 3. Lógica
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Primero obtenemos el archivo asociado al registro
        $stmt = $conexion->prepare("SELECT archivo FROM solicitudes_cartas_aceptacion WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($archivo);
        $stmt->fetch();
        $stmt->close();

        // Si existe archivo y está en el servidor, lo eliminamos
        if ($archivo) {
            // Si en la BD guardas solo el nombre del archivo:
            $ruta = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $archivo;
            if (file_exists($ruta)) {
                unlink($ruta);
            }

            // Si en la BD guardas la ruta completa, bastaría con:
            // if (file_exists($archivo)) { unlink($archivo); }
        }

        // Ahora eliminamos el registro de la base de datos
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
    // El exit() es correcto aquí para detener el script después del header
    exit();
}
?>
