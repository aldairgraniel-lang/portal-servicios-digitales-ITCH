<?php
// 1. Protección: Solo admins pueden ejecutar este script.
// Esto verifica el rol antes de realizar cualquier operación de borrado.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// Verificar si la petición es POST y si la acción es eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    
    // Obtener y sanitizar el ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Primero obtenemos el archivo asociado al registro
        $stmt = $conexion->prepare("SELECT archivo FROM registro_ingles WHERE id = ?");
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

        // Preparar la consulta para eliminar el registro
        $stmt = $conexion->prepare("DELETE FROM registro_ingles WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Éxito: Redirigir de vuelta con mensaje de éxito
            header("Location: solicitudes_ingles_constancias.php?mensaje=eliminado");
        } else {
            // Error: Redirigir con mensaje de error
            header("Location: solicitudes_ingles_constancias.php?mensaje=error");
        }
        $stmt->close();
    } else {
        header("Location: solicitudes_ingles_constancias.php?mensaje=error");
    }
    exit();
}
?>
