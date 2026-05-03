<?php
// Esto DEBE ser lo primero para procesar el POST antes de cualquier salida
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

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

        // 1. Detección de petición AJAX (Fetch API) para no recargar la página
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'nuevo_estado' => $nuevo
            ]);
            exit;
        }
    }
    
    // Si no es petición AJAX (ej. JS desactivado), recarga normal
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="img/imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCH - División de Estudios Profesionales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/panel.css">
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="panel-header mb-4">
    <h1>División de Estudios Profesionales</h1>
    <p>Gestión de servicios escolares y procesos académicos.</p>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php 
    $items = [
        ['preregistro - verano', 'preregistro.php', '📄', $count_verano, $verano_abierto, 'registro_abierto'],
        ['solicitudes - Inglés no inconveniencia', 'solicitudes_ingles.php', '📄', $count_ingles, $ingles_abierto, 'registro_ingles_abierto'],
        ['solicitudes - Carta Presentación', 'solicitudes_presentacion.php', '📄', $count_presentacion, $presentacion_abierto, 'registro_presentacion_abierto'],
        ['solicitudes - Carta Aceptación', 'solicitudes_aceptacion.php', '📄', $count_aceptacion, $aceptacion_abierto, 'registro_aceptacion_abierto'],
        ['solicitudes - Justificantes', 'solicitudes_justificantes.php', '📝', $count_justificantes, $justificantes_abierto, 'registro_justificantes_abierto']
    ];

    foreach($items as $i): ?>
    <div class="col">
        <div class="card-modern shadow-sm">
            <?php if($i[3] > 0): ?>
                <span class="notification-badge"><?= $i[3] ?></span>
            <?php endif; ?>
            
            <a href="<?= $i[1] ?>" class="card-link text-white">
                <div class="fs-2 mb-2"><?= $i[2] ?></div>
                <h5 class="fw-bold mb-2" style="font-size: 1.15rem;"><?= htmlspecialchars($i[0]) ?></h5>
                <p class="text-white-50 small mb-4">Total: <?= $i[3] ?> registros</p>
            </a>
            
            <form class="toggle-form" method="POST">
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

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".toggle-form").forEach(form => {
            form.addEventListener("submit", function(event) {
                event.preventDefault(); // Previene la recarga de página por defecto
                
                const formData = new FormData(this);
                formData.append('toggle', '1');

                const statusBadge = this.querySelector(".status-badge");
                const button = this.querySelector("button");

                fetch(window.location.href, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.nuevo_estado === '1') {
                            statusBadge.className = "status-badge bg-open";
                            statusBadge.textContent = "ABIERTO";
                            button.className = "btn btn-sm btn-outline-danger";
                            button.textContent = "Cerrar";
                        } else {
                            statusBadge.className = "status-badge bg-closed";
                            statusBadge.textContent = "CERRADO";
                            button.className = "btn btn-sm btn-outline-success";
                            button.textContent = "Abrir";
                        }
                    }
                })
                .catch(error => console.error("Error al conectar con el servidor:", error));
            });
        });
    });
</script>

<?php 
// Cerrar el <main> y el <div> (wrapper) que fueron abiertos en header.php
?>
</main>
</div>
</body>
</html>