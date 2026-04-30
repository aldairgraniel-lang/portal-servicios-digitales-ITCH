<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre']);

if($id > 0){
$stmt = $conexion->prepare("UPDATE cursos SET nombre=? WHERE id=?");
$stmt->bind_param("si",$nombre,$id);
}else{
$stmt = $conexion->prepare("INSERT INTO cursos(nombre) VALUES(?)");
$stmt->bind_param("s",$nombre);
}
$stmt->execute();
header("Location: cursos.php");
exit;