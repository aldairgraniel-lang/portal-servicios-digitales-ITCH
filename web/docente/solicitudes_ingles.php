<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. Obtener filtros
$filtro_nc      = isset($_GET['nc']) ? trim($_GET['nc']) : '';
$filtro_periodo = isset($_GET['periodo']) ? trim($_GET['periodo']) : '';
$filtro_fecha   = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// 2. Construcción de consulta dinámica
$condiciones = [];
$params = [];
$types = "";

if ($filtro_nc != '') {
    $condiciones[] = "numero_control LIKE ?";
    $params[] = "%" . $filtro_nc . "%";
    $types .= "s";
}

if ($filtro_periodo != '') {
    $condiciones[] = "periodo = ?";
    $params[] = $filtro_periodo;
    $types .= "s";
}

if ($filtro_fecha != '') {
    $condiciones[] = "DATE(fecha_registro) = ?";
    $params[] = $filtro_fecha;
    $types .= "s";
}

$sql = "SELECT * FROM registro_ingles";
if (count($condiciones) > 0) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}
$sql .= " ORDER BY periodo DESC, nombre ASC";

$stmt = $conexion->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$alumnos = $stmt->get_result();
$hayResultados = ($alumnos && $alumnos->num_rows > 0);

// Cargar periodos para el select
$periodos_db = $conexion->query("SELECT nombre FROM periodos GROUP BY nombre ORDER BY MAX(id) DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head> 
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta charset="UTF-8">
    <title>ITCH - Lista de Solicitudes Ingles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="css/tablasCSS.css">

</head>
<body>
    <div class="container py-5">
        <div class="card bg-dark border-secondary p-4 text-white shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-light"><i class="bi bi-journal-check me-2 text-light"></i> Cartas de no inconveniencia : Inglés</h3>
                <a href="panel_docente.php" class="btn btn-outline-primary fw-bold"><i class="bi bi-arrow-left-circle"></i> Volver</a>
            </div>

            <form method="GET" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="text-light fw-bold">N° Control:</label>
                    <div class="input-group">
                        <input type="text" name="nc" class="form-control" placeholder="ej: 19390015" value="<?= htmlspecialchars($filtro_nc) ?>">
                        <button type="submit" class="btn btn-info"><i class="bi bi-search"></i></button>
                        <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary fw-bold"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="text-light fw-bold">Periodo:</label>
                    <select name="periodo" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos los periodos</option>
                        <?php if ($periodos_db): while($p = $periodos_db->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($p['nombre']) ?>" <?= $filtro_periodo == $p['nombre'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="text-light fw-bold">Fecha:</label>
                    <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtro_fecha) ?>" onchange="this.form.submit()">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="button" 
                            onclick="<?= $hayResultados ? "window.location.href='exportar_excel_ingles.php?".http_build_query($_GET)."'" : "alertaVacia()" ?>" 
                            class="btn btn-success w-30 fw-bold"><i class="bi bi-file-earmark-excel"></i> Excel grupal</button>
                            <a href="generar_constancias_ingles.php" class="btn btn-warning w-30 fw-bold"><i class="bi bi-download"></i>  pdf por alumno</a>
                        </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-hover border-secondary align-middle">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th>N° Control</th>
                            <th>Nombre</th>
                            <th>Carrera</th>
                            <th class="text-center">Periodo</th>
                            <th>Situación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hayResultados): while($a = $alumnos->fetch_assoc()): ?>
                            <tr>
                                <td class="text-warning fw-bold"><?= htmlspecialchars($a['numero_control']) ?></td>
                                <td><?= htmlspecialchars($a['nombre']) ?></td>
                                <td><?= htmlspecialchars($a['carrera']) ?></td>
                                <td class="text-center"><span class="badge bg-primary"><?= htmlspecialchars($a['periodo']) ?></span></td>
                                <td>
                                    <?= htmlspecialchars($a['tipo_alumno']) ?>
                                    <?php if (!empty($a['semestre'])): ?>
                                        <br><small class="text-info"><?= htmlspecialchars($a['semestre']) ?>° Semestre</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5" class="text-center p-5">No se encontraron registros.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function alertaVacia() {
            Swal.fire({
                icon: 'info',
                title: 'Sin registros',
                text: 'No hay datos para exportar.',
                confirmButtonColor: '#198754',
                backdrop: false
            });
        }
    </script>
</body>
</html>