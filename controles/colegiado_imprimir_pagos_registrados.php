<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoPagoLogic.php');
$colegiadoPagoLogic = new colegiadoPagoLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
//                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//                 // Set font
                $this->SetFont('helvetica', 'B', 20);
//                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');
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
$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetPrintHeader(true);
$pdf->SetPrintFooter(true);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//$pdf->SetMargins(0, 0, 0);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
//$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('dejavusans', '', 8);
$pdf->AddPage();

$html='';

if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    $tipoPdf = $_POST['tipoPdf'];
    $mailDestino = $_POST['mail'];
    $periodoActual = $_SESSION['periodoActual'];
    //$mailDestino = 'sistemas@colmed1.org.ar'; //para las pruebas, sacar en produccion
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        
        //obtengo el estado con tesoreria
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
        
        //verifico si tiene especialidades con caducidad menores hacia un año adelante
        $resEspecialidad = $colegiadoEspecialistaLogic->especialidadesConCaducidad($idColegiado);
        if ($resEspecialidad['estado']) {
            $especialidades = "";
            foreach ($resEspecialidad['datos'] as $dato) {
                $especialidades .= $dato['especialidad'].' '.$dato['caducidad'];
            }
        }
        
        //obtengo el domicilio y localidad
        $domicilioCompleto = "";
        $localidad = "";
        $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
        if ($resDomicilio['estado']) {
            $domicilio = $resDomicilio['datos'];
            if ($domicilio['calle']) {
                $domicilioCompleto = $domicilio['calle'];
                if ($domicilio['numero']) {
                    $domicilioCompleto .= " Nº ".$domicilio['numero'];
                }
                if ($domicilio['lateral']) {
                    $domicilioCompleto .= " e/ ".$domicilio['lateral'];
                }
                if ($domicilio['piso'] && strtoupper($domicilio['piso']) != "NR") {
                    $domicilioCompleto .= " Piso ".$domicilio['piso'];
                }
                if ($domicilio['depto'] && strtoupper($domicilio['depto']) != "NR") {
                    $domicilioCompleto .= " Dto. ".$domicilio['depto'];
                }
            }
            if ($domicilio['nombreLocalidad']) {
                $localidad = $domicilio['nombreLocalidad'].' - ('.$domicilio['codigoPostal'].')';
            }
        }
        
        $fechaHasta = date('Y-m-d');
        $fechaDesde = strtotime ( '-3 year' , strtotime ( $fechaHasta ) ) ;
        $fechaDesde = date ( 'Y-m-d' , $fechaDesde );            
        //$resPagos = $colegiadoPagoLogic->obtenerPagosPorIdColegiado($idColegiado);
        $resPagos = $colegiadoPagoLogic->obtenerPagosColegiacionPorIdColegiado($idColegiado, $fechaDesde, $fechaHasta);
        if ($resPagos['estado']) {
            
            $html = '<label align="right">La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'</label><br>';
            $html .= '<b>Apellido y Nombres: </b>'.$colegiado['apellido'].", ".$colegiado['nombre'].'<br>';
            $html .= '<b>Matrícula: </b>'.$matricula.'<br>';
            $html .= '<b>Estado matricular:</b> '.$colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']).$colegiado['movimientoCompleto'].'<br>';
            $html .= '<b>Estado con Tesorería:</b> '.$estadoTesoreria.' - <b>Período actual: </b>'.$periodoActual.'<br>';
            $html .= '<br><hr><br>';
            $html .= '<h4>Pagos registrados de cuotas de Colegiación entre el '.cambiarFechaFormatoParaMostrar($fechaDesde).' y el '.cambiarFechaFormatoParaMostrar($fechaHasta).'</h4>';
            
            $html .= '<table border="1" cellspacing="0" cellpadding="4">';
            $html .= '<tr>
                        <td>Periodo</td>
                        <td>Cuota</td>
                        <td>Fecha de Pago</td>
                        <td>Importe abonado</td>
                        <td>Recibo</td>
                        <td>Lugar de pago</td>
                    </tr>';
            foreach ($resPagos['datos'] as $dato){
                $periodo = $dato['periodo'];
                $cuota = $dato['cuota'];
                $importeAbonado = $dato['importe'];
                $fechaPago = $dato['fechaPago'];
                $recibo = $dato['recibo'];
                $lugarPago = $dato['lugarPago'];
                $tipoPago = $dato['tipoPago'];
                $idTipoPago = $dato['idTipoPago'];
                
                $html .= '<tr>
                            <td>'.$periodo.'</td>
                            <td>'.$cuota.'</td>
                            <td>'.cambiarFechaFormatoParaMostrar($fechaPago).'</td>
                            <td>'.$importeAbonado.'</td>
                            <td>'.$recibo.'</td>
                            <td>'.$lugarPago.'</td>
                        </tr>';
            }
            $html .= "</table>";

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->lastPage();

            $destination = $tipoPdf; //'F';
            if (!preg_match('/\.pdf$/', $path_to_store_pdf))
            {
                   $path_to_store_pdf .= '.pdf';
            }
            ob_clean();
//            if ($destination == 'D')
//            {
//                   echo $this->view->pdf->Output($path_to_store_pdf, $destination);
//                   exit();
//            } 
            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF;
            $nombreArchivo = 'Colegiacion'.$periodoActual.'_Matricula_'.$matricula.'.pdf';

            $estructura = "../archivos/cuotas/".$periodoActual;
            if (!file_exists($estructura)) {
                mkdir($estructura, 0777, true);
            }
            if (file_exists("../archivos/cuotas/".$periodoActual."/".$nombreArchivo)) {
                unlink("../archivos/cuotas/".$periodoActual."/".$nombreArchivo);
            } 
    
            if ($tipoPdf == 'F') {
                $pdf->Output($camino.'/archivos/cuotas/'.$periodoActual.'/'.$nombreArchivo, $destination);        
                $envioMail = TRUE;
            } else {
                $pdf->Output($nombreArchivo, $destination);        
                $envioMail = FALSE;
            }
            
        } else {
            echo "<span><strong>".$resPagos['mensaje']."</strong></span>";
            $envioMail = FALSE;
        }
        
        if ($envioMail) {
            //enviamos el pdf por mail si tiene contacto
            $destinatario = $colegiado['apellido'].', '.$colegiado['nombre'];
            require_once '../PHPMailer/class.phpmailer.php';
            require_once '../PHPMailer/class.smtp.php';
            
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Host = "mail.colmed1.org.ar";
        $mail->Port = 465;
        //$mail->Username = "sistemas@colmed1.org.ar";
        //$mail->Password = "@sistemas1";
        $mail->Username = MAIL_MASIVO;
        $mail->Password = MAIL_MASIVO_PASS;

        $mail->From = "noreply@colmed1.org.ar";
        $mail->FromName = "Colegio de Medicos. Distrito I";
            $mail->Subject = "Pagos registrados ";
            $mail->AltBody = "";
            $mail->MsgHTML("Pagos registrados de Cuotas de Colegiacion.");
            $mail->AddAttachment("../archivos/cuotas/".$periodoActual."/".$nombreArchivo);
            $mail->AddAddress($mailDestino, $destinatario);
            $mail->IsHTML(true);
            //echo $mailDestino .' - '. $matricula .' - '. $destinatario;
            if($mail->Send()) {
                $mailEnviado = TRUE;
            }else{
                $mailEnviado = FALSE;
            }

        }
    }
    ?>
