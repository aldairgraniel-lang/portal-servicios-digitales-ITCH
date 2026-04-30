    <?php
    include('includes/header.php'); 
    include('conexion.php');

    // 1. Verificación de estado del registro
    $estado_query = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_abierto'");
    $estado = $estado_query->fetch_assoc()['valor'];

    if ($estado !== '1'): ?>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                        <div class="tarjeta-glass text-center p-5" style="max-width: 500px; border-top: 5px solid #9D843E;">
                            <div class="display-1 mb-4">⚠️</div>
                            <h2 class="titulo" style="color: #fff;">CERRADO</h2>
                            <p class="text-blanco-puro mt-3">No está disponible actualmente.</p>
                            <div class="text-center mt-4">
                                <a href="index.php" class="boton-servicio text-decoration-none" style="display: inline-block;">VOLVER AL INICIO</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body></html>
    <?php exit; endif;

    // 2. Carga de datos
    $carreras  = $conexion->query("SELECT nombre FROM carreras")->fetch_all(MYSQLI_ASSOC);
    $semestres = $conexion->query("SELECT numero FROM semestres")->fetch_all(MYSQLI_ASSOC);
    $cursos    = $conexion->query("SELECT nombre FROM cursos")->fetch_all(MYSQLI_ASSOC);
    $reps_data = $conexion->query("SELECT nombre FROM representantes ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
    ?>

    <style>
        .btn-tecnm { background: #1B396A; color: white; font-weight: bold; border-radius: 10px; transition: 0.3s; border: none; width: 100%; padding: 10px; text-decoration: none; display: inline-block; }
        .btn-tecnm:hover { background: #9D843E; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        /* Estilos mejorados para los botones */
        .reg-verano__btn-submit, 
        .reg-verano__btn-rep {
            display: inline-block;
            padding: 12px 25px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .reg-verano__btn-submit {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: #ffffff;
            width: 100%;
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
        }

        .reg-verano__btn-submit:hover {
            background: linear-gradient(135deg, #0b5ed7, #0a4bb4);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(13, 110, 253, 0.3);
        }

        .reg-verano__btn-submit:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        .reg-verano__btn-rep {
            background-color: transparent;
            border: 2px solid #0d6efd;
            color: #0d6efd;
        }

        .reg-verano__btn-rep:hover {
            background-color: #0d6efd;
            color: #ffffff;
            transform: translateY(-2px);
        }
    </style>

    <div class="reg-verano__wrapper">
        <header class="reg-verano__hero">
            <div class="container">
                <h1 class="reg-verano__title">Preregistro Verano</h1>
                <p class="reg-verano__subtitle">Sigue las instrucciones para un registro exitoso.</p>
                <p class="reg-verano__note">Es un preregistro no es definitivo se utilizara para avanzar grupos  o cursos que se ofertaran.</p>
            </div>
        </header>

        <main class="reg-verano__card-container">
            <div class="reg-verano__card">
                <section class="reg-verano__banner">
                    <div class="reg-verano__instruction-text">
                        <h5 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Pasos Importantes</h5>
                        <ul class="reg-verano__list">
                            <strong>Nota importante:</strong> iniciar con apellidos, luego nombres y mayúsculas.<br>
                            <li><strong>Líderes:</strong> Regístrate primero como representante en el botón azul.</li>
                            <li><strong>Contacto:</strong> Celular a 10 dígitos.</li>
                            <li><strong>Verificación:</strong> Revisa todo antes de enviar sin espacios de mas.</li>
                        </ul>
                    </div>
                    <div class="text-center reg-verano__btn-wrapper">
                        <small class="d-block mb-2 fw-bold opacity-75">¿ERES REPRESENTANTE  ?</small>
                        <a href="representantes.php" class="btn btn-primary  btn-block">Click aqui.</a>
                    </div>
                </section>

                <form id="registroForm" action="guardar_preregistro.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="reg-verano__label">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Juan Carlos" pattern="([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+[\s]*)+" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="reg-verano__label">Apellido(s)</label>
                            <input type="text" name="apellidos" class="form-control" placeholder="Ej. Pérez" pattern="([A-ZÁÉÍÓÚÑ][a-záéíóúñ]+[\s]*)+" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="reg-verano__label">Número de Control</label>
                            <input type="text" name="numero_control" class="form-control" required placeholder="Ej. 19390015" maxlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="reg-verano__label">Teléfono (WhatsApp)</label>
                            <input type="tel" name="numero_celular" class="form-control" placeholder="10 dígitos" pattern="[0-9]{10}" maxlength="10" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="reg-verano__label">Carrera</label>
                            <select name="carrera" class="form-select" required>
                                <option value="">Selecciona tu carrera</option>
                                <?php foreach($carreras as $c): ?>
                                    <option value="<?= $c['nombre'] ?>"><?= $c['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="reg-verano__label">Semestre Actual</label>
                            <select name="semestre" class="form-select" required>
                                <option value="">Nivel...</option>
                                <?php foreach($semestres as $s): ?>
                                    <option value="<?= $s['numero'] ?>"><?= $s['numero'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 mb-4">
                            <label class="reg-verano__label">Curso Requerido</label>
                            <select name="curso" class="form-select" required>
                                <option value="">Selecciona la materia de verano</option>
                                <?php foreach($cursos as $cur): ?>
                                    <option value="<?= $cur['nombre'] ?>"><?= $cur['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="reg-verano__label text-primary">Representante Titular</label>
                            <select name="representante_1" id="rep1" class="form-select border-primary-subtle" required onchange="filtrarReps()">
                                <option value="">Seleccionar...</option>
                                <?php foreach($reps_data as $r): ?>
                                    <option value="<?= $r['nombre'] ?>"><?= $r['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="reg-verano__label text-muted">Representante Auxiliar</label>
                            <select name="representante_2" id="rep2" class="form-select" required onchange="filtrarReps()">
                                <option value="">Seleccionar...</option>
                                <?php foreach($reps_data as $r): ?>
                                    <option value="<?= $r['nombre'] ?>"><?= $r['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmit" class="btn-tecnm">Finalizar y Enviar</button>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="text-muted text-decoration-none small">← Volver al Inicio</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Filtrado de representantes para no repetir
        function filtrarReps() {
            const r1 = document.getElementById('rep1');
            const r2 = document.getElementById('rep2');
            const v1 = r1.value;
            const v2 = r2.value;

            [...r1.options].forEach(opt => opt.disabled = (opt.value !== "" && opt.value === v2));
            [...r2.options].forEach(opt => opt.disabled = (opt.value !== "" && opt.value === v1));
        }

        // Efecto de carga al enviar
        document.getElementById('registroForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerText = "Procesando...";
            btn.style.opacity = "0.7";
        });
    </script>
    </body>
    </html>