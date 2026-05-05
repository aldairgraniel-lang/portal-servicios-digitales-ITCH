<?php
include('conexion.php');

function alertaSweet($titulo, $mensaje, $icono, $redirect = null) {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background-color: #0b0e14; font-family: 'Segoe UI', sans-serif; }</style>
    </head>
    <body>
    <script>
        Swal.fire({
            title: '$titulo',
            text: '$mensaje',
            icon: '$icono',
            timer: 5000,
            background: '#121212',
            color: '#ffffff',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false
        }).then(() => {
            " . ($redirect ? "window.location.href = '$redirect';" : "window.history.back();") . "
        });
    </script>
    </body>
    </html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $n_control = $_POST['n_control'];
    $motivo = $_POST['motivo'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    $directorio_subida = 'uploads/justificantes/';
    if (!is_dir($directorio_subida)) {
        mkdir($directorio_subida, 0777, true);
    }

    // Obtenemos el nombre original del archivo
    $nombre_archivo = $_FILES['archivo_justificante']['name'];
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    if ($extension !== 'pdf') {
        alertaSweet('Error', 'Solo se permiten archivos en formato PDF.', 'error');
    }

    // Usamos el nombre original directamente
    // Usamos basename() por seguridad para evitar rutas de directorio maliciosas
    $ruta_final = $directorio_subida . basename($nombre_archivo);

    // Verificación de seguridad: opcionalmente podrías verificar si el archivo ya existe
    if (file_exists($ruta_final)) {
        alertaSweet('Error', 'Ya existe un archivo con ese nombre. Por favor, cámbiale el nombre al archivo antes de subirlo.', 'error');
    }

    if (move_uploaded_file($_FILES['archivo_justificante']['tmp_name'], $ruta_final)) {
        
        $stmt = $conexion->prepare("INSERT INTO justificantes (nombre, n_control, motivo, fecha_inicio, fecha_fin, archivo_ruta) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $n_control, $motivo, $fecha_inicio, $fecha_fin, $ruta_final);

        if ($stmt->execute()) {
            alertaSweet('¡Éxito!', 'La solicitud se ha enviado correctamente.', 'success', 'index.php');
        } else {
            alertaSweet('Error', 'Hubo un problema al guardar en la base de datos.', 'error');
        }
        $stmt->close();
    } else {
        alertaSweet('Error', 'No se pudo subir el archivo. Revisa los permisos de la carpeta.', 'error');
    }
} else {
    header("Location: index.php");
}
?>