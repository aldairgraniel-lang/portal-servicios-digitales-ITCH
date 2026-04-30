<?php
// 1. Protección de seguridad: Valida sesión y rol de administrador
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión y Header
include("../../conexion.php");
include("../includes/header.php");
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="m-0 fw-bold">Nuevo Semestre</h4>
                    <a href="semestre.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <form action="guardar_semestre.php" method="POST" class="px-2">
                    <div class="mb-4">
                        <label class="text-white-50 small ms-1">Número del Semestre</label>
                        <input type="number" name="numero" class="input-glass form-control" min="1" max="20" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>