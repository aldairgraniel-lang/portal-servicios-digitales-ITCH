<?php
include('../includes/header.php'); 
include('conexion.php');

// 1. VALIDACIÓN DE ESTADO
// Se utiliza la clave general 'registro_justificantes_abierto' para sincronizarse con el curso
$estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_justificantes_abierto'");
$resultado = $estado_query->fetch_assoc();
$estado = isset($resultado['valor']) ? intval($resultado['valor']) : 0; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Justificantes</title>
    <style>
        .card-registro { 
            background: #ffffff; 
            border-top: 8px solid #9D843E; 
            border-radius: 15px; 
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .label-tecnm { color: #1B396A; font-weight: 700; font-size: 0.85rem; margin-bottom: 5px; display: block; }
        .btn-tecnm { background: #1B396A; color: white; font-weight: bold; border-radius: 10px; transition: 0.3s; border: none; width: 100%; padding: 10px; text-decoration: none; display: inline-block; text-align: center; }
        .btn-tecnm:hover { background: #9D843E; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .indicaciones-alerta { background-color: #f8f9fa; border-left: 5px solid #1B396A; font-size: 0.85rem; color: #555; }
        .form-control:focus, .form-select:focus { border-color: #1B396A; box-shadow: 0 0 0 0.25rem rgba(27, 57, 106, 0.1); }
    </style>
</head>
<body> 

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <?php if ($estado !== 1): ?>
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
                <div class="card-registro shadow">
                    <h3 class="text-center mb-2" style="color: #1B396A;">Trámite: Solicitud de Justificantes</h3>
                    <p class="text-center text-muted small mb-4">Ingrese los datos correspondientes para el trámite</p>

                    <div class="indicaciones-alerta p-3 mb-4 shadow-sm">
                        <strong>Nota importante:</strong> Iniciar con apellidos, luego nombres y mayúsculas.<br>
                        <strong>Archivo:</strong> Debe ser un archivo PDF real (Máximo 5MB).<br>
                        <strong>Verificación:</strong> Revisa que las fechas coincidan con tu constancia médica o documento probatorio.
                    </div>

                    <form id="formJustificante" action="guardar_justificante.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NOMBRE COMPLETO</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Pérez López Juan" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">NÚMERO DE CONTROL</label>
                                <input type="text" name="n_control" class="form-control" placeholder="Ej. 19390015" required pattern="[0-9]{8,15}" maxlength="15">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="label-tecnm">MOTIVO</label>
                            <select name="motivo" class="form-select" required>
                                <option value="" selected disabled>Seleccione una opción...</option>
                                <option value="Enfermedad">Enfermedad</option>
                                <option value="Asuntos Académicos">Asuntos Académicos</option>
                                <option value="Fuerza Mayor">Fuerza Mayor</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">FECHA DE INICIO</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">FECHA DE FIN</label>
                                <input type="date" name="fecha_fin" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4" style="padding: 20px; background: #f8f9fa; border-radius: 10px; border: 2px dashed #dee2e6;">
                            <label class="text-primary fw-bold mb-2 d-block">Sustento del justificante (Formato PDF):</label>
                            <input type="file" name="archivo_justificante" class="form-control bg-white" accept="application/pdf" required>
                            <small class="text-muted d-block mt-1">Sube tu documento justificativo en formato PDF.</small>
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

</body>
</html>