<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

$filtro_nc   = isset($_GET['nc']) ? trim($_GET['nc']) : '';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Lógica de filtrado
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Constancias</title>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #021936; color: white; font-family: 'Inter', sans-serif; }
        .card-glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border-radius: 15px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .form-control { background-color: #1e293b !important; color: white !important; border: 1px solid #475569 !important; }
        .form-control::placeholder { color: #ffffff !important; opacity: 1; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card bg-dark border-secondary p-4 text-white shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-file-earmark-text me-2"></i> constancias por alumno.</h3>
            <a href="solicitudes_ingles.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Regresar</a>
        </div>

        <form method="GET" class="row g-3 mb-4 align-items-end">
            <div class="col-md-5">
                <label class="fw-bold">N° Control:</label>
                <div class="input-group">
                    <input type="text" name="nc" class="form-control" placeholder="ej: 19390015" value="<?= htmlspecialchars($filtro_nc) ?>">
                    <button type="submit" class="btn btn-info"><i class="bi bi-search"></i></button>
                    <a href="<?= basename($_SERVER['PHP_SELF']) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </div>

            <div class="col-md-4">
                <label class="fw-bold">Situación Académica:</label>
                <select name="tipo" class="form-control" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="Cursando semestre" <?= $filtro_tipo == 'Cursando semestre' ? 'selected' : '' ?>>Cursando Semestre</option>
                    <option value="Egresado" <?= $filtro_tipo == 'Egresado' ? 'selected' : '' ?>>Egresado</option>
                </select>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>N° Control</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($hayResultados): while($a = $alumnos->fetch_assoc()): ?>
                    <tr>
                        <td class="text-warning fw-bold"><?= htmlspecialchars($a['numero_control']) ?></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td><?= $a['tipo_alumno'] == 'Cursando semestre' ? 'Cursando Semestre' : 'Egresado' ?></td>
                        <td>

                    <a href="imprimir_constancia_ingles.php?nc=<?= urlencode($a['numero_control']) ?>" 
                    class="btn btn-sm btn-primary" 
                    target="_blank">
                        <i class="bi bi-printer"></i> Generar PDF
                    </a>                       
                </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="text-center p-4 text-light"><i class="bi bi-folder"></i> No se encontraron registros.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>