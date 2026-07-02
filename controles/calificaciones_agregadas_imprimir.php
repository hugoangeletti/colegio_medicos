<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');
// 1. Inclusión de tu librería TCPDF
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
$objEspecialidades = new especialidades_pdo();
$resCalificaciones = $objEspecialidades->obtenerCalificacionesAgergadas();
if (!$resCalificaciones['estado'] || empty($resCalificaciones['datos'])) {
    echo "<script>alert('No hay datos para mostrar en el reporte.'); window.close();</script>";
    exit;
}
// Obtener la fecha de emisión actual
$fechaEmision = date('d/m/Y H:i');
// 2. Configuración e Inicialización de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
// Metadatos del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Colegio de Médicos');
$pdf->SetTitle('Reporte de Calificaciones Agregadas');
// Configuración de la cabecera por defecto
$pdf->SetHeaderData('', 0, 'COLEGIO DE MÉDICOS - DISTRITO I', 'Reporte General de Calificaciones Agregadas y Especialidades Base - Emitido el: ' . $fechaEmision);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// Márgenes
$pdf->SetMargins(15, 25, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
// Saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, 15);
// Fuentes
$pdf->SetFont('helvetica', '', 10);
// Añadir una página
$pdf->AddPage();
// 3. Construcción de la tabla en HTML para TCPDF
$html = '
<style>
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #f2f2f2; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; }
    .text-center { text-align: center; }
    .fila-padres td { border-top: none; background-color: #fafafa; }
    .padres-label { font-size: 9px; font-weight: bold; color: #333333; }
    .padres-lista { font-size: 9px; color: #555555; }
</style>
<h3>Listado de Calificaciones Registradas</h3>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="15%">ID</th>
            <th width="70%">Calificación Agregada</th>
            <th width="15%">Código</th>
        </tr>
    </thead>
    <tbody>';
foreach ($resCalificaciones['datos'] as $c) {
    $idCalificacion = $c['idEspecialidad'];
    $nombre = htmlspecialchars($c['nombreEspecialidad']);
    $codigo = !empty($c['codigo']) ? htmlspecialchars($c['codigo']) : '-';
    $codigoRes = htmlspecialchars($c['codigoResolucion']);

    // Fila principal con los datos de la calificación agregada
    $html .= '
        <tr>
            <td width="15%" class="text-center">'.$idCalificacion.'</td>
            <td width="70%">'.$nombre.'</td>
            <td width="15%" class="text-center">'.$codigo.'</td>
        </tr>';

    // Obtener las especialidades asociadas (padres) para cada calificación
    $resPadres = $objEspecialidades->obtenerEspecialidadesAsociadas($idCalificacion);
    if ($resPadres['estado'] && !empty($resPadres['datos'])) {
        $arrPadres = array();
        foreach ($resPadres['datos'] as $p) {
            $arrPadres[] = htmlspecialchars($p['nombreEspecialidad']);
        }
        $padresTexto = '• ' . implode('<br />• ', $arrPadres);
    } else {
        $padresTexto = '<i>Ninguna</i>';
    }

    // Fila adicional debajo de la calificación, ocupando todo el ancho,
    // con las especialidades base en lugar de una columna aparte
    $html .= '
        <tr class="fila-padres">
            <td></td>
            <td colspan="3">
                <span class="padres-label">Especialidades Base:</span><br />
                <span class="padres-lista">'.$padresTexto.'</span>
            </td>
        </tr>';
}
$html .= '
    </tbody>
</table>';
// Imprimir el HTML estructurado en el documento PDF
$pdf->writeHTML($html, true, false, true, false, '');
// 4. Salida del PDF al navegador
$pdf->Output('calificaciones_agregadas.pdf', 'I'); // 'I' abre el visor nativo del navegador
exit;
?>