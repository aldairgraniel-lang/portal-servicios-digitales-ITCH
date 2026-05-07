<?php
// Configuración de zona horaria y sesión
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

// 1. Captura de filtros
$filtro_tipo = isset($_GET['tipo_tramite']) ? trim($_GET['tipo_tramite']) : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// 2. Consulta SQL
$query = "SELECT numero_control, tipo_tramite, archivo_pdf 
          FROM solicitudes_cartas_aceptacion WHERE 1=1";
$params = [];
$types = "";

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

// 3. NOMBRE DEL ARCHIVO
$nombre_archivo = "Lista_Cartas_Aceptacion_" . date('Y-m-d') . ".xls";

// 4. Cabeceras para Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; 
?>
<link rel="stylesheet" href="css/S_aceptacion.css">

<div class="meta-info">
    Fecha de reporte: <?php echo date('d/m/Y H:i:s'); ?>
</div>

<table>
    <thead>
        <tr class="header">
            <th>Número de Control</th>
            <th>Tipo de Trámite</th>
            <th>Nombre del Archivo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($resultados as $row): 
            $nombre_limpio = str_replace('REFERENCIA_PREVIA: ', '', $row['archivo_pdf']);
        ?>
        <tr>
            <td class="border"><?php echo htmlspecialchars($row['numero_control']); ?></td>
            <td class="border"><?php echo htmlspecialchars($row['tipo_tramite']); ?></td>
            <td class="border"><?php echo htmlspecialchars($nombre_limpio); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>