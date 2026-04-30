<?php
include("conexion.php");

$conexion->set_charset("utf8mb4");

// Función de diseño para alertas
function alertaSweet($titulo, $mensaje, $icono, $redirect = null, $isHtml = false) {
    $field = $isHtml ? 'html' : 'text';
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
            $field: '$mensaje',
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

    // ============================
    // Límite de 2 registros por alumno
    // ============================
    $check = $conexion->prepare("SELECT COUNT(*) as total FROM VERANO WHERE numero_control = ?");
    $check->bind_param("s", $numero_control);
    $check->execute();
    $total = $check->get_result()->fetch_assoc()['total'];

    if ($total >= 2) {
        alertaSweet('Límite alcanzado', 'Ya tienes 2 registros. No puedes registrarte en más cursos.', 'warning');
    }

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
        alertaSweet('Registro exitoso', 'Alumno registrado: <b>' . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") . '</b>', 'success', 'index.php', true);
    } else {
        alertaSweet('Error', 'No se pudo guardar el registro. Intenta de nuevo.', 'error');
    }
}
?>