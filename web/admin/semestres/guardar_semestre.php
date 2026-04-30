<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
$id = intval($_POST['id'] ?? 0);
$numero = intval($_POST['numero']);

if($id > 0){
$stmt = $conexion->prepare("UPDATE semestres SET numero=? WHERE id=?");
$stmt->bind_param("ii",$numero,$id);
}else{
$stmt = $conexion->prepare("INSERT INTO semestres(numero) VALUES(?)");
$stmt->bind_param("i",$numero);
}
$stmt->execute();
header("Location: semestre.php");
exit;