<?php
// 1. Seguridad: Solo admins pueden entrar aquí
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php'); 
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// 2. Procesamiento de datos
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion == 'agregar') {
        $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, password, rol) VALUES (?, ?, ?)");
        $pass = md5($_POST['password']);
        $stmt->bind_param("sss", $_POST['usuario'], $pass, $_POST['rol']);
        $stmt->execute();
        $_SESSION['mensaje'] = "Usuario creado.";
        header("Location: usuarios.php");
        exit();
    }
    
    if ($accion == 'editar') {
        $id = $_POST['id'];
        $usuario = $_POST['usuario'];
        $rol = $_POST['rol'];
        
        if (!empty($_POST['password'])) {
            $pass = md5($_POST['password']);
            $stmt = $conexion->prepare("UPDATE usuarios SET usuario = ?, password = ?, rol = ? WHERE id = ?");
            $stmt->bind_param("sssi", $usuario, $pass, $rol, $id);
        } else {
            $stmt = $conexion->prepare("UPDATE usuarios SET usuario = ?, rol = ? WHERE id = ?");
            $stmt->bind_param("ssi", $usuario, $rol, $id);
        }
        $stmt->execute();
        header("Location: usuarios.php");
        exit();
    }
}

// 3. Obtener datos si es edición (para rellenar el formulario)
$usuario_data = ['usuario' => '', 'rol' => 'docente', 'id' => ''];
if (isset($_GET['id'])) {
    $res = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = " . (int)$_GET['id']);
    $usuario_data = mysqli_fetch_assoc($res);
}

// 4. Incluir el diseño (header)
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');
?>
   <link rel="stylesheet" href="../css/tablasG.css">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="glass-card p-4 shadow-lg">
                <h4 class="mb-4 fw-bold text-white"><?= isset($_GET['id']) ? 'Editar Usuario' : 'Nuevo Usuario' ?></h4>
                
                <form action="procesar_usuario.php" method="POST">
                    <input type="hidden" name="accion" value="<?= isset($_GET['id']) ? 'editar' : 'agregar' ?>">
                    <input type="hidden" name="id" value="<?= $usuario_data['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="text-white-50 small ms-1">Usuario</label>
                        <input type="text" name="usuario" class="form-control input-glass" value="<?= $usuario_data['usuario'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-white-50 small ms-1">Contraseña <?= isset($_GET['id']) ? '(Dejar vacío para mantener)' : '' ?></label>
                        <input type="password" name="password" class="form-control input-glass" <?= !isset($_GET['id']) ? 'required' : '' ?>>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-white-50 small ms-1">Rol</label>
                        <select name="rol" class="form-select select-glass">
                            <option value="docente" <?= $usuario_data['rol'] == 'docente' ? 'selected' : '' ?>>Docente</option>
                            <option value="admin" <?= $usuario_data['rol'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Guardar Cambios</button>
                    <a href="usuarios.php" class="btn btn-outline-secondary w-100 mt-2 rounded-pill">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>