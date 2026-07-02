<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
require_once ('../../dataAccess/tipoMovimientoLogic.php');
$tipoMovimientoLogic = new tipoMovimientoLogic();
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
            /*
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

                $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
        
             * 
             */
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
if (isset($_GET['n_exp']) && isset($_GET['a_exp'])) {
    $numeroExpediente = $_GET['n_exp'];
    $anioExpediente = $_GET['a_exp'];
    $resExpediente = $mesaEntradaEspecialistaLogic->obtenerEspecialistaPorExpediente($numeroExpediente, $anioExpediente);
    if ($resExpediente['estado']) {
        $datosExp = $resExpediente['datos']; 
        $idColegiado = $datosExp['idColegiado'];
        $tipoTramiteEspecialista = $datosExp['tipoTramiteEspecialista'];
        $nombreTipoEspecialista = $datosExp['nombreTipoEspecialista'];
        $nombreEspecialidadSolicitada = $datosExp['nombreEspecialidad'];
        $idMesaEntrada = $datosExp['idMesaEntrada'];
        $tipoTramiteEspecialista = $datosExp['tipoTramiteEspecialista'];
        $codigoEspecialista = $datosExp['codigoEspecialista'];
        $fechaMesaEntrada = $datosExp['fechaMesaEntrada'];
        $idEstadoMatricular = $datosExp['estadoMatricular'];
        $idEstadoTesoreria = $datosExp['estadoTesoreria'];
        $inciso = $datosExp['inciso'];
        $distrito = $datosExp['distrito'];
        
        //obtengo datos del colegiado
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $sexo = $colegiado['sexo'];
            $apellidoNombre = trim($colegiado['apellido'].' '.trim($colegiado['nombre']));
            $fechaMatriculacion = $colegiado['fechaMatriculacion'];
            $fechaNacimiento = $colegiado['fechaNacimiento'];
            $profesional = "";
            if ($sexo == "M") {
                $profesional = "el profesional médico, <b>DR. ";
            } else {
                $profesional = "la profesional médico, <b>DRA. ";
            }
            $profesional .= $apellidoNombre.' M.P. '.$matricula.'</b>';
        } else {
            $continua = FALSE;
            $resultado['mensaje'] = $resColegiado['mensaje'];
        }
        
        //obtengo el estado matricular en el momento de crear el expediente
        $resTipoMovimiento = $tipoMovimientoLogic->obtenerTipoMovimientoPorId($idEstadoMatricular);
        if ($resTipoMovimiento['estado']) {
            $tipoMovimiento = $resTipoMovimiento['datos'];
            $estadoMatricular = trim($colegiadoLogic->obtenerDetalleTipoEstado($tipoMovimiento['estado']));
            /*
            if (isset($estadoMatricular) && $estadoMatricular <> "") {
                $estadoMatricular .= ' - ';
            }
            $estadoMatricular .= $tipoMovimiento['detalleCompleto'];
             * 
             */
        } else {
            $estadoMatricular = "";
        }
        
        //obtengo el estado con teso en el momento de crear el expediente
        $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($idEstadoTesoreria);
        if ($resEstadoTesoreria['estado']){
            $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
        } else {
            $estadoTesoreria = $resEstadoTesoreria['mensaje'];
        }
        
        $solicitud = "Solicitud de ";
        switch ($tipoTramiteEspecialista) {
            case "J":
                $solicitud .= "Especialista Jerarquizado";
                break;

            case "C":
                $solicitud .= "Especialista Consultor";
                break;

            case "R":
                $solicitud .= "Recertificación";
                if ($codigoEspecialista == 'N') {
                    $solicitud = "Renovación <br>Expedido por Ministerio de Salud de la Nación";
                }
                break;

            default:
                $solicitud .= $nombreTipoEspecialista;
                /*
                if (isset($inciso) && $inciso <> "") {
                    $solicitud .= ' Inciso '.$inciso.' ('.  obtenerDetalleIncisoEspecialistaArt8($inciso).')';
                }
                if (isset($distrito) && $distrito <> "") {
                    $solicitud .= ' (Origen: Distrito '.obtenerNumeroRomano($distrito).')';
                }
                 * 
                 */
                break;
        }
    } else {
        $continua = FALSE;
        $resultado['mensaje'] = $resExpediente['mensaje'];
    }
} else {
    $continua = FALSE;
    $resultado['mensaje'] = 'FALTAN DATOS';
}
if ($continua){
    //armo el html con el certificado
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

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->AddPage();

    //imprimo la planilla
    $image_file = '../../public/images/logo_colmed1_hr.png';

    $pdf->Image($image_file, 35, 25, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->MultiCell(0, 7, 'Expediente Nº '.$numeroExpediente.'/'.$anioExpediente, 0, 'L', false, 1, '120', '');
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->MultiCell(0, 7, 'ME Nº '.$idMesaEntrada, 0, 'L', false, 1, '120', '');
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->MultiCell(0, 7, 'Fecha: '. cambiarFechaFormatoParaMostrar($fechaMesaEntrada), 0, 'L', false, 1, '120', '');
    $pdf->Ln(8);
    //ARMAMOS EL HTML
    $pdf->SetFont('dejavusans', '', 10);
    
    $html = '<table width="100%">
                <tr>
                    <td colspan="3" style="text-align: center; font-size: large;">'.$solicitud.'</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: center; font-size: xx-large;"><b>'.$nombreEspecialidadSolicitada.'</b></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="205px">Matrícula:</td>
                    <td width="390px"><b>'.$matricula.'</b></td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="205px">Apellido y Nombre:</td>
                    <td width="390px"><b>'.$apellidoNombre.'</b></td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="205px">Estado matricular a la fecha:</td>
                    <td width="390px"><b>'.$estadoMatricular.'</b></td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="205px">Estado con Tesoreria a la fecha:</td>
                    <td width="390px"><b>'.$estadoTesoreria.'</b></td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="205px">Fecha de matriculación: </td>
                    <td width="390px"><b>'.  cambiarFechaFormatoParaMostrar($fechaMatriculacion).' (Antigüedad: '.calcular_edad($fechaMatriculacion).')</b></td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="205px">Fecha de nacimiento: </td>
                    <td width="390px"><b>'.  cambiarFechaFormatoParaMostrar($fechaNacimiento).' (Edad: '.calcular_edad($fechaNacimiento).')</b></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
            </table>
            ';
    //obtengo las especialidades que tenga otrogadas
    $resEspecialidad = $colegiadoEspecialistaLogic->obtenerEspecialidadesPorIdColegiado($idColegiado);
    if ($resEspecialidad['estado']){
        $html .= '<table width="100%" style="font-size: small;">
                    <tr>
                        <td width="70px">&nbsp;</td>
                        <td width="600px">_______________________________________________________________________________________________________________</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align: center; font-size: medium;"><b>Especialidades otorgadas</b></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="70px">&nbsp;</td>
                        <td width="200px"><b>Especialidad</b></td>
                        <td width="80px" style="text-align: center;"><b>Especialista</b></td>
                        <td width="80px" style="text-align: center;"><b>Ult. Recert.</b></td>
                        <td width="80px" style="text-align: center;"><b>Vencimiento</b></td>
                        <td width="80px" style="text-align: center;"><b>Jerarquizado</b></td>
                        <td width="80px" style="text-align: center;"><b>Consultor</b></td>
                    </tr>';
        foreach ($resEspecialidad['datos'] as $dato){
            $idColegiadoEspecialista = $dato['idColegiadoEspecialista'];
            $fechaEspecialista = cambiarFechaFormatoParaMostrar($dato['fechaEspecialista']);
            $fechaRecertificacion = cambiarFechaFormatoParaMostrar($dato['fechaRecertificacion']);
            $distritoOrigen = $dato['distritoOrigen'];
            $fechaVencimiento = cambiarFechaFormatoParaMostrar($dato['fechaVencimiento']);
            $nombreEspecialidad = $dato['nombreEspecialidad'];
            //obtengo la fecha de jerarquizado
            $resJerarquizado = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'J');
            if ($resJerarquizado['estado']){
                $fechaJerarquizado = cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']);
            } else {
                $fechaJerarquizado = NULL;
            }
            //obtengo la fecha de consultor
            $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
            if ($resConsultor['estado']){
                $fechaConsultor = cambiarFechaFormatoParaMostrar($resConsultor['fecha']);
            } else {
                $fechaConsultor = NULL;
            }
            
            $html .='<tr>
                    <td width="70px">&nbsp;</td>
                    <td>'.$nombreEspecialidad.'</td>
                    <td>'.$fechaEspecialista.'</td>
                    <td>'.$fechaRecertificacion.'</td>
                    <td>'.$fechaVencimiento.'</td>
                    <td>'.$fechaJerarquizado.'</td>
                    <td>'.$fechaConsultor.'</td>
                    </tr>';
        }

        $html .= '</table>';
    }
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);

        $html = '
            <table width="100%">
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="600px">_______________________________________________________________________________________</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="600px">Se informa al matriculado que el plazo de validez de la presente solicitud para acompa&ntilde;ar, completar y/o cumplir los requisitos formales y sustanciales que impone reglamento de especialidades y del ejercicio de las especialidades, es de un a&ntilde;o contado a partir de su ingreso. Vencido el mismo sin que se hayan cumplimentado la totalidad de los requisitos, la caducidad del riquisitoria operar&aacute; de pleno derecho y deber&aacute; presentarse una nueva solicitud. Conforme las normas vigentes en dicha instancia.</td>
                </tr>
            </table>';

        $pdf->SetFont('dejavusans', '', 11);
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);

        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Ln(6);

        $html = '
            <table width="100%">
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="600px">Dejo constancia que lo presentado ante éste Colegio de Médicos tiene carácter de declaración jurada.</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="600px">Firma: __________________________</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
            </table>';

            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

            if ($tipoTramiteEspecialista == 'X' && $inciso == 'f') {
                $html = '<table width="100%">
                        <tr>
                            <td width="70px">&nbsp;</td>
                            <td width="600px">Rinde de acuerdo a los Convenios Universitarios: SI  -  NO</td>
                        </tr>
                        </table>';
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);
                $pdf->SetFont('dejavusans', '', 8);
                $html = '<table width="100%">
                        <tr>
                            <td width="70px">&nbsp;</td>
                            <td width="200px">(Marque lo que corresponde)</td>
                        </tr>
                        </table>';
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);
                $pdf->SetFont('dejavusans', 'B', 10);
            }
        
        $html = '<table width="100%">
                <tr>
                    <td width="70px">&nbsp;</td>
                    <td width="600px">_____________________________________________________________________________________________</td>
                </tr>
                </table>';
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);
        $pdf->SetFont('dejavusans', '', 8);

        //$pdf->Ln(90);
        $pdf->MultiCell(50, 7, 'Realizó: '.$_SESSION['user'], 0, 'L', false, 0, '35', '');
        $pdf->MultiCell(80, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'L', false, 0, '140', '');
        $pdf->lastPage();

        //si es por Articulo 8, Recertificacion, Jerarquizado o Consultor, se imprimen dos hojas mas
        $a_TipoTramiteEspecialista = array('X', 'R', 'J', 'C', 'E', 'U');
        if (in_array($tipoTramiteEspecialista, $a_TipoTramiteEspecialista)) {       
            $pdf->AddPage();
            $pdf->Image($image_file, 35, 25, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->SetFont('dejavusans', '', 12);
            $pdf->Ln(30);
            $html = '<p style="text-align: justify; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A los efectos de su presentación ante las autoridades que así lo requieran, se deja constancia que '.$profesional.' ha presentado los antecedentes profesionales para la tramitación de: <b>';
            switch ($tipoTramiteEspecialista) {
                case 'E':
                    $html .= 'TITULO DE ESPECIALISTA en '.$nombreEspecialidadSolicitada.'</b>';
                    break;

                case 'U':
                    $html .= 'TITULO DE ESPECIALISTA en '.$nombreEspecialidadSolicitada.'</b>';
                    break;

                case 'X':
                    $html .= 'TITULO DE ESPECIALISTA en '.$nombreEspecialidadSolicitada.' (Art. 8)</b>';
                    if ($inciso == 'f' || $inciso == 'd') {
                        $html .= ', (otorgado por este Colegio de Médicos de la Pcia. De Buenos Aires – Distrito I)';
                    }
                    break;
                case 'J':
                    $html .= 'TITULO DE ESPECIALISTA JERARQUIZADO en '.$nombreEspecialidadSolicitada.'</b>';
                    break;
                
                case 'C':
                    $html .= 'TITULO DE ESPECIALISTA CONSULTOR en '.$nombreEspecialidadSolicitada.'</b>';
                    break;
                default:
                    $html .= 'RECERTIFICACION de '.$nombreEspecialidadSolicitada.'</b>';

                    break;
            }
            $html .= ', bajo el Nº de Expte.: '.$numeroExpediente.'/'.$anioExpediente.' con fecha '.cambiarFechaFormatoParaMostrar($fechaMesaEntrada).'<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dicha presentación se encuentra a la espera de su evaluación por parte de la Comisión de Especialidades Médicas y posterior aprobación del Consejo Directivo de este Colegio de Médicos Distrito I.</p>';
            /* se elimina esta parte del texto el 1/4/2025 a pedido de la mesa
                    <br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;La presente se extiende en el marco de la Emergencia Sanitaria generada por la pandemia Covid-19.
            */
            $html .= '<br><br><br><br>';

            $html .= '<br><br><br>
                    COLEGIO DE MEDICOS DE LA PROVINCIA DE BS. AS. DISTRITO I
                    <br>
                    LA PLATA, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y');
            $html .= '<br><br>';

            //imprimo las firmas
            $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1); 
            if ($resFirmante['estado']) {
                $firmante = $resFirmante['datos'];
                $presidente = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
                $jpgfile1 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                    
                $htmlFirma1 = '<td style="text-align:center;" >
                                <img src="'.$jpgfile1.'" border="0" height="120" width="" />
                                <label style="font-size: 10px;">'.$presidente.'</label><br>
                                <label style="font-size: 8px;">Presidente<br>Colegio de Médicos - Distrito I</label>
                            </td>';
            } else {
                $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
            }
            
            $resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
            if ($resFirmante['estado']) {
                $firmante = $resFirmante['datos'];
                $secretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
                $jpgfile2 = '../firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                    
                $htmlFirma2 = '<td style="text-align:center;" >
                                <img src="'.$jpgfile2.'" border="0" height="120" width="" />
                                <label style="font-size: 10px;">'.$secretario.'</label><br>
                                <label style="font-size: 8px;">Secretario General<br>Colegio de Médicos - Distrito I</label>
                            </td>';
            } else {
                $htmlFirma2 = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
            }

            $imgSello = '../../public/images/SELLO.png';
            $html .= '<table>
                    <tr>'
                        .$htmlFirma2.
                        '<td style="text-align:center;" >
                            <img src="'.$imgSello.'" border="0" height="140" width="" />
                        </td>'
                        .$htmlFirma1.
                    '</tr>
                    </table';

            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);

            if ($tipoTramiteEspecialista == "X" && ($inciso == 'f' || $inciso == 'd')) {
                //recibido
                /*
                //se elimina la declarion jurada desde el 1/8/2022, a pedido de especialidades
                $pdf->AddPage();
                $pdf->Image($image_file, 35, 25, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->Ln(30);
                $html = '<p style="text-align: rigth;">La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').' </p><br><br>'; 
                */
                /*
                //se reemplaza el 25/6/2021
                $html .= '<p style="text-align: justify; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Declaro bajo juramento que solicito el Título de Especialista en <b>'.$nombreEspecialidadSolicitada.'</b>, a ese Distrito I, desistiendo expresamente de solicitar en el futuro que el mismo sea convalidado/homologado por cualquier institución con el cual el Colegio de Médicos tenga convenio vigente.</p>
                    <br><br><br><br><br><br>
                    Firma: _____________________
                    <br><br>
                    Aclaraci&oacute;n:_____________________________________________________
                    <br><br>
                    DNI: _______________________';
                */

                /*
                //se elimina la declarion jurada desde el 1/8/2022, a pedido de especialidades
                $html .= '<p style="text-align: justify; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Quien suscribe declara por medio de la presente que, con motivo de la solicitud efectuada para obtener la autorización por parte del Colegio de Médicos de la Pcia. de Buenos Aires - Distrito I para el uso del Título de Especialista, me han sido explicados debidamente los alcances y requisitos que impone el REGLAMENTO DE LAS ESPECIALIZACIONES Y DEL EJERCICIO DE LAS ESPECIALIDADES vigente. En tal sentido he comprendido que al peticionar el uso/reconocimiento del título de especialista, con o sin rendición de examen en esa institución, según corresponda, no tengo derecho y a todo evento desisto expresamente de requerir la expedición del título o diploma en la misma especialidad con intervención de una Mesa Examinadora y/o su convalidación u homologación conjunta por cualquier Universidad, Institución o Entidad Científica con la que este Colegio Profesional tenga convenio a tal fin o lo celebre en el futuro.</p>
                    <br><br><br><br><br><br>
                    Firma: _____________________
                    <br><br>
                    Aclaraci&oacute;n:_____________________________________________________
                    <br><br>
                    DNI: _______________________';
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);
            */
            }
            //si es por convenio UNLP, imprimo declaracion jurada
            if ($tipoTramiteEspecialista == "U") {
                //recibido
                $pdf->AddPage();
                $pdf->Image($image_file, 35, 25, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $pdf->SetFont('dejavusans', '', 12);
                $pdf->Ln(30);
                $html = '<p style="text-align: rigth;">La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').' </p><br><br>'; 
                $html .= '<p style="text-align: justify; ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Declaro bajo juramento que solicito el Título de Especialista en <b>'.$nombreEspecialidadSolicitada.'</b>, bajo prueba examinadora por convenio con aval de la UNLP, y que no poseo ninguna carrera de postgrado incompleta sin su correspondiente prueba final, en la Facultad de Ciencias Medicas de la Universidad Nacional de la Plata.</p>
                    <br><br><br><br><br><br>
                    Firma: _____________________
                    <br><br>
                    Aclaraci&oacute;n:_____________________________________________________
                    <br><br>
                    DNI: _______________________';

                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'L', true);
            }

        }

        ob_clean();
        /* Finalmente generamos el PDF */
        $destination = 'I';
        $nombreArchivo = 'Expediente_.pdf';
        $pdf->Output($nombreArchivo, $destination);        
} else {
    echo "error en los datos ingresados";
}
