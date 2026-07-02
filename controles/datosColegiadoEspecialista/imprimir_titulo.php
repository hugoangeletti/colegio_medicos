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

if (isset($idResolucionDetalle) && $idResolucionDetalle > 1) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF.'/archivos/tmp/';
    $nombreArchivo = $camino.$idResolucionDetalle.'.pdf';
    //echo $nombreArchivo; 
    //exit;
    if (!file_exists($camino)) {
        mkdir($camino, 0777, true);
    }

    //si el pdf ya existe, lo elimino
    if (file_exists($nombreArchivo)) {
        unlink($nombreArchivo);  
    }      

    $pdf = new MYPDF('P', PDF_UNIT, 'A1', true, 'UTF-8', false);
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

    // Añadir la fuente (una sola vez, luego se puede comentar)
    $fontname = TCPDF_FONTS::addTTFfont('../public/fonts/EnglischeSchT.ttf', 'TrueTypeUnicode', '', 32);
    $fontname_negrita = TCPDF_FONTS::addTTFfont('../public/fonts/EnglischeSchTBold.ttf', 'TrueTypeUnicode', '', 32);
    $pdf->SetFont($fontname, '', 12);
    $pdf->SetFont($fontname_negrita, '', 12);
    $pdf->AddPage();

    //imprimo la planilla
    $image_file = '../public/images/Escudo.jpg';

    $pdf->Image($image_file, 35, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->SetFont($fontname, 'B', 14);
    $pdf->MultiCell(0, 10, 'HOJA DE RUTA', 0, 'L', false, 1, '120', '');
    $pdf->SetFont($fontname, 'B', 12);
    $pdf->MultiCell(0, 7, 'MESA ENTRADA Nº '.$idResolucionDetalle, 0, 'L', false, 1, '120', '');
    $pdf->MultiCell(0, 7, 'REUNIÓN MESA Nº ', 0, 'L', false, 1, '120', '');
    //recuadro numero de reunion de mesa
    $pdf->Line(168, 21, 190, 21, array('width' => 0.50));
    $pdf->Line(168, 21, 168, 28, array('width' => 0.50));
    $pdf->Line(168, 28, 190, 28, array('width' => 0.50));
    $pdf->Line(190, 21, 190, 28, array('width' => 0.50));
    //fin recuadro numero de reunion de mesa

    $pdf->MultiCell(0, 7, 'Fecha: '.cambiarFechaFormatoParaMostrar($fechaAprobada), 0, 'L', false, 1, '120', '');
    $pdf->Ln(5);
    $pdf->SetFont($fontname, 'B', 12);
    $pdf->MultiCell(0, 5, 'Tipo de especialista '.$titulo, 0, 'C', false, 1, '30', '');
    $pdf->Ln(2);
    
    //datos remitente y tema
    $pdf->SetFont($fontname, 'B', 10);
    $pdf->MultiCell(0, 5, 'Matrícula: ', 0, 'L', false, 0, '', '');
    $pdf->SetFont($fontname, '', 10);
    $pdf->MultiCell(0, 5, $matricula, 0, 'L', false, 1, '40', '');
    $pdf->SetFont($fontname, 'B', 10);
    $pdf->MultiCell(0, 5, 'Remitente: ', 0, 'L', false, 0, '', '');
    $pdf->SetFont($fontname, '', 10);
    $pdf->MultiCell(0, 5, $apellidoNombre, 0, 'L', false, 1, '40', '');
    $html = '<b>Especialista en: </b>'.$especialidadDetalle;
    $pdf->writeHTMLCell(0, 10, '', '', $html, 0, 1, 0, true, 'J', true);
    $pdf->Ln(6);

    //cuerpo
    $x_inicio = 10;
    $y_inicio = 80;
    $x_fin = $x_inicio + 190; //200;
    $y_fin = $y_inicio + 80; //185;

    //titulo decision mesa 
    $pdf->SetXY($x_inicio, $y_inicio - 5);
    $pdf->SetFont($fontname, 'B', 12);
    $pdf->MultiCell(120, 7, 'Decisión de la Mesa Directiva', 0, 'C', false, 0, '35', '');
    $pdf->MultiCell(25, 7, 'Firma', 0, 'C', false, 1, '166', '');
    //fin titulo decision mesa 

    //recuadro decision mesa 
    $pdf->Line($x_inicio, $y_inicio, $x_fin, $y_inicio, array('width' => 1));
    $pdf->Line($x_inicio, $y_inicio, $x_inicio, $y_fin, array('width' => 1));
    $pdf->Line($x_inicio + 155, $y_inicio, $x_inicio + 155, $y_fin, array('width' => 1));
    $pdf->Line($x_fin, $y_inicio, $x_fin, $y_fin, array('width' => 1));
    $pdf->Line($x_inicio, $y_fin, $x_fin, $y_fin, array('width' => 1));
    //fin recuadro decision mesa 

    //linea final
    $y_fin_linea = 90;
    $pdf->Line($x_inicio, $y_fin + $y_fin_linea, $x_fin, $y_fin + $y_fin_linea, array('width' => 1));
    $pdf->SetFont($fontname, '', 8);
    $pdf->SetXY($x_inicio, $y_fin + $y_fin_linea + 5);
    //$pdf->MultiCell(50, 7, 'Realizó: '.$nombreUsuario, 0, 'L', false, 0, $x_inicio, '');
    $pdf->MultiCell(50, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'R', false, 0, '150', '');
    $pdf->lastPage();
        
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
