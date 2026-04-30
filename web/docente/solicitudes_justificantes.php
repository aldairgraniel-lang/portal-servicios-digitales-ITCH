<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. OBTENER FILTROS
$fecha = $_GET['fecha'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$nc = $_GET['nc'] ?? ''; // Nuevo filtro de control

// Obtener motivos únicos
$query_motivos = "SELECT DISTINCT motivo FROM justificantes WHERE motivo IS NOT NULL AND motivo != '' ORDER BY motivo ASC";
$res_motivos = $conexion->query($query_motivos);

// 2. CONSTRUCCIÓN DE CONSULTA
$sql = "SELECT * FROM justificantes WHERE 1=1";
$params = [];
$types = "";

if (!empty($fecha)) {
    $sql .= " AND DATE(fecha_registro) = ?";
    $params[] = $fecha;
    $types .= "s";
}
if (!empty($tipo)) {
    $sql .= " AND motivo = ?";
    $params[] = $tipo;
    $types .= "s";
}
// Filtro N° Control
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta charset="UTF-8">
    <title>ITCH - Listas Justificantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --bg-dark: #021936; --card-bg: #021936; --text-primary: #f8fafc; }
        body { background: #021936; color: var(--text-primary); min-height: 100vh; font-family: 'Inter', sans-serif; }
        .card-panel { background: #333333; border-radius: 20px; padding: 30px; }
        .table { color: var(--text-primary) !important; }
        .btn-descargar { background: #3b82f6 !important; color: white !important; }
        .btn-expedir { background: #10b981 !important; color: white !important; }
        
body { background: #021936; color: white; font-family: 'Inter', sans-serif; }
        .form-control, .form-select { background-color: #1e293b !important; color: white !important; border: 1px solid #475569 !important; }
        .form-control:focus { border-color: #0dcaf0 !important; box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25) !important; }
        .form-control::placeholder {
    color: #ffffff !important; /* Un gris claro visible sobre el fondo #1e293b */
    opacity: 1; /* Asegura que no se aplique transparencia extra */
}

    </style>
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
                <div class="input-group">
                    <input type="text" name="nc" class="form-control" placeholder="Ej: 19390015" value="<?= htmlspecialchars($nc) ?>">
                    <button type="submit" class="btn btn-info"><i class="bi bi-search"></i></button>
                        <a href="solicitudes_justificantes.php" class="btn btn-outline-light w-30"><i class="bi bi-arrow-clockwise"></i></a>
                
                </div>
            </div>

            <div class="col-md-3">
                <label class="small text-info">Fecha</label>
                <input type="date" name="fecha" class="form-control" onchange="this.form.submit()" value="<?= htmlspecialchars($fecha) ?>">
            </div>

            <div class="col-md-3">
                <label class="small text-info">Motivo</label>
                <select name="tipo" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Todos --</option>
                    <?php while($m = $res_motivos->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($m['motivo']) ?>" <?= ($tipo == $m['motivo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['motivo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            
        </form>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr><th>Fecha</th><th>Nombre</th><th>N° Control</th><th>Motivo</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): while($row = $resultado->fetch_assoc()): ?>
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
                    <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-5 text-light">
                                <i class="bi bi-folder-x fs-2 d-block mb-2"></i> No se encontraron registros.
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