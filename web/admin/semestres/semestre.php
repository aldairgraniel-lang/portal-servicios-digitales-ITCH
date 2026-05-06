<?php
// 1. Protección: Valida sesión y rol de administrador antes de cargar el archivo.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión y Header
include('../../conexion.php');
include("../includes/header.php");

$result = mysqli_query($conexion, "SELECT * FROM semestres ORDER BY numero ASC");
?>
<link rel="stylesheet" href="../css/tablasG.css">


<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-calendar-event me-2"></i> Gestión de Semestres</h3>
                <span class="text-white-50 small">Administra los niveles académicos</span>
            </div>
            <div class="d-flex gap-2">
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="nuevoSemestre()">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Semestre
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Número de Semestre</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>Semestre <?= $row['numero'] ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <?php if($row['numero'] > 12): ?>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarSem(<?= $row['id'] ?>)">
                                        Eliminar
                                    </button>
                                <?php endif; ?>
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
function nuevoSemestre() {
    Swal.fire({
        title: 'Nuevo Semestre',
        input: 'number',
        inputLabel: 'Número del semestre',
        inputPlaceholder: 'Ingresa el número (mayor o igual a 12)',
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
                return '¡El número no puede estar vacío!';
            }
            if (parseInt(value) < 12) {
                return '¡El número debe ser de 12 hacia arriba!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_semestre.php';
            form.method = 'POST';

            let numeroInput = document.createElement('input');
            numeroInput.type = 'hidden';
            numeroInput.name = 'numero';
            numeroInput.value = result.value;

            form.appendChild(numeroInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function eliminarSem(id) {
    Swal.fire({
        title: '¿Eliminar semestre?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_semestre.php?action=eliminar&id=' + id;
        }
    });
}
</script>