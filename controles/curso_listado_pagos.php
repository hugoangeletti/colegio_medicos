<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
    //Page header
    public function Header() 
    {
//                // Logo
        $image_file = '../public/images/logo_colmed1_lg.png';
        $this->Image($image_file, 10, 5, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//                 // Set font
        $this->SetFont('helvetica', 'B', 20);
//                // Title
        $this->Cell(0, 15, '', 0, false, 'C', 0, 'Listado de pagos por Curso', 0, false, 'M', 'M');

        $this->SetFont('dejavusans', '', 10);        
        $this->SetXY(0, 25);
        $this->MultiCell(0, 5, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 1, '50', '');
        $this->Ln(2);
        $this->SetFont('dejavusans', 'B', 10);        
        $this->MultiCell(0, 5, 'Cobranza por Cursos. Período: '.ENTRE_FECHA_DESDE.' al '.ENTRE_FECHA_HASTA, 0, 'L', false, 1, '', '');
        //imprimr encabezado de la grilla
        $p1y = 37;
        $alturaLinea = 7;
        $this->SetXY(0, $p1y);
        $this->SetFont('dejavusans', 'B', 10);        
        $this->Ln(2);
        $this->MultiCell(0, 0, 'Curso', 0, 'L', false, 0, '5', '');
        $this->MultiCell(20, 0, 'Pagos', 0, 'C', false, 0, '135', '');
        $this->MultiCell(20, 0, 'Ingresos', 0, 'R', false, 0, '155', '');
        $this->MultiCell(0, 0, 'Período', 0, 'L', false, 1, '178', '');
        $this->Line(0, $p1y, 220, $p1y, array('width' => 0));
        $this->Line(0, $p1y+$alturaLinea, 220, $p1y+$alturaLinea, array('width' => 0));
    }

    // Page footer
    public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);

            //$this->Cell(0, 10, 'x', 0, false, 'C', 0, '', 0, false, 'T', 'M');
            //$this->Ln(3);
            // Page number
            $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$continua = TRUE;
