<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include("../includes/header.php");

// Lógica de filtrado
$filtro_n_control = '';
if (isset($_GET['numero_control']) && trim($_GET['numero_control']) !== '') {
    $filtro_n_control = $conexion->real_escape_string(trim($_GET['numero_control']));
    $query = "SELECT * FROM registro_ingles WHERE numero_control LIKE '%$filtro_n_control%' ORDER BY fecha_registro DESC";
} else {
    $query = "SELECT * FROM registro_ingles ORDER BY fecha_registro DESC";
}

$res_solicitudes = $conexion->query($query);
?>
<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?php echo time(); ?>">
<div class="container py-5">

<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-4">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-book-half me-2"></i>Solicitudes de Inglés</h3>
                <span class="text-white-50 small">Gestión de constancias de no inconveniencia</span>
            </div>
            <div>
                <button class="btn btn-success btn-sm rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#modalIngles" onclick="limpiarIngles()">
                    <i class="bi bi-plus-circle me-1"></i> Nuevo
                </button>
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> Inicio
                </a>
            </div>
        </div>

        <form method="GET" class="mb-4 px-2">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="numero_control" class="col-form-label text-white fw-semibold">Filtrar por N° Control:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="numero_control" name="numero_control" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_n_control) ?>" placeholder="Escribe el N° Control...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <?php if(!empty($filtro_n_control)): ?>
                        <a href="solicitudes_ingles_constancias.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Carrera</th>
                        <th>Periodo</th>
                        <th>Fecha Registro</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-white-100">
                    <?php if ($res_solicitudes && $res_solicitudes->num_rows > 0): ?>
                        <?php while($sol = $res_solicitudes->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold text-white"><?= htmlspecialchars($sol['nombre']) ?></div>
                                <small class="text-white">#<?= htmlspecialchars($sol['numero_control']) ?></small>
                            </td>
                            <td class="text-white"><?= htmlspecialchars($sol['carrera']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($sol['periodo']) ?></td>
                            <td class="text-white"><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-warning text-white me-1" title="Editar" 
                                        onclick="editarIngles(<?= htmlspecialchars(json_encode($sol)) ?>)">
                                    Editar
                                </button>
                                <button onclick="eliminarIngles(<?= $sol['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar solicitud">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-white py-4">
                                <i class="bi bi-exclamation-circle me-2"></i> No se encontraron registros.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIngles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitleIngles">Nueva Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formIngles" action="procesar_constancias_ingles.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accionIngles" value="guardar">
                    <input type="hidden" name="id" id="ingles_id" value="">
                    
                    <div class="mb-3">
                        <label for="nombre_ingles" class="form-label">Nombre del Alumno</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="nombre_ingles" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="numero_control_ingles" class="form-label">N° de Control</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="numero_control_ingles" name="numero_control" required>
                    </div>
                    <div class="mb-3">
                        <label for="carrera_ingles" class="form-label">Carrera</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="carrera_ingles" name="carrera" required>
                    </div>
                    <div class="mb-3">
                        <label for="periodo_ingles" class="form-label">Periodo</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="periodo_ingles" name="periodo" required>
                    </div>
                    <div class="mb-3">
                        <label for="archivo_ingles" class="form-label">Archivo de Respaldo</label>
                        <input type="file" class="form-control bg-dark text-white border-secondary" id="archivo_ingles" name="archivo">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');

    if (mensaje) {
        let title = "";
        let text = "";
        let icon = "success";

        if (mensaje === "eliminado") {
            title = "¡Registro eliminado!";
            text = "El registro se ha eliminado exitosamente.";
        } else if (mensaje === "guardado") {
            title = "¡Registro guardado!";
            text = "El registro se creó exitosamente.";
        } else if (mensaje === "actualizado") {
            title = "¡Registro actualizado!";
            text = "El registro se modificó exitosamente.";
        } else if (mensaje === "error") {
            title = "¡Error!";
            text = "Ocurrió un error al procesar los datos.";
            icon = "error";
        }

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#3b82f6'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
});

function limpiarIngles() {
    document.getElementById('formIngles').reset();
    document.getElementById('accionIngles').value = 'guardar';
    document.getElementById('ingles_id').value = '';
    document.getElementById('modalTitleIngles').innerText = 'Nueva Solicitud';
}

function editarIngles(sol) {
    document.getElementById('accionIngles').value = 'actualizar';
    document.getElementById('ingles_id').value = sol.id;
    document.getElementById('nombre_ingles').value = sol.nombre;
    document.getElementById('numero_control_ingles').value = sol.numero_control;
    document.getElementById('carrera_ingles').value = sol.carrera;
    document.getElementById('periodo_ingles').value = sol.periodo;
    document.getElementById('modalTitleIngles').innerText = 'Editar Solicitud';

    let modal = new bootstrap.Modal(document.getElementById('modalIngles'));
    modal.show();
}

function eliminarIngles(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = 'procesar_constancias_ingles.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>
</div>