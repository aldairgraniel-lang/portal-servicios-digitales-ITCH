<?php
// buena_conducta.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Conexión a la base de datos (subiendo un nivel ya que este archivo vive en alumno_general/)
include('../conexion.php');
include('../includes/header.php');

// Verificar si el servicio está activo en la configuración general
$estado_buena_conducta = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_buena_conducta_abierto'")->fetch_assoc()['valor'] ?? '0';

if ($estado_buena_conducta !== '1') {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Servicio Cerrado',
            text: 'La solicitud de Carta de Buena Conducta no está disponible en este momento.',
            confirmButtonColor: '#1B396A'
        }).then(() => {
            window.location.href = '../index.php';
        });
    </script>";
    exit;
}

// Consultar las carreras ordenadas por ID
$query_carreras = $conexion->query("SELECT id, nombre FROM carreras ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Carta de Buena Conducta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/index.css">
    <style>
        body {
            background-color: #f4f6f9;
            color: #333333;
        }
        /* Réplica exacta del diseño de la tarjeta */
        .card-registro-exacto {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-top: 5px solid #9D843E; /* El toque gold arriba */
            padding: 2.5rem;
        }
        .titulo-tramite {
            color: #1B396A;
            font-weight: 500;
        }
        .indicaciones-exactas {
            background-color: #f8f9fa;
            border-left: 4px solid #1B396A;
            border-radius: 4px;
        }
        .label-tecnm-exacto {
            color: #1B396A;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
            display: block;
        }
        .form-control, .form-select {
            border: 1px solid #ced4da;
            padding: 0.6rem 0.75rem;
            color: #495057;
        }
        /* Botón institucional con el ligero salto y sombra al pasar el cursor */
        .btn-enviar-exacto {
            background-color: #1B396A;
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            width: 100%;
            display: block;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra base */
            transition: background-color 0.25s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-enviar-exacto:hover {
            background-color: #9D843E; /* Se pone gold */
            color: #ffffff;
            transform: translateY(-2px); /* El ligero salto hacia arriba */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Sombra más profunda */
        }
        .btn-enviar-exacto:active {
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body> 

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            
            <!-- Contenedor réplica exacta con borde gold superior -->
            <div class="card-registro-exacto shadow">
                <h3 class="text-center mb-1 titulo-tramite">Trámite: Solicitud de Carta de Buena Conducta</h3>
                <p class="text-center text-muted small mb-4">Ingrese los datos correspondientes para el trámite</p>

                <!-- Caja de Notas -->
                <div class="indicaciones-exactas p-3 mb-4 shadow-sm text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                    <strong>Nota importante:</strong> Iniciar con apellidos, luego nombres y mayúsculas.<br>
                    <strong>Verificación:</strong> Revisa que tu número de control sea el correcto y corresponda a tu carrera vigente.
                </div>

                <!-- Formulario -->
                <form id="formBuenaConducta" action="guardar_carta_buena_conducta.php" method="POST">
                    
                    <!-- Nombre Completo -->
                    <div class="mb-3">
                        <label class="label-tecnm-exacto">NOMBRE COMPLETO</label>
                        <input type="text" 
                               name="nombre_alumno" 
                               id="nombre_alumno" 
                               class="form-control" 
                               placeholder="Ej. Pérez López Juan" 
                               required 
                               autocomplete="off">
                    </div>

                    <div class="row">
                        <!-- Número de Control -->
                        <div class="col-md-6 mb-4">
                            <label class="label-tecnm-exacto">NÚMERO DE CONTROL</label>
                            <input type="text" 
                                   name="num_control" 
                                   id="num_control" 
                                   class="form-control" 
                                   placeholder="Ej. 19390015" 
                                   maxlength="10" 
                                   style="text-transform: uppercase;"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                                   required 
                                   autocomplete="off">
                        </div>

                        <!-- Carrera desde la base de datos -->
                        <div class="col-md-6 mb-4">
                            <label class="label-tecnm-exacto">CARRERA</label>
                            <select name="carrera_alumno" id="carrera_alumno" class="form-select" required>
                                <option value="" disabled selected>Seleccione una opción...</option>
                                <?php while($row_carrera = $query_carreras->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row_carrera['nombre']); ?>">
                                        <?php echo htmlspecialchars($row_carrera['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Botón de Envío Interactivo -->
                    <div class="mb-3">
                        <button type="submit" class="btn-enviar-exacto">Finalizar y Enviar</button>
                    </div>
                    
                    <!-- Regresar al Inicio debajo del botón -->
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Validaciones del lado del cliente
    document.getElementById('formBuenaConducta').addEventListener('submit', function(e) {
        const numControlInput = document.getElementById('num_control');
        numControlInput.value = numControlInput.value.toUpperCase().trim();
        
        const numControl = numControlInput.value;
        const nombre = document.getElementById('nombre_alumno').value.trim();
        const regexAlfanumerico = /^[A-Z0-9]{8,10}$/;

        if (!regexAlfanumerico.test(numControl)) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Número de Control Inválido',
                text: 'El número de control debe tener entre 8 y 10 caracteres alfanuméricos (letras y números).',
                confirmButtonColor: '#1B396A'
            });
            return;
        }

        if (nombre.split(' ').length < 2) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Nombre Incompleto',
                text: 'Por favor, asegúrate de ingresar tus apellidos y nombre completo.',
                confirmButtonColor: '#1B396A'
            });
        }
    });
</script>

</body>
</html>