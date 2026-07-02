<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoCargoLogic.php');
require_once ('../dataAccess/colegiadoArchivoLogic.php');

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
                $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 15);
                // Title
                $this->Cell(0, 15, 'Listado de Consejeros ('.cambiarFechaFormatoParaMostrar(date('Y-m-d')).')', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                $this->Ln(15);        
                $this->SetFont('helvetica', 'B', 9);
                $this->MultiCell(0, 5, 'Orden', 0, 'L', false, 0, '', '');
                $this->MultiCell(0, 5, 'Apellido y Nombres', 0, 'L', false, 0, '20', '');
                $this->MultiCell(0, 5, 'Teléfonos', 0, 'L', false, 0, '90', '');
                $this->MultiCell(0, 5, 'Correo Electronico', 0, 'L', false, 1, '145', '');
                $this->Line(0, 30, 220, 30, array('width' => 0));
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
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(5, 35, 5);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    define ('PDF_MARGIN_FOOTER', 8);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->AddPage();

$html='';
$orden = 0;
$colegiadoCargoLogic = new colegiadoCargoLogic();
$resConsejeros = $colegiadoCargoLogic->obtenerConsejerosVigentes();
if ($resConsejeros['estado']){
    foreach ($resConsejeros['datos'] as $dato){
        $pdf->SetFont('dejavusans', '', 8);
        $idColegiado = $dato['idColegiado'];
        $idColegiadoCargo = $dato['idColegiadoCargo'];
        $apellido = $dato['apellido'];
        $nombre = $dato['nombre'];
        $nombreCargo = $dato['nombreCargo'];
        $fechaDesde = $dato['fechaDesde'];
        $fechaHasta = $dato['fechaHasta'];
        $telefonoFijo = $dato['telefonoFijo'];
        $telefonoMovil = $dato['telefonoMovil'];
        $mail = $dato['mail'];
        $orden += 1;

        $pdf->MultiCell(0, 5, $orden, 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, 5, trim($apellido).' '.trim($nombre), 0, 'L', false, 0, '20', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 5, trim($telefonoFijo).' - '.trim($telefonoMovil), 0, 'L', false, 0, '90', '');
        $pdf->MultiCell(0, 5, $mail, 0, 'L', false, 1, '145', '');
        $pdf->Ln(2);        
    }
    //$html .= "</table>";
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

$pdf->Output('ListadoConsejeros.pdf', 'I');        
            
