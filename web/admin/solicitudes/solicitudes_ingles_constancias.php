<?php
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include("../includes/header.php");

// Lógica de filtrado
$filtro_n_control = '';
$filtro_carrera = '';
$filtro_periodo = '';

if (isset($_GET['numero_control'])) {
    $filtro_n_control = trim($_GET['numero_control']);
}
if (isset($_GET['carrera'])) {
    $filtro_carrera = trim($_GET['carrera']);
}
if (isset($_GET['periodo'])) {
    $filtro_periodo = trim($_GET['periodo']);
}

$condiciones = [];

if ($filtro_n_control !== '') {
    $nc_escaped = $conexion->real_escape_string($filtro_n_control);
    $condiciones[] = "numero_control LIKE '%$nc_escaped%'";
}

if ($filtro_carrera !== '') {
    $carrera_escaped = $conexion->real_escape_string($filtro_carrera);
    $condiciones[] = "carrera = '$carrera_escaped'";
}

if ($filtro_periodo !== '') {
    $periodo_escaped = $conexion->real_escape_string($filtro_periodo);
    $condiciones[] = "periodo = '$periodo_escaped'";
}

if (count($condiciones) > 0) {
    $query = "SELECT * FROM registro_ingles WHERE " . implode(' AND ', $condiciones) . " ORDER BY fecha_registro DESC";
} else {
    $query = "SELECT * FROM registro_ingles ORDER BY fecha_registro DESC";
}

$res_solicitudes = $conexion->query($query);

// Obtener datos para los selects desde registro_ingles (filtros de búsqueda)
$carreras = [];
$res_carreras = $conexion->query("SELECT DISTINCT carrera FROM registro_ingles WHERE carrera IS NOT NULL AND carrera != '' ORDER BY carrera ASC");
if ($res_carreras) {
    while ($row = $res_carreras->fetch_assoc()) {
        $carreras[] = $row['carrera'];
    }
    $res_carreras->free();
}

$periodos = [];
$res_periodos = $conexion->query("SELECT DISTINCT periodo FROM registro_ingles WHERE periodo IS NOT NULL AND periodo != '' ORDER BY periodo DESC");
if ($res_periodos) {
    while ($row = $res_periodos->fetch_assoc()) {
        $periodos[] = $row['periodo'];
    }
    $res_periodos->free();
}

// ------------------------------------------------------------------------
// Obtener datos para los SELECTS del Modal desde sus tablas reales en la BD.
// Nota: Ajusta los nombres de las tablas y las columnas según la estructura real de tu base de datos.
// ------------------------------------------------------------------------
$carreras_db = [];
$res_carr_db = $conexion->query("SELECT * FROM carreras ORDER BY nombre ASC"); 
if ($res_carr_db) {
    while ($row = $res_carr_db->fetch_assoc()) {
        $carreras_db[] = $row;
    }
    $res_carr_db->free();
}

$periodos_db = [];
$res_per_db = $conexion->query("SELECT * FROM periodos ORDER BY nombre DESC");
if ($res_per_db) {
    while ($row = $res_per_db->fetch_assoc()) {
        $periodos_db[] = $row;
    }
    $res_per_db->free();
}

$tipos_alumno_db = [];
$res_tipo_db = $conexion->query("SELECT * FROM tipo_estudiante ORDER BY nombre ASC");
if ($res_tipo_db) {
    while ($row = $res_tipo_db->fetch_assoc()) {
        $tipos_alumno_db[] = $row;
    }
    $res_tipo_db->free();
}

