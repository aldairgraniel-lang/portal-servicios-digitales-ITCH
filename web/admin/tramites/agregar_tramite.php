<?php 
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php'); 
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
        <div class="glass-card p-4 shadow-lg" style="background: rgba(255,255,255,0.02); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
        

    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h3 class="fw-bold m-0"></i>Nuevo Trámite</h3>
                    <a href="tramites.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
    </div>                
                <form action="procesar_tramite.php" method="POST">
                    <input type="hidden" name="accion" value="agregar">
                    <div class="mb-4">
                        <label class="text-white-50 small">Nombre del Trámite</label>
                        <input type="text" name="nombre_tramite" class="input-glass" placeholder="Ej: Constancia de Estudios" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>