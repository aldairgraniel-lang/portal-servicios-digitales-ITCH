<?php
// 1. LÓGICA DE PROCESAMIENTO (Mantenida intacta)
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

if (isset($_POST['toggle'])) {
    if (!isset($_POST['tipo_registro'])) exit('Solicitud inválida');
    $tipo = $_POST['tipo_registro'];
    $permitidos = ['registro_abierto', 'registro_ingles_abierto', 'registro_presentacion_abierto', 'registro_aceptacion_abierto', 'registro_terminacion_abierto', 'registro_justificantes_abierto'];
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
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'nuevo_estado' => $nuevo]);
            exit;
        }
    }
    header("Location: panel_docente.php");
    exit;
}

// 2. DEFINICIÓN DE MÓDULOS
$config_modulos = [
    ['label' => 'Preregistro Verano', 'url' => 'preregistro.php', 'icon' => '📋', 'clave' => 'registro_abierto', 'tabla' => 'VERANO'],
    ['label' => 'Inglés No Inconveniencia', 'url' => 'solicitudes_ingles.php', 'icon' => '📑', 'clave' => 'registro_ingles_abierto', 'tabla' => 'registro_ingles'],
    ['label' => 'Carta Presentación', 'url' => 'solicitudes_presentacion.php', 'icon' => '📄', 'clave' => 'registro_presentacion_abierto', 'tabla' => 'solicitudes_cartas_presentacion'],
    ['label' => 'Carta Aceptación', 'url' => 'solicitudes_aceptacion.php', 'icon' => '📄', 'clave' => 'registro_aceptacion_abierto', 'tabla' => 'solicitudes_cartas_aceptacion'],
    ['label' => 'Carta Terminación', 'url' => 'solicitudes_terminacion.php', 'icon' => '📄', 'clave' => 'registro_terminacion_abierto', 'tabla' => 'solicitudes_cartas_terminacion'],
    ['label' => 'Justificantes', 'url' => 'solicitudes_justificantes.php', 'icon' => '📝', 'clave' => 'registro_justificantes_abierto', 'tabla' => 'justificantes'],
];

$modulos_finales = [];
foreach ($config_modulos as $m) {
    $est = $conexion->query("SELECT valor FROM configuracion WHERE clave = '{$m['clave']}'")->fetch_assoc()['valor'] ?? '0';
    $cnt = $conexion->query("SELECT COUNT(*) as total FROM {$m['tabla']}")->fetch_assoc()['total'] ?? 0;
    $modulos_finales[] = array_merge($m, ['abierto' => ($est === '1'), 'conteo' => $cnt]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="img/imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCH - DEP Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(30, 41, 59, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
            --accent-color: #3b82f6;
        }

        body { 
            background: #0f172a; 
            color: #f1f5f9; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .dashboard-container { max-width: 1000px; margin: 2rem auto; padding: 0 15px; }
        
        .header-section { margin-bottom: 2.5rem; }
        .header-section h1 { font-weight: 800; font-size: calc(1.3rem + 1vw); }
        .header-section p { color: #94a3b8; }

        /* Isla de Control con Adaptabilidad */
        .isla-control {
            background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            flex-wrap: wrap; /* Permite que los elementos bajen en pantallas pequeñas */
            gap: 15px;
        }

        .isla-control:hover {
            border-color: rgba(59, 130, 246, 0.4);
            background: rgba(255, 255, 255, 0.07);
        }

        .info-principal { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            flex: 1; 
            min-width: 250px; /* Evita que el texto se aplaste demasiado */
        }
        
        .icon-box {
            font-size: 1.25rem;
            min-width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
        }

        .titulo-modulo { font-weight: 600; font-size: 0.95rem; margin: 0; }
        
        .badge-pill {
            display: inline-block;
            padding: 2px 10px;
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-color);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-top: 4px;
        }

        .acciones { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
            justify-content: flex-end;
        }

        /* Ajustes para móviles */
        @media (max-width: 576px) {
            .isla-control {
                padding: 1rem;
                justify-content: center;
                text-align: center;
            }
            .info-principal {
                flex-direction: column;
                min-width: 100%;
                margin-bottom: 5px;
            }
            .acciones {
                width: 100%;
                justify-content: space-around;
                border-top: 1px solid var(--glass-border);
                padding-top: 15px;
            }
            .status-text { text-align: left !important; }
        }

        .btn-gestionar {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #94a3b8;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .btn-gestionar:hover { color: #fff; background: rgba(255,255,255,0.05); }

        .form-check-input { width: 2.5em !important; height: 1.25em !important; cursor: pointer; }
        
        .status-text { 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            font-weight: 800; 
            min-width: 55px;
            text-align: right;
        }
        .text-open { color: #10b981; }
        .text-closed { color: #64748b; }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="dashboard-container">
    <div class="header-section">
        <h1>Servicios DEP</h1><strong> Division de Estudios Profesionales. </strong>
        <p>Administración de módulos y estados del sistema.</p>
    </div>

    <div class="list-modulos">
        <?php foreach($modulos_finales as $m): ?>
        <div class="isla-control shadow-sm">
            <div class="info-principal">
                <div class="icon-box"><?= $m['icon'] ?></div>
                <div>
                    <h5 class="titulo-modulo"><?= htmlspecialchars($m['label']) ?></h5>
                    <div class="badge-pill"><?= $m['conteo'] ?> REGISTROS</div>
                </div>
            </div>

            <div class="acciones">
                <a href="<?= $m['url'] ?>" class="btn-gestionar">Gestionar</a>

                <form class="toggle-form m-0" method="POST">
                    <input type="hidden" name="tipo_registro" value="<?= $m['clave'] ?>">
                    <div class="form-check form-switch d-flex align-items-center gap-2">
                        <span class="status-text <?= $m['abierto'] ? 'text-open' : 'text-danger' ?>">
                            <?= $m['abierto'] ? 'Activo' : 'Cerrado' ?>
                        </span><hr><hr><hr><hr><hr>
                        <input class="form-check-input" type="checkbox" role="switch" 
                               onchange="actualizarEstado(this)"
                               <?= $m['abierto'] ? 'checked' : '' ?>>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function actualizarEstado(checkbox) {
        const form = checkbox.closest('form');
        const formData = new FormData(form);
        formData.append('toggle', '1');
        const statusText = form.querySelector('.status-text');

        fetch(window.location.href, {
            method: "POST",
            body: formData,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.nuevo_estado === '1') {
                    statusText.textContent = "Activo";
                    statusText.className = "status-text text-open";
                } else {
                    statusText.textContent = "Cerrado";
                    statusText.className = "status-text text-danger";
                }
            }
        })
        .catch(err => {
            console.error(err);
            checkbox.checked = !checkbox.checked;
        });
    }
</script>
</body>
</html>