<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoConsultorioLogic.php');
$colegiadoConsultorioLogic = new colegiadoConsultorioLogic();
require_once ('../dataAccess/colegiadoCargoLogic.php');
require_once ('../dataAccess/presidenteLogic.php');

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                //$this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $this->Image($image_file, 60, 40, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 20);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');

                //MARCA DE AGUA 
                /*
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);
                $img_file2 = '../../public/images/fondoCertificado.png';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                 * 
                 */
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
?>
<?php
$continua = TRUE;
if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $idColegiadoConsultorio = $_GET['id'];
    $tipoConsultorio = $_GET['tipo'];
    $resConsultorio = $colegiadoConsultorioLogic->obtenerConsultorioPorId($idColegiadoConsultorio);
    if ($resConsultorio['estado']) {
        $consultorio = $resConsultorio['datos'];
        
        $idColegiado = $consultorio['idColegiado'];
    } else {
        $resultado['mensaje'] = $resConsultorio['mensaje'];
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
    $resultado['mensaje'] = 'No ingresó correctamente';
}

if ($continua){
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellido = $colegiado['apellido'];
        $nombre = $colegiado['nombre'];
        $sexo = $colegiado['sexo'];
        if ($sexo == 'M') {
            $elColegiado = 'el profesional médico, <b>DR. '.trim($nombre).' '.trim($apellido).'</b>';
        } else {
            $elColegiado = 'la profesional médica, <b>DRA.'.trim($nombre).' '.trim($apellido).'</b>';
        }
        $hash_qr = $colegiado['hash_qr'];
        if (!isset($hash_qr) || $hash_qr == "") {
            $creado = date('YmdHis');

            $hash_qr = hashData($idColegiadoConsultorio.'_'.$matricula.'_'.$creado);

            $resultado = $colegiadoConsultorioLogic->guardarQrColegiadoConsultorio($idColegiadoConsultorio, $hash_qr);
            if (!$resultado['estado']) {
                $continua = FALSE;
            }
        }
        $codigoQR = 'https://www.colmed1.com.ar/verificar/consultorio.php?id='.$hash_qr;

        if ($continua) {
            $libroFolio = 'Libro <b>'.$colegiado['tomo'].'</b>, Folio <b>'.$colegiado['folio'].'</b>, con fecha: <b>'.
                    substr($colegiado['fechaMatriculacion'], 8, 2).' de '.obtenerMes(substr($colegiado['fechaMatriculacion'], 5, 2)).
                    ' de '.substr($colegiado['fechaMatriculacion'], 0, 4).'</b>,';

            if (isset($consultorio['fechaHabilitacion']) && $consultorio['fechaHabilitacion'] <> '0000-00-00'){
                $fechaHabilitacion = cambiarFechaFormatoParaMostrar($consultorio['fechaHabilitacion']);
                $conFecha = ' con fecha <b>'.$fechaHabilitacion.'</b> ';
                $fecha = $consultorio['fechaHabilitacion'];
                $fechaVencimiento = strtotime ( '+1 year' , strtotime ( $fecha ) ) ;
                $fechaVencimiento = date ( 'j/m/Y' , $fechaVencimiento );
                //$elVencimiento = 'Fecha de caducidad: '.$fechaVencimiento;
                $elVencimiento = '&nbsp;';
            } else {
                $fechaHabilitacion = NULL;
                $elVencimiento = '&nbsp;';
                $conFecha = '';
            }

            if (isset($consultorio['resolucion']) && $consultorio['resolucion'] <> ''){
                $numeroResolucion = $consultorio['resolucion'];
            } else {
                $numeroResolucion = NULL;
            }
            
            if ($consultorio['calle']) {
                $domicilioCompleto = $consultorio['calle'];
                if ($consultorio['numero']) {
                    $domicilioCompleto .= " Nº ".$consultorio['numero'];
                }
                if ($consultorio['lateral'] && $consultorio['lateral'] <> "" && $consultorio['lateral'] <> "NR" && $consultorio['lateral'] <> "SL" && $consultorio['lateral'] <> "-") {
                    $domicilioCompleto .= " e/ ".$consultorio['lateral'];
                }
                if ($consultorio['piso'] && strtoupper($consultorio['piso']) != "NR") {
                    $domicilioCompleto .= " Piso ".$consultorio['piso'];
                }
                if ($consultorio['departamento'] && strtoupper($consultorio['departamento']) != "NR") {
                    $domicilioCompleto .= " Dto. ".$consultorio['departamento'];
                }
                $domicilioCompleto .= ' de '.$consultorio['nombreLocalidad'];
                $domicilioCompleto = strtoupper($domicilioCompleto);
            } else {
                $domicilioCompleto = '';
            }
            
            //obtener datos de los firmantes
            $firmante = NULL;
            $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); //secretario general
            //$resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(5); //pro secretario
            if ($resFirmante['estado']) {
                if ($resFirmante['datos']['matricula'] == $matricula) {
                    $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(4);
                    if ($resFirmante['estado']) {
                        $firmante = $resFirmante['datos'];
                    }
                } else {
                    $firmante = $resFirmante['datos'];
                }
            } 

            if (isset($firmante)) {
                $elSecretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
                $elCargo2 = $firmante['nombreCargo'];
            } else {
                $elSecretario = '';                
                $elCargo2 = "";
            }

            $firmante = NULL;
            $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1);
            if ($resFirmante['estado']) {
                if ($resFirmante['datos']['matricula'] == $matricula) {
                    $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(4);
                    if ($resFirmante['estado']) {
                        $firmante = $resFirmante['datos'];
                    }
                } else {
                    $firmante = $resFirmante['datos'];
                }
            }
            if (isset($firmante)) {
                $elPresidente = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
                $elCargo1 = $firmante['nombreCargo'];
            } else {
                $elPresidente = '';                
                $elCargo1 = "";
            }
            
            switch ($tipoConsultorio) {
                case 'Consultorio':
                    $detalleTipoConsultorio = ' el Consultorio ubicado en:';
                    break;

                case 'Centro':
                    $detalleTipoConsultorio = ' el Centro de Rehabilitación Cardiovascular sito en:';
                    break;

                default:
                    $detalleTipoConsultorio = '';
                    break;
            }
                
            $tamanioHoja = array(297,210);
            $tamanioHoja = array(297,210);
            $pdf = new MYPDF('L', PDF_UNIT, $tamanioHoja, true, 'UTF-8', false);
            //$pdf = new MYPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetPrintHeader(FALSE);
            $pdf->SetPrintFooter(FALSE);
            //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            #Establecemos los márgenes izquierda, arriba y derecha: 
            $pdf->SetMargins(30, 30 , 20); 
            $pdf->SetMargins(30, 0, 20); 
            //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //$pdf->SetHeaderMargin(0);
            //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            //$pdf->SetFooterMargin(0);
            //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->SetAutoPageBreak(TRUE, 0);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            //$pdf->AddPage('L', 'A4');
                
            $alturaLinea = 7;
            //Imprimir codigo QR
            $style = array(
                    'border' => true,
                    'vpadding' => 'auto',
                    'hpadding' => 'auto',
                    'fgcolor' => array(0,0,0),
                    'bgcolor' => false, //array(255,255,255)
                    'module_width' => 1, // width of a single module in points
                    'module_height' => 1 // height of a single module in points
                );
            $pdf->ln(5);
            $pdf->SetXY(270, 150);
            $pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 230, 30, 25, 25, $style, 'N');
            //fin imprimir codigo QR

            //imprimo la planilla
            $pdf->SetFont('dejavusans', '', 12);
            if (isset($numeroResolucion)) {
                //imprime con resolucion
                $html = '<p style="line-height: 15em; text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        'El Colegio de Médicos de la Provincia de Buenos Aires, DISTRITO I, certifica que '.$elColegiado.', registrado bajo el número de Matrícula Provincial <b>'.
                        $matricula.'</b> '.$libroFolio.' habiendo cumplido con todos los requisitos previos para el ejercicio '.
                        'de la Medicina en jurisdicción de la Provincia de Buenos Aires, conforme al Decreto-Ley 5413/58, '.
                        'Resolución Nº 3740/78 del Ministerio de Bienestar Social, Decreto Nº 3280/90 y Resoluciones del Consejo '.
                        'Superior del Colegio de Médicos de la Provincia de Buenos Aires Nº 567/04 y del Ministerio de Salud '.
                        'Nº 3057/09, le ha sido HABILITADO por Resolución Nº <b>'.$numeroResolucion.'</b> con fecha '.
                        '<b>'.$fechaHabilitacion.'</b>'.$detalleTipoConsultorio.'</p>';
            } else {
                //imprime SIN resolucion
                $html = '<p style="line-height: 15em; text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        'El Colegio de Médicos de la Provincia de Buenos Aires, DISTRITO I, certifica que '.$elColegiado.', registrado bajo el número de Matrícula Provincial <b>'.
                        $matricula.'</b> '.$libroFolio.' habiendo cumplido con todos los requisitos previos para el ejercicio '.
                        'de la Medicina en jurisdicción de la Provincia de Buenos Aires, conforme al Decreto-Ley 5413/58, '.
                        'Resolución Nº 3740/78 del Ministerio de Bienestar Social, Decreto Nº 3280/90 y Resoluciones del Consejo '.
                        'Superior del Colegio de Médicos de la Provincia de Buenos Aires Nº 567/04 y del Ministerio de Salud '.
                        'Nº 3057/09, le ha sido HABILITADO'.$conFecha.$detalleTipoConsultorio.'</p>';
            }
            $html .= '<br><p style="line-height: 15em; text-align: center;"><b>'.$domicilioCompleto.'</b></p>';
            //$pdf->Ln(80);
            $pdf->SetXY(50, 80);
            $pdf->writeHTMLCell(200, 50, '', '', $html, 0, 1, 0, true, 'J', true);

            $html = '<br><p style="line-height: 15em; text-align: right;">La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'</p>';
            $pdf->writeHTMLCell(220, 0, '', '', $html, 0, 1, 0, true, 'J', true);
            $img = '../public/images/SELLO.png';
            $pdf->Image($img , 135, 150, 25, 35, 'PNG');
            $conFirma = 'N';
            if ($conFirma == 'S') {
                //imprimo sello y firma
                //1: presidente
                $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1); 
                if ($resFirmante['estado']) {
                    $firmante = $resFirmante['datos'];
                    $presidente = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
                    $jpgfile1 = 'firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                        
                    $htmlFirma1 = '<td style="text-align:center;">
                                    <img src="'.$jpgfile1.'" border="0" height="120" width="" />
                                    <label style="font-size: 10px;">'.$elPresidente.'</label><br>
                                    <label style="font-size: 8px;">'.$elCargo1.'<br>Colegio de Médicos - Distrito I</label>
                                    </td>';
                } else {
                    $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
                }
                
                //2: secretariogeneral
                $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
                if ($resFirmante['estado']) {
                    $firmante = $resFirmante['datos'];
                    $secretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
                    $jpgfile2 = 'firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                        
                    $htmlFirma2 = '<td style="text-align:center;" >
                                    <p>'.$elVencimiento.'</p>
                                    <img src="'.$jpgfile2.'" border="0" height="120" width="" />
                                    <label style="font-size: 10px;">'.$elSecretario.'</label><br>
                                    <label style="font-size: 8px;">'.$elCargo2.'<br>Colegio de Médicos - Distrito I</label>
                                </td>';
                } else {
                    $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
                }
            } else {
                //$pdf->Ln(75);
                $htmlFirma2 = '<td style="text-align:center;" >
                                    <p>'.$elVencimiento.'</p>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <label style="font-size: 10px;">'.$elSecretario.'</label><br>
                                    <label style="font-size: 8px;">'.$elCargo2.'<br>Colegio de Médicos - Distrito I</label>
                                </td>';
                $htmlFirma1 = '<td style="text-align:center;">
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <label style="font-size: 10px;">'.$elPresidente.'</label><br>
                                    <label style="font-size: 8px;">'.$elCargo1.'<br>Colegio de Médicos - Distrito I</label>
                                </td>';
            }
            $html = '<table>
                    <tr>'
                        .$htmlFirma2.
                        '<td style="text-align:center;" >&nbsp;</td>'
                        .$htmlFirma1.
                    '</tr>
                    </table';
            $pdf->SetXY(40, 130);
            $pdf->writeHTMLCell(220, 50, '', '', $html, 0, 1, 0, true, 'J', true);
                
            $destination = 'I';
            if (!preg_match('/\.pdf$/', $path_to_store_pdf))
            {
                $path_to_store_pdf .= '.pdf';
            }
            ob_clean();

            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF;
            $nombreArchivo = 'Consultorio_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
            $periodoActual = $_SESSION['periodoActual'];
                        
            $estructura = "../archivos/certificados/".$periodoActual;
            if (!file_exists($estructura)) {
                mkdir($estructura, 0777, true);
            }
            if (file_exists("../archivos/certificados/".$periodoActual."/".$nombreArchivo)) {
                unlink("../archivos/certificados/".$periodoActual."/".$nombreArchivo);
            } 
        
            $pdf->Output($nombreArchivo, $destination);        
        } else {
        ?>
            <div id="pagina">
                <h2>Se produjo un error al generar el codigo QR</h2>
            </div>
        <?php
        }
    } else {
    ?>
        <div id="pagina">
            <h2>Se produjo un error al buscar al colegiado</h2>
        </div>
    <?php
    }
} else {
        require_once ('../html/head.php');
        require_once ('../html/encabezado.php');
    ?>
        <div class="row">
            <div class="col-md-12 alert alert-danger">
                <h3><?php echo $resultado['mensaje']; ?></h3>
            </div>
            <div class="row">&nbsp;</div>
            <div class="col-md-12">
                <h3>Cerrar esta pestaña del navegador, hubo un error en el reporte</h3>
            </div>
        </div>
<?php
}
