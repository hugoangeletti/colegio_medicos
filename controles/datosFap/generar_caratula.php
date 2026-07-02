<?php
require_once($pathOrigen.'../tcpdf/config/lang/spa.php');
require_once($pathOrigen.'../tcpdf/tcpdf.php');

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
                $this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}

if (isset($idSapCaratula) && $idSapCaratula > 1) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF.'/archivos/tmp/';
    $nombreArchivo = $camino.'FAP_'.$idSapCaratula.'.pdf';
    //echo $nombreArchivo; 
    //exit;
    if (!file_exists($camino)) {
        mkdir($camino, 0777, true);
    }

    //si el pdf ya existe, lo elimino
    if (file_exists($nombreArchivo)) {
        unlink($nombreArchivo);  
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
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->AddPage();

    //imprimo la planilla
    $image_file = '../public/images/logo_colmed1_hr.png';

    $pdf->Image($image_file, 35, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetFont('dejavusans', 'B', 12);
    //recuadro numero de reunion de mesa
    $pdf->Line(168, 10, 198, 10, array('width' => 0.50));
    $pdf->Line(168, 10, 168, 22, array('width' => 0.50));
    $pdf->Line(168, 22, 198, 22, array('width' => 0.50));
    $pdf->Line(198, 10, 198, 22, array('width' => 0.50));
    //fin recuadro numero de reunion de mesa

    $x_inicio = 10;
    $y_inicio = 5;
    $x_fin = $x_inicio + 190; 
    $y_fin = $y_inicio + 25; 
    $y_fin_linea = $y_fin + 140;
    /*
    $resPresidente = $mesaEntradaLogic->obtenerPresidenteDistrito(1);
    if ($resPresidente['estado']) {
        $presidente = $resPresidente['datos'];
        $presidenteDistritoI = $presidente['presidente'];
    } else {
        $presidenteDistritoI = NULL;
    }
    */
    $nombreCausa = $fapCaratula['NombreCausa'];
    $caratulaDefinitiva = $fapCaratula['CaratulaDefinitiva'];
    $nombreDepartamentoJudicial = $fapCaratula['NombreDepartamentoJudicial'];
    $nombreTipoCausa = $fapCaratula['NombreTipoCausa'];
    $fechaIngreso = $fapCaratula['FechaIngreso'];
    switch ($fapCaratula['Ambito']) {
        case '1':
            $nombreAmbito = 'Público';
            break;
        
        case '2':
            $nombreAmbito = 'Privado';
            break;
        
        default:
            $nombreAmbito = NULL;
            break;
    }
    $sexo = $fapCaratula['Sexo'];
    $edad = $fapCaratula['Edad'];
    $domicilioHecho = $fapCaratula['DomicilioHecho'];
    $fechaHecho = $fapCaratula['FechaHecho'];
    if (isset($fechaHecho) && $fechaHecho <> "") {
        $fechaHecho = cambiarFechaFormatoParaMostrar($fechaHecho);
    }
    $lugarHecho = $fapCaratula['LugarHecho'];
    switch ($fapCaratula['Recepcion']) {
        case 'P':
            $nombreRecepcion = 'Personal';
            break;
        
        case 'F':
            $nombreRecepcion = 'Familiar';
            break;
        
        default:
            $nombreRecepcion = NULL;
            break;
    }
    $fechaNotificacion = $fapCaratula['FechaNotificacion'];
    if (isset($fechaNotificacion) && $fechaNotificacion <> "") {
        $fechaNotificacion = cambiarFechaFormatoParaMostrar($fechaNotificacion);
    }
    $especialidad = $fapCaratula['Especialidad'];
    $especialidadesColegiado = $fapCaratula['Especialidades'];
    $inscriptoDistrito = NULL;
    $fechaInscripcion = NULL;
    switch ($fapCaratula['TieneCobertura']) {
        case 'S':
            $tieneCobertura = "SI";
            break;
        
        case 'N':
            $tieneCobertura = "NO";
            break;
        
        default:
            $tieneCobertura = "";
            break;
    }
    $nombreCobertura = $fapCaratula['NombreCobertura'];
    $coberturaDesde = $fapCaratula['CoberturaDesde'];
    $montoCobertura = NULL;
    $montoDemanda = NULL;
    $otrosProfesionales = NULL;
    $plazoConstatacion = NULL;
    $telefonoHecho = NULL;

    $domicilioReal  = $fapCaratula['DomicilioReal'];
    $domicilioProfesional = $fapCaratula['DomicilioProfesional'];
    $domicilioNotificacion = $fapCaratula['LugarNotificacion'];
    $telefonoParticular = $fapCaratula['TelefonoParticular'];
    $celular = $fapCaratula['Celular'];
    $mail = $fapCaratula['Mail'];
    $conCedula = $fapCaratula['ConCedula'];
    if (isset($conCedula) && $conCedula == 'S') {
        $conCedula = 'SI';
    } else {
        $conCedula = 'NO';
    }
    $conFotoDemanda = $fapCaratula['ConFotoDemanda'];
    if (isset($conFotoDemanda) && $conFotoDemanda == 'S') {
        $conFotoDemanda = 'SI';
    } else {
        $conFotoDemanda = 'NO';
    }
    $conFotoHC = NULL;
    $conFotoFicha = NULL;
    $notaDetalle = NULL;
    $conOtros = NULL;
    /*
    $conFotoHC = $fapCaratula['conFotoHC'];
    if (isset($conFotoHC) && $conFotoHC == 'S') {
        $conFotoHC = 'SI';
    } else {
        $conFotoHC = 'NO';
    }
    $conFotoFicha = $fapCaratula['conFotoFicha'];
    if (isset($conFotoFicha) && $conFotoFicha == 'S') {
        $conFotoFicha = 'SI';
    } else {
        $conFotoFicha = 'NO';
    }
    $notaDetalle = $fapCaratula['notaDetalle'];
    if (isset($notaDetalle) && $notaDetalle == 'S') {
        $notaDetalle = 'SI';
    } else {
        $notaDetalle = 'NO';
    }
    $conOtros = $fapCaratula['conOtros'];
    if (isset($conOtros) && $conOtros == 'S') {
        $conOtros = 'SI';
    } else {
        $conOtros = 'NO';
    }
    */
    $observaciones = $fapCaratula['Observaciones'];
    $recepciono = $fapCaratula['Recepciono'];

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->SetXY($x_inicio, $y_fin);
    $pdf->MultiCell(0, 5, 'La Plata, '.date(substr($fechaIngreso, 8, 2)).' de '.obtenerMes(substr($fechaIngreso, 5, 2)).' de '.date(substr($fechaIngreso, 0, 4)), 0, 'R', false, 1, '50', '');
    $pdf->MultiCell(120, 5, 'Señor Presidente del Colegio de Médicos', 0, 'L', false, 1, $x_inicio, '');
    $pdf->MultiCell(120, 5, 'Distrito I', 0, 'L', false, 1, $x_inicio, '');
    /*
    if (isset($presidenteDistritoI) && $presidenteDistritoI <> "") {
        $pdf->MultiCell(120, 5, $presidenteDistritoI, 0, 'L', false, 1, $x_inicio, '');
    }
    */
    $pdf->MultiCell(25, 5, 'S/D', 0, 'L', false, 1, $x_inicio, '');

    if ($sexo == "Femenino") {
        $dr_dra = "La";
        $matriculado_a = 'Matriculada';
    } else {
        $dr_dra = "El";
        $matriculado_a = 'Matriculado';
    }


    $html = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            '.$dr_dra.' que suscribe, <b>'.$apellidoNombre.'</b> M.P. <b>'.$matricula.'</b> '.$matriculado_a.' en este Distrito se dirige al Sr.Presidente a efectos de solicitar los beneficios del Fondo de Asistencia Profesional.<br>
            A tal fin declaro bajo juramento, conocer los alcances de las normas especificas del Fondo. Manifiesto además, conocer las normas que regulan el Ejercicio Profesional de la Medicina, Decreto Ley 5413/58, Ley 4534 y Ley 11.732 y otras normas vigentes a las que doy estricto cumplimiento.</p>
            <p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </p>';             
    $pdf->writeHTMLCell(0, 0, $x_inicio, '', $html, 0, 1, 0, true, 'J', true);

    $pdf->MultiCell(0, 5, 'Hechos', 0, 'L', false, 1, $x_inicio, '');
    $pdf->Ln(5);
    $indice = 1;
    $pdf->MultiCell(0, 7, $indice.'- Caratula de inicio: '.$nombreCausa, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Caratula definitiva: '.$caratulaDefinitiva, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Tramitada ante Dpto.Judicial: '.$nombreDepartamentoJudicial, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Tipo de causa: '.$nombreTipoCausa, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Fecha del hecho: '.$fechaHecho, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Institución: '.$nombreAmbito, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Domicilio: '.$domicilioHecho, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Teléfonos: '.$telefonoHecho, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Fecha Notificación: '.$fechaNotificacion.' Lugar: '.$lugarHecho, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Recepción: '.$nombreRecepcion, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Especialidad que origina la demanda: '.$especialidad, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Especialidad del Profesional demandado: '.$especialidadesColegiado, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Inscripto en otro distrito: '.$inscriptoDistrito.' Fecha de inscripción: '.cambiarFechaFormatoParaMostrar($fechaInscripcion), 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Posee cobertura de seguro: '.$tieneCobertura.', Cual: '.$nombreCobertura.', desde '.$coberturaDesde, 0, 'L', false, 1, $x_inicio, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 7, $indice.'- Monto de la demanda: '.$montoDemanda, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Edad del Profesional: '.$edad.' años, Sexo: '.$sexo, 0, 'L', false, 1, $x_inicio, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 7, $indice.'- Nombres de Profesionales demandados en la causa: '.$otrosProfesionales, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Plazo contestación de la demanda: '.$plazoConstatacion, 0, 'L', false, 1, $x_inicio, '');
    $html = '<p style="line-height: 15em;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Declaro conocer que la aprobación de esta solicitud es realizada por el Consejo Directivo quien ante incumplimiento o trasgresión de las normas vigentes, automáticamente, cancelará los beneficios otorgados.
            </p>';
    $pdf->writeHTMLCell(0, 0, $x_inicio, '', $html, 0, 1, 0, true, 'J', true);
    $pdf->Ln(10);
    $pdf->MultiCell(0, 5, 'Firma: ______________________________', 0, 'L', false, 0, $x_inicio, '');
    $pdf->MultiCell(0, 5, 'Aclaración: ______________________________', 0, 'L', false, 1, $x_inicio + 70, '');

    $pdf->SetFont('dejavusans', '', 8);
    $pdf->Ln(5);
    $pdf->MultiCell(0, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'R', false, 0, '150', '');
    $pdf->lastPage();

    $pdf->AddPage();

    $pdf->Image($image_file, 35, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetXY($x_inicio, $y_fin);
    $pdf->MultiCell(0, 7, 'Domicilio Real: '.$domicilioReal, 0, 'L', false, 1, $x_inicio, '');
    //$pdf->MultiCell(0, 7, 'Domicilio Profesional: '.$domicilioProfesional, 0, 'L', false, 1, $x_inicio, '');
    //$pdf->MultiCell(0, 7, 'Domicilio Notificación: '.$domicilioNotificacion, 0, 'L', false, 1, $x_inicio, '');
    $pdf->MultiCell(0, 7, 'Teléfono particular: '.$telefonoParticular.' - Celular: '.$celular, 0, 'L', false, 1, $x_inicio, '');
    $pdf->MultiCell(0, 7, 'Mail: '.$mail, 0, 'L', false, 1, $x_inicio, '');
    $pdf->Ln(5);
    $pdf->MultiCell(0, 7, 'Acompaño a la presente la siguiente documentación:', 0, 'L', false, 1, $x_inicio, '');
    $pdf->Ln(5);
    $indice = 1;
    $pdf->MultiCell(0, 7, $indice.'- Cédula: '.$conCedula, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $pdf->MultiCell(0, 7, $indice.'- Fotocopia de la demanda: '.$conFotoDemanda, 0, 'L', false, 1, $x_inicio, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 7, '3- Fotocopia de H.C. (Legible y sin abreviaturas): '.$conFotoHC, 0, 'L', false, 1, $x_inicio, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 7, '4- Fotocopia de ficha de consultorio legible: '.$conFotoFicha, 0, 'L', false, 1, $x_inicio, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 5, '5- Nota con detalles pormenorizados de los hechos: '.$notaDetalle, 0, 'L', false, 1, $x_inicio, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 7, '(en caso negativo deberá realizarla para su presentación en el estudio jurídico)', 0, 'L', false, 1, $x_inicio+5, '');
    //$indice += 1;
    //$pdf->MultiCell(0, 7, '6- Otros elementos que resulten útiles a los fines perseguidos: '.$conOtros, 0, 'L', false, 1, $x_inicio, '');
    $indice += 1;
    $html = $indice.'- De otorgarse los beneficios del sistema, me comprometo a solicitar una entrevista con el estudio jurídico dentro de las 24 horas posteriores a la obtención de dichos beneficios y aceptar el patrocinio letrado de los profesionales que indique el Colegio de Médicos, bajo apercibimiento de ser revocados los mismos.';
    $pdf->writeHTMLCell(0, 0, $x_inicio, '', $html, 0, 1, 0, true, 'J', true);
    $pdf->Ln(5);
    $indice += 1;
    $html = $indice.'- Dejar establecido que en el caso que las mediaciones se cierren sin acuerdo, resulta carga del beneficiario concurrir dentro de las 48 horas de recibida la notificación de la demanda a la sede de éste Colegio de Médicos, con el objeto de comunicar dicha circunstancia, caducando de pleno derecho y sin accesidad de intimación previa los beneficios acordados en el caso de incumplimiento de las obligaciones impuestas por el Decreto-Ley 5413/58 y sus reglamentaciones.-';
    $pdf->writeHTMLCell(0, 0, $x_inicio, '', $html, 0, 1, 0, true, 'J', true);
    $pdf->Ln(5);
    $pdf->MultiCell(0, 7, 'Sin otro particular lo saludo atentamente.-', 0, 'L', false, 1, $x_inicio, '');
    $pdf->Ln(10);
    $pdf->MultiCell(0, 5, 'Firma: ______________________________', 0, 'L', false, 0, $x_inicio, '');
    $pdf->MultiCell(0, 5, 'Aclaración: ______________________________', 0, 'L', false, 1, $x_inicio + 70, '');
    $pdf->Ln(5);
    $pdf->MultiCell(0, 5, 'Observaciones:', 0, 'L', false, 1, $x_inicio, '');
    $pdf->writeHTMLCell(0, 0, $x_inicio, '', $observaciones, 0, 1, 0, true, 'J', true);
    $pdf->Ln(5);
    $pdf->MultiCell(0, 7, 'Recepcionó miembro de la comisión: '.$recepciono, 0, 'L', false, 1, $x_inicio, '');
    $pdf->MultiCell(0, 7, 'Aprobación del Consejo Directivo: Fecha:_____________ Resolución Nº:___________________ ', 0, 'L', false, 1, $x_inicio, '');
    $pdf->MultiCell(0, 7, 'Giro al abogado Civil (    )    Penal (    )', 0, 'L', false, 1, $x_inicio, '');
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->Ln(5);
    $pdf->MultiCell(0, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'R', false, 0, '150', '');
    $pdf->lastPage();

    //ob_clean();
    /* Finalmente generamos el PDF */
    $pdf->Output($nombreArchivo, 'F');       

    if (file_exists($nombreArchivo)) {
        $pdf_content = file_get_contents($nombreArchivo);        
        $caratulaPDF = base64_encode($pdf_content);
    } else {
        echo 'no pudo generar recibo';
        $caratulaPDF = NULL;
    }
} else {
    echo 'no pudo generar recibo - ingreso incorrecto';
    $caratulaPDF = NULL;
}
