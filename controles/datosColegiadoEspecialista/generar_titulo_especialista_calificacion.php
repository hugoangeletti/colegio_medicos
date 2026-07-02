<?php
require_once($pathOrigen.'../tcpdf/config/lang/spa.php');
require_once($pathOrigen.'../tcpdf/tcpdf.php');
//require_once('../TCPDF-php8-main/tcpdf.php');
// Reportar todos los errores de PHP
error_reporting(E_ALL);

// Forzar que se muestren en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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

//obtener la especialidad base de la calificacion agregada
$resBase = $colegiadoEspecialistaLogic->obtenerEspecialidadBase($idColegiado, $especialidad);
if ($resBase['estado']) {
    $especialidadBaseDetalle = $resBase['especialidadBaseDetalle'];
} else {
    $continua = FALSE;
    echo $resBase['mensaje'];
}
//if ($continua && isset($idResolucionDetalle) && $idResolucionDetalle > 1) {
if ($continua) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF.'/archivos/titulo_especialista/';
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

    $pdf->SetFont($fontname, '', 22);
    $medidas = array(346, 275); // Ancho: 400mm, Alto: 271mm
    $pdf->AddPage('L', $medidas);

    // se arma el texto según el sexo del colegiado
    if ($sexo == 'M') {
        $al = "al Doctor";
    } else {
        $al = "a la Doctora";
    }

    $apellidoNombre          = formatearTexto($apellidoNombre);
    $especialidadDetalle     = formatearTexto($especialidadDetalle);
    $especialidadBaseDetalle = formatearTexto($especialidadBaseDetalle);
    /*
    $apellidoNombre = ucwords(strtolower($apellidoNombre));
    $mayusculasAcentuadas = array("Á", "É", "Í", "Ó", "Ú", "Ü", "Ñ", "Ö");
    $minusculasAcentuadas = array("á", "é", "í", "ó", "ú", "ü", "ñ", "ö");
    $apellidoNombre = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $apellidoNombre);
    $especialidadDetalle = ucwords(strtolower($especialidadDetalle));
    $especialidadDetalle = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $especialidadDetalle);
    $especialidadBaseDetalle = ucwords(strtolower($especialidadBaseDetalle));
    $especialidadBaseDetalle = str_replace($mayusculasAcentuadas, $minusculasAcentuadas, $especialidadBaseDetalle);
    */
    $html = 'Por cuanto '.$al.' <u><b>'.$apellidoNombre.'</b></u> Matrícula Provincial N° <u><b>'.$matricula.'</b></u>,ha cumplido con lo dispuesto en el Reglamento de las Especializaciones y del Ejercicio de las Especialidades del Colegio de Médicos de la Provincia de Buenos Aires; por lo tanto se deja constancia que estando certificado en la especialidad <u><b>'.$especialidadBaseDetalle.'</b></u> , ha acreditado de acuerdo a las disposiciones vigentes, la Calificación Agregada:';
    $x = 70;
    $y = 123;
    $pdf->writeHTMLCell(235, 0, $x, $y, $html, 0, 1, 0, true, 'J', true);

    //especialidad
    if (strlen($especialidadDetalle) > 50) {
        $pdf->SetFont($fontname_negrita, '', 24);
    } else {
        $pdf->SetFont($fontname_negrita, '', 28);
    }
    $pdf->SetY(180);
    $pdf->MultiCell(380, 0, $especialidadDetalle, 0, 'C', false, 1, '', '');

    $pdf->SetFont($fontname_negrita, '', 28);

    //verificar si tiene fecha de vencimiento para imprimir la validez
    $valido = NULL;
    $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
    if (!$resConsultor['estado']){
        if (isset($fechaVencimiento) && $fechaVencimiento <> "") {
            //si es el primer vencimiento, se pone como Certificada, sino va Recertificada
            $fecha = new DateTime($fechaRecertificacion);
            $fecha = $fecha->add(new DateInterval('P5Y'));
            if ($fechaEspecialista == $fecha->format("Y-m-d")) {
                $valido = 'Certificada hasta el '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'.-';
            } else {
                $valido = 'Recertificada hasta el '.cambiarFechaFormatoParaMostrar($fechaVencimiento).'.-';
            }
        } else {
            var_dump($fechaVencimiento);
        }
    }
    $pdf->SetFont($fontname_negrita, '', 20);
    $laFecha = 'La Plata, '.substr($fechaAprobada, 8, 2).' de '.obtenerMes(substr($fechaAprobada, 5, 2)).' de '.substr($fechaAprobada, 0, 4).'.';
    $pdf->SetY(195);
    $pdf->MultiCell(295, 0, $laFecha, 0, 'R', false, 0, '', '');

    if (isset($valido)) {
        $pdf->SetX(70);
        $pdf->MultiCell(0, 0, $valido, 0, 'L', false, 1, '', '');
    }
    $pdf->SetFont($fontname, '', 11);
    $pdf->SetY(217);
    $pdf->MultiCell(60, 5, $firmaColegio2, 0, 'C', false, 0, 72, '');
    $pdf->MultiCell(60, 5, $firmaColegio1, 0, 'C', false, 1, 250, '');
    $pdf->SetY(222);
    $pdf->MultiCell(60, 5, $cargoColegio2, 0, 'C', false, 0, 72, '');
    $pdf->MultiCell(60, 5, $cargoColegio1, 0, 'C', false, 1, 250, '');
    $pdf->SetY(227);
    $pdf->MultiCell(60, 5, 'Distrito I', 0, 'C', false, 0, 72, '');
    $pdf->MultiCell(60, 5, 'Distrito I', 0, 'C', false, 1, 250, '');

    //imprimir dorso
    require 'dorso_titulo_especialista.php';

    //ob_clean();
    /* Finalmente generamos el PDF */
    $pdf->Output($nombreArchivo, 'F');       

    if (file_exists($nombreArchivo)) {
        $pdf_content = file_get_contents($nombreArchivo);        
        $tituloPDF = base64_encode($pdf_content);
    } else {
        echo 'no pudo generar titulo';
        $tituloPDF = NULL;
    }
} else {
    echo 'no pudo generar titulo - ingreso incorrecto';
    $tituloPDF = NULL;
}
