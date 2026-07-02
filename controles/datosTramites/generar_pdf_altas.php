<?php
if ($generar) {
    $path_conf = '../';    
} else {
    $path_conf = '../../';
}
require_once ($path_conf.'dataAccess/config.php');
permisoLogueado();
require_once ($path_conf.'html/head.php');
require_once ($path_conf.'dataAccess/funcionesConector.php');
require_once ($path_conf.'dataAccess/funcionesPhp.php');
require_once ($path_conf.'dataAccess/tramiteLogic.php');
require_once ($path_conf.'dataAccess/envios_caja_medicosLogic.php');

require_once($path_conf.'tcpdf/config/lang/spa.php');
require_once($path_conf.'tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
        public $fechaDesde;
        public $fechaHasta;
        public $path_conf;

        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 12);
                // Title
                //$this->Cell(45, 45, 'Listado Nuevos Colegiados en el período del '.$this->fechaDesde.' al '.$this->fechaHasta, 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);

                //$this->Cell(0, 10, 'Relaciones con la comunidad', 0, false, 'C', 0, '', 0, false, 'T', 'M');
                //$this->Ln(3);
                // Page number
                $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetPrintHeader(true);
$pdf->SetPrintFooter(true);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//define ('PDF_MARGIN_FOOTER', 8);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$continua = TRUE;
if (!isset($idEnviosCajaMedicos)) {
    if (isset($_POST['id']) && $_POST['id'] > 0) {
        $idEnviosCajaMedicos = $_POST['id'];
    } else {
        $idEnviosCajaMedicos = NULL;
        $continua = FALSE;
    }
}
if ($continua) {
    $envioLogic = new enviosCajaMedicosLogic();
    $resEnvio = $envioLogic->obtenerEnvioPorId($idEnviosCajaMedicos);
    if ($resEnvio['estado']) {
        $envio = $resEnvio['datos'];
        $fechaEnvio = substr($envio['fechaEnvio'], 0, 10);
        $fechaDesde = cambiarFechaFormatoParaMostrar($envio['fechaDesde']);
        $fechaHasta = cambiarFechaFormatoParaMostrar($envio['fechaHasta']);
        $mail = $envio['mail'];

        $resTramites = $envioLogic->obtenerEnvioDetalle($idEnviosCajaMedicos);
        if ($resTramites['estado']) {
            $linea = 0;
            $encabezado = TRUE;
            foreach ($resTramites['datos'] as $dato){
                $idEnviosCajaMedicosDetalle = $dato['idEnviosCajaMedicosDetalle'];
                $fecha = $dato['fecha'];
                $idColegiado = $dato['idColegiado'];
                $apellido = $dato['apellido'];
                $nombre = $dato['nombre'];
                $matricula = $dato['matricula'];
                if ($dato['tipoNovedad'] == 'REHABILITACION') {
                    $nombreMovimiento = 'Rehabilitación';
                } else {
                    $nombreMovimiento = $dato['nombreMovimiento'];
                }
                $distritoCambio = $dato['distritoCambio'];
                $telefonoFijo = $dato['telefonoFijo'];
                $telefonoMovil = $dato['telefonoMovil'];
                $correoElectronico = $dato['correoElectronico'];

                if ($encabezado) {
                    $pdf->AddPage();
                    $pdf->SetFont('dejavusans', 'B', 10);
                    $pdf->MultiCell(0, 6, 'Listado de Novedades de Colegiados en el período del '.$fechaDesde.' al '.$fechaHasta, 0, 'C', false, 1, '', '');
                    $pdf->SetFont('dejavusans', '', 10);
                    $pdf->MultiCell(0, 6, 'La Plata, '.substr($fechaEnvio, 8, 2).' de '.obtenerMes(substr($fechaEnvio, 5, 2)).' de '.substr($fechaEnvio, 0, 4), 0, 'R', false, 1, '50', '');
                    $pdf->Ln(5);
                    $alturaLinea = 5;
                    $pdf->SetFont('dejavusans', 'B', 6);
                    $pdf->MultiCell(15, $alturaLinea, 'Matrícula', 0, 'C', false, 0, '5', '');
                    $pdf->MultiCell(0, $alturaLinea, 'Apellido y Nombre', 0, 'L', false, 0, '20', '');
                    $pdf->MultiCell(0, $alturaLinea, 'Tipo Movimiento', 0, 'L', false, 0, '70', '');
                    //$pdf->MultiCell(0, $alturaLinea, 'Distrito', 0, 'L', false, 0, '100', '');
                    $pdf->MultiCell(0, $alturaLinea, 'Fecha', 0, 'L', false, 0, '120', '');                
                    $pdf->MultiCell(0, $alturaLinea, 'Correo Electrónico', 0, 'L', false, 1, '140', '');                
                    $encabezado = FALSE;
                } 

                $linea++;
                $alturaLinea = 5;
                $pdf->SetFont('dejavusans', '', 6);
                //$pdf->MultiCell(0, $alturaLinea, $linea, 0, 'L', false, 0, '', '');
                $pdf->MultiCell(15, $alturaLinea, $matricula, 0, 'C', false, 0, '5', '');
                $pdf->MultiCell(50, $alturaLinea, $apellido.' '.$nombre, 0, 'L', false, 0, '20', '');
                $pdf->MultiCell(0, $alturaLinea, $nombreMovimiento, 0, 'L', false, 0, '70', '');
                //$pdf->MultiCell(0, $alturaLinea, $distritoCambio, 0, 'L', false, 0, '100', '');
                $pdf->MultiCell(0, $alturaLinea, cambiarFechaFormatoParaMostrar($fecha), 0, 'L', false, 0, '120', '');
                $pdf->MultiCell(0, $alturaLinea, $correoElectronico, 0, 'L', false, 1, '140', '');
                //$pdf->Ln(5);

                if(( $linea % 43 ) == 0){
                    $encabezado = TRUE;
                }
            }
            $pdf->writeHTML($html, true, false, false, false, '');
            $pdf->lastPage();

            ob_clean();

            $path = '/archivos/caja_medicos/';
            $camino = $_SERVER['DOCUMENT_ROOT'].PATH_PDF.$path;
            $date = new DateTime($fechaEnvio);
            $fechaPdf = $date->format('Ymd');
            $nombrePdf = 'NovedadesDistritoI_'.$fechaPdf.'.pdf';
            $nombreArchivo = $camino.$nombrePdf;
            if (!file_exists($camino)) {
                mkdir($camino, 0777, true);
            }
            $pdf->Output($nombreArchivo, 'F');       

            if (file_exists($nombreArchivo)) {
                //guardamos el nombre de archivo y path del certificado generado en solitudcertificados
                $resCertificadoPdf = $envioLogic->guardarEnvioArchivo($idEnviosCajaMedicos, $path, $nombrePdf, 'pdf');
                if ($resCertificadoPdf['estado']) {
                    //obtiene el certificado y lo guarda como base64 para mostrar
                    $pdf_content = file_get_contents($nombreArchivo);        
                    $listadoPDF = base64_encode($pdf_content);                    
                } else {
                    $resultado['mensaje'] = $resCertificadoPdf['mensaje'];
                    $listadoPDF = NULL;    
                }
            } else {
                $resultado['mensaje'] = 'no pudo generar envio';
                $listadoPDF = NULL;
            }
        } else {
            $resultado['mensaje'] = $resTramites['mensaje'];
            $listadoPDF = NULL;    
        }
    }
} else {
    echo 'INGRESO ERRONEO';
}                

