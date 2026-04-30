<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

$res_tramites = $conexion->query("SELECT * FROM tipos_tramite ORDER BY nombre_tramite ASC");
?>

<div class="glass-card p-4 shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold m-0"><i class="bi bi-file-earmark-text me-2"></i>Tipos de Trámite</h3>
            <span class="text-white-50 small">Gestión de catálogo de trámites</span>
        </div>
        
        <div class="d-flex gap-2">
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
            <a href="agregar_tramite.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Trámite
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nombre del Trámite</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($t = $res_tramites->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nombre_tramite']) ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="procesar_tramite.php?id=<?= $t['id'] ?>" class=" btn btn-sm btn-primary">                            
                                Editar
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarTramite(<?= $t['id'] ?>)">
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
function eliminarTramite(id) {
    Swal.fire({
        title: '¿Eliminar trámite?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, eliminar',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_tramite.php?accion=eliminar&id=' + id;
        }
    });
}
</script>