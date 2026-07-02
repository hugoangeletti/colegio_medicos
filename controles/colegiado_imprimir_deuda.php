<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoDeudaAnualLogic.php';
require_once '../dataAccess/colegiadoPlanPagoLogic.php';
require_once '../dataAccess/notificacionDeudaLogic.php';

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/headerNota200.png';
                $this->Image($image_file, 10, 5, 160, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
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
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('dejavusans', '', 8);
$pdf->AddPage();

$html='';
if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $tipoPdf = $_POST['tipoPdf'];
    $mailDestino = $_POST['mail'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];

        $html = '<label align="right">La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'</label><br>';
        $html .= '<b>Apellido y Nombres: </b>'.$colegiado['apellido'].", ".$colegiado['nombre'].'<br>';
        $html .= '<b>Matrícula: </b>'.$matricula.'<br>';
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
        
        $html .= '<b>Estado matricular:</b> '.$colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']).$colegiado['movimientoCompleto'].'<br>';
        $html .= '<b>Estado con Tesorería:</b> '.$estadoTesoreria.'<br><br><br>';
        
        $fecha = date('Y-m-d');
        $fechaVencimiento = strtotime ( '+7 day' , strtotime ( $fecha ) ) ;
        $fechaVencimiento = date ( 'Y-m-d' , $fechaVencimiento );
        
        //verifico si hay alguna notificacion dentro del vencimiento, en ese caso se reimprime la vigente
        $continua = TRUE;
        $resDeuda = $notificacionDeudaLogic->obtenerIdNotificacionVigente($idColegiado);
        if ($resDeuda['estado']) {
            if ($resDeuda['idNotificacion'] == 0) {
                $resDeuda = $notificacionDeudaLogic->generarNotificacionDeudores(NULL, 1, $fechaVencimiento, $matricula, $fecha, 0, $_SESSION['periodoActual'], 'T', 0);
                if (!$resDeuda['estado']) {
                    $continua = FALSE;
                }
            }
        } else {
            $continua = FALSE;
        }
        if ($continua) {
            $idNotificacion = $resDeuda['idNotificacion'];
            $html .= '<label align="right"><b>Notificación: </b>'.$idNotificacion.'<br>';
            $resNotificacion = $notificacionDeudaLogic->obtenerNotificacionDeudaPorId($idNotificacion);
            if ($resNotificacion['estado']) {
                
                $totalDeuda = 0;
                $totalDeudaPP = 0;
                $html .= '<table>';
                
                $origenAnterior = '';
                foreach ($resNotificacion['datos'] as $dato){
                    if ($origenAnterior <> $dato['origen']) {
                        $html .= '<tr><th colspan="6"><hr></th></tr>';
                        if ($dato['origen'] == 'C') {
                            $html .= '<tr>
                                        <th colspan="6"><h3><b>Cuotas de Colegiación</b></h3></th>
                                    </tr>
                                    <tr>
                                        <th align="center"><b>Per&iacute;odo</b></th>
                                        <th align="center"><b>Cuota</b></th>
                                        <th align="center"><b>Importe</b></th>
                                        <th align="center"><b>Vencimiento</b></th>
                                        <th align="center"><b>Recibo</b></th>
                                        <th align="center"><b>Importe Actualizado</b></th>
                                    </tr>';
                        } else {
                            $html .= '<tr>
                                        <th colspan="6"><h3><b>Cuotas de Plan de Pagos de Colegiación</b></h3></th>
                                    </tr>
                                    <tr>
                                        <th align="center"><b>Plan de Pagos</b></th>
                                        <th align="center"><b>Cuota</b></th>
                                        <th align="center"><b>Importe</b></th>
                                        <th align="center"><b>Vencimiento</b></th>
                                        <th align="center"><b>Recibo</b></th>
                                        <th align="center"><b>Importe Actualizado</b></th>
                                    </tr>';
                        }
                        $origenAnterior = $dato['origen'];
                    }
                    $importeActualizado = $dato['valorActualizado'];
                    $idNotificacionColegiado = $dato['idNotificacionColegiado'];
                    if ($dato['origen'] == 'C') {
                        $recibo = $dato['idColegiadoDeudaAnualCuota'];
                        $periodo = $dato['periodo'];
                        $importe = $dato['importe'];
                        $vencimiento = $dato['vencimiento'];
                        $cuota = $dato['cuota'];

                        $totalDeuda += $importeActualizado;
                    } else {
                        $recibo = $dato['idPlanPagosCuota'];
                        $periodo = $dato['idPlanPagos'];
                        $importe = $dato['importePlanPagos'];
                        $vencimiento = $dato['vencimientoPlanPagos'];
                        $cuota = $dato['cuotaPlanPagos'];

                        $totalDeudaPP += $importeActualizado;
                    }
                    $html .= '<tr>
                                <td align="center">'.$periodo.'</td>
                                <td align="center">'.rellenarCeros($cuota, 2).'</td>
                                <td align="center">'.number_format($importe, 2, ',', '.').'</td>
                                <td align="center">'.cambiarFechaFormatoParaMostrar($vencimiento).'</td>
                                <td align="center">'.$recibo.'</td>
                                <td align="center">'.number_format($importeActualizado, 2, ',', '.').'</td>
                            </tr>';

                }
                $html .= "<tr><td colspan='7'></td></tr>";
                $html .= "</table>";
                //$html .= "<br><hr><br>";
                //imprimo el total de la deuda
                if ($totalDeuda+$totalDeudaPP > 0) {
                    $html .= '<p>
                        <table width="100%" border="1" cellspacing="0" cellpadding="4">
                            <tr>
                            <td width="35%"><h3>Total deuda de colegiación:</h3></td>
                            <td width="25%" style="text-align: right"><h3><b>$'.number_format($totalDeuda, 2, ',', '.').'</b></h3></td>
                            </tr>';
                    if ($totalDeudaPP > 0) {
                        $html .= '<tr>
                                <td width="35%"><h3>Total deuda de Plan de Pagos:</h3></td>
                                <td width="25%" style="text-align: right"><h3><b>$'.number_format($totalDeudaPP, 2, ',', '.').'</b></h3></td>
                                </tr>
                                <tr>
                                <td width="35%"><h3>Total Deuda:</h3></td>
                                <td width="25%" style="text-align: right"><h3><b>$'.number_format($totalDeudaPP+$totalDeuda, 2, ',', '.').'</b></h3></td>
                                </tr>';
                    }

                    $importeTotal = $totalDeuda + $totalDeudaPP;
                    $html .= '<tr>
                                <td width="35%"><h3>Fecha de Vencimiento:</h3></td>
                                <td width="25%" align="center"><h3>'.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</h3></td>
                            </tr>';

                    $cuenta = '9'.rellenarCeros($idNotificacionColegiado, 6);
                    $pagarEn = "Lugar de Pago: RAPIPAGOS - Pago Facil - ProvinciaNET";
                    //if ($importeTotal >= 10000) {
                        $codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra44($cuenta, $importeTotal, $importeTotal, $fechaVencimiento, $fechaVencimiento, NULL);
                        //$pagarEn = "Lugar de Pago: RAPIPAGOS - Pago Facil";
                    //} else {
                        //$codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra($cuenta, $importeTotal, $importeTotal, $fechaVencimiento, $fechaVencimiento, NULL);
                        //$pagarEn = "Lugar de Pago: RAPIPAGOS - Pago Facil - ProvinciaNET";
                    //}
                    $params = $pdf->serializeTCPDFtagParameters(array($codigoBarra, 'I25', '', '', '', 18, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));

                    $html .= '<tr style="text-align: center;"><td colspan="3"><tcpdf method="write1DBarcode" params="'.$params.'" /></td></tr>';
                    $html .= '<tr style="text-align: center;"><td colspan="3">'.$pagarEn.'</td></tr>';
                    $html .= '</table></p>';
                } else {
                    $html .= '<h4>No hay cuotas pendiente de cobro</h4>';
                }
            } else {
                $html .= $resNotificacion['estado'].' - '.$resNotificacion['mensaje'];
            }
        } else {
            $html .= $resDeuda['estado'].' - '.$resDeuda['mensaje'];
        }
    } else {
        $html .= "<span><strong>".$resColegiado['mensaje']."</strong></span>";
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->lastPage();

    /*
    $destination='NotaDeudaMatricula_'.$matricula.'.pdf';
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

    $pdf->Output('NotaDeudaMatricula_'.$matricula.'.pdf', 'I');
    */
    $destination = $tipoPdf; //'F';
    if (!preg_match('/\.pdf$/', $path_to_store_pdf))
    {
           $path_to_store_pdf .= '.pdf';
    }
    ob_clean();
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF;
    $nombreArchivo = 'NotaDeuda_Matricula_'.$matricula.'.pdf';

    $estructura = "../archivos/NotaDeuda/".$periodoActual;
    if (!file_exists($estructura)) {
        mkdir($estructura, 0777, true);
    }
    if (file_exists("../archivos/NotaDeuda/".$periodoActual."/".$nombreArchivo)) {
        unlink("../archivos/NotaDeuda/".$periodoActual."/".$nombreArchivo);
    } 

    if ($tipoPdf == 'F') {
        $pdf->Output($camino.'/archivos/NotaDeuda/'.$periodoActual.'/'.$nombreArchivo, $destination);        
        $envioMail = TRUE;
    } else {
        $pdf->Output($nombreArchivo, $destination);        
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
        $mail->Subject = "Nota de Deuda - Tesoreria del Colegio de Medicos Distrito I";
        $mail->AltBody = "";
        $mail->MsgHTML("Le enviamos la Nota de Deuda de las cuotas de colegiacion del Colegio de Medicos Distrito I");
        $mail->AddAttachment("../archivos/NotaDeuda/".$periodoActual."/".$nombreArchivo);
        $mail->AddAddress($mailDestino, $destinatario);
        $mail->IsHTML(true);
        //echo $mailDestino .' - '. $matricula .' - '. $destinatario;
        if($mail->Send()) {
            $mailEnviado = TRUE;
        }else{
            $mailEnviado = FALSE;
        }
    }
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

