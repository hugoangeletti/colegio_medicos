<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tramiteLogic.php');
$tramiteLogic = new tramiteLogic();

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
                $this->Cell(0, 35, 'Movimientos matriculares', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                //$this->Cell(0, 15, 'Movimientos matriculares ('.cambiarFechaFormatoParaMostrar(date('Y-m-d')).')', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
    //define ('PDF_MARGIN_FOOTER', 8);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$continua = TRUE;
if (isset($_POST['id']) && $_POST['id'] > 0) {
    $idTramite = $_POST['id'];
} else {
    $idTramite = NULL;
    $continua = FALSE;
}
if ($continua) {
    $resTramites = $tramiteLogic->obtenerTramiteDetalle($idTramite);
    if ($resTramites['estado']){
        $resTramite = $tramiteLogic->obtenerTramitePorId($idTramite);
        if ($resTramite['estado']) {
            $tramite = $resTramite['datos'];
            $fechaTramite = $tramite['fecha'];
        }
        //$html .= '<p align="center"><h3><b>Listado de Consejeros</b></h3></p>';
        //$html .= '<p align="right">'.  cambiarFechaFormatoParaMostrar(date('Y-m-d')).'</p>';
        //$html .= '<div width="120"><b>Foto</b></div>
        //        <div width="400"><b>Apellido y Nombres</b></div>
        //        <div width="300"><b>Cargo</b></div><br />';
        $linea = 0;
        $encabezado = TRUE;
        foreach ($resTramites['datos'] as $dato){
            $idTramiteDetalle = $dato['idTramiteDetalle'];
            $fecha = $dato['fecha'];
            $apellido = $dato['apellido'];
            $nombre = $dato['nombre'];
            $matricula = $dato['matricula'];
            $nombreMovimiento = $dato['nombreMovimiento'];
            $distritoCambio = $dato['distritoCambio'];
            
            if ($encabezado) {
                $pdf->AddPage();
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->MultiCell(0, 6, 'La Plata, '.substr($fechaTramite, 8, 2).' de '.obtenerMes(substr($fechaTramite, 5, 2)).' de '.substr($fechaTramite, 0, 4), 0, 'R', false, 1, '50', '');
                $pdf->Ln(5);
                $alturaLinea = 5;
                $pdf->SetFont('dejavusans', 'B', 8);
                $pdf->MultiCell(0, $alturaLinea, 'Matrícula', 0, 'L', false, 0, '15', '');
                $pdf->MultiCell(0, $alturaLinea, 'Apellido y Nombre', 0, 'L', false, 0, '35', '');
                $pdf->MultiCell(0, $alturaLinea, 'Tipo Movimiento', 0, 'L', false, 0, '100', '');
                $pdf->MultiCell(0, $alturaLinea, 'Fecha', 0, 'L', false, 1, '170', '');                
                $encabezado = FALSE;
            } 

            $linea++;
            $alturaLinea = 5;
            $pdf->SetFont('dejavusans', '', 8);
            //$pdf->MultiCell(0, $alturaLinea, $linea, 0, 'L', false, 0, '', '');
            $pdf->MultiCell(15, $alturaLinea, $matricula, 0, 'R', false, 0, '15', '');
            $pdf->MultiCell(0, $alturaLinea, $apellido.' '.$nombre, 0, 'L', false, 0, '35', '');
            $pdf->MultiCell(0, $alturaLinea, $nombreMovimiento, 0, 'L', false, 0, '100', '');
            $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fecha), 0, 'L', false, 1, '170', '');
            //$pdf->Ln(5);

            if(( $linea % 45 ) == 0){
                $encabezado = TRUE;
            }
        }
        //$html .= "</table>";
    } else {
        $html .= $resTramites['mensaje'];
    }


    $pdf->writeHTML($html, true, false, false, false, '');
    $pdf->lastPage();

    $destination='MovimientosMatriculares_'.$idTramite.'.pdf';
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

    $pdf->Output($destination, 'I');        
} else {
    echo 'INGRESO ERRONEO';
}                

