<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
require_once ('../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();
require_once ('../dataAccess/colegiadoCargoLogic.php');
$colegiadoCargoLogic = new colegiadoCargoLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../dataAccess/colegiadoFapLogic.php');
$colegiadoFapLogic = new colegiadoFapLogic();
require_once ('../dataAccess/presidenteLogic.php');
$presidenteLogic = new presidenteLogic();
require_once ('../dataAccess/notaCambioDistritoLogic.php');
$notaCambioDistritoLogic = new notaCambioDistritoLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

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

                //MARCA DE AGUA 
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);
                $img_file2 = '../public/images/fondoCertificado.png';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
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
if (isset($_POST['idColegiado']) && isset($_POST['idTipoCertificado'])) {
    $idColegiado = $_POST['idColegiado'];
    $idTipoCertificado = $_POST['idTipoCertificado'];

    //si es para especialista, verifico que esté seleccioanda la especialidad
    if ($idTipoCertificado == 3) {
        if (isset($_POST['idColegiadoEspecialista'])) {
            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
        } else {
            $resultado['mensaje'] = "MAL ESPECIALIDAD";
            $continua = FALSE;
        }
    } else {
        $idColegiadoEspecialista = NULL;
    }
    
    //si es para cambio de distrito, verifico que venga seleccioando el distrito y la nota
    if ($idTipoCertificado == 1) {
        if (isset($_POST['distrito']) && isset($_POST['idNotaCambioDistrito'])) {
            $distrito = $_POST['distrito'];
            $idNotaCambioDistrito = $_POST['idNotaCambioDistrito'];
        } else {
            $resultado['mensaje'] = "MAL DISTRITO";
            $continua = FALSE;
        }
    } else {
        $distrito = NULL;
        $idNotaCambioDistrito = NULL;
    }

    //si envia por mail, verifica que esta cargado el mail
    if (isset($_POST['enviaMail'])) {
        $enviaMail = $_POST['enviaMail'];
        if ($enviaMail == 'S' && isset($_POST['mail'])) {
            $mail = $_POST['mail'];
            $mailOriginal = $_POST['mailOriginal'];
            if ($mail <> $mailOriginal) {
                //actualizo contacto
                $resContacto = $colegiadoContactoLogic->modificarMail($idColegiado, $mail);
            }
        } else {
            $mail = NULL;
        }
    } else {
        $resultado['mensaje'] = "MAL ENVIA MAIL. ".$_POST['enviaMail'];
        $continua = FALSE;
    }
    
    $presentado = strtoupper($_POST['presentado']);
    $estadoConTesoreria = $_POST['estadoConTesoreria'];
    $cuotasAdeudadas = $_POST['cuotasAdeudadas'];
    $conFirma = $_POST['conFirma'];
    $conLeyendaTeso = $_POST['conLeyendaTeso'];
    $codigoDeudor = $_POST['codigoDeudor'];
    $tipoCertificado = $_POST['tipoCertificado'];
} else {
    $resultado['mensaje'] = "ERROR EN LOS DATOS INGRESADOS";
    $continua = FALSE;
}

if ($continua){
    $resultado = $colegiadoCertificadosLogic->agregarSolicitudCertificado($idColegiado, $idTipoCertificado, $presentado, $distrito, $codigoDeudor, $cuotasAdeudadas, $idNotaCambioDistrito, $conFirma, $conLeyendaTeso, $idColegiadoEspecialista, $enviaMail);
    if ($resultado['estado']) {
        //imprimo el certificado
        $resultado['mensaje'] = "Certificado generado";
    }
} else {
    $resultado['icono'] = "glyphicon glyphicon-remove";
    $resultado['clase'] = "alert alert-error";
}

