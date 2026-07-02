<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
require_once ('../../dataAccess/colegiadoCertificadosLogic.php');
require_once ('../../dataAccess/colegiadoContactoLogic.php');
require_once ('../../dataAccess/colegiadoMovimientoLogic.php');
require_once ('../../dataAccess/colegiadoSancionLogic.php');
require_once ('../../dataAccess/colegiadoCargoLogic.php');
require_once ('../../dataAccess/colegiadoContactoLogic.php');
require_once ('../../dataAccess/colegiadoDomicilioLogic.php');
require_once ('../../dataAccess/colegiadoFapLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../../dataAccess/presidenteLogic.php');
require_once ('../../dataAccess/notaCambioDistritoLogic.php');
require_once ('../../dataAccess/tipoCertificadoLogic.php');
require_once ('../../dataAccess/colegiadoArchivoLogic.php');

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
            /*
                // Logo
                $image_file = '../../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

                //MARCA DE AGUA 
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);

                $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
        
             * 
             */
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                //$this->SetY(-15);
                // Set font
                //$this->SetFont('helvetica', 'I', 8);

                //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

}
?>
<?php
$continua = TRUE;
if (isset($_GET['idColegiado']) && isset($_GET['idMesaEntrada'])) {
    $idColegiado = $_GET['idColegiado'];
    $idMesaEntrada = $_GET['idMesaEntrada'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellido = $colegiado['apellido'];
        $nombre = $colegiado['nombre'];
        $estadoSolicitado = $colegiado['detalleMovimiento'];
    } else {
        $continua = FALSE;
        $resultado['mensaje'] = $resColegiado['mensaje'];
    }                
} else {
    $continua = FALSE;
}
if ($continua){
    //armo el html con el certificado
    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->AddPage();

    //imprimo la planilla
    $image_file = '../../public/images/logo_colmed1_hr.png';

    $pdf->Image($image_file, 35, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->MultiCell(0, 10, 'HOJA DE RUTA', 0, 'L', false, 1, '120', '');
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->MultiCell(0, 7, 'MESA ENTRADA Nº '.$idMesaEntrada, 0, 'L', false, 1, '120', '');
    $pdf->MultiCell(0, 7, 'REUNIÓN MESA Nº ', 0, 'L', false, 1, '120', '');
    $pdf->RoundedRect(168, 21, 24, 7, 3.50, '', '', array('width' => 0.50));
    $pdf->MultiCell(0, 7, 'Fecha, '.date('d/m/Y'), 0, 'L', false, 1, '120', '');
    $pdf->Ln(5);
    //ARMAMOS EL HTML
    $pdf->SetFont('dejavusans', '', 10);
    $html = '<table width="100%">
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="180px"><b>Matrícula:</b></td>
                    <td width="250px">'.$colegiado['matricula'].'</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="180px"><b>Apellido y Nombre:</b></td>
                    <td width="250px">'.trim($colegiado['apellido']).' '.$colegiado['nombre'].'</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="180px"><b>Movimiento Solicitado:</b></td>
                    <td width="250px">'.$estadoSolicitado.'</td>
                </tr>
            </table>
            ';
        

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Ln(6);
        //$pdf->Line(35, 60, 192, 60, array('width' => 1));
        $pdf->MultiCell(120, 7, 'Descisión de la Mesa Directiva', 0, 'C', false, 0, '35', '');
        $pdf->MultiCell(25, 7, 'Firma', 0, 'C', false, 1, '166', '');
        //$pdf->Line(35, 67, 192, 67, array('width' => 1));
        
        $pdf->RoundedRect(35, 60, 130, 185, 3.50, '', '',  array('width' => 1));
        $pdf->RoundedRect(165, 60, 25, 185, 3.50, '', '', array('width' => 1));
        $pdf->RoundedRect(35, 60, 155, 5, 3.50, '', '', array('width' => 1));
        
        $pdf->Line(35, 250, 192, 250, array('width' => 1));
        //$pdf->RoundedRect(35, 60, 130, 5, 3.50, '', '');
        //$pdf->RoundedRect(166, 60, 25, 5, 3.50, '', '');
        //$pdf->RoundedRect(35, 66, 130, 150, 3.50, '', '');
        //$pdf->RoundedRect(166, 66, 25, 150, 3.50, '', '');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Ln(185);
        $pdf->MultiCell(50, 7, 'Realizó: '.$_SESSION['user'], 0, 'L', false, 0, '35', '');
        $pdf->MultiCell(80, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'L', false, 0, '140', '');
        $pdf->lastPage();
        
        ob_clean();
        /* Finalmente generamos el PDF */
        $destination = 'I';
        $nombreArchivo = 'Legajo_.pdf';
        $pdf->Output($nombreArchivo, $destination);        
}
