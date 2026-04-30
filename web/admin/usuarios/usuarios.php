<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

$res_usuarios = $conexion->query("SELECT id, usuario, rol FROM usuarios ORDER BY usuario ASC");
?>

<div class="glass-card p-4 shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold m-0"><i class="bi bi-person-badge-fill me-2"></i>Gestión de Usuarios</h3>
            <span class="text-white-50 small">Administra los accesos al sistema</span>
            
        </div>
        <div class="d-flex gap-2">
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
            <a href="agregar_usuario.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
            </a>
        </div>
    </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $res_usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['usuario']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($user['rol']) ?></span></td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="procesar_usuario.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                Editar
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarUser(<?= $user['id'] ?>)" title="Eliminar">
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
function eliminarUser(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_usuario.php?accion=eliminar&id=' + id;
        }
    });
}
</script>