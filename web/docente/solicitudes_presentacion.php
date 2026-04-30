<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. OBTENER FILTROS
$nc = $_GET['nc'] ?? '';
$fecha_filtro = $_GET['fecha_filtro'] ?? '';
$ajuste_horas = "INTERVAL 6 HOUR";

// 2. CONSTRUCCIÓN FLEXIBLE DEL WHERE
$condiciones = [];

if (!empty($nc)) {
    $condiciones[] = "numero_control LIKE '%" . mysqli_real_escape_string($conexion, $nc) . "%'";
}

if (!empty($fecha_filtro)) {
    $condiciones[] = "DATE(DATE_SUB(fecha_registro, $ajuste_horas)) = '" . mysqli_real_escape_string($conexion, $fecha_filtro) . "'";
}

$where = (count($condiciones) > 0) ? " WHERE " . implode(" AND ", $condiciones) : "";

// 3. LÓGICA DE DESCARGA
if (isset($_GET['descargar_zip']) || isset($_GET['exportar_word'])) {
    $query_download = "SELECT * FROM solicitudes_cartas_presentacion $where ORDER BY tipo_tramite, fecha_registro DESC";
    $result_download = mysqli_query($conexion, $query_download);

    if (mysqli_num_rows($result_download) === 0) {
        header("Location: ?alerta=vacio");
        exit;
    }

    if (isset($_GET['descargar_zip'])) {
        $zipName = "Expedientes_cartas_presentacion_" . date('d_m_Y') . ".zip";
        $rutaCarpeta = realpath(__DIR__ . '/../uploads/cartas/') . DIRECTORY_SEPARATOR;

        $zip = new ZipArchive();
        if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            while ($reg = mysqli_fetch_assoc($result_download)) {
                $nombreArchivo = trim(basename($reg['archivo_pdf']));
                $rutaCompleta = $rutaCarpeta . $nombreArchivo;
                if (file_exists($rutaCompleta)) {
                    $tipoCarpeta = str_replace([' ', '/', '\\'], '_', $reg['tipo_tramite']);
                    $nombreEnZip = $tipoCarpeta . "/" . $reg['numero_control'] . "_" . str_replace(' ', '_', $reg['nombre_estudiante']) . ".pdf";
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
        $nombreArchivo = "Lista_Cartas_Presentacion_" . date('d_m_Y') . ".doc";
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
        echo "<html><meta charset='UTF-8'><body><h3>Solicitudes Cartas de Presentación</h3><table border='1' style='border-collapse:collapse; width:100%'><tr><th>N. Control</th><th>Nombre</th><th>Trámite</th></tr>";
        while($r = mysqli_fetch_assoc($result_download)) {
            echo "<tr><td>{$r['numero_control']}</td><td>{$r['nombre_estudiante']}</td><td>{$r['tipo_tramite']}</td></tr>";
        }
        echo "</table></body></html>";
        exit;
    }
}

// 4. CONSULTA PARA TABLA
$query = "SELECT *, DATE_SUB(fecha_registro, $ajuste_horas) as fecha_corregida FROM solicitudes_cartas_presentacion $where ORDER BY fecha_registro DESC";
$result = mysqli_query($conexion, $query);
$hayResultados = ($result && mysqli_num_rows($result) > 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>ITCH - Listas Presentación</title>
</head>
<style>
        .form-control, .form-select { background-color: #1e293b !important; color: white !important; border: 1px solid #475569 !important; }
        .form-control:focus { border-color: #0dcaf0 !important; box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25) !important; }
        .form-control::placeholder {
    color: #ffffff !important; /* Un gris claro visible sobre el fondo #1e293b */
    opacity: 1; /* Asegura que no se aplique transparencia extra */
}

</style>
<body style="background: #021936; color: white;">
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
                    <div class="input-group">
                        <input type="text-light" name="nc" class="form-control bg-dark border-secondary" value="<?php echo htmlspecialchars($nc); ?>" placeholder="Ej: 19390015">
                        <button type="submit" class="btn btn-info"><i class="bi bi-search"></i></button>
                        <a href="?" class="btn btn-outline-light w-70"><i class="bi bi-arrow-clockwise"></i></a>

                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small text-info">Fecha de Registro</label>
                    <input type="date" name="fecha_filtro" class="form-control bg-dark text-white border-secondary" value="<?php echo htmlspecialchars($fecha_filtro); ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="button" onclick="verificarExport('exportar_word')" class="btn btn-primary w-30 fw-bold"><i class="bi bi-file-word"></i> Word</button>
                    <button type="button" onclick="verificarExport('descargar_zip')" class="btn btn-warning w-30 fw-bold"><i class="bi bi-file-zip"></i> ZIP</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead><tr class="text-info"><th>Fecha</th><th>N° Control</th><th>Estudiante</th><th>Trámite</th><th class="text-center">PDF</th></tr></thead>
                    <tbody>
                        <?php if ($hayResultados): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime($row['fecha_corregida'])); ?></td>
                                <td class="fw-bold text-warning"><?php echo htmlspecialchars($row['numero_control']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_estudiante']); ?></td>
                                <td><span class="badge bg-primary text-light"><?php echo htmlspecialchars($row['tipo_tramite']); ?></span></td>
                                <td class="text-center"><a href="descargar_presentacion_archivo_pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success"><i class="bi bi-file-pdf"></i></a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center p-5 text-light">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2 text-light"></i> No se encontraron registros.
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

        function verificarExport(tipo) {
            if (!hayResultados) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin datos',
                    text: 'No hay registros disponibles para exportar con los filtros actuales.',
                    confirmButtonColor: '#0dcaf0'
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