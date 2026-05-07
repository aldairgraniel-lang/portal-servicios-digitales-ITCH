<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php");

$result = mysqli_query($conexion, "SELECT * FROM cursos ORDER BY id ASC");
?>
<link rel="stylesheet" href="../css/tablasG.css">
<link rel="stylesheet" href="../css/filtroUsuario.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-book me-2"></i> Gestión de Cursos</h3>
                <span class="text-white-50 small">Administra los cursos disponibles</span>
            </div>
            <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3 flex-wrap flex-fill flex-md-grow-0">
                <input type="text" id="filtroClave" class="form-control filtro-glass" placeholder="Filtrar por clave..." onkeyup="filtrarClave()">
                <div class="d-flex gap-2">
                    <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="nuevoCurso()">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Curso
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Nombre del Curso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['clave']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="editarCurso(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['clave'], ENT_QUOTES, 'UTF-8') ?>')">
                                    Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarCurso(<?= $row['id'] ?>)">
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
function nuevoCurso() {
    Swal.fire({
        title: 'Nuevo Curso',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Nombre del curso</label>' +
            '<input type="text" id="swal-nombre" class="swal2-input" placeholder="Ingresa el nombre" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Clave del curso</label>' +
            '<input type="text" id="swal-clave" class="swal2-input" placeholder="Ingresa la clave" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">',
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
            const clave = document.getElementById('swal-clave').value;
            if (!nombre || !clave) {
                Swal.showValidationMessage('¡Los campos no pueden estar vacíos!');
            }
            return { nombre: nombre, clave: clave };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_curso.php';
            form.method = 'POST';

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre';
            nombreInput.value = result.value.nombre;

            let claveInput = document.createElement('input');
            claveInput.type = 'hidden';
            claveInput.name = 'clave';
            claveInput.value = result.value.clave;

            form.appendChild(nombreInput);
            form.appendChild(claveInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function editarCurso(id, nombreActual, claveActual) {
    Swal.fire({
        title: 'Editar Curso',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Nombre del curso</label>' +
            '<input type="text" id="swal-nombre" class="swal2-input" value="' + nombreActual.replace(/"/g, '&quot;') + '" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Clave del curso</label>' +
            '<input type="text" id="swal-clave" class="swal2-input" value="' + claveActual.replace(/"/g, '&quot;') + '" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const nombre = document.getElementById('swal-nombre').value;
            const clave = document.getElementById('swal-clave').value;
            if (!nombre || !clave) {
                Swal.showValidationMessage('¡Los campos no pueden estar vacíos!');
            }
            return { nombre: nombre, clave: clave };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_curso.php';
            form.method = 'POST';

            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre';
            nombreInput.value = result.value.nombre;

            let claveInput = document.createElement('input');
            claveInput.type = 'hidden';
            claveInput.name = 'clave';
            claveInput.value = result.value.clave;

            form.appendChild(idInput);
            form.appendChild(nombreInput);
            form.appendChild(claveInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function eliminarCurso(id) {
    Swal.fire({
        title: '¿Eliminar curso?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_curso.php?action=eliminar&id=' + id;
        }
    });
}

function filtrarClave() {
    let input = document.getElementById('filtroClave').value.toLowerCase();
    let filas = document.querySelectorAll('tbody tr');
    
    filas.forEach(fila => {
        let clave = fila.querySelector('td:nth-child(1)').textContent.toLowerCase();
        if (clave.indexOf(input) > -1) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>