<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre']);
$clave = trim($_POST['clave']);

if($id > 0){
    $stmt = $conexion->prepare("UPDATE cursos SET nombre=?, clave=? WHERE id=?");
    $stmt->bind_param("ssi", $nombre, $clave, $id);
}else{
    $stmt = $conexion->prepare("INSERT INTO cursos(nombre, clave) VALUES(?, ?)");
    $stmt->bind_param("ss", $nombre, $clave);
}
$stmt->execute();
$stmt->close();

header("Location: cursos.php");
exit;