<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php");

$result = mysqli_query($conexion, "SELECT * FROM cursos ORDER BY id ASC");
?>

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-book me-2"></i> Gestión de Cursos</h3>
                <span class="text-white-50 small">Administra los cursos disponibles</span>
            </div>
            <div class="d-flex gap-2">
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
                <a href="nuevo_curso.php" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Curso
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre del Curso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="editar_curso.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarCurso(<?= $row['id'] ?>)">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function eliminarCurso(id) {
    Swal.fire({
        title: '¿Eliminar curso?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'eliminar_curso.php?id=' + id;
        }
    });
}
</script>