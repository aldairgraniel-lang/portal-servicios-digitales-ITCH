<?php
include('includes/header.php'); 
include('conexion.php');

// Verificación del estado del servicio
$stmt = $conexion->prepare("SELECT valor FROM configuracion WHERE clave = 'registro_justificantes_abierto'");
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();

// Usamos una variable booleana clara
$sistema_abierto = ($resultado && $resultado['valor'] == '1');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Justificante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card-registro { background: #ffffff; border-top: 8px solid #9D843E; border-radius: 15px; }
        .label-tecnm { color: #1B396A; font-weight: 700; font-size: 0.85rem; margin-bottom: 5px; display: block; }
        .swal-like-box { background: #f0f7ff; border: 1px solid #cce3fd; color: #4b6a91; padding: 1.25em; border-radius: 10px; display: flex; align-items: center; gap: 15px; font-size: 0.95rem; }
        .btn-tecnm { background: #1B396A; color: white; font-weight: bold; border-radius: 10px; transition: 0.3s; border: none; width: 100%; padding: 10px; text-decoration: none; display: inline-block; }
        .btn-tecnm:hover { background: #9D843E; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        
        /* Estilos para estado cerrado */

    </style>
</head>
<body> 

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <?php if ($sistema_abierto): ?>
                <div class="card-registro p-4 p-md-5 shadow-sm">
                    <h3 class="text-center mb-2" style="color: #1B396A; font-weight: 800;">Solicitud de Justificante</h3>
                    <p class="text-center text-muted small mb-4">Complete los campos correctamente para procesar su solicitud</p>

                    <div class="swal-like-box mb-4">
                        <i class="bi bi-info-circle-fill fs-3" style="color: #3085d6;"></i>
                        <div>
                            <strong>Nota importante:</strong> iniciar con apellidos, luego nombres y mayúsculas.<br>
                            <strong>Formato:</strong> Adjunte un documento probatorio (receta, citatorio, etc.) únicamente en PDF.<br>
                            <strong>Nombre:</strong> El archivo debe nombrarse: 19390015_Medico.pdf
                        </div>
                    </div>

                    <form id="formJustificante" action="guardar_justificante.php" method="POST" enctype="multipart/form-data">
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
                        <div class="mb-3">
                            <label class="label-tecnm">MOTIVO DE LA INASISTENCIA</label>
                            <select name="motivo" class="form-select" required>
                                <option value="" disabled selected>Seleccione el motivo...</option>
                                <option value="Medico">Motivos Médicos</option>
                                <option value="Personal">Asuntos Personales</option>
                                <option value="Academico">Evento Académico/Deportivo</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">FECHA INICIO</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="label-tecnm">FECHA FIN</label>
                                <input type="date" name="fecha_fin" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="label-tecnm">DOCUMENTO RESPALDO (PDF)</label>
                            <input type="file" name="archivo_justificante" class="form-control" accept=".pdf" required>
                        </div>
                        <button type="submit" class="btn-tecnm"> Finalizar y Enviar</button>
                        <div class="text-center mt-4">
                    <a href="index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                </div>
                    </form>
                </div>

            <?php else: ?>
                <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="tarjeta-glass text-center p-5" style="max-width: 500px; border-top: 5px solid #9D843E;">
        <div class="display-1 mb-4">⚠️</div>
        <h2 class="titulo" style="color: #fff;">CERRADO</h2>
        <p class="text-white mt-3"> no está disponible actualmente.</p>
            
            </div>
        </div>
    </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    const form = document.getElementById('formJustificante');
    if (form) {
        form.addEventListener('submit', function(e) {
            Swal.fire({
                title: '¡Enviando!',
                text: 'Estamos procesando tu solicitud...',
                icon: 'info',
                background: '#ffffff',
                color: '#9D843E',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        });
    }
</script>

</body>
</html>