<!--    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success" role="alert">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                <span><strong>Se gener&oacute; el archivo.</strong></span>
            </div>        
        </div>
    </div>-->
    <?php
    if ($envioMail) {
        if ($mailEnviado) {
            require_once ('../html/head.php');
            require_once ('../html/encabezado.php');
        ?>
            <div class="col-md-12">
                <div class="row" style="background-color: #428bca;">
                    <div class="col-md-12"></div>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <h3>Pagos registrados solicitados por <?php echo $colegiado['nombre'].' '.$colegiado['apellido']; ?>, de cuotas del período <?php echo $periodoActual; ?></h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success" role="alert">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                        <span><strong>&nbsp;El mail se envió con éxito al correo: </strong><?php echo $mailDestino; ?></span>
                    </div>        
                </div>
            </div>
        <?php
        } else {
        ?>    
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger" role="alert">
                        <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
                        <span><strong>ERROR al enviar el mail al correo: </strong><?php echo $mailDestino; ?><strong>. Vuelva a intentar más tarde.</strong></span>
                    </div>        
                </div>
            </div>
        <?php
        }
        ?>    
        <div class="row">
            <div class="col-md-12 text-center">
                Cierre esta pestaña del navegador.
            </div>
        </div>
        <?php
    }
} else {
    ?>
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
            <span><strong>ERROR AL INGRESAR</strong></span>
        </div>        
    <?php
}

require_once '../html/footer.php';
