<?php
include('conexion.php');

// Verificar que los datos vengan por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Limpieza de datos (Trimming)
    $nombre         = trim($_POST['nombre']);
    $n_control      = trim($_POST['n_control']);
    $tipo_tramite   = $_POST['tipo_tramite'] ?? '';
    $nombre_archivo = trim($_POST['nombre_archivo_aceptacion']);

    // 2. Validación básica de seguridad (Sin celular)
    if (empty($nombre) || empty($n_control) || empty($tipo_tramite)) {
        die("Error: Faltan datos obligatorios.");
    }

    // 3. BLINDAJE: Preparar la consulta SQL con 4 parámetros
    $query = "INSERT INTO solicitudes_cartas_terminacion (nombre, n_control, tipo_tramite, nombre_archivo_aceptacion) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($query);

    if ($stmt) {
        // "ssss" indica que los 4 parámetros son strings (cadenas de texto)
        $stmt->bind_param("ssss", $nombre, $n_control, $tipo_tramite, $nombre_archivo);

        if ($stmt->execute()) {
            // RESPUESTA DE ÉXITO CON SWEETALERT2
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <style>body { background-color: #0b0e14; font-family: sans-serif; }</style>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: '¡Registro Exitoso!',
                        text: 'Tu trámite de terminación se guardó correctamente.',
                        icon: 'success',
                        timer: 5000,
                        background: '#121212',
                        color: '#ffffff',
                        confirmButtonColor: '#3085d6'   
                    }).then(() => {
                        window.location.href='../index.php';
                    });
                }
            </script>";
        } else {
            // RESPUESTA DE ERROR (Por si el número de control ya existe o falla la BD)
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Hubo un problema',
                        text: 'No se pudo registrar: " . addslashes($stmt->error) . "',
                        icon: 'error',
                        confirmButtonColor: '#1B396A'
                    }).then(() => {
                        window.history.back();
                    });
                }
            </script>";
        }
        $stmt->close();
    }

    // Cerrar la conexión
    $conexion->close();

} else {
    // Si intentan entrar por URL directa, redirigir al formulario
    header("Location: carta_terminacion.php");
    exit();
}
?>