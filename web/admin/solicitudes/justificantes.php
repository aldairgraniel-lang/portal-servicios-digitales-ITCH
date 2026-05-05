<?php
// 1. Protección: Solo administradores pueden pasar de aquí.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include(__DIR__ . "/../../conexion.php");

// 3. Diseño
include("../includes/header.php");

// Lógica de filtrado
$filtro_n_control = '';
$filtro_motivo = '';
$query = "SELECT * FROM justificantes";

$conditions = [];

if (isset($_GET['n_control']) && trim($_GET['n_control']) !== '') {
    $filtro_n_control = $conexion->real_escape_string(trim($_GET['n_control']));
    $conditions[] = "n_control LIKE '%$filtro_n_control%'";
}

if (isset($_GET['motivo']) && trim($_GET['motivo']) !== '') {
    $filtro_motivo = $conexion->real_escape_string(trim($_GET['motivo']));
    $conditions[] = "motivo = '$filtro_motivo'";
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY fecha_registro DESC";

$res_justificantes = $conexion->query($query);
?>
<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?php echo time(); ?>">
<div class="container py-5">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-4">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-medical me-2"></i>Justificantes</h3>
                <span class="text-white-50 small">Gestión de justificantes médicos/escolares</span>
            </div>
            <div>
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> Inicio
                </a>
            </div>
        </div>

        <form method="GET" class="mb-4 px-2">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="n_control" class="col-form-label text-white fw-semibold">Filtrar por N° Control:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="n_control" name="n_control" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_n_control) ?>" placeholder="Escribe el N° Control...">
                </div>

                <div class="col-auto">
                    <label for="motivo" class="col-form-label text-white fw-semibold">Filtrar por Motivo:</label>
                </div>
                <div class="col-auto">
                    <select id="motivo" name="motivo" class="form-control bg-dark text-white border-secondary">
                        <option value="">Todos los motivos</option>
                        <?php
                        $motivos_query = "SELECT DISTINCT motivo FROM justificantes WHERE motivo IS NOT NULL AND motivo != '' ORDER BY motivo ASC";
                        $res_motivos = $conexion->query($motivos_query);
                        if ($res_motivos) {
                            while ($m = $res_motivos->fetch_assoc()) {
                                $selected = ($filtro_motivo === $m['motivo']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($m['motivo']) . '" ' . $selected . '>' . htmlspecialchars($m['motivo']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    
                    <a href="justificantes.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

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
                <tbody class="text-white-100">
                    <?php if ($res_justificantes && $res_justificantes->num_rows > 0): ?>
                        <?php while($jus = $res_justificantes->fetch_assoc()): ?>
                        <tr>
                            <td class="text-white fw-bold"><?= htmlspecialchars($jus['nombre']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($jus['n_control']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($jus['motivo']) ?></td>
                            <td class="text-white">
                                <?= date('d/m/Y', strtotime($jus['fecha_inicio'])) ?> al 
                                <?= date('d/m/Y', strtotime($jus['fecha_fin'])) ?>
                            </td>
                            <td class="text-white"><?= date('d/m/Y', strtotime($jus['fecha_registro'])) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-warning text-white me-1" title="Editar" 
                                        onclick="editarJustificante(<?= htmlspecialchars(json_encode($jus)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="eliminarJustificante(<?= $jus['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-white py-4">
                                <i class="bi bi-exclamation-circle me-2"></i> No se encontraron registros.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJustificante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitle">Nuevo Justificante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formJustificante" action="procesar_justificantes.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accion" value="guardar">
                    <input type="hidden" name="id" id="justificante_id" value="">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Estudiante</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="n_control_form" class="form-label">N° de Control</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="n_control_form" name="n_control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="label-tecnm">MOTIVO</label>
                        <select name="motivo" id="motivo_form" class="form-select bg-dark text-white border-secondary" required>
                            <option value="" selected disabled>Seleccione una opción...</option>
                            <option value="Enfermedad">Enfermedad</option>
                            <option value="Asuntos Académicos">Asuntos Académicos</option>
                            <option value="Fuerza Mayor">Fuerza Mayor</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control bg-dark text-white border-secondary" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control bg-dark text-white border-secondary" id="fecha_fin" name="fecha_fin" required>
                        </div>
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
            text = "El registro ha sido eliminado correctamente.";
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

function limpiarFormulario() {
    document.getElementById('formJustificante').reset();
    document.getElementById('accion').value = 'guardar';
    document.getElementById('justificante_id').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Justificante';
    // Se restaura el select a la opción por defecto
    document.getElementById('motivo_form').selectedIndex = 0;
}

function editarJustificante(jus) {
    document.getElementById('accion').value = 'actualizar';
    document.getElementById('justificante_id').value = jus.id;
    document.getElementById('nombre').value = jus.nombre;
    document.getElementById('n_control_form').value = jus.n_control;
    document.getElementById('motivo_form').value = jus.motivo;
    document.getElementById('fecha_inicio').value = jus.fecha_inicio;
    document.getElementById('fecha_fin').value = jus.fecha_fin;
    document.getElementById('modalTitle').innerText = 'Editar Justificante';
    
    let modal = new bootstrap.Modal(document.getElementById('modalJustificante'));
    modal.show();
}

function eliminarJustificante(id) {
    Swal.fire({
        title: '¿Eliminar justificante?',
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
            f.action = 'procesar_justificantes.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>
</div>