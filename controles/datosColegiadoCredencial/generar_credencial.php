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

                $img_file2 = '../public/images/activo.png';
                $this->Image($img_file2, 0, 0, 85, 54, '', '', '', false, 300, '', false, false, 0);
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
    $cargoColegio1 = 'Presidente';
} else {
    $continua = FALSE;
}
$resFirmante = $colegiadoLogic->obtenerFirmaPorCargo(2); 
if ($resFirmante['estado']) {
    $firmante = $resFirmante['datos'];
    $firmaColegio2 = 'Dr. '.$firmante['nombre'].' '.$firmante['apellido'];
    $firmaColegio2 = ucwords(strtolower($firmaColegio2));
    $cargoColegio2 = 'Secretario General';
} else {
    $continua = FALSE;
}

$resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '1');
if ($resArchivos['estado'] && isset($resArchivos['datos'])){
    $archivos = $resArchivos['datos'];
    $fileFoto = trim($archivos['nombre']);
    // insertamos la foto
    $foto = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Fotos/".$fileFoto, "rb");
    if ($foto) {
        $foto_imprimir = stream_get_contents($foto);
        fclose ($foto);
    }
} else {
    $continua = FALSE;
}

if ($continua) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    //$camino .= '../archivos/tmp/';
    //$nombreArchivo = $camino.'50'.'.pdf';
    $camino .= PATH_PDF.'/archivos/credencial/';
    $nombreArchivo = $camino.'Credencial_'.$matricula.'.pdf';
    //echo $nombreArchivo; 
    //exit;
    if (!file_exists($camino)) {
        mkdir($camino, 0777, true);
    }

    //si el pdf ya existe, lo elimino
    if (file_exists($nombreArchivo)) {
        unlink($nombreArchivo);  
    }      

    $medidas = array(85, 54); // Ancho: 400mm, Alto: 271mm
    $pdf = new MYPDF('L', PDF_UNIT, $medidas, true, 'UTF-8', false);
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
    //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetAutoPageBreak(TRUE, 0);
    //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->setImageScale(1);

    // Añadir la fuente (una sola vez, luego se puede comentar)
    $fontname = TCPDF_FONTS::addTTFfont('../public/fonts/AVGARDD_2.TTF', 'TrueTypeUnicode', '', 32);
    $fontname_o = TCPDF_FONTS::addTTFfont('../public/fonts/AVGARDDO_2.TTF', 'TrueTypeUnicode', '', 32);
    $fontname_n = TCPDF_FONTS::addTTFfont('../public/fonts/AVGARDN_2.TTF', 'TrueTypeUnicode', '', 32);

    //$pdf->AddPage('L', $medidas);
    $pdf->AddPage();
    $pdf->SetFont($fontname_n, '', 10);

    $tarjeta = '../public/images/activo.png';
    $foto_colegiado = 'data://text/plain;base64,' . base64_encode($foto_imprimir);

    $pdf->Image($tarjeta, '', '', 85, 54, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
    if ($sexo == 'M') {
        $profe = 'Dr. ';
    } else {
        $profe = 'Dra. ';
    }
    $profe .= ucwords(strtolower($apellidoNombre));

    $pdf->SetY(21);
    $pdf->MultiCell(0, 5, $profe, 0, 'L', false, 1, 5, '');
    $pdf->MultiCell(0, 5, 'M.P. '.$matricula, 0, 'L', false, 1, 5, '');
    $pdf->MultiCell(0, 5, 'DNI '.$numeroDocumento, 0, 'L', false, 1, 5, '');

    $pdf->Image($foto_colegiado, 60, 21, 20, 20, 'JPG', '', '', true, 300, '', false, false, 0, false, false, false);

    $archivo = rellenarceros($matricula, 8);
    //firma del colegiado
    $resArchivos = $colegiadoArchivoLogic->obtenerColegiadoArchivo($idColegiado, '2');
    if ($resArchivos['estado'] && isset($resArchivos['datos'])){
        $archivos = $resArchivos['datos'];
        $fileFirma = trim($archivos['nombre']);
        $firma = @fopen ("ftp://webcolmed:web.2017@192.168.2.50:21/Firmas/".$fileFirma, "rb");
        if ($firma) {
            $contents=stream_get_contents($firma);
            fclose ($firma);
            $firmaVer = base64_encode($contents);
            $tieneFotoFirma = TRUE;
            $jpgFile = "../../archivos/tmp/".$archivo.".jpg";
        }
    }

    /*
    $ftp_server = "ftp://192.168.2.50:21";
    $ftp_user = "webcolmed";
    $ftp_pass = "web.2017";
    $remote_file = "/Firmas/".$archivo.".bmp";
    $local_file = "../../archivos/tmp/imagen_local.bmp";
    $jpg_file = "../../archivos/tmp/".$archivo.".jpg";

    // Conectar y loguearse
    $conn_id = ftp_connect($ftp_server);
    ftp_login($conn_id, $ftp_user, $ftp_pass);
    ftp_pasv($conn_id, true); // Modo pasivo recomendado

    // Descargar en modo binario
    $handle = fopen($local_file, 'w');
    if (ftp_fget($conn_id, $handle, $remote_file, FTP_BINARY)) {
        echo "Descarga exitosa\n";
    } else {
        echo "Error al descargar\n";
    }
    ftp_close($conn_id);
    fclose($handle);
    */
    // Crear imagen desde BMP
    // Ruta del archivo BMP original
    $bmpFile = $firmaVer;
    // Ruta donde se guardará el archivo JPG
    //$jpgFile = $archivo;

    /*
    // 1. Cargar la imagen BMP
    $im = imagecreatefrombmp($bmpFile);

    if ($im) {
        // 2. Guardar la imagen como JPG (con 85% de calidad)
        imagejpeg($im, $jpgFile, 85);
        
        // 3. Liberar memoria
        imagedestroy($im);
        echo "Conversión exitosa: $jpgFile";
    } else {
        echo "Error al convertir la imagen.";
    }
    */
/*
    $pdf->SetFont($fontname_n, '', 16);
    $laFecha = 'La Plata, '.substr($fechaActo, 8, 2).' de '.obtenerMes(substr($fechaActo, 5, 2)).' de '.substr($fechaActo, 0, 4).'.';
    $pdf->SetY(190);
    $pdf->MultiCell(295, 0, $laFecha, 0, 'R', false, 0, '', '');
*/

    $pdf->AddPage('L', $medidas);
    $pdf->SetFont('dejavusans', '', 10);
    //imprimir QR
    $style = array(
            'border' => true,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255)
            'module_width' => 1, // width of a single module in points
            'module_height' => 1 // height of a single module in points
        );
    $codigoQR = 'http://www.colmed1.com.ar/portal/controls/credencial.php?id='.$hashColegiado;
    $pdf->write2DBarcode($codigoQR, 'QRCODE,Q', 55, 5, 25, 25, $style, 'N');
    //fin imprimir QR

    $pdf->SetFont($fontname_n, 'B', 11);
    $pdf->SetXY(5, 5);
    $pdf->MultiCell(0, 5, $profe, 0, 'L', false, 1, 5, '');

    $numeroLibro = 10;
    $numeroFolio = 250;

    $pdf->SetFont($fontname_n, '', 9);
    $html = 'Se encuentra inscripto en este DISTRITO I del Colegio de Médicos con Matrícula Nº '.$matricula.' Libro '.$numeroLibro.' Folio '.$numeroFolio.' con fecha '.cambiarFechaFormatoParaMostrar($fechaMatriculacion).' habiendo llenado los requisitos necesarios para ejercer la MEDICINA en jurisdicción de la Provincia de Buenos Aires, conforme al decreto Ley 5413/58 y Reglamentaciones.';
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, 'J', true);
    $pdf->MultiCell(0, 5, 'Por el Consejo Directivo del Distrito I.-', 0, 'L', false, 1, 5, '');
    
    $pdf->SetY(48);
    $pdf->MultiCell(0, 5, 'SECRETARIO', 0, 'L', false, 0, 5, '');
    $pdf->MultiCell(0, 5, 'PRESIDENTE', 0, 'L', false, 1, 60, '');

    $pdf->Output($nombreArchivo, 'F');       

    if (file_exists($nombreArchivo)) {
        $pdf_content = file_get_contents($nombreArchivo);        
        $credencialPDF = base64_encode($pdf_content);
    } else {
        echo 'no pudo generar diploma';
        $credencialPDF = NULL;
    }
} else {
    echo 'no pudo generar diploma - ingreso incorrecto';
    $credencialPDF = NULL;
}
