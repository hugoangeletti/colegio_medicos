<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/colegiadoRecetariosLogic.php');
$colegiadoRecetariosLogic = new colegiadoRecetariosLogic();

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
                $img_file2 = '../../public/images/fondoCertificado.png';
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
if (isset($_GET['idReceta'])) {
    $idReceta = $_GET['idReceta'];
    $resRecetarios = $colegiadoRecetariosLogic->obtenerRecetariosPorId($idReceta);
    if ($resRecetarios['estado']) {
        $recetarios = $resRecetarios['datos'];
        
        $idColegiado = $_GET['idColegiado'];
        $entrega = $recetarios['entrega'];
        $fecha = $recetarios['fecha'];
        $serie = $recetarios['serie'];
        $desde = $recetarios['desde'];
        $hasta = $recetarios['hasta'];
        $cantidad = $recetarios['cantidad'];
        $idUsuario = $recetarios['idUsuario'];
        $nombreEspecialidad = $recetarios['nombreEspecialidad'];

        $periodoActual = $_SESSION['periodoActual'];
        //obtengo el estado actual con tesoreria
        $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, $periodoActual);
        if ($resEstadoTeso['estado']){
            $codigo = $resEstadoTeso['codigoDeudor'];
            $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
            if ($resEstadoTesoreria['estado']){
                $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
            } else {
                $estadoTesoreria = $resEstadoTesoreria['mensaje'];
            }
        } else {
            $estadoTesoreria = $resEstadoTeso['mensaje'];
        }

        //imprimo el recibo
        
        
    } else {
        $resultado['mensaje'] = $resRecetarios['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if ($continua){
    //armo el html con el certificado
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];

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
        $pdf->Ln(5);
        $pdf->MultiCell(0, $alturaLinea, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 0, '50', '');
        $pdf->Ln(15);

        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, 'ENTREGA DE RECETARIOS PARA PSICOTROPICOS Nº '.$idReceta, 0, 'C', false, 1, '', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(15);
        $pdf->MultiCell(0, $alturaLinea, 'Matrícula: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $matricula, 0, 'L', false, 1, '90', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(5);
        $pdf->MultiCell(0, $alturaLinea, 'Apellido y nombre: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $colegiado['apellido'].' '.$colegiado['nombre'], 0, 'L', false, 1, '90', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(5);
        $pdf->MultiCell(0, $alturaLinea, 'Especialidad: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $nombreEspecialidad, 0, 'L', false, 1, '90', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(5);
        $pdf->MultiCell(0, $alturaLinea, 'Serie: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $serie, 0, 'L', false, 1, '90', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(5);
        $pdf->MultiCell(0, $alturaLinea, 'Número Desde/Hasta: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $desde.' / '.$hasta, 0, 'L', false, 1, '90', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(5);
        $pdf->MultiCell(0, $alturaLinea, 'Cantidad: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $cantidad, 0, 'L', false, 1, '90', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(30);
        $pdf->MultiCell(0, $alturaLinea, 'Firma: _______________________________', 0, 'L', false, 1, '110', '', true);
        $pdf->Ln(10);
        $pdf->MultiCell(0, $alturaLinea, 'Sello: _______________________________', 0, 'L', false, 1, '110', '', true);
        $pdf->Ln(15);
        $pdf->MultiCell(0, $alturaLinea, 'Estado actual con Tesorería: ', 0, 'L', false, 0, '40', '', true);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->MultiCell(0, $alturaLinea, $estadoTesoreria, 0, 'L', false, 1, '103', '', true);
        $pdf->SetFont('dejavusans', '', 12);
        $pdf->Ln(15);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->MultiCell(0, $alturaLinea, 'ARCHIVESE.-', 0, 'L', false, 1, '', '', true);
        
        $tipoPdf = 'I';
        $destination = $tipoPdf; //'F';
        if (!preg_match('/\.pdf$/', $path_to_store_pdf))
        {
            $path_to_store_pdf .= '.pdf';
        }
        ob_clean();

        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF;
        $nombreArchivo = 'Certificado_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
        $periodoActual = $_SESSION['periodoActual'];

        $estructura = "../../archivos/recetarios/".$periodoActual;
        if (!file_exists($estructura)) {
            mkdir($estructura, 0777, true);
        }
        if (file_exists("../../archivos/recetarios/".$periodoActual."/".$nombreArchivo)) {
            unlink("../../archivos/recetarios/".$periodoActual."/".$nombreArchivo);
        } 

        if ($tipoPdf == 'F') {
            $pdf->Output($camino.'/archivos/certificados/'.$periodoActual.'/'.$nombreArchivo, $destination);        
            $envioMail = TRUE;
        } else {
            $pdf->Output($nombreArchivo, $destination);        
            $envioMail = FALSE;
        }

    } else {
    ?>
        <div id="pagina">
            <h2>Se produjo un error al buscar al colegiado</h2>
        </div>
    <?php
    }
} else {
?>
    <div class="row">
        <div class="col-md-12 alert alert-danger">
            <h3><?php echo $resultado['mensaje']; ?></h3>
        </div>
        <div class="row">&nbsp;</div>
        <div class="col-md-12">
            <h3>Cerrar esta pestaña del navegador, el mail fue enviado con éxito.</h3>
        </div>
    </div>
<?php
}
