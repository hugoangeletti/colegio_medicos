<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
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

$continua = TRUE;
if (isset($_GET['idPP'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idPlanPago = $_GET['idPP'];
    $resPlanPago = $colegiadoPlanPagoLogic->obtenerPlanPagoPorId($idPlanPago);
    if ($resPlanPago['estado']) {
        $planPago = $resPlanPago['datos'];
        $idColegiado = $planPago['idColegiado'];
        $totalPlanPagos = $planPago['importeTotal'];
        $cuotas = $planPago['cuotas'];
        
        //armo la leyenda de las cuotas de colegiacion i otros planes de pagos
        $leyendaDeuda = "";
        $totalDeuda = 0;
        $totalDeudaActualizada = 0;
        $resDeuda = $colegiadoPlanPagoLogic->obtenerCuotasEnPlanPagos($idPlanPago);
        if ($resDeuda['estado']) {
            foreach ($resDeuda['datos'] as $row) {
                $leyendaDeuda .= $row.'<br>';
            }
        }
        
        //busco los datos del colegiado
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $apellidoNombre = $colegiado['apellido'].', '.$colegiado['nombre'];
        } else {
            $resultado['mensaje'] = $resColegiado['mensaje'];
            $continua = FALSE;
        }
    } else {
        $resultado['mensaje'] = $resPlanPago['mensaje'];
        $continua = FALSE;
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
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->AddPage();

    $alturaLinea = 5;
    //imprimo la planilla
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Ln(5);
    $pdf->MultiCell(0, $alturaLinea, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 0, '50', '');
    $pdf->Ln(5);
    $pdf->MultiCell(0, $alturaLinea, 'Señor', 0, 'L', false, 1, '', '', true);
    $pdf->MultiCell(0, $alturaLinea, 'Presidente del', 0, 'L', false, 1, '', '', true);
    $pdf->MultiCell(0, $alturaLinea, 'Colegio de Médicos DIstrito I', 0, 'L', false, 1, '', '', true);
    $pdf->MultiCell(0, $alturaLinea, 'Dr. Jorge A. MAZZONE.-', 0, 'L', false, 1, '', '', true);
    $pdf->MultiCell(0, $alturaLinea, 'S/Despacho.-', 0, 'L', false, 1, '', '', true);
    $pdf->Ln(5);
        
    $html = "Tengo el agrado de dirigirme a Usted, a fin de solicitarle tenga a bien concederme la "
            . "posibilidad de hacer efectivo el pago de mi deuda correspondiente a los siguientes "
            . "Períodos de Colegiación: <br>"
            . $leyendaDeuda."<br>"
            . "que asciende a la suma de <b>$".number_format($totalPlanPagos, 0, ',', '.')."</b>, en <b>".$cuotas."</b> cuotas.-<br><br>"
            . "Asimismo dejo contancia que conozco y acepto los términos y condiciones de la "
            . "Resolución de Consejo Directivo Nº 047/05, como también las posibles consecuencias,"
            . "ante el incumplimiento de las condiciones de pago.<br><br>"
            . "Sin otro particular, aprovecho la oportunidad para saludarlo muy atentamente.-";
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Ln(10);
    $pdf->MultiCell(0, $alturaLinea, 'Firma: ___________________________________', 0, 'L', false, 1, '40', '', true);
    $pdf->Ln(5);
    $pdf->MultiCell(0, $alturaLinea, 'Aclaración: ', 0, 'L', false, 0, '40', '', true);
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->MultiCell(0, $alturaLinea, $colegiado['apellido'].' '.$colegiado['nombre'], 0, 'L', false, 1, '65', '', true);
    $pdf->Ln(5);
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->MultiCell(0, $alturaLinea, 'Matrícula: ', 0, 'L', false, 0, '40', '', true);
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->MultiCell(0, $alturaLinea, $matricula, 0, 'L', false, 1, '65', '', true);
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Ln(5);
    $pdf->MultiCell(0, $alturaLinea, 'Domicilio: _________________________________', 0, 'L', false, 1, '40', '', true);
    $pdf->Ln(5);
    $pdf->MultiCell(0, $alturaLinea, 'Teléfono: _________________________________', 0, 'L', false, 1, '40', '', true);
    $pdf->Ln(10);
    $html = "<b>Art.9 - Resol. 047/05</b>: Para gozar de los beneficios previstos en los art. 5 inc. 4) y 34 del 
        Decreto Ley 5413/58, el matriculado no debe registrar plan de pago pendiente, aún cuando el mismo 
        se encontrase al dia con sus pagos. Para gozar de todos los beneficios señalados (incluyendo ser 
        electo o elector, obtener autorización para el Título de Especialista, ser defendido por problemas
        de responsabilidad profesional) y/o de cualquier otro que se creare, el interesado deberá indefectiblemente
        satisfacer la totalidad de su deuda en forma previa al momento de su necesidad o solicitud.";
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);

    $pdf->Ln(20);
    $pdf->SetFont('dejavusans', '', 10);
    $html = "Se deja constancia de haber entregado la chequera correspondiente al Plan de Pagos Nº ".$idPlanPago;
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    $pdf->Ln(5);
    $html = "Firma del empleado: ________________________";
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    $pdf->SetFont('dejavusans', '', 12);
    $tipoPdf = 'I';
    $destination = $tipoPdf; //'F';
    if (!preg_match('/\.pdf$/', $path_to_store_pdf))
    {
        $path_to_store_pdf .= '.pdf';
    }
    ob_clean();

    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF;
    $nombreArchivo = 'PlanPagos_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
    /*
    $periodoActual = $_SESSION['periodoActual'];

    $estructura = "../../archivos/planpago/".$periodoActual;
    if (!file_exists($estructura)) {
        mkdir($estructura, 0777, true);
    }
    if (file_exists("../../archivos/planpago/".$periodoActual."/".$nombreArchivo)) {
        unlink("../../archivos/planpago/".$periodoActual."/".$nombreArchivo);
    } 

    if ($tipoPdf == 'F') {
        $pdf->Output($camino.'/archivos/planpago/'.$periodoActual.'/'.$nombreArchivo, $destination);        
        $envioMail = TRUE;
    } else {
        $pdf->Output($nombreArchivo, $destination);        
        $envioMail = FALSE;
    }
     * *>
     */
    $pdf->Output($nombreArchivo, $destination);        
} else {
?>
    <div class="row">
        <div class="col-md-12 alert alert-danger">
            <h3><?php echo $resultado['mensaje']; ?></h3>
        </div>
        <div class="row">&nbsp;</div>
        <div class="col-md-12">
            <h3>Cerrar esta pestaña del navegador, Hubo un error en la emisión de la Notificación.</h3>
        </div>
    </div>
<?php
}
