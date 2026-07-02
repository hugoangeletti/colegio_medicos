<?php
require_once($pathOrigen.'../tcpdf/config/lang/spa.php');
require_once($pathOrigen.'../tcpdf/tcpdf.php');
//require_once('../TCPDF-php8-main/tcpdf.php');

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

$continua = TRUE;
//firmantes
$colegiadoLogic = new colegiadoLogic();
$resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(1); 
if ($resFirmante['estado']) {
    $firmante = $resFirmante['datos'];
    $firmaColegio1 = 'Dr. '.$firmante['nombre'].' '.$firmante['apellido'];
    $firmaColegio1 = ucwords(strtolower($firmaColegio1));
    $cargoColegio1 = 'Presidente del Colegio de Médicos';
} else {
    $continua = FALSE;
}
$resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
if ($resFirmante['estado']) {
    $firmante = $resFirmante['datos'];
    $firmaColegio2 = 'Dr. '.$firmante['nombre'].' '.$firmante['apellido'];
    $firmaColegio2 = ucwords(strtolower($firmaColegio2));
    $cargoColegio2 = 'Secretario General del Colegio de Médicos';
} else {
    $continua = FALSE;
}

//firmantes UBA
$resFirmante = $colegiadoEspecialistaLogic->firmantesPorEntidad('UNLP', 1);
if ($resFirmante['estado']) {
    $firmante = $resFirmante['datos'];
    $firmaEntidad1 = $firmante['titulo'].' '.$firmante['apellidoNombre'];
    $firmaEntidad1 = ucwords(strtolower($firmaEntidad1));
    $cargoEntidad1 = $firmante['cargo'];
} else {
    $continua = FALSE;
}
$resFirmante = $colegiadoEspecialistaLogic->firmantesPorEntidad('UNLP', 2);
if ($resFirmante['estado']) {
    $firmante = $resFirmante['datos'];
    $firmaEntidad2 = $firmante['titulo'].' '.$firmante['apellidoNombre'];
    $firmaEntidad2 = ucwords(strtolower($firmaEntidad2));
    $cargoEntidad2 = $firmante['cargo'];
} else {
    $continua = FALSE;
}

//obtenemos el hash para el qr
$resHash = $colegiadoEspecialistaLogic->obtenerCodigoQR($idColegiadoEspecialista);

if ($resHash['estado']) {
    $hash_qr = $resHash['hash_qr'];
} 
if (!isset($hash_qr) || $hash_qr == "") {
    $pathArchivo = NULL;
    $nombreArchivo = NULL;
    $creado = date('YmdHis');
    $hash_qr = hashData($idColegiadoEspecialista.'_'.$matricula.'_'.$creado);
    $resultado = $colegiadoEspecialistaLogic->guardarQrColegiadoEspecialista($idColegiadoEspecialista, $hash_qr, $pathArchivo, $nombreArchivo);
}    

