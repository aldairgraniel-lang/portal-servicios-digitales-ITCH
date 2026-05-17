<?php
include("conexion.php");

$conexion->set_charset("utf8mb4");

// Función de diseño para alertas (Optimizada para personalizar el diseño tipo Info Azul)
function alertaSweet($titulo, $mensaje, $icono, $redirect = null, $isHtml = false, $iconColor = null, $btnText = 'Aceptar', $btnColor = '#3085d6') {
    $field = $isHtml ? 'html' : 'text';
    $customIconColor = $iconColor ? "iconColor: '$iconColor'," : "";
    
    echo "
    <!DOCTYPE html>
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
            title: '$titulo',
            $field: '$mensaje',
            icon: '$icono',
            $customIconColor
            timer: 5000,
            background: '#121212',
            color: '#ffffff',
            confirmButtonColor: '$btnColor',
            confirmButtonText: '$btnText',
            allowOutsideClick: false
        }).then(() => {
            " . ($redirect ? "window.location.href = '$redirect';" : "window.history.back();") . "
        });
    </script>
    </body>
    </html>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ============================
    // Capturar datos
    // ============================
    $nombre         = trim($_POST['nombre'] ?? '');
    $apellidos      = trim($_POST['apellidos'] ?? '');
    $numero_celular = trim($_POST['numero_celular'] ?? '');
    $numero_control = trim($_POST['numero_control'] ?? '');
    $carrera        = trim($_POST['carrera'] ?? '');
    $semestre       = intval($_POST['semestre'] ?? 0);
    $curso          = trim($_POST['curso'] ?? '');
    $rep1           = trim($_POST['representante_1'] ?? '');
    $rep2           = trim($_POST['representante_2'] ?? '');

    // ============================
    // Verificar registro abierto
    // ============================
    $estado = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_abierto'")->fetch_assoc()['valor'];

    if ($estado !== '1') {
        alertaSweet('Registro cerrado', 'El registro no está disponible en este momento.', 'warning', 'index.php');
    }

    // =========================================================================
    // NUEVO BLINDAJE: Evitar inscripción duplicada en el MISMO curso
    // =========================================================================
    $dup_check = $conexion->prepare("SELECT COUNT(*) as ya_inscrito FROM VERANO WHERE numero_control = ? AND curso_interes = ?");
    $dup_check->bind_param("ss", $numero_control, $curso);
    $dup_check->execute();
    $ya_inscrito = $dup_check->get_result()->fetch_assoc()['ya_inscrito'];

    if ($ya_inscrito > 0) {
        $dup_check->close();
        $conexion->close();
        // Llama a la alerta con la estética idéntica a tu imagen informativa azul
        alertaSweet('Ya registrado', 'Este número de control ya cuenta con una solicitud activa para este curso.', 'info', null, false, '#3ea2f0', 'OK', '#2b7cd3');
    }
    $dup_check->close();
    // =========================================================================

    // ============================
    // Límite de 2 registros por alumno en total
    // ============================
    $check = $conexion->prepare("SELECT COUNT(*) as total FROM VERANO WHERE numero_control = ?");
    $check->bind_param("s", $numero_control);
    $check->execute();
    $total = $check->get_result()->fetch_assoc()['total'];

    if ($total >= 2) {
        $check->close();
        $conexion->close();
        alertaSweet('Límite alcanzado', 'Ya tienes 2 registros en total. No puedes registrarte en más cursos.', 'warning');
    }
    $check->close();

    // ============================
    // Validación de campos
    // ============================
    $errores = [];

    if ($nombre === '')         $errores[] = "Nombre requerido";
    if ($apellidos === '')      $errores[] = "Apellidos requeridos";
    if ($numero_celular === '') $errores[] = "Número de celular requerido";
    if ($numero_control === '') $errores[] = "Número de control requerido";
    if ($carrera === '')        $errores[] = "Carrera requerida";
    if ($curso === '')          $errores[] = "Curso requerido";
    if ($rep1 === '')           $errores[] = "Representante 1 requerido";
    if ($rep2 === '')           $errores[] = "Representante 2 requerido";
    if ($semestre < 1 || $semestre > 12) $errores[] = "Semestre inválido";

    if (!empty($errores)) {
        alertaSweet('Error de validación', implode("<br>", $errores), 'error', null, true);
    }

    // ============================
    // Insertar en la BD
    // ============================
    $sql = "INSERT INTO VERANO 
            (nombre, apellidos, numero_celular, numero_control, carrera, semestre, curso_interes, representante_1, representante_2)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssisssss",
        $nombre, $apellidos, $numero_celular, $numero_control,
        $carrera, $semestre, $curso, $rep1, $rep2
    );

    if ($stmt->execute()) {
        alertaSweet('Registro exitoso', 'Alumno registrado: <b>' . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") . '</b>', 'success', '/index.php', true);
    } else {
        alertaSweet('Error', 'No se pudo guardar el registro. Intenta de nuevo.', 'error');
    }

    $stmt->close();
    $conexion->close();
}
?>