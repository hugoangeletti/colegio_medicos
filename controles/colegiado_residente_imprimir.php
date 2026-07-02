<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoResidenteLogic.php');
$colegiadoResidenteLogic = new colegiadoResidenteLogic();
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();

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
                $this->Image($image_file, 20, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                //$this->SetFont('helvetica', 'B', 20);
                // Title
                //$this->Cell(0, 15, '', 0, false, 'C', 0, 'SOLICITUD DE EXENCION DE PAGO DE CUOTA ANUAL DE MATRICULACIÓN', 0, false, 'M', 'M');

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
if (isset($_GET['id'])) {
    $idColegiadoResidente = $_GET['id'];
    $resColegiadoResidente = $colegiadoResidenteLogic->obtenerColegiadoResidentePorId($idColegiadoResidente);
    if ($resColegiadoResidente['estado']) {
        $colegiadoResidente = $resColegiadoResidente['datos'];
        $idColegiado = $colegiadoResidente['idColegiado'];
        $fechaInicio = $colegiadoResidente['fechaInicio'];
        $fechaFin = $colegiadoResidente['fechaFin'];
        $opcion = $colegiadoResidente['opcion'];
        $adjunto = $colegiadoResidente['adjunto'];
        $resColegiadoDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
        if ($resColegiadoDomicilio['estado']) {
            $domicilio = $resColegiadoDomicilio['datos'];

        }
        $resColegiadoContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resColegiadoContacto['estado']) {
            $contacto = $resColegiadoContacto['datos'];
            
        }
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
        $numeroDocumento = $colegiado['numeroDocumento'];
        if ($sexo == 'M') {
            $elColegiado = '<b>DR. '.trim($nombre).' '.trim($apellido).'</b>';
        } else {
            $elColegiado = '<b>DRA.'.trim($nombre).' '.trim($apellido).'</b>';
        }

        $domicilioCompleto = "";
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
            
            $nombreLocalidad = $domicilio['nombreLocalidad'];
        }

        $telefonos = "";
        $mail = "";
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $contacto = $resContacto['datos'];
            if ($contacto['telefonoFijo'] && strtoupper($contacto['telefonoFijo']) != "NR") {
                $telefonos .= " Fijo ".$contacto['telefonoFijo'];
            }
            if ($contacto['telefonoMovil'] && strtoupper($contacto['telefonoMovil']) != "NR") {
                $telefonos .= " Movil ".$contacto['telefonoMovil'];
            }
            $mail = $contacto['email'];
        }

        /*
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
        */
        if ($continua) {
            //$pdf = new MYPDF('L', PDF_UNIT, $tamanioHoja, true, 'UTF-8', false);
            $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            $pdf->SetPrintHeader(TRUE);
            $pdf->SetPrintFooter(FALSE);
            //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            #Establecemos los márgenes izquierda, arriba y derecha: 
            $pdf->SetMargins(30, 40, 20); 
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
            /*
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
            */
            //fin imprimir codigo QR

            $pdf->SetFont('dejavusans', '', 12);
            //$pdf->SetXY(10,40);
            $pdf->MultiCell(160, 5, 'N° '.rellenarCeros($idColegiadoResidente, 4), 0, 'R', false, 1, '', '', true);
            $pdf->Ln(10);

            //imprimo la planilla
            switch ($opcion) {
                case 'EXENCION':
                    //$pdf->SetXY(20, 50);
                    $pdf->MultiCell(150, 5, 'SOLICITUD DE EXENCIÓN DE PAGO DE CUOTA ANUAL DE MATRICULACIÓN', 0, 'C', false, 1, '', '', true);

                    $html = '<p style="line-height: 15em; text-align: justify;">El que suscribe, '.$elColegiado.' DNI N° <b>'.$numeroDocumento.'</b> M.P. N° <b>'.$matricula.'</b>, con domicilio en calle <b>'.$domicilioCompleto.' de '.$nombreLocalidad.'</b>, teléfono <b>'.$telefonos.'</b> y correo electrónico <b>'.$mail.'</b>, solicito en mi calidad de médico residente del sistema de salud de la provincia de Buenos Aires <b>la exención del pago de la cuota anual de matriculación</b>, en los términos del artículo 2° de la Ley N° 15434 y su Decreto promulgatorio Nº 702/2023.</p>
                        <p style="line-height: 15em; text-align: justify;">A tales efectos adjunto al presente:<br>
                        <b>'.$adjunto.'</b></p>
                        <p style="line-height: 15em; text-align: justify;"><b>Declaro conocer que la exención solicitada me impide gozar de cualquier beneficio derivado de mi condición de matriculado, entre ellos, acceder a los que otorga a los colegiados los artículos 5° inc. 17) y 34° inc. g) del Decreto N° Ley 5413/58; como asi también no acceder al seguro de praxis médica contratado por la institución</p>
                        <p style="line-height: 15em; text-align: justify;">Asimismo se me ha comunicado que la Asamblea Anual Ordinaria celebrada con fecha 16 de junio del año en curso, resolvió establecer el pago voluntario optativo para los residentes del sistema público provincial de la matricula anual, que deseen acceder a los beneficios de la colegiación.</b>
                        <br><br></p>
                        <p>
                            Fecha de solicitud: <b>'.cambiarFechaFormatoParaMostrar($fechaInicio).'</b>
                            <br>
                            Fecha de caducidad: <b>'.cambiarFechaFormatoParaMostrar($fechaFin).'</b>
                            <br><br>
                        </p>';
                    break;
                
                case 'PAGO_CUOTA':
                    //$pdf->SetXY(10, 50);
                    $pdf->MultiCell(160, 5, 'SOLICITUD DE PAGO VOLUNTARIO DE LA CUOTA ANUAL DE MATRICULACIÓN', 0, 'C', false, 1, '', '', true);

                    $html = '<p style="line-height: 15em; text-align: justify;">El que suscribe, '.$elColegiado.' DNI N° <b>'.$numeroDocumento.'</b> M.P. N° <b>'.$matricula.'</b>, con domicilio en calle <b>'.$domicilioCompleto.' de '.$nombreLocalidad.'</b>, teléfono <b>'.$telefonos.'</b> y correo electrónico <b>'.$mail.'</b>, solicito en mi calidad de médico residente del sistema de salud de la provincia de Buenos Aires <b>acceder al pago voluntario de la cuota anual de matriculación que me permite acceder a los beneficios de la colegiación</b>, conforme lo decidido por la Asamblea Anual Ordinaria celebrada con fecha 16 de junio de 2023.</p>
                        <p style="line-height: 15em; text-align: justify;">Declaro conocer que la Ley N° 15434 dispone en su artículo 2° que <i>“Quedan exentos del pago del aporte mensual correspondiente a la matrícula profesional al Colegio de Médicos de la Provincia de Buenos Aires, los profesionales médicos que se encuentren en el sistema de residencias del sistema de salud de la provincia de Buenos Aires”</i> y que la exención allí prevista me impide gozar de cualquier beneficio derivado de mi condición de matriculado, entre ellos, acceder a los que otorga a los colegiados los artículos 5° inc. 17) y 34° inc. g) del Decreto N° Ley 5413/58.</p>
                        <p style="line-height: 15em; text-align: justify;">A tales efectos adjunto al presente:</p>
                        <p style="line-height: 15em; text-align: justify;"><b>'.$adjunto.'</b></p>';
                    break;
                
                default:
                    $html = '';
                    break;
            }

            $html .= 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'.-';
            $pdf->Ln(10);
            $pdf->SetFont('dejavusans', '', 10);
            //$pdf->SetXY(10, 65);
            $pdf->writeHTMLCell(160, 25, '', '', $html, 0, 1, 0, true, 'R', true);

            $pdf->Ln(10);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->MultiCell(100, 5, 'Firma: ________________________________________', 0, 'L', false, 1, '', '', true);
            $pdf->Ln(2);
            $pdf->MultiCell(100, 5, 'Aclaración: ___________________________________', 0, 'L', false, 1, '', '', true);
            $pdf->Ln(2);
            $pdf->MultiCell(100, 5, 'M.P.: _________________________________________', 0, 'L', false, 1, '', '', true);

            $destination = 'I';
            if (!preg_match('/\.pdf$/', $path_to_store_pdf))
            {
                $path_to_store_pdf .= '.pdf';
            }
            ob_clean();

            $nombreArchivo = 'Residente_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
            /*
            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF;
            $periodoActual = $_SESSION['periodoActual'];
                        
            $estructura = "../archivos/certificados/".$periodoActual;
            if (!file_exists($estructura)) {
                mkdir($estructura, 0777, true);
            }
            if (file_exists("../archivos/certificados/".$periodoActual."/".$nombreArchivo)) {
                unlink("../archivos/certificados/".$periodoActual."/".$nombreArchivo);
            } 
            */

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
