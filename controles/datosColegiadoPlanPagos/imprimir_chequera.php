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
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
//                // Logo
//                $image_file = '../public/images/logo_colmed1_lg.png';
//                $this->Image($image_file, 10, 5, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//                 // Set font
//                $this->SetFont('helvetica', 'B', 20);
//                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer() {
//                // Position at 15 mm from bottom
//                $this->SetY(-15);
//                // Set font
//                $this->SetFont('helvetica', 'I', 8);
//
//                //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
//                //$this->Ln(3);
//                // Page number
//                $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}
$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(true);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(0, 0, 0);
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('dejavusans', '', 8);
$pdf->AddPage();

$html='';
$continua = TRUE;
if (isset($_GET['idPP'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idPlanPago = $_GET['idPP'];
    $tipoPdf = $_POST['tipoPdf'];
    $mailDestino = $_POST['mail'];

    $resPlanPago = $colegiadoPlanPagoLogic->obtenerPlanPagoPorId($idPlanPago);
    if ($resPlanPago['estado']) {
        $planPago = $resPlanPago['datos'];
        $idColegiado = $planPago['idColegiado'];
        $totalPlanPagos = $planPago['importeTotal'];
        $cuotas = $planPago['cuotas'];
        
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
        
        //obtengo las cuotas a imprimir
        $resCuotas = $colegiadoPlanPagoLogic->obtenerPlanPagosCuotasPorIdPlanPago($idPlanPago);
        if (!$resCuotas['estado']){
            $resultado['mensaje'] = $resCuotas['mensaje'];
            $continua = FALSE;
        }
    } else {
        $resultado['mensaje'] = $resPlanPago['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if ($continua) {
    $html .= '<table border="1" cellspacing="0" cellpadding="4">';
    $cantidadCuotas = 0;
    foreach ($resCuotas['datos'] as $cuotas){
        $idPlanPagoCuota = $cuotas['idPlanPagoCuota'];
        $importe = $cuotas['importe'];
        $cuota = $cuotas['cuota'];
        $importeActualizado = $cuotas['importeActualizado'];
        $fechaVencimiento = $cuotas['vencimiento'];
        $estado = $cuotas['estado'];
        $fechaPago = $cuotas['fechaPago'];

        if ($estado == 1) {
            $codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra($idPlanPagoCuota, $importeActualizado, $importeActualizado, $fechaVencimiento, $fechaVencimiento, NULL);

            $params = $pdf->serializeTCPDFtagParameters(array($codigoBarra, 'I25', '', '', 80, 10, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));

            $html .= '<tr>
                        <td>
                        <table>
                        <tr>
                        <td colspan="3"><img src="../../public/images/logoChequera.png" /></td>
                        </tr>                                        
                        <tr>
                        <td colspan="2">'.$apellidoNombre.'</td>
                        <td align="right">'.$idPlanPagoCuota.'</td>
                        </tr>
                        <tr>
                        <td colspan="3">Matrícula: <b>'.$matricula.'</b></td>
                        </tr>
                        <tr>
                        <td colspan="2"><h2>Plan de Pagos Nº: '.$idPlanPago.'</h2></td>
                        <td align="center"><h2>Cuota:'.rellenarCeros($cuota, 2).'</h2></td>
                        </tr>
                        <tr>
                        <td colspan="2" align="left">Vencimiento: '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
                        <td align="center">Importe: '.number_format($importeActualizado, 2, ',', '.').'</td>
                        </tr>
                        <tr><td></td></tr>
                        <tr>
                        <td colspan="3"><b>Pago Electrónico - Red Link: </b>'.  rellenarCeros($matricula, 8).'</td>
                        </tr>
                        <tr>
                        <td colspan="3">Esta cuota incluye el Fondo Solidario, para gozar del mismo deberá cancelarla al vto.</td>
                        </tr>
                        </table>
                        </td>

                        <td>
                        <table>
                        <tr>
                        <td colspan="3"><img src="../../public/images/logoChequera.png" /></td>
                        </tr>                                       
                        <tr>
                        <td colspan="2">'.$apellidoNombre.'</td>
                        <td align="center">'.$idPlanPagoCuota.'</td>
                        </tr>
                        <tr>
                        <td colspan="3">Matrícula: <b>'.$matricula.'</b></td>
                        </tr>
                        <tr>
                        <td colspan="2"><h2>Plan de Pagos Nº: '.$idPlanPago.'</h2></td>
                        <td align="center"><h2>Cuota:'.rellenarCeros($cuota, 2).'</h2></td>
                        </tr>
                        <tr>
                        <td colspan="2" align="left">Vencimiento: '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
                        <td align="center">Importe: '.number_format($importeActualizado, 2, ',', '.').'</td>
                        </tr>
                        <tr><td></td></tr>
                        <tr>
                        <td colspan="3"><tcpdf method="write1DBarcode" params="'.$params.'" />
                         </td>
                        </tr>

                        </table>
                        </td>
                    </tr>';

            $cantidadCuotas += 1;
            /*
            if ($cantidadCuotas == 5){
                //imprimo datos del colegiado y domicilio
                $html .= '<tr>
                        <td colspan="2">
                        <table>
                        <tr>
                            <td><img src="../../public/images/logoChequera.png" /></td>
                            <td><b>IMPORTANTE:</b> Recuerde que para hacer uso de la matricula colegiada y sus beneficios, debe tener sus pagos al dia.<br>';
                $html .= '</td>
                        </tr>
                        <tr>
                        <td><br>
                        Matrícula: <b>'.$matricula.'</b><br>
                        <br>
                        Apellido y nombres: <b>'.$apellidoNombre.'</b><br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        </td>
                        <td>';
                $html .= '<h3><b>Pago Electrónico - Red Link: </b>'.  rellenarCeros($matricula, 8).'</h3>
                        Lugar de Pago: BaproPago / RapiPagos / PagoFacil</td>
                        </tr>
                        </table>
                        </td></tr>';
            }
             * 
             */
        }
    }
    
    //si la cantidad de cuotas es menor a 5, debo imprimir los datos de la caratula
    if ($cantidadCuotas <= 5){
        //imprimo datos del colegiado y domicilio
        $html .= '<tr>
                <td colspan="2">
                <table>
                <tr>
                    <td><img src="../../public/images/logoChequera.png" /></td>
                    <td><b>IMPORTANTE:</b> Recuerde que para hacer uso de la matricula colegiada y sus beneficios, debe tener sus pagos al dia.<br>';
        $html .= '</td>
                </tr>
                <tr>
                <td><br>
                Matrícula: <b>'.$matricula.'</b><br>
                <br>
                Apellido y nombres: <b>'.$apellidoNombre.'</b><br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                </td>
                <td>';
        $html .= '<h3><b>Pago Electrónico - Red Link: </b>'.  rellenarCeros($matricula, 8).'</h3>
                Lugar de Pago: BaproPago / RapiPagos / PagoFacil</td>
                </tr>
                </table>
                </td></tr>';
    }
    $html .= "</table>";

    $pdf->writeHTML($html, true, false, false, false, '');
    $pdf->lastPage();

    $destination = $tipoPdf; //'F';
    if (!preg_match('/\.pdf$/', $path_to_store_pdf))
    {
           $path_to_store_pdf .= '.pdf';
    }
    ob_clean();
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF;
    $nombreArchivo = 'PlanDePagos_'.$matricula.'.pdf';

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

    if ($envioMail) {
        //enviamos el pdf por mail si tiene contacto
        $destinatario = $colegiado['apellido'].', '.$colegiado['nombre'];
        require_once '../../PHPMailer/class.phpmailer.php';
        require_once '../../PHPMailer/class.smtp.php';

                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "ssl";
                $mail->Host = "mail.colmed1.org.ar";
                $mail->Port = 465;
                //$mail->Username = "sistemas@colmed1.org.ar";
                //$mail->Password = "@sistem@s_1965";
                //$mail->Username = 'noreply@colmed1.org.ar';
                //$mail->Password = 'YWY1NDE4OTMwNGZlODE2NDRhNzQzMjI3';
                //$mail->Password = '11edaef3b5f4b1091b4ebec3355a3210';
                $mail->Username = MAIL_MASIVO;
                $mail->Password = MAIL_MASIVO_PASS;


                $mail->From = "noreply@colmed1.org.ar";
                $mail->FromName = "Colegio de Medicos. Distrito I";

        /*        
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Host = "mail.colmed1.org.ar";
        $mail->Port = 465;
        $mail->Username = "sistemas@colmed1.org.ar";
        $mail->Password = "@sistemas1";
        $mail->From = "tesoreria@colmed1.org.ar";
        $mail->FromName = "Colegio de Medicos. Distrito I";
        */
        $mail->Subject = "Chequera de cuotas de colegiacion - Tesoreria del Colegio de Medicos Distrito I";
        $mail->AltBody = "";
        $mail->MsgHTML("Le enviamos la chequera de las cuotas de colegiacion del Colegio de Medicos Distrito I");
        $mail->AddAttachment("../../archivos/planpago/".$periodoActual."/".$nombreArchivo);
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
            require_once ('../../html/head.php');
            require_once ('../../html/encabezado.php');
        ?>
            <div class="col-md-12">
                <div class="row" style="background-color: #428bca;">
                    <div class="col-md-12"></div>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <h3>Chequera de Plan de Pagos solicitado por <?php echo $apellidoNombre; ?></h3>
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
        <span><strong><?php echo $resultado['mensaje']; ?></strong></span>
    </div>        
<?php
}