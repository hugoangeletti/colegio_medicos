<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
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
set_time_limit(0);
ini_set('memory_limit', '-1');

$periodoActual = date('Y');
if (date('m')<6) {
    $periodoActual -= 1;
}

$totalChequeras = 0;
$resDeudaAnual = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnual($periodoActual);
if ($resDeudaAnual['estado']){
    foreach ($resDeudaAnual['datos'] as $deuda) {
        //guardo el envio en enviomailchequera y genero el pdf
        $idColegiadoDeudaAnual = $deuda['idColegiadoDeudaAnual'];
        $idColegiado = $deuda['idColegiado'];
        $matricula = $deuda['matricula'];
        $apellido = $deuda['apellido'];
        $nombre = $deuda['nombre'];
        $periodo = $periodoActual;
        //$colegiadoEnvioChequeraLogic->guardarEnvioChequera($idColegiadoDeudaAnual);
        
        //genero el pdf
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
        $especialidades = "";
        $resEspecialidad = $colegiadoEspecialistaLogic->especialidadesConCaducidad($idColegiado);
        if ($resEspecialidad['estado']) {
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
        
        $html .= '<table border="1" cellspacing="0" cellpadding="4">';
        $resCuotas = $colegiadoDeudaAnualLogic->obtenerDeudaAnualCuotas($idColegiadoDeudaAnual);
        if ($resCuotas['estado']){
            $cantidadCuotas = 0;
            foreach ($resCuotas['datos'] as $cuotas){
                $idColegiadoDeudaAnualCuota = $cuotas['idColegiadoDeudaAnualCuota'];
                $importe = $cuotas['importe'];
                $cuota = $cuotas['cuota'];
                $estado = $cuotas['estado'];
                $estadoPP = $cuotas['estadoPP'];
                $idPlanPago = $cuotas['idPlanPago'];
                $fechaPago = $cuotas['fechaPago'];

                if ($estado == 1) {
                    $importeActualizado = $cuotas['importeActualizado'];
                    $fechaVencimiento = $cuotas['vencimiento'];
                    $fechaLabel = 'Fecha de vencimiento: ';
                    $codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra($idColegiadoDeudaAnualCuota, $importeActualizado, $importeActualizado, $fechaVencimiento, $fechaVencimiento, NULL);

                    $params = $pdf->serializeTCPDFtagParameters(array($codigoBarra, 'I25', '', '', 80, 10, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));
                } else {
                    $importeActualizado = $cuotas['importe'];
                    $fechaVencimiento = $cuotas['fechaPago'];
                    $fechaLabel = 'Fecha de Pago: ';
                }
                $html .= '<tr>
                            <td>
                            <table>
                            <tr>
                            <td colspan="3"><img src="../public/images/logoChequera.png" /></td>
                            </tr>                                        
                            <tr>
                            <td colspan="2">'.$apellido.', '.$nombre.'</td>
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
                            <td colspan="2" align="left">'.$fechaLabel.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
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
                            <td colspan="3"><img src="../public/images/logoChequera.png" /></td>
                            </tr>                                       
                            <tr>
                            <td colspan="2">'.$apellido.', '.$nombre.'</td>
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
                            <td colspan="2" align="left">'.$fechaLabel.cambiarFechaFormatoParaMostrar($fechaVencimiento).'</td>
                            <td align="center">Importe: '.number_format($importeActualizado, 2, ',', '.').'</td>
                            </tr>
                            <tr><td></td></tr>
                            <tr>';
                if ($estado == 1) {
                    $html .= '<td colspan="3"><tcpdf method="write1DBarcode" params="'.$params.'" /></td>';
                } else {
                    $html .= '<td colspan="3"></td>';
                }
                $html .= '</tr>
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
                                <td><img src="../public/images/logoChequera.png" /></td>
                                <td><b>IMPORTANTE:</b> Recuerde que para hacer uso de la matricula colegiada y sus beneficios, debe tener sus pagos al dia.<br>';
                    if ($estadoTesoreria <> 'Al día') {
                        $html .= 'Según nuestros registros Ud. tiene deuda de: <b>'.$estadoTesoreria.'.</b> Regularice su situación.';
                    }
                    $html .= '</td>
                            </tr>
                            <tr>
                            <td><br>
                            Matrícula: <b>'.$matricula.'</b><br>
                            Apellido y nombres: <b>'.$apellido.', '.$nombre.'</b><br>
                            Domicilio: <b>'.$domicilioCompleto.'</b><br>
                            Localidad: <b>'.$localidad.'</b><br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            </td>
                            <td>';
                    if ($especialidades <> "") {
                        $html .= 'Su autorización para el uso del título de especialista en: '.$especialidades.', recertifique la misma.<br>';
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
            //si la cantidad de cuotas es menor a 5, debo imprimir los datos de la caratula
            if ($cantidadCuotas <= 5){
                //imprimo datos del colegiado y domicilio
                $html .= '<tr>
                        <td colspan="2">
                        <table>
                        <tr>
                            <td><img src="../public/images/logoChequera.png" /></td>
                            <td><b>IMPORTANTE:</b> Recuerde que para hacer uso de la matricula colegiada y sus beneficios, debe tener sus pagos al dia.<br>';
                if ($estadoTesoreria <> 'Al día') {
                    $html .= 'Según nuestros registros Ud. tiene deuda de: <b>'.$estadoTesoreria.'.</b> Regularice su situación.';
                }
                $html .= '</td>
                        </tr>
                        <tr>
                        <td><br>
                        Matrícula: <b>'.$matricula.'</b><br>
                        
                        Apellido y nombres: <b>'.$apellido.', '.$nombre.'</b><br>
                        
                        Domicilio: <b>'.$domicilioCompleto.'</b><br>
                        Localidad: <b>'.$localidad.'</b><br>
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
            } else {
                //imprimo el pago total si no esta vencido
                $resPagoTotal = $colegiadoDeudaAnualLogic->obtenerPagoTotalPorIdDeudaAnual($idColegiadoDeudaAnual);
                if ($resPagoTotal['estado']) {
                    $pagoTotal = $resPagoTotal['datos'];
                    $idPagoTotal = $pagoTotal['idColegiadoDeudaAnualTotal'];
                    $fechaVencimiento = $pagoTotal['fechaVencimiento'];
                    $importeActualizado = $pagoTotal['importe'];
                    $codigoBarra = $pagoTotal['codigoBarra'];
                    if ($fechaVencimiento >= date('Y-m-d')) {
                        //imprimo el cupon del pago total
                            
                        $params = $pdf->serializeTCPDFtagParameters(array($codigoBarra, 'I25', '', '', 80, 10, 0.4, array('position'=>'S', 'border'=>false, 'padding'=>0, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4), 'N'));

                        $html .= '<tr><td colspan="2">
                                <table>
                                <tr>
                                <td colspan="3"><img src="../public/images/logoChequera.png" /></td>
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
                }
            }
        } else {
            $html .= $resCuotas[estado].' - '.$resCuotas['mensaje'];
        }
        $html .= "</table>";

        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->lastPage();

        $destination = 'F';
        if (!preg_match('/\.pdf$/', $path_to_store_pdf))
        {
               $path_to_store_pdf .= '.pdf';
        }
        ob_clean();
        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF;
        $nombreArchivo = 'Colegiacion'.$periodoActual.'_Matricula_'.$matricula.'.pdf';

        $estructura = "../archivos/cuotas/".$periodoActual;
        echo $estructura;
        if (!file_exists($estructura)) {
            mkdir($estructura, 0777, true);
        }
        if (file_exists("../archivos/cuotas/".$periodoActual."/".$nombreArchivo)) {
            unlink("../archivos/cuotas/".$periodoActual."/".$nombreArchivo);
        } 

        if ($destination == 'F') {
            $pdf->Output($camino.'/archivos/cuotas/'.$periodoActual.'/'.$nombreArchivo, $destination);        
            $envioMail = TRUE;
            $totalChequeras += 1;
        } else {
            $pdf->Output($nombreArchivo, $destination);        
            $envioMail = FALSE;
        }
            
    } 
} else {
    echo "<span><strong>".$resDeudaAnual['mensaje']."</strong></span>";
}

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
        <h3>Se generaron los PDF de las Chequeras. Total generadas: <?php echo $totalChequeras; ?></h3>
    </div>
</div>
<?php
require_once '../html/footer.php';
