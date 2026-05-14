<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. OBTENER FILTROS
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$nc = $_GET['nc'] ?? ''; 

// Variable para controlar si se ha presionado el botón de búsqueda
$busqueda_solicitada = isset($_GET['buscar']);

// Obtener motivos únicos (Se mantiene tu lógica original)
$query_motivos = "SELECT DISTINCT motivo FROM justificantes WHERE motivo IS NOT NULL AND motivo != '' ORDER BY motivo ASC";
$res_motivos = $conexion->query($query_motivos);

// 2. CONSTRUCCIÓN DE CONSULTA
$resultado = null;
$hayResultados = false;

if ($busqueda_solicitada) {
    $sql = "SELECT * FROM justificantes WHERE 1=1";
    $params = [];
    $types = "";

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
    }

    if (!empty($tipo)) {
        $sql .= " AND motivo = ?";
        $params[] = $tipo;
        $types .= "s";
    }

    if (!empty($nc)) {
        $sql .= " AND n_control LIKE ?";
        $params[] = "%" . $nc . "%";
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
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITCH - Listas Justificantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/constancias.css">
</head>
<body>
<div class="container py-5">
    <div class="card-panel">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-journal-check me-2 text-info"></i>Solicitudes : Justificantes</h3>
            <a href="panel_docente.php" class="btn btn-outline-primary fw-bold"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
        </div>

        <form class="row g-3 mb-4 p-3 rounded" style="background: #ffffff0d;" method="GET">
            <div class="col-md-3">
                <label class="small text-info">N° Control</label>
                <input type="text" name="nc" class="form-control" placeholder="Ej: 19390015" value="<?= htmlspecialchars($nc) ?>">
            </div>

            <div class="col-md-2">
                <label class="small text-info">Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
            </div>

            <div class="col-md-2">
                <label class="small text-info">Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
            </div>

            <div class="col-md-3">
                <label class="small text-info">Motivo</label>
                <select name="tipo" class="form-select">
                    <option value="">-- Todos --</option>
                    <?php while($m = $res_motivos->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($m['motivo']) ?>" <?= ($tipo == $m['motivo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['motivo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-1">
                <button type="submit" name="buscar" value="1" class="btn btn-info flex-grow-1">
                    <i class="bi bi-search"></i>
                </button>
                <a href="solicitudes_justificantes.php" class="btn btn-outline-light"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>N° Control</th>
                        <th>Motivo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$busqueda_solicitada): ?>
                        <tr>
                            <td colspan="5" class="text-center p-5 text-light">
                                <i class="bi bi-funnel fs-2 d-block mb-2 text-info"></i> Use los filtros para mostrar registros.
                            </td>
                        </tr>
                    <?php elseif ($hayResultados): ?>
                        <?php while($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= date("d/m/Y", strtotime($row['fecha_registro'])) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['nombre']) ?></td>
                                <td><?= htmlspecialchars($row['n_control']) ?></td>
                                <td><?= htmlspecialchars($row['motivo']) ?></td>
                                <td>
                                    <a href="../<?= htmlspecialchars($row['archivo_ruta']) ?>" target="_blank" class="btn btn-sm btn-descargar" title="Ver PDF">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                    <a href="imprimir_justificante.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-expedir" title="Expedir">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-5 text-warning">
                                <i class="bi bi-folder-x fs-2 d-block mb-2 text-warning"></i> No se encontraron registros.
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