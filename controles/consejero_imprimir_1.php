<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');
$colegiadoCargoLogic = new colegiadoCargoLogic();
require_once ('../dataAccess/colegiadoArchivoLogic.php');
$colegiadoArchivoLogic = new colegiadoArchivoLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 10, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Listado de Consejero', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);

                //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(true);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 8);

$html='';
$resConsejeros = $colegiadoCargoLogic->obtenerConsejeros();
if ($resConsejeros['estado']){
    $html .= '<p align="center"><h3><b>Listado de Consejeros</b></h3></p>';
    $html .= '<p align="right">'.  cambiarFechaFormatoParaMostrar(date('Y-m-d')).'</p>';
    $html .= '<table align="center" border="1" cellspacing="0" cellpadding="4">
            <tr>
            <th width="120"><b>Foto</b></th>
            <th><b>Apellido y Nombres</b></th>
            <th><b>Cargo</b></th>
            </tr>';
    foreach ($resConsejeros['datos'] as $dato){
        $idColegiado = $dato['idColegiado'];
        $idColegiadoCargo = $dato['idColegiadoCargo'];
        $apellido = $dato['apellido'];
        $nombre = $dato['nombre'];
        $nombreCargo = $dato['nombreCargo'];
        $fechaDesde = $dato['fechaDesde'];
        $fechaHasta = $dato['fechaHasta'];
        
        //verifica que tenga foto y firma para mostrar
        $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '1');
        if ($resArchivos['estado'] && isset($resArchivos['datos'])){
            $archivos = $resArchivos['datos'];
            $fileFoto = trim($archivos['nombre']);
            // insertamos la foto y firma
            $foto = fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Fotos/".$fileFoto, "rb");
            if (!$foto) {
                $foto = fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Fotos/silueta.jpg", "rb");
            }
            $contents=stream_get_contents($foto);
            fclose ($foto);

            $fotoVer = base64_encode($contents);
            $html .= '<tr>
                <td width="120"><img src="data:image/jpg;base64,'.$fotoVer.'" WIDTH="120" HEIGHT="80" /></td>
                <td>'.$apellido.', '.$nombre.'</td>
                <td>'.$nombreCargo.'</td>
                </tr>';
//                <td>'.cambiarFechaFormatoParaMostrar($fechaDesde).'</td>
//                <td>'.cambiarFechaFormatoParaMostrar($fechaHasta).'</td>
        }
    }
    $html .= "</table>";
} else {
    $html .= $resConsejeros['mensaje'];
}

$pdf->writeHTML($html, true, false, false, false, '');
$pdf->lastPage();

$destination='ColegiacionMatricula_'.$matricula.'.pdf';
if (!preg_match('/\.pdf$/', $path_to_store_pdf))
{
       $path_to_store_pdf .= '.pdf';
}
ob_clean();
if ($destination == 'D')
{
       echo $this->view->pdf->Output($path_to_store_pdf, $destination);
       exit();
}

$pdf->Output('ListadoConsejeros.pdf', 'D');        
            
