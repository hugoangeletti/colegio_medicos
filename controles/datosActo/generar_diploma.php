<?php
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
//require_once('../../TCPDF-php8-main/tcpdf.php');

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
/*
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
*/
if ($continua) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    //$camino .= '../archivos/tmp/';
    //$nombreArchivo = $camino.'50'.'.pdf';
    $camino .= PATH_PDF.'/archivos/tmp/';
    if ($diplomaUnico) {
        $nombreArchivo = $camino.'Diploma_'.$matricula.'_'.$antiguedad.'años.pdf';
    } else {
        $nombreArchivo = $camino.'Diplomas_'.$antiguedad.'años.pdf';
    }
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
    $fontname = TCPDF_FONTS::addTTFfont('../public/fonts/AVGARDD_2.TTF', 'TrueTypeUnicode', '', 32);
    $fontname_o = TCPDF_FONTS::addTTFfont('../public/fonts/AVGARDDO_2.TTF', 'TrueTypeUnicode', '', 32);
    $fontname_n = TCPDF_FONTS::addTTFfont('../public/fonts/AVGARDN_2.TTF', 'TrueTypeUnicode', '', 32);

    foreach ($actoColegiados as $colegiado) {
        $medidas = array(346, 275); // Ancho: 400mm, Alto: 271mm
        $pdf->AddPage('L', $medidas);

        $pdf->SetFont($fontname_n, '', 36);
        
        $pdf->SetY(140, 121);
        $pdf->MultiCell(380, 0, 'Diploma de Honor', 0, 'C', false, 1, '', '');

        if ($colegiado['sexo'] == 'M') {
            $profe = 'del Dr. ';
        } else {
            $profe = 'de la Dra. ';
        }
        $profe .= '<u><b>'.ucwords(strtolower($colegiado['apellidoNombre'])).'</b></u>';
        $html = 'Reconocimiento del Colegio de Médicos de la Provincia de Buenos Aires Distrito I, a la trayectoria '.$profe.' Matrícula Provincial <u><b>'.$colegiado['matricula'].'</b></u>, por haber cumplido '.$antiguedad.' años de egresado en la Carrera de Medicina.-';
        $x = 70;
        $y = 160;
        $pdf->SetFont($fontname_n, '', 18);
        $pdf->writeHTMLCell(230, 0, $x, $y, $html, 0, 1, 0, true, 'J', true);

        $pdf->SetFont($fontname_n, '', 16);
        $laFecha = 'La Plata, '.substr($fechaActo, 8, 2).' de '.obtenerMes(substr($fechaActo, 5, 2)).' de '.substr($fechaActo, 0, 4).'.';
        $pdf->SetY(190);
        $pdf->MultiCell(295, 0, $laFecha, 0, 'R', false, 0, '', '');

        $pdf->SetFont($fontname, '', 10);
        $pdf->SetY(217);
        $pdf->MultiCell(100, 5, $firmaColegio2, 0, 'C', false, 0, 72, '');
        $pdf->MultiCell(100, 5, $firmaColegio1, 0, 'C', false, 1, 215, '');
        $pdf->SetY(222);
        $pdf->MultiCell(100, 5, $cargoColegio2, 0, 'C', false, 0, 72, '');
        $pdf->MultiCell(100, 5, $cargoColegio1, 0, 'C', false, 1, 215, '');
        $pdf->SetY(227);
        $pdf->MultiCell(100, 5, 'Distrito I', 0, 'C', false, 0, 72, '');
        $pdf->MultiCell(100, 5, 'Distrito I', 0, 'C', false, 1, 215, '');

        //imprimir dorso
        //require 'dorso_titulo_especialista.php';
        //ob_clean();
        
    }
    $pdf->Output($nombreArchivo, 'F');       

    if (file_exists($nombreArchivo)) {
        $pdf_content = file_get_contents($nombreArchivo);        
        $diplomaPDF = base64_encode($pdf_content);
    } else {
        echo 'no pudo generar diploma';
        $diplomaPDF = NULL;
    }
} else {
    echo 'no pudo generar diploma - ingreso incorrecto';
    $diplomaPDF = NULL;
}
