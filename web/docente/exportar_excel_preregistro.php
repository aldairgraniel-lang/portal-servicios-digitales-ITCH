<?php
ob_start();
include(__DIR__ . "/../conexion.php");
include('includes/auth_docente.php'); // <--- Esto ya hace la magia de bloquear accesos
// =========================
// FILTROS
// =========================
$filtro_curso = $_GET['curso'] ?? '';
$filtro_rep = $_GET['representante'] ?? '';

// =========================
// QUERY RESUMEN
// =========================
$sql = "
SELECT 
    curso_interes,
    representante_1,
    representante_2,
    COUNT(*) as total_alumnos
FROM VERANO
WHERE 1=1
";

$params = [];
$types = "";

if ($filtro_curso !== "") {
    $sql .= " AND curso_interes = ?";
    $params[] = $filtro_curso;
    $types .= "s";
}

if ($filtro_rep !== "") {
    $sql .= " AND (representante_1 = ? OR representante_2 = ?)";
    $params[] = $filtro_rep;
    $params[] = $filtro_rep;
    $types .= "ss";
}

$sql .= " GROUP BY curso_interes, representante_1, representante_2 ORDER BY curso_interes ASC";

$stmt = $conexion->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// =========================
// HEADERS EXCEL
// =========================
$fecha_archivo = date('d-m-Y'); // Formato: dia-mes-año_hora-minuto
$nombre_archivo = "Lista_Pre-registro_Verano_" . $fecha_archivo . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
echo "\xEF\xBB\xBF";

// =========================
// TABLA 1: RESUMEN (3 COLUMNAS)
// =========================
echo "<table border='1' style='border-collapse:collapse;width:100%'>";

// TÍTULO
echo "<tr>
<th colspan='3' style='background:#198754;color:white;font-size:18px;text-align:center'>
REPORTE RESUMEN - CURSOS DE VERANO
</th>
</tr>";

// FECHA
echo "<tr><td colspan='3'><b>Fecha:</b> ".date('d/m/Y H:i')."</td></tr>";

// FILTROS
if ($filtro_curso !== "") {
    echo "<tr><td colspan='3'><b>Curso:</b> ".$filtro_curso."</td></tr>";
}
if ($filtro_rep !== "") {
    echo "<tr><td colspan='3'><b>Representante:</b> ".$filtro_rep."</td></tr>";
}

echo "<tr><td colspan='3'></td></tr>";

// ENCABEZADO
echo "<tr style='background:#212529;color:white'>
<th>Curso</th>
<th>Representantes</th>
<th>Total Alumnos</th>
</tr>";

// DATOS RESUMEN
while($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>".htmlspecialchars($row['curso_interes'])."</td>
        <td>".htmlspecialchars($row['representante_1'])." - ".htmlspecialchars($row['representante_2'])."</td>
        <td style='text-align:center;font-weight:bold'>".$row['total_alumnos']."</td>
    </tr>";
}

echo "</table>";

// =========================
// ESPACIO
// =========================
echo "<br><br>";

// =========================
// TABLA 2: DETALLE (6 COLUMNAS)
// =========================

// NUEVA QUERY (DETALLE)
$sql2 = "SELECT * FROM VERANO WHERE 1=1";

$params2 = [];
$types2 = "";

if ($filtro_curso !== "") {
    $sql2 .= " AND curso_interes = ?";
    $params2[] = $filtro_curso;
    $types2 .= "s";
}

if ($filtro_rep !== "") {
    $sql2 .= " AND (representante_1 = ? OR representante_2 = ?)";
    $params2[] = $filtro_rep;
    $params2[] = $filtro_rep;
    $types2 .= "ss";
}

$sql2 .= " ORDER BY curso_interes, representante_1, representante_2, nombre";

$stmt2 = $conexion->prepare($sql2);
if (!empty($params2)) $stmt2->bind_param($types2, ...$params2);
$stmt2->execute();
$result2 = $stmt2->get_result();

// TABLA DETALLE
echo "<table border='1' style='border-collapse:collapse;width:100%'>";

// TÍTULO DETALLE
echo "<tr>
<th colspan='6' style='background:#0d6efd;color:white'>
DETALLE DE ALUMNOS
</th>
</tr>";

$current_grupo = "";

while($row = $result2->fetch_assoc()) {

    $grupo = $row['curso_interes']."|".$row['representante_1']."|".$row['representante_2'];

    if ($grupo !== $current_grupo) {

        echo "<tr style='background:#ddebf7;font-weight:bold'>
            <td colspan='6'>
                Curso: ".htmlspecialchars($row['curso_interes'])."<br>
                Representantes: ".htmlspecialchars($row['representante_1'])." - ".htmlspecialchars($row['representante_2'])."
            </td>
        </tr>";

        echo "<tr style='background:#212529;color:white'>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Celular</th>
            <th>No. Control</th>
            <th>Carrera</th>
            <th>Semestre</th>
        </tr>";

        $current_grupo = $grupo;
    }

    echo "<tr>
        <td>".htmlspecialchars($row['nombre'])."</td>
        <td>".htmlspecialchars($row['apellidos'])."</td>
        <td>".htmlspecialchars($row['numero_celular'])."</td>
        <td>".htmlspecialchars($row['numero_control'])."</td>
        <td>".htmlspecialchars($row['carrera'])."</td>
        <td>".htmlspecialchars($row['semestre'])."</td>
    </tr>";
}

echo "</table>";

exit;