<?php
if (isset($_GET['ws']) && $_GET['ws'] == 'ok') {
    $path = '../../';
} else {
    $path = '../';
}
require_once ($path.'dataAccess/config.php');
permisoLogueado();
require_once ($path.'html/head.php');
require_once ($path.'dataAccess/funcionesConector.php');
require_once ($path.'dataAccess/funcionesPhp.php');
require_once ($path.'dataAccess/colegiadoLogic.php');
require_once ($path.'dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ($path.'dataAccess/colegiadoPlanPagoLogic.php');
require_once ($path.'dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ($path.'dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();

require_once($path.'tcpdf/config/lang/spa.php');
require_once($path.'tcpdf/tcpdf.php');

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

if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $tipoPdf = $_POST['tipoPdf'];
        $mailDestino = $_POST['mail'];
        
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
        //se elimina el mensaje 2024/10/03
        $especialidades = "";
        /*
        $resEspecialidad = $colegiadoEspecialistaLogic->especialidadesConCaducidad($idColegiado);
        if ($resEspecialidad['estado']) {
            $especialidades = "";
            foreach ($resEspecialidad['datos'] as $dato) {
                $especialidades .= $dato['especialidad'].' '.$dato['caducidad'];
            }
        }
        */
        
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
        
        $periodoHasta = $periodoActual;
        $periodoDesde = $periodoActual; //$periodoHasta - 4;
        $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualPorIdColegiado($idColegiado, 0, $periodoDesde, $periodoHasta);
        if ($resDeuda['estado']) {
            $deuda = $resDeuda['datos'];
            $totalDeuda = 0;
            
            $html .= '<table border="1" cellspacing="0" cellpadding="4">';
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
                        $cantidadCuotas = 0;
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
                            
                            if ($fechaVencimiento < date('Y-m-d')) {
                                //$fechaVencimiento = sumarRestarSobreFecha(date('Y-m-d'), 7, 'day', '+');
                                $fechaVencimiento = ultmioDiaDelMes(date('Y-m-d'));
                            }
                            
                            if ($estado == 1) {
                                $codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra($idColegiadoDeudaAnualCuota, $importeActualizado, $importeActualizado, $fechaVencimiento, $fechaVencimiento, NULL);

                                $params = $pdf->serializeTCPDFtagParameters(array($codigoBarra, 'I25', '', '', 80, 10, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));

                                $html .= '<tr>
                                            <td>
                                            <table>
                                            <tr>
                                            <td colspan="3"><img src="'.$path.'public/images/logoChequera.png" /></td>
                                            </tr>                                        
                                            <tr>
                                            <td colspan="2">'.$colegiado['apellido'].', '.$colegiado['nombre'].'</td>
                                            <td align="right">'.$idColegiadoDeudaAnualCuota.'</td>
                                            </tr>
                                            <tr>
                                            <td colspan="3">Matrícula: <b>'.$matricula.'</b></td>
                                            </tr>
                                            <tr>
                                            <td colspan="2">Período: '.$periodo.'</td>
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
                                            <td colspan="3"><img src="'.$path.'public/images/logoChequera.png" /></td>
                                            </tr>                                       
                                            <tr>
                                            <td colspan="2">'.$colegiado['apellido'].', '.$colegiado['nombre'].'</td>
                                            <td align="center">'.$idColegiadoDeudaAnualCuota.'</td>
                                            </tr>
                                            <tr>
                                            <td colspan="3">Matrícula: <b>'.$matricula.'</b></td>
                                            </tr>
                                            <tr>
                                            <td colspan="2">Período: '.$periodo.'</td>
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
                                if ($cantidadCuotas == 5){
                                    //imprimo datos del colegiado y domicilio
                                    $html .= '<tr>
                                            <td colspan="2">
                                            <table>
                                            <tr>
                                                <td><img src="'.$path.'public/images/logoChequera.png" /></td>
                                                <td><b>IMPORTANTE:</b> Recuerde que para hacer uso de la matricula colegiada y sus beneficios, debe tener sus pagos al dia.<br>';
                                    if ($estadoTesoreria <> 'Al día') {
                                        $html .= 'Según nuestros registros Ud. tiene deuda de: <b>'.$estadoTesoreria.'.</b> Regularice su situación.';
                                    }
                                    $html .= '</td>
                                            </tr>
                                            <tr>
                                            <td><br>
                                            Matrícula: <b>'.$matricula.'</b><br>
                                            <br>
                                            Apellido y nombres: <b>'.$colegiado['apellido'].', '.$colegiado['nombre'].'</b><br>
                                            <br>
                                            Domicilio: <b>'.$domicilioCompleto.'</b><br>
                                            Localidad: <b>'.$localidad.'</b><br>
                                            <br>
                                            <br>
                                            <br>
                                            </td>
                                            <td>';
                                    if ($especialidades <> "") {
                                        $html .= 'Su autorización para el uso del título de especialista en: '.$especialidades.'., recertifique la misma.<br>';
                                    } else {
                                        $html .= '<br>';
                                    }
                                    $html .= '<h3><b>Pago Electrónico - Red Link: </b>'.  rellenarCeros($matricula, 8).'</h3>
                                            Lugar de Pago: BaproPago / RapiPagos / PagoFacil</td>
                                            </tr>
                                            </table>
                                            </td></tr>';
                                }
                            }
                        }
                        //termino de imprimir las cuotas, me fijo si esta vigente el pago completo, debo imprimirlo
                        $resPagoTotal = $colegiadoDeudaAnualLogic->obtenerPagoTotalPorIdDeudaAnual($idColegiadoDeudaAnual);
                        if ($resPagoTotal['estado']) {
                            $pagoTotal = $resPagoTotal['datos'];
                            $idPagoTotal = $pagoTotal['idColegiadoDeudaAnualTotal'];
                            $fechaVencimiento = $pagoTotal['fechaVencimiento'];
                            $importeActualizado = $pagoTotal['importe'];
                            $codigoBarra = $pagoTotal['codigoBarra'];
                            $params = $pdf->serializeTCPDFtagParameters(array($codigoBarra, 'I25', '', '', 80, 10, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
                            
                            $html .= '<tr><td colspan="2">
                                    <table>
                                    <tr>
                                    <td colspan="3"><img src="'.$path.'public/images/logoChequera.png" /></td>
                                    <td colspan="3"></td>
                                    </tr>                                       
                                    <tr>
                                    <td colspan="6"><h1>Pago Total Anual 10% de descuento, importe: $'.number_format($importeActualizado, 2, ',', '.').'</h1></td>
                                    </tr>                                       
                                    <tr>
                                    <td colspan="2">'.$colegiado['apellido'].', '.$colegiado['nombre'].'</td>
                                    <td>Matrícula: <b>'.$matricula.'</b></td>
                                    </tr>
                                    <tr>
                                    <td colspan="2">Recibo: '.$idPagoTotal.'</td>
                                    <td>Período: '.$periodo.'</td>
                                    <td></td>
                                    </tr>
                                    <tr>
                                    <td colspan="3">Vencimiento: '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
                                    <td colspan="3"><tcpdf method="write1DBarcode" params="'.$params.'" /></td>
                                    </tr>
                                    </table>
                                    </td>
                                    </tr>';
                        }
                        
                        //si la cantidad de cuotas es menor a 5, debo imprimir los datos de la caratula
                        if ($cantidadCuotas <= 5){
                            //imprimo datos del colegiado y domicilio
                            $html .= '<tr>
                                    <td colspan="2">
                                    <table>
                                    <tr>
                                        <td><img src="'.$path.'public/images/logoChequera.png" /></td>
                                        <td><b>IMPORTANTE:</b> Recuerde que para hacer uso de la matricula colegiada y sus beneficios, debe tener sus pagos al dia.<br>';
                            if ($estadoTesoreria <> 'Al día') {
                                $html .= 'Según nuestros registros Ud. tiene deuda de: <b>'.$estadoTesoreria.'.</b> Regularice su situación.';
                            }
                            $html .= '</td>
                                    </tr>
                                    <tr>
                                    <td><br>
                                    Matrícula: <b>'.$matricula.'</b><br>
                                    <br>
                                    Apellido y nombres: <b>'.$colegiado['apellido'].', '.$colegiado['nombre'].'</b><br>
                                    <br>
                                    Domicilio: <b>'.$domicilioCompleto.'</b><br>
                                    Localidad: <b>'.$localidad.'</b><br>
                                    <br>
                                    <br>
                                    <br>
                                    </td>
                                    <td>';
                            if ($especialidades <> "") {
                                $html .= 'Su autorización para el uso del título de especialista en: '.$especialidades.'., recertifique la misma.<br>';
                            } else {
                                $html .= '<br>';
                            }
                            $html .= '<h3><b>Pago Electrónico - Red Link: </b>'.  rellenarCeros($matricula, 8).'</h3>
                                    Lugar de Pago: BaproPago / RapiPagos / PagoFacil</td>
                                    </tr>
                                    </table>
                                    </td></tr>';
                        }
                    } else {
                        $html .= $resCuotas[estado].' - '.$resCuotas['mensaje'];
                    }
                } 
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
            $nombreArchivo = 'ColegiacionMatricula_'.$matricula.'.pdf';

            $estructura = $path."archivos/ctacte/".$periodoActual;
            if (!file_exists($estructura)) {
                mkdir($estructura, 0777, true);
            }
            if (file_exists($path."archivos/ctacte/".$periodoActual."/".$nombreArchivo)) {
                unlink($path."archivos/ctacte/".$periodoActual."/".$nombreArchivo);
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
                $mail->Username = 'noreply@colmed1.org.ar';
                $mail->Password = '11edaef3b5f4b1091b4ebec3355a3210';

                $mail->From = "noreply@colmed1.org.ar";
                $mail->FromName = "Colegio de Medicos. Distrito I";
                $mail->Subject = "Chequera de cuotas de colegiacion - Tesoreria del Colegio de Medicos Distrito I";
                $mail->AltBody = "";
                $mail->MsgHTML("Le enviamos la chequera de las cuotas de colegiacion del Colegio de Medicos Distrito I");
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
            echo "<span><strong>".$resDeuda['mensaje']."</strong></span>";
        }
            
        } else {
            echo "<span><strong>".$resColegiado['mensaje']."</strong></span>";
        }
} else {
?>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
        <span><strong>ERROR AL INGRESAR</strong></span>
    </div>        
<?php
}