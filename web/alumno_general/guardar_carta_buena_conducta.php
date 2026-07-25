<?php
// guardar_carta_buena_conducta.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Configuración de zona horaria local
date_default_timezone_set('America/Cancun');

// Conexión a la base de datos
include('../conexion.php');

// 1. VERIFICAR QUE EL ACCESO SEA POR MÉTODO POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

// 2. VERIFICAR SI EL SERVICIO SE ENCUENTRA ACTIVO EN LA CONFIGURACIÓN
$estado_buena_conducta = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_buena_conducta_abierto'")->fetch_assoc()['valor'] ?? '0';

if ($estado_buena_conducta !== '1') {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<body></body>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            background: '#121212',
            color: '#ffffff',
            title: 'Servicio deshabilitado',
            text: 'Lo sentimos, este trámite ha sido cerrado temporalmente.',
            confirmButtonColor: '#1B396A'
        }).then(() => {
            window.location.href = '../index.php';
        });
    </script>";
    exit;
}

// 3. RECIBIR Y SANITIZAR LOS DATOS DEL FORMULARIO
$nombre_completo = isset($_POST['nombre_alumno']) ? trim(mb_strtoupper($_POST['nombre_alumno'], 'UTF-8')) : '';
$numero_control  = isset($_POST['num_control']) ? trim(strtoupper($_POST['num_control'])) : '';
$carrera         = isset($_POST['carrera_alumno']) ? trim($_POST['carrera_alumno']) : '';

// 4. VALIDACIONES ESTRICTAS DE BACKEND
if (empty($nombre_completo) || empty($numero_control) || empty($carrera)) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<body></body>";
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            text: 'Por favor, rellene todos los campos marcados como obligatorios.',
            confirmButtonColor: '#1B396A'
        }).then(() => {
            window.history.back();
        });
    </script>";
    exit;
}

// Validar estructura alfanumérica de 8 a 10 caracteres (acorde al cliente)
if (!preg_match('/^[A-Z0-9]{8,10}$/', $numero_control)) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<body></body>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Estructura incorrecta',
            text: 'El número de control ingresado no cuenta con una estructura alfanumérica válida.',
            confirmButtonColor: '#1B396A'
        }).then(() => {
            window.history.back();
        });
    </script>";
    exit;
}

// 5. PREPARAR INSERCIÓN A LA BASE DE DATOS
// Se obtiene la fecha actual en formato limpio Y-m-d sin marca de tiempo
$fecha_solicitud = date('Y-m-d');

// Consulta estructurada según tu esquema de base de datos exacto
$sql = "INSERT INTO solicitudes_cartas_buena_conducta (nombre_completo, numero_control, carrera, fecha_solicitud) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $nombre_completo, $numero_control, $carrera, $fecha_solicitud);
    
    if ($stmt->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<body></body>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                background: '#121212',
                color: '#ffffff',
                title: '¡Solicitud Registrada!',
                text: 'Tu trámite de Carta de Buena Conducta se guardó exitosamente.',
                confirmButtonColor: '#1B396A'
            }).then(() => {
                window.location.href = '../index.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<body></body>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                background: '#121212',
                color: '#ffffff',
                title: 'Error de servidor',
                text: 'No se pudo procesar la solicitud en este momento. Intente más tarde.',
                confirmButtonColor: '#1B396A'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
    $stmt->close();
} else {
    die("Error crítico en el sistema de base de datos: " . $conexion->error);
}

$conexion->close();
?>  