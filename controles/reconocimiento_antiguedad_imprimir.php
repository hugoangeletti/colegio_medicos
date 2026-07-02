<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/reconocimientoAntiguedadLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
set_time_limit(0);

class MYPDF extends TCPDF 
{
        public $el_titulo;

        //Page header
        public function Header() 
        {
                // Logo
                $image_file = '../public/images/logo_colmed1_lg.png';
                $this->Image($image_file, 10, 10, 90, 12, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                //$this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', '', 10);
                $this->SetXY(190, 10);
                $fecha_actual = cambiarFechaFormatoParaMostrar(date('Y-m-d'));
                $this->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'R'); // A la derecha (R)

                // Title
                $this->SetFont('helvetica', 'B', 12);
                $this->Ln(5);        
                $this->Cell(0, 10, $this->el_titulo, 0, false, 'C', 0, '', 0, false, 'M', 'M');
                $this->Ln(5);
                $this->SetFont('helvetica', 'B', 7);
                $this->MultiCell(0, 5, 'Matrícula', 0, 'L', false, 0, '', '');
                $this->MultiCell(65, 5, 'Apellido y Nombres', 0, 'L', false, 0, '20', '');
                $this->MultiCell(30, 5, 'Estado matricular', 0, 'L', false, 0, '80', '');
                $this->MultiCell(10, 5, '', 0, 'C', false, 1, '190', '');
                $this->MultiCell(80, 5, 'Domicilio', 0, 'L', false, 0, '10', '');
                $this->MultiCell(50, 5, 'Teléfonos', 0, 'L', false, 0, '100', '');
                $this->MultiCell(50, 5, 'Email', 0, 'L', false, 0, '160', '');
                $this->MultiCell(20, 5, '', 0, 'C', false, 1, '220', '');

                $y_line = $this->GetY();
                $this->Line(0, $y_line, 220, $y_line, array('width' => 0.5));
        }

        // Page footer
        public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);

                // Page number
                $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && isset($_GET['filtro'])) {
    $estado = $_GET['filtro'];
    $margen_top = 42;
    $idReconocimientoAntiguedad = $_GET['id'];
    $actosLogic = new reconocimientoAntiguedadLogic();
    $resActos = $actosLogic->obtenerActoPorId($idReconocimientoAntiguedad);            
    if ($resActos['estado']){
        $acto = $resActos['datos'];
        $fechaActo = $acto['fechaActo'];
        $lugarActo = $acto['lugarActo'];
        $antiguedad = $acto['antiguedad'];
    } else {
        $continua = FALSE;
        $mensaje .= $resActos['mensaje'];
    }        

    if ($continua) {
        $titulo = 'Acto de entrega de diplomas por los '.$antiguedad.' años de recibido. Fecha del acto: '.cambiarFechaFormatoParaMostrar($fechaActo);
        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, $margen_top, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 20);
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //inicializar variables de header
        $pdf->el_titulo = $titulo;
        $pdf->AddPage();

        $resActoDetalle = $actosLogic->obtenerColegiadosPorActo($idReconocimientoAntiguedad, $estado);
        if ($resActoDetalle['estado']){
            $totalEspecialidad = 0;
            
            foreach ($resActoDetalle['datos'] as $dato) {
                $idReconocimientoAntiguedadDetalle = $dato['idReconocimientoAntiguedadDetalle'];
                $idColegiado = $dato['idColegiado'];
                $matricula = $dato['matricula'];
                $apellidoNombre = $dato['apellidoNombre'];
                $estadoInvitacion = $dato['estadoInvitacion'];
                $estadoMatricular = $dato['estadoMatricular'];
                $codigoDeudor = $dato['codigoDeudor'];
                $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
                if ($resEstadoTesoreria['estado']){
                  $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                } else {
                  $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                }

                $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
                if ($resDomicilio['estado']) {
                    $domicilio = $resDomicilio['datos'];
                    if ($domicilio['calle']) {
                        $domicilioCompleto = $domicilio['calle'];
                        if ($domicilio['numero']) {
                            $domicilioCompleto .= " Nº ".$domicilio['numero'];
                        }
                        if ($domicilio['lateral']) {
                            $domicilioCompleto .= " e/ ".$domicilio['lateral'];
                        }
                        if ($domicilio['piso'] && strtoupper($domicilio['piso']) != "NR") {
                            $domicilioCompleto .= " Piso ".$domicilio['piso'];
                        }
                        if ($domicilio['depto'] && strtoupper($domicilio['depto']) != "NR") {
                            $domicilioCompleto .= " Dto. ".$domicilio['depto'];
                        }
                    }
                    if ($domicilio['nombreLocalidad']) {
                        $domicilioCompleto .= ' ( '.$domicilio['nombreLocalidad'].' )';
                    }
                } else {
                    $domicilioCompleto = NULL;
                    $localidad = NULL;
                }

                $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                if ($resContacto['estado']) {
                    $contacto = $resContacto['datos'];
                    $telefonoFijo = $contacto['telefonoFijo'];
                    $telefonoMovil = $contacto['telefonoMovil'];
                    $email = $contacto['email'];
                } else {
                    $telefonoFijo = NULL;
                    $telefonoMovil = NULL;
                    $email = NULL;
                }
               
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Ln(1);
                $pdf->MultiCell(0, 5, $matricula, 0, 'L', false, 0, '', '');
                $pdf->MultiCell(80, 5, $apellidoNombre, 0, 'L', false, 0, '20', '');
                $pdf->MultiCell(30, 5, $estadoMatricular, 0, 'L', false, 1, '80', '');

                $pdf->SetFont('dejavusans', '', 6);
                $pdf->MultiCell(80, 5, $domicilioCompleto, 0, 'L', false, 0, '', '');
                //$pdf->MultiCell(30, 5, $localidad, 0, 'L', false, 0, '85', '');
                $pdf->MultiCell(20, 5, $telefonoMovil, 0, 'L', false, 0, '100', '');
                $pdf->MultiCell(20, 5, $telefonoFijo, 0, 'L', false, 0, '120', '');
                $pdf->MultiCell(50, 5, $email, 0, 'L', false, 0, '160', '');
                $pdf->MultiCell(20, 5, '', 0, 'C', false, 1, '220', '');

                $y_line = $pdf->GetY();        
                $pdf->Line(0, $y_line, 220, $y_line, array('width' => 0));
                $totalEspecialidad += 1;
            }
            $pdf->lastPage();

            /*
            $destination = 'Presentismo.pdf';
            if (!preg_match('/\.pdf$/', $path_to_store_pdf))
            {
                   $path_to_store_pdf .= '.pdf';
            }
            */
            ob_clean();
            $pdf->Output('Presentismo.pdf', 'I');        
        } else {

        }
    } else {
        echo 'ERROR->'.$mensaje;
    }
} else {
    echo 'ERROR-><br>';
    var_dump($_POST);
}

