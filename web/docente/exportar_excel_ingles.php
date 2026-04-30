<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();
include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

$filtro = $_GET['periodo'] ?? '';

// 1. ACTUALIZADO: Consulta SQL incluyendo los nuevos campos
$sql = "SELECT numero_control, nombre, carrera, periodo, tipo_alumno, semestre FROM registro_ingles";
if ($filtro != '') {
    $sql .= " WHERE periodo = '" . $conexion->real_escape_string($filtro) . "'";
}
$sql .= " ORDER BY nombre ASC";

$resultado = $conexion->query($sql);

// --- ALERTA CON ESTILO "GLASS" (Sin cambios) ---
if (!$resultado || $resultado->num_rows === 0) {
    die("
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css'>
            <style>
                body { background: #0f172a; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Inter', sans-serif; }
                .tarjeta-glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(15px); padding: 3rem; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            </style>
        </head>
        <body>
            <div class='tarjeta-glass'>
                <i class='bi bi-folder-x' style='font-size: 4rem; color: #fbbf24;'></i>
                <h3 class='mt-3'>¡Sin registros!</h3>
                <p class='text-secondary'>No hay datos disponibles para exportar con los criterios seleccionados.</p>
                <a href='javascript:history.back()' class='btn btn-outline-light w-100 mt-3'>Volver atrás</a>
            </div>
        </body>
        </html>
    ");
}

// Si llega aquí, es porque SÍ hay datos
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=Lista_Solicitudes_contancias_Ingles_' . date('d-m-Y') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');
echo "\xEF\xBB\xBF"; // Marca de orden de bytes para UTF-8
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1">
    <thead>
        <tr style="background-color: #1B396A; color: white; font-weight: bold;">
            <th colspan="6" style="font-size: 16px; height: 30px;">REPORTE DE SOLICITUDES DE CONSTANCIA DE INGLÉS - ITCH</th>
        </tr>
        <tr style="background-color: #9D843E; color: white;">
            <th>No. de Control</th>
            <th>Nombre del Alumno</th>
            <th>Carrera</th>
            <th>Periodo</th>
            <th>Tipo</th>
            <th>Semestre</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        while ($row = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['numero_control']) . "</td>";
            echo "<td>" . htmlspecialchars(mb_strtoupper($row['nombre'], 'UTF-8')) . "</td>";
            echo "<td>" . htmlspecialchars($row['carrera']) . "</td>";
            echo "<td>" . htmlspecialchars($row['periodo']) . "</td>";
            // 3. ACTUALIZADO: Imprimir nuevos datos
            echo "<td>" . htmlspecialchars($row['tipo_alumno']) . "</td>";
            echo "<td>" . htmlspecialchars($row['semestre'] ?? '') . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>