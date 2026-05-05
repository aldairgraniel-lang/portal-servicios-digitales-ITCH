<?php
$host = getenv("DB_HOST") ?: "db";
$usuario = getenv("DB_USER") ?: "user";
$password = getenv("DB_PASS") ?: "user";
$base_datos = getenv("DB_NAME") ?: "servicios";

$conexion = null;
$intentos = 10;

while ($intentos > 0) {
    $conexion = new mysqli($host, $usuario, $password, $base_datos);

    if (!$conexion->connect_error) {
        $conexion->set_charset("utf8mb4");
        $conexion->query("SET NAMES utf8mb4");
        $conexion->query("SET CHARACTER SET utf8mb4");
        
        // Configurar la zona horaria aquí mismo, solo cuando la conexión es exitosa
        $conexion->query("SET time_zone = '-06:00'");
        
        break;
    }

    $intentos--;
    sleep(2);
}

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
// ... después de tu $conexion = new mysqli(...);

?>