<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 5, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
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
    $periodoActual = PERIODO_ACTUAL;
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $tipoPdf = $_POST['tipoPdf'];
        $mailDestino = $_POST['mail'];

        
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
        
        $totalDeuda = 0;
        $totalDeudaPP = 0;

        $html = '<label align="right">La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'</label><br>';
        $html .= 'Apellido y Nombres: <b>'.$colegiado['apellido'].", ".$colegiado['nombre'].'</b><br>';
        $html .= 'Matrícula: <b>'.$matricula.'</b><br>';
        $html .= 'Estado matricular:<b> '.$colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']).$colegiado['movimientoCompleto'].'</b><br>';
        $html .= 'Estado con Tesorería:<b> '.$estadoTesoreria.'</b><br>';
        $html .= '<br><hr><br>';
        $html .= '<h4>Cuotas de Colegiación</h4>';
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');
    
        $pdf->SetFont('dejavusans', '', 8);
        $html = "";    
        $periodoHasta = $periodoActual;
        $periodoDesde = 0; //$periodoHasta - 4;
        $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualPorIdColegiado($idColegiado, $matricula, $periodoDesde, $periodoHasta);
        //var_dump($resDeuda); exit;
        if ($resDeuda['estado']) {
            $deuda = $resDeuda['datos'];
            $html .= '<table >
                        <tr>
                            <th align="center"><b>Per&iacute;odo</b></th>
                            <th align="center"><b>Cuota</b></th>
                            <th align="center"><b>Importe</b></th>
                            <th align="center"><b>Vencimiento</b></th>
                            <th align="center"><b>Recibo</b></th>
                            <th align="center"><b>Fecha Pago</b></th>
                            <th align="center"><b>Importe Actualizado</b></th>
                            <th align="center"><b>Estado</b></th>
                        </tr>';
            foreach ($resDeuda['datos'] as $dato){
                $idColegiadoDeudaAnual = $dato['id'];
                $periodo = $dato['periodo'];
                $importeAnual = $dato['importe'];
                $cuotas = $dato['cuotas'];
                $estado = $dato['estado'];
                
                if ($estado == "A") {
                    //muestra cuotas del periodo
                    //esta con deuda, muestro las cuotas
                    $resCuotas = $colegiadoDeudaAnualLogic->obtenerDeudaAnualCuotas($idColegiadoDeudaAnual);
                    if ($resCuotas['estado']){
                        foreach ($resCuotas['datos'] as $cuotas){
                            $idColegiadoDeudaAnualCuota = $cuotas['idColegiadoDeudaAnualCuota'];
                            $importe = $cuotas['importe'];
                            $cuota = $cuotas['cuota'];
                            $importeActualizado = $cuotas['importeActualizado'];
                            $fechaVencimiento = $cuotas['vencimiento'];
                            $estado = $cuotas['estado'];
                            $estadoPP = $cuotas['estadoPP'];
                            $idPlanPago = $cuotas['idPlanPago'];
                            $fechaPago = $cuotas['fechaPago'];

                            $html .= '<tr>
                                        <td align="center">'.$periodo.'</td>
                                        <td align="center">'.rellenarCeros($cuota, 2).'</td>
                                        <td align="center">'.number_format($importe, 2, ',', '.').'</td>
                                        <td align="center">'.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
                                        <td align="center">'.$idColegiadoDeudaAnualCuota.'</td>
                                        <td align="center">'.cambiarFechaFormatoParaMostrar($fechaPago).'</td>';
                            switch ($estado) {
                                case 1:
                                    $totalDeuda += $importeActualizado;

                                    $html .= '<td align="center">'.number_format($importeActualizado, 2, ',', '.').'</td>
                                            <td align="center">A pagar</td>';
                                break;

                                case 2:
                                    $html .= '<td align="center"></td>
                                            <td align="center">Abonada</td>';
                                break;

                                case 3:
                                    $html .= '<td align="center"></td>';
                                    if ($estadoPP == 'A') {
                                        $html .= '<td align="center">Plan Pagos ('.$idPlanPago.')</td>';
                                    } elseif ($estadoPP == 'C') {
                                        $html .= '<td align="center">Abonada P.P.('.$idPlanPago.')</td>';
                                    }
                                break;

                                case 4:
                                    $html .= '<td align="center"></td>
                                            <td align="center">Condonada</td>';
                                break;

                                default:
                                break;
                            }
                            $html .= '</tr>';
                            
                        }
                        $html .= "<tr><td colspan='7'></td></tr>";
                    } else {
                        //$html .= $resCuotas[estado].' - '.$resCuotas['mensaje'];
                    }
                } else {
                    $html .= '<tr>
                                <td align="center">'.$dato['periodo'].'</td>
                                <td align="center">--</td>
                                <td align="center">'.number_format($dato['importe'], 2, ',', '.').'</td>
                                <td align="center">-------</td>
                                <td align="center">----------</td>
                                <td align="center">-------</td>
                                <td align="center">-------</td>
                                <td align="center">ABONADO</td>
                            </tr>';
                }
            }
            $html .= "</table>";

            $html .= "<br><hr><br>";
            //verifica la deuda de plan de pagos
            if ($colegiadoPlanPagoLogic->tienePlanPagos($idColegiado)) {
                $html .= "<h4>Planes de pago de cuotas de colegiación</h4>";
                $resPP = $colegiadoPlanPagoLogic->obtenerPlanPagoPorIdColegiado($idColegiado);
                if ($resPP['estado']) {
                    $planPago = $resPP['datos'];
                    $html .= '<table >
                                <tr>
                                    <th align="center"><b>Plan de pagos</b></th>
                                    <th align="center"><b>Cuota</b></th>
                                    <th align="center"><b>Importe</b></th>
                                    <th align="center"><b>Vencimiento</b></th>
                                    <th align="center"><b>Fecha Pago</b></th>
                                    <th align="center"><b>Importe Actualizado</b></th>
                                    <th align="center"><b>Estado</b></th>
                                </tr>';
                    foreach ($resPP['datos'] as $dato){
                        $idPlanPago = $dato['idPlanPago'];
                        $fechaCreacion = $dato['fechaCreacion'];
                        $importe = $dato['importe'];
                        $cuotas = $dato['cuotas'];
                        $estado = $dato['estado'];

                        if ($estado == "A") {
                            //muestra cuotas del periodo
                            //esta con deuda, muestro las cuotas
                            $resCuotasPP = $colegiadoPlanPagoLogic->obtenerPlanPagosCuotasPorIdPlanPago($idPlanPago);
                            if ($resCuotasPP['estado']){
                                foreach ($resCuotasPP['datos'] as $cuotas){
                                    $idPlanPagoCuota = $cuotas['idPlanPagoCuota'];
                                    $importe = $cuotas['importe'];
                                    $cuota = $cuotas['cuota'];
                                    $importeActualizado = $cuotas['importeActualizado'];
                                    $fechaVencimiento = $cuotas['vencimiento'];
                                    $estado = $cuotas['estado'];
                                    $fechaPago = $cuotas['fechaPago'];

                                    $html .= '<tr>
                                                <td align="center">'.$idPlanPago.'</td>
                                                <td align="center">'.rellenarCeros($cuota, 2).'</td>
                                                <td align="center">'.number_format($importe, 2, ',', '.').'</td>
                                                <td align="center">'.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
                                                <td align="center">'.cambiarFechaFormatoParaMostrar($fechaPago).'</td>';
                                    switch ($estado) {
                                        case 1:
                                            $totalDeudaPP += $importeActualizado;
                                            
                                            $html .= '<td align="center">'.number_format($importeActualizado, 2, ',', '.').'</td>
                                                    <td align="center">A pagar</td>';
                                        break;

                                        case 2:
                                            $html .= '<td align="center"></td>
                                                    <td align="center">Abonada</td>';
                                        break;

                                        default:
                                            $html .= '<td align="center"></td>
                                                    <td align="center">'.$estado.'</td>';
                                        break;
                                    }
                                    $html .= '</tr>';

                                }
                                $html .= "<tr><td colspan='6'></td></tr>";
                            } else {
                                $html .= $resCuotasPP['estado'].' - '.$resCuotasPP['mensaje'];
                            }
                        } else {
                            $html .= '<tr>
                                        <td align="center">'.$dato['idPlanPago'].'</td>
                                        <td align="center">--</td>
                                        <td align="center">'.number_format($dato['importe'], 2, ',', '.').'</td>
                                        <td align="center">-------</td>
                                        <td align="center">-------</td>
                                        <td align="center">ABONADO</td>
                                    </tr>';
                        }
                    }
                    $html .= "</table>";

                    $html .= "<br><hr><br>";
                }
            }

            //imprimo el total de la deuda
            if ($totalDeuda+$totalDeudaPP > 0) {
                $html .= '<br /><br />
                        <table border="1" cellspacing="0" cellpadding="4">
                        <tr>
                        <td width="35%"><h3>Total deuda de colegiación:</h3></td>
                        <td width="30%" style="text-align: right"><h3><b>$'.number_format($totalDeuda, 2, ',', '.').'</b></h3></td>
                        </tr>';
                if ($totalDeudaPP > 0) {
                    $html .= '<tr>
                            <td width="35%"><h3>Total deuda de Plan de Pagos:</h3></td>
                            <td width="30%" style="text-align: right"><h3><b>$'.number_format($totalDeudaPP, 2, ',', '.').'</b></h3></td>
                            </tr>
                            <tr>
                            <td width="35%"><h3>Total Deuda:</h3></td>
                            <td width="30%" style="text-align: right"><h3><b>$'.number_format($totalDeudaPP+$totalDeuda, 2, ',', '.').'</b></h3></td>
                            </tr>';
                }
                $html .= '</table>';
            } else {
                $html .= '<h4>No hay cuotas pendiente de cobro</h4>';
            }
        } else {
            $html .= "<span><strong>".$resColegiado['mensaje']."</strong></span>";
        }
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->lastPage();
    $destination = $tipoPdf; //'F';
    if (!preg_match('/\.pdf$/', $path_to_store_pdf))
    {
           $path_to_store_pdf .= '.pdf';
    }
    ob_clean();
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF;
    $nombreArchivo = 'CtaCte_Matricula_'.$matricula.'.pdf';

    $estructura = "../archivos/ctacte/".$periodoActual;
    if (!file_exists($estructura)) {
        mkdir($estructura, 0777, true);
    }
    if (file_exists("../archivos/ctacte/".$periodoActual."/".$nombreArchivo)) {
        unlink("../archivos/ctacte/".$periodoActual."/".$nombreArchivo);
    } 

    if ($tipoPdf == 'F') {
        $pdf->Output($camino.'/archivos/ctacte/'.$periodoActual.'/'.$nombreArchivo, $destination);        
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
        echo MAIL_MASIVO.' '.MAIL_MASIVO_PASS.'<br>';
        $mail->From = "noreply@colmed1.org.ar";
        $mail->FromName = "Colegio de Medicos. Distrito I";
        $mail->Subject = "Cta.Cte. cuotas de colegiacion - Tesoreria del Colegio de Medicos Distrito I";
        $mail->AltBody = "";
        $mail->MsgHTML("Le enviamos la cuenta corriente de las cuotas de colegiacion del Colegio de Medicos Distrito I");
        $mail->AddAttachment("../archivos/ctacte/".$periodoActual."/".$nombreArchivo);
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
                        <span><strong>ERROR al enviar el mail al correo: </strong><?php echo $mailDestino.' '.$mail->ErrorInfo; ?><strong>. Vuelva a intentar más tarde.</strong></span>
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

