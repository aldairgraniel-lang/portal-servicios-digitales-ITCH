<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include("../includes/header.php");

// 1. SANITIZACIÓN Y CAPTURA DE FILTROS
$filtro_n_control = isset($_GET['numero_control']) ? trim($_GET['numero_control']) : '';
$filtro_materia = isset($_GET['materia']) ? trim($_GET['materia']) : '';

// 2. CONSTRUCCIÓN DE CONSULTA SEGURA (Evita Inyección SQL)
$query = "SELECT * FROM solicitudes_cartas_presentacion";
$conditions = [];
$params = [];
$types = "";

if ($filtro_n_control !== '') {
    $conditions[] = "numero_control LIKE ?";
    $params[] = "%" . $filtro_n_control . "%";
    $types .= "s";
}

if ($filtro_materia !== '') {
    $conditions[] = "materia LIKE ?";
    $params[] = "%" . $filtro_materia . "%";
    $types .= "s";
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY fecha_registro DESC";

$stmt = $conexion->prepare($query);
if (count($conditions) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res_solicitudes = $stmt->get_result();
?>

<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        
        <!-- CABECERA -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-person me-2"></i>Cartas de Presentación</h3>
                <span class="text-white-50 small">Gestión integral de trámites y solicitudes estudiantiles</span>
            </div>
            <div>

                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> Inicio
                </a>
            </div>
        </div>

        <!-- FORMULARIO DE FILTROS -->
        <form method="GET" class="mb-4 px-2">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="numero_control" class="col-form-label text-white fw-semibold">N° Control:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="numero_control" name="numero_control" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_n_control) ?>" placeholder="Buscar control...">
                </div>
                
                <div class="col-auto">
                    <label for="materia" class="col-form-label text-white fw-semibold">Materia:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="materia" name="materia" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_materia) ?>" placeholder="Buscar materia...">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <a href="solicitudes_cartas_presentacion.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <!-- TABLA DE RESULTADOS -->
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Estudiante / N° Control</th>
                        <th>Materia / Semestre</th>
                        <th>Dirigido A</th>
                        <th>Vigencia Periodo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-white-100">
                    <?php if ($res_solicitudes && $res_solicitudes->num_rows > 0): ?>
                        <?php while($sol = $res_solicitudes->fetch_assoc()): ?>
                        <tr>
                            <td class="text-white">
                                <!-- CORREGIDO: Usamos la columna 'nombre' correcta -->
                                <div class="fw-bold"><?= htmlspecialchars($sol['nombre'] ?? '') ?></div>
                                <span class="text-muted small"><?= htmlspecialchars($sol['numero_control'] ?? '') ?></span>
                            </td>
                            <td class="text-white">
                                <div><?= htmlspecialchars($sol['materia'] ?? '') ?></div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($sol['semestre'] ?? '') ?>° Semestre</span>
                            </td>
                            <td class="text-white small"><?= htmlspecialchars($sol['dirigido_a'] ?? '') ?></td>
                            <td class="text-white small">
                                <div class="fw-semibold text-info"><?= htmlspecialchars($sol['periodo'] ?? '') ?></div>
                                <div class="text-muted text-nowrap">
                                    <?= !empty($sol['fecha_inicio']) ? date('d/m/Y', strtotime($sol['fecha_inicio'])) : '' ?> al <?= !empty($sol['fecha_final']) ? date('d/m/Y', strtotime($sol['fecha_final'])) : '' ?>
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-warning text-white me-1" title="Editar Solicitud" 
                                        onclick="editarPresentacion(<?= htmlspecialchars(json_encode($sol), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="eliminarPresentacion(<?= intval($sol['id']) ?>)" class="btn btn-sm btn-danger" title="Eliminar Solicitud">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-white py-4">
                                <i class="bi bi-exclamation-circle me-2"></i> No se encontraron registros de solicitudes.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL COMPLETO DE ALUMNO (EDICIÓN Y CREACIÓN) -->
