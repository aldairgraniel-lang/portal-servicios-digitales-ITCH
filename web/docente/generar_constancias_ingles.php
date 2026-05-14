<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. Obtener filtros
$filtro_nc     = isset($_GET['nc']) ? trim($_GET['nc']) : '';
$filtro_tipo   = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$fecha_inicio  = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin     = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Variable para determinar si se ha iniciado una búsqueda
$busqueda_activa = !empty($_GET);

// 2. Construcción de consulta dinámica
$condiciones = [];
$params = [];
$types = "";

if ($filtro_nc != '') {
    $condiciones[] = "numero_control LIKE ?";
    $params[] = "%" . $filtro_nc . "%";
    $types .= "s";
}
if ($filtro_tipo != '') {
    $condiciones[] = "tipo_alumno = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

// Filtro de rango de fechas
if ($fecha_inicio != '' && $fecha_fin != '') {
    $condiciones[] = "DATE(fecha_registro) BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
    $types .= "ss";
} elseif ($fecha_inicio != '') {
    $condiciones[] = "DATE(fecha_registro) >= ?";
    $params[] = $fecha_inicio;
    $types .= "s";
} elseif ($fecha_fin != '') {
    $condiciones[] = "DATE(fecha_registro) <= ?";
    $params[] = $fecha_fin;
    $types .= "s";
}

$alumnos = null;
$hayResultados = false;

// SOLO ejecutar la consulta si hay una búsqueda activa (parámetros en la URL)
if ($busqueda_activa) {
    $sql = "SELECT * FROM registro_ingles";
    if (count($condiciones) > 0) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }
    $sql .= " ORDER BY nombre ASC";

    $stmt = $conexion->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $alumnos = $stmt->get_result();
    $hayResultados = ($alumnos && $alumnos->num_rows > 0);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Constancias</title>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/constancias.css">
</head>
<body>
<div class="container py-5">
    <div class="card bg-dark border-secondary p-4 text-white shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-file-earmark-text me-2"></i> Constancias por alumno</h3>
            <a href="solicitudes_ingles.php" class="btn btn-outline-primary fw-bold"><i class="bi bi-arrow-left"></i> Regresar</a>
        </div>

        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-3">
                <label class="fw-bold small">N° Control:</label>
                <div class="input-group">
                    <input type="text" name="nc" class="form-control" placeholder="ej: 19390015" value="<?= htmlspecialchars($filtro_nc) ?>">
                </div>
            </div>

            <div class="col-md-3">
                <label class="fw-bold small">Situación:</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="Cursando semestre" <?= $filtro_tipo == 'Cursando semestre' ? 'selected' : '' ?>>Cursando Semestre</option>
                    <option value="Egresado" <?= $filtro_tipo == 'Egresado' ? 'selected' : '' ?>>Egresado</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="fw-bold small">Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
            </div>

            <div class="col-md-2">
                <label class="fw-bold small">Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-info w-100 fw-bold"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary fw-bold"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle border-secondary">
                <thead class="table-secondary text-dark">
                    <tr>
                        <th>N° Control</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (!$busqueda_activa): ?>
                        <tr>
                            <td colspan="4" class="text-center p-5 text-light">
                                <i class="bi bi-funnel fs-2 d-block mb-2"></i> Use los filtros para buscar alumnos y generar sus constancias.
                            </td>
                        </tr>
                    <?php elseif ($hayResultados): while($a = $alumnos->fetch_assoc()): ?>
                        <tr>
                            <td class="text-warning fw-bold"><?= htmlspecialchars($a['numero_control']) ?></td>
                            <td><?= htmlspecialchars($a['nombre']) ?></td>
                            <td>
                                <span class="badge <?= $a['tipo_alumno'] == 'Egresado' ? 'bg-secondary' : 'bg-success' ?>">
                                    <?= htmlspecialchars($a['tipo_alumno']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="imprimir_constancia_ingles.php?nc=<?= urlencode($a['numero_control']) ?>" 
                                   class="btn btn-sm btn-primary fw-bold" 
                                   target="_blank">
                                    <i class="bi bi-printer me-1"></i> Generar PDF
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="text-center p-5 text-light">
                                <i class="bi bi-folder-x fs-2 d-block mb-2"></i> No se encontraron registros con esos criterios.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>