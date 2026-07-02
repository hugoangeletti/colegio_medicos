<?php
require_once ('../../dataAccess/config.php');
permisoLogueado();
require_once ('../../html/head.php');
require_once ('../../dataAccess/funcionesConector.php');
require_once ('../../dataAccess/funcionesPhp.php');
require_once ('../../dataAccess/colegiadoLogic.php');
require_once ('../../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../../dataAccess/colegiadoCertificadosLogic.php');
require_once ('../../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
require_once ('../../dataAccess/colegiadoSancionLogic.php');
$colegiadoSancionLogic = new colegiadoSancionLogic();
require_once ('../../dataAccess/colegiadoCargoLogic.php');
$colegiadoCargoLogic = new colegiadoCargoLogic();
require_once ('../../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../../dataAccess/colegiadoFapLogic.php');
$colegiadoFapLogic = new colegiadoFapLogic();
require_once ('../../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../../dataAccess/presidenteLogic.php');
require_once ('../../dataAccess/notaCambioDistritoLogic.php');
require_once ('../../dataAccess/tipoCertificadoLogic.php');
require_once ('../../dataAccess/colegiadoArchivoLogic.php');
$colegiadoArchivoLogic = new colegiadoArchivoLogic();

require_once('../../tcpdf/config/lang/spa.php');
require_once('../../tcpdf/tcpdf.php');
//require_once('../../TCPDF-php8-main/tcpdf.php');
class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
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
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $estadoMatricular = $colegiado['estado'];
        $sexo = $colegiado['sexo'];
        
        //obtengo el estado con tesoreria
        $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, PERIODO_ACTUAL);
        if ($resEstadoTeso['estado']){
            $codigoDeudor = $resEstadoTeso['codigoDeudor'];
            $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
            if ($resEstadoTesoreria['estado']){
                $estadoConTesoreria = $resEstadoTesoreria['estadoTesoreria'];
            } else {
                $continua = FALSE;
                $resultado['mensaje'] = $resEstadoTesoreria['mensaje'];
            }                
        } else {
            $continua = FALSE;
            $resultado['mensaje'] = $resEstadoTeso['mensaje'];
        }                
    } else {
        $continua = FALSE;
        $resultado['mensaje'] = $resColegiado['mensaje'];
    }                
} else {
    $continua = FALSE;
}
if ($continua){
    //armo el html con el certificado
        $conEstadoTesoreria = TRUE;
        switch ($colegiado['tipoEstado']) {
            case 'A':
                $tipoEstadoMatricular = 'ACTIVO';
                break;

            case 'I':
                $tipoEstadoMatricular = 'Inscripto';
                break;

            case 'C':
                $tipoEstadoMatricular = 'BAJA('.$colegiado['movimientoCompleto'].')';
                break;

            case 'F':
            case 'J':
                $tipoEstadoMatricular = 'BAJA('.$colegiado['movimientoCompleto'].')';
                break;

            default:
                $tipoEstadoMatricular = '-';
                break;
        } 
        $libro = $colegiado['tomo'];
        $folio = $colegiado['folio'];
        $tipoDocumento = $colegiado['tipoDocumento'];
        $numeroDocumento = $colegiado['numeroDocumento'];
        $fechaMatriculacion = cambiarFechaFormatoParaMostrar($colegiado['fechaMatriculacion']);
        $fechaNacimiento = cambiarFechaFormatoParaMostrar($colegiado['fechaNacimiento']);
        $nacionalidad = $colegiado['nacionalidad'];

        $resColegiadoTitulo = $colegiadoLogic->obtenerTitulosPorColegiado($idColegiado);
        if ($resColegiadoTitulo['estado']) {
            $colegiadoTitulo = $resColegiadoTitulo['datos'];
            $fechaTitulo = cambiarFechaFormatoParaMostrar($colegiadoTitulo['fechaTitulo']);
            $tituloColegiado = $colegiadoTitulo['tipoTitulo'];
            $universidad = $colegiadoTitulo['universidad'];
        }

        $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '1');
        if ($resArchivos['estado'] && isset($resArchivos['datos'])){
            $archivos = $resArchivos['datos'];
            $fileFoto = trim($archivos['nombre']);
            // insertamos la foto y firma
            $foto = @fopen (FTP_ARCHIVOS."/Fotos/".$fileFoto, "rb");
            if ($foto) {
                $contents=stream_get_contents($foto);
                fclose ($foto);

                $fotoVer = base64_encode($contents);
                $tieneFotoFirma = TRUE;
            } 
            $tieneFotoFirma = TRUE;
        }

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

        $alturaLinea = 6;
        //imprimo la planilla
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', '', 10);
        if ($tieneFotoFirma) {
            $pic = 'data:image/jpg;base64,' . base64_encode($contents);
            $img_base64_encoded = $pic;
            $imageContent = file_get_contents($img_base64_encoded);
            $path = tempnam(sys_get_temp_dir(), 'prefix');

            file_put_contents ($path, $imageContent);

            $img = '<img src="' . $path . '"  width="100" height="100">';
            $pdf->SetXY(170, 25);
            $pdf->writeHTMLCell(0, 0, '', '', $img, '', 1, 0, true, 'R', true);            
        } else {
            $pdf->MultiCell(0, $alturaLinea, 'SIN FOTO', 0, 'R', false, 1, '50', '');
        }
        //$pdf->MultiCell(0, $alturaLinea, 'Nº '.rellenarCeros($idCertificado, 8), 0, 'L', false, 0, '', '');
        $pdf->MultiCell(0, $alturaLinea, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 1, '50', '');
        $pdf->Ln(5);
        //ARMAMOS EL HTML
        $html = '<br><p style="text-align: center"><b>LEGAJO (Uso interno)</b></p>'
                . '<br><br><br>';
        $html .= '<p style="line-height: 20em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
                'Tengo el agrado de dirigirme a usted, a los efectos de remitirle los datos '.
                'registrados en este Colegio de Médicos Distrito I pertenecientes ';
        if ($sexo <> 'F') {
            $html .= 'al profesional médico ';
        } else {
            $html .= 'a la profesional médica ';
        }
        $html .= '<b>'.$colegiado['nombre'].' '.$colegiado['apellido'].' </b></p>';

        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', '', 9);
        $indice = 1;
        if ($sexo == 'F') {
            $html = $indice.'. Médica matriculada bajo el Nº <b>'.$matricula.'</b> , registrado en el Libro <b>'.$libro.'</b> Folio <b>'.$folio.'</b>';
        } else {
            $html = $indice.'. Médico matriculado bajo el Nº <b>'.$matricula.'</b> , registrado en el Libro <b>'.$libro.'</b> Folio <b>'.$folio.'</b>';
        }
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        $indice += 1;
        $html = $indice.'. Tipo y Número de Documento: <b>'.$tipoDocumento.'</b> <b>'.$numeroDocumento.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        $indice += 1;
        $html = $indice.'. Nacionalidad: <b>'.$nacionalidad.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        $indice += 1;
        $html = $indice.'. Fecha de Matriculación: <b>'.$fechaMatriculacion.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        $indice += 1;
        $html = $indice.'. Fecha de Nacimiento: <b>'.$fechaNacimiento.'</b>';
        $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);

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
            $html = $indice.'. Domicilio: <b>'.$domicilioCompleto.'</b>';
            $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
            //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        }

        //datos de contacto
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $contacto = $resContacto['datos'];
            $telefonos = $contacto['telefonoFijo'].' - '.$contacto['telefonoMovil'];
            $mail = $contacto['email'];

            $indice += 1;
            $html = $indice.'. Teléfonos: <b>'.$telefonos.'</b>  -  Correo Electrónico: <b>'.$mail.'</b>';
            $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        }

        //datos de consultorio
        $resConsultorio = $colegiadoDomicilioLogic->obtenerDomicilioProfesional($idColegiado);
        if ($resConsultorio['estado']) {
            $consultorio = $resConsultorio['datos'];
            $consultorios = $consultorio['domicilio'].' - '.$consultorio['nombreLocalidad'];

            $indice += 1;
            $html = $indice.'. Domicilio profesional: <b>'.$consultorios.'</b>';
            $pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
            //$pdf->MultiCell(0, $alturaLinea, , 0, 'L', false, 1, '', '', true);
        }

        $indice += 1;
        //$html = $indice.'. Domicilio profesional: <b>'.$consultorios.'</b>';
        //$pdf->writeHTMLCell(0, $alturaLinea, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, $indice.'. Título: ', 0, 'L', false, 0, '', '', true);
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->MultiCell(0, $alturaLinea, $tituloColegiado, 0, 'L', false, 0, '30', '', true);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, ' Otorgó: ', 0, 'L', false, 0, '45', '', true);
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->MultiCell(0, $alturaLinea, $universidad, 0, 'L', false, 0, '60', '', true);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, ' con fecha: ', 0, 'L', false, 0, '150', '', true);
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->MultiCell(0, $alturaLinea, $fechaTitulo, 0, 'L', false, 1, '170', '', true);
        if (isset($bloqueoTitulo)) {
            $pdf->MultiCell(0, $alturaLinea, 'Con Bloqueo de Título desde el  '.$fechaBloqueoTitulo, 0, 'L', false, 1, '', '', true);
        }

        $indice += 1;
        $resEspecialidades = $colegiadoEspecialistaLogic->obtenerEspecialidadesPorIdColegiado($idColegiado);
        if ($resEspecialidades['estado']) {
                    //verifico si tiene alguna especialidad por convenio unlp que actualiza una existente
                    $conUnlpActualizada = FALSE;
                    foreach ($resEspecialidades['datos'] as $row) {
                        if (isset($row['fechaEspecialistaOrigen']) && $row['fechaEspecialistaOrigen'] <> "") {
                            $conUnlpActualizada = TRUE;
                        }
                    }

                    if ($conUnlpActualizada) {
                        $pdf->SetFont('dejavusans', '', 8);
                    } else {
                        $pdf->SetFont('dejavusans', '', 9);
                    }
                    $pdf->MultiCell(0, $alturaLinea, $indice.'. Especialidad: ', 0, 'L', false, 0, '', '', true);
                    $pdf->MultiCell(0, $alturaLinea, 'Distrito', 0, 'L', false, 0, '70', '', true);
                    $pdf->MultiCell(0, $alturaLinea, 'Con fecha', 0, 'L', false, 0, '83', '', true);

                    if ($conUnlpActualizada) {
                        $pdf->MultiCell(0, $alturaLinea, 'Convenio UNLP', 0, 'L', false, 0, '100', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Ult.Recerti.', 0, 'L', false, 0, '125', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Jerarquizado', 0, 'L', false, 0, '142', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Consultor', 0, 'L', false, 0, '162', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Caducidad', 0, 'L', false, 0, '177', '', true); 
                        $pdf->SetFont('dejavusans', 'B', 6);
                    } else {
                        //$pdf->MultiCell(0, $alturaLinea, 'Con fecha', 0, 'L', false, 0, '90', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Ult.Recerti.', 0, 'L', false, 0, '108', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Jerarquizado', 0, 'L', false, 0, '129', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Consultor', 0, 'L', false, 0, '150', '', true);
                        $pdf->MultiCell(0, $alturaLinea, 'Caducidad', 0, 'L', false, 0, '171', '', true);
                        $pdf->SetFont('dejavusans', 'B', 8);
                    }
                    $pdf->Ln(5);
                    
                    foreach ($resEspecialidades['datos'] as $row) {
                        /*
                        if (strlen($row['nombreEspecialidad']) > 34) {
                            $pdf->SetFont('dejavusans', 'B', 6);
                        } else {
                            $pdf->SetFont('dejavusans', 'B', 8);
                        }
                        */
                        $pdf->MultiCell(0, $alturaLinea, $row['nombreEspecialidad'], 0, 'L', false, 0, '', '', true);
                        //$pdf->SetFont('dejavusans', 'B', 8);
                        $estadoEspecialidad = $row['estado'];
                        if (isset($row['fechaEspecialistaOrigen'])) {
                            $fechaEspecialista = $row['fechaEspecialistaOrigen'];
                            $fechaUNLP = $row['fechaEspecialista'];    
                        } else {
                            $fechaEspecialista = $row['fechaEspecialista'];
                            $fechaUNLP = NULL;
                        }
                        
                        $fechaVencimiento = $row['fechaVencimiento'];
                        if (isset($row['fechaRecertificacion']) && $row['fechaRecertificacion'] <> "0000-00-00") {
                            $fechaRecertificacion = $row['fechaRecertificacion'];
                        } else {
                            $fechaRecertificacion = "";
                        }
                        $idEspecialidad = $row['idEspecialidad'];

                        if ($estadoEspecialidad == 'A') {
                            //if ($row['tipoespecialista'] == 8) {
                            //    $otorgadaPor = 'Nación';
                            //} else {
                                $otorgadaPor = substr($row['distritoOrigen'], 0, 3);
                                if ($otorgadaPor == '0') {
                                    $otorgadaPor = 1;
                                }
                            //}
                            $pdf->MultiCell(15, $alturaLinea, $otorgadaPor, 0, 'C', false, 0, '70', '', true);
                            $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaEspecialista), 0, 'L', false, 0, '83', '', true);
                            $proxima_columna = 21;
                            $columna_fechas = 108;
                            if ($conUnlpActualizada) {
                                if (isset($fechaUNLP)) {
                                    $proxima_columna = 18;
                                    $columna_fechas = 107;
                                    //$pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaUNLP), 0, 'L', false, 0, '177', '', true);
                                    $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaUNLP), 0, 'L', false, 0, $columna_fechas, '', true);
                                    $columna_fechas += $proxima_columna;
                                }
                            }
                            //$pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaRecertificacion), 0, 'L', false, 0, '107', '', true);
                            $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fechaRecertificacion), 0, 'L', false, 0, $columna_fechas, '', true);
                            $columna_fechas += $proxima_columna;
                            $idColegiadoEspecialista = $row['idColegiadoEspecialista'];

                            //verifico si tiene jerarquizado y consultor
                            $verVencimiento = TRUE;
                            if ($otorgadaPor <> "NAC") {
                                //imprimo JER y CON si no es de NACION
                                $resJerarquizado = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'J');
                                if ($resJerarquizado['estado']) {
                                    //$pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']), 0, 'L', false, 0, '125', '', true);
                                    $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']), 0, 'L', false, 0, $columna_fechas, '', true);
                                    $columna_fechas += $proxima_columna;

                                    $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
                                    if ($resConsultor['estado']) {
                                        //$pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resConsultor['fecha']), 0, 'L', false, 0, '144', '', true);
                                        $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($resConsultor['fecha']), 0, 'L', false, 0, $columna_fechas, '', true);
                                        $verVencimiento = FALSE;
                                    } else {
                                        //$pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '155', '', true);
                                        $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, ($columna_fechas + 5), '', true);
                                    }
                                    $columna_fechas += $proxima_columna;
                                } else {
                                    //$pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '135', '', true);
                                    //$pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, '155', '', true);
                                    //if (isset($fechaUNLP)) {
                                        $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, ($columna_fechas + 10), '', true);
                                        $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, ($columna_fechas + 25), '', true);
                                    //} else {
                                    //    $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, ($columna_fechas + 10), '', true);
                                    //    $pdf->MultiCell(0, $alturaLinea, 'No', 0, 'L', false, 0, ($columna_fechas + 25), '', true);
                                    //}
                                    $columna_fechas += ($proxima_columna * 2);
                                }
                            }

                            if ($verVencimiento) {
                                if (isset($fechaVencimiento) && $fechaVencimiento != '0000-00-00') {
                                    if (date('Y-m-d') > $fechaVencimiento) {
                                        //verifica si esta dentro de los 2 años permitidos a Recertificar
                                        $fechaLimite = sumarRestarSobreFecha($fechaVencimiento, 2, 'year', '+');
                                        if (date('Y-m-d') > $fechaLimite) {
                                            if ($otorgadaPor <> "NAC") {
                                                $caduca = 'No Recertificada';
                                            } else {
                                                $caduca = 'No Renovada';
                                            }
                                        } else {
                                            if ($otorgadaPor <> "NAC") {
                                                $caduca = 'Recert. en Trámite';
                                            } else {
                                                $caduca = 'Renov. en Trámite';
                                            }
                                        }
                                    } else {
                                        $caduca = "";
                                        /*
                                        if ($otorgadaPor <> "NAC") {
                                            $caduca = 'Recert.hasta: ';
                                        } else {
                                            $caduca = 'Renov.hasta: ';
                                        }
                                        */
                                        $caduca .= cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                        $fechaLimite = sumarRestarSobreFecha($fechaVencimiento, 5, 'year', '-');
                                        if ($fechaEspecialista == $fechaLimite) {
                                            $caduca = "";
                                            /*
                                            if ($otorgadaPor <> "NAC") {
                                                $caduca = 'Certif.hasta: ';
                                            } else {
                                                $caduca = 'Renov.hasta: ';
                                            }
                                            */
                                            $caduca .= cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                        }
                                    }
                                    //$pdf->MultiCell(100, $alturaLinea, $caduca, 0, 'L', false, 0, '160', '', true);
                                    $pdf->MultiCell(100, $alturaLinea, $caduca, 0, 'L', false, 0, $columna_fechas, '', true);
                                } else {
                                    $caduca = 'No';
                                    //$pdf->MultiCell(100, $alturaLinea, $caduca.$columna_fechas, 0, 'C', false, 0, '130', '', true);
                                    $pdf->MultiCell(100, $alturaLinea, $caduca, 0, 'C', false, 0, $columna_fechas, '', true);
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
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, $alturaLinea, $indice.'. Especialidad: ', 0, 'L', false, 0, '', '', true);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, $alturaLinea, 'NINGUNA.', 0, 'L', false, 1, '42', '', true);
        }

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
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, $indice.'. Sanciones éticas disciplinarias: ', 0, 'L', false, 0, '', '', true);
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
                    case '52C':
                    case '52 c':
                    case '52 C':
                        // le sumo 10 años a la fecha de la sancion para ver si caducó
                        $fechaLimite = sumarRestarSobreFecha($fechaDesde, 10, 'year', '+');
                        if ($fechaDesde <= $fechaLimite) {
                            //$sanciones = $ley .' '. cambiarFechaFormatoParaMostrar($fechaDesde) .' al '. cambiarFechaFormatoParaMostrar($fechaHasta) .' Art.:'. $articulo; 
                            $sanciones = 'Art. 52c Decreto-Ley 5413/58'; 
                        }
                        break;

                    case '40c':
                    case '40C':
                    case '40 c':
                    case '40 C':
                        $fechaActual = date('Y-m-d'); 
                        if ($fechaDesde<=$fechaActual && $fechaHasta>=$fechaActual) {
                            //$sanciones = $ley .' '. cambiarFechaFormatoParaMostrar($fechaDesde) .' al '. cambiarFechaFormatoParaMostrar($fechaHasta) .' Art.:'. $articulo; 
                            $sanciones = 'Art. 40c Decreto-Ley 5413/58'; 
                        }
                        break;

                    default:
                        break;
                }
                if (isset($sanciones)) {
                    if ($i == 0) {
                        //$pdf->MultiCell(0, $alturaLinea, $indice.'. Sanciones éticas disciplinarias: ', 0, 'L', false, 0, '', '', true);
                    }
                    $pdf->SetFont('dejavusans', 'B', 9);
                    //$pdf->MultiCell(0, $alturaLinea, 'SI', 0, 'L', false, 1, '70', '', true);
                    $pdf->MultiCell(0, $alturaLinea, $sanciones, 0, 'L', false, 1, '70', '', true);
                    $i += 1;
                    $conSancion = TRUE;
                }
            }
        } 

        if (!$conSancion) {
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, $alturaLinea, 'NINGUNA', 0, 'L', false, 1, '70', '', true);
            //$pdf->MultiCell(0, $alturaLinea, $indice.'. Sanciones éticas disciplinarias: NINGUNA', 0, 'L', false, 1, '', '', true);
        }
        $pdf->SetFont('dejavusans', '', 9);

        //imprimir movimientos matriculares
        $indice += 1;
        $pdf->MultiCell(0, $alturaLinea, $indice.'. Movimientos matriculares:', 0, 'L', false, 0, '', '', true);
        $resMovimiento = $colegiadoMovimientoLogic->obtenerMovimientosPorIdColegiado($idColegiado);
        if ($resMovimiento['estado']) {
            $pdf->Ln(5);
            if (isset($resMovimiento['datos']) && sizeof($resMovimiento['datos'])>1) {
                if (sizeof($resMovimiento['datos'])>5) {
                    $pdf->SetFont('dejavusans', '', 8);
                    $alturaLineaMov = 5;
                } else {
                    $pdf->SetFont('dejavusans', '', 9);
                    $alturaLineaMov = 5;
                }
                foreach ($resMovimiento['datos'] as $row) {
                    $elDistrito = $row['distritoCambio'];
                    if (isset($row['romanos'])) {
                        $elDistrito = $row['romanos'];
                    }

                    switch ($row['idTipoMovimietno']) {
                        case 5:
                            $movimiento = 'Colegiado en Distrito I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').' (Baja del Distrito '.$elDistrito.'). ';
                            break;

                        case 6:
                            $movimiento = 'Egreso Definitivo del Distrito I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').', colegiado en Dist.'.$elDistrito.'. ';
                            break;

                        case 8:
                            $movimiento = 'Colegiado en Dist.'.$elDistrito.' Inscripto en Dist.I desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y').'. ';
                            break;

                        case 9:
                            $movimiento = 'Cancelación de matrícula por Art.40 inc C Decreto Ley 5413/58. Desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y');
                            if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($row['fechaHasta'],'d-m-y').'.-';
                            }
                            break;

                        case 10:
                            $movimiento = 'Colegiado en Dist.I, Inscripto en Dist.'.$elDistrito.' desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde'],'d-m-y');
                            if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                $movimiento .= ' hasta el '.cambiarFechaFormatoParaMostrar($row['fechaHasta'],'d-m-y').'.-';
                            }
                            break;

                        default:
                            $movimiento = $row['detalleMovimiento'].' desde el '.cambiarFechaFormatoParaMostrar($row['fechaDesde']);
                            if (isset($row['fechaHasta']) && $row['fechaHasta'] <> '0000-00-00') {
                                $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($row['fechaHasta']).'.-';
                            }
                            break;
                    }
                    $pdf->MultiCell(0, $alturaLineaMov,$movimiento, 0, 'L', false, 1, '20', '', true);
                }
            } else {
                $unMovimiento = $resMovimiento['datos'][0];
                $elDistrito = $unMovimiento['distritoCambio'];
                if (isset($unMovimiento['romanos'])) {
                    $elDistrito = $unMovimiento['romanos'];
                }
                switch ($unMovimiento['idTipoMovimietno']) {
                    case 5:
                        $movimiento = 'Colegiado en Distrito I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').' (Baja del Distrito '.$elDistrito.'). ';
                        break;

                    case 6:
                        $movimiento = 'Egreso Definitivo del Distrito I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').', colegiado en Dist.'.$elDistrito.'. ';
                        break;

                    case 8:
                        $movimiento = 'Colegiado en Dist.'.$elDistrito.' Inscripto en Dist.I desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y').'. ';
                        break;

                    case 9:
                        $movimiento = 'Cancelación de matrícula por Art.40 inc C Decreto Ley 5413/58. Desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y');
                        if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                            $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta'],'d-m-y').'.-';
                        }
                        break;

                    case 10:
                        $movimiento = 'Colegiado en Dist.I, Inscripto en Dist.'.$elDistrito.' desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde'],'d-m-y');
                        if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                            $movimiento .= ' hasta el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta'],'d-m-y').'.-';
                        }
                        break;

                    default:
                        $movimiento = $unMovimiento['detalleMovimiento'].' desde el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaDesde']);
                        if (isset($unMovimiento['fechaHasta']) && $unMovimiento['fechaHasta'] <> '0000-00-00') {
                            $movimiento .= ' Rehabilitado el '.cambiarFechaFormatoParaMostrar($unMovimiento['fechaHasta']).'.-';
                        }
                        break;
                }
                $pdf->MultiCell(0, $alturaLinea,$movimiento, 0, 'L', false, 1, '20', '', true);
            }
        } else {
            //$pdf->MultiCell(0, $alturaLinea, $indice.'. Movimientos matriculares: NO REGISTRA', 0, 'L', false, 1, '', '', true);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, $alturaLinea, 'NO REGISTRA', 0, 'L', false, 1, '70', '', true);
        }

        //Situación con tesorería:
        //    !si es fallecido, jubilado, inscripto -> no se imprime la leyenda de Situacion Con Tesoreria
        $indice += 1;
        //$pdf->Ln(2);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, $indice.'. Situación con tesorería: ', 0, 'L', false, 0, '', '', true);
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->MultiCell(0, $alturaLinea, $estadoConTesoreria, 0, 'L', false, 1, '60', '', true);

        //Si tiene antecedentes en fap lo imprime
        $fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 10, 'year', '-');
        $indice += 1;
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(0, $alturaLinea, $indice.'. Registra antecedentes en el FAP: ', 0, 'L', false, 0, '', '', true);
        if ($colegiadoFapLogic->colegiadoTieneFap($idColegiado, $fechaLimite)) {
            $enFap = 'SI';
        } else {
            $enFap = 'NO';
        }
        $pdf->SetFont('dejavusans', 'B', 9);
        $pdf->MultiCell(0, $alturaLinea, $enFap, 0, 'L', false, 1, '75', '', true);                    
        $pdf->SetFont('dejavusans', '', 9);

        $notaUnica = "";

        /*
        a pedido del profesional y autorizado por mesa se le agrega nota a la matricula 120861
        */
        if ($matricula == 120861) {
            $notaUnica = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.'Asimismo, <b>se aclara que la profesional médica DRA. MARIA PAZ MATTIOLI, presentó nuevo DIPLOMA DE MEDICO con fecha de expedición 21/12/2023 en reemplazo del anterior que presentaba error en sus datos personales</b>.-';
        }
        /*
        a pedido del profesional y autorizado por mesa se le agrega nota a la matricula 117528
        */
        if ($matricula == 117528) {
            $notaUnica = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.'Asimismo, <b>se aclara que el profesional médico DR. FERNANDO JAVIER RUIZ POUYTE, presentó nuevo DIPLOMA DE MEDICO con fecha de expedición 10/03/2014 en reemplazo del anterior que perdiera en la inundación de la ciudad de La Plata</b>.-';
        }
        /*
        a pedido del profesional y autorizado por mesa se le agrega nota a la matricula 119153
        */
        if ($matricula == 119153) {
            $notaUnica = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.'Asimismo, <b>se aclara que el profesional médico DR. ALAN CHRISTIAN CANTO, presentó nuevo DIPLOMA DE MEDICO con fecha de expedición 20/02/2018 en reemplazo del anterior que fuera extraviado</b>.-';
        }
        if ($notaUnica <> "") {
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->writeHTMLCell(0, 0, '', '', $notaUnica, 0, 1, 0, true, 'J', true);
            $pdf->Ln(2);            
        }        

        $pdf->Ln(2);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 1, 'Realizó: '.$_SESSION['user_entidad']['nombreUsuario'], 0, 'L', false, 1, '', '', true);                    
        $pdf->SetFont('dejavusans', '', 10);

            $i++;
        }

        $destination = 'I';
        if (!preg_match('/\.pdf$/', $path_to_store_pdf))
        {
            $path_to_store_pdf .= '.pdf';
        }
        ob_clean();

        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF;
        $nombreArchivo = 'Legajo_'.$matricula.'_'.date('Ymd').date('his').'.pdf';
        $pdf->Output($nombreArchivo, $destination);        
