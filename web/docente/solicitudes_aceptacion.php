<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// Captura de Filtros
$filtro_tipo  = isset($_GET['tipo_tramite']) ? trim($_GET['tipo_tramite']) : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$filtro_nc    = isset($_GET['nc']) ? trim($_GET['nc']) : '';

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
if (!empty($filtro_fecha)) {
    $query .= " AND DATE(fecha_registro) = ?";
    $params[] = $filtro_fecha;
    $types .= "s";
}

$stmt = $conexion->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta charset="UTF-8">
    <title>ITCH - Listas Aceptaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
    <main class="container mt-5">
        <div class="card bg-dark border-secondary p-4 text-white shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold"><i class="bi bi-journal-check me-2 text-info"></i> Solicitudes: Cartas de Aceptación</h3>
                <a href="panel_docente.php" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-arrow-left-circle"></i> Volver al Panel
                </a>
            </div>

            <form method="GET" class="row g-3 mb-5 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-white fw-bold">N° Control</label>
                    <div class="input-group">
                        <input type="text" name="nc" class="form-control" placeholder="Ej: 19390015" value="<?= htmlspecialchars($filtro_nc) ?>">
                        <button type="submit" class="btn btn-info"><i class="bi bi-search"></i></button>
                            <a href="solicitudes_aceptacion.php" class="btn btn-outline-secondary w-70">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white fw-bold">Tipo de Trámite</label>
                    <select name="tipo_tramite" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="RESIDENCIA PROFESIONAL" <?= ($filtro_tipo === 'RESIDENCIA PROFESIONAL') ? 'selected' : '' ?>>RESIDENCIA PROFESIONAL</option>
                        <option value="SERVICIO SOCIAL" <?= ($filtro_tipo === 'SERVICIO SOCIAL') ? 'selected' : '' ?>>SERVICIO SOCIAL</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-white fw-bold">Fecha de Registro</label>
                    <input type="date" name="fecha" class="form-control" onchange="this.form.submit()" value="<?= htmlspecialchars($filtro_fecha) ?>">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    
                    <?php 
                    $url_descarga = "descargar_aceptacion.php?" . http_build_query($_GET);
                    if (!empty($resultados)) {
                        echo '<a href="'.$url_descarga.'" class="btn btn-success w-30 fw-bold"><i class="bi bi-file-earmark-excel"></i> Excel</a>';
                    } else {
                        echo '<button type="button" onclick="alertaNoDatos()" class="btn btn-success w-30 fw-bold"><i class="bi bi-file-earmark-excel"></i> Excel</button>';
                    }
                    ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Número de Control</th>
                            <th>Tipo de Trámite</th>
                            <th>Referencia Archivo</th>
                            <th>Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resultados) > 0): ?>
                            <?php foreach ($resultados as $row): ?>
                                <tr>
                                    <td class="text-warning fw-bold"><?= htmlspecialchars($row['numero_control']) ?></td>
                                    <td><?= htmlspecialchars($row['tipo_tramite']) ?></td>
                                    <td><span class="badge bg-primary text-light"><?= htmlspecialchars($row['archivo_pdf']) ?></span></td>
                                    <td><?= date("d/m/Y", strtotime($row['fecha_registro'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center p-5 text-light">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i> No se encontraron registros.
                                </td>
                            </tr>                            
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function alertaNoDatos() { 
            Swal.fire({ 
                icon: 'info', 
                title: 'Sin datos', 
                text: 'No hay registros disponibles para descargar.', 
                confirmButtonColor: '#198754',
                background: '#1e293b',
                color: '#fff',
                backdrop: false
            }); 
        }
    </script>
</body>
</html>