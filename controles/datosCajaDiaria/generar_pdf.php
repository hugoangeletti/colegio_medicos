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

class MYPDF_anulado extends TCPDF 
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
            */

            //MARCA DE AGUA 
            $bMargin = $this->getBreakMargin();
            $auto_page_break = $this->AutoPageBreak;
            $this->SetAutoPageBreak(false, 0);

            $img_file2 = '../public/images/reciboAnulado.png';
            $this->Image($img_file2, 15, 25, 180, 180, '', '', 'C', false, 300, '', false, false, 0);
            $this->SetAutoPageBreak($auto_page_break, $bMargin);
            $this->setPageMark();
            //FIN MARCA DE AGUA 
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
$resultado = NULL;

if (isset($idCajaDiariaMovimiento) && $idCajaDiariaMovimiento > 1) {
    //guarda pdf
    $cajaDiariaLogic = new cajaDiariaLogic();
    $resRecibo = $cajaDiariaLogic->obtenerCajaDiariaMovimientoPorId($idCajaDiariaMovimiento);
    if ($resRecibo['estado']) {
        $recibo = $resRecibo['datos']; //$idCajaDiaria, $fechaPago, $horaPago, $monto, $tipo, $numero, $idAsistente, $idColegiado, $usuario, $estado, $apellidoNombre, $matricula
        $idCajaDiaria = $recibo['idCajaDiaria'];
        $fechaPago = $recibo['fechaPago'];
        $totalRecibo = $recibo['monto'];
        if ($totalRecibo < 0) {
            $totalRecibo = $totalRecibo * (-1);
        }
        $tipoRecibo = $recibo['tipoRecibo'];
        $numeroRecibo = $recibo['numeroRecibo'];
        $idAsistente = $recibo['idAsistente'];
        $idColegiado = $recibo['idColegiado'];
        $usuario = $recibo['usuario'];
        $apellidoNombre = $recibo['apellidoNombre'];
        $matricula = $recibo['matricula'];
        $estadoRecibo = $recibo['estadoRecibo'];
        if ($estadoRecibo == 'A') {
            $tipoRecibo = $tipoRecibo.'A';
        }
        $domicilioCompleto = $recibo['domicilio'];
        $cuit = $recibo['cuit'];
        /*
        if (isset($idColegiado) || isset($idAsistente)) {
            //es un colegiado o asistente y obtengo el domicilio de colegiadodomicilio
            include_once $pathOrigen.'../dataAccess/colegiadoDomicilioLogic.php';
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
                    $domicilioCompleto .=  ' - ('.$domicilio['codigoPostal'].') '.$domicilio['nombreLocalidad'];
                }
            }
        }
        */

        //obtengo detalle del recibo
        $resDetalle = $cajaDiariaLogic->obtenerCajaDiariaMovimientoDetallePorId($idCajaDiariaMovimiento);
        if ($resDetalle['estado'] && $resDetalle['datos']) {
            $cajaDiariaMovimientoDetalle = $resDetalle['datos'];
        } else {
            $continua = FALSE;
            $resultado['mensaje'] = $resColegiado['mensaje'];
        }        
    } else {
        $continua = FALSE;
        $resultado['mensaje'] = $resExpediente['mensaje'];
    }
    if ($continua){
        /* armamaos el path donde se va a guardar el pdf */
        $subCarpeta = substr($fechaPago, 0, 4).'/'.substr($fechaPago, 5, 2);

        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF.'/archivos/recibos/'.$subCarpeta.'/';
        $nombreArchivo = $camino.$tipoRecibo.'_'.$numeroRecibo.'.pdf';
        //echo $nombreArchivo; 
        //exit;
        if (!file_exists($camino)) {
            mkdir($camino, 0777, true);
        }

        //si el pdf ya existe, no lo vuelvo a generar
        if (file_exists($nombreArchivo)) {
            $pdf_content = file_get_contents($nombreArchivo);        
            $reciboPDF = base64_encode($pdf_content);
        } else {
            //armo el html con el certificado
            if ($estadoRecibo == 'A') {
                $pdf = new MYPDF_anulado('P', PDF_UNIT, 'A4', true, 'UTF-8', false);    
            } else {
                $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            }
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

            //imprimo la planilla
            $image_file = $pathOrigen.'../public/images/logo_colmed1_hr.png';
            $image_file = $pathOrigen.'../public/images/EscudoRecibo.JPG';

            //$i = 1;
            //while ($i <= 2) {
                //$ori_dup = "ORIGINAL";
                //if ($i == 2) {
                //    $ori_dup = "DUPLICADO";
                //}
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->AddPage();

                $pdf->Line(100, 5, 100, 52, array('width' => 0));
                $pdf->Image($image_file, 5, 5, 25, 25, 'jpeg', '', 'T', false, 300, '', false, false, 0, false, false, false);
                $pdf->SetFont('dejavusans', 'B', 16);
                $pdf->MultiCell(0, 5, 'Colegio de Médicos', 0, 'L', false, 0, '30', '');
                if ($tipoRecibo == 'NC') {
                    $pdf->MultiCell(0, 7, 'NOTA DE CREDITO', 0, 'L', false, 1, '105', '');
                } else {
                    $pdf->MultiCell(0, 7, 'RECIBO', 0, 'L', false, 1, '105', '');
                }
                $pdf->MultiCell(0, 5, 'de la Provincia', 0, 'L', false, 0, '30', '');
                $pdf->MultiCell(0, 7, 'Nº '.rellenarCeros($numeroRecibo, 8), 0, 'L', false, 1, '105', '');
                //$pdf->MultiCell(0, 7, $ori_dup, 0, 'L', false, 1, '140', '');
                $pdf->MultiCell(0, 5, 'de Buenos Aires', 0, 'L', false, 0, '30', '');
                $pdf->SetFont('dejavusans', 'B', 12);
                $pdf->MultiCell(0, 7, 'Fecha: '. cambiarFechaFormatoParaMostrar($fechaPago), 0, 'L', false, 1, '105', '');
                $pdf->MultiCell(0, 5, 'Distrito I', 0, 'L', false, 0, '30', '');
                $pdf->SetFont('dejavusans', '', 8);
                $pdf->MultiCell(0, 5, 'CUIT Nº: 30-54078002-8', 0, 'L', false, 1, '105', '');
                $pdf->MultiCell(0, 5, 'Calle 51 Nº723 - Tel. 4256311 / 4232731', 0, 'L', false, 0, '10', '');
                $pdf->MultiCell(0, 5, 'Ingresos Brutos: EXENTO', 0, 'L', false, 1, '105', '');
                $pdf->MultiCell(0, 5, '(1900) La Plata - Pcia. Bs.As ', 0, 'L', false, 0, '10', '');
                $pdf->MultiCell(0, 5, 'Caja Prev. Nº: 30-54078002-8', 0, 'L', false, 1, '105', '');
                $pdf->MultiCell(0, 5, 'tesoreria@colmed1.org.ar ', 0, 'L', false, 0, '10', '');
                $pdf->MultiCell(0, 5, 'IVA EXENTO', 0, 'L', false, 1, '105', '');
                $pdf->MultiCell(0, 5, 'www.colmed1.org.ar ', 0, 'L', false, 0, '10', '');
                $pdf->MultiCell(0, 5, 'Exceptuado Cumplimiento R.G. 1415 Anexo I Ap."A" inc.k', 0, 'L', false, 1, '105', '');
                $pdf->Ln(2);
                //ARMAMOS EL HTML
                $pdf->Line(0, 52, 220, 52, array('width' => 0));
                $pdf->SetFont('dejavusans', '', 10);
                $pdf->MultiCell(0, 5, 'Apellido y Nombre: '.$apellidoNombre, 0, 'L', false, 0, '10', '');
                if (isset($matricula)) {
                    $pdf->MultiCell(0, 5, 'Matrícula: '.$matricula, 0, 'L', false, 1, '160', '');        
                } else {
                    if (isset($cuit)) {
                        $pdf->MultiCell(0, 5, 'CUIT: '.$cuit, 0, 'L', false, 1, '150', '');        
                    } else {
                        $pdf->MultiCell(0, 5, '', 0, 'L', false, 1, '10', '');        
                    }
                }
                $pdf->MultiCell(0, 5, 'Domicilio: '.$domicilioCompleto, 0, 'L', false, 1, '10', '');
                $pdf->Ln(2);
                $pdf->SetFont('dejavusans', 'B', 8);
                $pdf->Line(0, 63, 220, 63, array('width' => 0));
                $pdf->MultiCell(0, 5, 'Concepto', 0, 'L', false, 0, '10', '');
                $pdf->MultiCell(0, 5, 'Importe', 0, 'L', false, 1, '160', '');
                $pdf->Line(0, 70, 220, 70, array('width' => 0));
                $pdf->Ln(2);
                foreach ($cajaDiariaMovimientoDetalle as $dato){
                    $idCajaDiariaMovimientoDetalle = $dato['idCajaDiariaMovimientoDetalle'];
                    $indice = $dato['indice'];
                    $monto = $dato['monto'];
                    if ($monto < 0) {
                        $monto = $monto * (-1);
                    }
                    $codigoPago = $dato['codigoPago'];
                    $tipoPago = $dato['tipoPago'];
                    $detalle = "";
                    switch ($codigoPago) {
                        case '1':
                        case '3':
                            if (isset($dato['periodo'])) {
                                $periodo = $dato['periodo'];
                                $detalle .= ' - Período: '.$periodo;
                            }
                            if (isset($dato['cuota'])) {
                                $cuota = $dato['cuota'];
                                if ($cuota > 0) {
                                    $detalle .= ' /Cuota: '.$cuota;
                                } else {
                                    $detalle .= ' / PAGO TOTAL';
                                }
                            }
                            break;
                         
                        case '2':
                            if (isset($dato['cuota'])) {
                                $cuota = $dato['cuota'];
                            } else {
                                $cuota = 'sin discriminar';
                            }
                            $detalle .= ' - Número: '.$indice.' /Cuota: '.$cuota;
                            break;

                        case '10':
                            $cursos_pdo = new cursos_pdo();
                            $resCurso = $cursos_pdo->obtenerNombreCursoAsistente($idAsistente);
                            $detalle .= $dato['detalle'];    
                            
                            break;

                        default:
                            if (!isset($detalle) || $detalle == "" || $detalle == " ") {
                                //si es por especialista, busco el nombre de la especialidad
                                $arrayTipoPago = array('72', '38', '59', '37', '82', '52', '55', '61');
                                if (in_array($codigoPago, $arrayTipoPago)) {
                                    $resEspecialidad = $mesaEntradaEspecialistaLogic->obtenerEspecialidadPorIdMesaEntrada($indice);
                                    if ($resEspecialidad['estado']) {
                                        $detalle = '('.$resEspecialidad['datos']['nombreEspecialidad'].')';
                                        if (isset($resEspecialidad['datos']['incisoArticulo8']) && $resEspecialidad['datos']['incisoArticulo8'] <> "") {
                                            $detalle .= $resEspecialidad['datos']['incisoArticulo8'];
                                        }
                                    } else {
                                        $detalle = 'Error al obtener el detalle del recibo';
                                    }
                                }
                            }
                            break;
                    }
                    if (strlen($detalle) < 80) {                    
                        $pdf->SetFont('dejavusans', '', 8);
                    } else {
                        $pdf->SetFont('dejavusans', '', 7);
                    }
                    $pdf->MultiCell(0, 5, $codigoPago.'-'.$tipoPago.': '.$detalle, 0, 'L', false, 0, '10', '');
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->MultiCell(0, 5, $monto, 0, 'L', false, 1, '160', '');
                }

                //obtengo la forma de pago para imprimir
                $resFormaPago = $cajaDiariaLogic->obtenerCajaDiariaMovimientoFormaPagoPorId($idCajaDiariaMovimiento);
                if ($resFormaPago['estado']) {
                    $formaPago = $resFormaPago['datos'];
                    $montoFormaPago = $formaPago['monto'];
                    $detalleFormaPago = $formaPago['detalle'];
                    $formaDePago = $formaPago['formaDePago'];
                    $bancoNombre = $formaPago['bancoNombre'];
                    $intereses = $formaPago['intereses'];
                }
                $pdf->SetXY(110, 235);
                $pdf->SetFont('dejavusans', '', 8);
                $pdf->MultiCell(0, 5, 'Forma de pago: '.$formaDePago, 0, 'L', false, 0, '10', '');
                if (isset($detalleFormaPago) && $detalleFormaPago <> "") {
                    $pdf->MultiCell(0, 5, 'Comprobante: '.$detalleFormaPago, 0, 'L', false, 0, '65', '');
                }
                if (isset($bancoNombre) && $bancoNombre <> "") {
                    $pdf->MultiCell(0, 5, 'Entidad: '.$bancoNombre, 0, 'L', false, 0, '120', '');
                }

                //imprimo el total, en caso de haber cobrado con intereses se debe mostrar
                $posY = 243;
                $pdf->Line(0, $posY, 220, $posY, array('width' => 0));
                $pdf->SetXY(110, 245);
                if (isset($intereses) && $intereses > 0) {
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->MultiCell(0, 5, 'SubTotal Conceptos:', 0, 'L', false, 0, '120', '');
                    $pdf->SetFont('dejavusans', 'B', 10);
                    $pdf->MultiCell(30, 5, '$'.number_format(($totalRecibo - $intereses), 2, ',', '.'), 0, 'R', false, 1, '155', '');
                    $pdf->SetFont('dejavusans', '', 8);
                    $pdf->MultiCell(0, 5, 'Intereses T.Crédito:', 0, 'L', false, 0, '120', '');
                    $pdf->SetFont('dejavusans', 'B', 10);
                    $pdf->MultiCell(30, 5, '$'.number_format($intereses, 2, ',', '.'), 0, 'R', false, 1, '155', '');
                    $posY += 17;
                } else {
                    $posY += 7;
                }
                $pdf->SetFont('dejavusans', 'B', 10);
                $pdf->MultiCell(0, 5, 'TOTAL A PAGAR:', 0, 'L', false, 0, '120', '');
                $pdf->MultiCell(30, 5, '$'.number_format($totalRecibo, 2, ',', '.'), 0, 'R', false, 0, '155', '');
                $pdf->Line(0, $posY, 220, $posY, array('width' => 0));

                //$pdf->SetXY(110, 260);
                $pdf->Ln(10);
                $pdf->SetFont('dejavusans', '', 8);
                $pdf->MultiCell(50, 5, 'Realizó: '.$_SESSION['user'], 0, 'L', false, 0, '35', '');
                $pdf->MultiCell(80, 5, 'Caja: '.$idCajaDiaria, 0, 'L', false, 0, '80', '');
                $pdf->MultiCell(80, 5, 'Emitido el: '.date('d/m/Y H:i:s'), 0, 'L', false, 0, '140', '');
                $pdf->lastPage();

            //    $i++;
            //}
            ob_clean();
            $pdf->Output($nombreArchivo, 'F');       

            if (file_exists($nombreArchivo)) {
                //marca el enviamail para el envio automatico
                $resCaja = $cajaDiariaLogic->marcarEnviaMailCajadiariaMovimiento($idCajaDiariaMovimiento);
                //obtiene el recibo y lo guarda como base64 para mostrar
                $pdf_content = file_get_contents($nombreArchivo);        
                $reciboPDF = base64_encode($pdf_content);
            } else {
                echo 'no pudo generar recibo';
                $reciboPDF = NULL;
            }
        } 
    } else {
        $resultado['mensaje'] = "error en los datos ingresados";
        $resultado['estado'] = FALSE;
        $reciboPDF = NULL;
    }                
} else {
    $resultado['mensaje'] = $mensaje;
    $resultado['estado'] = FALSE;
    $reciboPDF = NULL;
}
