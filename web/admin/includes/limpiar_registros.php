<?php
include("../../conexion.php");

// Mapa centralizado de servicios
$servicios_map = [
    'VERANO' => 'Curso de Verano',
    'registro_ingles' => 'Constancias de Inglés',
    'solicitudes_cartas_presentacion' => 'Cartas de Presentación',
    'solicitudes_cartas_aceptacion' => 'Cartas de Aceptación',
    'solicitudes_cartas_terminacion' => 'Cartas de Terminación',
    'justificantes' => 'Justificantes',
    'avisos' => 'Avisos'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabla = $_POST['tabla'];
    $inicio = $_POST['fecha_inicio'] . ' 00:00:00';
    $fin = $_POST['fecha_fin'] . ' 23:59:59';

    // Validación de seguridad
    if (!array_key_exists($tabla, $servicios_map)) {
        die("Error: Servicio no válido.");
    }

    // Ejecución segura
    $stmt = $conexion->prepare("DELETE FROM $tabla WHERE fecha_registro BETWEEN ? AND ?");
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
        $nombre = $servicios_map[$tabla];
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