<?php
include('conexion.php');

$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_ingles_abierto'");
$resultado = $estado_query->fetch_assoc();
$estado = $resultado['valor'] ?? '0';

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: "Segoe UI", sans-serif; background-color: #0b0e14; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .swal2-confirm { border-radius: 6px !important; padding: 10px 24px !important; }
    </style>
</head>
<body>';

if ($estado !== '1') {
    echo "
    <script>
        Swal.fire({
            title: '¡Lo sentimos!',
            text: 'El periodo de registro acaba de finalizar. No se pudieron guardar tus datos.',
            icon: 'warning',
            background: '#121212',
            color: '#ffffff',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Volver al inicio'
        }).then(() => {
            window.location.href = 'index.php';
        });
    </script>
    </body></html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Limpieza básica de datos entrantes
    $nombre         = trim($_POST['nombre'] ?? '');
    $numero_control = trim($_POST['numero_control'] ?? '');
    $carrera        = trim($_POST['carrera'] ?? '');
    $periodo        = trim($_POST['periodo'] ?? '');
    $tipo           = trim($_POST['tipo_alumno'] ?? '');
    $semestre       = trim($_POST['semestre'] ?? '');

    if (empty($nombre) || empty($numero_control) || empty($carrera) || empty($periodo) || empty($tipo)) {
        echo "
        <script>
            Swal.fire({
                title: 'Campos incompletos',
                text: 'Por favor, llena todos los campos obligatorios.',
                icon: 'error',
                background: '#121212',
                color: '#ffffff',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.history.back();
            });
        </script></body></html>";
        exit;
    }

    // =========================================================================
    // 2. CONTROL DE DUPLICADOS: Verificar si el número de control ya existe
    // =========================================================================
    $check_query = "SELECT numero_control FROM registro_ingles WHERE numero_control = ? LIMIT 1";
    $stmt_check = $conexion->prepare($check_query);
    
    if ($stmt_check) {
        $stmt_check->bind_param("s", $numero_control);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $stmt_check->close();
            $conexion->close();
            
            // Alerta adaptada para verse EXACTAMENTE igual a tu imagen
            echo "
            <script>
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
            </script></body></html>";
            exit; // Rompe el script para evitar que se inserte
        }
        $stmt_check->close();
    }
    // =========================================================================

    // 3. Preparar el valor del semestre (si es vacío o 'NULL', guardar como NULL real en MySQL)
    $valor_semestre = ($semestre === '' || $semestre === 'NULL') ? null : $semestre;
    
    // 4. INSERCIÓN BLINDADA CON SENTENCIAS PREPARADAS
    $sql = "INSERT INTO registro_ingles (nombre, numero_control, carrera, periodo, tipo_alumno, semestre) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssss", $nombre, $numero_control, $carrera, $periodo, $tipo, $valor_semestre);

        if ($stmt->execute()) {
            echo "
            <script>
                Swal.fire({
                    title: '¡Registro Exitoso!',
                    text: 'Gracias $nombre. Tu información ha sido guardada correctamente.',
                    icon: 'success',
                    timer: 5000,
                    timerProgressBar: true,
                    background: '#121212',
                    color: '#ffffff',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ir al inicio ahora',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = '/index.php';
                });
            </script>";
        } else {
            echo "
            <script>
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'No se pudo guardar: " . addslashes($stmt->error) . "',
                    icon: 'error',
                    background: '#121212',
                    color: '#ffffff',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.history.back();
                });
            </script>";
        }
        $stmt->close();
    }
}

$conexion->close();
echo '</body></html>';
?>