<?php 
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

$res_periodos = $conexion->query("SELECT * FROM periodos ORDER BY nombre ASC");
?>

<div class="glass-card p-4 shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold m-0"><i class="bi bi-calendar-event me-2"></i>Gestión de Periodos</h3>
            <span class="text-white-50 small">Administra los ciclos escolares</span>
        </div>
        <div class="d-flex gap-2">
        <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
        <a href="agregar_periodo.php" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Periodo
        </a></div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nombre del Periodo</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($per = $res_periodos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($per['nombre']) ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="procesar_periodo.php?id=<?= $per['id'] ?>" class="btn btn-primary">
                                
                                Editar    
                            </a>
                            <button type="button" class="btn btn-danger " onclick="eliminarPer(<?= $per['id'] ?>)">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function eliminarPer(id) {
    Swal.fire({
        title: '¿Eliminar periodo?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_periodo.php?accion=eliminar&id=' + id;
        }
    });
}
</script>