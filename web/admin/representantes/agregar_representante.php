<?php 
// 1. Iniciar sesión y validar rutas
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php"); 
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="glass-card p-4 shadow-lg" style="background: rgba(255,255,255,0.02); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h3 class="fw-bold m-0"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Representante</h3>
                    <a href="representantes.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <form action="procesar_representante.php" method="POST">
                    <input type="hidden" name="accion" value="agregar">

                    <div class="mb-3">
                        <label class="form-label text-white-50 small">Número de Control</label>
                        <input type="text" name="numero_control" class="input-glass" placeholder="Ej: 19390015" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-white-50 small">Nombre Completo</label>
                        <input type="text" name="nombre" class="input-glass" placeholder="Ej: Graham Aldair Graniel Cruz" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">
                            <i class="bi bi-save me-2"></i> Guardar Representante
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>