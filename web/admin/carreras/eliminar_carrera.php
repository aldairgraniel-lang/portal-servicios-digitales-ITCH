<?php
// 1. Protección de seguridad: Impide el acceso a usuarios no autorizados
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php');

// 2. Conexión a la base de datos
include("../../conexion.php");
$id = intval($_GET['id'] ?? 0);
if($id>0){
$stmt = $conexion->prepare("DELETE FROM carreras WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();
}
header("Location: carreras.php");
exit;