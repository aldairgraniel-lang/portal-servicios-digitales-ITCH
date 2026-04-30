<?php 
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php");

$res_representantes = $conexion->query("SELECT * FROM representantes ORDER BY nombre ASC");
?>

<div class="glass-card p-4 shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold m-0"><i class="bi bi-people-fill me-2"></i>Gestión de Representantes</h3>
            <span class="text-white-50 small">Administra los representantes registrados en el sistema</span>
        </div>
        <div class="d-flex gap-2">
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
            <a href="agregar_representante.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Representante
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Número Control</th>
                    <th>Nombre</th>
                    <th>Fecha de Registro</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($rep = $res_representantes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($rep['numero_control']) ?></td>
                    <td><?= htmlspecialchars($rep['nombre']) ?></td>
                    <td class="text-black-100"><?= date('d/m/Y', strtotime($rep['fecha_registro'])) ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="procesar_representante.php?id=<?= $rep['id'] ?>" class="btn btn-sm btn-primary" title="Editar">Editar
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarRep(<?= $rep['id'] ?>)" title="Eliminar">eliminar
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
function eliminarRep(id) {
    Swal.fire({
        title: '¿Eliminar representante?',
        text: "Esta acción no se puede revertir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_representante.php?accion=eliminar&id=' + id;
        }
    });
}
</script>