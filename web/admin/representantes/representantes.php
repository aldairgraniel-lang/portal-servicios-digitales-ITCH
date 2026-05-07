<?php 
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php");

$res_representantes = $conexion->query("SELECT * FROM representantes ORDER BY nombre ASC");
?>
<link rel="stylesheet" href="../css/tablasG.css">
<link rel="stylesheet" href="../css/filtroUsuario.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-people-fill me-2"></i>Gestión de Representantes</h3>
                <span class="text-white-50 small">Administra los representantes registrados en el sistema</span>
            </div>
            <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3 flex-wrap flex-fill flex-md-grow-0">
                <input type="text" id="filtroControl" class="form-control filtro-glass" placeholder="Filtrar por número de control..." onkeyup="filtrarControl()">
                <div class="d-flex gap-2">
                    <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> volver
                    </a>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="nuevoRepresentante()">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Representante
                    </button>
                </div>
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
                                <button type="button" class="btn btn-sm btn-primary" onclick="editarRepresentante(<?= $rep['id'] ?>, '<?= htmlspecialchars($rep['numero_control'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($rep['nombre'], ENT_QUOTES, 'UTF-8') ?>')" title="Editar">
                                    Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarRep(<?= $rep['id'] ?>)" title="Eliminar">
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
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function nuevoRepresentante() {
    Swal.fire({
        title: 'Nuevo Representante',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Número de Control</label>' +
            '<input type="text" id="swal-numero-control" class="swal2-input" placeholder="Ingresa el número de control" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Nombre</label>' +
            '<input type="text" id="swal-nombre" class="swal2-input" placeholder="Ingresa el nombre" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const numeroControl = document.getElementById('swal-numero-control').value;
            const nombre = document.getElementById('swal-nombre').value;
            if (!numeroControl || !nombre) {
                Swal.showValidationMessage('¡Los campos no pueden estar vacíos!');
            }
            return { numeroControl: numeroControl, nombre: nombre };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_representante.php';
            form.method = 'POST';

            let accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'agregar';

            let numeroControlInput = document.createElement('input');
            numeroControlInput.type = 'hidden';
            numeroControlInput.name = 'numero_control';
            numeroControlInput.value = result.value.numeroControl;

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre';
            nombreInput.value = result.value.nombre;

            form.appendChild(accionInput);
            form.appendChild(numeroControlInput);
            form.appendChild(nombreInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function editarRepresentante(id, numeroControlActual, nombreActual) {
    Swal.fire({
        title: 'Editar Representante',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Número de Control</label>' +
            '<input type="text" id="swal-numero-control" class="swal2-input" value="' + numeroControlActual.replace(/"/g, '&quot;') + '" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Nombre</label>' +
            '<input type="text" id="swal-nombre" class="swal2-input" value="' + nombreActual.replace(/"/g, '&quot;') + '" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const numeroControl = document.getElementById('swal-numero-control').value;
            const nombre = document.getElementById('swal-nombre').value;
            if (!numeroControl || !nombre) {
                Swal.showValidationMessage('¡Los campos no pueden estar vacíos!');
            }
            return { numeroControl: numeroControl, nombre: nombre };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_representante.php';
            form.method = 'POST';

            let accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'editar';

            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            let numeroControlInput = document.createElement('input');
            numeroControlInput.type = 'hidden';
            numeroControlInput.name = 'numero_control';
            numeroControlInput.value = result.value.numeroControl;

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre';
            nombreInput.value = result.value.nombre;

            form.appendChild(accionInput);
            form.appendChild(idInput);
            form.appendChild(numeroControlInput);
            form.appendChild(nombreInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function eliminarRep(id) {
    Swal.fire({
        title: '¿Eliminar representante?',
        text: "Esta acción no se puede revertir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a',
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_representante.php?accion=eliminar&id=' + id;
        }
    });
}

function filtrarControl() {
    let input = document.getElementById('filtroControl').value.toLowerCase();
    let filas = document.querySelectorAll('tbody tr');
    
    filas.forEach(fila => {
        let numeroControl = fila.querySelector('td:nth-child(1)').textContent.toLowerCase();
        if (numeroControl.indexOf(input) > -1) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>