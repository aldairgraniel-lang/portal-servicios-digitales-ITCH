<?php
// 1. Protección de seguridad: Valida que el usuario sea administrador
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión y Header
include('../../conexion.php');
include("../includes/header.php");
$conexion->set_charset("utf8mb4");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// LÓGICA DE ACTUALIZACIÓN
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt = $conexion->prepare("
        UPDATE VERANO SET nombre=?, apellidos=?, numero_celular=?, numero_control=?, 
        carrera=?, semestre=?, curso_interes=?, representante_1=?, representante_2=? 
        WHERE id=?
    ");
    $stmt->bind_param("sssssssssi", $_POST['nombre'], $_POST['apellidos'], $_POST['numero_celular'], 
                      $_POST['numero_control'], $_POST['carrera'], $_POST['semestre'], 
                      $_POST['curso_interes'], $_POST['representante_1'], $_POST['representante_2'], $id);
    $stmt->execute();
    header("Location: preregistro.php");
    exit;
}

// OBTENER DATOS
$stmt = $conexion->prepare("SELECT * FROM VERANO WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
?>
   <link rel="stylesheet" href="../css/tablasS.css">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-4 shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <h4 class="m-0 fw-bold">Editar Alumno</h4>
                    <a href="preregistro.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <form method="POST" class="row g-3 px-2">
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Nombre</label>
                        <input name="nombre" value="<?= htmlspecialchars($alumno['nombre']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Apellidos</label>
                        <input name="apellidos" value="<?= htmlspecialchars($alumno['apellidos']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">No. Control</label>
                        <input name="numero_control" value="<?= htmlspecialchars($alumno['numero_control']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Celular</label>
                        <input name="numero_celular" value="<?= htmlspecialchars($alumno['numero_celular']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Carrera</label>
                        <input name="carrera" value="<?= htmlspecialchars($alumno['carrera']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Semestre</label>
                        <input name="semestre" value="<?= htmlspecialchars($alumno['semestre']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="text-white-50 small ms-1">Curso de interés</label>
                        <input name="curso_interes" value="<?= htmlspecialchars($alumno['curso_interes']) ?>" class="input-glass form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Representante 1</label>
                        <input name="representante_1" value="<?= htmlspecialchars($alumno['representante_1']) ?>" class="input-glass form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="text-white-50 small ms-1">Representante 2</label>
                        <input name="representante_2" value="<?= htmlspecialchars($alumno['representante_2']) ?>" class="input-glass form-control">
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Actualizar Información</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>