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
    
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>

<div class="glass-card text-center p-4">
<div class="login-logo">
<img src="img/1.GIF" alt="" style="max-width: 100px; height: auto; filter: drop-shadow(0 0 10px rgb(255, 255, 255));">
</div>

    
    <h3 class="login-title"> D.E.P <br>
        <p class="login-subtitle">Divison de Estudios Profesionales.</p>

    <hr>Acceso</h3>
    <p class="login-subtitle">Gestión  del portal de servicios.</p>

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