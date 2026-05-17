<?php
include('../includes/header.php'); 
include('conexion.php');

// --- LÓGICA DE FILTRADO ---
// Detectamos si el usuario envió activamente el formulario de filtrado
$tipo_filtro = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$sql_filtro = "";
$filtrado_activo = isset($_GET['tipo']); // Evalúa si la variable existe en la URL

// Valores permitidos para validar que no manden filtros alterados por URL
$filtros_validos = ['urgente', 'advertencia', 'info'];

if ($filtrado_activo && in_array($tipo_filtro, $filtros_validos)) {
    // Escapamos el tipo para evitar inyecciones SQL
    $tipo_seguro = $conexion->real_escape_string($tipo_filtro);
    $sql_filtro = " AND a.tipo = '$tipo_seguro'";
}

// --- LÓGICA DE PAGINACIÓN ---
$por_pagina = 5;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($pagina - 1) * $por_pagina;

$total_avisos = 0;
$total_paginas = 0;
$result = false;

// CRÍTICO: Solo interactuar con la base de datos si el usuario aplicó un filtro
if ($filtrado_activo) {
    // Contar total para calcular páginas
    $sql_count = "SELECT COUNT(*) as total FROM avisos a WHERE a.activo = 1" . $sql_filtro;
    $total_res = $conexion->query($sql_count);
    $total_avisos = $total_res->fetch_assoc()['total'] ?? 0;
    $total_paginas = ceil($total_avisos / $por_pagina);

    // Consulta con LIMIT, OFFSET y filtro aplicado
    $sql = "SELECT a.*, u.usuario AS docente FROM avisos a 
            JOIN usuarios u ON u.id = a.id_docente 
            WHERE a.activo = 1 $sql_filtro ORDER BY a.fecha_registro DESC LIMIT $por_pagina OFFSET $offset";
    $result = $conexion->query($sql);
}

$modales_html = ""; 
?>

<link rel="stylesheet" href="../css/aviso.css">
<div class="contenedor-central">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <a href="/index.php" class="btn btn-primary outlined-primary">
                            Volver al Panel
                        </a>
                    </div>
                    
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <select name="tipo" class="form-select bg-dark text-white border-secondary" style="width: auto;" required>
                            <option value="" disabled <?= !$filtrado_activo ? 'selected' : '' ?>>-- Selecciona una categoría --</option>
                            <option value="urgente" <?= ($tipo_filtro === 'urgente') ? 'selected' : '' ?>>🚨 Urgente</option>
                            <option value="advertencia" <?= ($tipo_filtro === 'advertencia') ? 'selected' : '' ?>>⚠️ Advertencia</option>
                            <option value="info" <?= ($tipo_filtro === 'info') ? 'selected' : '' ?>>ℹ️ Informativo</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                </div>

                <div class="tarjeta-glass rounded-5 shadow-lg">
                    <h2 class="titulo mb-4 texto-legible text-center">🔊 Avisos</h2>
                    
                    <?php if (!$filtrado_activo): ?>
                        <div class="text-center py-5">
                            <div class="mb-3" style="font-size: 3rem;">🔍</div>
                            <h4 class="subtexto-legible">Por favor, selecciona una categoría de avisos para comenzar la búsqueda.</h4>
                        </div>

                    <?php elseif ($result && $result->num_rows > 0): ?>
                        <div class="row g-3">
                            <?php while ($aviso = $result->fetch_assoc()): 
                                $color = match($aviso['tipo']) {
                                    'urgente' => '#ff1744',
                                    'advertencia' => '#ff9100',
                                    default => '#00e5ff',
                                };
                                $modal_id = "aviso_" . $aviso['id'];
                            ?>
                                <div class="col-12">
                                    <div class="aviso-item p-4 rounded-4" 
                                         style="border-left: 6px solid <?= $color ?>; cursor: pointer;" 
                                         data-bs-toggle="modal" 
                                         data-bs-target="#<?= $modal_id ?>">
                                        
                                        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-start align-items-sm-center mb-2 gap-2">
                                            <h5 class="fw-bold m-0 texto-legible" style="color: <?= $color ?> !important;">
                                                <?= htmlspecialchars($aviso['titulo']) ?>
                                            </h5>
                                            <span class="badge flex-shrink-0" style="background: <?= $color ?>; color: #000; font-weight: bold;">
                                                <?= strtoupper($aviso['tipo']) ?>
                                            </span>
                                        </div>

                                        <p class="mb-0 subtexto-legible">
                                            📅 <?= date("d/m/Y", strtotime($aviso['fecha_registro'])) ?> • Click para leer
                                        </p>
                                    </div>
                                </div>

                                <?php ob_start(); ?>
                                <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title fw-bold" style="color: <?= $color ?>;">
                                                    <?= htmlspecialchars($aviso['titulo']) ?>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-content-body p-3">
                                                <div class="mb-4 p-2 rounded bg-dark">
                                                    <span class="text-info small">Publicado por: <b><?= htmlspecialchars($aviso['docente']) ?></b></span><br>
                                                    <span class="text-secondary small">Fecha: <?= date("d/m/Y", strtotime($aviso['fecha_registro'])) ?></span>
                                                </div>
                                                
                                                <div class="aviso-contenido">
                                                    <?= nl2br(htmlspecialchars($aviso['contenido'])) ?>
                                                </div>

                                                <?php if (!empty($aviso['archivo'])): ?>
                                                    <div class="mt-4">
                                                        <a href="../uploads/avisos/<?= $aviso['archivo'] ?>" target="_blank" class="btn btn-primary w-100 py-2">
                                                            📥 Descargar Archivo Adjunto
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $modales_html .= ob_get_clean(); ?>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($total_paginas > 1): ?>
                            <div class="d-flex justify-content-center mt-5 gap-2">
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <a href="?p=<?= $i ?>&tipo=<?= urlencode($tipo_filtro) ?>" class="btn-paginacion <?= ($pagina == $i) ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <h3 class="subtexto-legible">No se encontraron avisos en la categoría seleccionada.</h3>
                        </div>
                    <?php endif; ?>
                </div> 
            </div>
        </div>
    </div>
</div>

<?= $modales_html ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>  