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
    $nombre         = $conexion->real_escape_string($_POST['nombre'] ?? '');
    $numero_control = $conexion->real_escape_string($_POST['numero_control'] ?? '');
    $carrera        = $conexion->real_escape_string($_POST['carrera'] ?? '');
    $periodo        = $conexion->real_escape_string($_POST['periodo'] ?? '');
    $tipo           = $conexion->real_escape_string($_POST['tipo_alumno'] ?? '');
    $semestre       = $conexion->real_escape_string($_POST['semestre'] ?? 'NULL');

    $valor_semestre = ($semestre === "NULL") ? "NULL" : "'$semestre'";
    
    $sql = "INSERT INTO registro_ingles (nombre, numero_control, carrera, periodo, tipo_alumno, semestre) 
            VALUES ('$nombre', '$numero_control', '$carrera', '$periodo', '$tipo', $valor_semestre)";

    if ($conexion->query($sql)) {
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
                text: 'No se pudo guardar: " . addslashes($conexion->error) . "',
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
}
echo '</body></html>';
?>