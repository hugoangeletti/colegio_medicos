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
            //$this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$continua = TRUE;
$mensaje = "";
$hojaRutaPDF = NULL;
if (isset($idMesaEntrada) && $idMesaEntrada > 1) {
    $idMesaEntradaEntrega = NULL;
    $resMesaEntradaEntrega = $mesaEntradaLogic->obtenerMesaEntradaEntregaPorId($idMesaEntradaEntrega, $idMesaEntrada);
    if ($resMesaEntradaEntrega['estado']) {
        $mesaEntradaEntrega = $resMesaEntradaEntrega['datos'];
        $idMesaEntradaEntrega = $mesaEntradaEntrega['idMesaEntradaEntrega'];
        $idTipoEntrega = $mesaEntradaEntrega['idTipoEntrega'];
        /*
        $idColegiado = $mesaEntradaEntrega['idColegiado'];
        $fechaIngreso = $mesaEntradaEntrega['fechaIngreso'];
        $observaciones = $mesaEntradaEntrega['observaciones'];
        $nombreTipoEntrega = $mesaEntradaEntrega['nombreTipoEntrega'];
        $leyendaTipoEntrega = $mesaEntradaEntrega['leyendaTipoEntrega'];

        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        } else {
            $continua = FALSE;
            $mensaje .= $resColegiado['mensaje'];
        }
        */
        //si tipo de entrega es titulo de especialista, obtenemos que titulo se entrega
        if ($idTipoEntrega == 3) {
            $resTitulo = $mesaEntradaLogic->obtenerTituloEspecialistaPorEntrega($idMesaEntradaEntrega);
            if ($resTitulo['estado']) {
                $titulo = $resTitulo['datos'];
                $leyendaTipoEntrega = $titulo['especialidadEntregar'];
            }
        }
        if ($continua) {
            //guarda pdf
            /* armamaos el path donde se va a guardar el pdf */
            $camino = $_SERVER['DOCUMENT_ROOT'];
            $camino .= PATH_PDF.'/archivos/tmp/';
            $nombreArchivo = $camino.$idMesaEntrada.'.pdf';
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
            //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetFooterMargin(0);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetFont('dejavusans', '', 10);
            $pdf->AddPage();

            $image_file = '../public/images/logo_colmed1_hr.png';
            $pdf->Image($image_file, 5, 5, 80, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->SetXY(150, 15);
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->MultiCell(0, 7, 'Nº '.rellenarCeros($idMesaEntradaEntrega, 8), 0, 'R', false, 1, '115', '');

            $pdf->SetXY(10, 35);
            //imprimo la planilla
            $dia = substr($fechaIngreso, 8, 2);
            $mes = substr($fechaIngreso, 5, 2);
            $anio = substr($fechaIngreso, 0, 4);
            $fecha_texto = 'La Plata, '.$dia.' de '.obtenerMes($mes).' de '.$anio;

            //$pdf->Line(100, 5, 100, 52, array('width' => 0));
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->MultiCell(0, 5, 'CONSTANCIA DE '.strtoupper($nombreTipoEntrega), 0, 'C', false, 1, '30', '');
            $pdf->Ln(7);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->MultiCell(0, 5, $fecha_texto, 0, 'R', false, 1, '50', '');
            $pdf->Ln(7);
            $pdf->MultiCell(0, 5, 'Firmo con la constancia de haber recibido '.$leyendaTipoEntrega.'.-', 0, 'L', false, 1, '', '');
            $pdf->Ln(10);
            $pdf->MultiCell(0, 5, 'Observaciones: __________________________________________________________________________________', 0, 'L', false, 1, '', '');
            $pdf->Ln(7);
            $pdf->MultiCell(0, 5, 'Firma: _______________________________', 0, 'L', false, 1, '', '', true);
            $pdf->Ln(5);
            $pdf->MultiCell(0, 5, 'Apellido y Nombre: '.$nombreRemitente, 0, 'L', false, 1, '', '');
            $pdf->Ln(5);
            $pdf->MultiCell(0, 5, 'Matrícula: '.$matricula, 0, 'L', false, 1, '', '');        

            $pdf->Line(15, 250, 192, 250, array('width' => 1));
            $pdf->SetY(255);
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->MultiCell(50, 7, 'Realizó: '.$nombreUsuario, 0, 'L', false, 0, '35', '');
            $pdf->MultiCell(80, 7, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'L', false, 0, '140', '');
            $pdf->lastPage();
                
            //ob_clean();
            /* Finalmente generamos el PDF */
            //echo 'generar 4'; exit;
            $pdf->Output($nombreArchivo, 'F');       

            if (file_exists($nombreArchivo)) {
                $pdf_content = file_get_contents($nombreArchivo);        
                $hojaRutaPDF = base64_encode($pdf_content);
            } else {
                $mensaje .= 'no pudo generar planilla';
            }
        }
    } else {
        $mensaje .= $resMesaEntradaEntrega['mensaje'];
    }
} else {
    $mensaje .= 'no pudo generar planilla - ingreso incorrecto';
}
