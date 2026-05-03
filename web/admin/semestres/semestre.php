<?php
// 1. Protección: Valida sesión y rol de administrador antes de cargar el archivo.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión y Header
include('../../conexion.php');
include("../includes/header.php");

$result = mysqli_query($conexion, "SELECT * FROM semestres ORDER BY numero ASC");
?>
   <link rel="stylesheet" href="../css/tablasG.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-calendar-event me-2"></i> Gestión de Semestres</h3>
                <span class="text-white-50 small">Administra los niveles académicos</span>
            </div>
            <div class="d-flex gap-2">
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
                <a href="nuevo_semestre.php" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Semestre
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Número de Semestre</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>Semestre <?= $row['numero'] ?></td>
                        <td class="text-end">
                            <?php if($row['numero'] >= 13): ?>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarSem(<?= $row['id'] ?>)">
                                    Eliminar
                                </button>
                            <?php endif; ?>
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
function eliminarSem(id) {
    Swal.fire({
        title: '¿Eliminar semestre?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'eliminar_semestre.php?id=' + id;
        }
    });
}
</script>