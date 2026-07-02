<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
        public $el_titulo;
        public $x_inicio;
        public $x_fin;

        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', '', 10);
                $this->SetXY(190, 10);
                $fecha_actual = cambiarFechaFormatoParaMostrar(date('Y-m-d'));
                $this->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'R'); // A la derecha (R)

                // Title
                $this->SetFont('helvetica', 'B', 12);
                $this->Ln(5);        
                $this->Cell(0, 10, $this->el_titulo, 0, false, 'C', 0, '', 0, false, 'M', 'M');
                $this->Ln(5);
                $this->SetFont('helvetica', 'B', 7);
                $this->MultiCell(0, 5, 'Año', 0, 'L', false, 0, $this->x_inicio, '');
                $this->MultiCell(0, 5, 'Cantidad', 0, 'L', false, 1, $this->x_inicio + 30, '');

                $y_line = $this->GetY();
                $this->Line($this->x_inicio, $y_line, $this->x_fin, $y_line, array('width' => 0.5));
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);

                // Page number
                $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}

$fapLogic = new fapLogic();
$anioHasta = date('Y');
$anioDesde = $anioHasta - 15;
$idSapTipoTramite = 4;
$resultado = $fapLogic->obtenerCantidadPorAnio($anioDesde, $anioHasta, $idSapTipoTramite);
if ($resultado['estado'] && sizeof($resultado['datos']) > 0) {
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    $x_inicio = 70;
    $x_fin = $x_inicio + 50;
    $pdf->x_inicio = $x_inicio;
    $pdf->x_fin = $x_fin;
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
    //define ('PDF_MARGIN_FOOTER', 8);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->el_titulo = 'Causa FAP Aprobadas de los últimos 15 años.';
    $pdf->AddPage();
    foreach ($resultado['datos'] as $value) {
        $anio = $value['anio'];
        $cantidad = $value['cantidad'];

        $pdf->SetFont('dejavusans', '', 7);
        $pdf->Ln(1);
        $pdf->MultiCell(0, 5, $anio, 0, 'L', false, 0, $x_inicio, '');
        $pdf->MultiCell(0, 5, $cantidad, 0, 'L', false, 1, $x_inicio + 30, '');

        $y_line = $pdf->GetY();
        $pdf->Line($x_inicio, $y_line, $x_fin, $y_line, array('width' => 0.5));
    }
    $pdf->lastPage();
    ob_clean();
    $pdf->Output('Presentismo.pdf', 'I');        
} else {
    echo 'ERROR-><br>';
}

