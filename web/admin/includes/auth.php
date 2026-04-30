<?php

// Si no hay sesión iniciada, la iniciamos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lógica de validación:
// 1. Si no existe la variable de sesión (no está logueado)
// 2. O si existe, pero el rol NO es 'admin' (es docente o cualquier otro)
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    
    // Destruimos cualquier sesión basura por seguridad
    session_destroy();
    
    // Redirigimos al login
    header("Location: /admin/login.php"); 
    exit;
}
?>