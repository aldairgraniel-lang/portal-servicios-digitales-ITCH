<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php");

$result = mysqli_query($conexion, "SELECT * FROM carreras ORDER BY id ASC");
?>
<link rel="stylesheet" href="../css/tablasG.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-mortarboard me-2"></i> Gestión de Carreras</h3>
                <span class="text-white-50 small">Administra la oferta académica</span>
            </div>
            <div class="d-flex gap-2">
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="nuevaCarrera()">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Carrera
                </button>
            </div>
        </div>

        <div class="table-responsive ">
            <table class="table align-middle ">
                <thead>
                    <tr>
                        <th>Nombre de la Carrera</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="editarCarrera(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') ?>')">
                                    Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarCarrera(<?= $row['id'] ?>)">
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
function nuevaCarrera() {
    Swal.fire({
        title: 'Nueva Carrera',
        input: 'text',
        inputLabel: 'Nombre de la carrera',
        inputPlaceholder: 'Ingresa el nombre',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return '¡El nombre no puede estar vacío!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_carrera.php';
            form.method = 'POST';

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre';
            nombreInput.value = result.value;

            form.appendChild(nombreInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function editarCarrera(id, nombreActual) {
    Swal.fire({
        title: 'Editar Carrera',
        input: 'text',
        inputLabel: 'Nombre de la carrera',
        inputValue: nombreActual,
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return '¡El nombre no puede estar vacío!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_carrera.php';
            form.method = 'POST';

            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            let nombreInput = document.createElement('input');
            nombreInput.type = 'hidden';
            nombreInput.name = 'nombre';
            nombreInput.value = result.value;

            form.appendChild(idInput);
            form.appendChild(nombreInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function eliminarCarrera(id) {
    Swal.fire({
        title: '¿Eliminar carrera?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_carrera.php?action=eliminar&id=' + id;
        }
    });
}
</script>