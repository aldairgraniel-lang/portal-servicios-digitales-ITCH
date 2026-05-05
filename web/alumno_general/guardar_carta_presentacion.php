<?php
include('conexion.php');

// Configuración de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Función para alertas estéticas
function mostrarAlerta($icon, $title, $text, $redir = null) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>
            body { background-color: #0b0e14; font-family: 'Segoe UI', sans-serif; }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text',
                timer: 5000,
                background: '#121212',
                color: '#ffffff',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
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
    // 1. Nombres ajustados según tu formulario HTML
    $nombre = trim($_POST['nombre'] ?? '');
    $n_control = trim($_POST['n_control'] ?? '');
    $tipo = trim($_POST['tipo_tramite'] ?? '');

    if (empty($nombre) || empty($n_control) || empty($tipo)) {
        mostrarAlerta('error', 'Campos incompletos', 'Por favor, llena todos los campos del formulario.');
    }

    // 2. Lógica adaptativa para el archivo según el tipo de trámite
    $tipo_lower = strtolower($tipo);
    $requiere_archivo = ($tipo_lower === 'servicio social' || $tipo_lower === 'residencia profesional');
    
    $nombre_archivo_final = null; // Se inicializa como nulo

    if ($requiere_archivo) {
        if (isset($_FILES['documento_pdf']) && $_FILES['documento_pdf']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['documento_pdf'];
            $tipo_archivo = $archivo['type'];

            if ($tipo_archivo !== 'application/pdf') {
                mostrarAlerta('error', 'Formato no válido', 'Solo se permiten archivos PDF.');
            }

            $directorio = "../uploads/cartas/";
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            // Generamos el nombre del archivo
            $nombre_archivo_final = $n_control . "_" . time() . ".pdf";
            $ruta_destino = $directorio . $nombre_archivo_final;

            if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                mostrarAlerta('error', 'Error de guardado', 'No se pudo mover el archivo al servidor.');
            }
        } else {
            mostrarAlerta('warning', 'Falta archivo', 'Por favor, adjunta tu documento en PDF.');
        }
    }

    // 3. Insertar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_presentacion 
        (nombre_estudiante, numero_control, tipo_tramite, archivo_pdf) 
        VALUES (?, ?, ?, ?)");

    $stmt->bind_param("ssss", $nombre, $n_control, $tipo, $nombre_archivo_final);

    if ($stmt->execute()) {
        mostrarAlerta('success', '¡Enviado!', 'Tu solicitud se ha registrado correctamente.', '/index.php');
    } else {
        mostrarAlerta('error', 'Error de base de datos', 'No se pudo registrar en la base de datos.');
    }
    
    $stmt->close();
}
?>