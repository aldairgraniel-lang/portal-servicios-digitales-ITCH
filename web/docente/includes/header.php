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
        :root { --bg-dark: #021936; --sidebar-bg: #04336c; --card-bg: #04336c; --accent-blue: #ff9534; --text-p: #f8fafc; --border: #ffffff14; }
        body { background: var(--bg-dark); color: var(--text-p); font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; margin: 0; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar-bg); border-right: 1px solid var(--border); padding: 20px; flex-shrink: 0; }
        .nav-link { color: #94a3b8; padding: 12px; border-radius: 10px; text-decoration: none; transition: 0.3s; margin-bottom: 5px; display: block; }
        .nav-link:hover, .nav-link.active { background: #0062ff; color: #fff; }
        
        /* Contenido Wrapper */
        .wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        
        /* Header Superior */
        .top-navbar { background: #ff9634d4; backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Tarjetas */
        .card-modern { background: var(--card-bg); border: 1px solid var(--border); border-radius: 20px; padding: 25px; transition: 0.3s; height: 100%; backdrop-filter: blur(10px); }
        .card-modern:hover { border-color: var(--accent-blue); transform: translateY(-5px); }
|         /* Asegura que el contenedor sea relativo para que el badge se posicione encima */
.card-modern { 
    background: var(--card-bg); 
    border: 1px solid var(--border); 
    border-radius: 20px; 
    padding: 25px; 
    transition: 0.3s; 
    height: 100%; 
    backdrop-filter: blur(10px);
    position: relative; /* <--- ESTO ES CRUCIAL */
}

.card-modern:hover { border-color: var(--accent-blue); transform: translateY(-5px); }

/* Estilo y posicionamiento del badge */
.notification-badge { 
    position: absolute; 
    top: 20px; 
    right: 20px; 
    background: var(--accent-blue); 
    color: white; 
    padding: 4px 12px; 
    border-radius: 15px; 
    font-weight: bold; 
    font-size: 0.8rem; 
    z-index: 10;
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
        <img src="../../img/imagen3.png" alt="Logo" width="90" height="130 px " >
    </a>
</h4>    
    <a href="<?php echo BASE_URL; ?>panel_docente.php" class="nav-link">📊 Gestión Servicios.</a>
    <a href="<?php echo BASE_URL; ?>avisos_docente.php" class="nav-link">📢 Comunicar Avisos.</a>
    
    <a href="<?php echo BASE_URL; ?>cursos/cursos.php" class="nav-link">📚 Ofertar Cursos.</a>
</aside>

<div class="wrapper">
    <header class="top-navbar">
        <span class="text-white"></span>
<a href="<?php echo BASE_URL2; ?>logout.php" 
   class="btn btn-outline-danger" 
   style="width: 200px; font-size: 1.2rem; padding: 10px;">
   🔐 Cerrar Sesión
</a>    </header>

    <main class="p-4">