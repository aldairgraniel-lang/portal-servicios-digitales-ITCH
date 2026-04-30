<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
// VALIDAR ID
if(!isset($_GET['id'])){
    header("Location: semestres.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// OBTENER SEMESTRE
$res = mysqli_query($conexion, "SELECT numero FROM semestres WHERE id = $id");
$data = mysqli_fetch_assoc($res);

// VALIDAR REGLA
if(!$data || $data['numero'] < 13){
    header("Location: semestres.php?error=1");
    exit;
}

// ELIMINAR
mysqli_query($conexion, "DELETE FROM semestres WHERE id = $id");

// REDIRIGIR
header("Location: semestre.php?ok=1");
exit;