<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="/docente/img/Imagen1.png" type="image/x-icon">
    <title>TecNM - Campus Chetumal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/estilos.css">
    
    <style>
        /* Estilos para el spinner de carga */
        #spinner {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s linear, visibility 0.5s linear;
            z-index: 9999;
            /* Fondo con degradado azul oscuro */
            background: linear-gradient(135deg, #0d1b2a, #1b263b) !important;
        }
        #spinner.loaded {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>
<body>

    <div id="spinner" class="position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex flex-column align-items-center justify-content-center">
        <div class="spinner-border text-light" style="width: 3.5rem; height: 3.5rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <span class="text-light mt-3 fw-bold">Cargando sitio...</span>
    </div>

    <nav class="navbar navbar-tecnm">
        <div class="container">
            <a href="/index.php" class="glass-header text-decoration-none">
                <div class="navbar-brand m-0">
                    <div class="title-main">Instituto Tecnológico de Chetumal</div>
                    <small class="subtitle-main">División de Estudios Profesionales</small>
                </div>
            </a>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            const spinner = document.getElementById('spinner');
            
            // Tiempo mínimo de carga en milisegundos (2000 ms = 2 segundos)
            const tiempoMinimo = 0.3 * 1000; // Puedes ajustar este valor según tus necesidades

            setTimeout(() => {
                spinner.classList.add('loaded');
                
                // Ocultar del DOM después de la transición
                setTimeout(() => {
                    spinner.style.display = 'none';
                }, 500);
            }, tiempoMinimo);
        });
    </script>
</body>
</html>