<?php
// includes/limpiar_registros.php

include("../../conexion.php");

// Mapa centralizado de servicios autorizados para la limpieza masiva
$servicios_map = [
    'VERANO' => 'Curso de Verano',
    'registro_ingles' => 'Constancias de Inglés',
    'solicitudes_cartas_presentacion' => 'Cartas de Presentación',
    'solicitudes_cartas_aceptacion' => 'Cartas de Aceptación',
    'solicitudes_cartas_terminacion' => 'Cartas de Terminación',
    'solicitudes_cartas_buena_conducta' => 'Cartas de Buena Conducta', // <-- Agregado al mapa de seguridad
    'justificantes' => 'Justificantes',
    'avisos' => 'Avisos'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tabla = $_POST['tabla'];
    
    // Se extrae la fecha pura del calendario del formulario sin concatenación de timestamps rígidos
    $inicio = $_POST['fecha_inicio'];
    $fin = $_POST['fecha_fin'];

    // Validación de seguridad contra el mapa
    if (!array_key_exists($tabla, $servicios_map)) {
        die("Error: Servicio no válido.");
    }

    // Adaptación dinámica del nombre de la columna de fecha según la estructura de tus tablas
    $columna_fecha = ($tabla === 'solicitudes_cartas_buena_conducta') ? 'fecha_solicitud' : 'fecha_registro';

    // Ejecución segura con Prepared Statements
    $stmt = $conexion->prepare("DELETE FROM $tabla WHERE $columna_fecha BETWEEN ? AND ?");
    $stmt->bind_param("ss", $inicio, $fin);

    // ESTRUCTURA HTML COMPLETA para evitar el parpadeo blanco y cargar librerías
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
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