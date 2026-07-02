<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
//require_once ('../html/head.php');
//require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../tcpdf/config/lang/spa.php');
require_once ('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
        //Page header
        public function Header() 
        {
            //global $title;
                // Logo
                //$image_file = '../public/images/logo_colmed1_lg.png';
                //$this->Image($image_file, 10, 5, 170, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                 // Set font
                $this->SetFont('helvetica', 'B', 8);
                // Title
                $this->Ln(4);
                $this->Cell(0, 15, TITULO_CAJA, 0, false, 'L', 0, '', 0, false, 'M', 'M');              
                $this->Ln(5);

                $this->SetFont('helvetica', 'B', 6);
                $this->Line(0, 13, 220, 13, array('width' => 0));
                $this->MultiCell(0, 7, 'Recibo', 0, 'L', false, 0, '10', '');
                $this->MultiCell(0, 7, 'Matrícula/Asistente', 0, 'L', false, 0, '27', '');    
                $this->MultiCell(0, 7, 'Apellido y Nombre', 0, 'L', false, 0, '60', '');
                $this->MultiCell(0, 7, 'Importe', 0, 'L', false, 0, '110', '');
                $this->MultiCell(0, 7, 'Forma de Pago', 0, 'L', false, 0, '123', '');
                $this->MultiCell(0, 7, 'Banco', 0, 'L', false, 0, '143', '');
                $this->MultiCell(0, 7, 'Comprobante', 0, 'L', false, 0, '170', '');
                $this->Line(0, 17, 220, 17, array('width' => 0));

                /*
                //MARCA DE AGUA 
                $bMargin = $this->getBreakMargin();
                $auto_page_break = $this->AutoPageBreak;
                $this->SetAutoPageBreak(false, 0);

                $img_file2 = '../../public/images/fondoCertificadoClaro.jpg';
                $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
                $this->SetAutoPageBreak($auto_page_break, $bMargin);
                $this->setPageMark();
                //FIN MARCA DE AGUA 
                */
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
                $this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }

}

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCajaDiaria = $_GET['id'];
    $resCajaDiaria = $cajaDiariaLogic->obtenerCajaDiariaPorId($idCajaDiaria);
    if ($resCajaDiaria['estado']) {
        $cajaDiaria = $resCajaDiaria['datos'];
        $fechaCaja = $cajaDiaria['fechaApertura'];
        define('TITULO_CAJA', 'Caja diaria N° '.$idCajaDiaria.' del día '.cambiarFechaFormatoParaMostrar($fechaCaja));
    } else {
        $continua = FALSE;
        $mensaje .= $resRecibo['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCajaDiariaMovimiento";
}

if ($continua) {
    $resMovimientosCaja = $cajaDiariaLogic->obtenerCajaDiariaMovimientos($idCajaDiaria);
    if ($resMovimientosCaja['estado']) {
        $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP-12, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //imprimo la planilla
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->AddPage();

        //$pdf->MultiCell(0, 7, 'Caja del día '.cambiarFechaFormatoParaMostrar($fechaCaja), 0, 'L', false, 0, '30', '');
        //$pdf->MultiCell(0, 7, 'Número de Caja '.$idCajaDiaria, 0, 'L', false, 1, '100', '');
        //$pdf->Ln(2);
        $pdf->Ln(4);
        $pdf->SetFont('dejavusans', '', 8);
        $totalRecaudacion = 0;
        $lineas = 1;
        foreach ($resMovimientosCaja['datos'] as $dato){
            $idCajaDiariaMovimiento = $dato['idCajaDiariaMovimiento'];
            $idColegiado = $dato['idColegiado'];
            $idAsistente = $dato['idAsistente'];
            $matricula = $dato['matricula'];
            $apellidoNombre = $dato['apellidoNombre'];
            $monto = $dato['monto'];
            $tipo = $dato['tipo'];
            $numero = $dato['numero'];
            $estado = $dato['estado'];
            $formaDePago = $dato['formaDePago'];
            if (isset($formaDePago) && $formaDePago <> "") {
                if (substr($formaDePago, 0, 7) == 'Tarjeta') {
                    $formaDePago = substr($formaDePago, 11, 50);
                } else {
                    if (substr($formaDePago, 0, 14) == 'Transferencia') {
                        $formaDePago = 'Tranfer.';
                    } else {
                        if ($tipo == 'NC') {
                            $formaDePago = 'Devolución';
                        }
                    }
                }
            }
            $nombreBanco = $dato['nombreBanco'];
            if (isset($nombreBanco) && $nombreBanco <> "" && substr($nombreBanco, 0, 5) == 'Banco') {
                $nombreBanco = substr($nombreBanco, 6, 50);
            }
            $comprobante = $dato['comprobante'];
            $nombreUsuario = $dato['usuario'];
            $pdf->MultiCell(0, 7, $tipo.' '.rellenarCeros($numero, 8), 0, 'L', false, 0, '5', '');
            if (isset($idAsistente) && $idAsistente <> "") {
                $pdf->MultiCell(0, 7, 'As.'.$idAsistente, 0, 'L', false, 0, '30', '');    
            } else {
                if (isset($matricula) && $matricula <> "") {
                    $pdf->MultiCell(0, 7, 'MP.'.$matricula, 0, 'L', false, 0, '30', '');
                }
            }
            $pdf->MultiCell(0, 7, substr($apellidoNombre, 0, 30), 0, 'L', false, 0, '50', '');
            if ($estado <> 'A') {
                $pdf->MultiCell(0, 7, number_format($monto, 2, '.', ','), 0, 'L', false, 0, '105', '');
                $pdf->MultiCell(0, 7, $formaDePago, 0, 'L', false, 0, '122', '');
                $pdf->MultiCell(0, 7, substr($nombreBanco, 0, 20), 0, 'L', false, 0, '138', '');
                $pdf->SetFont('dejavusans', '', 6);
                $pdf->MultiCell(25, 7, $comprobante, 0, 'L', false, 0, '165', '');
                $pdf->MultiCell(15, 7, $nombreUsuario, 0, 'L', false, 0, '190', '');
                $pdf->SetFont('dejavusans', '', 8);
                $totalRecaudacion += $monto;
            } else {
                $pdf->SetFont('dejavusans', 'B', 8);
                $pdf->MultiCell(0, 7, 'A N U L A D O', 0, 'L', false, 0, '105', '');
                $pdf->SetFont('dejavusans', '', 8);
            }
            $pdf->Ln(5);
            $lineas += 1;
            if ($lineas > 50) {
                $lineas = 1;
                $pdf->AddPage();
                $pdf->Ln(4);
                $pdf->SetFont('dejavusans', '', 8);
            }
        }
        $pdf->SetFont('dejavusans', 'B', 8);
        $pdf->MultiCell(0, 7, 'Total de Recaudacion: '.number_format($totalRecaudacion, 2, '.', ','), 0, 'L', false, 0, '10', '');
        $pdf->SetFont('dejavusans', '', 8);
        ob_clean();
        //echo 'guardar recibo -> '.$nombreArchivo;
        $pdf->Output('CajaDiaria_'.$idCajaDiaria, 'I');       
    } else {
        echo $resMovimientosCaja['mensaje'];
    }
    ?>
<?php
} else {
    echo 'error de Acceso';
}

