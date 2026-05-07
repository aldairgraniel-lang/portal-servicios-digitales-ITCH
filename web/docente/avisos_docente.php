<?php
// Esto DEBE ser lo primero de todo el archivo
if (session_status() === PHP_SESSION_NONE) session_start();

include(__DIR__ . "/../conexion.php");
include('includes/auth_docente.php'); // <--- Esto ya hace la magia de bloquear accesos

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['mensaje'])) $_SESSION['mensaje'] = '';

if (!isset($_SESSION['usuario_id'])) {
    die("Sesión no iniciada");
}

$id_docente = $_SESSION['usuario_id'];

// ───────── FUNCION SUBIR ARCHIVO ─────────
function subirArchivo($file) {
    if (!isset($file) || $file['error'] !== 0) return null;
    
    $permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $permitidos)) return null;

    $ruta = __DIR__ . '/../uploads/avisos/';
    if (!is_dir($ruta)) mkdir($ruta, 0777, true);
    
    $nombre = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $destino = $ruta . $nombre;
    
    return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : null;
}

// ───────── PROCESAR ACCIONES (POST) ─────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $pagina_actual = isset($_POST['pagina_actual']) ? (int)$_POST['pagina_actual'] : 1;
    $tipo_filtro_post = $_POST['tipo_filtro'] ?? '';
    $archivo = (isset($_FILES['archivo']) && $_FILES['archivo']['name'] !== '') ? subirArchivo($_FILES['archivo']) : null;

    if (isset($_FILES['archivo']) && $_FILES['archivo']['name'] !== '' && $archivo === null) {
        $_SESSION['mensaje'] = "Archivo no permitido ⚠️";
        header("Location: avisos_docente.php?vista=lista&pagina=" . $pagina_actual . "&tipo=" . urlencode($tipo_filtro_post));
        exit();
    }

    if ($accion === 'crear') {
        $titulo = $_POST['titulo'];
        $contenido = $_POST['contenido'];
        $tipo = $_POST['tipo'];
        $stmt = $conexion->prepare("INSERT INTO avisos (titulo, contenido, tipo, activo, id_docente, archivo) VALUES (?, ?, ?, 1, ?, ?)");
        $stmt->bind_param("sssis", $titulo, $contenido, $tipo, $id_docente, $archivo);
        $stmt->execute();
        $_SESSION['mensaje'] = "Aviso creado correctamente ✅";
        header("Location: avisos_docente.php");
        exit();
    }

    if ($accion === 'editar') {
        $id = (int)$_POST['id'];
        $titulo = $_POST['titulo'];
        $contenido = $_POST['contenido'];
        $tipo = $_POST['tipo'];
        if ($archivo) {
            $stmt = $conexion->prepare("UPDATE avisos SET titulo=?, contenido=?, tipo=?, archivo=? WHERE id=? AND id_docente=?");
            $stmt->bind_param("ssssii", $titulo, $contenido, $tipo, $archivo, $id, $id_docente);
        } else {
            $stmt = $conexion->prepare("UPDATE avisos SET titulo=?, contenido=?, tipo=? WHERE id=? AND id_docente=?");
            $stmt->bind_param("sssii", $titulo, $contenido, $tipo, $id, $id_docente);
        }
        $stmt->execute();
        $_SESSION['mensaje'] = "Aviso actualizado ✅";
    }

    if ($accion === 'eliminar') {
        $id = (int)$_POST['id'];
        
        $stmt_check = $conexion->prepare("SELECT archivo FROM avisos WHERE id=? AND id_docente=?");
        $stmt_check->bind_param("ii", $id, $id_docente);
        $stmt_check->execute();
        $res_archivo = $stmt_check->get_result()->fetch_assoc();
        
        if ($res_archivo && !empty($res_archivo['archivo'])) {
            $ruta_archivo = __DIR__ . '/../uploads/avisos/' . $res_archivo['archivo'];
            if (file_exists($ruta_archivo)) {
                unlink($ruta_archivo);
            }
        }

        $stmt = $conexion->prepare("DELETE FROM avisos WHERE id=? AND id_docente=?");
        $stmt->bind_param("ii", $id, $id_docente);
        $stmt->execute();
        $_SESSION['mensaje'] = "Aviso y archivo eliminados 🗑️";

        // Lógica de retorno automático si es el último
        $stmt_count = $conexion->prepare("SELECT COUNT(*) AS total FROM avisos WHERE id_docente = ?");
        $stmt_count->bind_param("i", $id_docente);
        $stmt_count->execute();
        $quedan = $stmt_count->get_result()->fetch_assoc()['total'];

        if ($quedan == 0) {
            header("Location: avisos_docente.php");
        } else {
            header("Location: avisos_docente.php?vista=lista&pagina=" . $pagina_actual . "&tipo=" . urlencode($tipo_filtro_post));
        }
        exit();
    }

    if ($accion === 'toggle') {
        $id = (int)$_POST['id'];
        $query = $conexion->prepare("SELECT activo FROM avisos WHERE id=? AND id_docente=?");
        $query->bind_param("ii", $id, $id_docente);
        $query->execute();
        $res = $query->get_result()->fetch_assoc();
        
        if ($res) {
            $nuevo_estado = $res['activo'] ? 0 : 1;
            $stmt = $conexion->prepare("UPDATE avisos SET activo=? WHERE id=? AND id_docente=?");
            $stmt->bind_param("iii", $nuevo_estado, $id, $id_docente);
            $stmt->execute();
            $_SESSION['mensaje'] = $nuevo_estado ? "Aviso activado ✅" : "Aviso desactivado ⚠️";
        }
    }

    header("Location: avisos_docente.php?vista=lista&pagina=" . $pagina_actual . "&tipo=" . urlencode($tipo_filtro_post));
    exit();
}

