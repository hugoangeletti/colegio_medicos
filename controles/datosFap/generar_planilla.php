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

if (isset($idSapConsejo) && $idSapConsejo > 1) {
    //guarda pdf
    /* armamaos el path donde se va a guardar el pdf */
    $camino = $_SERVER['DOCUMENT_ROOT'];
    $camino .= PATH_PDF.'/archivos/tmp/';
    $nombreArchivo = $camino.'FAP_reunion_'.$idSapConsejo.'.pdf';
    //echo $nombreArchivo; 
    //exit;
    if (!file_exists($camino)) {
        mkdir($camino, 0777, true);
    }

    //si el pdf ya existe, lo elimino
    if (file_exists($nombreArchivo)) {
        unlink($nombreArchivo);  
    }      

    $pdf = new MYPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetPrintHeader(true);
    $pdf->SetPrintFooter(false);
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

    //$x_inicio = 10;
    $x_inicio = 3;
    $y_inicio = 5;
    $x_fin = $x_inicio + 290; 
    $y_fin = $y_inicio + 25; 
    $y_fin_linea = $y_fin + 150;
    
    $resDetalle = $fapLogic->obtenerReunionDetallePorIdReunion($idSapConsejo);
    if (!$resDetalle['estado']) {
        echo 'no pudo generar planilla - '.$resDetalle['mensaje'];
        $planillaPDF = NULL;
    } else {
        $fecha = cambiarFechaFormatoParaMostrar($fechaReunion);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->SetXY($x_inicio, $y_fin);
        $pdf->MultiCell(0, 5, 'CAUSAS PARA PRESENTAR EN LA REUNIÓN DE CONSEJO DEL '.$fecha, 0, 'L', false, 1, $x_inicio, '');

        $pdf->SetFont('dejavusans', '', 8);
        $y_fin += 5;
        $pdf->SetXY($x_inicio, $y_fin);
        //recuadro numero de reunion de mesa
        $pdf->Line($x_inicio, $y_fin, $x_fin, $y_fin, array('width' => 0.50));
        $pdf->Line($x_inicio, $y_fin, $x_inicio, $y_fin + 7, array('width' => 0.50));
        $pdf->Line($x_inicio, $y_fin + 7, $x_fin, $y_fin + 7, array('width' => 0.50));
        $pdf->Line($x_fin, $y_fin, $x_fin, $y_fin + 7, array('width' => 0.50));
        //fin recuadro numero de reunion de mesa
        $pdf->Ln(2);
        $x = $x_inicio;
        $pdf->MultiCell(15, 5, 'Orden', 0, 'C', false, 0, $x, '');
        $x += 10;
        $pdf->MultiCell(15, 5, 'N°', 0, 'C', false, 0, $x, '');
        $x += 10;
        $pdf->MultiCell(65, 5, 'Apellido y Nombre', 0, 'L', false, 0, $x, '');
        $x += 65;
        $pdf->MultiCell(17, 5, 'Matrícula', 0, 'L', false, 0, $x, '');
        $x += 14;
        $pdf->MultiCell(120, 5, 'Nombre de la causa', 0, 'L', false, 0, $x, '');
        $x += 120;
        $pdf->MultiCell(20, 5, 'Dto. Judicial', 0, 'L', false, 0, $x, '');
        $x += 20;
        $pdf->MultiCell(30, 5, 'Tipo Causa', 0, 'L', false, 0, $x, '');
        $x += 30;
        $pdf->MultiCell(20, 5, 'Condición', 0, 'L', false, 1, $x, '');
        //$x += 15;
        //$pdf->MultiCell(20, 5, 'Fecha Reunión', 0, 'L', false, 1, $x, '');
        $pdf->Ln(2);
        foreach ($resDetalle['datos'] as $detalle) {
            $orden = $detalle['orden'];
            $idSapConsejoDetalle = $detalle['idSapConsejoDetalle'];
            $idSapCaratula = $detalle['idSapCaratula'];
            $apellidoNombre = trim($detalle['apellido']).' '.trim($detalle['nombre']);
            $matricula = $detalle['matricula'];
            $nombreCausa = $detalle['nombreCausa'];
            $nombreDepartamentoJudicial = $detalle['nombreDepartamentoJudicial'];
            $nombreTipoCausa = $detalle['nombreTipoCausa'];
            $nombreSapCondicion = $detalle['nombreSapCondicion'];

            $x = $x_inicio;
            $pdf->MultiCell(10, 7, $orden, 0, 'R', false, 0, $x, '');
            $x += 10;
            $pdf->MultiCell(10, 7, $idSapCaratula, 0, 'R', false, 0, $x, '');
            $x += 10;
            $pdf->MultiCell(65, 7, $apellidoNombre, 0, 'L', false, 0, $x, '');
            $x += 65;
            $pdf->MultiCell(14, 7, $matricula, 0, 'R', false, 0, $x, '');
            $x += 14;
            $pdf->MultiCell(120, 7, $nombreCausa, 0, 'L', false, 0, $x, '');
            $x += 120;
            $pdf->MultiCell(20, 7, $nombreDepartamentoJudicial, 0, 'L', false, 0, $x, '');
            $x += 20;
            $pdf->MultiCell(30, 7, $nombreTipoCausa, 0, 'L', false, 0, $x, '');
            $x += 30;
            $pdf->MultiCell(20, 7, $nombreSapCondicion, 0, 'L', false, 1, $x, '');
            $y = $pdf->GetY();
            $pdf->Line($x_inicio, $y, $x_fin, $y, array('width' => 0.20));
            $pdf->Ln(2);
            //$x += 15;
            //$pdf->MultiCell(20, 5, $fecha, 0, 'L', false, 1, $x, '');
        }
        $pdf->SetXY($x_fin, $y_fin_linea);
        $pdf->MultiCell(0, 5, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'R', false, 0, '150', '');
        $pdf->lastPage();

        //ob_clean();
        /* Finalmente generamos el PDF */
        $pdf->Output($nombreArchivo, 'F');       

        if (file_exists($nombreArchivo)) {
            $pdf_content = file_get_contents($nombreArchivo);        
            $planillaPDF = base64_encode($pdf_content);
        } else {
            echo 'no pudo generar planilla';
            $planillaPDF = NULL;
        }
    }
} else {
    echo 'no pudo generar planilla - ingreso incorrecto';
    $planillaPDF = NULL;
}
