<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
//require_once ('../html/head.php');
//require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

ini_set('memory_limit', '-1');
set_time_limit(0);
error_reporting(0);
error_reporting(E_ERROR | E_PARSE);

class MYPDF extends TCPDF 
{
    //Page header
    public function Header() 
    {
        // Logo
        //$image_file = '../public/images/derecha.png';
        //$this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
         // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

        //MARCA DE AGUA 
        $bMargin = $this->getBreakMargin();
        $auto_page_break = $this->AutoPageBreak;
        $this->SetAutoPageBreak(false, 0);

        //$image_file2 = '../public/images/derecha.png';
        //$this->Image($img_file2, 10, 10, 280, 190, '', '', 'C', false, 300, '',     false, false, 0);
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        $this->setPageMark();
        //FIN MARCA DE AGUA 
        
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        //$this->SetY(-10);
        $this->SetY(-15);
        // Set font
        $this->SetFont('dejavusans', '', 8);

        $this->MultiCell(180, 0, 'Dirección de Informática - DPPSV', 1, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
    }
}
 
$continua = TRUE;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSapCaratula = $_GET['id'];
    $resAdjuntos = obtenerCaratulaAdjuntosPorIdCaratula($idSapCaratula);
    if ($resAdjuntos['estado']) {
        $cantidadAdjuntos = sizeof($resAdjuntos['datos']);
        if ($cantidadAdjuntos > 0) {
            //genero el PDF
            $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetPrintHeader(true);
            $pdf->SetPrintFooter(true);

            // set default header data
            //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

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

            // set font
            $pdf->SetFont('dejavusans', '', 10);
            foreach ($resAdjuntos['datos'] as $fila) {
                $idSapCaratulaArchivo = $fila['idSapCaratulaArchivo'];
                $path = $fila['path'];
                $nombreArchivo = $fila['nombreArchivo'];
                $extensionAdjunto = $fila['extensionAdjunto'];
                if (isset($path) && isset($nombreArchivo) && isset($extensionAdjunto)) {
                    $i += 1;
                    // add a page
                    $pdf->AddPage();
                    $imagen2Pdf = $path."/". $nombreArchivo;
                    $pdf->Image($imagen2Pdf, 10, 10, 600, 800, $extensionAdjunto);
                }
            }
            ob_clean();
            $camino = PATH_PDF.$path;
            $pdf->Output($camino.$filename, 'I');
        } else { 
            echo $resAdjuntos['mensaje'];
        }
    } else {
        echo $resAdjuntos['mensaje'];
    }
} else {
?>
    <div class="row">
        <div class="col-md-12" >
            <div class="alert alert-danger" role="alert">
                ACCESO INCORRECTO<br>
                <a href="fap_listado.php" class="btn btn-primary">Salir</a>
           </div> 
        </div>
    </div>
<?php
}

require_once '../html/footer.php';
