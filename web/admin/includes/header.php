<?php
// /admin/includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no está logueado O no es admin, lo sacamos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: /admin/login.php");
    exit; // ESTO ES VITAL: Detiene la ejecución del script aquí mismo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - ITCH</title>
    <link rel="icon" href="../../img/imagen1.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: radial-gradient(circle at top right, #021936, #021936); 
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #f8fafc;
        }

        .navbar {
            background: #0f1729cc !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid #ffffff14;
            padding: 0.7rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1050;
        }

        .navbar-brand {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff !important;
        }

        .brand-subtitle {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            line-height: 1;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: white;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
            transform: translateY(-1px);
        }

        /* Contenedor principal para que las tablas no peguen a los bordes */
        .main-content {
            padding: 40px 0;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin/inicio.php">
            <img src="../../img/imagen1.png" alt="Logo" width="50" height="50">
            <div>
                ITCH 
                <span class="brand-subtitle">Estudios Profesionales</span>
            </div>
        </a>

        <div class="ms-auto d-flex align-items-center">
            <div class="d-none d-md-flex align-items-center me-4">
                <div class="text-end me-3">
                    <div class="small fw-bold text-white">Administrador</div>
                    <div style="font-size: 0.65rem; color: #4ade80;">● En línea</div>
                </div>
            </div>
            
            <a href="/admin/logout.php" class="btn-logout">
                <i class="bi bi-power"></i> 
                <span class="d-none d-sm-inline">Salir</span>
            </a>
        </div>
    </div>
</nav>

<div class="container main-content">