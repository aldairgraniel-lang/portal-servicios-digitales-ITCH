<?php
include('../includes/header.php');
include('conexion.php');

// --- LÓGICA DE PAGINACIÓN ---
$por_pagina = 5;
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($pagina - 1) * $por_pagina;

// Contar total para calcular páginas
$total_res = $conexion->query("SELECT COUNT(*) as total FROM avisos WHERE activo = 1");
$total_avisos = $total_res->fetch_assoc()['total'] ?? 0;
$total_paginas = ceil($total_avisos / $por_pagina);

// Consulta con LIMIT y OFFSET
$sql = "SELECT a.*, u.usuario AS docente FROM avisos a 
        JOIN usuarios u ON u.id = a.id_docente 
        WHERE a.activo = 1 ORDER BY a.fecha_registro DESC LIMIT $por_pagina OFFSET $offset";
$result = $conexion->query($sql);

$modales_html = ""; 
?>

<style>
    .tarjeta-glass {
        background: rgba(0, 0, 0, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        padding: 30px;
    }

    .aviso-item {
        background: rgba(255, 255, 255, 0.07) !important;
        border: 1px solid rgba(255, 255, 255, 0.15);
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .aviso-item:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(255, 255, 255, 0.3);
    }

    .texto-legible {
        color: #ffffff !important;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
    }

    .subtexto-legible {
        color: rgba(255, 255, 255, 0.75) !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        background: #121212 !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }

    .aviso-contenido {
        color: #f0f0f0 !important;
        font-size: 1.1rem;
        line-height: 1.7;
    }
    
    .btn-paginacion {
        padding: 8px 16px;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
        color: white;
        text-decoration: none;
        transition: 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .btn-paginacion:hover {
        background: rgba(255,255,255,0.2);
        color: #00e5ff;
        border-color: #00e5ff;
    }

    .btn-paginacion.active {
        background: #00e5ff;
        color: black;
        font-weight: bold;
    }
</style>

<div class="contenedor-central">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="mb-3">
                    <a href="/index.php" class="btn btn-primary outlined-primary">
                        Volver al Panel
                    </a>
                </div>

                <div class="tarjeta-glass rounded-5 shadow-lg">
                    <h2 class="titulo mb-4 texto-legible text-center">📢 Avisos Recientes</h2>
                    
                    <?php if ($result && $result->num_rows > 0): ?>
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
                                            <div class="modal-body">
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
                                    <a href="?p=<?= $i ?>" class="btn-paginacion <?= ($pagina == $i) ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <h3 class="subtexto-legible">No hay avisos registrados</h3>
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