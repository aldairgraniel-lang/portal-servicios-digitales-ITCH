<?php
include('./../includes/header.php'); 
include('conexion.php');

// 1. VALIDACIÓN DE ESTADO
$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_ingles_abierto'");
$resultado = $estado_query->fetch_assoc();
$estado = $resultado['valor'] ?? '0'; 

// 2. Carga de datos originales
$carreras = $conexion->query("SELECT nombre FROM carreras ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$periodos = $conexion->query("SELECT nombre FROM periodos ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// 3. NUEVAS CONSULTAS (Tablas dinámicas)
$tipos_alumno = $conexion->query("SELECT nombre FROM tipo_estudiante")->fetch_all(MYSQLI_ASSOC);
$semestres_db = $conexion->query("SELECT numero FROM semestres ORDER BY numero ASC")->fetch_all(MYSQLI_ASSOC);
?>
<link rel="stylesheet" href="../css/ingles.css">

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
                    <h3 class="text-center mb-2" style="color: #1B396A; font-weight: 800;">Constancia de no Inconveniencia - INGLÉS</h3>
                    <p class="text-center text-muted small mb-4">Registro de datos personales</p>

                    <div class="indicaciones-alerta p-3 mb-4">
                        <h6 class="fw-bold mb-2 text-uppercase"><i class="bi bi-info-circle-fill me-2"></i>Indicaciones:</h6>
                        <ul class="mb-0 ps-3">
                            <strong>Nota importante:</strong> iniciar con apellidos, luego nombres y mayúsculas.<br>
                            <li><strong>Datos:</strong> Selecciona correctamente tu carrera y periodo.</li>
                            <li><strong>Verificación:</strong> Revisa todo antes de enviar sin espacios de mas.</li>
                        </ul>
                    </div>
                    
                    <form action="guardar_ingles_constancia.php" method="POST">
                        <div class="mb-3">
                            <label class="label-tecnm">NOMBRE COMPLETO</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Pérez López Juan" title="Ingresa tu nombre completo" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NÚMERO DE CONTROL</label>
                                <input type="text" name="numero_control" class="form-control" placeholder="19390015" pattern="[A-Za-z0-9]{8,10}" minlength="8" maxlength="10" title="Debe contener entre 8 y 10 caracteres (letras y números)" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">PERIODO ACTUAL</label>
                                <select name="periodo" class="form-select" required>
                                    <option value="">Selecciona...</option>
                                    <?php foreach($periodos as $p): ?>
                                        <option value="<?= htmlspecialchars($p['nombre']) ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="label-tecnm">TIPO DE ESTUDIANTE</label>
                            <select name="tipo_alumno" id="tipo_alumno" class="form-select" onchange="toggleSemestre(this.value)" required>
                                <option value="">Selecciona...</option>
                                <?php foreach($tipos_alumno as $t): ?>
                                    <option value="<?= htmlspecialchars($t['nombre']) ?>"><?= htmlspecialchars($t['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3" id="div-semestre" style="display:none;">
                            <label class="label-tecnm">ESPECIFICA TU SEMESTRE</label>
                            <select name="semestre" id="semestre-input" class="form-select">
                                <option value="">Selecciona tu semestre...</option>
                                <?php foreach($semestres_db as $s): ?>
                                    <option value="<?= $s['numero'] ?>"><?= $s['numero'] ?>° Semestre</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="label-tecnm">CARRERA</label>
                            <select name="carrera" class="form-select" required>
                                <option value="">Selecciona tu carrera...</option>
                                <?php foreach($carreras as $c): ?>
                                    <option value="<?= htmlspecialchars($c['nombre']) ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn-tecnm">Finalizar y Enviar</button>
                        
                        <div class="text-center mt-4">
                            <a href="/index.php" class="text-muted text-decoration-none small">← Volver al inicio</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleSemestre(valor) {
        const div = document.getElementById('div-semestre');
        const input = document.getElementById('semestre-input');
        // Si el valor es "Cursando semestre", mostramos. (Asegúrate que coincida con el nombre en tu BD)
        if (valor === 'Cursando semestre') {
            div.style.display = 'block';
            input.required = true;
        } else {
            div.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }
</script>