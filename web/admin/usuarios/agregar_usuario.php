<?php 
// 1. Seguridad: Solo admins pueden entrar aquí
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php'); 
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

?>
<style>
    /* Estilo para los inputs y selects */
    .input-glass, .select-glass {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        padding: 10px 15px !important;
        transition: all 0.3s ease;
    }

    .input-glass:focus, .select-glass:focus {
        background: rgba(255, 255, 255, 0.1) !important;
        border-color: #4361ee !important; /* El color azul de tu login */
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15) !important;
        color: #ffffff !important;
    }

    /* Estilos del contenedor */
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        color: white;
    }
</style>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="m-0 fw-bold">Nuevo Usuario</h4>
                    <a href="usuarios.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <form action="procesar_usuario.php" method="POST" class="px-2">
                    <input type="hidden" name="accion" value="agregar">
                    
                    <div class="mb-3">
                        <label class="text-white-50 small ms-1">Usuario</label>
                        <input type="text" name="usuario" class="input-glass form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-white-50 small ms-1">Contraseña</label>
                        <input type="password" name="password" class="input-glass form-control" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-white-50 small ms-1">Rol</label>
                        <select name="rol" class="form-select select-glass">
                            <option value="docente">Docente</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>