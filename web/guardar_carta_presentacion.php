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
            }).then((result) => {
                if ('$redir') {
                    window.location.href = '$redir';
                } else {
                    window.history.back();
                }
            });
        </script>
    </body>
    </html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre    = trim($_POST['nombre']);
    $n_control = trim($_POST['n_control']);
    $tipo      = trim($_POST['tipo_tramite']);
    $nombre_archivo_final = NULL; // Cambiamos $ruta por esta variable

    // --- BLOQUE DE VALIDACIÓN DE DUPLICADOS ---
    $check_stmt = $conexion->prepare("SELECT id FROM solicitudes_cartas_presentacion WHERE numero_control = ?");
    $check_stmt->bind_param("s", $n_control);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        mostrarAlerta('info', 'Solicitud duplicada', 'Ya has enviado una solicitud anteriormente.', 'carta_presentacion.php');
    }
    $check_stmt->close();

    // 1. Validar archivo
    if (isset($_FILES['documento_pdf']) && $_FILES['documento_pdf']['error'] === 0) {
        $archivo = $_FILES['documento_pdf'];

        if ($archivo['size'] > 5 * 1024 * 1024) {
            mostrarAlerta('error', 'Archivo muy pesado', 'El límite es de 5MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            mostrarAlerta('error', 'Formato no válido', 'Solo se permiten archivos PDF.');
        }

        $directorio = "uploads/cartas/";
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

    // 2. Insertar en la base de datos
    $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_presentacion 
        (nombre_estudiante, numero_control, tipo_tramite, archivo_pdf) 
        VALUES (?, ?, ?, ?)");

    // IMPORTANTE: Aquí guardamos solo $nombre_archivo_final (el nombre), NO la ruta completa
    $stmt->bind_param("ssss", $nombre, $n_control, $tipo, $nombre_archivo_final);

    if ($stmt->execute()) {
        mostrarAlerta('success', '¡Enviado!', 'Tu solicitud se ha registrado correctamente. estará Pendiente de revisión.', 'index.php');
    } else {
        mostrarAlerta('error', 'Error en BD', 'No se pudo guardar la información en la base de datos.');
    }

    $stmt->close();
}

$conexion->close();
?>