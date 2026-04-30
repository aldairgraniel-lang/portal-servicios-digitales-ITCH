<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
include("../includes/header.php");
$id = intval($_GET['id'] ?? 0);

$stmt = $conexion->prepare("SELECT * FROM carreras WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if(!$row){
    echo "<div class='container py-5 text-white'>Carrera no encontrada</div>";
    exit;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="m-0 fw-bold">Editar Carrera</h4>
                    <a href="carreras.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <form action="guardar_carrera.php" method="POST" class="px-2">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    
                    <div class="mb-4">
                        <label class="text-white-50 small ms-1">Nombre de la Carrera</label>
                        <input type="text" name="nombre" class="input-glass form-control" 
                               value="<?= htmlspecialchars($row['nombre']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Actualizar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>