$mensaje = "";
$cursos_pdo = new cursos_pdo();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCurso = $_GET['id'];
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
        $director = $curso['director'];
        $fechaInicio = $curso['fechaInicio'];
        $estadoCurso = $curso['estado'];
        $tema = $curso['tema'];
        $dias = $curso['dias'];
        $fechas = $curso['fechas'];
        $salon = $curso['salon'];
        $lugar = $curso['lugar'];
        $coordinador = $curso['coordinador'];
        $vigenciaHasta = $curso['vigenciaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
    }
} else {
    if (isset($_POST['idCurso']) && $_POST['idCurso'] <> "") {
        $idCurso = $_POST['idCurso'];
    } else {
        $idCurso = NULL;
    }
    if (isset($_POST['fechaDesde']) && $_POST['fechaDesde'] <> "") {
        $fechaDesde = $_POST['fechaDesde'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta fechaDesde - ';
    }
    if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> "") {
        $fechaHasta = $_POST['fechaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta fechaHasta - ';
    }
    if ($fechaHasta < $fechaDesde) {
        $continua = FALSE;
        $mensaje .= 'fechaHasta debe ser mayo a igaul a fechaDesde - ';
    }
    if (isset($_POST['tipoListado']) && $_POST['tipoListado'] <> "") {
        $tipoListado = $_POST['tipoListado'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta tipoListado - ';
    }
    if (isset($_POST['totalizado']) && $_POST['totalizado'] <> "") {
        $totalizado = $_POST['totalizado'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta totalizado - ';
    }
}

$tituloListado = "Cobranza por Cursos. Período ".cambiarFechaFormatoParaMostrar($fechaDesde).' al '.cambiarFechaFormatoParaMostrar($fechaHasta);

if ($continua) {
    $asiste = 'S';
    if (isset($idCurso) && $idCurso <> "") {
        $porCurso = TRUE;
    } else {
        $porCurso = FALSE;
    }
    if ($totalizado == 'FECHA') {
        $resCobranza = $cursos_pdo->obtenerTotalCobranzaPorPerido($idCurso, $fechaDesde, $fechaHasta);
    } else {
        if ($totalizado == 'CUOTA') {
            $resCobranza = $cursos_pdo->obtenerTotalPorCuotaPeriodo($idCurso, $fechaDesde, $fechaHasta);
        } else {
            $resCobranza['estado'] = FALSE;
            $resCobranza['mensaje'] = 'Totalizado incorrecto';
        }
    }
    if ($resCobranza['estado'] && sizeof($resCobranza['datos']) > 0) {
        define("ENTRE_FECHA_DESDE", cambiarFechaFormatoParaMostrar($fechaDesde));
        define("ENTRE_FECHA_HASTA", cambiarFechaFormatoParaMostrar($fechaHasta));

        $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 45, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->AddPage();

        $cantidadAsistentes = 0;
        $alturaLinea = 7;
        $p1y = $pdf->getY();
        $idCursoAnterior = 0;
        $cuotaAnterior = NULL;
        $lineaPeriodoCurso = 0;
        $totalPagosCurso = 0;
        $totalIngresosCurso = 0;
        $imprimirTituloCurso = TRUE;
        foreach ($resCobranza['datos'] as $dato){
            $idCurso = $dato['idCurso'];
            if ($idCurso <> $idCursoAnterior) {
                if ($idCursoAnterior <> 0) {
                    $p1y = $pdf->getY();
                    //if ($lineaPeriodoCurso > 1) {
                        //imprimimos el total del curso
                        $pdf->Line(5, $p1y, 200, $p1y, array('width' => 0));
                        $pdf->SetFont('dejavusans', 'B', 9);
                        $pdf->MultiCell(0, 0, 'TOTAL '.$nombreCurso, 0, 'L', false, 0, '5', '');
                        //$pdf->MultiCell(20, 0, $totalPagosCurso, 0, 'C', false, 0, '135', '');
                        $pdf->MultiCell(0, 0, number_format($totalIngresosCurso, 2, '.', ','), 0, 'R', false, 1, '155', '');
                        $pdf->Line(5, $p1y+5, 200, $p1y+5, array('width' => 0));
                        $pdf->Ln(2);
                    //} else {
                        //imprimimos linea de cierre
                    //    $pdf->Line(5, $p1y, 200, $p1y, array('width' => 0.5));
                    //}
                    $imprimirTituloCurso = TRUE;
                }
                $idCursoAnterior = $idCurso;
                $totalPagosCurso = 0;
                $totalIngresosCurso = 0;
                $lineaPeriodoCurso = 0;
            }

            switch ($totalizado) {
                case 'FECHA':
                    $nombreCurso = $dato['nombreCurso'];
                    $anioPago = $dato['anioPago'];
                    $mesPago = $dato['mesPago'];
                    $cantidadPagos = $dato['cantidadPagos'];
                    $importePagado = $dato['importePagado'];
                    $totalPagosCurso += $cantidadPagos;
                    $totalIngresosCurso += $importePagado;
                    $lineaPeriodoCurso += 1;

                    $pdf->SetFont('dejavusans', 'B', 8);
                    $pdf->MultiCell(0, 0, $nombreCurso, 0, 'L', false, 0, '5', '');
                    $pdf->MultiCell(20, 0, $cantidadPagos, 0, 'C', false, 0, '135', '');
                    $pdf->MultiCell(20, 0, number_format($importePagado, 0, ',', '.'), 0, 'R', false, 0, '155', '');
                    $pdf->MultiCell(0, 0, $anioPago.'/'.$mesPago, 0, 'L', false, 1, '180', '');
                    $pdf->Ln(2);
                    if ($tipoListado == "DETALLE") { 
                        //obtengo el detalle de los pagos del periodo
                        $pdf->SetFont('dejavusans', '', 8);
                        $resDetalle = $cursos_pdo->obtenerDetalleCobranzaPorPerido($idCurso, $fechaDesde, $fechaHasta, $anioPago, $mesPago);
                        if ($resDetalle['estado']){
                            $totalPeriodo = 0;
                            foreach ($resDetalle['datos'] as $detalle) {
                                $apellidoNombre = $detalle['apellidoNombre'];
                                $fechaPago = $detalle['fechaPago'];
                                $importePagado = $detalle['importe'];
                                $recibo = $detalle['recibo'];
                                $detalleCuota = $detalle['detalleCuota'];

                                $totalPeriodo += $importePagado;

                                $pdf->MultiCell(0, 0, $apellidoNombre, 0, 'L', false, 0, '5', '');
                                $pdf->MultiCell(0, 0, $fechaPago, 0, 'L', false, 0, '80', '');
                                $pdf->MultiCell(20, 0, number_format($importePagado, 0, ',', '.'), 0, 'R', false, 0, '100', '');
                                $pdf->MultiCell(0, 0, $recibo, 0, 'L', false, 0, '125', '');
                                $pdf->MultiCell(0, 0, $detalleCuota, 0, 'L', false, 1, '140', '');
                                $pdf->Ln(2);
                            }
                        } else {
                            $resDetalle = $resEstadoTeso['mensaje'];
                        }
                        
                    }
                    break;
                
                case 'CUOTA':
                    $nombreCurso = $dato['nombreCurso'];
                    if ($imprimirTituloCurso) {
                        $pdf->SetFont('dejavusans', 'B', 8);
                        $pdf->MultiCell(0, 0, $nombreCurso, 0, 'L', false, 1, '5', '');
                        $pdf->Ln(2);
                        $imprimirTituloCurso = FALSE;
                    }

                    $cuota = $dato['cuota'];
                    $fechaVencimiento = $dato['fechaVencimiento'];
                    $detalleCuota = $dato['detalleCuota'];
                    if ($cuota <> $cuotaAnterior) {
                        //cierro la cuota e imprimo el encabezado de la nueva cuota
                        $p1y = $pdf->getY();
                        $pdf->Line(5, $p1y+5, 200, $p1y+5, array('width' => 0));
                        $pdf->SetFont('dejavusans', 'B', 8);
                        $pdf->MultiCell(0, 0, 'Cuota: '.$cuota.' - '.$detalleCuota.' - Fecha de Vencimiento: '.cambiarFechaFormatoParaMostrar($fechaVencimiento), 0, 'L', false, 1, '5', '');
                        $pdf->Ln(2);

                        //encabezado
                        $pdf->SetFont('dejavusans', 'B', 8);
                        $pdf->MultiCell(0, 0, 'Apellido y Nombre', 0, 'L', false, 0, '5', '');
                        $pdf->MultiCell(0, 0, 'Fecha de pago', 0, 'L', false, 0, '60', '');
                        $pdf->MultiCell(40, 0, 'Importe pagado', 0, 'R', false, 0, '80', '');
                        $pdf->MultiCell(0, 0, 'Recibo de pago', 0, 'L', false, 1, '125', '');
                        //seteo cuota nueva    
                        $cuotaAnterior = $cuota;
                    }
                    $apellidoNombre = $dato['apellidoNombre'];
                    $fechaPago = $dato['fechaPago'];
                    $importePagado = $dato['importePagado'];
                    $recibo = $dato['recibo'];

                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->MultiCell(0, 0, $apellidoNombre, 0, 'L', false, 0, '5', '');
                    if (isset($fechaPago) && $fechaPago <> "" && $fechaPago <> "0000-00-00") {
                        $totalIngresosCurso += $importePagado;
                        $pdf->MultiCell(0, 0, cambiarFechaFormatoParaMostrar($fechaPago), 0, 'L', false, 0, '60', '');
                        $pdf->MultiCell(40, 0, number_format($importePagado, 0, ',', '.'), 0, 'R', false, 0, '80', '');
                        $pdf->MultiCell(0, 0, $recibo, 0, 'L', false, 1, '125', '');
                    } else {
                        $pdf->MultiCell(0, 0, 'Pendiente de pago', 0, 'L', false, 1, '125', '');
                    }
                    $pdf->Ln(2);
                    break;

                default:
                    // code...
                    break;
            }  
        }
        $p1y = $pdf->getY();
        //if ($lineaPeriodoCurso > 1) {
            //imprimimos el total del curso
            $pdf->Line(5, $p1y, 200, $p1y, array('width' => 0));
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 0, 'TOTAL '.$nombreCurso, 0, 'L', false, 0, '5', '');
            //$pdf->MultiCell(20, 0, $totalPagosCurso, 0, 'C', false, 0, '135', '');
            $pdf->MultiCell(0, 0, number_format($totalIngresosCurso, 0, ',', '.'), 0, 'R', false, 1, '155', '');
            $pdf->Line(5, $p1y+5, 200, $p1y+5, array('width' => 0));
            $pdf->Ln(2);
        //} else {
            //imprimimos linea de cierre
        //    $pdf->Line(5, $p1y, 200, $p1y, array('width' => 0.5));
        //}
        $pdf->lastPage();
        ob_clean();
        $pdf->Output('Curso_id_'.$idCurso.'.pdf', 'I');                   
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR pagos->".$resCobranza['mensaje'];
    }
} else {
    echo $mensaje;
}

// imprimir cuadrilla
function imprimirGrilla($pdf, $p1y, $alturaLinea) {
    $i = 0;
    $p1x = 60;
    $pdf->Line(0, $p1y, 0, $p1y+$alturaLinea, array('width' => 0));
    while ($i <= 15) {
        $pdf->Line($p1x, $p1y, $p1x, $p1y+$alturaLinea, array('width' => 0));
        $i += 1;
        $p1x += 10;
    }
    $pdf->Line(0, $p1y+$alturaLinea, 220, $p1y+$alturaLinea, array('width' => 0));
    // fin imprimir cuadrilla
}
?>
