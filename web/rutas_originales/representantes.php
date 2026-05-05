<?php
include('includes/header.php');
include('conexion.php');

$mensaje_swal = ""; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $n_control = trim($_POST['numero_control']);

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
    }
}
?>

<link rel="stylesheet" href="registro-verano.css">

<div class="reg-rep__wrapper">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
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
                                    <li>Usa <strong>Mayúsculas</strong> al inicio de cada palabra.</li>
                                </ul>
                            </div>

                            <form method="POST" id="formRepresentante" class="rep-form">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-uppercase opacity-75">Número de Control</label >
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-0 text-white opacity-50"><i class="bi bi-hash"></i></span>
                                        <input type="text" name="numero_control" class="form-control rep-input" 
                                               placeholder="Ej. 19390015" required maxlength="8">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-uppercase opacity-75">Nombre Completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-0 text-white opacity-50"><i class="bi bi-person-fill"></i></span>
                                        <input type="text" name="nombre" class="form-control rep-input" 
                                               placeholder="Apellidos Nombre(s)"
                                               pattern="([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+[\s]*)+" 
                                               title="Inicia cada palabra con Mayúscula" required>
                                    </div>
                                    <div class="form-text text-white-50 small mt-2">Debe coincidir exactamente con tu credencial.</div>
                                </div>

                                <div class="d-grid gap-3">
                                    <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm py-3">
                                        <i class="bi bi-check-circle-fill me-2"></i>Confirmar Registro
                                    </button>
                                    <a href="preregistro.php" class="btn btn-link text-white text-decoration-none small opacity-75">
                                        <i class="bi bi-arrow-left me-1"></i> Volver al Formulario Alumno
                                    </a>
                                </div>
                            </form>
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
            title: '¡Registro Exitoso!',
            text: 'Ya puedes seleccionarte como representante.',
            icon: 'success',
            confirmButtonColor: '#198754'
        }).then(() => { window.location = 'preregistro.php'; });
    } else if (estado === "duplicado") {
        Swal.fire({
            title: 'Atención',
            text: 'Este número de control ya está registrado.',
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    } else if (estado === "error") {
        Swal.fire({ title: 'Error', text: 'No se pudo procesar el registro.', icon: 'error' });
    }
});
</script>