<div class="modal fade" id="modalPresentacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitlePresentacion">Detalles de la Solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formPresentacion" action="procesar_cartas_presentacion.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accionPresentacion" value="guardar">
                    <input type="hidden" name="id" id="presentacion_id" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label text-white-50 small fw-bold">NOMBRE COMPLETO</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary text-uppercase" id="nombre" name="nombre" required placeholder="Ej. PÉREZ LÓPEZ JUAN">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="numero_control_form" class="form-label text-white-50 small fw-bold">NÚMERO DE CONTROL</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary text-uppercase" id="numero_control_form" name="numero_control" required minlength="8" maxlength="10" pattern="[0-9A-Za-z]{8,10}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="dirigido_a" class="form-label text-white-50 small fw-bold">A QUIÉN VA DIRIGIDO (PUESTO Y NOMBRE)</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary text-uppercase" id="dirigido_a" name="dirigido_a" required placeholder="Ej. DRA. ARIADNE JUDITH TORRES PEDROZA">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="materia_form" class="form-label text-white-50 small fw-bold">MATERIA</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="materia_form" name="materia" required placeholder="Ej. Residencia Profesional">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="semestre" class="form-label text-white-50 small fw-bold">SEMESTRE</label>
                            <select name="semestre" id="semestre" class="form-select bg-dark text-white border-secondary" required>
                                <option value="" selected disabled>Seleccione semestre...</option>
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i; ?>"><?= $i; ?>° Semestre</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="periodo" class="form-label text-white-50 small fw-bold">PERIODO</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" id="periodo" name="periodo" required placeholder="Ej. Enero - Junio 2026">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label text-white-50 small fw-bold">FECHA DE INICIO</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_final" class="form-label text-white-50 small fw-bold">FECHA FINAL</label>
                            <input type="date" id="fecha_final" name="fecha_final" class="form-control bg-dark text-white border-secondary" required>
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
        let title = "", text = "", icon = "success";
        if (mensaje === "eliminado") { title = "¡Registro eliminado!"; text = "La solicitud se borró del sistema."; }
        else if (mensaje === "guardado") { title = "¡Registro guardado!"; text = "La solicitud se capturó con éxito."; }
        else if (mensaje === "actualizado") { title = "¡Registro actualizado!"; text = "Los cambios se guardaron con éxito."; }
        else if (mensaje === "error") { title = "¡Error!"; text = "Ocurrió un problema en la operación."; icon = "error"; }

        Swal.fire({
            title: title, text: text, icon: icon,
            background: '#0f172a', color: '#fff', confirmButtonColor: '#3b82f6'
        }).then(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    }

    // Regla de Fechas Dinámicas
    const fInicio = document.getElementById('fecha_inicio');
    const fFinal = document.getElementById('fecha_final');
    fInicio.addEventListener('change', () => { if(fInicio.value) fFinal.min = fInicio.value; });

    // Forzar mayúsculas limpias en procesamiento backend
    document.getElementById('formPresentacion').addEventListener('submit', function() {
        document.getElementById('nombre').value = document.getElementById('nombre').value.toUpperCase().trim();
        document.getElementById('numero_control_form').value = document.getElementById('numero_control_form').value.toUpperCase().trim();
        document.getElementById('dirigido_a').value = document.getElementById('dirigido_a').value.toUpperCase().trim();
    });
});

function limpiarPresentacion() {
    document.getElementById('formPresentacion').reset();
    document.getElementById('accionPresentacion').value = 'guardar';
    document.getElementById('presentacion_id').value = '';
    document.getElementById('modalTitlePresentacion').innerText = 'Nueva Solicitud de Carta';
}

function editarPresentacion(sol) {
    document.getElementById('accionPresentacion').value = 'actualizar';
    document.getElementById('presentacion_id').value = sol.id;
    document.getElementById('nombre').value = sol.nombre; // CORREGIDO
    document.getElementById('numero_control_form').value = sol.numero_control;
    document.getElementById('dirigido_a').value = sol.dirigido_a;
    document.getElementById('materia_form').value = sol.materia;
    document.getElementById('semestre').value = sol.semestre;
    document.getElementById('periodo').value = sol.periodo;
    document.getElementById('fecha_inicio').value = sol.fecha_inicio;
    document.getElementById('fecha_final').value = sol.fecha_final;
    document.getElementById('modalTitlePresentacion').innerText = 'Editar Solicitud N° ' + sol.numero_control;

    let modal = new bootstrap.Modal(document.getElementById('modalPresentacion'));
    modal.show();
}

function eliminarPresentacion(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Se borrará toda la información vinculada a esta solicitud.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',   cancelButtonColor: '#6c757d',
        background: '#0f172a', color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const f = document.createElement('form');
            f.method = 'POST'; f.action = 'procesar_cartas_presentacion.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>

<?php 
$stmt->close();
$conexion->close();
?>