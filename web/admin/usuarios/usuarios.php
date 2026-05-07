<?php 
// 1. Seguridad: Solo admins pueden entrar aquí
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/auth.php'); 
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');
include($_SERVER['DOCUMENT_ROOT'] . '/admin/includes/header.php');

$result = $conexion->query("SELECT * FROM usuarios ORDER BY id ASC");
?>
<link rel="stylesheet" href="../css/tablasG.css">
<link rel="stylesheet" href="../css/filtroUsuario.css">

<div class="container py-5">
    <div class="glass-card p-4 shadow-lg">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold m-0"><i class="bi bi-people-fill me-2"></i> Gestión de Usuarios</h3>
                <span class="text-white-50 small">Administra los usuarios del sistema</span>
            </div>
            <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3 flex-wrap flex-fill flex-md-grow-0">
                <input type="text" id="filtroUsuario" class="form-control filtro-glass" placeholder="Filtrar por usuario..." onkeyup="filtrarUsuario()">
                <div class="d-flex gap-2">
                    <a href="../inicio.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> regresar
                    </a>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="nuevoUsuario()">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= htmlspecialchars($row['rol']) ?></td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-sm btn-primary" onclick="editarUsuario(<?= $row['id'] ?>, '<?= htmlspecialchars($row['usuario'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($row['rol'], ENT_QUOTES, 'UTF-8') ?>')" title="Editar">
                                    Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?= $row['id'] ?>)" title="Eliminar">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function nuevoUsuario() {
    Swal.fire({
        title: 'Nuevo Usuario',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Usuario</label>' +
            '<input type="text" id="swal-usuario" class="swal2-input" placeholder="Ingresa el usuario" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Contraseña</label>' +
            '<input type="password" id="swal-password" class="swal2-input" placeholder="Ingresa la contraseña" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Rol</label>' +
            '<select id="swal-rol" class="swal2-select" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<option value="docente">Docente</option>' +
            '<option value="admin">Admin</option>' +
            '</select>',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const usuario = document.getElementById('swal-usuario').value;
            const password = document.getElementById('swal-password').value;
            const rol = document.getElementById('swal-rol').value;
            if (!usuario || !password || !rol) {
                Swal.showValidationMessage('¡Los campos no pueden estar vacíos!');
            }
            return { usuario: usuario, password: password, rol: rol };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_usuario.php';
            form.method = 'POST';

            let accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'agregar';

            let usuarioInput = document.createElement('input');
            usuarioInput.type = 'hidden';
            usuarioInput.name = 'usuario';
            usuarioInput.value = result.value.usuario;

            let passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = result.value.password;

            let rolInput = document.createElement('input');
            rolInput.type = 'hidden';
            rolInput.name = 'rol';
            rolInput.value = result.value.rol;

            form.appendChild(accionInput);
            form.appendChild(usuarioInput);
            form.appendChild(passwordInput);
            form.appendChild(rolInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function editarUsuario(id, usuarioActual, rolActual) {
    Swal.fire({
        title: 'Editar Usuario',
        html:
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-bottom:5px; margin-left:5px;">Usuario</label>' +
            '<input type="text" id="swal-usuario" class="swal2-input" value="' + usuarioActual.replace(/"/g, '&quot;') + '" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Contraseña (dejar en blanco para mantener)</label>' +
            '<input type="password" id="swal-password" class="swal2-input" placeholder="Nueva contraseña" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<label class="text-white-50 small" style="display:block; text-align:left; margin-top:10px; margin-bottom:5px; margin-left:5px;">Rol</label>' +
            '<select id="swal-rol" class="swal2-select" style="background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; margin-top:0; width: 85%;">' +
            '<option value="docente" ' + (rolActual === 'docente' ? 'selected' : '') + '>Docente</option>' +
            '<option value="admin" ' + (rolActual === 'admin' ? 'selected' : '') + '>Admin</option>' +
            '</select>',
        showCancelButton: true,
        confirmButtonColor: '#6366f1',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a', 
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)',
        preConfirm: () => {
            const usuario = document.getElementById('swal-usuario').value;
            const password = document.getElementById('swal-password').value;
            const rol = document.getElementById('swal-rol').value;
            if (!usuario || !rol) {
                Swal.showValidationMessage('¡Los campos usuario y rol no pueden estar vacíos!');
            }
            return { usuario: usuario, password: password, rol: rol };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.action = 'procesar_usuario.php';
            form.method = 'POST';

            let accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'editar';

            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            let usuarioInput = document.createElement('input');
            usuarioInput.type = 'hidden';
            usuarioInput.name = 'usuario';
            usuarioInput.value = result.value.usuario;

            let passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = result.value.password;

            let rolInput = document.createElement('input');
            rolInput.type = 'hidden';
            rolInput.name = 'rol';
            rolInput.value = result.value.rol;

            form.appendChild(accionInput);
            form.appendChild(idInput);
            form.appendChild(usuarioInput);
            form.appendChild(passwordInput);
            form.appendChild(rolInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        text: "Esta acción no se puede revertir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, borrar',
        background: '#0f172a',
        color: '#fff',
        backdrop: 'rgba(0, 0, 0, 0.8)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'procesar_usuario.php?accion=eliminar&id=' + id;
        }
    });
}

function filtrarUsuario() {
    let input = document.getElementById('filtroUsuario').value.toLowerCase();
    let filas = document.querySelectorAll('tbody tr');
    
    filas.forEach(fila => {
        let usuario = fila.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (usuario.indexOf(input) > -1) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>