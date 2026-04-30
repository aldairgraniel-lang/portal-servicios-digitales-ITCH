<?php
ob_start();
date_default_timezone_set('America/Cancun');
if (session_status() === PHP_SESSION_NONE) session_start();

include('includes/auth_docente.php');
include(__DIR__ . "/../conexion.php");
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['nc']) || empty($_GET['nc'])) {
    die("Error: Número de control no especificado.");
}

$nc = $_GET['nc'];
$stmt = $conexion->prepare("SELECT * FROM registro_ingles WHERE numero_control = ?");
$stmt->bind_param("s", $nc);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Error: Alumno no encontrado.");
}

$estado_detalle = htmlspecialchars($data['tipo_alumno']);
if ($data['tipo_alumno'] == 'Cursando semestre' && !empty($data['semestre'])) {
    $estado_detalle .= " (" . htmlspecialchars($data['semestre']) . ")";
}

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

// Carga de imágenes
$imgSep = imgToBase64('sep.png');
$imgImg2 = imgToBase64('img2.jpg');
$imgMj = imgToBase64('mj.png');
$img1a = imgToBase64('1a.png');
$img2a = imgToBase64('2a.png');
$img5 = imgToBase64('img5.jpg');
$img3a = imgToBase64('3A.png');
$img4a = imgToBase64('4a.png');
$img5a = imgToBase64('5a.png');

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 150px 50px 140px 50px; }
    body { font-family: 'Times New Roman', serif; font-size: 11.5pt; line-height: 1.4; color: #000; }

    /* HEADER: Ajustado para mayor precisión visual */
    header { position: fixed; top: -130px; left: 0px; right: 0px; height: 110px; }
    
    .header-table { width: 100%; border-collapse: collapse; }
    
    /* Logos Principales (Izquierda): Tamaño reducido para que no se pierdan */
    .logo-sep-redimensionado { width: 230px; height: auto; vertical-align: middle; } 
    .logo-img2-redimensionado { width: 60px; height: auto; vertical-align: middle; margin-left: 10px; } 

    /* Logo Lateral (Derecha): Tamaño ajustado y texto institucional */
    .logo-mj-header-redimensionado { width: 90px; height: auto; }
    .header-text-right { font-size: 8pt; text-align: right; line-height: 1.1; margin-top: 3px; }

    /* FOOTER: Diseño de línea negra gruesa y logos alineados (sin cambios) */
    footer { position: fixed; bottom: -110px; left: 0px; right: 0px; height: 130px; }
    .footer-table { width: 100%; border-collapse: collapse; }
    .img-margarita { width: 135px; height: auto; }
    .cert-block { text-align: right; margin-bottom: 5px; }
    .logo-footer-small { height: 32px; width: auto; margin-left: 8px; vertical-align: middle; }
    .black-line { border-top: 3.5px solid #333; width: 100%; margin-bottom: 5px; }
    .footer-address { font-size: 7.2pt; text-align: left; line-height: 1.2; color: #000; }

    /* CUERPO Y FIRMA (sin cambios) */
    .metadata { text-align: right; margin-bottom: 15px; font-size: 11pt; }
    .recipient { margin-bottom: 20px; font-weight: bold; }
    .body-text { text-align: justify; margin-bottom: 20px; }
    .signature-section { text-align: left; margin-top: 30px; }
    .slogan { font-size: 9pt; font-style: italic; margin: 0; }
    .signature-line { width: 280px; border-top: 1px solid #000; margin: 50px 0 10px 0; }
    .signature-name { font-weight: bold; margin-top: 5px; text-transform: uppercase; }
    .initials { text-align: left; margin-top: 5px; font-size: 6pt; }

</style>
</head>
<body>

<header>
    <table class="header-table">
        <tr>
            <td style="width: 75%; text-align: left; vertical-align: middle;">
                <img src="<?= $imgSep ?>" class="logo-sep-redimensionado">
                <img src="<?= $imgImg2 ?>" class="logo-img2-redimensionado">
            </td>
            <td style="width: 25%; text-align: right; vertical-align: top;">
                <img src="<?= $imgMj ?>" class="logo-mj-header-redimensionado">
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
                <img src="<?= $img1a ?>" class="img-margarita">
            </td>
            <td style="width: 78%; vertical-align: bottom;">
                <div class="cert-block">
                    <img src="<?= $img2a ?>" class="logo-footer-small">
                    <img src="<?= $img5 ?>" class="logo-footer-small">
                    <img src="<?= $img3a ?>" class="logo-footer-small">
                    <img src="<?= $img4a ?>" class="logo-footer-small">
                    <img src="<?= $img5a ?>" class="logo-footer-small">
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
    <p>Chetumal, Quintana Roo, a <?= $fecha_formateada ?></p> 
    <p><strong>OFICIO:</strong> C-174/2026</p>
    <p><strong>ASUNTO:</strong> NO INCONVENIENCIA DE INGLÉS</p>
</div>

<div class="recipient">
    <p>MTRA. PAOLY ELIZABETH PERERA MALDONADO<br>
    DIRECTORA GENERAL DEL INSTITUTO TECNOLÓGICO SUPERIOR<br>
    DE FELIPE CARRILLO PUERTO<br>
    PRESENTE</p>
</div>

<div class="body-text">
    <p>Por este medio le informo que no existe inconveniente para que el (la) C. <strong><?= htmlspecialchars($data['nombre']) ?></strong> 
    con número de control <strong><?= htmlspecialchars($data['numero_control']) ?></strong>, y su estado como <strong><?= $estado_detalle ?></strong> 
    de la carrera de <strong><?= htmlspecialchars($data['carrera']) ?></strong>, pueda cursar los niveles correspondientes al idioma INGLÉS 
    en el Instituto que Usted dirige, en el periodo <strong><?= htmlspecialchars($data['periodo']) ?></strong> para cumplir con el requisito 
    de una lengua extranjera para titulación. Sin otro particular, le envío un cordial saludo.</p> 
</div>

<div class="signature-section">
    <p><strong>A T E N T A M E N T E</strong></p>
    <p class="slogan">"Excelencia en Educación Tecnológica ®"</p>
    <p class="slogan">"Cultura, Ciencia y Tecnología para la Superación de México ®"</p>
    
    <div class="signature-line"></div>
    
    <p class="signature-name">MTRO. MARIO VICENTE GONZÁLEZ ROBLES</p>
    <p>DIRECTOR</p>
    <div class="initials">
        MVGR/MARA/YAT*imas
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

$dompdf->stream("Constancia_No_Inveniencia_" . $nc . ".pdf", ["Attachment" => false]);
?>