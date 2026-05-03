<?php
// 1. Seguridad y Conexión
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

// 2. Lógica de Procesamiento
$tipo_tramite = ['id' => '', 'nombre_tramite' => '']; // Valores por defecto

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $nombre = $_POST['nombre_tramite'] ?? '';

    if ($accion === 'agregar') {
        $stmt = $conexion->prepare("INSERT INTO tipos_tramite (nombre_tramite) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        header("Location: tramites.php?status=success");
        exit();
    }

    if ($accion === 'editar') {
        $id = $_POST['id'] ?? 0;
        $stmt = $conexion->prepare("UPDATE tipos_tramite SET nombre_tramite = ? WHERE id = ?");
        $stmt->bind_param("si", $nombre, $id);
        $stmt->execute();
        header("Location: tramites.php?status=updated");
        exit();
    }
}

// Acción por GET (Eliminar o Cargar para Editar)
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM tipos_tramite WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    header("Location: tramites.php?status=deleted");
    exit();
}

// Si viene un ID por GET, cargamos los datos para editar
if (isset($_GET['id'])) {
    $res = mysqli_query($conexion, "SELECT * FROM tipos_tramite WHERE id = " . intval($_GET['id']));
    $tipo_tramite = mysqli_fetch_assoc($res) ?? ['id' => '', 'nombre_tramite' => ''];
}

// 3. Diseño (HTML)
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');
?>
   <link rel="stylesheet" href="../css/tablasG.css">


<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="glass-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="m-0 fw-bold text-white"><?= isset($_GET['id']) ? 'Editar Trámite' : 'Nuevo Trámite' ?></h4>
                    <a href="tramites.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Volver</a>
                </div>

                <form method="POST">
                    <input type="hidden" name="accion" value="<?= isset($_GET['id']) ? 'editar' : 'agregar' ?>">
                    <input type="hidden" name="id" value="<?= $tipo_tramite['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="text-white-50 small ms-1">Nombre del Trámite</label>
                        <input type="text" name="nombre_tramite" class="form-control input-glass" 
                               value="<?= htmlspecialchars($tipo_tramite['nombre_tramite']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                        Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php ?>
<link rel="stylesheet" href="/admin/adminCSS.css?v=<?php echo time(); ?>"><div class="container py-5">
