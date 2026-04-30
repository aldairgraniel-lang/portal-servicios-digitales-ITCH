<?php
// 1. ESTO ES LO QUE TE FALTA. 
// Incluir esto reemplaza al session_start() y protege todo el archivo.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Luego incluyes la conexión
include(__DIR__ . "/../../conexion.php");

// 3. Y después, el resto de tu código...
include("../includes/header.php");
$res_solicitudes = $conexion->query("SELECT * FROM solicitudes_cartas_aceptacion ORDER BY fecha_registro DESC");
?>

<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container py-4">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-text me-2"></i>Cartas de Aceptación</h3>
                <span class="text-white-50 small">Gestión de trámites de aceptación</span>
            </div>
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>N° Control</th>
                        <th>Tipo Trámite</th>
                        <th>Fecha Registro</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-black-100">
                    <?php while($sol = $res_solicitudes->fetch_assoc()): ?>
                    <tr>
                        <td class="text-black"><?= htmlspecialchars($sol['numero_control']) ?></td>
                        <td class="text-black"><?= htmlspecialchars($sol['tipo_tramite']) ?></td>
                        <td class="text-black"><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                        <td class="text-end">
                            <button onclick="eliminarSolicitud(<?= $sol['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar solicitud">
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
function eliminarSolicitud(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
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
            f.action = 'procesar_cartas_aceptacion.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>