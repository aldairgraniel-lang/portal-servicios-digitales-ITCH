<?php
// 1. Protección: Solo administradores pueden pasar de aquí.
// Al incluir auth.php, validamos sesión y rol automáticamente.
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include(__DIR__ . "/../../conexion.php");

// 3. Diseño
include("../includes/header.php");

// Lógica de filtrado
$filtro_n_control = '';
if (isset($_GET['n_control']) && trim($_GET['n_control']) !== '') {
    $filtro_n_control = $conexion->real_escape_string(trim($_GET['n_control']));
    $query = "SELECT * FROM justificantes WHERE n_control LIKE '%$filtro_n_control%' ORDER BY fecha_registro DESC";
} else {
    $query = "SELECT * FROM justificantes ORDER BY fecha_registro DESC";
}

$res_justificantes = $conexion->query($query);
?>
<link rel="stylesheet" href="/admin/adminCSS2.css?v=<?php echo time(); ?>"><div class="container py-5">

<link rel="stylesheet" href="../css/estilos.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-4">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <div>
                <h3 class="fw-bold m-0 text-white"><i class="bi bi-file-earmark-medical me-2"></i>Justificantes</h3>
                <span class="text-white-50 small">Gestión de justificantes médicos/escolares</span>
            </div>
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    <?php if(!empty($filtro_n_control)): ?>
                        <a href="justificantes.php" class="btn btn-secondary">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function eliminarJustificante(id) {
    Swal.fire({
        title: '¿Eliminar justificante?',
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
            f.action = 'procesar_justificantes.php';
            f.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="accion" value="eliminar">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}
</script>
</div>