?>
<body onLoad="document.forms['myForm'].submit()">
    <?php
    if ($resultado['estado']) {
        //armo el html con el certificado
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $estadoMatricular = $colegiado['estado'];
            switch ($colegiado['tipoEstado']) {
                case 'A':
                    $tipoEstadoMatricular = 'ACTIVO';
                    $conEstadoTesoreria = TRUE;
                    if ($cuotasAdeudadas>=1 && $cuotasAdeudadas<=5 ) {
                        $conEstadoTesoreria = FALSE;
                    }
                    break;

                case 'I':
                    $tipoEstadoMatricular = 'Inscripto';
                    $conEstadoTesoreria = FALSE;
                    break;

                case 'C':
                    $tipoEstadoMatricular = 'BAJA('.$colegiado['movimientoCompleto'].')';
                    $conEstadoTesoreria = TRUE;
                    break;

                case 'F':
                case 'J':
                    $tipoEstadoMatricular = 'BAJA('.$colegiado['movimientoCompleto'].')';
                    $conEstadoTesoreria = FALSE;
                    break;

                default:
                    $tipoEstadoMatricular = '-';
                    $conEstadoTesoreria = TRUE;
                    break;
            } 
            $libro = $colegiado['tomo'];
            $folio = $colegiado['folio'];
            $tipoDocumento = $colegiado['tipoDocumento'];
            $numeroDocumento = $colegiado['numeroDocumento'];
            $fechaMatriculacion = cambiarFechaFormatoParaMostrar($colegiado['fechaMatriculacion']);
            $fechaNacimiento = cambiarFechaFormatoParaMostrar($colegiado['fechaNacimiento']);
            
            $resColegiadoTitulo = $colegiadoLogic->obtenerTitulosPorColegiado($idColegiado);
            if ($resColegiadoTitulo['estado']) {
                $colegiadoTitulo = $resColegiadoTitulo['datos'];
                $fechaTitulo = cambiarFechaFormatoParaMostrar($colegiadoTitulo['fechaTitulo']);
                $tituloColegiado = $colegiadoTitulo['tipoTitulo'];
                $universidad = $colegiadoTitulo['universidad'];
            }
        ?>
            <form name="myForm"  method="POST" target="_blank" action="colegiado_certificados.php?idColegiado=<?php echo $idColegiado;?>">
            <?php
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

            $alturaLinea = 7;
            //imprimo la planilla
            $pdf->Ln(5);
            $pdf->MultiCell(0, $alturaLinea, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 0, '50', '');
            $pdf->Ln(5);

            $conNota = TRUE;
            switch ($idTipoCertificado) {
                case 1: //cambio de distrito
                    $resPresidenteDistrito = $presidenteLogic->obtenerPresidenteDistrito($distrito);
                    if ($resPresidenteDistrito['estado']) {
                        $presidenteDistrito = $resPresidenteDistrito['datos']['nombre'];
                    } else {
                        $presidenteDistrito = '';
                    }
                    $html = 'Señor<br>'.
                        'Presidente del Consejo Directivo<br>'.
                        'del Colegio de Médicos, Distrito '.$distrito.'<br>'.
                        'Doctor '.$presidenteDistrito.'<br><br>'.
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        'Tengo el agrado de dirigirme a usted, a los efectos de remitirle los datos '.
                        'registrados en este Colegio de Médicos Distrito I pertenecientes al profesional médico '.
                        '<b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b> '.
                        'con motivo de su inscripción en ese Distrito.';
                    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(5);
                    
                    $conDetalle = TRUE;
                    break;

                case 3: //especialista y recertificacion
                    $pdf->Ln(5);
                    $resEspecialista = $colegiadoEspecialistaLogic->obtenerColegiadoEspecialistaPorId($idColegiadoEspecialista);
                    if ($resEspecialista['estado']) {
                        $especialista = $resEspecialista['datos'];
                        $especialistaDistrito = $especialista['distritoOrigen'];
                        $tipoEspecialidad = $especialista['tipoEspecialidad']; 
                        $especialidad = $especialista['nombreEspecialidad'];
                        $fechaEspecialista = $especialista['fechaEspecialista'];
                        $fechaRecertificacion = $especialista['fechaRecertificacion'];
                        $fechaVencimiento = $especialista['fechaVencimiento'];
                        
                        $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                            'A pedido del interesado y a los efectos que mejor corresponda se deja '.
                            'expresa constancia que el Consejo Directivo del Colegio de Médicos Distrito '.$especialistaDistrito.' '.
                            'ha resuelto autorizar al uso del Título de '.$tipoEspecialidad.' en <b>'.
                            $especialidad.'</b> al profesional médico <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b> '.
                            'matrícula Nº <b>'.$matricula.'</b> con fecha <b>'.  cambiarFechaFormatoParaMostrar($fechaEspecialista).'</b>.-';
                        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                        $pdf->Ln(5);

                        //si recertificó lo imprimo
                        //if ($fechaEspecialista < $fechaRecertificacion) {
                        //    $pdf->MultiCell(0, $alturaLinea, 'Recertificación: '.  cambiarFechaFormatoParaMostrar($fechaRecertificacion), 0, 'C', false, 1, '', '', true);
                        //}
                            
                        //Fecha de Caducidad, si es del I, verifica que la fecha sea mayor a 27/09/1994 y no sea Consultor
                        if ($especialistaDistrito == 1) {
                            $resFechaPorTipo = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
                            if ($resFechaPorTipo['estado']) {
                                $fechaCaducidad = NULL;
                            } else {
                                if ($fechaEspecialista > '1994-09-27') {
                                    $fechaCaducidad = $fechaVencimiento;
                                } else {
                                    $fechaCaducidad = NULL;
                                }
                            }
                        } else {
                            $fechaCaducidad = $fechaVencimiento;
                        }
                        
                        //si tiene caducidad la imprimo
                        if (isset($fechaCaducidad)) {
                            $fechaActual = date('Y-m-d');
                            if ($fechaActual > $fechaVencimiento) {
                                //verifica si esta dentro de los 2 años permitidos a Recertificar
                                $dia = date($fechaVencimiento, 'd');
                                $mes = date($fechaVencimiento, 'm');
                                $anio = date($fechaVencimiento, 'Y') + 2;
                                $fechaVencimientoMasDos = $anio.'-'.$mes.'-'.$dia;
                                if ($fechaActual > $fechaVencimientoMasDos) {
                                    $caduca = 'Especialidad No Recertificada';
                                } else {
                                    $caduca = 'Especialidad con Recertificación en Trámite';
                                }
                            } else {
                                $caduca = 'Recertificada hasta: '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                $dia = date($fechaVencimiento, 'd');
                                $mes = date($fechaVencimiento, 'm');
                                $anio = date($fechaVencimiento, 'Y') - 5;
                                $fechaVencimientoMenosCinco = $anio.'-'.$mes.'-'.$dia;
                                if ($fechaEspecialista == $fechaVencimientoMenosCinco) {
                                    $caduca = 'Certificada hasta: '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                }
                            }
                            $pdf->MultiCell(0, $alturaLinea, $caduca, 0, 'C', false, 1, '', '', true);
                        }

                        //imprimo el estado matricular
                        $pdf->MultiCell(0, $alturaLinea, 'Estado de la matrícula: '.$tipoEstadoMatricular, 0, 'L', false, 1, '', '', true);
                        //imprimo el estado con tesoreria
                        $pdf->MultiCell(0, $alturaLinea, 'Situación con tesorería: '.$estadoConTesoreria, 0, 'L', false, 1, '', '', true);
                    } else {
                        $pdf->writeHTMLCell(0, 0, '', '', 'ERROR: '.$resEspecialista['mensaje'], 0, 1, 0, true, 'J', true);
                    }
                    $conDetalle = FALSE;

                    break;

                case 5: //Para comisiones
                    $pdf->SetFont('dejavusans', 'B', 10);
                    $pdf->MultiCell(0, $alturaLinea, 'CERTIFICADO PARA COMISIONES', 0, 'C', false, 1, '', '', true);
                    $pdf->SetFont('dejavusans', '', 10);
                    $pdf->Ln(10);
                    $pdf->MultiCell(0, $alturaLinea, 'Comisión: '.$presentado, 0, 'L', false, 1, '', '', true);
                    $pdf->Ln(5);
                    $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                            'Tengo el agrado de dirigirme a usted, a los efectos de remitirle los datos '.
                            'registrados en este Colegio de Médicos Distrito I pertenecientes al profesional médico '.
                            '<b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b>';
                    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(5);
                    $conDetalle = TRUE;
                    $conNota = FALSE;
                    $conDomicilio = TRUE;
                    break;
                
                case 6: //a todo efecto
                case 10: //Para la CAJA
                case 11: //Para la AMP
                    if ($estadoMatricular == 2 || $estadoMatricular == 3) {
                        $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                            'A los efectos de ser presentado ante <b>'.$presentado.'</b> se deja expresa constancia que la <b>M.P. '.$matricula.'</b> '.
                            'perteneciente al profesional médico, <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b>'.
                            'se encuentra dada de <b>BAJA</b> en este Colegio de Médicos de la Pcia. de Bs. As. Distrito I. '.
                            'Registrándose a la fecha los siguientes antecedentes:';
                    } else {
                        $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                            'A los efectos de ser presentado ante <b>'.$presentado.'</b> se deja expresa constancia que, del '.
                            'profesional médico, <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b>'.
                            'se registran en este Colegio de Médicos de la Pcia. de Bs. As. Distrito I, a la fecha los siguientes antecedentes:';
                    }
                    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(5);

                    $conDetalle = TRUE;
                    if ($idTipoCertificado == 6) {
                        $conDomicilio = FALSE;
                    } else {
                        if ($idTipoCertificado == 5) {
                            $conNota = FALSE;
                        }
                        $conDomicilio = TRUE;
                    }
                    break;

                case 9: //FAP completo
                    $html = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        'Certifico que el profesional médico <b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b>'.
                        'M.P. Nº <b>'.$matricula.'</b> , matriculado en este Distrito I del Colegio de Médicos goza al día de la '.
                        'fecha de los beneficios otorgados por el Articulo 5º inciso 17) del Decreto-Ley 5413/58, en '.
                        'la redacción dada por la Ley 12043, para eventuales acciones judiciales que tengan como '.
                        'causa los efectos del ejercicio profesional y el acto médico personal y particular realizado '.
                        'dentro de las normas y reglamentos que regulan el ejercicio de la medicina y los cánones '.
                        'médicos habituales, en la jurisdicción de la provincia de Buenos Aires.'.
                        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                        'Se deja expresa constancia que este certificado revela la situación del profesional al '.
                        'día de la fecha para resultar beneficiario del sistema, sin que refleje en forma alguna la '.
                        'situación del mismo con anterioridad o posterioridad a la emisión del presente.'.
                        '<b> "Artículo 88 ter- Es acto contrario a la ética y constituye falta  grave (con  relación '.
                        'a un establecimiento privado, a uno estatal o a una institución de derecho público '.
                        'no estatal), la acción por la cual el Director médico, el médico propietario, el '.
                        'médico que ejerza alguna función jerárquica en la institución o a los médicos '.
                        'directores o gerentes exigieren, reclamaren o, de cualquier forma pidieran la '.
                        'contratación de un seguro de responsabilidad civil profesional a un colega que '.
                        'trabaje o se desempeñe en ese establecimiento o en la órbita de su sistema '.
                        'asistencial, cualquiera sea la forma contractual en que se desarrolle ese trabajo '.
                        'profesional. También se considerará infracción a la misma exigencia cuando se '.
                        'estableciere como requisito para acceder a la relación de trabajo médico o '.
                        'locación de servicio. Las actuaciones Sumariales podrán ser promovidas de '.
                        'oficio por los Consejos Directivos Distritales. (Res.C.S. Nº542/04)". DECRETO '.
                        'LEY 5413/58, Pág. 68.- </b>'.
                        
                    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(10);
                    
                    $pdf->MultiCell(0, $alturaLinea, 'Para ser presentado ante quien corresponda.', 0, 'L', false, 1, '', '', true);
                    
                    $conDetalle = FALSE;
                    $conNota = FALSE;
                    
                    break;

                default:
                    break;
            }
            
            if ($conDetalle) {
                $indice = 1;
                $pdf->MultiCell(0, $alturaLinea, $indice.'. Médico/a matriculado/a bajo el Nº '.$matricula.' , registrado en el Libro '.$libro.' Folio '.$folio, 0, 'L', false, 1, '', '', true);
                $indice += 1;
                $pdf->MultiCell(0, $alturaLinea, $indice.'. Tipo y Número de Documento: '.$tipoDocumento.' '.$numeroDocumento, 0, 'L', false, 1, '', '', true);
                $indice += 1;
                $pdf->MultiCell(0, $alturaLinea, $indice.'. Fecha de Matriculación: '.$fechaMatriculacion, 0, 'L', false, 1, '', '', true);
                $indice += 1;
                $pdf->MultiCell(0, $alturaLinea, $indice.'. Fecha de Nacimiento: '.$fechaNacimiento, 0, 'L', false, 1, '', '', true);
                
                //si lleva imprimo los datos del domicilio actual y consultorio
                if ($conDomicilio) {
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
                        
                        $domicilioCompleto .= ' - '.$domicilio['nombreLocalidad'].' ('.$domicilio['codigoPostal'].')';
                        
                        $indice += 1;
                        $pdf->MultiCell(0, $alturaLinea, $indice.'. Domicilio: '.$domicilioCompleto, 0, 'L', false, 1, '', '', true);
                    }
                    
                    //datos de contacto
                    $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                    if ($resContacto['estado']) {
                        $contacto = $resContacto['datos'];
                        $telefonos = $contacto['telefonoFijo'].' - '.$contacto['telefonoMovil'];
                        
                        $indice += 1;
                        $pdf->MultiCell(0, $alturaLinea, $indice.'. Teléfonos: '.$telefonos, 0, 'L', false, 1, '', '', true);
                    }
                    
                    //datos de consultorio
                    $resConsultorio = $colegiadoDomicilioLogic->obtenerDomicilioProfesional($idColegiado);
                    if ($resConsultorio['estado']) {
                        $consultorio = $resConsultorio['datos'];
                        $consultorios = $consultorio['domicilio'].' - '.$consultorio['nombreLocalidad'];
                        
                        $indice += 1;
                        $pdf->MultiCell(0, $alturaLinea, $indice.'. Domicilio profesional: '.$consultorios, 0, 'L', false, 1, '', '', true);
                    }
                }
                
                $indice += 1;
                $pdf->MultiCell(0, $alturaLinea, $indice.'. Título: '.$tituloColegiado.' Otorgado por: '.$universidad.' con fecha: '.$fechaTitulo, 0, 'L', false, 1, '', '', true);
                if (isset($bloqueoTitulo)) {
                    $pdf->MultiCell(0, $alturaLinea, 'Con Bloqueo de Título desde el  '.$fechaBloqueoTitulo, 0, 'L', false, 1, '', '', true);
                }

                $indice += 1;
                $resEspecialidades = $colegiadoEspecialistaLogic->obtenerEspecialidadesPorIdColegiado($idColegiado);
                if ($resEspecialidades['estado']) {
                    $pdf->SetFont('dejavusans', '', 10);
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Especialidad: ', 0, 'L', false, 0, '', '', true);
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->MultiCell(0, $alturaLinea, 'Otorgó', 0, 'L', false, 0, '77', '', true);
                    $pdf->MultiCell(0, $alturaLinea, 'Con fecha', 0, 'L', false, 0, '90', '', true);
                    $pdf->MultiCell(0, $alturaLinea, 'Jerarquizado', 0, 'L', false, 0, '108', '', true);
                    $pdf->MultiCell(0, $alturaLinea, 'Consultor', 0, 'L', false, 0, '130', '', true);
                    $pdf->MultiCell(0, $alturaLinea, 'Caducidad', 0, 'L', false, 0, '150', '', true);
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->Ln(5);
                    
                    foreach ($resEspecialidades['datos'] as $row) {
                        $pdf->MultiCell(0, $alturaLinea, $row['nombreEspecialidad'], 0, 'L', false, 0, '', '', true);
                        $estadoEspecialidad = $row['estado'];
                        $fechaEspecialista = $row['fechaEspecialista'];
                        $fechaVencimiento = $row['fechaVencimiento'];
                        $idEspecialidad = $row['idEspecialidad'];

                        if ($estadoEspecialidad == 'A') {
                            if ($row['tipoespecialista'] == 8) {
                                $otorgadaPor = 'NACIÓN';
                            } else {
                                if ($row['tipoespecialista'] == 0) {
                                    $row['tipoespecialista'] = 1;
                                }
                                $otorgadaPor = 'Dist. '.$row['tipoespecialista'];
                            }
                            $pdf->MultiCell(0, $alturaLinea, $otorgadaPor, 0, 'L', false, 0, '80', '', true);
                            $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaEspecialista), 0, 'L', false, 0, '90', '', true);
                            $idColegiadoEspecialista = $row['idColegiadoEspecialista'];

                            //verifico si tiene jerarquizado y consultor
                            $verVencimiento = TRUE;
                            $resJerarquizado = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'J');
                            if ($resJerarquizado['estado']) {
                                $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']), 0, 'L', false, 0, '110', '', true);
                                $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
                                if ($resConsultor['estado']) {
                                    $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resConsultor['fecha']), 0, 'L', false, 0, '130', '', true);
                                    $verVencimiento = FALSE;
                                } else {
                                    $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '130', '', true);
                                }
                            } else {
                                $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '110', '', true);
                                $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '130', '', true);
                            }

                            if ($verVencimiento) {
                                if (isset($fechaVencimiento)) {
                                    if (date('Y-m-d') > $fechaVencimiento) {
                                        //verifica si esta dentro de los 2 años permitidos a Recertificar
                                        $dia = date($fechaVencimiento, 'd');
                                        $mes = date($fechaVencimiento, 'm');
                                        $anio = date($fechaVencimiento, 'Y') + 2;
                                        $fechaVencimientoMasDos = $anio.'-'.$mes.'-'.$dia;
                                        if (date('Y-m-d') > $fechaVencimientoMasDos) {
                                            $caduca = 'No Recertificada';
                                        } else {
                                            $caduca = 'Recertificación en Trámite';
                                        }
                                    } else {
                                        $caduca = 'Recertificada hasta: '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                        $dia = date($fechaVencimiento, 'd');
                                        $mes = date($fechaVencimiento, 'm');
                                        $anio = date($fechaVencimiento, 'Y') - 5;
                                        $fechaVencimientoMenosCinco = $anio.'-'.$mes.'-'.$dia;
                                        if ($fechaEspecialista == $fechaVencimientoMenosCinco) {
                                            $caduca = 'Certificada hasta: '.cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                        }
                                    }
                                    $pdf->MultiCell(0, $alturaLinea, $caduca, 0, 'L', false, 0, '150', '', true);
                                } 
                            }
                        } else {
                            //busco la resolucion de la baja
                            $resBaja = $colegiadoEspecialistaLogic->verBajaEspecialista($idColegiado, $idEspecialidad, $fechaEspecialista);
                            if ($resBaja['estado']) {
                                $baja = $resBaja['datos'];
                                $pdf->SetFont('dejavusans', 'B', 8);
                                $pdf->MultiCell(0, $alturaLinea, 'Baja por Res. '.$baja['numero'].' de fecha '. cambiarFechaFormatoParaMostrar($baja['fecha']), 0, 'L', false, 0, '100', '', true);
                            } else {
                                $resBaja['mensaje'];
                            }
                        }
                        $pdf->Ln(5);
                    }
                } else {
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Especialidad: NINGUNA.', 0, 'L', false, 0, '', '', true);
                }
                $pdf->SetFont('dejavusans', '', 10);

                //imprimir cargos en el colegio
                /*
                $indice += 1;
                $resCargos = $colegiadoCargoLogic->obtenerCargosColegioPorColegiado($idColegiado);
                if ($resCargos['estado']) {
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Cargos desempeñados en el Colegio: ', 0, 'L', false, 0, '', '', true);
                    foreach ($resCargos['datos'] as $row) {
                        $pdf->MultiCell(0, $alturaLinea, $row['nombreCargo'].' desde el'.cambiarFechaFormatoParaMostrar($row['fechaDesde']).' hasta el '.cambiarFechaFormatoParaMostrar($row['fechaHasta']), 0, 'L', false, 1, '', '', true);
                    }
                } else {
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Cargos desempeñados en el Colegio: NINGUNO.', 0, 'L', false, 0, '', '', true);
                }
                */

                //imprimir sanciones
                $indice += 1;
                $conSancion = FALSE;
                $resSanciones = $colegiadoSancionLogic->obtenerSancionesPorIdColegiado($idColegiado);
                if ($resSanciones['estado']) {
                    $i = 0;
                    foreach ($resSanciones['datos'] as $row) {
                        $fechaDesde = $row['fechaDesde'];
                        $fechaHasta = $row['fechaHasta'];
                        $ley = $row['ley'];
                        $articulo = $row['articulo'];
                        $sanciones = NULL;
                        switch ($articulo) {
                            case '52c':
                                // le sumo 10 años a la fecha de la sancion para ver si caducó
                                $dia = date($fechaDesde, 'd');
                                $mes = date($fechaDesde, 'm');
                                $anio = date($fechaDesde, 'Y') + 10;
                                $fechaMasDiez = $anio.'-'.$mes.'-'.$dia;
                                if ($fechaDesde >= $fechaMasDiez) {
                                    $sanciones = $ley .' '. cambiarFechaFormatoParaMostrar($fechaDesde) .' al '. cambiarFechaFormatoParaMostrar($fechaHasta) .' Art.:'. $articulo; 
                                }
                                break;

                            case '40c':
                                $fechaActual = date('Y-m-d'); 
                                if ($fechaDesde<=$fechaActual && $fechaHasta>=$fechaActual) {
                                    $sanciones = $ley .' '. cambiarFechaFormatoParaMostrar($fechaDesde) .' al '. cambiarFechaFormatoParaMostrar($fechaHasta) .' Art.:'. $articulo; 
                                }
                                break;

                            default:
                                break;
                        }
                        if (isset($sanciones)) {
                            if ($i == 0) {
                                $pdf->MultiCell(0, $alturaLinea, $indice.'. Sanciones éticas disciplinarias: ', 0, 'L', false, 1, '', '', true);
                            }
                            $pdf->MultiCell(0, $alturaLinea, $sanciones, 0, 'L', false, 1, '', '', true);
                            $i += 1;
                            $conSancion = TRUE;
                        }
                    }
                } 

                if (!$conSancion) {
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Sanciones éticas disciplinarias: NINGUNA', 0, 'L', false, 1, '', '', true);
                }

                //imprimir movimientos matriculares
                $indice += 1;
                $resMovimiento = $colegiadoMovimientoLogic->obtenerMovimientosPorIdColegiado($idColegiado);
                if ($resMovimiento['estado']) {
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Movimientos matriculares:', 0, 'L', false, 1, '', '', true);
                    
                    if (count($resMovimiento['datos'])>1) {
                        foreach ($resMovimiento['datos'] as $row) {
                            switch ($row['idTipoMovimietno']) {
                                case 5:
                                    $movimiento = 'Colegiado en Distrito I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').' (Baja del Distrito '.$row['distritoCambio'].'). ';
                                    break;

                                case 6:
                                    $movimiento = 'Egreso Definitivo del Distrito I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').', colegiado en Dist.'.$row['distritoCambio'].'. ';
                                    break;

                                case 8:
                                    $movimiento = 'Colegiado en Dist.'.$row['distritoCambio'].' Inscripto en Dist.I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').'. ';
                                    break;

                                case 9:
                                    $movimiento = 'Cancelación de matrícula por Art.40 inc C Decreto Ley 5413/58. Desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y');
                                    if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                        $movimiento .= ' al '.cambiarFechaFormatoParaMostrar($row['fechaHasta'],'d-m-y').'.-';
                                    }
                                    break;

                                case 10:
                                    $movimiento = 'Colegiado en Dist.I, Inscripto en Dist.'.$row['distritoCambio'].' desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y');
                                    if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                        $movimiento .= ' hasta el '.cambiarFechaFormatoParaMostrar($row['fechaHasta'],'d-m-y').'.-';
                                    }
                                    break;

                                default:
                                    $movimiento = $row['detalleMovimiento'].' desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde']);
                                    if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                        $movimiento .= ' al '.cambiarFechaFormatoParaMostrar($row['fechaHasta']).'.-';
                                    }
                                    break;
                            }
                            $pdf->MultiCell(0, $alturaLinea,$movimiento, 0, 'L', false, 1, '20', '', true);
                        }
                    } else {
                        $unMovimiento = $resMovimiento['datos'][0];
                        switch ($unMovimiento['idTipoMovimietno']) {
                            case 5:
                                $movimiento = 'Colegiado en Distrito I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').' (Baja del Distrito '.$unMovimiento['distritoCambio'].'). ';
                                break;

                            case 6:
                                $movimiento = 'Egreso Definitivo del Distrito I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').', colegiado en Dist.'.$unMovimiento['distritoCambio'].'. ';
                                break;

                            case 8:
                                $movimiento = 'Colegiado en Dist.'.$row['distritoCambio'].' Inscripto en Dist.I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').'. ';
                                break;

                            case 9:
                                $movimiento = 'Cancelación de matrícula por Art.40 inc C Decreto Ley 5413/58. Desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y');
                                if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                    $movimiento .= ' al '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta'],'d-m-y').'.-';
                                }
                                break;

                            case 10:
                                $movimiento = 'Colegiado en Dist.I, Inscripto en Dist.'.$unMovimiento['distritoCambio'].' desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y');
                                if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                                    $movimiento .= ' hasta el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta'],'d-m-y').'.-';
                                }
                                break;

                            default:
                                $movimiento = $unMovimiento['detalleMovimiento'].' desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde']);
                                if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                                    $movimiento .= ' al '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta']).'.-';
                                }
                                break;
                        }
                        $pdf->MultiCell(0, $alturaLinea,$movimiento, 0, 'L', false, 1, '20', '', true);
                    }
                } else {
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Movimientos matriculares: NO REGISTRA', 0, 'L', false, 1, '', '', true);
                }

                //Situación con tesorería:
                //    !si es fallecido, jubilado, inscripto -> no se imprime la leyenda de Situacion Con Tesoreria
                if ($conEstadoTesoreria) {
                    $indice += 1;
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Situación con tesorería: '.$estadoConTesoreria, 0, 'L', false, 1, '', '', true);
                }
                
                //Si tiene antecedentes en fap lo imprime
                $dia = date('d');
                $mes = date('m');
                $anio = date('Y') - 10;
                $fechaFap = $anio.'-'.$mes.'-'.$dia;
                $indice += 1;
                $pdf->MultiCell(0, $alturaLinea, $indice.'. Registra antecedentes en el FAP: ', 0, 'L', false, 0, '', '', true);
                if ($colegiadoFapLogic->colegiadoTieneFap($idColegiado, $fechaFap)) {
                    $enFap = 'SI';
                } else {
                    $enFap = 'NO';
                }
                $pdf->SetFont('dejavusans', 'B', 10);
                $pdf->MultiCell(0, $alturaLinea, $enFap, 0, 'L', false, 1, '82', '', true);                    
                $pdf->SetFont('dejavusans', '', 10);
                
                
            } //fin conDetalle=TRUE

            if ($conNota) {
                //imprimir NOTA
                $pdf->Ln(5);
                if ($idTipoCertificado == 1) {
                    $resNotaCambio = $notaCambioDistritoLogic->obtenerNotaCambioDistritoPorId($idNotaCambioDistrito);
                    if ($resNotaCambio['estado']) {
                        $notaCambioDistrito = $resNotaCambio['datos']['nota'];
                        $html = 'NOTA: '.$notaCambioDistrito.'<br><br>'; 
                    } else {
                        $html = '';
                    }

                    $html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                            'Sin otro particular saludo a usted muy atentamente.-';
                } else {
                    $html = 'NOTA: conforme lo expresa el Artículo 7º del Reglamento de Matriculación: '.
                        '"... el médico que ejerza en otro u otros distritos deberá presentarse a los respectivos '.
                        'Colegios para la debida constancia administrativa y el registro correspondiente..",.';
                }
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
            }
            
            //imprimo sello y firma
            if ($conFirma == 'S') {
                $img = '../public/images/SELLO.png';
                $resFirmante = obtenerFirmaSecretarioGeneral();
                if ($resFirmante['estado']) {
                    $firmante = $resFirmante['datos'];
                    $secretario = 'Dr. '. ucfirst($firmante['nombre']) .' '. ucfirst($firmante['apellido']);
//                    $fileFirma = rellenarCeros($firmante['matricula'], 8) .'.bmp';
//                    $firma = fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Firmas/".$fileFirma, "rb");
//                    if (!$firma) {
//                        echo "<p>No puedo abrir el archivo para lectura</p>";
//                        exit;
//                    }
//                    $contents=stream_get_contents($firma);
//                    fclose ($firma);
//                    $firmaVer = base64_encode($contents);
                    $jpgfile = 'firma/'.rellenarCeros($firmante['matricula'], 8) .'.jpg';
                        
                    $htmlFirma = '<td style="text-align:center;" >
                                    <img src="'.$jpgfile.'" border="0" height="120" width="" />
                                    <label style="font-size: 10px;">'.$secretario.'</label><br>
                                    <label style="font-size: 8px;">Secretario General<br>Colegio de Médicos - Distrito I</label>
                                </td>';
                } else {
                    $htmlFirma = '<td>&nbsp;'.$resFirmante['mensaje'].'</td>';
                }
                $html = '<table>
                        <tr>
                            <td>&nbsp;</td>
                            <td style="text-align:center;" >
                                <img src="'.$img.'" border="0" height="140" width="" />
                            </td>'
                            .$htmlFirma.
                        '</tr>
                        </table';

                $pdf->Ln(15);
                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
            }
            
            if ($enviaMail == 'S' && $conFirma == 'S') {
                $tipoPdf = 'F';
            } else {
                $tipoPdf = 'I';
            }
            
            $destination = $tipoPdf; //'F';
            if (!preg_match('/\.pdf$/', $path_to_store_pdf))
            {
                $path_to_store_pdf .= '.pdf';
            }
            ob_clean();

            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF;
            $nombreArchivo = 'Certificado_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
            $periodoActual = $_SESSION['periodoActual'];
                    
            $estructura = "../archivos/certificados/".$periodoActual;
            if (!file_exists($estructura)) {
                mkdir($estructura, 0777, true);
            }
            if (file_exists("../archivos/certificados/".$periodoActual."/".$nombreArchivo)) {
                unlink("../archivos/certificados/".$periodoActual."/".$nombreArchivo);
            } 
    
            if ($tipoPdf == 'F') {
                $pdf->Output($camino.'/archivos/certificados/'.$periodoActual.'/'.$nombreArchivo, $destination);        
                $envioMail = TRUE;
            } else {
                $pdf->Output($nombreArchivo, $destination);        
                $envioMail = FALSE;
            }
            if ($envioMail && isset($mail)) {
                //enviamos el pdf por mail si tiene contacto
                $destinatario = $colegiado['apellido'].', '.$colegiado['nombre'];
                $mailDestino = $mail;
                require_once '../PHPMailer/class.phpmailer.php';
                require_once '../PHPMailer/class.smtp.php';

                $mail = new PHPMailer();
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = "ssl";
                $mail->Host = "mail.colmed1.org.ar";
                $mail->Port = 465;
                $mail->Username = "sistemas@colmed1.org.ar";
                $mail->Password = "@sistemas1";

                $mail->From = "info@colmed1.org.ar";
                $mail->FromName = "Colegio de Medicos. Distrito I";
                $mail->Subject = "Certificado solicitado";
                $mail->AltBody = "";
                $mail->MsgHTML("Envio Certificado Solicitado");
                $mail->AddAttachment("../archivos/certificados/".$periodoActual."/".$nombreArchivo);
                $mail->AddAddress($mailDestino, $destinatario);
                $mail->IsHTML(true);
                if($mail->Send()) {
                    $mailEnviado = TRUE;
                }else{
                    $mailEnviado = FALSE;
                }

            }
            if ($envioMail) {
                require_once ('../html/head.php');
                require_once ('../html/encabezado.php');
                if ($mailEnviado) {
                ?>
                    <div class="col-md-12">
                        <div class="row" style="background-color: #428bca;">
                            <div class="col-md-12"></div>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Certificado solicitado por <?php echo $colegiado['nombre'].' '.$colegiado['apellido']; ?></h3>
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
            }
            ?>
        </form>
        <?php
        } else {
        ?>
            <div id="pagina">
                <h2>Se produjo un error al buscar al colegiado</h2>
            </div>
        <?php
        }
        ?>
        <!--BOTON VOLVER -->
        <div class="row">
            <div class="col-md-3" id="volver">
                <h3>Cerrar esta pestaña del navegador, el mail fue enviado con éxito.</h3>
            </div>
        </div>
    <?php
    } else {
    ?>
        <form name="myForm"  method="POST" action="colegiado_certificados_alta.php?idColegiado=<?php echo $idColegiado; ?>">
            <input type="hidden"  name="mensaje" id="mensaje" value="<?php echo $resultado['mensaje']; ?>">
            <input type="hidden"  name="icono" id="icono" value="<?php echo $resultado['icono']; ?>">
            <input type="hidden"  name="clase" id="clase" value="<?php echo $resultado['clase']; ?>">
            <input type="hidden"  name="mail" id="mail" value="<?php echo $mail;?>">
            <input type="hidden"  name="mailOriginal" id="mailOriginal" value="<?php echo $mailOriginal;?>">
            <input type="hidden"  name="idTipoCertificado" id="idTipoCertificado" value="<?php echo $idTipoCertificado;?>">
            <input type="hidden"  name="idEspecialidad" id="idEspecialidad" value="<?php echo $idEspecialidad;?>">
            <input type="hidden"  name="distrito" id="distrito" value="<?php echo $distrito;?>">
            <input type="hidden"  name="idNotaCambioDistrito" id="idNotaCambioDistrito" value="<?php echo $idNotaCambioDistrito;?>">
            <input type="hidden"  name="enviaMail" id="enviaMail" value="<?php echo $enviaMail;?>">
            <input type="hidden"  name="presentado" id="presentado" value="<?php echo $presentado;?>">
            <input type="hidden"  name="estadoConTesoreria" id="estadoConTesoreria" value="<?php echo $estadoConTesoreria;?>">
            <input type="hidden"  name="cuotasAdeudadas" id="cuotasAdeudadas" value="<?php echo $cuotasAdeudadas;?>">
            <input type="hidden"  name="conFirma" id="conFirma" value="<?php echo $conFirma;?>">
            <input type="hidden"  name="conLeyendaTeso" id="conLeyendaTeso" value="<?php echo $conLeyendaTeso;?>">
            <input type="hidden"  name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor;?>">
            <input type="hidden"  name="tipoCertificado" id="tipoCertificado" value="<?php echo $tipoCertificado;?>">
        </form>
    <?php
    }
    ?>
</body>

