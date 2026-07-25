<?php
// 1. Protección de sesión administrativa con ruta absoluta
include(__DIR__ . '/../includes/auth.php');

// 2. Conexión a la base de datos
include(__DIR__ . "/../../conexion.php");

// 3. Encabezado estructural de la página
include(__DIR__ . "/../includes/header.php");

// Capturamos los filtros desde la URL basados en tu estructura de base de datos
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$carrera_filtro = isset($_GET['carrera']) ? trim($_GET['carrera']) : '';

// Flag para saber si hay un filtro activo y mantener la privacidad inicial
$filtro_activo = (!empty($buscar) || !empty($carrera_filtro));

// OPTIMIZACIÓN: Obtener todas las carreras desde tu catálogo global para usarlas en filtros y modales
$carreras_lista = [];
// Cambia 'carreras' por el nombre real de tu tabla global si difiere (ej. 'cat_carreras')
$carreras_query = mysqli_query($conexion, "SELECT id, nombre FROM carreras ORDER BY nombre ASC");
if ($carreras_query) {
    while ($c = mysqli_fetch_assoc($carreras_query)) {
        $carreras_lista[] = $c;
    }
}

$solicitudes = [];
if ($filtro_activo) {
    $sql = "SELECT id, nombre_completo, numero_control, carrera, fecha_solicitud FROM solicitudes_cartas_buena_conducta WHERE 1=1";
    
    if (!empty($buscar)) {
        $buscar_esc = mysqli_real_escape_string($conexion, $buscar);
        $sql .= " AND (nombre_completo LIKE '%$buscar_esc%' OR numero_control LIKE '%$buscar_esc%')";
    }
    
    if (!empty($carrera_filtro)) {
        $carrera_esc = mysqli_real_escape_string($conexion, $carrera_filtro);
        // Filtramos por el nombre de la carrera guardado en la solicitud
        $sql .= " AND carrera = '$carrera_esc'";
    }
    
    $sql .= " ORDER BY fecha_solicitud DESC";
    $result = mysqli_query($conexion, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $solicitudes[] = $row;
        }
    }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?= time(); ?>">
