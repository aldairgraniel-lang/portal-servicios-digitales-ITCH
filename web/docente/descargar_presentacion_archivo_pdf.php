<?php
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();

include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");

if (isset($_GET['id'])) {
    // Uso de sentencia preparada para seguridad
    $stmt = $conexion->prepare("SELECT archivo_pdf, nombre_estudiante, numero_control FROM solicitudes_cartas_presentacion WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $nombreArchivo = trim(basename($row['archivo_pdf']));
        $rutaCompleta = "/var/www/html/uploads/cartas/" . $nombreArchivo;

        if (!empty($nombreArchivo) && file_exists($rutaCompleta)) {
            
            $size = filesize($rutaCompleta);
            $nombreLimpio = $row['numero_control'] . "_" . str_replace(' ', '_', $row['nombre_estudiante']) . ".pdf";

            // 2. Limpieza total de búferes
            while (ob_get_level()) {
                ob_end_clean();
            }

            // 3. Cabeceras profesionales para archivos pesados
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $nombreLimpio . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $size);

            // 4. LECTURA POR TROZOS
            $chunkSize = 8192; 
            $handle = fopen($rutaCompleta, 'rb');
            
            if ($handle === false) {
                exit("Error al abrir el archivo.");
            }

            while (!feof($handle)) {
                echo fread($handle, $chunkSize);
                flush(); 
            }
            
            fclose($handle);
            exit;
        }
    }
}

echo "<script>alert('Error: Archivo no encontrado.'); window.history.back();</script>";
?>