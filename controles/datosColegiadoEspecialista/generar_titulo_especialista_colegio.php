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
    $image_escudo = '../public/images/Escudo.jpg';
    $image_logo = '../public/images/logo_colmed1.png';
    $pdf->Image($image_escudo, 54, 21, 25, 35, 'jpg', '', 'T', false, '', '', false, false, 0, false, false, false);
    $pdf->SetFont('freesans', 'B', 11);
    $pdf->SetXY(45, 60);
    $pdf->MultiCell(90, 0, 'REPÚBLICA ARGENTINA', 0, 'C', false, 1, 20, '');
    $pdf->Image($image_logo, 307, 21, 35, 35, 'png', '', 'T', false, '', '', false, false, 0, false, false, false);
    $pdf->SetFont('freesans', 'B', 11);
    $pdf->SetY(60);
    $pdf->MultiCell(100, 0, 'COLEGIO DE MEDICOS', 0, 'C', false, 1, 275, '');
    //$pdf->SetFont('freesans', 'B', 10);
    $pdf->MultiCell(100, 0, 'DE LA PROVINCIA DE BUENOS AIRES', 0, 'C', false, 1, 275, '');
    $pdf->MultiCell(100, 0, 'DISTRITO I', 0, 'C', false, 1, 275, '');

    // se arma el texto según el sexo del colegiado
    if ($sexo == 'M') {
        $al = "al Doctor";
    } else {
        $al = "a la Doctora";
    }
    $apellidoNombre          = formatearTexto($apellidoNombre);
    $especialidadDetalle     = formatearTexto($especialidadDetalle);
    /*
    $apellidoNombre = ucwords(strtolower($apellidoNombre));
    $mayusculasAcentuadas = array("Á", "É", "Í", "Ó", "Ú", "Ü", "Ñ", "Ö");
    $minusculasAcentuadas = array("á", "é", "í", "ó", "ú", "ü", "ñ", "ö");
    $apellidoNombre = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $apellidoNombre);
    $especialidadDetalle = ucwords(strtolower($especialidadDetalle));
    $especialidadDetalle = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $especialidadDetalle);
    //echo $apellidoNombre; exit;
    */
    $pdf->SetFont($fontname, '', 26);
    $html = '<p style="line-height: 1.5;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        El Colegio de Médicos de la Provincia de Buenos Aires autoriza '.$al.' <u><b>'.$apellidoNombre.'</b></u> Matrícula Provincial N° <u><b>'.$matricula.'</b></u> a utilizar el título de especialista en <u><b>'.$especialidadDetalle.'</b></u> en razón de haber cumplimentado los recaudos exigidos en el Reglamento de Especializaciones y del Ejercicio de las mismas, según fija el Decreto 5413/58.';
    $x = 40;
    $y = 100;
    $pdf->writeHTMLCell(320, 0, $x, $y, $html, 0, 1, 0, true, 'J', true);

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
    $laFecha = 'La Plata, '.substr($fechaAprobada, 8, 2).' de '.obtenerMes(substr($fechaAprobada, 5, 2)).' de '.substr($fechaAprobada, 0, 4);
    $pdf->SetFont($fontname, 'B', 22);
    if (isset($valido)) {
        $pdf->SetXY(40, 180);
        $pdf->MultiCell(0, 0, $valido, 0, 'L', false, 1, '', '');
    }
    $pdf->SetXY(226, 180);
    $pdf->MultiCell(130, 0, $laFecha, 0, 'R', false, 1, '', '');

    $pdf->SetFont($fontname, '', 14);
    $pdf->SetXY(380, 200);
    $pdf->StartTransform();
    $pdf->Rotate(90);
    $pdf->Cell(50,0,'Resolución Nº '.$numeroResolucion.'  Diploma Nº',0,0,'L','','');
    $pdf->StopTransform();    

    $pdf->SetFont($fontname, '', 14);
    $pdf->SetXY(40, 210);
    $pdf->MultiCell(0, 0, 'La presente certificación de Especialista se adecua al Programa Nacional de Garantía de Calidad de la Atención Médica según Resolución 946/02 del Ministerio de Salud de la Nación.', 0, 'L', false, 1, '', '');
    
    $pdf->SetFont($fontname, '', 11);
    $pdf->SetY(238);
    $pdf->MultiCell(50, 5, $firmaColegio2, 0, 'C', false, 0, 67, '');
    $pdf->MultiCell(50, 5, $firmaColegio1, 0, 'C', false, 1, 268, '');
    $pdf->SetY(243);
    $pdf->MultiCell(60, 5, $cargoColegio2, 0, 'C', false, 0, 60, '');
    $pdf->MultiCell(50, 5, $cargoColegio1, 0, 'C', false, 1, 268, '');
    $pdf->SetY(248);
    $pdf->MultiCell(50, 5, 'Distrito I', 0, 'C', false, 0, 67, '');
    $pdf->MultiCell(50, 5, 'Distrito I', 0, 'C', false, 1, 268, '');

    //dorso
    require 'dorso_titulo_especialista.php';

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