$semestres_db = [];
$res_sem_db = $conexion->query("SELECT * FROM semestres ORDER BY numero ASC");
if ($res_sem_db) {
    while ($row = $res_sem_db->fetch_assoc()) {
        $semestres_db[] = $row;
    }
    $res_sem_db->free();
}
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
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> Inicio
                </a>
            </div>
        </div>

        <form method="GET" class="mb-4 px-2">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-auto">
                    <input type="text" id="numero_control" name="numero_control" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_n_control ?? '') ?>" placeholder="Escribe el N° Control...">
                </div>

                <div class="col-12 col-md-auto">
                    <select id="carrera_filtro" name="carrera" class="form-control bg-dark text-white border-secondary">
                        <option value="">Todas las carreras</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= (isset($filtro_carrera) && $filtro_carrera === $c) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-auto">
                    <select id="periodo_filtro" name="periodo" class="form-control bg-dark text-white border-secondary">
                        <option value="">Todos los periodos</option>
                        <?php foreach ($periodos as $p): ?>
                            <option value="<?= htmlspecialchars($p) ?>" <?= (isset($filtro_periodo) && $filtro_periodo === $p) ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <a href="solicitudes_ingles_constancias.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
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
                        <th>Tipo Alumno</th>
                        <th>Semestre</th>
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
                            <td class="text-white"><?= htmlspecialchars($sol['tipo_alumno']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($sol['semestre'] ?? 'N/A') ?></td>
                            <td class="text-white"><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex">
                                    <button class="btn btn-sm btn-warning text-white me-1" title="Editar" 
                                            onclick="editarIngles(<?= htmlspecialchars(json_encode($sol)) ?>)">
                                        Editar
                                    </button>
                                    <button onclick="eliminarIngles(<?= $sol['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar solicitud">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-white py-4">
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
                        <select class="form-control bg-dark text-white border-secondary" id="carrera_ingles" name="carrera" required>
                            <option value="">Seleccione una carrera</option>
                            <?php foreach ($carreras_db as $c): ?>
                                <option value="<?= htmlspecialchars($c['nombre'] ?? $c['carrera']) ?>"><?= htmlspecialchars($c['nombre'] ?? $c['carrera']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="periodo_ingles" class="form-label">Periodo</label>
                        <select class="form-control bg-dark text-white border-secondary" id="periodo_ingles" name="periodo" required>
                            <option value="">Seleccione un periodo</option>
                            <?php foreach ($periodos_db as $p): ?>
                                <option value="<?= htmlspecialchars($p['periodo'] ?? $p['nombre']) ?>"><?= htmlspecialchars($p['periodo'] ?? $p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_alumno_ingles" class="form-label">Tipo de Alumno</label>
                        <select class="form-control bg-dark text-white border-secondary" id="tipo_alumno_ingles" name="tipo_alumno" required>
                            <option value="">Seleccione el tipo de alumno</option>
                            <?php foreach ($tipos_alumno_db as $t): ?>
                                <option value="<?= htmlspecialchars($t['tipo'] ?? $t['nombre']) ?>"><?= htmlspecialchars($t['tipo'] ?? $t['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="semestre_ingles" class="form-label">Semestre</label>
                        <select class="form-control bg-dark text-white border-secondary" id="semestre_ingles" name="semestre">
                            <option value="">Seleccione el semestre (Opcional)</option>
                            <?php foreach ($semestres_db as $s): ?>
                                <option value="<?= htmlspecialchars($s['semestre'] ?? $s['numero'] ?? '') ?>"><?= htmlspecialchars($s['semestre'] ?? $s['numero'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
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
    document.getElementById('carrera_ingles').value = '';
    document.getElementById('periodo_ingles').value = '';
    document.getElementById('tipo_alumno_ingles').value = '';
    document.getElementById('semestre_ingles').value = '';
    document.getElementById('modalTitleIngles').innerText = 'Nueva Solicitud';
}

function editarIngles(sol) {
    document.getElementById('accionIngles').value = 'actualizar';
    document.getElementById('ingles_id').value = sol.id;
    document.getElementById('nombre_ingles').value = sol.nombre;
    document.getElementById('numero_control_ingles').value = sol.numero_control;
    document.getElementById('carrera_ingles').value = sol.carrera;
    document.getElementById('periodo_ingles').value = sol.periodo;
    document.getElementById('tipo_alumno_ingles').value = sol.tipo_alumno;
    document.getElementById('semestre_ingles').value = sol.semestre;
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