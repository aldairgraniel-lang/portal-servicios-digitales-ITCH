<?php
include(__DIR__ . "/../conexion.php");

// 1. CAPTURA DE FILTROS DESDE LA URL
$nc = $_GET['nc'] ?? '';
$tipo_tramite = $_GET['tipo'] ?? '';
$fecha_filtro = $_GET['fecha'] ?? '';

// 2. REPLICAR CONSULTA FILTRADA (Se eliminó numero_celular)
$sql = "SELECT n_control, nombre, tipo_tramite, nombre_archivo_aceptacion, fecha_registro 
        FROM solicitudes_cartas_terminacion WHERE 1=1";
$params = [];
$types = "";

if (!empty($nc)) {
    $sql .= " AND n_control LIKE ?";
    $params[] = "%" . $nc . "%";
    $types .= "s";
}
if (!empty($tipo_tramite)) {
    $sql .= " AND tipo_tramite = ?";
    $params[] = $tipo_tramite;
    $types .= "s";
}
if (!empty($fecha_filtro)) {
    $sql .= " AND DATE(fecha_registro) = ?";
    $params[] = $fecha_filtro;
    $types .= "s";
}

// Agrupamos por tipo (Residencia vs Servicio) y luego por fecha
$sql .= " ORDER BY tipo_tramite ASC, fecha_registro DESC";

$stmt = $conexion->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// 3. DEFINIR NOMBRE DINÁMICO DEL ARCHIVO
$label_tipo = empty($tipo_tramite) ? 'GENERAL' : str_replace(' ', '_', $tipo_tramite);
$fecha_hoy = date('d-m-Y');
$filename = "cartas_terminacion_{$label_tipo}_{$fecha_hoy}.xls";

// 4. CABECERAS DE EXCEL
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// BOM para que Excel reconozca acentos en español (UTF-8)
echo "\xEF\xBB\xBF";

echo "<table border='1'>";
echo "<thead>
        <tr style='background-color: #1B396A; color: white;'>
            <th colspan='5' style='font-size: 16px;'>REPORTE DE CARTAS DE TERMINACIÓN - " . strtoupper($label_tipo) . "</th>
        </tr>
        <tr style='background-color: #f2f2f2;'>
            <th>N° Control</th>
            <th>Nombre del Alumno</th>
            <th>Tipo de Trámite</th>
            <th>Archivo Referencia</th>
            <th>Fecha de Registro</th>
        </tr>
      </thead>
      <tbody>";

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        // CAMBIO AQUÍ: Extraer únicamente la fecha omitiendo la hora (Formato Día/Mes/Año)
        $fecha_sin_hora = date("d/m/Y", strtotime($row['fecha_registro']));

        echo "<tr>";
        echo "<td>" . $row['n_control'] . "</td>";
        echo "<td>" . mb_convert_encoding($row['nombre'], 'UTF-8') . "</td>";
        echo "<td>" . $row['tipo_tramite'] . "</td>";
        echo "<td>" . $row['nombre_archivo_aceptacion'] . "</td>";
        echo "<td>" . $fecha_sin_hora . "</td>"; // Imprime la fecha limpia sin hora
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No hay datos disponibles con los filtros aplicados.</td></tr>";
}

echo "</tbody></table>";
exit;
?>