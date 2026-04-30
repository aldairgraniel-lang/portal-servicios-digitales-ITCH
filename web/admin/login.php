<?php
session_start();
include("../conexion.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario']);
    $password = md5($_POST['password']); 

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ?");
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario']    = $row['usuario'];
        $_SESSION['rol']        = $row['rol'];

        if ($row['rol'] === 'admin') {
            header("Location: inicio.php");
        } else {
            header("Location: ../docente/panel_docente.php");
        }
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecNM | Campus Chetumal</title>
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
        }

        body {
            /* Gradiente oscuro profundo para que el cristal resalte */
            background: radial-gradient(circle at center, #04336c 30%, #ff9534 150%);
            
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow: hidden;
        }

        /* Tarjeta de Cristal */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo {
            font-size: 3rem;
            color: #ffffff;
            margin-bottom: 1rem;
            filter: drop-shadow(0 0 10px rgba(67, 97, 238, 0.5));
        }
        .login-logo img {
    border-radius: 50%;       /* Convierte la imagen en un círculo */
    width: 100px;             /* Ancho fijo */
    height: 100px;            /* Altura fija (igual al ancho para que sea círculo) */
    object-fit: cover;        /* Asegura que la imagen llene el círculo sin deformarse */
    border: 2px solid rgba(255, 255, 255, 0.2); /* Opcional: un borde sutil para que resalte más */
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}

        .login-title {
            color: #ffffff;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        /* Estilo de los Inputs */
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 15px;
            color: #ffffff;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
            color: #ffffff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* Botón Moderno */
        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background: #3751d4;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .alert-modern {
            background: rgba(255, 23, 68, 0.1);
            border: 1px solid rgba(255, 23, 68, 0.2);
            color: #ff5252;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="glass-card text-center">
<div class="login-logo">
<img src="img/1.GIF" alt="" style="max-width: 100px; height: auto; filter: drop-shadow(0 0 10px rgba(67, 97, 238, 0.5));">
</div>

    
    <h3 class="login-title"> D.E.P <br>
        <p class="login-subtitle">Divison de Estudios Profesionales.</p>

    <hr>Acceso</h3>
    <p class="login-subtitle">Gestión Administrativa y Docente.</p>

    <?php if ($error): ?>
        <div class="alert alert-modern p-2 mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3 text-start">
            <label class="form-label small text-white-50 ms-2">Usuario</label>
            <input name="usuario" class="form-control" placeholder="Usuario" required autofocus>
        </div>
        
        <div class="mb-4 text-start">
            <label class="form-label small text-white-50 ms-2">Contraseña</label>
            <input name="password" type="password" class="form-control" placeholder="********" required>
        </div>

        <button class="btn btn-primary w-100 btn-login">
            Iniciar Sesión <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </form>

    <div class="mt-4">
        <a href="../index.php" class="text-white-50 text-decoration-none small">
            <i class="fas fa-chevron-left me-1"></i> Visitar portal de servicios
        </a>
    </div>
</div>

</body>
</html>