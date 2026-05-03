<?php
// header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg-dark: #021936; 
            --sidebar-bg: #04336c; 
            --accent-blue: #ff9534; 
            --text-p: #f8fafc; 
            --border: #ffffff14; 
        }

        body { 
            background: var(--bg-dark); 
            color: var(--text-p); 
            font-family: 'Inter', sans-serif; 
            display: flex; 
            min-height: 100vh; 
            margin: 0; 
        }
        
        /* Estilos del Sidebar */
        .sidebar { 
            width: 260px; 
            background: var(--sidebar-bg); 
            border-right: 1px solid var(--border); 
            padding: 20px; 
            flex-shrink: 0; 
        }
        
        .nav-link { 
            color: #94a3b8; 
            padding: 12px; 
            border-radius: 10px; 
            text-decoration: none; 
            transition: 0.3s; 
            margin-bottom: 5px; 
            display: block; 
        }
        
        .nav-link:hover, .nav-link.active { 
            background: #0062ff; 
            color: #fff; 
        }
        
        /* Contenedor principal de contenidos */
        .wrapper { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
        }
        
        /* Diseño único y exclusivo para el Header Superior */
        .top-navbar { 
            background: #ff9634d4; 
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid var(--border); 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        /* Botón único para el Header */
        .top-navbar .logout-btn {
            width: 200px; 
            font-size: 1.2rem; 
            padding: 10px; 
            transition: all 0.3s ease;
            background-color: transparent;
            border: 2px solid #dc3545;
            color: #dc3545;
        }

        .top-navbar .logout-btn:hover {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>

<?php 
// 1. Incluyes la configuración primero
require_once 'config.php'; 
?>

<aside class="sidebar">
    <h4 class="text-white text-center fw-bold mb-4 px-2">
        <a href="<?php echo BASE_URL; ?>panel_docente.php">
            <img src="../../img/imagen3.png" alt="Logo" width="90" height="130">
        </a>
    </h4>    
    <a href="<?php echo BASE_URL; ?>panel_docente.php" class="nav-link">📊 Gestión Servicios.</a>
    <a href="<?php echo BASE_URL; ?>avisos_docente.php" class="nav-link">📢 Comunicar Avisos.</a>
    <a href="<?php echo BASE_URL; ?>cursos/cursos.php" class="nav-link">📚 Ofertar Cursos.</a>
</aside>

<div class="wrapper">
    <header class="top-navbar">
        <span class="text-white"></span>
        <a href="<?php echo BASE_URL2; ?>logout.php" class="btn btn-outline-danger logout-btn">
            🔐 Cerrar Sesión
        </a>
    </header>

    <main class="p-4">