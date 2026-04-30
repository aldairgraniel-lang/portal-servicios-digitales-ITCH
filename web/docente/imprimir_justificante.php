<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Conexión
include(__DIR__ . "/../conexion.php");
include('includes/auth_docente.php');

$id = $_GET['id'] ?? 0;
$stmt = $conexion->prepare("SELECT * FROM justificantes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) { die("Registro no encontrado."); }

function fechaEspanol() {
    $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    return date('d') . " de " . $meses[date('n') - 1] . " de " . date('Y');
}

/**
 * Función para convertir imágenes a Base64
 * Busca en: /var/www/html/Portal_De_Servicios/web/docente/img/
 */
function imgToBase64($nombreArchivo) {
    $rutaReal = __DIR__ . '/img/' . $nombreArchivo;
    if (file_exists($rutaReal)) {
        $type = pathinfo($rutaReal, PATHINFO_EXTENSION);
        $data = file_get_contents($rutaReal);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return ''; 
}

// Carga de imágenes para el PDF
$imgSep   = imgToBase64('sep.png');
$imgItch2 = imgToBase64('img2.jpg');
$imgItch  = imgToBase64('2a.png');
$imgCert  = imgToBase64('img5.jpg');

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* DOMPDF CONFIGURATION */
        @page { 
            margin: 150px 50px 150px 50px; 
        }
        
        body { font-family: 'Times New Roman', serif; font-size: 13px; line-height: 1.5; color: #000; }

        /* Header Fijo */
        header {
            position: fixed;
            top: -130px;
            left: 0px;
            right: 0px;
            height: 100px;
            border-bottom: 2px solid #000;
        }

        /* Footer Fijo */
        footer {
            position: fixed;
            bottom: -130px;
            left: 0px;
            right: 0px;
            height: 100px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* Estilos de contenido */
        .header-table { width: 100%; }
        .header-table img { width: 80px; }
        .center { text-align: center; }
        
        .info-table { width: 100%; margin: 30px 0; }
        .label { font-weight: bold; width: 150px; }
        
        .footer-table { width: 100%; }
        .footer-img { height: 50px; }
        .footer-text { font-size: 9px; text-align: center; color: #555; }
    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td style="width: 20%;"><img src="<?= $imgSep ?>"></td>
                <td style="width: 60%; text-align: center;">
                    <strong>INSTITUTO TECNOLÓGICO DE CHETUMAL</strong><br>
                    División de Estudios Profesionales
                </td>
                <td style="width: 20%; text-align: right;"><img src="<?= $imgItch2 ?>"></td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 20%;"><img src="<?= $imgItch ?>" class="footer-img"></td>
                <td class="footer-text">
                    Av. Insurgentes #330, Col. David Gustavo Gutiérrez, C.P. 77013 | Chetumal, Q. Roo.<br>
                    Tel: (983) 832-23-30 | Email: direccion@itchetumal.edu.mx | escolares@itchetumal.edu.mx<br>
                    http://www.itchetumal.edu.mx
                </td>
                <td style="width: 20%; text-align: right;"><img src="<?= $imgCert ?>" class="footer-img"></td>
            </tr>
        </table>
    </footer>

    <main>
        <div style="text-align: right; margin-top: 20px;">
            <strong>ASUNTO:</strong> Justificante Académico<br>
            Chetumal, Q. Roo., a <?= fechaEspanol() ?>
        </div>

        <table class="info-table">
            <tr><td class="label">NOMBRE:</td><td><?= htmlspecialchars($data['nombre']) ?></td></tr>
            <tr><td class="label">N. DE CONTROL:</td><td><?= htmlspecialchars($data['n_control']) ?></td></tr>
        </table>

        <div>
            <p><strong>A QUIEN CORRESPONDA:</strong></p>
            <p>Por medio de la presente, se hace constar que el alumno(a) arriba mencionado(a) ha presentado la documentación comprobatoria necesaria para justificar su inasistencia a las actividades académicas por motivos de: <strong><?= htmlspecialchars($data['motivo']) ?></strong>.</p>
            
            <p>Dicho periodo de inasistencia comprende del <strong><?= htmlspecialchars($data['fecha_inicio']) ?></strong> al <strong><?= htmlspecialchars($data['fecha_fin']) ?></strong>.</p>
            
            <p>Se solicita atentamente a los docentes de las asignaturas que cursa el estudiante, brindar las facilidades necesarias para la entrega de trabajos, tareas y/o evaluaciones programadas.</p>
            
            <p>Se extiende la presente para los fines legales y administrativos que al interesado convengan.</p>
        </div>

        <div style="margin-top: 100px; text-align: center;">
            <p>__________________________________________<br>
            <strong>Departamento de División de Estudios Profesionales</strong></p>
        </div>
    </main>

</body>
</html>
<?php
$html = ob_get_clean();

// Configuración de Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true); 
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Justificante_" . $data['n_control'] . ".pdf", ["Attachment" => false]);
?>