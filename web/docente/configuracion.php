<?php
include('../conexion.php');
include('includes/auth_docente.php'); // <--- ¡AÑADE ESTO AL PRINCIPIO!
// 🔁 FUNCIÓN PARA CAMBIAR ESTADO (CORREGIDA)
function toggleRegistro($conexion, $clave) {
    $stmt = $conexion->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if (!$resultado) {
        // 🔥 si no existe, lo crea como abierto
        $stmt = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, '1')");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        return;
    }

    $actual = $resultado['valor'];
    $nuevo  = ($actual === '1') ? '0' : '1';

    $stmt = $conexion->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
    $stmt->bind_param("ss", $nuevo, $clave);
    $stmt->execute();
}

// 🔘 ACCIÓN DEL BOTÓN
if (isset($_POST['toggle']) && isset($_POST['tipo'])) {
    toggleRegistro($conexion, $_POST['tipo']);
    header("Location: configuracion.php");
    exit;
}

// 🔍 CONSULTAR ESTADOS (MEJORADO)
function obtenerEstado($conexion, $clave) {
    $stmt = $conexion->prepare("SELECT valor FROM configuracion WHERE clave = ?");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if (!$resultado) {
        // 🔥 si no existe, lo crea en 0
        $stmt = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, '0')");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        return '0';
    }

    return $resultado['valor'];
}

// 🔍 ESTADOS
$verano = obtenerEstado($conexion, 'registro_abierto');
$ingles = obtenerEstado($conexion, 'registro_ingles_abierto');
$presentacion = obtenerEstado($conexion, 'registro_presentacion_abierto');
?>

<div class="container mt-4">
  <h2>Panel de Control de Registros</h2>

  <!-- 🔵 VERANO -->
  <div class="card mt-3 p-4 text-center">
    <h4>Registro Verano</h4>

    <?php if ($verano === '1'): ?>
      <span class="badge bg-success fs-5 mb-3">Abierto ✅</span>
    <?php else: ?>
      <span class="badge bg-danger fs-5 mb-3">Cerrado ❌</span>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="tipo" value="registro_abierto">
      <button name="toggle" class="btn <?= $verano === '1' ? 'btn-danger' : 'btn-success' ?> btn-lg">
        <?= $verano === '1' ? 'Cerrar' : 'Abrir' ?>
      </button>
    </form>
  </div>

  <!-- 🟣 INGLÉS -->
  <div class="card mt-3 p-4 text-center">
    <h4>Registro Inglés</h4>

    <?php if ($ingles === '1'): ?>
      <span class="badge bg-success fs-5 mb-3">Abierto ✅</span>
    <?php else: ?>
      <span class="badge bg-danger fs-5 mb-3">Cerrado ❌</span>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="tipo" value="registro_ingles_abierto">
      <button name="toggle" class="btn <?= $ingles === '1' ? 'btn-danger' : 'btn-success' ?> btn-lg">
        <?= $ingles === '1' ? 'Cerrar' : 'Abrir' ?>
      </button>
    </form>
  </div>

  <!-- 🟢 CARTAS -->
  <div class="card mt-3 p-4 text-center">
    <h4>Cartas de Presentación</h4>

    <?php if ($presentacion === '1'): ?>
      <span class="badge bg-success fs-5 mb-3">Abierto ✅</span>
    <?php else: ?>
      <span class="badge bg-danger fs-5 mb-3">Cerrado ❌</span>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="tipo" value="registro_presentacion_abierto">
      <button name="toggle" class="btn <?= $presentacion === '1' ? 'btn-danger' : 'btn-success' ?> btn-lg">
        <?= $presentacion === '1' ? 'Cerrar' : 'Abrir' ?>
      </button>
    </form>
  </div>

</div>