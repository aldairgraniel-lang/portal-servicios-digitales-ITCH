<?php
// 1. Protección de seguridad: Esto valida sesión y rol de administrador antes de cargar nada
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión y Header
include('../../conexion.php');
include("../includes/header.php");
$result = mysqli_query($conexion, "SELECT * FROM VERANO ORDER BY id DESC");
?>

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-people me-2"></i> Registro de Alumnos</h3>
                <span class="text-white-50 small">Gestión de pre-registros</span>
            </div>
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>No. Control</th>
                        <th>Carrera</th>
                        <th>Curso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) ?></td>
                        <td><?= htmlspecialchars($row['numero_control']) ?></td>
                        <td><?= htmlspecialchars($row['carrera']) ?></td>
                        <td><?= htmlspecialchars($row['curso_interes']) ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="procesar_preregistro.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarPre(<?= $row['id'] ?>)">Eliminar</button>
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
function eliminarPre(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'eliminar_preregistro.php?id=' + id;
        }
    });
}
</script>