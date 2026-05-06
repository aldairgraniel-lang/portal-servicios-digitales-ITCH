<?php 
// Esto DEBE ser lo primero para procesar la sesión
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/header.php'); 
include('conexion.php');

// Obtener estados actuales de la base de datos
$estado_verano = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_ingles = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_ingles_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_presentacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_presentacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_aceptacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_aceptacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_justificantes = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_justificantes_abierto'")->fetch_assoc()['valor'] ?? '0';

$verano_abierto = ($estado_verano === '1');
$ingles_abierto = ($estado_ingles === '1');
$presentacion_abierto = ($estado_presentacion === '1');
$aceptacion_abierto = ($estado_aceptacion === '1');
$justificantes_abierto = ($estado_justificantes === '1');

$color_primario_abierto = '#04336c';
$color_aviso_abierto = '#ff7b00';
$color_cerrado = '#6c757d'; // Color gris para indicar servicio inactivo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="img/imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCH - Portal de Servicios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

<main class="container contenedor-central">
    <div class="tarjeta-glass">
        <div class="titulo text-center mb-5">Portal de Servicios Digitales</div>
        <div class="row g-4 justify-content-center">
            
            <div class="col-12 col-md-6 col-lg-4">
                <a href="alumno_general/avisos.php" class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" style="background-color: <?= $color_aviso_abierto; ?>;">
                    <span>AVISOS GENERALES.</span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $verano_abierto ? 'alumno_general/preregistro.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $verano_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$verano_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$verano_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>PREREGISTRO VERANO.</span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $ingles_abierto ? 'alumno_general/ingles_constancia.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $ingles_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$ingles_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$ingles_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>CONSTANCIA INGLES NO INCONVENIENCIA.</span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $presentacion_abierto ? 'alumno_general/carta_presentacion.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $presentacion_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$presentacion_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$presentacion_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>CARTA DE PRESENTACIÓN.</span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $aceptacion_abierto ? 'alumno_general/carta_aceptacion.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $aceptacion_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$aceptacion_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$aceptacion_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>CARTA DE ACEPTACIÓN.</span>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $justificantes_abierto ? 'alumno_general/justificante.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $justificantes_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$justificantes_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$justificantes_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>JUSTIFICANTE.</span>
                </a>
            </div>

        </div>
    </div>
</main>

<footer class="footer-pro">
    <div class="container">
        <div class="row g-5">
            <div class="col-12 col-md-4">
                <h5>Dirección</h5>
                <p>Av. Insurgentes No. 330, Esq. Andrés Quintana Roo,<br>Colonia David Gustavo Gutiérrez, Apdo. Postal 267<br><strong>C.P. 77013, Chetumal, Quintana Roo, México</strong></p>
                <h5 class="mt-4">Contacto</h5>
                <p>Email: comunicacion@itchetumal.edu.mx<br>Tel: (983) 8322330, Ext. 112</p>
            </div>
            <div class="col-12 col-md-3">
                <h5>Enlaces</h5>
                <ul class="list-unstyled">
                    <li><a href="https://www.plataformadetransparencia.org.mx/" target="_blank" rel="noopener noreferrer">Transparencia</a></li>
                    <li><a href="https://chetumal.tecnm.mx/notas/#" target="_blank" rel="noopener noreferrer">CAMPUS CHETUMAL</a></li>
                    <li><a href="https://qroo.gob.mx/seq" target="_blank" rel="noopener noreferrer">SEQ</a></li>
                    <li><a href="https://www.gob.mx/sep" target="_blank" rel="noopener noreferrer">SEP</a></li>
                    <li><a href="https://www.gob.mx/" target="_blank" rel="noopener noreferrer">GOB FED</a></li>
                    <li><a href="https://home.inai.org.mx/" target="_blank" rel="noopener noreferrer">INAI</a></li>
                </ul>
            </div>
            <div class="col-12 col-md-5">
                <div class="map-wrapper">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15132.874810762865!2d-88.3020831!3d18.5190165!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x847731bd399905be!2sTECNM%20Campus%20Chetumal!5e0!3m2!1ses-419!2smx!4v1580235946221!5m2!1ses-419!2smx" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
                </div>
            </div>
            <div class="col-12 text-center text-secondary">
                © Copyright 2026 TecNM - Todos los Derechos Reservados Aviso de Privacidad Última actualización: 23/07/2026
            </div>
        </div>
    </div>
</footer>

<div class="social-sidebar">
    <a href="javascript:void(0)" class="bg-info-btn btn" onclick="mostrarServicios()">
        <i class="fas fa-info-circle"></i>
    </a>
    <a href="https://www.facebook.com/ITChetumal" class="bg-fb" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-facebook-f"></i>
    </a>
    <a href="https://x.com/IT_Chetumal" class="bg-tw" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-twitter"></i>
    </a>
    <a href="https://www.youtube.com/channel/UChLQcazFScVKbXtHTY6Ej6A" class="bg-yt" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-youtube"></i>
    </a>
</div>

<script>
    function mostrarServicios() {
        Swal.fire({
            title: '<h3 style="color: #00264d; border-bottom: 2px solid #00aaff; padding-bottom: 10px;">Guía de Trámites</h3>',
            html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="font-size: 0.9rem; color: #555; margin-bottom: 15px;">
                        Selecciona el trámite correspondiente para iniciar tu solicitud:
                    </p>
                    
                    <div style="display: grid; gap: 15px;">
                        <a href="alumno_general/preregistro.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-edit"></i> Prerregistro Verano</div>
                            <div style="font-size: 0.8rem; color: #666;"> Pre-regístrate a los cursos de verano disponibles para regularización o adelantamiento.</div>
                        </a>

                        <a href="alumno_general/avisos.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-bullhorn"></i> Avisos Generales</div>
                            <div style="font-size: 0.8rem; color: #666;">Consulta comunicados importantes del departamento.</div>
                        </a>

                        <a href="alumno_general/carta_presentacion.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-alt"></i> Carta de Presentación</div>
                            <div style="font-size: 0.8rem; color: #666;">Genera tu carta para iniciar servicio social o residencia profesional.</div>
                        </a>
                        
                        <a href="alumno_general/carta_aceptacion.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-alt"></i> Carta de Aceptación</div>
                            <div style="font-size: 0.8rem; color: #666;">Genera tu carta de aceptación para iniciar servicio social o residencia profesional.</div>
                        </a>
                        
                        <a href="alumno_general/ingles_constancia.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-language"></i> Constancia Inglés no inconveniencia </div>
                            <div style="font-size: 0.8rem; color: #666;">Solicita tu constancia de no inconveniencia para llevar acabo el ingles externamente de la institución.</div>
                        </a>

                        <a href="alumno_general/justificante.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-calendar-times"></i> Justificante</div>
                            <div style="font-size: 0.8rem; color: #666;">Reporta tus inasistencias por motivos médicos, escolares o personales justificados.</div>
                        </a>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#00264d',
            width: '500px',
            padding: '20px'
        });
    }
</script>

</body>
</html>