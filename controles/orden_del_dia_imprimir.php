<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
//require_once ('../html/head.php');
//require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/ordenDelDiaLogic.php');
$ordenDelDiaLogic = new ordenDelDiaLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
//require_once('../'.LIBRERIA_TCPDF);

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

                //$this->SetAutoPageBreak($auto_page_break, $bMargin);
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

                //$this->MultiCell(180, 0, 'Este certificado fue emitido en forma online desde el sistema del Colegio de Médicos Pcia.de Bs.As – Distrito I. Debe ser recibido por los organismos que lo requieran. Validez del certificado: 30 días a partir de la fecha de la firma. ', 1, 'L', 0, 0, '', '', true, 0, false, true, 40, 'T');
                //$this->Cell(180, 0, 'La fotocopia de éste certificado no tiene validez', 1, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

}

$continua = TRUE;

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php    
}   

if (isset($_GET['id']) && $_GET['id'] <> "" && isset($_GET['tipo']) && $_GET['tipo'] <> "") {
    $idOrdenDia = $_GET['id'];
    $tipoPlanilla = $_GET['tipo'];
    $resOrden = $ordenDelDiaLogic->obtenerOrdenDelDiaPorId($idOrdenDia);
    if ($resOrden['estado']) {
        $ordenDelDia = $resOrden['datos'];
        $fechaOrden = $ordenDelDia['fecha'];
        $numero = $ordenDelDia['numero'];
        $periodoOrden = $ordenDelDia['periodo'];
        $fechaDesde = $ordenDelDia['fechaDesde'];
        $fechaHasta = $ordenDelDia['fechaHasta'];
        $observaciones = $ordenDelDia['observaciones'];
        $estadoOrdenDia = $ordenDelDia['estado'];
        switch ($estadoOrdenDia) {
            case 'A':
                $estadoOrdenDiaDetalle = 'Abierto';
                break;
            
            case 'B':
                $estadoOrdenDiaDetalle = 'Borrado';
                break;
            
            case 'C':
                $estadoOrdenDiaDetalle = 'Cerrado';
                break;
            
            default:
                // code...
                break;
        }

        $resOrdenDetalle = $ordenDelDiaLogic->ordenDelDiaDetallePorIdOrdenDia($idOrdenDia, $tipoPlanilla);
        //var_dump($resOrdenDetalle);
        if ($resOrdenDetalle['estado']) {
            $ordenDelDiaTipo = $resOrdenDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resOrdenDetalle['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje = $resOrden['mensaje'];
    }
} else {
    $idOrdenDia = NULL;
    $continua = FALSE;
    $mensaje = "ACCESO ERRONEO";
}

if ($continua) {
    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(true);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(20);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    $pdf->AddPage();

    $diaSemana = date("w", strtotime($fechaOrden));

    //echo $fechaOrden.'->'.$diaSemana.'<br>'; exit;

    $html = '<h3>
            <table style="text-align: center;">
                <tr><td>REUNIÓN DE MESA DIRECTIVA - COLEGIO DE MÉDICOS</td></tr>
                <tr><td>DISTRITO I - Período '.$_SESSION['periodoActual'].'/'.($_SESSION['periodoActual'] + 1).' (Nº '.$numero.')</td></tr>
                <tr><td>'.NombreDeLaSemana($diaSemana).' '.substr($fechaOrden, 8, 2).' de '.obtenerMes(substr($fechaOrden, 5, 2)).' de '.substr($fechaOrden, 0, 4).'.-
                </td></tr>
            </table>
            </h3>';
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    switch ($tipoPlanilla) {
        case 1:
            $titulo = "<b>Planilla de Asuntos Internos.<b>";
            $altura = "height: 90px; ";
            $multiplo = 8;
            $leyendaDerecha = "";
            break;
        
        case 2:
            $titulo = "<b>Planilla de Notas Recibidas.</b>";
            $altura = "height: 90px; ";
            $multiplo = 8;
            $leyendaDerecha = "";
            break;
        
        case 3:
            $titulo = "<b>Archivado - Descarta el Trámite Definitivamente.</b>";
            break;
        
        case 4:
            $titulo = "<b>Planilla de movimientos matriculares.</b>";
            $altura = "height: 45px; ";
            $multiplo = 15;
            $leyendaDerecha = "VERIFICAR Y DAR CURSO.-";
            break;
        
        default:
            // code...
            break;
    }

    if (sizeof($ordenDelDiaTipo) > 0) {
        $html = '<h4>'.$titulo.'</h4>';
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);

        $pdf->SetFont('dejavusans', '', 9);
        $html = '<br>
            <table border="2" cellpadding="10">
            <tbody>';

        $i = 0;
        foreach ($ordenDelDiaTipo as $dato) {
            $idMesaEntrada = $dato['idMesaEntrada'];
            if (isset($dato['idOrdenDiaDetalle'])) {
                $idOrdenDiaDetalle = $dato['idOrdenDiaDetalle'];
            } else {
                $idOrdenDiaDetalle = NULL;
            }
            $fechaIngreso = cambiarFechaFormatoParaMostrar($dato['fechaIngreso']);
            $tipoTramite = $dato['nombreMovimiento'];
            if (isset($dato['tipoPlanilla'])) {
                $tipoPlanilla = $dato['tipoPlanilla'];
            } else {
                $tipoPlanilla = NULL;
            }
            $apellido = $dato['apellido'];
            $nombre = $dato['nombre'];
            $nombreRemitente = $dato['nombreRemitente'];
            $detalleCompleto = $dato['detalleCompleto'];
            $tema = $dato['tema'];
            $observaciones = $dato['observaciones'];
            $colegiadoRemitente = NULL;
            $temaObservaciones = NULL;
            $detalleLinea = NULL;
            if (isset($apellido) && $apellido <> "") {
                $detalleLinea .= trim($apellido).' '.trim($nombre).'. ';
            }
            if (isset($detalleCompleto) && $detalleCompleto <> "") {
                $detalleLinea .= '<br>'.' '.trim($detalleCompleto).'. ';
            } else {
                $detalleLinea .= '<br>'.$tipoTramite;
            }
            if (isset($nombreRemitente) && $nombreRemitente <> "") {
                $detalleLinea .= ' '.trim($nombreRemitente).'. ';
            }
            if (isset($tema) && $tema <> "") {
                $detalleLinea .= ' '.trim($tema).'. ';
            }
            if (isset($observaciones) && $observaciones <> "") {
                $detalleLinea .= ' '.trim($observaciones).'. ';
            }
            
            $i++;
            
            if($i >= $multiplo && $i%$multiplo == 0) {
                $html .= '</tbody>
                </table>';
                $pdf->writeHTMLCell(0, 0, 5, '', $html, 0, 1, 0, true, 'J', true);
                $pdf->Ln(5);
                $pdf->MultiCell(0, 1, 'Realizó: '.$_SESSION['user_entidad']['nombreUsuario'], 0, 'L', false, 1, '', '', true);                    
                //$pdf->Ln(0);
                $pdf->MultiCell(0, 1, 'Fecha Emisión: '.date('d-m-Y'), 0, 'L', false, 1, '', '', true);                    

                $pdf->AddPage();
                $html = '<h4>'.$titulo.'</h4>';
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);

                $pdf->SetFont('dejavusans', '', 10);
                $html = '<h4>'.$titulo.'</h4>';
                $pdf->SetFont('dejavusans', '', 9);
                $html = '<br>
                        <table border="2" cellpadding="10">
                        <tbody>';
            }


            
            $html .= '
            <tr>
                <td border="2" style="'.$altura.' width: 300px">'.$tipoPlanilla.'.'.$i.' ME: '.$idMesaEntrada.'.- '.$detalleLinea.'</td>
                <td border="2" style="'.$altura.' text-align: center; width: 400px">'.$leyendaDerecha.'</td>
            </tr>';
        }
        $html .= '</tbody>
        </table>';
        //echo $html; exit;
        $pdf->writeHTMLCell(0, 0, 5, '', $html, 0, 1, 0, true, 'J', true);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Ln(5);
        $pdf->MultiCell(0, 1, 'Realizó: '.$_SESSION['user_entidad']['nombreUsuario'], 0, 'L', false, 1, '', '', true);                    
        //$pdf->Ln(0);
        $pdf->MultiCell(0, 1, 'Fecha Emisión: '.date('d-m-Y'), 0, 'L', false, 1, '', '', true);                    

        ob_clean();

        $nombreArchivo = 'Orden_del_dia_'.$idOrdenDia.'.pdf';
        $pdf->Output($nombreArchivo, 'I');
    }
} else {
?>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>
<?php        
}
?>
<div class="row">&nbsp;</div>
<div class="row">

</div>
<?php
require_once '../html/footer.php';