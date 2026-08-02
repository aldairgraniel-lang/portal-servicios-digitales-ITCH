<?php
include('../includes/header.php'); 
include('conexion.php');

// 1. VALIDACIÓN DE ESTADO
$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_terminacion_abierto'");
$resultado = $estado_query->fetch_assoc();
$estado = $resultado['valor'] ?? '0'; 

// 2. CARGA DE TRÁMITES (Opcional si usas el select manual abajo)
$query_tramites = "SELECT nombre_tramite FROM tipos_tramite";
$resultado_tramites = mysqli_query($conexion, $query_tramites);
?>

<link rel="stylesheet" href="../css/aceptacion.css">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <?php if ($estado !== '1'): ?>
                <div class="tarjeta-glass text-center p-5" style="border-top: 5px solid #9D843E;">
                    <div class="display-1 mb-4">⚠️</div>
                    <h2 style="color: #fff;">SISTEMA CERRADO</h2>
                    <p class="text-white mt-3">El registro de terminación no está disponible.</p>
                    <a href="/index.php" class="btn btn-gold mt-4">VOLVER AL INICIO</a>
                </div>
            <?php else: ?>
                <div class="card-registro p-4 shadow">
                    <h3 class="text-center mb-2" style="color: #1B396A; font-weight: 800;">Carta de Terminación (Internos)</h3>
                    <p class="text-center text-muted small mb-4">Solo alumnos que realizaron el proceso dentro del plantel</p>
                                        <div class="indicaciones-alerta p-3 mb-4 shadow-sm">
                        <strong>Nota importante:</strong> Iniciar con apellidos, luego nombres y mayúsculas.<br>
                        <strong>SOLO INTERNOS:</strong> alumnos que realizan su proceso dentro la institución.<br>
                        <strong>Verificación:</strong> Revisa que tus datos coincidan con tu credencial.
                    </div>
                    <form id="formTerminacion" action="guardar_carta_terminacion.php" method="POST">
                        <div class="mb-3">
                            <label class="label-tecnm">NOMBRE COMPLETO</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Pérez López Juan" required>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="label-tecnm">NÚMERO DE CONTROL</label>
                                <input type="text" id="n_control" name="n_control" class="form-control" placeholder="Ej. 19390015" required pattern="[A-Za-z0-9]{8,10}" minlength="8" maxlength="10">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="label-tecnm">TIPO DE TRÁMITE</label>
                            <select name="tipo_tramite" class="form-select" required>
                                <option value="" selected disabled>Seleccione...</option>
                                <option value="Servicio Social">Servicio Social</option>
                                <option value="Residencia Profesional">Residencia Profesional</option>
                            </select>
                        </div>

                        <!--
                        <div class="mb-4">
                            <label class="label-tecnm">ARCHIVO DE ACEPTACIÓN ENTREGADO</label>
                            <input type="text" id="archivo_aceptacion" name="nombre_archivo_aceptacion" class="form-control" readonly style="background-color: #e9ecef;">
                            <small class="text-muted">Nombre generado automáticamente por el sistema.</small>
                        </div>
                            -->

                        <button type="submit" class="btn-tecnm w-100">Finalizar y enviar</button>
                        <div class="text-center mt-4">
                            <a href="/index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Footer 
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Auto-completar nombre de archivo basado en el número de control
const inputArchivo = document.getElementById('archivo_aceptacion');
if (inputArchivo) {
    document.getElementById('n_control').addEventListener('input', function(e) {
        const nControl = e.target.value;
        inputArchivo.value = nControl.length > 0 ? `ACEPTACION_${nControl}.pdf` : "";
    });
}
</script>
-->
</body>