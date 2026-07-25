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

$id_solicitud = intval($_GET['id']);

// 2. CONSULTAR LA TABLA DE BUENA CONDUCTA
$stmt = $conexion->prepare("SELECT id, nombre_completo, numero_control, carrera, fecha_solicitud FROM solicitudes_cartas_buena_conducta WHERE id = ?");
$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Error: Solicitud no encontrada.");
}

$nc = $data['numero_control'];

// 3. PROCESAMIENTO DE FECHAS EN ESPAÑOL
$timestamp = strtotime($data['fecha_solicitud']);
$dia = date('d', $timestamp);
$meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
$mes_texto = $meses[date('n', $timestamp) - 1];
$anio = date('Y', $timestamp);

$fecha_formateada = "$dia de $mes_texto del $anio";

/**
 * FUNCIÓN ROBUSTA PARA IMÁGENES EN DOCKER
 */
function imgToBase64($nombreArchivo) {
    $rutasAProbar = [
        __DIR__ . '/img/' . $nombreArchivo,
        __DIR__ . '/../img/' . $nombreArchivo,
        $_SERVER['DOCUMENT_ROOT'] . '/img/' . $nombreArchivo,
        'img/' . $nombreArchivo
    ];

    foreach ($rutasAProbar as $rutaReal) {
        if (file_exists($rutaReal)) {
            $type = pathinfo($rutaReal, PATHINFO_EXTENSION);
            if (strtolower($type) === 'jpg' || strtolower($type) === 'jpeg') {
                $type = 'jpeg';
            }
            $data = file_get_contents($rutaReal);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
    return ''; 
}

// Carga exacta de tus archivos individuales
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
    /* COMPACTADO: Reducción de márgenes superior e inferior de la página */
    @page { margin: 135px 50px 125px 50px; }
    body { font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; color: #000; }

    /* HEADER */
    header { position: fixed; top: -115px; left: 0px; right: 0px; height: 100px; }
    .header-table { width: 100%; border-collapse: collapse; }
    .logo-sep-redimensionado { width: 220px; height: auto; vertical-align: middle; } 
    .logo-img2-redimensionado { width: 55px; height: auto; vertical-align: middle; margin-left: 10px; } 
    .logo-mj-header-redimensionado { width: 85px; height: auto; }
    .header-text-right { font-size: 7.5pt; text-align: right; line-height: 1.1; margin-top: 3px; color: #333; }

    /* FOOTER */
    footer { position: fixed; bottom: -105px; left: 0px; right: 0px; height: 110px; }
    .footer-table { width: 100%; border-collapse: collapse; }
    .img-margarita { width: 120px; height: auto; }
    .cert-block { text-align: right; margin-bottom: 3px; }
    .logo-footer-small { height: 28px; width: auto; margin-left: 6px; vertical-align: middle; }
    .black-line { border-top: 3px solid #333; width: 100%; margin-bottom: 4px; }
    .footer-address { font-size: 7pt; text-align: left; line-height: 1.2; color: #000; }

    /* CUERPO Y DOCUMENTO - COMPACTADOS */
    .metadata { text-align: right; margin-bottom: 15px; font-size: 10pt; line-height: 1.2; }
    .recipient { margin-bottom: 15px; font-weight: bold; }
    .body-text { text-align: justify; margin-bottom: 15px; }
    .body-text p { margin-bottom: 10px; text-indent: 0px; }
    
    /* SECCIÓN FIRMA COMPACTA */
    .signature-section { text-align: left; margin-top: 20px; }
    .slogan { font-size: 8.5pt; font-style: italic; margin: 0; line-height: 1.2; }
    .signature-line { width: 260px; border-top: 1px solid #000; margin: 35px 0 5px 0; }
    .signature-name { font-weight: bold; margin-top: 3px; text-transform: uppercase; }
    .initials { text-align: left; margin-top: 5px; font-size: 6pt; color: #555; }
</style>
</head>
<body>

<header>
    <table class="header-table">
        <tr>
            <td style="width: 75%; text-align: left; vertical-align: middle;">
                <?php if ($imgSep): ?><img src="<?php echo $imgSep; ?>" class="logo-sep-redimensionado"><?php endif; ?>
                <?php if ($imgImg2): ?><img src="<?php echo $imgImg2; ?>" class="logo-img2-redimensionado"><?php endif; ?>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: top;">
                <?php if ($imgMj): ?><img src="<?php echo $imgMj; ?>" class="logo-mj-header-redimensionado"><?php endif; ?>
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
                <?php if ($img1a): ?><img src="<?php echo $img1a; ?>" class="img-margarita"><?php endif; ?>
            </td>
            <td style="width: 78%; vertical-align: bottom;">
                <div class="cert-block">
                    <?php if ($img2a): ?><img src="<?php echo $img2a; ?>" class="logo-footer-small"><?php endif; ?>
                    <?php if ($img5): ?><img src="<?php echo $img5; ?>" class="logo-footer-small"><?php endif; ?>
                    <?php if ($img3a): ?><img src="<?php echo $img3a; ?>" class="logo-footer-small"><?php endif; ?>
                    <?php if ($img4a): ?><img src="<?php echo $img4a; ?>" class="logo-footer-small"><?php endif; ?>
                    <?php if ($img5a): ?><img src="<?php echo $img5a; ?>" class="logo-footer-small"><?php endif; ?>
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
    <p><strong>DEPENDENCIA:</strong> SUBDIRECCIÓN ACADÉMICA</p>
    <p><strong>SECCIÓN:</strong> DIVISIÓN DE ESTUDIOS PROFESIONALES</p>
    <p style="margin-top: 2px;">Chetumal, Quintana Roo, a <?php echo $fecha_formateada; ?></p> 
    <p><strong>Oficio No.</strong> 339/<?php echo $anio; ?></p>
    <p style="margin-top: 2px;"><strong>ASUNTO:</strong> Carta respaldo de buena conducta</p>
</div>

<div class="recipient">
    <p>A QUIEN CORRESPONDA:<br>
    PRESENTE</p>
</div>

<div class="body-text">
    <p>
        Por este medio se hace constar que el (la) C. <strong><?php echo mb_strtoupper($data['nombre_completo'], 'UTF-8'); ?></strong>, estudiante con número de control <strong><?php echo htmlspecialchars($data['numero_control']); ?></strong>, de la carrera de <strong><?php echo htmlspecialchars($data['carrera']); ?></strong>, que se ofrece en este plantel educativo, durante su estancia como alumno(a) de nuestra Institución, ha demostrado tener <strong>BUENA CONDUCTA</strong> dando cumplimiento al Reglamento Estudiantil (Derechos y obligaciones) del Tecnológico Nacional de México (TecNM).
    </p> 

    <p>
        Por lo que, a petición del (de la) estudiante, se expide la presente en la ciudad de Chetumal, Quintana Roo, a los <?php echo $dia; ?> días del mes de <?php echo $mes_texto; ?> del año dos mil veintiséis.
    </p>

    <p>
        Sin más por el momento, aprovecho la ocasión para enviarle un afectuoso saludo.
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
        c.c.p. Minutario<br>
        MANM/CLT/lina
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

$dompdf->stream("Carta_Buena_Conducta_" . $nc . ".pdf", ["Attachment" => false]);
?>