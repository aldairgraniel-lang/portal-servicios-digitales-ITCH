<?php
include('conexion.php');

// Verificar que los datos vengan por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Limpieza de datos (Trimming)
    $nombre         = trim($_POST['nombre']);
    $n_control      = trim($_POST['n_control']);
    $tipo_tramite   = $_POST['tipo_tramite'] ?? '';
    $nombre_archivo = trim($_POST['nombre_archivo_aceptacion']);

    // 2. Validación básica de seguridad
    if (empty($nombre) || empty($n_control) || empty($tipo_tramite)) {
        die("Error: Faltan datos obligatorios.");
    }

    // =========================================================================
    // 3. CONTROL DE DUPLICADOS: Verificar si el número de control ya existe
    // =========================================================================
    $check_query = "SELECT n_control FROM solicitudes_cartas_terminacion WHERE n_control = ? LIMIT 1";
    $stmt_check = $conexion->prepare($check_query);
    
    if ($stmt_check) {
        $stmt_check->bind_param("s", $n_control);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        // Si el número de filas es mayor a 0, el alumno ya está registrado
        if ($stmt_check->num_rows > 0) {
            $stmt_check->close();
            $conexion->close();
            
            // Alerta de registro duplicado con diseño idéntico al de la imagen
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <style>
                body { background-color: #0b0e14; font-family: sans-serif; }
                .swal2-confirm { border-radius: 6px !important; padding: 10px 24px !important; }
            </style>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Ya registrado',
                        text: 'Este número de control ya cuenta con una solicitud activa.',
                        icon: 'info',
                        iconColor: '#3ea2f0',
                        background: '#121212',
                        color: '#ffffff',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2b7cd3'
                    }).then(() => {
                        window.history.back();
                    });
                }
            </script>";
            exit(); // Detiene por completo la ejecución para evitar el INSERT
        }
        $stmt_check->close();
    } else {
        die("Error al preparar la verificación de duplicados: " . $conexion->error);
    }
    // =========================================================================


    // 4. BLINDAJE DE INSERCIÓN: Preparar la consulta SQL con 4 parámetros
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
            // RESPUESTA DE ERROR (Falla general en la base de datos)
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Hubo un problema',
                        text: 'No se pudo registrar en el sistema: " . addslashes($stmt->error) . "',
                        icon: 'error',
                        confirmButtonColor: '#1B396A'
                    }).then(() => {
                        window.history.back();
                    });
                }
            </script>";
        }
        $stmt->close();
    } else {
        die("Error al preparar la consulta de inserción: " . $conexion->error);
    }

    // Cerrar la conexión principal
    $conexion->close();

} else {
    // Si intentan entrar de forma directa por URL, redirigir al formulario original
    header("Location: carta_terminacion.php");
    exit();
}
?>