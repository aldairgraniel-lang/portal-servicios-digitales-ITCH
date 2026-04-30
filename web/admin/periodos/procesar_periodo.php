<?php
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// 1. PROCESS REQUESTS (POST or Delete via GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Add or Edit
    $accion = $_POST['accion'] ?? '';
    $nombre = $_POST['nombre'] ?? '';

    if ($accion === 'agregar') {
        $stmt = $conexion->prepare("INSERT INTO periodos (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
    } elseif ($accion === 'editar') {
        $id = $_POST['id'] ?? 0;
        $stmt = $conexion->prepare("UPDATE periodos SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nombre, $id);
        $stmt->execute();
    }
    header("Location: periodos.php");
    exit;
}

// Handle Delete (via GET)
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM periodos WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    header("Location: periodos.php");
    exit;
}

// 2. RENDER THE FORM (Edit View)
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

// Safely get the ID for the Edit form
$id = $_GET['id'] ?? null;
$per = ['id' => '', 'nombre' => '']; // Default empty values

if ($id) {
    $query = $conexion->prepare("SELECT * FROM periodos WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $per = $result->fetch_assoc();
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="m-0 fw-bold"><?= $id ? 'Editar' : 'Nuevo' ?> Periodo</h4>
                    <a href="periodos.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
                
                <form action="procesar_periodo.php" method="POST">
                    <input type="hidden" name="accion" value="<?= $id ? 'editar' : 'agregar' ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($per['id']) ?>">
                    
                    <div class="mb-4">
                        <label class="text-white-50 small ms-1">Nombre del Periodo</label>
                        <input type="text" name="nombre" class="input-glass form-control" 
                               value="<?= htmlspecialchars($per['nombre']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        <?= $id ? 'Actualizar' : 'Guardar' ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>