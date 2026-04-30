<?php
// 1. Protección: Solo administradores pueden pasar de aquí.
// Al incluir auth.php, validamos sesión y rol automáticamente.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include(__DIR__ . "/../../conexion.php");

// 3. Diseño
include("../includes/header.php");
$res_justificantes = $conexion->query("SELECT * FROM justificantes ORDER BY fecha_registro DESC");
?>

<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container py-4">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-medical me-2"></i>Justificantes</h3>
                <span class="text-white-50 small">Gestión de justificantes médicos/escolares</span>
            </div>
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>N° Control</th>
                        <th>Motivo</th>
                        <th>Periodo</th>
                        <th>Registro</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-black-100">
                    <?php while($jus = $res_justificantes->fetch_assoc()): ?>
                    <tr>
                        <td class="text-black fw-bold"><?= htmlspecialchars($jus['nombre']) ?></td>
                        <td class="text-black"><?= htmlspecialchars($jus['n_control']) ?></td>
                        <td class="text-black"><?= htmlspecialchars($jus['motivo']) ?></td>
                        <td class="text-black">
                            <?= date('d/m/Y', strtotime($jus['fecha_inicio'])) ?> al 
                            <?= date('d/m/Y', strtotime($jus['fecha_fin'])) ?>
                        </td>
                        <td class="text-black"><?= date('d/m/Y', strtotime($jus['fecha_registro'])) ?></td>
                        <td class="text-end">
                            <button onclick="eliminarJustificante(<?= $jus['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
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
function eliminarJustificante(id) {
    Swal.fire({
        title: '¿Eliminar justificante?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = 'procesar_justificantes.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>