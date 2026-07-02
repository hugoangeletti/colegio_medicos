<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');

// 1. Inclusión de tu librería TCPDF
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

$objEspecialidades = new especialidades_pdo();
$resEspecialidades = $objEspecialidades->obtenerEspecialidades();

if (!$resEspecialidades['estado'] || empty($resEspecialidades['datos'])) {
    echo "<script>alert('No hay datos para mostrar en el reporte.'); window.close();</script>";
    exit;
}

// Obtener la fecha de emisión actual [1, 2]
$fechaEmision = date('d/m/Y H:i');

// 2. Configuración e Inicialización de TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Metadatos del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Colegio de Médicos');
$pdf->SetTitle('Reporte de Especialidades');

// Configuración de la cabecera incluyendo la fecha de emisión [3]
$pdf->SetHeaderData('', 0, 'COLEGIO DE MÉDICOS - DISTRITO I', 'Reporte General de Especialidades - Emitido el: ' . $fechaEmision);
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

// 3. Construcción de la tabla en HTML para TCPDF [1, 3]
$html = '
<style>
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #f2f2f2; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; }
    .text-center { text-align: center; }
</style>
<h3>Listado de Especialidades</h3>
<table cellpadding="5">
    <thead>
        <tr>
            <th width="10%">ID</th>
            <th width="60%">Especialidad</th>
            <th width="15%">Código</th>
            <th width="15%">Res. 62707</th>
        </tr>
    </thead>
    <tbody>';

foreach ($resEspecialidades['datos'] as $c) {
    // Se mantiene la exclusión de Calificaciones Agregadas (tipo 3) [4, 5]
    if ($c['idTipoEspecialidad'] == 3) { continue; }

    $idEspecialidad = $c['idEspecialidad'];
    $nombre = htmlspecialchars($c['nombreEspecialidad']);
    $codigo = !empty($c['codigo']) ? htmlspecialchars($c['codigo']) : '-';
    $codigoRes = htmlspecialchars($c['codigoResolucion']);
    
    $html .= '
        <tr>
            <td width="10%" class="text-center">'.$idEspecialidad.'</td>
            <td width="60%">'.$nombre.'</td>
            <td width="15%" class="text-center">'.$codigo.'</td>
            <td width="15%" class="text-center">'.$codigoRes.'</td>
        </tr>';
}

$html .= '
    </tbody>
</table>';

// Imprimir el HTML estructurado en el documento PDF [1, 2]
$pdf->writeHTML($html, true, false, true, false, '');

// 4. Salida del PDF al navegador
$pdf->Output('especialidades.pdf', 'I'); 
exit;
?>