<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reunionConsejoLogic.php');

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
        public $el_titulo;

        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 12);
                // Title
                //$this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                $this->Ln(15);        
                //$this->MultiCell(0, 5, $this->el_titulo, 0, 'C', false, 1, '', '');
                $this->Cell(0, 10, $this->el_titulo, 0, false, 'C', 0, '', 0, false, 'M', 'M');
                $this->Ln(5);
                $this->SetFont('helvetica', 'B', 9);
                $this->MultiCell(0, 5, 'Matrícula', 0, 'L', false, 0, '', '');
                $this->MultiCell(0, 5, 'Apellido y Nombres', 0, 'L', false, 0, '20', '');
                $this->MultiCell(65, 5, 'F e c h a s   de   R e u n i o n e s', 0, 'C', false, 0, '100', '');
                $this->MultiCell(20, 5, 'Cantidad', 0, 'C', false, 1, '165', '');
                $y_line = $this->GetY();
                $this->Line(0, $y_line, 220, $y_line, array('width' => 0.5));
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

if (isset($_POST['cantidadReuniones']) && isset($_POST['fechaDesde']) && isset($_POST['fechaHasta'])) {
    //$listaConsejeros = $_POST['listaConsejeros'];
    //$listaConsejeros = json_decode($listaConsejeros);
    $cantidadReuniones = $_POST['cantidadReuniones'];
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];

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
    //define ('PDF_MARGIN_FOOTER', 8);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    //inicializar variables de header
    $pdf->el_titulo = 'Presentismo por Reunión de Consejo entre '.cambiarFechaFormatoParaMostrar($fechaDesde).' y '.cambiarFechaFormatoParaMostrar($fechaHasta).' - Cantidad de reuniones: '.$cantidadReuniones;

    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 9);

    $reunionConsejoLogic = new reunionConsejoLogic();
    $resConsejeros = $reunionConsejoLogic->obtenerConsejerosPresentismo($fechaDesde, $fechaHasta);
    if ($resConsejeros['estado']) {
        foreach ($resConsejeros['datos'] as $fila) {
            $idColegiadoCargo = $fila['idColegiadoCargo'];
            $matricula = $fila['matricula'];
            $apellidoNombre = trim($fila["apellido"]).' '.trim($fila['nombre']);
            
            $pdf->Ln(1);
            $pdf->MultiCell(0, 6, $matricula, 0, 'L', false, 0, '', '');
            $pdf->MultiCell(80, 6, $apellidoNombre, 0, 'L', false, 0, '20', '');

            $reuniones = explode(',', $fila['reuniones']);
            $cantidadAsistencias = 0;
            $fecha_asiste_detalle = "";
            $y = 100;
            foreach ($reuniones as $reunion) {
                $reunion_array = explode('_', $reunion);
                $fecha = $reunion_array[0];
                $asiste = $reunion_array[1];
                if ($asiste == 'S') {
                    $cantidadAsistencias += 1;
                    $fecha_asiste = cambiarFechaFormatoParaMostrar($fecha);
                } else {
                    $fecha_asiste = "          ";
                }
                $pdf->MultiCell(0, 6, $fecha_asiste, 0, 'L', false, 0, $y, '');
                $y += 20;
            }
            $pdf->MultiCell(20, 6, $cantidadAsistencias, 0, 'C', false, 1, '165', '');
            $y_line = $pdf->GetY();        
            $pdf->Line(0, $y_line, 220, $y_line, array('width' => 0));
            $orden += 1;
        }
        $pdf->lastPage();

        /*
        $destination = 'Presentismo.pdf';
        if (!preg_match('/\.pdf$/', $path_to_store_pdf))
        {
               $path_to_store_pdf .= '.pdf';
        }
        */
        ob_clean();
        $pdf->Output('Presentismo.pdf', 'I');        
    } else {

    }
} else {

}

