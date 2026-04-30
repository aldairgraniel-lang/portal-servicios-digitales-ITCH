<?php 
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php"); 

$res_avisos = $conexion->query("SELECT * FROM avisos ORDER BY fecha_pub DESC");
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Estilos de tabla y inputs (Glassmorphism) */
    .table {
        --bs-table-bg: transparent !important;
        --bs-table-color: white !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .input-glass {
        background: transparent !important;
        border: 1px solid transparent !important;
        color: #fff !important;
        padding: 8px !important;
        width: 100%;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .input-glass:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    .input-glass:focus {
        background: rgba(15, 23, 42, 0.7) !important;
        border-color: #3b82f6 !important;
        color: #60a5fa !important;
        outline: none;
    }

    .select-glass {
        background-color: #1e293b !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 8px;
    }

    /* Botones de Acción */
    .btn-action {
        width: 38px; height: 38px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        color: white;
        transition: all 0.3s;
    }

    .btn-save:hover { background: #3b82f6 !important; box-shadow: 0 0 15px rgba(59, 130, 246, 0.6); }
    .btn-delete:hover { background: #ef4444 !important; box-shadow: 0 0 15px rgba(239, 68, 68, 0.6); }

    .table thead th {
        background: rgba(255, 255, 255, 0.05) !important;
        color: #94a3b8 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        border-top: none;
        padding: 15px 10px;
    }
</style>

<div class="glass-card p-4 shadow-lg" style="background: rgba(255,255,255,0.02); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h3 class="fw-bold m-0"><i class="bi bi-pencil-square me-2"></i>Gestión de Avisos</h3>
            <span class="text-white-50 small">Actualiza o elimina comunicados directamente</span>
        </div>
        <div class="d-flex gap-2">
            <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-house-door me-1"></i> Inicio
            </a>
            <button onclick="confirmarBorrarTodo()" class="btn btn-danger btn-sm rounded-pill px-3">
                <i class="bi bi-trash-fill me-1"></i> Borrar Todo
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th width="10%">Estado</th>
                    <th width="50%">Contenido del Aviso</th>
                    <th width="20%">Categoría</th>
                    <th width="20%" class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($av = $res_avisos->fetch_assoc()): ?>
                <tr>
                    <form action="procesar_aviso.php" method="POST">
                        <input type="hidden" name="id" value="<?= $av['id'] ?>">
                        
                        <td>
                            <button type="submit" name="accion" value="toggle" class="btn-action border-0" title="Cambiar visibilidad" style="color: <?= $av['activo'] ? '#4ade80' : '#f87171' ?>;">
                                <i class="bi <?= $av['activo'] ? 'bi-toggle-on' : 'bi-toggle-off' ?> fs-4"></i>
                            </button>
                        </td>

                        <td>
                            <input type="text" name="titulo" class="input-glass fw-bold" value="<?= htmlspecialchars($av['titulo']) ?>" style="color: #3b82f6 !important;">
                            <textarea name="contenido" class="input-glass small text-white-50" rows="1"><?= htmlspecialchars($av['contenido']) ?></textarea>
                        </td>

                        <td>
                            <select name="tipo" class="form-select form-select-sm select-glass">
                                <option value="info" <?= $av['tipo']=='info' ? 'selected' : '' ?>>Info</option>
                                <option value="advertencia" <?= $av['tipo']=='advertencia' ? 'selected' : '' ?>>Aviso</option>
                                <option value="urgente" <?= $av['tipo']=='urgente' ? 'selected' : '' ?>>Urgente</option>
                            </select>
                        </td>

                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="submit" name="accion" value="editar" class="btn-action btn-save" title="Guardar cambios">
                                    <i class="bi bi-cloud-arrow-up-fill"></i>
                                </button>
                                <button type="button" class="btn-action btn-delete" onclick="eliminar(<?= $av['id'] ?>)" title="Eliminar este aviso">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Función para eliminar un solo aviso
function eliminar(id) {
    Swal.fire({
        title: '¿Eliminar aviso?',
        text: "Se borrará permanentemente de la base de datos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarFormulario(id, 'eliminar');
        }
    });
}

// Función para borrar TODOS los avisos (Doble Confirmación)
function confirmarBorrarTodo() {
    Swal.fire({
        title: '¡ALERTA MÁXIMA!',
        text: "¿Estás COMPLETAMENTE seguro de borrar TODOS los avisos? No hay vuelta atrás.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, estoy seguro',
        cancelButtonText: 'No, cancelar',
        background: '#0f172a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            // SEGUNDA CONFIRMACIÓN
            Swal.fire({
                title: 'Confirmación Final',
                text: "Escribe 'BORRAR TODO' para confirmar la limpieza de la base de datos:",
                input: 'text',
                inputAttributes: { autocapitalize: 'off' },
                showCancelButton: true,
                confirmButtonText: 'EJECUTAR LIMPIEZA',
                confirmButtonColor: '#d33',
                background: '#0f172a',
                color: '#fff',
                preConfirm: (texto) => {
                    if (texto !== 'BORRAR TODO') {
                        Swal.showValidationMessage('Debes escribir la frase exacta para continuar');
                    }
                }
            }).then((finalResult) => {
                if (finalResult.isConfirmed) {
                    enviarFormulario(0, 'borrar_todo');
                }
            });
        }
    });
}

function enviarFormulario(id, accion) {
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'procesar_aviso.php';
    f.innerHTML = `
        <input type="hidden" name="id" value="${id}">
        <input type="hidden" name="accion" value="${accion}">
    `;
    document.body.appendChild(f);
    f.submit();
}

// Notificación de éxito si existe un mensaje de sesión
<?php if(isset($_SESSION['mensaje'])): ?>
    Swal.fire({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
        icon: 'success', title: '<?= $_SESSION['mensaje'] ?>',
        background: '#1e293b', color: '#fff'
    });
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>
</script>

<?php ?>