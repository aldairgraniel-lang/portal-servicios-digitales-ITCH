<?php 
// 1. Configuración inicial
include("../conexion.php");
include("includes/header.php");
    
// --- NUEVA SECCIÓN: Mapa de servicios para la limpieza ---
$servicios_map = [
    'VERANO' => 'Solicitudes de Pre registro verano',
    'registro_ingles' => 'solicitudes de constancias de inglés no inconvenidencia',
    'solicitudes_cartas_presentacion' => 'solicitudes de cartas de presentación',
    'solicitudes_cartas_aceptacion' => 'Solicitudes de Cartas de Aceptación',
    'solicitudes_cartas_terminacion' => 'Solicitudes de Cartas de Terminación',
    'solicitudes_cartas_buena_conducta' => 'Solicitudes de Cartas de Buena Conducta', // <-- Agregado para la limpieza masiva
    'justificantes' => 'solicitudes de justificantes',
    'avisos' => 'Avisos'
];

// 2. FuncionesA
function get_count($conexion, $tabla) {
    $query = mysqli_query($conexion, "SELECT COUNT(*) as total FROM $tabla");
    return mysqli_fetch_assoc($query)['total'] ?? 0;
}

$datos = [
    "Verano" => [
        ["Carreras totales", get_count($conexion, "carreras"), "carreras/carreras.php"],
        ["Cursos totales", get_count($conexion, "cursos"), "cursos/cursos.php"],
        ["Semestres totales", get_count($conexion, "semestres"), "semestres/semestre.php"],
        ["Representantes totales", get_count($conexion, "representantes"), "representantes/representantes.php"],
        ["Usuarios totales", get_count($conexion, "usuarios"), "usuarios/usuarios.php"],
        ["Periodos totales", get_count($conexion, "periodos"), "periodos/periodos.php"],
        ["Trámites Totales", get_count($conexion, "tipos_tramite"), "tramites/tramites.php"]
    ],
    "Tramites" => [
        ["Avisos totales - docente", get_count($conexion, "avisos"), "avisos/avisos.php"],
        ["Solicitudes - Pre registro verano ", get_count($conexion, "VERANO"), "solicitudes/preregistro.php"],
        ["Solicitudes - constancias de inglés no inconvenidencia", get_count($conexion, "registro_ingles"), "solicitudes/solicitudes_ingles_constancias.php"],
        ["Solicitudes - cartas de presentacion", get_count($conexion, "solicitudes_cartas_presentacion"), "solicitudes/solicitudes_cartas_presentacion.php"],
        ["Solicitudes - cartas de aceptación", get_count($conexion, "solicitudes_cartas_aceptacion"), "solicitudes/solicitudes_cartas_aceptacion.php"],
        ["Solicitudes - cartas de terminación", get_count($conexion, "solicitudes_cartas_terminacion"), "solicitudes/solicitudes_cartas_terminacion.php"],
        ["Solicitudes - cartas de buena conducta", get_count($conexion, "solicitudes_cartas_buena_conducta"), "solicitudes/buena_conducta.php"], // <-- Nueva tarjeta agregada
        ["Solicitudes - justificantes", get_count($conexion, "justificantes"), "solicitudes/justificantes.php"]
    ]
];
?>

<link rel="stylesheet" href="css/inicio.css">

<div class="container-fluid py-4">

    <h4 class="section-title">✨ Gestión General de datos.</h4>
    <div class="row g-3">
        <?php foreach ($datos["Verano"] as $item): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="glass-card p-4 text-center border-info">
                <span class="label-card"><?= $item[0] ?></span>
                <h1 class="text-number"><?= $item[1] ?></h1>
                <a href="<?= $item[2] ?>" class="btn btn-sm btn-glass w-100">Administrar</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <h4 class="section-title">🚀 Gestión de avisos - docente y Solicitudes - alumno.</h4>
    <div class="row g-3">
        <?php foreach ($datos["Tramites"] as $item): ?>
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="glass-card p-4 text-center border-info">
                <span class="label-card"><?= $item[0] ?></span>
                <h1 class="text-number text-info"><?= $item[1] ?></h1>
                <a href="<?= $item[2] ?>" class="btn btn-sm btn-glass w-100">Gestionar</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-5 text-center">
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalLimpieza">
            <i class="fas fa-trash"></i> Limpieza masiva de registros
        </button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<div class="modal fade" id="modalLimpieza" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content bg-dark text-white" action="includes/limpiar_registros.php" method="POST">
            <div class="modal-header border-0">
                <h5 class="modal-title">Limpieza de Base de Datos</h5>
            </div>
            <div class="modal-body">
                <p class="text-danger small">⚠️ Esta acción es irreversible.</p>
                
                <div class="mb-3">
                    <label>Seleccionar servicio:</label>
                    <select name="tabla" class="form-select" required>
                        <option value="">-- Selecciona el servicio --</option>
                        <?php foreach ($servicios_map as $tabla => $nombre): ?>
                            <option value="<?= $tabla ?>"><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col">
                        <label>Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Fecha Fin:</label>
                        <input type="date" name="fecha_fin" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar Registros</button>
            </div>
        </form>
    </div>
</div>