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
    $archivo = (isset($_FILES['archivo']) && $_FILES['archivo']['name'] !== '') ? subirArchivo($_FILES['archivo']) : null;

    if (isset($_FILES['archivo']) && $_FILES['archivo']['name'] !== '' && $archivo === null) {
        $_SESSION['mensaje'] = "Archivo no permitido ⚠️";
        header("Location: avisos_docente.php?vista=lista&pagina=" . $pagina_actual);
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
            header("Location: avisos_docente.php?vista=lista&pagina=" . $pagina_actual);
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

    header("Location: avisos_docente.php?vista=lista&pagina=" . $pagina_actual);
    exit();
}

// ───────── LOGICA DE VISTA Y PAGINACIÓN ─────────
$vista = $_GET['vista'] ?? 'inicio';
$stmt_total = $conexion->prepare("SELECT COUNT(*) AS total FROM avisos WHERE id_docente = ?");
$stmt_total->bind_param("i", $id_docente);
$stmt_total->execute();
$total_avisos = $stmt_total->get_result()->fetch_assoc()['total'];

$por_pagina = 5;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$total_paginas = max(1, (int)ceil($total_avisos / $por_pagina));
if ($pagina > $total_paginas) $pagina = $total_paginas;
$offset = ($pagina - 1) * $por_pagina;

