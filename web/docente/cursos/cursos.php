<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

// Asegurar que el script se detenga si los archivos no existen
require_once(__DIR__ . "/../../conexion.php");
require_once(__DIR__ . "/../includes/auth_docente.php");

// Lógica para detectar si estamos editando
$edit_mode = isset($_GET['editar']);
$edit_data = ['id' => '', 'nombre' => '', 'clave' => ''];

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
    <link rel="icon" href="../img/Imagen1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCH - <?= $edit_mode ? 'Editar Curso' : 'Gestión de Cursos' ?></title> 
    <link rel="icon" href="/docente/img/Imagen1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="../css/cursos.css">
</head>
<body>
    
<?php include('../includes/header.php'); ?>

<div class="wrapper">
    
    <main class="main-container p-4" style="max-width: 1200px; margin: 0 auto; width: 100%;">
        
        <div class="registration-bar mb-4">
            <header class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h1 class="text-white m-0">Gestión de Cursos</h1>
                <a href="../panel_docente.php" class="btn btn-outline-primary">Regresar</a>
            </header>
            
            <section class="card bg-dark border-secondary p-4" style="border-radius: 16px; background: #1e293b !important; border: 1px solid rgba(255, 255, 255, 0.08) !important;">
                <form action="procesar_cursos.php?accion=<?= $edit_mode ? 'actualizar' : 'crear' ?>" method="POST">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">
                    <?php endif; ?>
                    
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5 col-md-12">
                            <label class="form-label text-white">Nombre del curso</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($edit_data['nombre'] ?? '') ?>" placeholder="Nombre del curso..." required>
                        </div>

                        <div class="col-lg-5 col-md-12">
                            <label class="form-label text-white">Clave del curso</label>
                            <input type="text" name="clave" class="form-control" value="<?= htmlspecialchars($edit_data['clave'] ?? '') ?>" placeholder="Clave del curso..." required>
                        </div>
                        
                        <div class="col-lg-2 col-md-12">
                            <div class="d-grid gap-2 w-100">
                                <button type="submit" class="btn btn-success"><?= $edit_mode ? 'Actualizar' : 'Registrar' ?></button>
                                <?php if ($edit_mode): ?>
                                    <a href="cursos.php" class="btn btn-outline-secondary mt-1">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="text-white m-0 h4">Lista de Cursos</h2>
            <div style="max-width: 300px; width: 100%;">
                <input type="text" id="filtroClave" class="form-control "class="text-white" placeholder="Filtrar por clave... " onkeyup="filtrarCursos()">
            </div>
        </div>

        <div class="course-grid">
            <?php
            $res = $conexion->query("SELECT * FROM cursos ORDER BY id DESC");
            while ($row = $res->fetch_assoc()): ?>
                <div class="course-card shadow-sm">
                    <h4><?= htmlspecialchars($row['nombre']) ?></h4>
                    <p class="text-secondary mb-2">Clave: <?= htmlspecialchars($row['clave']) ?></p>
                    
                    <div class="mt-3 d-flex gap-2">
                        <a href="cursos.php?editar=<?= $row['id'] ?>" class="btn btn-outline-primary text-white">Editar</a>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmarEliminar(<?= $row['id'] ?>)">Eliminar</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div id="mensajeSinResultados" class="text-center text-white mt-4 d-none">
            No se encontraron cursos que coincidan con la clave.
        </div>
    </main>
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

    // 2. Función para filtrar los cursos por clave
    function filtrarCursos() {
        var input, filter, cards, p, txtValue, visibleCount = 0;
        input = document.getElementById('filtroClave');
        filter = input.value.toUpperCase();
        cards = document.getElementsByClassName('course-card');
        
        for (var i = 0; i < cards.length; i++) {
            p = cards[i].getElementsByTagName("p")[0];
            txtValue = p.textContent || p.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                cards[i].style.display = "";
                visibleCount++;
            } else {
                cards[i].style.display = "none";
            }
        }

        var msg = document.getElementById('mensajeSinResultados');
        if (visibleCount === 0 && filter !== "") {
            msg.classList.remove('d-none');
        } else {
            msg.classList.add('d-none');
        }
    }

    // 3. Notificaciones Toast (se ejecutan automáticamente al cargar si hay mensaje)
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