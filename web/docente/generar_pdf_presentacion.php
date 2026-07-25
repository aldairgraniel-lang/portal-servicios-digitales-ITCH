<?php
ob_start();
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. VALIDAR QUE SE RECIBA EL ID DE LA SOLICITUD
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Identificador de solicitud no especificado.");
}

$id_solicitud = $_GET['id'];

// 2. CONSULTAR LA TABLA CORRECTA (solicitudes_cartas_presentacion)
$stmt = $conexion->prepare("SELECT * FROM solicitudes_cartas_presentacion WHERE id = ?");
$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Error: Solicitud no encontrada.");
}

// Guardar número de control para el nombre del archivo PDF de salida
$nc = $data['numero_control'];

// Formatear la fecha de emisión en español
$meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$fecha_formateada = date('d') . " de " . $meses[(int)date('m')] . " del " . date('Y');

/**
 * FUNCIÓN PARA CARGAR IMÁGENES BASE64
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

// Carga de imágenes para Header y Footer
$imgSep   = imgToBase64('sep.png');
$imgImg2  = imgToBase64('img2.jpg');
$imgMj    = imgToBase64('mj.png');
$img1a    = imgToBase64('1a.png');
$img2a    = imgToBase64('2a.png');
$img5     = imgToBase64('img5.jpg');
$img3a    = imgToBase64('3a.png'); 
$img4a    = imgToBase64('4a.png');
$img5a    = imgToBase64('5a.png');

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 150px 50px 140px 50px; }
    body { font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.5; color: #000; }

    /* HEADER */
    header { position: fixed; top: -130px; left: 0px; right: 0px; height: 110px; }
    .header-table { width: 100%; border-collapse: collapse; }
    .logo-sep-redimensionado { width: 230px; height: auto; vertical-align: middle; } 
    .logo-img2-redimensionado { width: 60px; height: auto; vertical-align: middle; margin-left: 10px; } 
    .logo-mj-header-redimensionado { width: 90px; height: auto; }
    .header-text-right { font-size: 8pt; text-align: right; line-height: 1.1; margin-top: 3px; }

    /* FOOTER */
    footer { position: fixed; bottom: -110px; left: 0px; right: 0px; height: 130px; }
    .footer-table { width: 100%; border-collapse: collapse; }
    .img-margarita { width: 135px; height: auto; }
    .cert-block { text-align: right; margin-bottom: 5px; }
    .logo-footer-small { height: 32px; width: auto; margin-left: 8px; vertical-align: middle; }
    .black-line { border-top: 3.5px solid #333; width: 100%; margin-bottom: 5px; }
    .footer-address { font-size: 7.2pt; text-align: left; line-height: 1.2; color: #000; }

    /* CUERPO Y FIRMA */
    .metadata { text-align: right; margin-bottom: 25px; font-size: 11pt; }
    .recipient { margin-bottom: 25px; font-weight: bold; }
    .body-text { text-align: justify; margin-bottom: 25px; }
    .body-text p { margin-bottom: 15px; text-indent: 0px; }
    .signature-section { text-align: left; margin-top: 35px; }
    .slogan { font-size: 9pt; font-style: italic; margin: 0; }
    .signature-line { width: 280px; border-top: 1px solid #000; margin: 45px 0 10px 0; }
    .signature-name { font-weight: bold; margin-top: 5px; text-transform: uppercase; }
    .initials { text-align: left; margin-top: 5px; font-size: 6pt; }
</style>
</head>
<body>

<header>
    <table class="header-table">
        <tr>
            <td style="width: 75%; text-align: left; vertical-align: middle;">
                <img src="<?php echo $imgSep; ?>" class="logo-sep-redimensionado">
                <img src="<?php echo $imgImg2; ?>" class="logo-img2-redimensionado">
            </td>
            <td style="width: 25%; text-align: right; vertical-align: top;">
                <img src="<?php echo $imgMj; ?>" class="logo-mj-header-redimensionado">
                <div class="header-text-right">
                    Instituto Tecnológico de Chetumal<br>
                    DIVISIÓN DE ESTUDIOS PROFESIONALES
                </div>
            </td>
        </tr>
    </table>
</header>

<footer>
    <table class="footer-table">
        <tr>
            <td style="width: 22%; vertical-align: bottom; text-align: left;">
                <img src="<?php echo $img1a; ?>" class="img-margarita">
            </td>
            <td style="width: 78%; vertical-align: bottom;">
                <div class="cert-block">
                    <img src="<?php echo $img2a; ?>" class="logo-footer-small">
                    <img src="<?php echo $img5; ?>" class="logo-footer-small">
                    <img src="<?php echo $img3a; ?>" class="logo-footer-small">
                    <img src="<?php echo $img4a; ?>" class="logo-footer-small">
                    <img src="<?php echo $img5a; ?>" class="logo-footer-small">
                </div>
                <div class="black-line"></div>
                <div class="footer-address">
                    Av. Insurgentes #330, esq. Andrés Quintana Roo, Col. David Gustavo Gutiérrez, Apdo. Postal 67 C.P. 77013, <br>
                    Chetumal, Quintana Roo. Tel. (983) 8322330 ext. 101. <strong>www.chetumal.tecnm.mx</strong>
                </div>
            </td>
        </tr>
    </table>
</footer>

<div class="metadata">
    <p>Chetumal, Quintana Roo, a <?php echo $fecha_formateada; ?></p> 
    <p><strong>OFICIO:</strong> DEP-CP-<?php echo date('Y'); ?></p>
    <p><strong>ASUNTO:</strong> CARTA DE PRESENTACIÓN</p>
</div>

<div class="recipient">
    <p>A QUIEN CORRESPONDA:<br>
    PRESENTE</p>
</div>

<div class="body-text">
    <p>
        La que suscribe <strong>Lic. Cecilia Loría Tzab</strong>, Jefa de la División de Estudios Profesionales del Instituto Tecnológico de Chetumal, por medio del presente tengo a bien solicitar su amable colaboración, a efecto de que se le proporcione las facilidades necesarias al (la) estudiante <strong><?php echo htmlspecialchars($data['nombre']); ?></strong>, inscrito(a) en el <strong><?php echo htmlspecialchars($data['semestre']); ?>°</strong> semestre, de la carrera de <strong><?php echo htmlspecialchars($data['carrera'] ?? 'Licenciatura/Ingeniería'); ?></strong>, quien actualmente cursa la materia <em><?php echo htmlspecialchars($data['materia']); ?></em>, en el periodo <strong><?php echo htmlspecialchars($data['periodo']); ?></strong>, bajo la supervisión del (la) docente asesor(a) <strong><?php echo htmlspecialchars($data['docente_asesor']); ?></strong>.
    </p> 

    <p>
        El (la) estudiante requiere llevar a cabo actividades académicas y de vinculación práctica del <strong><?php echo date("d/m/Y", strtotime($data['fecha_inicio'])); ?></strong> al <strong><?php echo date("d/m/Y", strtotime($data['fecha_final'])); ?></strong>, con el objetivo de: <em><?php echo htmlspecialchars($data['objetivo']); ?></em>; lo cual facilitará el desarrollo de investigaciones y la aplicación de las técnicas correspondientes a su área de estudio.
    </p>

    <p>
        Lo anterior, debido a que aporta un gran valor a la formación integral de los estudiantes en temas relacionados con su desarrollo profesional, promoviendo la adquisición de experiencias directas en el campo laboral.
    </p>

    <p>
        Por lo que, agradezco de antemano el apoyo que se brinde a nuestros educandos, lo cual reviste un alto valor a la formación de profesionistas mejor preparados que respondan a las necesidades de los mercados laborales actualmente.
    </p>
</div>

<div class="signature-section">
    <p><strong>A T E N T A M E N T E</strong></p>
    <p class="slogan">"Excelencia en Educación Tecnológica ®"</p>
    <p class="slogan">"Cultura, Ciencia y Tecnología para la Superación de México ®"</p>
    
    <div class="signature-line"></div>
    
    <p class="signature-name">LIC. CECILIA LORÍA TZAB</p>
    <p>JEFA DE LA DIVISIÓN DE ESTUDIOS PROFESIONALES</p>
    <div class="initials">
        CLT/mara*imas
    </div>
</div>

</body>
</html>
<?php
$html = ob_get_clean();
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

$dompdf->stream("Carta_Presentacion_" . $nc . ".pdf", ["Attachment" => false]);
?>