<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    // CASO ESPECIAL: Borrar todo no necesita un ID específico
    if ($accion === 'borrar_todo') {
        // Si quieres también eliminar todos los archivos asociados:
        $result = $conexion->query("SELECT archivo FROM avisos");
        while ($row = $result->fetch_assoc()) {
            $archivo = $row['archivo'];
            if ($archivo) {
                $ruta = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $archivo;
                if (file_exists($ruta)) {
                    unlink($ruta);
                }
            }
        }
        $conexion->query("DELETE FROM avisos");
        $_SESSION['mensaje'] = "Base de datos de avisos limpiada con éxito 🧹";
    } 
    // CASOS QUE REQUIEREN ID (Eliminar uno, Toggle, Editar)
    else if ($id > 0) {
        switch ($accion) {
            case 'eliminar':
                // Obtener archivo asociado
                $stmt = $conexion->prepare("SELECT archivo FROM avisos WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($archivo);
                $stmt->fetch();
                $stmt->close();

                // Eliminar archivo físico si existe
                if ($archivo) {
                    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $archivo;
                    if (file_exists($ruta)) {
                        unlink($ruta);
                    }
                }

                // Eliminar registro
                $stmt = $conexion->prepare("DELETE FROM avisos WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['mensaje'] = "Registro eliminado de la base de datos 🗑️";
                break;

            case 'toggle':
                $stmt = $conexion->prepare("UPDATE avisos SET activo = NOT activo WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['mensaje'] = "Estado actualizado correctamente ✅";
                break;

            case 'editar':
                $titulo = $_POST['titulo'];
                $contenido = $_POST['contenido'];
                $tipo = $_POST['tipo'];
                $stmt = $conexion->prepare("UPDATE avisos SET titulo = ?, contenido = ?, tipo = ? WHERE id = ?");
                $stmt->bind_param("sssi", $titulo, $contenido, $tipo, $id);
                $stmt->execute();
                $_SESSION['mensaje'] = "Cambios guardados con éxito 💾";
                break;
        }
    }
}

header("Location: avisos.php");
exit();
?>
