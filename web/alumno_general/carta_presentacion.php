<?php
include('../includes/header.php'); 
include('conexion.php');

// 1. VALIDACIÓN DE ESTADO
$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_presentacion_abierto'");
$resultado = $estado_query->fetch_assoc();
$estado = $resultado['valor'] ?? '0'; 

// 2. CARGA DE TRÁMITES (Se mantiene por si requieres la consulta, aunque tu tabla maneja datos directos)
$query_tramites = "SELECT nombre_tramite FROM tipos_tramite";
$resultado_tramites = mysqli_query($conexion, $query_tramites);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Carta de Presentación</title>
    <style>
        .card-registro { background: rgba(255, 255, 255, 0.98); border-top: 8px solid #9D843E; border-radius: 15px; }
        .label-tecnm { color: #1B396A; font-weight: bold; font-size: 0.82rem; letter-spacing: 0.5px; display: block; margin-bottom: 0.4rem; }
        .btn-tecnm { background: #1B396A; color: white; font-weight: bold; border-radius: 10px; transition: 0.3s; border: none; width: 100%; padding: 12px; text-decoration: none; display: inline-block; text-align: center; }
        .btn-tecnm:hover { background: #9D843E; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .indicaciones-alerta { background-color: #f8f9fa; border-left: 5px solid #1B396A; font-size: 0.85rem; color: #555; }
        .form-control:focus, .form-select:focus { border-color: #1B396A; box-shadow: 0 0 0 0.25rem rgba(27, 57, 106, 0.1); }
    </style>
</head>
<body> 

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">
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
                <div class="card-registro p-4 shadow">
                    <h3 class="text-center mb-2" style="color: #1B396A;">Trámite: Solicitud de Carta de Presentación</h3>
                    <p class="text-center text-muted small mb-4">Por favor, rellene de forma detallada todos los campos de la solicitud.</p>

                    <div class="indicaciones-alerta p-3 mb-4 shadow-sm">
                        <strong>Nota importante:</strong> Iniciar con apellidos, luego nombres y usar mayúsculas.<br>
                        <strong>Validación:</strong> El Número de Control debe ser alfanumérico y contener de 8 a 10 caracteres.
                    </div>

                    <form id="formCarta" action="guardar_carta_presentacion.php" method="POST">
                        
                        <!-- 1. Datos Personales -->
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="label-tecnm">NOMBRE COMPLETO</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Pérez López Juan" required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="label-tecnm">NÚMERO DE CONTROL</label>
                                <input type="text" 
                                       name="numero_control" 
                                       id="numero_control"
                                       class="form-control" 
                                       placeholder="Ej. 19390015" 
                                       minlength="8"
                                       maxlength="10"
                                       pattern="[a-zA-Z0-9]{8,10}"
                                       title="El número de control debe tener entre 8 y 10 caracteres alfanuméricos."
                                       style="text-transform: uppercase;"
                                       oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                                       required>
                            </div>
                        </div>

                        <!-- 2. Información Académica -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="label-tecnm">MATERIA</label>
                                <input type="text" name="materia" class="form-control" placeholder="Ej. Residencias Profesionales" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="label-tecnm">SEMESTRE</label>
                                <input type="number" name="semestre" class="form-control" min="1" max="15" placeholder="Ej. 9" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="label-tecnm">PERIODO</label>
                                <input type="text" name="periodo" class="form-control" placeholder="Ej. Ene - Jun 2026" required>
                            </div>
                        </div>

                        <!-- 3. Información del Destino y Emisor -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">DIRIGIDO A (EMPRESA / INSTITUCIÓN)</label>
                                <input type="text" name="dirigido_a" class="form-control" placeholder="Ej. C.P. Fernando Gómez - Director General de Empresa S.A." required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">DOCENTE O ASESOR</label>
                                <input type="text" name="docente_asesor" class="form-control" placeholder="Ej. Ing. Alejandro Ramírez Gómez" required>
                            </div>
                        </div>

                        <!-- 4. Fechas de Duración -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">FECHA DE INICIO</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">FECHA FINAL</label>
                                <input type="date" name="fecha_final" class="form-control" required>
                            </div>
                        </div>

                        <!-- 5. Objetivo -->
                        <div class="mb-4">
                            <label class="label-tecnm">OBJETIVO DE LA CARTA / MOTIVO DETALLADO</label>
                            <textarea name="objetivo" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Describa brevemente el motivo de la carta (Ej. Realizar el proyecto de Servicio Social enfocado en desarrollo web...)" 
                                      required></textarea>
                        </div>

                        <button type="submit" class="btn-tecnm">Finalizar y Enviar Solicitud</button>
                        
                        <div class="text-center mt-4">
                            <a href="/index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>