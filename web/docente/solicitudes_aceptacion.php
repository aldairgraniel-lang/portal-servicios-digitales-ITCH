<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. CAPTURA DE FILTROS
$filtro_tipo   = isset($_GET['tipo_tramite']) ? trim($_GET['tipo_tramite']) : '';
$filtro_nc     = isset($_GET['nc']) ? trim($_GET['nc']) : '';
$fecha_inicio  = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin     = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Solo ejecutamos la consulta si el parámetro 'buscar' existe en la URL
$busqueda_solicitada = isset($_GET['buscar']);

$resultados = [];

if ($busqueda_solicitada) {
    $query = "SELECT numero_control, tipo_tramite, archivo_pdf, fecha_registro 
              FROM solicitudes_cartas_aceptacion WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($filtro_nc)) {
        $query .= " AND numero_control LIKE ?";
        $params[] = "%" . $filtro_nc . "%";
        $types .= "s";
    }

    if (!empty($filtro_tipo)) {
        $query .= " AND TRIM(tipo_tramite) = ?";
        $params[] = $filtro_tipo;
        $types .= "s";
    }
    
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $query .= " AND DATE(fecha_registro) BETWEEN ? AND ?";
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
        $types .= "ss";
    } elseif (!empty($fecha_inicio)) {
        $query .= " AND DATE(fecha_registro) >= ?";
        $params[] = $fecha_inicio;
        $types .= "s";
    } elseif (!empty($fecha_fin)) {
        $query .= " AND DATE(fecha_registro) <= ?";
        $params[] = $fecha_fin;
        $types .= "s";
    }

    $query .= " ORDER BY fecha_registro DESC";

    $stmt = $conexion->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Variable para JS
$hayResultados = (count($resultados) > 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITCH - Listas Aceptaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/tablasCSS.css">
</head>
<body>
    <main class="container mt-5">
        <div class="card bg-dark border-secondary p-4 text-white shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold"><i class="bi bi-journal-check me-2 text-info"></i> Solicitudes: Cartas de Aceptación</h3>
                <a href="panel_docente.php" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-arrow-left-circle"></i> Volver al Panel
                </a>
            </div>

            <!-- Formulario de Filtros -->
            <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="row g-3 mb-5 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-white fw-bold small">N° Control</label>
                    <input type="text" name="nc" class="form-control" placeholder="Ej: 19390015" value="<?= htmlspecialchars($filtro_nc) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white fw-bold small">Tipo de Trámite</label>
                    <select name="tipo_tramite" class="form-select">
                        <option value="">Todos los trámites</option>
                        <option value="RESIDENCIA PROFESIONAL" <?= ($filtro_tipo === 'RESIDENCIA PROFESIONAL') ? 'selected' : '' ?>>RESIDENCIA PROFESIONAL</option>
                        <option value="SERVICIO SOCIAL" <?= ($filtro_tipo === 'SERVICIO SOCIAL') ? 'selected' : '' ?>>SERVICIO SOCIAL</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-white fw-bold small">Desde:</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-white fw-bold small">Hasta:</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <!-- Botón de Búsqueda -->
                    <button type="submit" name="buscar" type="submit" class="btn btn-info w-100 fw-bold">
                       <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    
                    <!-- Botón Limpiar -->
                    <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary" title="Limpiar todo">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>

                    <!-- BOTÓN DE EXCEL FIJO CON VALIDACIÓN JS -->
                    <button type="button" onclick="verificarExcel()" class="btn btn-success" title="Descargar Excel">
                        <i class="bi bi-file-earmark-excel"></i>
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover align-middle">
                    <thead>
                        <tr class="text-info">
                            <th>Número de Control</th>
                            <th>Tipo de Trámite</th>
                            <th>Fecha Registro</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$busqueda_solicitada): ?>
                            <tr>
                                <td colspan="4" class="text-center p-5 text-light">
                                    <i class="bi bi-funnel fs-1 d-block mb-2 text-light"></i>
                                    Seleccione los filtros y presione "Filtrar" para ver los datos.
                                </td>
                            </tr>
                        <?php elseif (count($resultados) > 0): ?>
                            <?php foreach ($resultados as $row): ?>
                                <tr>
                                    <td class="text-warning fw-bold"><?= htmlspecialchars($row['numero_control']) ?></td>
                                    <td><?= htmlspecialchars($row['tipo_tramite']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['fecha_registro'])) ?></td>
                                    <td><span class="badge bg-primary text-light"><?= htmlspecialchars($row['archivo_pdf']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center p-5 text-warning">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i> No se encontraron resultados para esta búsqueda.
                                </td>
                            </tr>                               
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function verificarExcel() {
            const busquedaRealizada = <?= $busqueda_solicitada ? 'true' : 'false' ?>;
            const hayDatos = <?= $hayResultados ? 'true' : 'false' ?>;

            if (!busquedaRealizada || !hayDatos) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos para exportar',
                    text: 'Debe realizar una búsqueda que contenga resultados antes de descargar el archivo.',
                    confirmButtonColor: '#198754',
                    background: '#1e293b',
                    color: '#fff'
                });
            } else {
                // Si todo está bien, redirige a la descarga pasando los parámetros actuales
                const urlParams = new URLSearchParams(window.location.search);
                window.location.href = 'descargar_aceptacion.php?' + urlParams.toString();
            }
        }
    </script>
</body>
</html>