<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

// Usamos require_once para asegurar que el script se detenga si los archivos no existen
require_once(__DIR__ . "/../../conexion.php");
require_once(__DIR__ . "/../includes/auth_docente.php");

// Lógica para detectar si estamos editando
$edit_mode = isset($_GET['editar']);
$edit_data = ['id' => '', 'nombre' => ''];

if ($edit_mode) {
    $id_edit = (int)$_GET['editar'];
    $stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_data = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../img/imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCH - <?= $edit_mode ? 'Editar Curso' : 'Gestión de Cursos' ?></title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --bg-dark: #020617; --card-bg: #4376c980; --border-color: rgba(255, 255, 255, 0.06); --accent: #00e5ff; --danger: #ef4444; }
        body { background: radial-gradient(circle at top right, rgb(30, 41, 59), var(--bg-dark)); color: #a8a8a8; font-family: 'Inter', sans-serif; min-height: 100vh; padding-bottom: 50px; }
        .main-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .registration-bar { background: #212529; backdrop-filter: blur(15px); border: 1px solid #2c2c2c; border-radius: 16px; padding: 20px; margin-bottom: 50px; }
        .form-control-dark { background: rgb(255, 255, 255) !important; border: 1px solid rgba(255, 255, 255, 0.98) !important; color: black !important; }
        .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .course-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 16px; padding: 25px; transition: 0.3s; }
        .course-card:hover { transform: translateY(-4px); border: 1px solid #ff5252; }
        .btn-glow { padding: 12px 24px; background: #000; color: var(--accent); border: 2px solid var(--accent); border-radius: 8px; font-weight: 700; text-transform: uppercase; }
        .btn-glow:hover { background: var(--accent); color: #000; box-shadow: 0 0 20px rgba(0, 229, 255, 0.5); }
        .btn-action-mini { background: transparent; border: none; color: #fff; font-size: 0.8rem; margin-right: 10px; }
        .btn-tecnm { background: #1B396A; color: white; font-weight: bold; border-radius: 10px; transition: 0.3s; border: none; width: 100%; padding: 10px; text-decoration: none; display: inline-block; }
        .btn-tecnm:hover { background: #1B396A; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

<?php include('../includes/header.php'); ?>  

<div class="container mt-4">
    <div class="registration-bar">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <h1>Gestión de Cursos</h1>
            <a href="../panel_docente.php" class="btn btn-outline-primary">Regresar</a>
        </header>
        <section class="card bg-dark border-secondary mb-5 p-4" style="border-radius: 15px;">
            <form action="procesar_cursos.php?accion=<?= $edit_mode ? 'actualizar' : 'crear' ?>" method="POST" class="row g-3 align-items-center">
                <?php if ($edit_mode): ?><input type="hidden" name="id" value="<?= $edit_data['id'] ?>"><?php endif; ?>
                <div class="col">
                    <input type="text" name="nombre" class="form-control form-control-dark" value="<?= $edit_data['nombre'] ?>" placeholder="Nombre del curso..." required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success"><?= $edit_mode ? 'Actualizar' : 'Registrar' ?></button>
                    <?php if ($edit_mode): ?><a href="cursos.php" class="btn btn-outline-secondary ms-2">Cancelar</a><?php endif; ?>
                </div>
            </form>
        </section>
    </div>

    <div class="course-grid">
        <?php
        $res = $conexion->query("SELECT * FROM cursos ORDER BY id DESC");
        while ($row = $res->fetch_assoc()): ?>
            <div class="course-card">
                <h4><?= htmlspecialchars($row['nombre']) ?></h4>
                <div class="mt-3">
                    <a href="cursos.php?editar=<?= $row['id'] ?>" class="btn btn-outline-primary text-white">Editar</a>
                    <button type="button" class="btn btn-outline-danger" onclick="confirmarEliminar(<?= $row['id'] ?>)">Eliminar</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    // 1. Función para confirmar la eliminación
    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción borrará el curso permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#0f172a',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'procesar_cursos.php?accion=eliminar&id=' + id;
            }
        });
    }

    // 2. Notificaciones Toast (se ejecutan automáticamente al cargar si hay mensaje)
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (isset($_SESSION['mensaje'])): ?>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '<?= $_SESSION['mensaje'] ?>',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1e293b',
                color: '#fff'
            });
            <?php unset($_SESSION['mensaje']); // Limpiamos el mensaje tras mostrarlo ?>
        <?php endif; ?>
    });
</script>

</body>
</html>