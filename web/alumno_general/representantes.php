<?php
include('../includes/header.php'); 
include('conexion.php');

// 1. Verificación de estado del registro para sincronizar con el portal
$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_abierto'");
$resultado_estado = $estado_query->fetch_assoc();
$estado = $resultado_estado['valor'] ?? '0';

$mensaje_swal = ""; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Seguridad: Si el registro está cerrado, no permitir insertar datos
    if ($estado !== '1') {
        $mensaje_swal = "cerrado";
    } else {
        // 2. Recibir y limpiar datos, asegurándonos de que existan para evitar errores
        $nombre    = trim(preg_replace('/\s+/', ' ', $_POST['nombre'] ?? ''));
        $n_control = trim($_POST['numero_control'] ?? '');

        // 3. Validación en servidor
        if (empty($nombre) || empty($n_control)) {
            $mensaje_swal = "error";
        } elseif (!preg_match('/^[A-Za-z0-9]{8,10}$/', $n_control)) {
            $mensaje_swal = "error";
        } else {
            // 4. Verificar duplicados en la base de datos
            $check = $conexion->prepare("SELECT id FROM representantes WHERE numero_control = ?");
            $check->bind_param("s", $n_control);
            $check->execute();
            $resultado = $check->get_result();

            if ($resultado->num_rows > 0) {
                $mensaje_swal = "duplicado";
            } else {
                $stmt = $conexion->prepare("INSERT INTO representantes (nombre, numero_control) VALUES (?, ?)");
                $stmt->bind_param("ss", $nombre, $n_control);
                
                if ($stmt->execute()) {
                    $mensaje_swal = "exito";
                } else {
                    $mensaje_swal = "error";
                }
                $stmt->close();
            }
            $check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Representante</title>
    <link href="../css/estilos.css" rel="stylesheet">
    <style>
        /* Estilo global para clonar el radio exacto de los botones de SweetAlert2 de tu captura */
        .swal2-confirm { border-radius: 6px !important; padding: 10px 24px !important; }
    </style>
</head>
<body class="bg-dark text-white">

<div class="reg-rep__wrapper">
    <div class="container">
        <div class="row justify-content-center align-items-center pt-0 pb-3" style="margin-top: -45px; min-height: 90vh;">
            <div class="col-12 col-md-8 col-lg-6">
                
                <div class="text-center mb-4">
                    <div class="rep-icon-circle mb-3">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h2 class="reg-verano__title">Alta de Representante</h2>
                </div>

                <div class="tarjeta-glass p-1">
                    <div class="card border-0 bg-transparent text-white">
                        <div class="card-body p-4 p-md-5">
                            
                            <div class="rep-info-banner mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                                    <span class="fw-bold small text-uppercase">Instrucciones Críticas</span>
                                </div>
                                <ul class="small mb-0 opacity-90">
                                    <li>Inicia con <strong>Apellidos</strong> (Ej: Pérez López Juan).</li>
                                    <li>Usa <strong>Mayúsculas</strong> en inicciales de tu nombre.</li>
                                </ul>
                            </div>

                            <?php if ($estado !== '1' && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                                <div class="alert alert-danger text-center fw-bold">
                                    El periodo de registro se encuentra cerrado actualmente.
                                </div>
                            <?php else: ?>
                                <form method="POST" id="formRepresentante" class="rep-form">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-uppercase opacity-75">Número de Control</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-0 text-white opacity-50"><i class="bi bi-hash"></i></span>
                                            <input type="text" name="numero_control" class="form-control rep-input" 
                                                   placeholder="Ej. 19390015" required maxlength="10" pattern="[A-Za-z0-9]{8,10}" title="Debe contener entre 8 y 10 caracteres (letras y números)">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-uppercase opacity-75">Nombre Completo</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-0 text-white opacity-50"><i class="bi bi-person-fill"></i></span>
                                            <input type="text" name="nombre" class="form-control rep-input" 
                                                   placeholder="Apellidos Nombre(s)"
                                                   pattern="([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+[\s]*)+" 
                                                   title="Inicia cada palabra con Mayúscula y solo letras" required>
                                        </div>
                                        <div class="form-text text-white-50 small mt-2">Debe coincidir exactamente con tu credencial.</div>
                                    </div>

                                    <div class="d-grid gap-3">
                                        <button type="submit" id="btnSubmit" class="btn btn-success btn-lg fw-bold shadow-sm py-3">
                                            <i class="bi bi-check-circle-fill me-2"></i>Confirmar Registro
                                        </button>
                                        <a href="preregistro.php" class="btn btn-link text-white text-decoration-none small opacity-75">
                                            <i class="bi bi-arrow-left me-1"></i> Volver al Formulario Alumno
                                        </a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const estado = "<?= $mensaje_swal ?>";
    
    if (estado === "exito") {
        Swal.fire({
            background: '#121212',
            color: '#ffffff',
            backdrop: '#0b0e14',
            title: '¡Registro Exitoso!',
            text: 'Ya puedes seleccionarte como representante.',
            icon: 'success',
            confirmButtonColor: '#198754'
        }).then(() => { window.location = 'preregistro.php'; });
        
    } else if (estado === "duplicado") {
        // AJUSTADO: Diseño idéntico al de tu captura de pantalla (Info Azul)
        Swal.fire({
            background: '#121212',
            color: '#ffffff',
            backdrop: '#0b0e14',
            title: 'Ya registrado',
            text: 'Este número de control ya cuenta con una solicitud activa.',
            icon: 'info',
            iconColor: '#3ea2f0',
            confirmButtonText: 'OK',
            confirmButtonColor: '#2b7cd3'
        }).then(() => { window.location = 'preregistro.php'; });
        
    } else if (estado === "cerrado") {
        Swal.fire({
            background: '#121212',
            color: '#ffffff',
            backdrop: '#0b0e14',
            title: 'Registro cerrado',
            text: 'El periodo de registro no está disponible.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        }).then(() => { window.location = 'preregistro.php'; });
        
    } else if (estado === "error") {
        Swal.fire({ 
            background: '#121212',
            color: '#ffffff',
            backdrop: '#0b0e14',
            title: 'Error', 
            text: 'No se pudo procesar el registro (Verifica que los campos sean válidos).', 
            icon: 'error' 
        });
    }
});
</script>
</body>
</html>