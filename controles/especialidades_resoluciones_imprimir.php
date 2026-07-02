<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 30, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            /*
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
if (isset($_GET['idResolucion'])) {
    $idResolucion = $_GET['idResolucion'];
    $resResolucion = $resolucionesLogic->obtenerResolucionPorId($idResolucion);
    if ($resResolucion['estado']) {
        $resolucion = $resResolucion['datos']; 
        $numeroResolucion = $resolucion['numero'];
        $fechaResolucion = $resolucion['fecha'];
        $idTipoResolucion = $resolucion['idTipoResolucion'];
        $estado = $resolucion['estado'];
        
        //si el estado es A, se modifica a E->enviado a Consejo
        if ($estado == 'A') {
            $resEnviar = $resolucionesLogic->cambiarEstadoResolucion($idResolucion, 'A', 'E');  
            if (!$resEnviar['estado']) {
                $continua = FALSE;
                $resultado['mensaje'] = $resEnviar['mensaje'];
            }
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
    $pdf = new MYPDF('P', PDF_UNIT, 'LEGAL', true, 'UTF-8', false);
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

    /*
    $image_file = '../public/images/logo_colmed1_hr.png';
    $pdf->Image($image_file, 35, 25, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
     * 
     */
    $alturaLinea = 6;
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->Ln(10);
    $html = 'VISTO';
    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'L', true);
    $pdf->Ln(6);
    $html = '- Las disposiciones del Artículo 5º inciso 9) del Decreto-Ley 5413/58.-';
    $pdf->writeHTMLCell(0, 0, 50, '', $html, 0, 1, 0, true, 'L', true);
    $pdf->Ln(6);
    $html = '- Las disposiciones del Reglamento de las Especializaciones y del Ejercicio de las Especialidades.-';
    $pdf->writeHTMLCell(0, 0, 50, '', $html, 0, 1, 0, true, 'L', true);
    $pdf->Ln(6);
    if ($idTipoResolucion <> 2 && $idTipoResolucion <> 9) {
        $html = '- El convenio suscripto entre el Ministerio de Salud de la Nación y el Ministerio de Salud de la Pcia de Bs. As. y la Resolución Nº 835 del Consejo Superior del Colegio de Médicos de la Pcia. de Buenos Aires.-';
        $pdf->writeHTMLCell(0, 0, 50, '', $html, 0, 1, 0, true, 'L', true);
        $pdf->Ln(6);
    }
    $html = 'Y CONSIDERANDO';
    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'L', true);
    $pdf->Ln(6);
    $html = '- La aprobación efectuada por el Tribunal de Especializaciones Médicas.-';
    $pdf->writeHTMLCell(0, 0, 50, '', $html, 0, 1, 0, true, 'L', true);
    $pdf->Ln(6);
    $html = 'EL CONSEJO DIRECTIVO DEL COLEGIO DE MÉDICOS DISTRITO I';
    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'C', true);
    $pdf->Ln(6);
    $html = 'RESUELVE';
    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'C', true);
    $pdf->Ln(8);
    //ARMAMOS EL HTML
    $pdf->SetFont('dejavusans', '', 9);
    
    //obtengo las especialidades que tenga otrogadas
    $resMatriculas = $resolucionesLogic->obtenerMatriculasPorIdResolucion($idResolucion);
    //var_dump($resMatriculas); exit;
    if ($resMatriculas['estado']){
        $indice = 1;
        foreach ($resMatriculas['datos'] as $dato){
            $nroExpediente = $dato['nroExpediente'];
            $codigoEspecialista = $dato['codigoEspecialista'];
            $anioExpediente = $dato['anioExpediente'];
            $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
            $matricula = $dato['matricula'];
            $nombreEspecialidad = $dato['especialidad'];
            $tipoEspecialista = $dato['tipoEspecialista'];
            if (isset($dato['fechaEspecialista'])) {
                $fechaEspecialista = cambiarFechaFormatoParaMostrar($dato['fechaEspecialista']);
            } else {
                if (isset($dato['fechaEspecialista2'])) {
                    $fechaEspecialista = cambiarFechaFormatoParaMostrar($dato['fechaEspecialista2']);
                } else {
                    $fechaEspecialista = NULL;
                }
            }

            $fechaRecertificacion = $dato['fechaRecertificacion'];
            $origen = $dato['origen'];
            $especialistaInciso = $dato['especialistaInciso'];
            /*
            $fechaVencimiento = $dato['fechaVencimiento'];
            if (isset($fechaVencimiento) && $fechaVencimiento <> "" && $fechaVencimiento <> "0000-00-00") {
                //$fechaRecertificacion = sumarRestarSobreFecha($dato['fechaRecertificacion'], 5, 'year', '+');
                $fechaPermitida = sumarRestarSobreFecha($fechaVencimiento, 2, 'year', '+');
                if ($fechaPermitida <= date('Y-m-d')) {
                    //$fechaRecertificacion = sumarRestarSobreFecha($fechaVencimiento, 5, 'year', '+');
                    //$fechaRecertificacion = sumarRestarSobreFecha($fechaResolucion, 5, 'year', '+');
                    $fechaRecertificacion = $fechaResolucion;
                } else {
                    $fechaRecertificacion = $fechaVencimiento;
                }
            } else {
                $fechaPermitida = sumarRestarSobreFecha($dato['fechaEspecialista'], 2, 'year', '+');
                if ($fechaPermitida > date('Y-m-d')) {
                    $fechaRecertificacion = sumarRestarSobreFecha($dato['fechaEspecialista'], 5, 'year', '+');
                } else {
                    $fechaRecertificacion = $fechaResolucion; //sumarRestarSobreFecha($fechaResolucion, 5, 'year', '+');
                }
            }
             * 
             */

            $fechaVencimiento = cambiarFechaFormatoParaMostrar(sumarRestarSobreFecha($fechaRecertificacion, 5, 'year', '+'));
            $fechaRecertificacion = cambiarFechaFormatoParaMostrar($fechaRecertificacion);
            $sexo = $dato['sexo'];
            if ($sexo <> 'F') {
                $genero = 'DR. ';
            } else {
                $genero = 'DRA. ';
            }
            
            switch ($tipoEspecialista) {
                case 'J':
                case 'Especialista Jerarquizado':
                    $tipoEspecialista = 'Jerarquizado';
                    $otroTramite = ""; //ver si en el mismo dia realiza recertificacion del titulo de especialista

                    $html = $indice.'º).- Aprobar lo actuado por el tribunal de Especializaciones Médicas en el Expediente Nº '.$nroExpediente.'/'.$anioExpediente.', caratulado '.
                        '<b>"'.$genero.$apellidoNombre.' - M.P.: '.$matricula.' S/SOLICITUD CERTIFICADO DE '.strtoupper($tipoEspecialista).' EN '.strtoupper($nombreEspecialidad).'"</b> '.
                        'determinandose que se otorgue la autorización al uso del Título de '.$tipoEspecialista.' en '.$nombreEspecialidad.' de acuerdo a las disposiciones de los '.
                        'artículos 12º y 21º del reglamento vigente.-'.$otroTramite;
                    if (isset($origen) && $origen <> "") {
                        $html .= ' ( '.$origen.' '.$especialistaInciso.')';
                    }
                    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(6);
                    break;

                case 'C':
                case 'Especialista Consultor':
                    $tipoEspecialista = 'Consultor';
                    $otroTramite = ""; //ver si en el mismo dia realiza recertificacion del titulo de especialista

                    $html = $indice.'º).- Aprobar lo actuado por el tribunal de Especializaciones Médicas en el Expediente Nº '.$nroExpediente.'/'.$anioExpediente.', caratulado '.
                        '<b>"'.$genero.$apellidoNombre.' - M.P.: '.$matricula.' S/SOLICITUD CERTIFICADO DE '.strtoupper($tipoEspecialista).' EN '.strtoupper($nombreEspecialidad).'"</b> '.
                        'determinandose que se otorgue la autorización al uso del Título de '.$tipoEspecialista.' en '.$nombreEspecialidad.' de acuerdo a las disposiciones de los '.
                        'artículos 12º y 22º del reglamento vigente.-'.$otroTramite;
                    if (isset($origen) && $origen <> "") {
                        $html .= ' ( '.$origen.' '.$especialistaInciso.')';
                    }
                    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(6);
                    break;

                case 'R':
                case 'Recertificación':
                    $tipoEspecialista = 'RECERTIFICACIÓN';
                    if (isset($origen) && $origen == "Expedido por Ministerio de Salud de la Nación") {
                        $tipoEspecialista = 'RENOVACIÓN RECONOCIMIENTO NACIÓN';
                    }
                    $otroTramite = ""; //ver si en el mismo dia realiza recertificacion del titulo de especialista

                    $html = $indice.'º).- Aprobar lo actuado por el tribunal de Especializaciones Médicas en el Expediente Nº '.$nroExpediente.'/'.$anioExpediente.', caratulado '.
                        '<b>"'.$genero.$apellidoNombre.' - M.P.: '.$matricula.' S/SOLICITUD DE '.$tipoEspecialista.' DE LA ESPECIALIDAD '.strtoupper($nombreEspecialidad).'"</b> '.
                        'otorgada con fecha <b>'.$fechaEspecialista.'</b> y determinandose que la misma ha reunido los requisitos correspondientes para su nueva <b>'.$tipoEspecialista.'</b>, '.
                        'a partir del día <b>'.$fechaRecertificacion.'</b> debiendo renovarse a los cinco años <b>'.$fechaVencimiento.'</b>. '.$otroTramite;
                    if (isset($origen) && $origen <> "") {
                        $html .= ' ( '.$origen.' '.$especialistaInciso.')';
                    }
                    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(6);
                    break;

                case 'N':
                case 'Expedido por Ministerio de Salud de la Nación':
                    $tipoEspecialista = 'Especialista';
                    $otroTramite = ""; //ver si en el mismo dia realiza recertificacion del titulo de especialista

                    $html = $indice.'º).- Aprobar lo actuado por el tribunal de Especializaciones Médicas en el Expediente Nº '.$nroExpediente.'/'.$anioExpediente.', caratulado '.
                        '<b>"'.$genero.$apellidoNombre.' - M.P.: '.$matricula.' S/SOLICITUD PARA LA OBTENCIÓN DEL TÍTULO DE ESPECIALISTA EN '.strtoupper($nombreEspecialidad).'"</b>, '.
                        'disponiéndose el <b>RECONOCIMIENTO</b> del Título de Especialista en '.$nombreEspecialidad.' otorgado por el Ministerio de Salud de la Nación.-';
                    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(6);
                    break;

                default:
                    //$origen = $tipoEspecialista;
                    //echo $tipoEspecialista; exit;
                    if ($tipoEspecialista == "Calificación Agregada") {
                        $tipoEspecialista = 'Calificación Agregada';
                    } else {
                        $tipoEspecialista = 'Especialista';
                    }

                    $otroTramite = ""; //ver si en el mismo dia realiza recertificacion del titulo de especialista

                    $html = $indice.'º).- Aprobar lo actuado por el tribunal de Especializaciones Médicas en el Expediente Nº '.$nroExpediente.'/'.$anioExpediente.', caratulado '.
                        '<b>"'.$genero.$apellidoNombre.' - M.P.: '.$matricula.' S/SOLICITUD CERTIFICADO DE '.strtoupper($tipoEspecialista).' EN '.strtoupper($nombreEspecialidad).'"</b> '.
                        'determinandose que se otorgue la autorización al uso del Título de '.$tipoEspecialista.' en '.$nombreEspecialidad.' de acuerdo a las normas citadas en el VISTO del presente.-';
                    $html .= ' ( '.$origen.' '.$especialistaInciso.')';
                    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(6);
                    break;
            }
            
            $indice++;
        }
        
         //obtengo los anexos, si tiene imprimo
        $resAnexos = $resolucionesLogic->obtenerAnexosResolucion($idResolucion);
        if ($resAnexos['estado']) {
            foreach ($resAnexos['datos'] as $dato) {
                if ($dato['borrado'] == 0) {
                    $html = $indice.'º).- '.$dato['observacion'];
                    $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
                    $pdf->Ln(6);
            
                    $indice++;
                }
            }
        }
        
        $html = $indice.'º).- De forma.-';
        $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);

        $indice++;
        $html = $indice.'º).- Efectuar las comunicaciones correspondientes.-';
        $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);

        $indice++;
        $html = $indice.'º).- Archívese.-';
        $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
        $pdf->Ln(10);

        $html = 'CONSEJO DIRECTIVO DEL COLEGIO DE MÉDICOSDE LA PCIA. DE BUENOS AIRES - DISTRITO I.-';
        $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
        
        $anio = substr($fechaResolucion, 0, 4);
        $mes = substr($fechaResolucion, 5, 2);
        $dia = substr($fechaResolucion, 8, 2);
        $html = 'LA PLATA, '.$dia.' de '.obtenerMes($mes).' de '.$anio.'.-';
        $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
        
        $html = 'RESOLUCIÓN Nº '.$numeroResolucion;
        $pdf->writeHTMLCell(0, 0, 30, '', $html, 0, 1, 0, true, 'J', true);
    }
    ob_clean();
    /* Finalmente generamos el PDF */
    $destination = 'I';
    $nombreArchivo = 'Expediente_.pdf';
    $pdf->Output($nombreArchivo, $destination);        
} else {
    echo "error en los datos ingresados";
}
