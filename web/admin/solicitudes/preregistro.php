<?php
// 1. Protección: Solo administradores pueden pasar de aquí.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include(__DIR__ . "/../../conexion.php");

// 3. Diseño
include("../includes/header.php");

// Obtener datos para los select desde sus respectivas tablas
$carreras = [];
if ($res_carreras = $conexion->query("SELECT * FROM carreras ORDER BY nombre ASC")) {
    while ($row = $res_carreras->fetch_assoc()) {
        $carreras[] = $row;
    }
    $res_carreras->free();
}

$cursos = [];
if ($res_cursos = $conexion->query("SELECT * FROM cursos ORDER BY nombre ASC")) {
    while ($row = $res_cursos->fetch_assoc()) {
        $cursos[] = $row;
    }
    $res_cursos->free();
}

$representantes = [];
if ($res_rep = $conexion->query("SELECT * FROM representantes ORDER BY nombre ASC")) {
    while ($row = $res_rep->fetch_assoc()) {
        $representantes[] = $row;
    }
    $res_rep->free();
}

// Lógica de filtrado
$filtro_n_control = '';
$filtro_carrera = '';

if (isset($_GET['numero_control'])) {
    $filtro_n_control = trim($_GET['numero_control']);
}
if (isset($_GET['carrera'])) {
    $filtro_carrera = trim($_GET['carrera']);
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

if (count($condiciones) > 0) {
    $query = "SELECT * FROM VERANO WHERE " . implode(' AND ', $condiciones) . " ORDER BY fecha_registro DESC";
} else {
    $query = "SELECT * FROM VERANO ORDER BY fecha_registro DESC";
}

$res_verano = $conexion->query($query);
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
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-mortarboard me-2"></i>Verano</h3>
                <span class="text-white-50 small">Gestión de pre-registros de cursos de verano</span>
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
                    <label for="numero_control" class="col-form-label text-white fw-semibold">N° Control:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="numero_control" name="numero_control" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_n_control) ?>" placeholder="Escribe el N° Control...">
                </div>
                
                <div class="col-auto">
                    <label for="carrera_filtro" class="col-form-label text-white fw-semibold">Carrera:</label>
                </div>
                <div class="col-auto">
                    <select id="carrera_filtro" name="carrera" class="form-control bg-dark text-white border-secondary">
                        <option value="">Todas las carreras</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= htmlspecialchars($c['nombre']) ?>" <?= ($filtro_carrera === $c['nombre']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <a href="preregistro.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>N° Control</th>
                        <th>Nombre Completo</th>
                        <th>Carrera</th>
                        <th>Semestre</th>
                        <th>Curso de Interés</th>
                        <th>Contacto</th>
                        <th>Registro</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-white-100">
                    <?php if ($res_verano && $res_verano->num_rows > 0): ?>
                        <?php while($item = $res_verano->fetch_assoc()): ?>
                        <tr>
                            <td class="text-white"><?= htmlspecialchars($item['numero_control']) ?></td>
                            <td class="text-white fw-bold"><?= htmlspecialchars($item['nombre'] . ' ' . $item['apellidos']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($item['carrera']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($item['semestre']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($item['curso_interes']) ?></td>
                            <td class="text-white"><?= htmlspecialchars($item['numero_celular']) ?></td>
                            <td class="text-white"><?= date('d/m/Y', strtotime($item['fecha_registro'])) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-warning text-white me-1" title="Editar" 
                                        onclick="editarVerano(<?= htmlspecialchars(json_encode($item)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="eliminarVerano(<?= $item['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-white py-4">
                                <i class="bi bi-exclamation-circle me-2"></i> No se encontraron registros.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerano" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitleVerano">Nuevo Pre-registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formVerano" action="procesar_preregistro.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="accionVerano" value="guardar">
                    <input type="hidden" name="id" id="verano_id" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre(s)</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="apellidos" name="apellidos" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_control_form" class="form-label">N° de Control</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="numero_control_form" name="numero_control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="numero_celular" class="form-label">N° de Celular</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary" id="numero_celular" name="numero_celular" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="carrera" class="form-label">Carrera</label>
                            <select class="form-control bg-dark text-white border-secondary" id="carrera" name="carrera" required>
                                <option value="" disabled selected>Seleccione una carrera</option>
                                <?php foreach ($carreras as $c): ?>
                                    <option value="<?= htmlspecialchars($c['nombre']) ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="semestre" class="form-label">Semestre</label>
                            <input type="number" class="form-control bg-dark text-white border-secondary" id="semestre" name="semestre" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="curso_interes" class="form-label">Curso de Interés</label>
                        <select class="form-control bg-dark text-white border-secondary" id="curso_interes" name="curso_interes" required>
                            <option value="" disabled selected>Seleccione un curso</option>
                            <?php foreach ($cursos as $cu): ?>
                                <option value="<?= htmlspecialchars($cu['nombre']) ?>"><?= htmlspecialchars($cu['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="representante_1" class="form-label">Representante 1</label>
                            <select class="form-control bg-dark text-white border-secondary" id="representante_1" name="representante_1" required>
                                <option value="" disabled selected>Seleccione representante</option>
                                <?php foreach ($representantes as $r): ?>
                                    <option value="<?= htmlspecialchars($r['nombre']) ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="representante_2" class="form-label">Representante 2</label>
                            <select class="form-control bg-dark text-white border-secondary" id="representante_2" name="representante_2" required>
                                <option value="" disabled selected>Seleccione representante</option>
                                <?php foreach ($representantes as $r): ?>
                                    <option value="<?= htmlspecialchars($r['nombre']) ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
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
function actualizarSelects() {
    const rep1 = document.getElementById('representante_1');
    const rep2 = document.getElementById('representante_2');
    
    const val1 = rep1.value;
    const val2 = rep2.value;

    for (let option of rep1.options) {
        option.disabled = false;
    }
    for (let option of rep2.options) {
        option.disabled = false;
    }

    if (val1) {
        for (let option of rep2.options) {
            if (option.value === val1 && option.value !== "") {
                option.disabled = true;
            }
        }
    }

    if (val2) {
        for (let option of rep1.options) {
            if (option.value === val2 && option.value !== "") {
                option.disabled = true;
            }
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const rep1 = document.getElementById('representante_1');
    const rep2 = document.getElementById('representante_2');

    rep1.addEventListener('change', actualizarSelects);
    rep2.addEventListener('change', actualizarSelects);

    const formVerano = document.getElementById('formVerano');
    formVerano.addEventListener('submit', function(e) {
        const rep1Val = rep1.value;
        const rep2Val = rep2.value;

        if (rep1Val && rep2Val && rep1Val === rep2Val) {
            e.preventDefault();
            Swal.fire({
                title: '¡Error!',
                text: 'El Representante 1 y el Representante 2 deben ser diferentes.',
                icon: 'error',
                background: '#0f172a',
                color: '#fff',
                confirmButtonColor: '#3b82f6'
            });
        }
    });

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

function limpiarVerano() {
    document.getElementById('formVerano').reset();
    document.getElementById('accionVerano').value = 'guardar';
    document.getElementById('verano_id').value = '';
    document.getElementById('modalTitleVerano').innerText = 'Nuevo Pre-registro';
    
    const rep1 = document.getElementById('representante_1');
    const rep2 = document.getElementById('representante_2');
    for (let option of rep1.options) { option.disabled = false; }
    for (let option of rep2.options) { option.disabled = false; }
}

function editarVerano(item) {
    document.getElementById('accionVerano').value = 'actualizar';
    document.getElementById('verano_id').value = item.id;
    document.getElementById('nombre').value = item.nombre;
    document.getElementById('apellidos').value = item.apellidos;
    document.getElementById('numero_control_form').value = item.numero_control;
    document.getElementById('numero_celular').value = item.numero_celular;
    document.getElementById('carrera').value = item.carrera;
    document.getElementById('semestre').value = item.semestre;
    document.getElementById('curso_interes').value = item.curso_interes;
    document.getElementById('representante_1').value = item.representante_1;
    document.getElementById('representante_2').value = item.representante_2;
    document.getElementById('modalTitleVerano').innerText = 'Editar Pre-registro';

    actualizarSelects();

    let modal = new bootstrap.Modal(document.getElementById('modalVerano'));
    modal.show();
}

function eliminarVerano(id) {
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
            f.action = 'procesar_preregistro.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>
</div>