//if ($continua && isset($idResolucionDetalle) && $idResolucionDetalle > 1) {
if ($continua) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF.'/archivos/tmp/';
    $nombreArchivo = $camino.$idColegiado.'_'.$idColegiadoEspecialista.'.pdf';
    //echo $nombreArchivo; 
    //exit;
    if (!file_exists($camino)) {
        mkdir($camino, 0777, true);
    }

    //si el pdf ya existe, lo elimino
    if (file_exists($nombreArchivo)) {
        unlink($nombreArchivo);  
    }      

    $pdf = new MYPDF('L', PDF_UNIT, 'A1', true, 'UTF-8', false);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetMargins(0, 0, 0);
    //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetHeaderMargin(0);
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Añadir la fuente (una sola vez, luego se puede comentar)
    $fontname = TCPDF_FONTS::addTTFfont('../public/fonts/EnglischeSchT.ttf', 'TrueTypeUnicode', '', 32);
    $fontname_negrita = TCPDF_FONTS::addTTFfont('../public/fonts/EnglischeSchTBold.ttf', 'TrueTypeUnicode', '', 32);

    $pdf->SetFont($fontname, '', 12);
    $medidas = array(400, 280); // Ancho: 400mm, Alto: 271mm
    $pdf->AddPage('L', $medidas);

    //imprimo la planilla
    $image_escudo = '../public/images/UBA especialista.jpg';
    $pdf->Image($image_escudo, 120, 14, 146, 48, 'jpg', '', 'T', false, '', '', false, false, 0, false, false, false);

    // se arma el texto según el sexo del colegiado
    if ($sexo == 'M') {
        $al = "el Doctor";
    } else {
        $al = "la Doctora";
    }
    $apellidoNombre = ucwords(strtolower($apellidoNombre));
    $mayusculasAcentuadas = array("Á", "É", "Í", "Ó", "Ú", "Ü", "Ñ", "Ö");
    $minusculasAcentuadas = array("á", "é", "í", "ó", "ú", "ü", "ñ", "ö");
    $apellidoNombre = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $apellidoNombre);
    $especialidadDetalle = ucwords(strtolower($especialidadDetalle));
    $especialidadDetalle = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $especialidadDetalle);
    //echo $apellidoNombre; exit;
    $pdf->SetFont($fontname, '', 26);
    //parrafo 1
    $html = 'Por cuanto '.$al.' <u><b>'.$apellidoNombre.'</b></u> Matrícula Provincial N° <u><b>'.$matricula.'</b></u> ha cumplido con los recaudos exigidos en el Reglamento de Especializaciones y del Ejercicio de las mismas, según fija el Decreto 5413/58.';
    $x = 40;
    $y = 98;
    $pdf->writeHTMLCell(320, 0, $x, $y, $html, 0, 1, 0, true, 'J', true);

    //parrafo 2
    $html = 'Por lo tanto la Facultad de Ciencias Médicas de la Universidad Nacional de La Plata y el Colegio de Médicos de la Provincia de Buenos Aires, le otorgan la Certificación del Título de Especialista en: ';
    $y = 133;
    $pdf->writeHTMLCell(320, 0, $x, $y, $html, 0, 1, 0, true, 'J', true);

    //especialidad
    $pdf->SetFont($fontname_negrita, 'U', 38);
    $pdf->SetY(165);
    $pdf->MultiCell(380, 0, $especialidadDetalle, 0, 'C', false, 1, '', '');

    $pdf->SetFont($fontname_negrita, '', 38);
    //verificar si tiene fecha de vencimiento para imprimir la validez
    $valido = NULL;
    $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
    if (!$resConsultor['estado']){
        if (isset($fechaVencimiento) && $fechaVencimiento <> "") {
            //si es el primer vencimiento, se pone como Certificada, sino va Recertificada
            if (!empty($fechaRecertificacion)) {
                $fecha = new DateTime($fechaRecertificacion);
                $fecha = $fecha->add(new DateInterval('P5Y'));
            } else {
                $fecha = new DateTime($fechaEspecialista);
            }
            if ($fechaEspecialista == $fecha->format("Y-m-d")) {
                $valido = 'Certificada hasta el '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'.-';
            } else {
                $valido = 'Recertificada hasta el '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'.-';
            }
        }
    }

    //si viene con fecha de expedicion distinta a la fecha aprobada, entonces se imprime la fecha ingresada
    if (isset($_POST['fechaExpedicion']) && $_POST['fechaExpedicion'] <> "") {
        $fechaAprobada = $_POST['fechaExpedicion'];
    }
    $laFecha = 'La Plata, '.substr($fechaAprobada, 8, 2).' de '.obtenerMes(substr($fechaAprobada, 5, 2)).' de '.substr($fechaAprobada, 0, 4);
    $pdf->SetFont($fontname, '', 22);
    $pdf->SetY(183);
    if (isset($valido)) {
        $pdf->SetX(40);
        $pdf->MultiCell(0, 0, $valido, 0, 'L', false, 0, '', '');
    }
    $pdf->SetX(226);
    $pdf->MultiCell(130, 0, $laFecha, 0, 'R', false, 1, '', '');

    $pdf->SetFont($fontname, '', 14);
    $pdf->SetXY(380, 200);
    $pdf->StartTransform();
    $pdf->Rotate(90);
    $pdf->Cell(50,0,'Resolución Nº '.$numeroResolucion.'  Diploma Nº',0,0,'L','','');
    $pdf->StopTransform();    

    $pdf->SetFont($fontname, '', 11);
    $pdf->SetY(212);
    $pdf->MultiCell(80, 5, $firmaEntidad1, 0, 'C', false, 0, 90, '');
    $pdf->MultiCell(80, 5, $firmaEntidad2, 0, 'C', false, 1, 230, '');
    $pdf->SetY(217);
    $pdf->MultiCell(80, 5, $cargoEntidad1, 0, 'C', false, 0, 90, '');
    $pdf->MultiCell(80, 5, $cargoEntidad2, 0, 'C', false, 1, 230, '');

    
    $pdf->SetFont($fontname, '', 11);
    $pdf->SetY(240);
    $pdf->MultiCell(50, 5, $firmaColegio2, 0, 'C', false, 0, 67, '');
    $pdf->MultiCell(50, 5, $firmaColegio1, 0, 'C', false, 1, 268, '');
    $pdf->SetY(245);
    $pdf->MultiCell(60, 5, $cargoColegio2, 0, 'C', false, 0, 62, '');
    $pdf->MultiCell(50, 5, $cargoColegio1, 0, 'C', false, 1, 268, '');
    $pdf->SetY(250);
    $pdf->MultiCell(50, 5, 'Distrito I', 0, 'C', false, 0, 67, '');
    $pdf->MultiCell(50, 5, 'Distrito I', 0, 'C', false, 1, 268, '');

    //dorso
    require 'dorso_titulo_especialista_uba_unlp.php';

    //ob_clean();
    /* Finalmente generamos el PDF */
    $pdf->Output($nombreArchivo, 'F');       

    if (file_exists($nombreArchivo)) {
        $pdf_content = file_get_contents($nombreArchivo);        
        $tituloPDF = base64_encode($pdf_content);
    } else {
        echo 'no pudo generar recibo';
        $tituloPDF = NULL;
    }
} else {
    echo 'no pudo generar recibo - ingreso incorrecto';
    $tituloPDF = NULL;
}
