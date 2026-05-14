<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. CAPTURA DE FILTROS
$nc = $_GET['nc'] ?? '';
$tipo_tramite = $_GET['tipo'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

// Determinamos si el usuario ha solicitado una búsqueda explícita
$busqueda_solicitada = isset($_GET['buscar']);

$resultado = null;
$hayResultados = false;

if ($busqueda_solicitada) {
    // 2. CONSTRUCCIÓN DE CONSULTA DINÁMICA
    $sql = "SELECT id_terminacion, nombre, n_control, tipo_tramite, nombre_archivo_aceptacion, fecha_registro 
            FROM solicitudes_cartas_terminacion WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($nc)) {
        $sql .= " AND n_control LIKE ?";
        $params[] = "%" . $nc . "%";
        $types .= "s";
    }
    if (!empty($tipo_tramite)) {
        $sql .= " AND tipo_tramite = ?";
        $params[] = $tipo_tramite;
        $types .= "s";
    }
    
    // Filtro por rango de fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $sql .= " AND DATE(fecha_registro) BETWEEN ? AND ?";
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
        $types .= "ss";
    } elseif (!empty($fecha_inicio)) {
        $sql .= " AND DATE(fecha_registro) >= ?";
        $params[] = $fecha_inicio;
        $types .= "s";
    } elseif (!empty($fecha_fin)) {
        $sql .= " AND DATE(fecha_registro) <= ?";
        $params[] = $fecha_fin;
        $types .= "s";
    }

    $sql .= " ORDER BY fecha_registro DESC";

    $stmt = $conexion->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $hayResultados = ($resultado && $resultado->num_rows > 0);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITCH - Gestión Terminaciones</title>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/tablasCSS.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="">

<div class="container py-5">
    <div class="card bg-dark border-secondary p-4 shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
        
        <div class="d-flex justify-content-between align-items-center mb-4 text-white">
            <h3 class="fw-bold"><i class="bi bi-mortarboard-fill me-2 text-info"></i>Solicitudes: Terminación</h3>
            <a href="panel_docente.php" class="btn btn-outline-primary fw-bold">
                <i class="bi bi-arrow-left-circle"></i> Panel
            </a>
        </div>

        <!-- FORMULARIO DE FILTROS -->
        <form method="GET" action="" class="row g-3 mb-4 p-3 rounded border border-secondary" style="background: rgba(255,255,255,0.05);">
            <div class="col-md-3">
                <label class="small text-info fw-bold">N° de Control</label>
                <input type="text" name="nc" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 19390015" value="<?= htmlspecialchars($nc) ?>">
            </div>

            <div class="col-md-3">
                <label class="small text-info fw-bold">Trámite</label>
                <select name="tipo" class="form-select bg-dark text-white border-secondary">
                    <option value="">-- Todos --</option>
                    <option value="Residencia Profesional" <?= $tipo_tramite == 'Residencia Profesional' ? 'selected' : '' ?>>Residencia</option>
                    <option value="Servicio Social" <?= $tipo_tramite == 'Servicio Social' ? 'selected' : '' ?>>Servicio Social</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="small text-info fw-bold">Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($fecha_inicio) ?>">
            </div>

            <div class="col-md-2">
                <label class="small text-info fw-bold">Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($fecha_fin) ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end gap-1">
                <!-- Botón Buscar -->
                <button type="submit" name="buscar" type="submit" class="btn btn-info w-100 fw-bold" title="Buscar ahora">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                
                <!-- Botón Excel Fijo con Validación -->
                <button type="button" onclick="verificarExcel()" class="btn btn-success" title="Exportar a Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </button>

                <!-- Botón Limpiar -->
                <a href="solicitudes_terminacion.php" class="btn btn-outline-light"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr class="text-info">
                        <th>N° Control</th>
                        <th>Estudiante</th>
                        <th>Trámite</th>
                        <th>Fecha Registro</th>
                        <th>Ref. Aceptación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$busqueda_solicitada): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-funnel fs-1 d-block mb-2 text-light"></i>
                                <span class="text-white">Seleccione los filtros y presione "Filtrar" para ver los datos.</span>
                            </td>
                        </tr>
                    <?php elseif ($hayResultados): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-warning"><?= htmlspecialchars($row['n_control']) ?></td>
                                <td>
                                    <span class="text-uppercase fw-bold text-white"><?= htmlspecialchars($row['nombre']) ?></span>
                                </td>
                                <td class="text-white"><?= htmlspecialchars($row['tipo_tramite']) ?></td>
                                <td class="text-white"><?= date('d/m/Y', strtotime($row['fecha_registro'])) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($row['nombre_archivo_aceptacion']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-warning"></i>
                                <span class="text-warning">No se encontraron registros con los filtros seleccionados.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function verificarExcel() {
        const busquedaRealizada = <?= $busqueda_solicitada ? 'true' : 'false' ?>;
        const hayDatos = <?= $hayResultados ? 'true' : 'false' ?>;

        if (!busquedaRealizada) {
            Swal.fire({
                icon: 'info',
                title: 'Búsqueda requerida',
                text: 'Debe filtrar los datos antes de poder exportar a Excel.',
                confirmButtonColor: '#0dcaf0',
                background: '#1e293b',
                color: '#fff'
            });
            return;
        }

        if (!hayDatos) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin resultados',
                text: 'No hay datos disponibles para exportar con los filtros aplicados.',
                confirmButtonColor: '#0dcaf0',
                background: '#1e293b',
                color: '#fff'
            });
        } else {
            // Construir URL de descarga con los parámetros actuales
            const params = new URLSearchParams(window.location.search);
            window.location.href = 'descargar_excel_terminacion.php?' + params.toString();
        }
    }
</script>

</body>
</html>