<?php 
// Esto DEBE ser lo primero para procesar la sesión
if (session_status() === PHP_SESSION_NONE) session_start();
// Incluye el encabezado de la página y la conexión a la base de datos
include('includes/header.php'); 
include('conexion.php');
// Obtener estados actuales de la base de datos
// Se consultan los parámetros de configuración para verificar si cada servicio está abierto ('1') o cerrado ('0')
$estado_verano = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_ingles = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_ingles_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_presentacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_presentacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_aceptacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_aceptacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_terminacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_terminacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_justificantes = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_justificantes_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_buena_conducta = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_buena_conducta_abierto'")->fetch_assoc()['valor'] ?? '0';

// Asigna variables booleanas a cada estado para evaluar su disponibilidad
$verano_abierto = ($estado_verano === '1');
$ingles_abierto = ($estado_ingles === '1');
$presentacion_abierto = ($estado_presentacion === '1');
$aceptacion_abierto = ($estado_aceptacion === '1');
$terminacion_abierto = ($estado_terminacion === '1');
$justificantes_abierto = ($estado_justificantes === '1');
$buena_conducta_abierto = ($estado_buena_conducta === '1');

// Definición de colores base para los botones
$color_primario_abierto = '#04336c'; // Color para servicios habilitados
$color_aviso_abierto = '#ff7b00'; // Color de advertencia o avisos
$color_cerrado = '#7d6c6c'; // Color gris para indicar servicio inactivo
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

            <!-- BOTÓN DE CARTA DE ACEPTACIÓN CON ALERTA DE ALUMNO INTERNO -->
            <div class="col-12 col-md-6 col-lg-4">
                <?php if ($aceptacion_abierto): ?>
                    <!-- BOTÓN ACTIVO CON ALERTA -->
                    <a href="javascript:void(0);" 
                       onclick="confirmarInternoAceptacion()"
                       class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                       style="background-color: <?= $color_primario_abierto ?>;">
                        <span>CARTA DE ACEPTACIÓN.</span>
                    </a>
                <?php else: ?>
                    <!-- BOTÓN DESHABILITADO -->
                    <a href="javascript:void(0);" 
                       class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                       style="background-color: <?= $color_cerrado ?>; cursor: not-allowed; opacity: 0.7;"
                       title="Servicio Cerrado">
                        <span>CARTA DE ACEPTACIÓN.</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <?php if ($terminacion_abierto): ?> 
                    <!-- BOTÓN ACTIVO CON ALERTA -->
                    <a href="javascript:void(0);" 
                       onclick="confirmarInterno()"
                       class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                       style="background-color: <?= $color_primario_abierto ?>;">
                        <span>CARTA DE TERMINACIÓN.</span>
                    </a>
                <?php else: ?>
                    <!-- BOTÓN DESHABILITADO -->
                    <a href="javascript:void(0);" 
                       class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                       style="background-color: <?= $color_cerrado ?>; cursor: not-allowed; opacity: 0.7;"
                       title="Servicio Cerrado">
                        <span>CARTA DE TERMINACIÓN.</span>
                    </a>
                <?php endif; ?>
            </div>          

            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $justificantes_abierto ? 'alumno_general/justificante.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $justificantes_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$justificantes_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$justificantes_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>JUSTIFICANTE.</span>
                </a>
            </div>

            <!-- NUEVO BOTÓN: CARTA DE BUENA CONDUCTA -->
            <div class="col-12 col-md-6 col-lg-4">
                <a href="<?= $buena_conducta_abierto ? 'alumno_general/carta_buena_conducta.php' : 'javascript:void(0);'; ?>" 
                   class="btn btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center" 
                   style="background-color: <?= $buena_conducta_abierto ? $color_primario_abierto : $color_cerrado; ?>; <?= !$buena_conducta_abierto ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                   title="<?= !$buena_conducta_abierto ? 'Servicio Cerrado' : ''; ?>">
                    <span>CARTA DE BUENA CONDUCTA.</span>
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
                <p>
                    <a href="https://maps.google.com/?q=TecNM+Campus+Chetumal" target="_blank" style="color: inherit; text-decoration: none;" title="Ver ubicación en Google Maps">
                        Av. Insurgentes No. 330, Esq. Andrés Quintana Roo,<br>Colonia David Gustavo Gutiérrez, Apdo. Postal 267<br><strong>C.P. 77013, Chetumal, Quintana Roo, México</strong>
                    </a>
                </p>
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
            background: '#0080ff',
            title: '<h3 style="color: #ffffff; border-bottom: 2px solid #ffffff; padding-bottom: 10px;">Guía de Trámites</h3>',
            html: `
                <div style="text-align: left; padding: 10px; background-color: #ffffff00; border-radius: 8px;">
                    <p style="font-size: 0.9rem; color: #ffffff; margin-bottom: 15px;">
                        A continuación se describen los trámites disponibles en el portal:
                    </p>
                    <div style="display: grid; gap: 15px; ">
                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-bullhorn"></i> Avisos Generales</div>
                            <div style="font-size: 0.8rem; color: #555;">Consulta comunicados importantes del departamento.</div>
                        </div>
                    
                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-edit"></i> Prerregistro Verano</div>
                            <div style="font-size: 0.8rem; color: #555;">Pre-regístrate a los cursos de verano disponibles para regularización o adelantamiento.</div>
                        </div>

                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-alt"></i> Carta de Presentación</div>
                            <div style="font-size: 0.8rem; color: #555;">Genera tu carta para iniciar servicio social o residencia profesional.</div>
                        </div>
                        
                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-alt"></i> Carta de Aceptación</div>
                            <div style="font-size: 0.8rem; color: #555;">Genera tu carta de aceptación para iniciar servicio social o residencia profesional.</div>
                        </div>
                        
                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-contract"></i> Cartas de Terminación</div>
                            <div style="font-size: 0.8rem; color: #555;">Solicita tu carta de terminación si realizaste tu trámite como estudiante interno.</div>
                        </div>

                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-language"></i> Constancia Inglés no inconveniencia</div>
                            <div style="font-size: 0.8rem; color: #555;">Solicita tu constancia de no inconveniencia para llevar a cabo el inglés externamente de la institución.</div>
                        </div>

                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-calendar-times"></i> Justificante</div>
                            <div style="font-size: 0.8rem; color: #555;">Reporta tus inasistencias por motivos médicos, escolares o personales justificados.</div>
                        </div>

                        <div style="color: #333; display: block; padding: 12px; background: #e8f1fa; border-radius: 8px; border-left: 4px solid #ff7b00;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-user-check"></i> Carta de Buena Conducta</div>
                            <div style="font-size: 0.8rem; color: #555;">Solicita tu constancia oficial de buena conducta emitida por la institución.</div>
                        </div>
                    </div>
                </div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#ff7b00',
            width: '500px',
            padding: '20px'
        });
    }
</script>

</body>
</html>
<script>
function confirmarInterno() {
    Swal.fire({
        title: '<strong>Confirmación de Acceso</strong>',
        icon: 'info',
        html: `
            <div style="text-align: center;">
                <p>Este registro es <b>exclusivamente</b> para alumnos del instituto que realizaron su trámite como <b>estudiantes INTERNOS</b>.</p>
                <hr>
                <p class="small text-muted">¿Deseas continuar con el registro?</p>
            </div>
        `,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: '<i class="bi bi-check-lg"></i> Sí, soy interno',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1B396A', 
        cancelButtonColor: '#d33',
        heightAuto: false, 
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'alumno_general/carta_terminacion.php';
        }
    });
}

function confirmarInternoAceptacion() {
    Swal.fire({
        title: '<strong>Confirmación de Acceso</strong>',
        icon: 'info',
        html: `
            <div style="text-align: center;">
                <p>Este registro es <b>exclusivamente</b> para alumnos del instituto que realizaron su trámite como <b>estudiantes INTERNOS</b>.</p>
                <hr>
                <p class="small text-muted">¿Deseas continuar con el registro?</p>
            </div>
        `,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: '<i class="bi bi-check-lg"></i> Sí, soy interno',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1B396A', 
        cancelButtonColor: '#d33',
        heightAuto: false, 
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'alumno_general/carta_aceptacion.php';
        }
    });
}
</script>