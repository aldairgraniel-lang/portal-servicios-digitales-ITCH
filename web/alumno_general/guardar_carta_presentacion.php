<?php
include('conexion.php');

// Configuración de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Función para alertas estéticas (SweetAlert2)
function mostrarAlerta($icon, $title, $text, $redir = null, $iconColor = null, $btnText = 'Aceptar') {
    $customIconColor = $iconColor ? "iconColor: '$iconColor'," : "";
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>
            body { background-color: #0b0e14; font-family: 'Segoe UI', sans-serif; }
            .swal2-confirm { border-radius: 6px !important; padding: 10px 24px !important; }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: '$icon',
                $customIconColor
                title: '$title',
                text: '$text',
                timer: 5000,
                background: '#121212',
                color: '#ffffff',
                confirmButtonColor: '#2b7cd3',
                confirmButtonText: '$btnText'
            }).then((result) => {";
    
    if ($redir !== null) {
        echo "window.location.href = '$redir';";
    } else {
        echo "window.history.back();";
    }

    echo "});
        </script>
    </body>
    </html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Captura y limpieza de datos según el nuevo formulario
    $nombre         = trim($_POST['nombre'] ?? '');
    $n_control      = trim($_POST['n_control'] ?? '');
    $dirigido_a     = trim($_POST['dirigido_a'] ?? '');
    $docente_asesor = trim($_POST['docente_asesor'] ?? ''); // <--- CAPTURA DOCENTE / ASESOR
    $objetivo       = trim($_POST['objetivo'] ?? '');       // <--- CAPTURA OBJETIVO
    $materia        = trim($_POST['materia'] ?? '');
    $semestre       = trim($_POST['semestre'] ?? '');
    $periodo        = trim($_POST['periodo'] ?? '');
    $fecha_inicio   = trim($_POST['fecha_inicio'] ?? '');
    $fecha_final    = trim($_POST['fecha_final'] ?? '');

    // Validación de campos obligatorios (se añaden los nuevos campos a la verificación)
    if (empty($nombre) || empty($n_control) || empty($dirigido_a) || empty($docente_asesor) || empty($objetivo) || empty($materia) || empty($semestre) || empty($periodo) || empty($fecha_inicio) || empty($fecha_final)) {
        mostrarAlerta('error', 'Campos incompletos', 'Por favor, llena todos los campos del formulario.');
    }

    // =========================================================================
    // CONTROL DE DUPLICADOS
    // =========================================================================
    $check_query = "SELECT numero_control FROM solicitudes_cartas_presentacion WHERE numero_control = ? LIMIT 1";
    $stmt_check = $conexion->prepare($check_query);
    
    if ($stmt_check) {
        $stmt_check->bind_param("s", $n_control);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $stmt_check->close();
            $conexion->close();
            
            mostrarAlerta('info', 'Ya registrado', 'Este número de control ya cuenta con una solicitud activa.', null, '#3ea2f0', 'OK');
        }
        $stmt_check->close();
    } else {
        die("Error al verificar duplicados: " . $conexion->error);
    }
    // =========================================================================

    // 2. Insertar en la base de datos incluyendo las nuevas columnas docente_asesor y objetivo
    $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_presentacion 
        (nombre, numero_control, dirigido_a, docente_asesor, objetivo, materia, semestre, periodo, fecha_inicio, fecha_final) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // El parámetro cambia a "ssssssssss" (10 strings para las 10 columnas en total)
    $stmt->bind_param("ssssssssss", $nombre, $n_control, $dirigido_a, $docente_asesor, $objetivo, $materia, $semestre, $periodo, $fecha_inicio, $fecha_final);

    if ($stmt->execute()) {
        mostrarAlerta('success', '¡Enviado!', 'Tu solicitud se ha registrado correctamente.', '/index.php');
    } else {
        mostrarAlerta('error', 'Error de base de datos', 'No se pudo registrar en la base de datos.');
    }
    
    $stmt->close();
    $conexion->close();
}
?>