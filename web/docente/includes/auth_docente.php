<?php
// includes/auth_docente.php

// 1. Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Validar:
// - ¿Existe usuario_id? (Sesión iniciada)
// - ¿Existe el rol de docente? (Identificar si es docente)
// NOTA: Ajusta 'rol' por el nombre de la variable que usas al loguearte (ej: 'tipo', 'nivel')
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    
    // Si no es docente, lo enviamos al login
    // IMPORTANTE: Asegúrate de que la ruta hacia login.php sea correcta desde donde se llame
    header("Location: ../admin/login.php");
    
    // EXIT es crucial: detiene el script para que no se siga cargando nada de la página
    exit();
}
?>