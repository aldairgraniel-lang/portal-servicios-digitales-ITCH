<?php
include('../includes/header.php'); 
include('conexion.php');

// 1. VALIDACIÓN DE ESTADO
$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_presentacion_abierto'");
$resultado = $estado_query->fetch_assoc();
$estado = $resultado['valor'] ?? '0'; 

// 2. CARGA DE TRÁMITES
$query_tramites = "SELECT nombre_tramite FROM tipos_tramite";
$resultado_tramites = mysqli_query($conexion, $query_tramites);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Carta de Presentación</title>
    <link rel="stylesheet" href="../css/presentacion.css">
</head>
<body> 

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <?php if ($estado !== '1'): ?>
                    <div class="container d-flex justify-content-center align-items-center  ">
                        <div class="tarjeta-glass text-center p-5" style="max-width: 500px; border-top: 5px solid #9D843E;">
                            <div class="display-1 mb-4">⚠️</div>
                            <h2 class="titulo" style="color: #fff;">CERRADO</h2>
                            <p class="text-blanco-puro mt-3">No está disponible actualmente.</p>
                            <div class="text-center mt-4">
                                <a href="/index.php" class="boton-servicio text-decoration-none" style="display: inline-block;">VOLVER AL INICIO</a>
                            </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-registro p-4 shadow">
                    <h3 class="text-center mb-2" style="color: #1B396A;">Trámite: Solicitud de Carta de Presentación</h3>
                    <p class="text-center text-muted small mb-4">Datos personales</p>

                    <div class="indicaciones-alerta p-3 mb-4 shadow-sm">
                        <strong>Nota importante:</strong> Iniciar con apellidos, luego nombres y mayúsculas.<br>
                        <strong>Archivo:</strong> Debe ser un archivo PDF real (Máximo 5MB).<br>
                        <strong>Verificación:</strong> Revisa que tus datos coincidan con tu credencial.
                    </div>

                    <form id="formCarta" action="guardar_carta_presentacion.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NOMBRE COMPLETO</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Pérez López Juan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NÚMERO DE CONTROL</label>
                                <input type="text" name="n_control" class="form-control" placeholder="Ej. 19390015" required pattern="[0-9]{8}" maxlength="8">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="label-tecnm">TIPO DE TRÁMITE</label>
                            <select name="tipo_tramite" id="tipo_tramite" class="form-select" required onchange="actualizarRequisitos()">
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultado_tramites)) {
                                    $nombre = htmlspecialchars(trim($row['nombre_tramite']), ENT_QUOTES, 'UTF-8');
                                    echo '<option value="' . $nombre . '">' . $nombre . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div id="seccion_archivos" class="mb-4" style="display:none; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 2px dashed #dee2e6;">
                            <label id="label_archivo" class="text-primary fw-bold mb-2 d-block"></label>
                            <input type="file" name="documento_pdf" class="form-control bg-white" accept="application/pdf">
                            <small class="text-muted d-block mt-1">Asegúrate de que el archivo esté en formato PDF.</small>
                        </div>

                        <button type="submit" class="btn-tecnm">Finalizar y Enviar</button>
                        
                        <div class="text-center mt-4">
                            <a href="/index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function actualizarRequisitos() {
    const select = document.getElementById('tipo_tramite');
    const seccion = document.getElementById('seccion_archivos');
    const label = document.getElementById('label_archivo');
    const inputArchivo = document.querySelector('input[name="documento_pdf"]');
    const valor = select.value.trim().toLowerCase();

    // Mostrar sección de carga para trámites específicos
    if (valor === "servicio social" || valor === "residencia profesional") {
        seccion.style.display = "block";
        inputArchivo.required = true;
        label.innerText = "Sube tu documento para " + valor + " (Formato PDF):";
    } else {
        seccion.style.display = "none";
        inputArchivo.required = false;
        inputArchivo.value = ""; // Limpiar selección si cambia de trámite
    }
}
</script>
</body>
</html>