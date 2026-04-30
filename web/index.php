<?php include('includes/header.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    /* --- TU CSS ORIGINAL --- */
    .boton-servicio {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        border-radius: 10px !important;
        border: none !important;
    }
    .boton-servicio:hover {
        transform: scale(1.05) translateY(-5px) !important;
        box-shadow: 0 15px 30px rgba(0, 170, 255, 0.3) !important;
        filter: brightness(1.15) !important;
    }

    .footer-pro { background-color: #00264d; color: #ffffff; padding: 60px 0 30px 0; margin-top: 50px; }
    .footer-pro h5 { color: #00aaff; font-weight: 700; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
    .footer-pro p, .footer-pro a { font-size: 0.95rem; line-height: 1.6; color: #e0e0e0 !important; text-decoration: none; transition: 0.3s; }
    .footer-pro a:hover { color: #ffffff !important; padding-left: 5px; }
    .map-wrapper { aspect-ratio: 16 / 9; width: 100%; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.4); border: 2px solid #00aaff; }
    
    @media (max-width: 768px) { .footer-pro { text-align: center; } }

    /* --- NUEVO: ESTILOS DE LA BARRA LATERAL --- */
    .social-sidebar {
        position: fixed;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .social-sidebar a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        color: white;
        text-decoration: none;
        transition: 0.3s;
        font-size: 20px;
    }
    .social-sidebar a:hover { width: 60px; } /* Efecto expansión al pasar el mouse */
    .bg-fb { background: #3b5998; border-radius: 8px 0 0 0; }
    .bg-tw { background: #1da1f2; }
    .bg-yt { background: #ff0000; border-radius: 0 0 0 8px; }
    .bg-info-btn { background: #eeff0000; border-radius: 0%; }
    .bg-info-btn:hover { background: #ffffff00; color: #000000 !important; }
    .bg-info-btn i { color: #ffffff; font-size: 18px; }
    .bg-info-btn:hover i { color: #ffea00; }

</style>

<main class="container contenedor-central">
    <div class="tarjeta-glass">
        <div class="titulo text-center mb-5">Portal de Servicios Digitales</div>
        <div class="row g-4 justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <a href="preregistro.php" class="btn btn-primary btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center">
                    <span>PREREGISTRO VERANO.</span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="avisos.php" class="btn btn-primary btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center">
                    <span>AVISOS GENERALES.</span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="ingles_constancia.php" class="btn btn-primary btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center">
                    <span>CONSTANCIA INGLES NO INCONVENIENCIA.</span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="carta_presentacion.php" class="btn btn-primary btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center">
                    <span>CARTA DE PRESENTACIÓN.</span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="carta_aceptacion.php" class="btn btn-primary btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center">
                    <span>CARTA DE ACEPTACIÓN.</span>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <a href="justificante.php" class="btn btn-primary btn-lg boton-servicio w-100 d-flex align-items-center justify-content-center">
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
                    <li><a href="https://www.plataformadetransparencia.org.mx/">Transparencia</a></li>
                    <li><a href="https://chetumal.tecnm.mx/notas/#">CAMPUS CHETUMAL</a></li>
                    <li><a href="https://qroo.gob.mx/seq">SEQ</a></li>
                    <li><a href="https://www.gob.mx/sep">SEP</a></li>
                    <li><a href="https://www.gob.mx/">GOB FED</a></li>
                    <li><a href="https://home.inai.org.mx/">INAI</a></li>
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
    <a href="https://www.facebook.com/ITChetumal" class="bg-fb"><i class="fab fa-facebook-f"></i></a>
    <a href="https://x.com/IT_Chetumal" class="bg-tw"><i class="fab fa-twitter"></i></a>
    <a href="https://www.youtube.com/channel/UChLQcazFScVKbXtHTY6Ej6A" class="bg-yt"><i class="fab fa-youtube"></i></a>
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
                        
                        <a href="preregistro.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-edit"></i> Prerregistro Verano</div>
                            <div style="font-size: 0.8rem; color: #666;"> Pre-regístrate a los cursos de verano disponibles para regularización o adelantamiento.</div>
                        </a>

                        <a href="avisos.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-bullhorn"></i> Avisos Generales</div>
                            <div style="font-size: 0.8rem; color: #666;">Consulta comunicados importantes del departamento.</div>
                        </a>

                        <a href="carta_presentacion.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-alt"></i> Carta de Presentación</div>
                            <div style="font-size: 0.8rem; color: #666;">Genera tu carta para iniciar servicio social o residencia profesional.</div>
                        </a>
                            <a href="carta_aceptacion.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-file-alt"></i> Carta de Aceptación</div>
                            <div style="font-size: 0.8rem; color: #666;">Genera tu carta de aceptación para iniciar servicio social o residencia profesional.</div>
                        </a>

                        
                        <a href="ingles_constancia.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-language"></i> Constancia Inglés no inconveniencia </div>
                            <div style="font-size: 0.8rem; color: #666;">Solicita tu constancia de no inconveniencia para llevar acabo el ingles externamente de la institución.</div>
                        </a>

                        <a href="justificante.php" style="text-decoration: none; color: inherit; display: block; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #00aaff;">
                            <div style="font-weight: bold; color: #00264d;"><i class="fas fa-calendar-times"></i> Justificante</div>
                            <div style="font-size: 0.8rem; color: #666;">Reporta tus inasistencias por motivos médicos,escolares o personales justificados.</div>
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