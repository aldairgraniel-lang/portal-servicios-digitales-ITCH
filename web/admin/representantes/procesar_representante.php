<?php
session_start();
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
// 1. ELIMINAR (Vía GET)
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $stmt = $conexion->prepare("DELETE FROM representantes WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    header("Location: representantes.php?msg=eliminado");
    exit();
}

// 2. CREAR (Vía POST)
if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $stmt = $conexion->prepare("INSERT INTO representantes (numero_control, nombre, fecha_registro) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $_POST['numero_control'], $_POST['nombre']);
    $stmt->execute();
    header("Location: representantes.php?msg=creado");
    exit();
}

// 3. EDITAR (Formulario de edición - Cuando llega el ID sin acción)
if (isset($_GET['id']) && !isset($_GET['accion'])) {
    $query = $conexion->prepare("SELECT * FROM representantes WHERE id = ?");
    $query->bind_param("i", $_GET['id']);
    $query->execute();
    $representante = $query->get_result()->fetch_assoc();

    // Aquí deberías incluir tu header y pintar un formulario similar al de agregar
    // precargando los datos: value="<?= $representante['nombre'] "
    include("../includes/header.php");
    ?>
       <link rel="stylesheet" href="../css/tablasG.css">

    <div class="glass-card p-4 shadow-lg" style="max-width: 600px; margin: auto;">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="m-0 fw-bold">Editar Representante</h4>
                    <a href="representantes.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
        <form action="procesar_representante.php" method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id" value="<?= $representante['id'] ?>">
            
            <div class="mb-3">
                <label>Número Control</label>
                <input type="text" name="numero_control" class="input-glass form-control" value="<?= htmlspecialchars($representante['numero_control']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="input-glass form-control" value="<?= htmlspecialchars($representante['nombre']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </form>
    </div>
    <?php
    exit();
}

// 4. ACTUALIZAR (Ejecución del update en DB)
if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $stmt = $conexion->prepare("UPDATE representantes SET numero_control = ?, nombre = ? WHERE id = ?");
    $stmt->bind_param("ssi", $_POST['numero_control'], $_POST['nombre'], $_POST['id']);
    $stmt->execute();
    header("Location: representantes.php?msg=actualizado");
    exit();
}
?>
   <link rel="stylesheet" href="../css/tablasG.css">
