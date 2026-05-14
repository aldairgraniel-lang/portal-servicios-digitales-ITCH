<?php
session_start();
// Autenticación y Conexión
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include(__DIR__ . "/../../conexion.php");

// Reporte de errores para desarrollo (en producción podrías querer desactivar el log detallado)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    // El 'id' puede venir como 'id' (desde JS de eliminación) o 'id_terminacion' (desde el modal de edición)
    $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['id_terminacion']) ? intval($_POST['id_terminacion']) : 0);

    try {
        // --- ACCIÓN: ELIMINAR ---
        if ($accion === 'eliminar' && $id > 0) {
            $stmt = $conexion->prepare("DELETE FROM solicitudes_cartas_terminacion WHERE id_terminacion = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            header("Location: solicitudes_cartas_terminacion.php?mensaje=eliminado");
            exit();
        } 
        
        // --- ACCIÓN: GUARDAR O ACTUALIZAR ---
        elseif ($accion === 'guardar' || $accion === 'actualizar') {
            $nombre = trim($_POST['nombre'] ?? '');
            $n_control = trim($_POST['n_control'] ?? '');
            $celular = trim($_POST['numero_celular'] ?? '');
            $tipo = $_POST['tipo_tramite'] ?? '';
            $archivo = "pendiente_terminacion.pdf"; // Valor por defecto

            // Validación simple
            if (empty($nombre) || empty($n_control)) {
                header("Location: solicitudes_cartas_terminacion.php?mensaje=error");
                exit();
            }

            if ($accion === 'guardar') {
                // INSERTAR
                $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_terminacion (nombre, n_control, numero_celular, tipo_tramite, nombre_archivo_aceptacion) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nombre, $n_control, $celular, $tipo, $archivo);
                $res_msg = "guardado";
            } else {
                // ACTUALIZAR (usando el ID)
                if ($id <= 0) throw new Exception("ID no válido para actualización");
                
                $stmt = $conexion->prepare("UPDATE solicitudes_cartas_terminacion SET nombre = ?, n_control = ?, numero_celular = ?, tipo_tramite = ? WHERE id_terminacion = ?");
                $stmt->bind_param("ssssi", $nombre, $n_control, $celular, $tipo, $id);
                $res_msg = "actualizado";
            }

            $stmt->execute();
            $stmt->close();
            
            header("Location: solicitudes_cartas_terminacion.php?mensaje=$res_msg");
            exit();
        }
    } catch (Exception $e) {
        // En caso de error, puedes loguear $e->getMessage() para debug
        header("Location: solicitudes_cartas_terminacion.php?mensaje=error");
        exit();
    }
} else {
    // Si alguien intenta entrar directamente al archivo sin POST
    header("Location: solicitudes_cartas_terminacion.php");
    exit();
}