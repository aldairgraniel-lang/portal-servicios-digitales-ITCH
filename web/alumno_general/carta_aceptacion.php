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

<link rel="stylesheet" href="../css/aceptacion.css">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <?php if ($estado !== '1'): ?>
                <div class="container d-flex justify-content-center align-items-center">
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
                <div class="card-registro">
                    <h3 class="text-center mb-2" style="color: #1B396A; font-weight: 800;">Solicitud de Carta de Aceptación</h3>
                    <p class="text-center text-muted small mb-4">Ingrese los datos correspondientes para su trámite</p>

                    <div class="indicaciones-alerta p-3 mb-4 shadow-sm">
                        <strong>Nota importante:</strong> iniciar con apellidos, luego nombres y mayúsculas.<br>
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i> <strong>Formato:</strong> PRESENTACION_N_CONTROL.pdf 
                    </div>

                    <form id="formCarta" action="guardar_carta_aceptacion.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NOMBRE COMPLETO</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Pérez López Juan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NÚMERO DE CONTROL</label>
                                <!-- Se agrega ID para el Script -->
                                <input type="text" id="input_control" name="n_control" class="form-control" placeholder="Ej. 19390015" required pattern="[0-9]{8}" maxlength="8">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="label-tecnm">TIPO DE TRÁMITE</label>
                            <select name="tipo_tramite" class="form-select" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultado_tramites)) {
                                    $nombre_t = htmlspecialchars(trim($row['nombre_tramite']), ENT_QUOTES, 'UTF-8');
                                    echo '<option value="' . $nombre_t . '">' . $nombre_t . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="label-tecnm">NOMBRE DEL ARCHIVO PDF</label>
                            <!-- Se agrega ID y readonly para que sea automático y no editable -->
                            <input type="text" id="input_archivo" name="nombre_archivo_previo" class="form-control" placeholder="Esperando número de control..." required readonly>
                            <small class="text-muted">El nombre se genera automáticamente con su número de control.</small>
                        </div>

                        <button type="submit" class="btn-tecnm w-100">Finalizar y Enviar</button>
                        
                        <div class="text-center mt-4">
                            <a href="/index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SCRIPT PARA AUTOCOMPLETADO -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputControl = document.getElementById('input_control');
    const inputArchivo = document.getElementById('input_archivo');

    if (inputControl && inputArchivo) {
        inputControl.addEventListener('input', function() {
            // Limpiamos espacios y obtenemos el valor
            let valor = this.value.trim();
            
            if (valor.length > 0) {
                // Seteamos el valor con el formato requerido
                inputArchivo.value = `PRESENTACION_${valor}.pdf`;
            } else {
                // Si el campo está vacío, limpiamos el nombre del archivo
                inputArchivo.value = "";
            }
        });
    }
});
</script>

<?php // Opcional, dependiendo de tu estructura ?>