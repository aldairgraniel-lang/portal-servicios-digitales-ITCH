<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include(__DIR__ . "/../../conexion.php");
include("../includes/header.php");

$filtro_n_control = '';
$filtro_tramite = '';
$query = "SELECT * FROM solicitudes_cartas_terminacion";
$conditions = [];

if (!empty($_GET['n_control'])) {
    $filtro_n_control = $conexion->real_escape_string(trim($_GET['n_control']));
    $conditions[] = "n_control LIKE '%$filtro_n_control%'";
}

if (!empty($_GET['tipo_tramite'])) {
    $filtro_tramite = $conexion->real_escape_string($_GET['tipo_tramite']);
    $conditions[] = "tipo_tramite = '$filtro_tramite'";
}

if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY fecha_registro DESC";
$res = $conexion->query($query);
?>

<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?= time(); ?>">
<link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-check me-2"></i>Cartas de Terminación</h3>
                <span class="text-white-50 small">Finalización de Servicio y Residencias</span>
            </div>
            <div class="d-flex gap-2">
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door"></i> inicio
                </a>
                <!-- Quitamos data-bs-toggle para manejarlo todo por JS como en Presentaciones -->

            </div>
        </div>

        <!-- Filtros -->
        <form method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="n_control" class="form-control bg-dark text-white border-secondary" placeholder="N° Control" value="<?= htmlspecialchars($filtro_n_control) ?>">
                </div>
                <div class="col-md-4">
                    <select name="tipo_tramite" class="form-select bg-dark text-white border-secondary">
                        <option value="">Todos los trámites</option>
                        <option value="Servicio Social" <?= $filtro_tramite == 'Servicio Social' ? 'selected' : '' ?>>Servicio Social</option>
                        <option value="Residencia Profesional" <?= $filtro_tramite == 'Residencia Profesional' ? 'selected' : '' ?>>Residencia Profesional</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    <a href="solicitudes_cartas_terminacion.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>N° Control</th>
                        <th>Trámite</th>
                        <th>Celular</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-white">
                    <?php if ($res && $res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): 
                            $json_data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['n_control']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= $row['tipo_tramite'] ?></span></td>
                            <td><?= htmlspecialchars($row['numero_celular']) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-warning text-white" onclick="editarRegistro(<?= $json_data ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarRegistro(<?= $row['id_terminacion'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-white-50">No se encontraron solicitudes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalTerminacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitle">Nueva Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTerminacion" action="procesar_cartas_terminacion.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accion" value="guardar">
                    <!-- Importante: El ID debe llamarse como lo espera tu procesar.php -->
                    <input type="hidden" name="id" id="id_terminacion">
                    
                    <div class="mb-3">
                        <label class="form-label small text-white-50">Nombre Completo</label>
                        <input type="text" name="nombre" id="nombre" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-white-50">N° Control</label>
                            <input type="text" name="n_control" id="n_control" class="form-control bg-dark text-white border-secondary" maxlength="8" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-white-50">Celular</label>
                            <input type="text" name="numero_celular" id="numero_celular" class="form-control bg-dark text-white border-secondary" maxlength="10" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-white-50">Tipo de Trámite</label>
                        <select name="tipo_tramite" id="tipo_tramite" class="form-select bg-dark text-white border-secondary" required>
                            <option value="Servicio Social">Servicio Social</option>
                            <option value="Residencia Profesional">Residencia Profesional</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');

    if (mensaje) {
        let config = {
            background: '#0f172a',
            color: '#fff',
            confirmButtonColor: '#3b82f6',
            timer: 2500,
            timerProgressBar: true
        };

        if (mensaje === 'guardado') {
            config.icon = 'success';
            config.title = '¡Guardado!';
            config.text = 'El registro se creó correctamente.';
        } else if (mensaje === 'actualizado') {
            config.icon = 'success';
            config.title = '¡Actualizado!';
            config.text = 'Los cambios se guardaron con éxito.';
        } else if (mensaje === 'eliminado') {
            config.icon = 'success';
            config.title = 'Eliminado';
            config.text = 'El registro fue removido del sistema.';
        } else if (mensaje === 'error') {
            config.icon = 'error';
            config.title = 'Error';
            config.text = 'Hubo un problema al procesar la solicitud.';
        }

        Swal.fire(config).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }
});

function nuevoRegistro() {
    document.getElementById('formTerminacion').reset();
    document.getElementById('accion').value = 'guardar';
    document.getElementById('id_terminacion').value = '';
    document.getElementById('modalTitle').innerText = 'Nueva Solicitud';
    
    let modal = new bootstrap.Modal(document.getElementById('modalTerminacion'));
    modal.show();
}

function editarRegistro(data) {
    // Ya no necesitamos JSON.parse porque pasamos el objeto directamente en el onclick
    document.getElementById('accion').value = 'actualizar';
    document.getElementById('id_terminacion').value = data.id_terminacion;
    document.getElementById('nombre').value = data.nombre;
    document.getElementById('n_control').value = data.n_control;
    document.getElementById('numero_celular').value = data.numero_celular;
    document.getElementById('tipo_tramite').value = data.tipo_tramite;
    
    document.getElementById('modalTitle').innerText = 'Editar Solicitud';
    
    let modal = new bootstrap.Modal(document.getElementById('modalTerminacion'));
    modal.show();
}

function eliminarRegistro(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = 'procesar_cartas_terminacion.php';
            f.innerHTML = `
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="accion" value="eliminar">
            `;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>