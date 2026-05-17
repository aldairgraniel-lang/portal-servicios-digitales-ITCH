<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include(__DIR__ . "/../conexion.php");
include('includes/auth_docente.php');

// =========================
// FILTROS
// =========================
$filtro_nc     = $_GET['nc'] ?? ''; 
$filtro_curso  = $_GET['curso'] ?? '';
$fecha_inicio  = $_GET['fecha_inicio'] ?? '';
$fecha_fin     = $_GET['fecha_fin'] ?? '';

// Detectar si el usuario ha intentado realizar una búsqueda
$busqueda_activa = ($filtro_nc !== '' || $filtro_curso !== '' || $fecha_inicio !== '' || $fecha_fin !== '');

$params = [];
$types  = "";
$result = null;

if ($busqueda_activa) {
    $sql = "SELECT * FROM VERANO WHERE 1=1";

    if ($filtro_nc !== "") {
        $sql    .= " AND numero_control LIKE ?";
        $params[] = "%" . $filtro_nc . "%";
        $types  .= "s";
    }

    if ($filtro_curso !== "") {
        $sql    .= " AND TRIM(curso_interes) LIKE ?";
        $params[] = "%" . $filtro_curso . "%";
        $types  .= "s";
    }

    // Lógica de rango de fechas
    if ($fecha_inicio !== "" && $fecha_fin !== "") {
        $sql    .= " AND DATE(fecha_registro) BETWEEN ? AND ?";
        $params[] = $fecha_inicio;
        $params[] = $fecha_fin;
        $types  .= "ss";
    } elseif ($fecha_inicio !== "") {
        $sql    .= " AND DATE(fecha_registro) >= ?";
        $params[] = $fecha_inicio;
        $types  .= "s";
    } elseif ($fecha_fin !== "") {
        $sql    .= " AND DATE(fecha_registro) <= ?";
        $params[] = $fecha_fin;
        $types  .= "s";
    }

    $sql .= " ORDER BY curso_interes ASC, representante_1 ASC, representante_2 ASC, nombre ASC";

    $stmt = $conexion->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Para el select de cursos siempre necesitamos los datos
$cursos = $conexion->query("SELECT DISTINCT curso_interes FROM VERANO ORDER BY curso_interes");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <title>ITCH - Listas Preregistro Verano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/Avisos.css">
</head>
<body>

<div class="container py-5">
    <div class="card bg-dark border-secondary p-4 shadow-lg">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-light">Preregistro cursos de Verano</h3>
            <a href="panel_docente.php" class="btn btn-outline-primary fw-bold">
                <i class="bi bi-arrow-left-circle"></i> Volver al Panel
            </a>
        </div>

        <form class="row g-3 mb-4 align-items-end" method="GET">
            <div class="col-md-2">
                <label class="text-light fw-bold mb-1 small">N° Control:</label>
                <input type="text" name="nc" class="form-control" value="<?= htmlspecialchars($filtro_nc) ?>" placeholder="Ej: 19390015">
            </div>

            <div class="col-md-3">
                <label class="text-light fw-bold mb-1 small">Curso:</label>
                <select name="curso" class="form-select">
                    <option value="">Seleccione un Curso</option>
                    <?php while($c = $cursos->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['curso_interes']) ?>" <?= ($filtro_curso == $c['curso_interes']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['curso_interes']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="text-light fw-bold mb-1 small">Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fecha_inicio) ?>">
            </div>

            <div class="col-md-2">
                <label class="text-light fw-bold mb-1 small">Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fecha_fin) ?>">
            </div>

            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-info fw-bold flex-grow-1">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="preregistro.php" class="btn btn-outline-secondary" title="Limpiar Filtros">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
                <button type="button" 
                        onclick="<?= ($result && $result->num_rows > 0) ? "window.location.href='exportar_excel_preregistro.php?".http_build_query($_GET)."'" : "alertaNoDatos()" ?>" 
                        class="btn btn-success fw-bold" title="Exportar a Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle border-secondary">
                <thead class="table-secondary text-dark">
                    <tr>
                        <th>Curso</th>
                        <th>Rep 1</th>
                        <th>Rep 2</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Celular</th>
                        <th>No. Control</th>
                        <th>Carrera</th>
                        <th>Semestre</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if(!$busqueda_activa): ?>
                        <tr>
                            <td colspan="9" class="text-center p-5 text-light">
                                <i class="bi bi-funnel fs-1 d-block mb-2 text-light"></i> Seleccione los filtros y presione "Filtrar" para ver los datos.
                            </td>
                        </tr>
                    <?php elseif($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-light"><?= htmlspecialchars($row['curso_interes']) ?></td>
                            <td><?= htmlspecialchars($row['representante_1']) ?></td>
                            <td><?= htmlspecialchars($row['representante_2']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['apellidos']) ?></td>
                            <td class="text-light"><?= htmlspecialchars($row['numero_celular']) ?></td>
                            <td class="text-warning"><?= htmlspecialchars($row['numero_control']) ?></td>
                            <td><?= htmlspecialchars($row['carrera']) ?></td>
                            <td><span class="badge bg-primary text-light"><?= htmlspecialchars($row['semestre']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center p-5 text-light">
                                <i class="bi bi-folder-x fs-2 d-block mb-2"></i> No se encontraron registros con los criterios seleccionados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function alertaNoDatos() {
        Swal.fire({
            icon: 'info',
            title: 'Sin datos',
            text: 'No hay registros disponibles para exportar. Realice una búsqueda primero.',
            confirmButtonColor: '#198754',
            background: '#1e293b',
            color: '#fff'
        });
    }
</script>
</body>
</html>