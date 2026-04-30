<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

/**
 * LÓGICA DE CONTROL
 */
if (isset($_POST['toggle'])) {
    if (!isset($_POST['tipo_registro'])) exit('Solicitud inválida');

    $tipo = $_POST['tipo_registro'];
    $permitidos = [
        'registro_abierto',
        'registro_ingles_abierto',
        'registro_presentacion_abierto',
        'registro_aceptacion_abierto',
        'registro_justificantes_abierto'
    ];

    if (!in_array($tipo, $permitidos)) exit('Acceso no permitido');

    $stmt = $conexion->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if ($resultado) {
        $nuevo = ($resultado['valor'] === '1') ? '0' : '1';
        $stmt = $conexion->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
        $stmt->bind_param("ss", $nuevo, $tipo);
        $stmt->execute();
    }
    header("Location: panel_docente.php");
    exit;
}

// Obtener estados actuales
$estado_verano = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_ingles = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_ingles_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_presentacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_presentacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_aceptacion = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_aceptacion_abierto'")->fetch_assoc()['valor'] ?? '0';
$estado_justificantes = $conexion->query("SELECT valor FROM configuracion WHERE clave = 'registro_justificantes_abierto'")->fetch_assoc()['valor'] ?? '0';

// OBTENCIÓN DE CONTADORES
$count_verano = $conexion->query("SELECT COUNT(*) as total FROM VERANO")->fetch_assoc()['total'] ?? 0;
$count_ingles = $conexion->query("SELECT COUNT(*) as total FROM registro_ingles")->fetch_assoc()['total'] ?? 0;
$count_presentacion = $conexion->query("SELECT COUNT(*) as total FROM solicitudes_cartas_presentacion")->fetch_assoc()['total'] ?? 0;
$count_aceptacion = $conexion->query("SELECT COUNT(*) as total FROM solicitudes_cartas_aceptacion")->fetch_assoc()['total'] ?? 0;
$count_justificantes = $conexion->query("SELECT COUNT(*) as total FROM justificantes")->fetch_assoc()['total'] ?? 0;

$verano_abierto = ($estado_verano === '1');
$ingles_abierto = ($estado_ingles === '1');
$presentacion_abierto = ($estado_presentacion === '1');
$aceptacion_abierto = ($estado_aceptacion === '1');
$justificantes_abierto = ($estado_justificantes === '1');

// --- AHORA LLAMAMOS AL HEADER ---
include('includes/header.php'); 
?>
<title> ITCH | Panel Docente</title>
    <header class="mb-5">
        <h1 class="fw-bold">Division de Estudios Profesionales</h1>
        <p class="text-light">Gestión de servicios escolares y procesos académicos.</p>
    </header>

    <div class="row g-4">
        <?php 
        $items = [
            ['preregistro - verano', 'preregistro.php', '📄', $count_verano, $verano_abierto, 'registro_abierto'],
            ['solicitudes - Inglés no inconveniencia', 'solicitudes_ingles.php', '📄', $count_ingles, $ingles_abierto, 'registro_ingles_abierto'],
            ['solicitudes - Carta Presentación', 'solicitudes_presentacion.php', '📄', $count_presentacion, $presentacion_abierto, 'registro_presentacion_abierto'],
            ['solicitudes - Carta Aceptación', 'solicitudes_aceptacion.php', '📄', $count_aceptacion, $aceptacion_abierto, 'registro_aceptacion_abierto'],
            ['solicitudes - Justificantes', 'solicitudes_justificantes.php', '📝', $count_justificantes, $justificantes_abierto, 'registro_justificantes_abierto']
        ];

        foreach($items as $i): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card-modern">
                <?php if($i[3] > 0): ?><span class="notification-badge"><?= $i[3] ?></span><?php endif; ?>
                <a href="<?= $i[1] ?>" class="text-decoration-none text-white">
                    <div class="fs-2 mb-3"><?= $i[2] ?></div>
                    <h5 class="fw-bold"><?= $i[0] ?></h5>
                    <p class="text-light small">Total: <?= $i[3] ?> registros</p>
                </a>
                
                <form method="POST" class="mt-3">
                    <input type="hidden" name="tipo_registro" value="<?= $i[5] ?>">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="status-badge <?= $i[4] ? 'bg-open' : 'bg-closed' ?>">
                            <?= $i[4] ? 'ABIERTO' : 'CERRADO' ?>
                        </span>
                        <button type="submit" name="toggle" class="btn btn-sm <?= $i[4] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                            <?= $i[4] ? 'Cerrar' : 'Abrir' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>