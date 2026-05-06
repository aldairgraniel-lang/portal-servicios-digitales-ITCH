<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

$res_tramites = $conexion->query("SELECT * FROM tipos_tramite ORDER BY nombre_tramite ASC");
?>
<link rel="stylesheet" href="../css/tablasG.css">

<div class="glass-card p-4 shadow-lg">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold m-0"><i class="bi bi-file-earmark-text me-2"></i>Tipos de Trámite</h3>
            <span class="text-white-50 small">Gestión de catálogo de trámites</span>
        </div>
        
        <div class="d-flex gap-2">
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Regresar
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="nuevoTramite()">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Trámite
            </button>
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
                            <button type="button" class="btn btn-sm btn-primary" onclick="editarTramite(<?= $t['id'] ?>, '<?= htmlspecialchars($t['nombre_tramite'], ENT_QUOTES) ?>')">
                                Editar
                            </button>
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
function nuevoTramite() {
    Swal.fire({
        title: 'Nuevo Trámite',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Nombre del Trámite</label>' +
            '<input type="text" id="swal-nombre" class="swal2-input" placeholder="Ingresa el nombre del trámite" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const nombre = document.getElementById('swal-nombre').value;
            if (!nombre) {
                Swal.showValidationMessage('¡El campo no puede estar vacío!');
            }
            return { nombre: nombre };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_tramite.php';
            form.method = 'POST';

            let accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'agregar';

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre_tramite';
            nombreInput.value = result.value.nombre;

            form.appendChild(accionInput);
            form.appendChild(nombreInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function editarTramite(id, nombreActual) {
    Swal.fire({
        title: 'Editar Trámite',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Nombre del Trámite</label>' +
            '<input type="text" id="swal-nombreedit" class="swal2-input" value="' + nombreActual + '" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const nombre = document.getElementById('swal-nombreedit').value;
            if (!nombre) {
                Swal.showValidationMessage('¡El campo no puede estar vacío!');
            }
            return { nombre: nombre };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_tramite.php';
            form.method = 'POST';

            let accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'actualizar';

            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre_tramite';
            nombreInput.value = result.value.nombre;

            form.appendChild(accionInput);
            form.appendChild(idInput);
            form.appendChild(nombreInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function eliminarTramite(id) {
    Swal.fire({
        title: '¿Eliminar trámite?',
        text: "Esta acción no se puede revertir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a',
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_tramite.php?accion=eliminar&id=' + id;
        }
    });
}
</script>