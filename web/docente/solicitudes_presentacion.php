<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. OBTENER FILTROS
$nc = $_GET['nc'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$ajuste_horas = "6"; // Horas de ajuste

// Determinar si hay una búsqueda activa
$busqueda_activa = (!empty($nc) || !empty($fecha_inicio) || !empty($fecha_fin));

// 2. CONSTRUCCIÓN FLEXIBLE DEL WHERE
$condiciones = [];

if (!empty($nc)) {
    $condiciones[] = "numero_control LIKE '%" . mysqli_real_escape_string($conexion, $nc) . "%'";
}

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    // Filtro por rango usando DATE_SUB para ajustar la zona horaria de la DB
    $condiciones[] = "DATE(DATE_SUB(fecha_registro, INTERVAL $ajuste_horas HOUR)) BETWEEN '" . mysqli_real_escape_string($conexion, $fecha_inicio) . "' AND '" . mysqli_real_escape_string($conexion, $fecha_fin) . "'";
} elseif (!empty($fecha_inicio)) {
    $condiciones[] = "DATE(DATE_SUB(fecha_registro, INTERVAL $ajuste_horas HOUR)) >= '" . mysqli_real_escape_string($conexion, $fecha_inicio) . "'";
} elseif (!empty($fecha_fin)) {
    $condiciones[] = "DATE(DATE_SUB(fecha_registro, INTERVAL $ajuste_horas HOUR)) <= '" . mysqli_real_escape_string($conexion, $fecha_fin) . "'";
}

$where = (count($condiciones) > 0) ? " WHERE " . implode(" AND ", $condiciones) : "";

// 3. LÓGICA DE DESCARGA (Solo si hay búsqueda activa)
if ($busqueda_activa && (isset($_GET['descargar_zip']) || isset($_GET['exportar_word']))) {
    $query_download = "SELECT * FROM solicitudes_cartas_presentacion $where ORDER BY tipo_tramite, fecha_registro DESC";
    $result_download = mysqli_query($conexion, $query_download);

    if (mysqli_num_rows($result_download) === 0) {
        header("Location: ?alerta=vacio");
        exit;
    }

    if (isset($_GET['descargar_zip'])) {
        $zipName = "Expedientes_cartas_" . date('d_m_Y') . ".zip";
        $rutaCarpeta = realpath(__DIR__ . '/../uploads/cartas/') . DIRECTORY_SEPARATOR;
        $zip = new ZipArchive();
        if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            while ($reg = mysqli_fetch_assoc($result_download)) {
                $nombreArchivo = trim(basename($reg['archivo_pdf']));
                $rutaCompleta = $rutaCarpeta . $nombreArchivo;
                if (file_exists($rutaCompleta)) {
                    $tipoCarpeta = str_replace([' ', '/', '\\'], '_', $reg['tipo_tramite']);
                    $nombreEnZip = $tipoCarpeta . "/" . $reg['numero_control'] . "_" . str_replace(' ', '_', $reg['nombre']) . ".pdf";
                    $zip->addFile($rutaCompleta, $nombreEnZip);
                }
            }
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $zipName);
            header('Content-Length: ' . filesize($zipName));
            readfile($zipName);
            unlink($zipName); 
            exit;
        }
    }

    if (isset($_GET['exportar_word'])) {
        $nombreArchivo = "Lista_Presentacion_" . date('d_m_Y') . ".doc";
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
        echo "<html><meta charset='UTF-8'><body><h3>Solicitudes Cartas de Presentación</h3><table border='1' style='border-collapse:collapse; width:100%'><tr><th>N. Control</th><th>Nombre</th><th>Trámite</th></tr>";
        while($r = mysqli_fetch_assoc($result_download)) {
            echo "<tr><td>{$r['numero_control']}</td><td>{$r['nombre']}</td><td>{$r['tipo_tramite']}</td></tr>";
        }
        echo "</table></body></html>";
        exit;
    }
}

// 4. CONSULTA PARA TABLA (Solo si hay búsqueda activa)
$result = null;
$hayResultados = false;

if ($busqueda_activa) {
    $query = "SELECT *, DATE_SUB(fecha_registro, INTERVAL $ajuste_horas HOUR) as fecha_corregida FROM solicitudes_cartas_presentacion $where ORDER BY fecha_registro DESC";
    $result = mysqli_query($conexion, $query);
    $hayResultados = ($result && mysqli_num_rows($result) > 0);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/tablasCSS.css">
    <title>ITCH - Listas Presentación</title>
</head>
<body>
    <div class="container py-5">
        <div class="card bg-dark border-secondary p-4 text-white shadow-lg" style="border-radius: 15px; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold"><i class="bi bi-journal-check me-2 text-info"></i>Solicitudes : Cartas de presentación</h3>
                <a href="panel_docente.php" class="btn btn-outline-primary fw-bold">
                <i class="bi bi-arrow-left-circle"></i> Volver al Panel</a>
            </div>

            <form method="GET" class="row g-3 mb-4 p-3 rounded border border-secondary" style="background: rgba(255,255,255,0.05);">
                <div class="col-md-3">
                    <label class="small text-info">N° de Control</label>
                    <input type="text" name="nc" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($nc); ?>" placeholder="Ej: 19390015">
                </div>
                <div class="col-md-3">
                    <label class="small text-info">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                </div>
                <div class="col-md-3">
                    <label class="small text-info">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-info w-100 fw-bold" title="Buscar ahora" ><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="?" class="btn btn-outline-light"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
                <div class="col-md-12 d-flex gap-2 mt-2">
                    <button type="button" onclick="verificarExport('exportar_word')" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-file-word"></i> Exportar Word</button>
                    <button type="button" onclick="verificarExport('descargar_zip')" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-file-zip"></i> Descargar ZIP</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr class="text-info">
                            <th>Fecha</th>
                            <th>N° Control</th>
                            <th>Estudiante</th>
                            <th>Trámite</th>
                            <th class="text-center">PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$busqueda_activa): ?>
                            <tr>
                                <td colspan="5" class="text-center p-5 text-light">
                                    <i class="bi bi-funnel fs-1 d-block mb-2 text-light"></i> Seleccione los filtros y presione "Filtrar" para ver los datos.
                                </td>
                            </tr>
                        <?php elseif ($hayResultados): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime($row['fecha_corregida'])); ?></td>
                                <td class="fw-bold text-warning"><?php echo htmlspecialchars($row['numero_control']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><span class="badge bg-primary text-light"><?php echo htmlspecialchars($row['tipo_tramite']); ?></span></td>
                                <td class="text-center">
                                    <a href="descargar_presentacion_archivo_pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center p-5 text-light">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2 text-warning"></i> No se encontraron registros con esos filtros.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const hayResultados = <?php echo $hayResultados ? 'true' : 'false'; ?>;
        const busquedaActiva = <?php echo $busqueda_activa ? 'true' : 'false'; ?>;

        function verificarExport(tipo) {
            if (!busquedaActiva) {
                Swal.fire({
                    icon: 'info',
                    title: 'Atención',
                    text: 'Debe realizar una búsqueda primero antes de exportar.',
                    confirmButtonColor: '#0dcaf0',
                    background: '#1e293b',
                    color: '#fff'
                });
                return;
            }
            if (!hayResultados) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos',
                    text: 'No hay registros para exportar con los filtros seleccionados.',
                    confirmButtonColor: '#0dcaf0',
                    background: '#1e293b',
                    color: '#fff'
                });
            } else {
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set(tipo, '1');
                window.location.href = "?" + urlParams.toString();
            }
        }
    </script>
</body>
</html>