// ───────── LÓGICA DE VISTA Y PAGINACIÓN ─────────
$vista = $_GET['vista'] ?? 'inicio';
$tipo_filtro = $_GET['tipo'] ?? ''; // <--- Captura de filtro por categoría

if ($tipo_filtro !== '') {
    $stmt_total = $conexion->prepare("SELECT COUNT(*) AS total FROM avisos WHERE id_docente = ? AND tipo = ?");
    $stmt_total->bind_param("is", $id_docente, $tipo_filtro);
} else {
    $stmt_total = $conexion->prepare("SELECT COUNT(*) AS total FROM avisos WHERE id_docente = ?");
    $stmt_total->bind_param("i", $id_docente);
}
$stmt_total->execute();
$total_avisos = $stmt_total->get_result()->fetch_assoc()['total'];

$por_pagina = 5;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$total_paginas = max(1, (int)ceil($total_avisos / $por_pagina));
if ($pagina > $total_paginas) $pagina = $total_paginas;
$offset = ($pagina - 1) * $por_pagina;

$avisos = null;
if ($vista === 'lista') {
    if ($tipo_filtro !== '') {
        $stmt = $conexion->prepare("SELECT * FROM avisos WHERE id_docente = ? AND tipo = ? ORDER BY fecha_registro DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("isii", $id_docente, $tipo_filtro, $por_pagina, $offset);
    } else {
        $stmt = $conexion->prepare("SELECT * FROM avisos WHERE id_docente = ? ORDER BY fecha_registro DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $id_docente, $por_pagina, $offset);
    }
    $stmt->execute();
    $avisos = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <title>ITCH - Gestión de Avisos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/avisos.css">
    <link rel="stylesheet" href="css/avisosV2.css">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="container py-4">

    <?php if ($vista === 'inicio'): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card custom-card shadow-lg mb-5">
                    <div class="card-body p-4 p-md-5">
                        <header class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
                            <div>
                                <h4 class="fw-bold text-light mb-0">📢 COMUNICAR AVISOS</h4>
                                <p class="text-secondary small mt-1 mb-0" style="letter-spacing: 0.3px;">
                                    Administra los avisos para la comunidad educativa. Crea mensajes importantes y mantén a todos informados.
                                </p>
                            </div>
                            <a href="panel_docente.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Regresar
                            </a>
                        </header>

                        <hr class="border-secondary mb-4">

                        <h5 class="text-light fw-bold mb-3">✍️ Crear Nuevo Aviso</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="crear">
                            
                            <div class="mb-3">
                                <label class="text-secondary small fw-bold mb-1">Título del aviso</label>
                                <input name="titulo" class="form-control" placeholder="Escribe el título aquí..." required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-secondary small fw-bold mb-1">Contenido</label>
                                <textarea name="contenido" class="form-control" rows="3" placeholder="Escribe el mensaje detallado aquí..." required></textarea>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-bold mb-1">Categoría</label>
                                    <select name="tipo" class="form-select">
                                        <option value="info">🔵 Informativo</option>
                                        <option value="advertencia">🟡 Advertencia</option>
                                        <option value="urgente">🔴 Urgente</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-secondary small fw-bold mb-1">Adjuntar archivo (Opcional)</label>
                                    <input type="file" name="archivo" class="form-control">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                <i class="bi bi-send-fill"></i> Publicar Aviso Ahora
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center py-4">
                    <?php if ($total_avisos > 0): ?>
                        <a href="avisos_docente.php?vista=lista&pagina=1" class="btn btn-primary px-5 py-3 shadow">
                            📋 Ver Historial de Avisos (<?= $total_avisos ?>)
                        </a>
                    <?php else: ?>
                        <p class="text-light fw-bold fs-5" style="text-shadow: 0 0 10px rgba(255,255,255,0.1);">
                            Aún no has publicado avisos. 🚀
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2 flex-wrap gap-3">
            <div>
                <h4 class="text-light fw-bold m-0 d-flex align-items-center gap-2">
                    📋 Historial de Avisos
                    <span class="badge bg-primary text-white fs-6 align-middle"><?= $total_avisos ?></span>
                </h4>
                <br>
                <a href="avisos_docente.php" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-arrow-left-circle"></i> Volver al Panel
                </a>
            </div>
            
            <div class="d-flex align-items-center gap-3 flex-wrap">


                <form method="GET" class="d-flex gap-2 align-items-center mb-0">
                    <input type="hidden" name="vista" value="lista">
                    <label class="text-secondary small text-nowrap d-none d-sm-block">Filtrar por categoría:</label>
                    <select name="tipo" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="info" <?= $tipo_filtro === 'info' ? 'selected' : '' ?>>🔵 Informativo</option>
                        <option value="advertencia" <?= $tipo_filtro === 'advertencia' ? 'selected' : '' ?>>🟡 Advertencia</option>
                        <option value="urgente" <?= $tipo_filtro === 'urgente' ? 'selected' : '' ?>>🔴 Urgente</option>
                    </select>
                </form>
            </div>
        </div>

        <?php if ($avisos && $avisos->num_rows > 0): ?>
            <?php while ($row = $avisos->fetch_assoc()): ?>
                <div class="aviso-item-card shadow-sm">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="pagina_actual" value="<?= $pagina ?>">
                        <input type="hidden" name="tipo_filtro" value="<?= $tipo_filtro ?>">
                        
                        <div class="row g-4">
                            <div class="col-md-8">
                                <label class="text-secondary small fw-bold mb-1">Título del aviso</label>
                                <input name="titulo" class="form-control fw-bold mb-3" value="<?= htmlspecialchars($row['titulo']) ?>" required>
                                
                                <label class="text-secondary small fw-bold mb-1">Contenido</label>
                                <textarea name="contenido" class="form-control mb-2" rows="3" required><?= htmlspecialchars($row['contenido']) ?></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="text-secondary small fw-bold mb-1">Categoría</label>
                                <select name="tipo" class="form-select mb-3">
                                    <option value="info" <?= $row['tipo']=='info' ? 'selected' : '' ?>>🔵 Informativo</option>
                                    <option value="advertencia" <?= $row['tipo']=='advertencia' ? 'selected' : '' ?>>🟡 Advertencia</option>
                                    <option value="urgente" <?= $row['tipo']=='urgente' ? 'selected' : '' ?>>🔴 Urgente</option>
                                </select>

                                <label class="text-secondary small fw-bold mb-1">Actualizar archivo (opcional)</label>
                                <input type="file" name="archivo" class="form-control mb-2">
                                
                                <?php if ($row['archivo']): ?>
                                    <div class="mt-1">
                                        <a href="../uploads/avisos/<?= $row['archivo'] ?>" target="_blank" class="text-info text-decoration-none small">
                                            <i class="bi bi-paperclip"></i> 📎 Ver archivo actual
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="border-secondary my-3">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex gap-3 align-items-center">
                                <button type="submit" name="accion" value="editar" class="btn btn-success btn-sm px-3 py-2 fw-bold">
                                    <i class="bi bi-floppy"></i> Guardar
                                </button>
                                <span class="badge <?= $row['activo'] ? 'bg-success text-white' : 'bg-danger text-white' ?> px-3 py-2">
                                    <?= $row['activo'] ? '🟢 Visible' : '🔴 Oculto' ?>
                                </span>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-warning btn-sm px-3 py-2 fw-bold" onclick="confirmar(this.form, 'toggle')">
                                    <?= $row['activo'] ? 'Ocultar' : 'Mostrar' ?>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm px-3 py-2 fw-bold" onclick="confirmar(this.form, 'eliminar')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5" style="border: 1px dashed #4a5568; border-radius: 20px; background: rgba(255,255,255,0.01);">
                <i class="bi bi-card-checklist text-secondary" style="font-size: 3rem;"></i>
                <h4 class="text-light fw-bold mt-3">No hay avisos registrados.</h4>
                <p class="text-secondary">Crea tu primer aviso arriba o cambia el filtro de categoría.</p>
            </div>
        <?php endif; ?>

        <?php if ($total_paginas > 1): ?>
            <div class="paginacion-wrapper d-flex justify-content-center gap-2 mt-4">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <a href="?vista=lista&pagina=<?= $i ?><?= !empty($tipo_filtro) ? '&tipo=' . urlencode($tipo_filtro) : '' ?>" class="btn <?= ($i == $pagina) ? 'btn-primary' : 'btn-outline-secondary' ?> px-3 py-2">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<script>
    function confirmar(form, accion) {
        const config = {
            toggle: { t: '¿Cambiar visibilidad?', txt: 'El aviso cambiará su estado actual.', c: '#3b82f6' },
            eliminar: { t: '¿Eliminar aviso?', txt: 'Esta acción borrará el registro y el archivo permanentemente.', c: '#ef4444' }
        };
        Swal.fire({
            title: config[accion].t, 
            text: config[accion].txt, 
            icon: accion === 'eliminar' ? 'warning' : 'info',
            showCancelButton: true, 
            confirmButtonColor: config[accion].c, 
            background: '#0f172a', 
            color: '#fff', 
            confirmButtonText: 'Confirmar'
        }).then((result) => {
            if (result.isConfirmed) {
                const input = document.createElement('input'); 
                input.type = 'hidden'; 
                input.name = 'accion'; 
                input.value = accion;
                form.appendChild(input); 
                form.submit();
            }
        });
    }
    
    document.addEventListener("DOMContentLoaded", () => {
        let msg = "<?= $_SESSION['mensaje'] ?? '' ?>";
        if (msg !== "") {
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, background: '#1e293b', color: '#fff' })
            .fire({ icon: msg.includes('✅') ? 'success' : (msg.includes('⚠️') ? 'warning' : 'info'), title: msg });
        }
    });
</script>
<?php unset($_SESSION['mensaje']); ?>
</body>
</html>