<?php
// 1. Protección: Solo admins pueden ver este archivo.
// Al incluir auth.php, esto valida la sesión y el rol automáticamente.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a base de datos
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// 3. Diseño (el header ya incluye el CSS necesario)
include("../includes/header.php");

// Lógica de filtrado
$filtro_n_control = '';
if (isset($_GET['numero_control']) && trim($_GET['numero_control']) !== '') {
    $filtro_n_control = $conexion->real_escape_string(trim($_GET['numero_control']));
    $query = "SELECT * FROM registro_ingles WHERE numero_control LIKE '%$filtro_n_control%' ORDER BY fecha_registro DESC";
} else {
    $query = "SELECT * FROM registro_ingles ORDER BY fecha_registro DESC";
}

$res_solicitudes = $conexion->query($query);
?>
<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?php echo time(); ?>"><div class="container py-5">

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
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
        </div>

        <form method="GET" class="mb-4 px-2">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="numero_control" class="col-form-label text-white fw-semibold">Filtrar por N° Control:</label>
                </div>
                <div class="col-auto">
                    <input type="text" id="numero_control" name="numero_control" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($filtro_n_control) ?>" placeholder="Escribe el N° Control...">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <?php if(!empty($filtro_n_control)): ?>
                        <a href="solicitudes_ingles_constancias.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </a>
                    <?php endif; ?>
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
                            <td class="text-white"><?= date('d/m/Y', strtotime($sol['fecha_registro'])) ?></td>
                            <td class="text-end">
                                <button onclick="eliminarSolicitud(<?= $sol['id'] ?>)" class="btn btn-sm btn-danger" title="Eliminar solicitud">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-white py-4">
                                <i class="bi bi-exclamation-circle me-2"></i> No se encontraron registros.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function eliminarSolicitud(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
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