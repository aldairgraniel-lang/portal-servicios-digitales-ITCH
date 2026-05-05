<?php
include("../../conexion.php");

// Mapa centralizado de servicios con su respectiva columna de fecha
$servicios_map = [
    'VERANO' => ['nombre' => 'Curso de Verano', 'columna' => 'fecha_registro'],
    'registro_ingles' => ['nombre' => 'Constancias de Inglés', 'columna' => 'fecha_registro'],
    'solicitudes_cartas_presentacion' => ['nombre' => 'Cartas de Presentación', 'columna' => 'fecha_registro'],
    'solicitudes_cartas_aceptacion' => ['nombre' => 'Cartas de Aceptación', 'columna' => 'fecha_registro'],
    'justificantes' => ['nombre' => 'Justificantes', 'columna' => 'fecha_registro'],
    'avisos' => ['nombre' => 'Avisos', 'columna' => 'fecha_pub'], // Columna correcta para avisos
    'representantes' => ['nombre' => 'Representantes', 'columna' => 'fecha_registro']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabla = $_POST['tabla'];
    $inicio = $_POST['fecha_inicio'] . ' 00:00:00';
    $fin = $_POST['fecha_fin'] . ' 23:59:59';

    // Validación de seguridad (evita inyección en la tabla)
    if (!array_key_exists($tabla, $servicios_map)) {
        die("Error: Servicio no válido.");
    }

    // Extraer valores de la configuración de la tabla seleccionada
    $servicio_config = $servicios_map[$tabla];
    $nombre = $servicio_config['nombre'];
    $columna_fecha = $servicio_config['columna'];

    // Ejecución segura utilizando la columna correspondiente a la tabla
    $stmt = $conexion->prepare("DELETE FROM $tabla WHERE $columna_fecha BETWEEN ? AND ?");
    $stmt->bind_param("ss", $inicio, $fin);

    // ESTRUCTURA HTML COMPLETA para evitar el parpadeo blanco y cargar librerías
    echo "<!DOCTYPE html>
    <html>
    <head>
        <style>body { background-color: #212529; }</style>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>";

    if ($stmt->execute()) {
        echo "
        <script>
            Swal.fire({
                title: '¡Éxito!',
                text: 'Los registros de $nombre fueron eliminados correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#3085d6',
                background: '#212529',
                color: '#fff'
            }).then(() => {
                window.location.href = '../inicio.php';
            });
        </script>";
    } else {
        echo "
        <script>
            Swal.fire({
                title: 'Error',
                text: 'No se pudieron eliminar los registros.',
                icon: 'error',
                confirmButtonText: 'Entendido',
                background: '#212529',
                color: '#fff'
            }).then(() => {
                window.location.href = '../inicio.php';
            });
        </script>";
    }
    
    echo "</body></html>";
    $stmt->close();
}
?>