$avisos = null;
if ($vista === 'lista') {
    $stmt = $conexion->prepare("SELECT * FROM avisos WHERE id_docente = ? ORDER BY fecha_pub DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $id_docente, $por_pagina, $offset);
    $stmt->execute();
    $avisos = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../img/imagen1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITCH - Gestión de Avisos</title>
    
    <style>
        :root { 
    --bg-dark: #020617; 
    --bg-gradient-top: #0f172a; 
    --card-bg: rgba(15, 23, 42, 0.7); 
    --accent-blue: #3b82f6; 
    --border-color: rgba(255, 255, 255, 0.15); 
    --text-primary: #f8fafc; 
    --text-secondary: #94a3b8; 
    --neon-glow: 0 0 15px rgba(59, 130, 246, 0.5); 
}

body { 
    background: radial-gradient(circle at top right, var(--bg-gradient-top), var(--bg-dark)); 
    color: var(--text-primary); 
    font-family: 'Inter', sans-serif; 
    min-height: 100vh; 
}

.main-card { 
    background: var(--card-bg); 
    backdrop-filter: blur(12px); 
    border: 1px solid var(--border-color); 
    border-radius: 20px; 
    padding: 30px; 
    box-shadow: 0 20px 50px rgba(0,0,0,0.5); 
    margin-top: 30px; 
    margin-bottom: 50px; 
}

.form-control, .form-select { 
    background-color: rgb(30, 41, 59) !important; 
    border: 1px solid var(--border-color) !important; 
    color: #ffffff !important; 
    border-radius: 12px; 
}

.form-control::placeholder, .form-select::placeholder {
    color: var(--text-secondary) !important; 
    opacity: 1 !important;
}

.aviso-item-card { 
    background: rgba(255, 255, 255, 0.03); 
    border: 1px solid var(--border-color); 
    border-radius: 16px; 
    transition: 0.3s; 
    margin-bottom: 20px; 
}

.aviso-item-card:hover { 
    background: rgba(255, 255, 255, 0.05); 
    transform: translateY(-3px); 
    border-color: var(--accent-blue); 
    box-shadow: var(--neon-glow); 
}

.btn { border-radius: 10px; font-weight: 600; }

.paginacion-wrapper { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }

.page-link-btn { 
    min-width: 40px; height: 40px; 
    display: flex; align-items: center; justify-content: center; 
    background: rgba(30, 41, 59, 0.5); 
    border: 1px solid var(--border-color); 
    color: white; 
    text-decoration: none; 
    border-radius: 10px; 
}

.page-link-btn.activa { 
    background: var(--accent-blue); 
    border-color: var(--accent-blue); 
}

    </style>
</head>
<body>
 <?php include('includes/header.php'); ?>
<div class="container">

        <?php if ($vista === 'inicio'): ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card bg-dark border-secondary mb-5" style="border-radius: 15px;">
                        <div class="card-body p-4">
                        
            <div class="container">
            <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h5 class="fw-bold  text-light">📢 COMUNICAR AVISOS</h5>
                <p style="color: #e2e8f0 !important; font-weight: 500; font-size: 1rem; margin-top: 5px; letter-spacing: 0.3px;">
                Administra los avisos para la comunidad educativa. Crea mensajes importantes, adjunta archivos relevantes y mantén a todos informados de manera rápida y efectiva. ¡Haz que tus avisos destaquen! 🚀
                </p>
            </div>
                <a href="panel_docente.php" class="btn btn-outline-primary">Regresar</a>
            </header>

                            <h5 class="text-light fw-bold mb-4">✍️ Crear Nuevo Aviso</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="accion" value="crear">
                                <div class="mb-3"><input name="titulo" class="form-control" placeholder="Título del aviso" required></div>
                                <div class="mb-3"><textarea name="contenido" class="form-control" rows="3" placeholder="Escribe el mensaje aquí..." required></textarea></div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <select name="tipo" class="form-select">
                                            <option value="info">🔵 Informativo</option>
                                            <option value="advertencia">🟡 Advertencia</option>
                                            <option value="urgente">🔴 Urgente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="file" name="archivo" class="form-control">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Publicar Aviso Ahora</button>
                            </form>
                        </div>
                    </div>
                    <div class="text-center py-4">
                        <?php if ($total_avisos > 0): ?>
                            <a href="avisos_docente.php?vista=lista&pagina=1" class="btn btn-primary px-5 py-3 shadow">
                                📋 Ver Historial de Avisos (<?= $total_avisos ?>)
                            </a>
                        <?php else: ?>
                            <p style="color: #ffffff !important; font-weight: 700; font-size: 1.2rem; text-shadow: 0 0 10px rgba(255,255,255,0.2);">
                                Aún no has publicado avisos. 🚀
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <?php if ($avisos && $avisos->num_rows > 0): ?>
                <?php while ($row = $avisos->fetch_assoc()): ?>
                    <div class="aviso-item-card p-4">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="pagina_actual" value="<?= $pagina ?>">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <input name="titulo" class="form-control fw-bold mb-2" value="<?= htmlspecialchars($row['titulo']) ?>">
                                    <textarea name="contenido" class="form-control mb-2" rows="2"><?= htmlspecialchars($row['contenido']) ?></textarea>
                                </div>
                                <div class="col-md-4">
                                    <select name="tipo" class="form-select mb-2">
                                        <option value="info" <?= $row['tipo']=='info' ? 'selected' : '' ?>>Info</option>
                                        <option value="advertencia" <?= $row['tipo']=='advertencia' ? 'selected' : '' ?>>Advertencia</option>
                                        <option value="urgente" <?= $row['tipo']=='urgente' ? 'selected' : '' ?>>Urgente</option>
                                    </select>
                                    <input type="file" name="archivo" class="form-control mb-2">
                                    <?php if ($row['archivo']): ?>
                                        <a href="../uploads/avisos/<?= $row['archivo'] ?>" target="_blank" class="text-info small">📎 Ver Adjunto</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" name="accion" value="editar" class="btn btn-primary btn-sm">💾 Guardar</button>
                                    <span class="badge <?= $row['activo'] ? 'bg-success' : 'bg-danger' ?> pt-2"><?= $row['activo'] ? 'Visible' : 'Oculto' ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="confirmar(this.form, 'toggle')"><?= $row['activo'] ? 'Ocultar' : 'Mostrar' ?></button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmar(this.form, 'eliminar')">🗑️</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5" style="border: 1px dashed var(--text-secondary); border-radius: 20px; background: rgba(255,255,255,0.02);">
                    <h4 style="color: #ffffff !important; font-weight: 800;">No hay avisos registrados.</h4>
                    <p style="color: var(--text-secondary);">Regresa al inicio para crear tu primer aviso.</p>
                </div>
            <?php endif; ?>

            <?php if ($total_paginas > 1): ?>
                <div class="paginacion-wrapper">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?vista=lista&pagina=<?= $i ?>" class="page-link-btn <?= ($i == $pagina) ? 'activa' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function confirmar(form, accion) {
        const config = {
            toggle: { t: '¿Cambiar visibilidad?', txt: 'El aviso cambiará su estado.', c: '#3b82f6' },
            eliminar: { t: '¿Eliminar aviso?', txt: 'Esta acción borrará el registro y el archivo permanentemente.', c: '#ef4444' }
        };
        Swal.fire({
            title: config[accion].t, text: config[accion].txt, icon: accion === 'eliminar' ? 'warning' : 'info',
            showCancelButton: true, confirmButtonColor: config[accion].c, background: '#0f172a', color: '#fff', confirmButtonText: 'Confirmar'
        }).then((result) => {
            if (result.isConfirmed) {
                const input = document.createElement('input'); input.type = 'hidden'; input.name = 'accion'; input.value = accion;
                form.appendChild(input); form.submit();
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