<link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-4">
    <div class="glass-card p-4 shadow-lg">
        <!-- Encabezado de la Sección -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-check me-2"></i>Buena Conducta</h3>
                <span class="text-white-50 small">Gestión de solicitudes de cartas de buena conducta</span>
            </div>
            <div>
                <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> Inicio
                </a>
            </div>
        </div>

        <!-- Formulario de Filtros y Búsqueda -->
        <form method="GET" action="" class="mb-4 px-2">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="buscar" class="form-label text-white fw-semibold">Buscar Estudiante:</label>
                    <input type="text" id="buscar" name="buscar" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($buscar) ?>" placeholder="Nombre o N° de Control...">
                </div>

                <div class="col-md-4">
                    <label for="carrera" class="form-label text-white fw-semibold">Filtrar por Carrera:</label>
                    <select id="carrera" name="carrera" class="form-select bg-dark text-white border-secondary">
                        <option value="">Todas las Carreras</option>
                        <?php
                        foreach ($carreras_lista as $c) {
                            $selected = ($carrera_filtro === $c['nombre']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($c['nombre']) . '" ' . $selected . '>' . htmlspecialchars($c['nombre']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <?php if ($filtro_activo): ?>
                        <a href="buena_conducta.php" class="btn btn-secondary w-50">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Contenedor de Resultados -->
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead>
                    <tr class="text-info border-bottom border-secondary">
                        <th>N° Control</th>
                        <th>Nombre del Estudiante</th>
                        <th>Carrera</th>
                        <th>Fecha Registro</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-white-100">
                    <?php if (!$filtro_activo): ?>
                        <!-- Privacidad: Lista oculta hasta aplicar filtros -->
                        <tr>
                            <td colspan="5" class="text-center text-white-50 py-5">
                                <i class="bi bi-sliders fa-2x d-block mb-2 text-info"></i>
                                <span>Por favor, ingresa un criterio de búsqueda o selecciona una carrera para mostrar los registros.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php if (!empty($solicitudes)): ?>
                            <?php foreach ($solicitudes as $sol): ?>
                                <tr id="fila-<?= $sol['id'] ?>">
                                    <td class="text-white"><strong><?= htmlspecialchars($sol['numero_control']) ?></strong></td>
                                    <td class="text-white fw-bold"><?= htmlspecialchars($sol['nombre_completo']) ?></td>
                                    <td class="text-white"><?= htmlspecialchars($sol['carrera']) ?></td>
                                    <td class="text-white"><?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-warning text-white me-1" title="Editar" 
                                                data-id="<?= $sol['id'] ?>"
                                                data-nc="<?= htmlspecialchars($sol['numero_control']) ?>"
                                                data-nombre="<?= htmlspecialchars($sol['nombre_completo']) ?>"
                                                data-carrera="<?= htmlspecialchars($sol['carrera']) ?>"
                                                data-fecha="<?= date('Y-m-d', strtotime($sol['fecha_solicitud'])) ?>"
                                                onclick="editarSolicitud(this)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="eliminarSolicitud(<?= $sol['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-warning py-4">
                                    <i class="bi bi-exclamation-circle me-2"></i> No se encontraron registros que coincidan con los filtros aplicados.
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PARA ACTUALIZAR REGISTROS -->
<div class="modal fade" id="modalBuenaConducta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalTitle">Modificar Registro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Apunta al procesador correcto -->
            <form id="formBuenaConducta" action="procesar_cartas_buena_conducta.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="action" value="update">
                    <input type="hidden" name="id" id="solicitud_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label">Número de Control</label>
                        <input type="text" class="form-control bg-secondary text-white border-0" id="numero_control" name="numero_control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control bg-secondary text-white border-0" id="nombre_completo" name="nombre_completo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Carrera</label>
                        <!-- CORRECCIÓN: Ahora es un selector cargado con tu catálogo global de la BD -->
                        <select class="form-select bg-secondary text-white border-0" id="carrera_form" name="carrera" required>
                            <option value="" disabled selected>Selecciona una carrera...</option>
                            <?php
                            foreach ($carreras_lista as $c) {
                                echo '<option value="' . htmlspecialchars($c['nombre']) . '">' . htmlspecialchars($c['nombre']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Registro</label>
                        <input type="date" class="form-control bg-secondary text-white border-0" id="fecha_solicitud" name="fecha_solicitud" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CONTROL JAVASCRIPT Y SWEETALERT2 -->
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
            text = "La solicitud ha sido removida del sistema correctamente.";
        } else if (mensaje === "actualizado") {
            title = "¡Registro modificado!";
            text = "Los datos de la solicitud fueron actualizados con éxito.";
        } else if (mensaje === "error") {
            title = "¡Error!";
            text = "Ocurrió una anomalía al procesar los datos en el servidor.";
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
            window.history.replaceState({}, document.title, window.location.pathname + (urlParams.get('buscar') || urlParams.get('carrera') ? window.location.search : ''));
        });
    }
});

function editarSolicitud(btn) {
    document.getElementById('action').value = 'update';
    document.getElementById('solicitud_id').value = btn.getAttribute('data-id');
    document.getElementById('numero_control').value = btn.getAttribute('data-nc');
    document.getElementById('nombre_completo').value = btn.getAttribute('data-nombre');
    
    // Al ser un select, JS busca la coincidencia exacta por el string/value
    document.getElementById('carrera_form').value = btn.getAttribute('data-carrera');
    document.getElementById('fecha_solicitud').value = btn.getAttribute('data-fecha');
    
    let modal = new bootstrap.Modal(document.getElementById('modalBuenaConducta'));
    modal.show();
}

function eliminarSolicitud(id) {
    Swal.fire({
        title: '¿Eliminar este registro?',
        text: "Esta operación no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = 'procesar_cartas_buena_conducta.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="action" value="delete">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>