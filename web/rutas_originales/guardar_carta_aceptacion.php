<?php
include('conexion.php');

// Función para respuestas visuales (SweetAlert2)
function responder($icon, $title, $text, $redir = null) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>body { background-color: #0b0e14; font-family: sans-serif; }</style>
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
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.location.href = '" . ($redir ?? 'javascript:history.back()') . "';
            });
        </script>
    </body>
    </html>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recibir datos con seguridad
    $numero_control = trim($_POST['n_control'] ?? '');
    $tipo_tramite   = trim($_POST['tipo_tramite'] ?? '');
    $archivo        = trim($_POST['nombre_archivo_previo'] ?? '');

    // 2. Validación de campos obligatorios básicos
    if (empty($numero_control) || empty($tipo_tramite)) {
        responder('error', 'Campos incompletos', 'Por favor, asegúrate de ingresar tu número de control y seleccionar un trámite.');
    }

    // 3. Lógica condicional: Validar archivo solo si es necesario
    $tramite_limpio = strtolower($tipo_tramite);
    $necesita_archivo = ($tramite_limpio === "servicio social" || $tramite_limpio === "residencia profesional");

    if ($necesita_archivo && empty($archivo)) {
        responder('warning', 'Archivo requerido', 'Para este trámite, el nombre del archivo es obligatorio.');
    }

    // Si el trámite no requiere archivo, guardamos un valor por defecto o dejamos vacío
    $archivo_final = $necesita_archivo ? $archivo : "NO APLICA";

    // 4. Verificar duplicados (Seguridad)
    $stmt_check = $conexion->prepare("SELECT id FROM solicitudes_cartas_aceptacion WHERE numero_control = ?");
    $stmt_check->bind_param("s", $numero_control);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        responder('info', 'Ya registrado', 'Este número de control ya cuenta con una solicitud activa.');
    }
    $stmt_check->close();

    // 5. Inserción en Base de Datos
    $stmt = $conexion->prepare("INSERT INTO solicitudes_cartas_aceptacion (numero_control, tipo_tramite, archivo_pdf) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $numero_control, $tipo_tramite, $archivo_final);

    if ($stmt->execute()) {
        responder('success', '¡Registro Exitoso!', 'Tu solicitud se ha enviado correctamente.', 'index.php');
    } else {
        responder('error', 'Error en BD', 'No se pudo guardar la información, intenta de nuevo.');
    }
    $stmt->close();
}

$conexion->close();
?>