<?php
require_once ('../dataAccess/config.php');
permisoLogueado();

require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF 
{
    // Propiedades personalizadas para recibir los datos del script
    public $tituloCurso = '';
    public $tipoListado = '';
    public $x_cuota = '';

    // Page header
    public function Header() 
    {
        // Logo
        $image_file = '../public/images/logo_colmed1_lg.png';
        $this->Image($image_file, 10, 5, 190, 20, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Título del reporte (Centrado en el logo)
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, '', 0, false, 'C', 0, 'Listado de Asistentes al Curso', 0, false, 'M', 'M');
        $this->Ln(25); // Espacio después del logo y título principal

        // Bloque dinámico movido al Header
        $this->SetFont('dejavusans', '', 8);
        $this->MultiCell(0, 5, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 1, '50', '');
        
        $this->SetFont('dejavusans', 'B', 12);        
        $this->MultiCell(0, 5, $this->tituloCurso, 0, 'L', false, 1, '', '');
        $this->Ln(5);
        
        $this->SetFont('dejavusans', 'B', 10);        
        $this->MultiCell(0, 5, $this->tipoListado, 0, 'L', false, 1, '', '');

        // Dibujo de la cabecera de la tabla
        $this->SetFont('dejavusans', 'B', 8);        
        $this->SetXY(0, 53);
        $this->Line(0, 53, 220, 53, array('width' => 0));
        
        $this->MultiCell(0, 5, 'Apellido y Nombre', 0, 'L', false, 0, '10', '');
        $this->MultiCell(0, 5, 'Matrícula', 0, 'L', false, 1, '80', '');
        $this->MultiCell(0, 5, 'Cuota', 0, 'L', false, 0, $this->x_cuota, '');
        $this->MultiCell(0, 5, 'Importe', 0, 'L', false, 0, $this->x_cuota+15, '');
        $this->MultiCell(0, 5, 'Vencimiento', 0, 'L', false, 0, $this->x_cuota+35, '');
        $this->MultiCell(0, 5, 'Fecha Pago', 0, 'L', false, 0, $this->x_cuota+60, '');
        $this->MultiCell(0, 5, 'Recibo', 0, 'L', false, 0, $this->x_cuota+80, '');
        $this->MultiCell(0, 5, 'Detalle', 0, 'L', false, 1, $this->x_cuota+100, '');
        $this->Line(0, 65, 220, 65, array('width' => 0));
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$continua = TRUE;
$mensaje = "";
$cursos_pdo = new cursos_pdo();

if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
    $idCurso = $_GET['idCurso'];
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->" . $resCurso['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idCurso - ';
}

if (isset($_GET['deuda'])) {
    $conDeuda = TRUE;
    $tipoListado = "Listado de asistentes deudores.";
} else {
    $conDeuda = FALSE;
    $tipoListado = "Listado completo de asistentes.";
}

if ($continua) {
    $asiste = 'S';
    $resAsistentes = $cursos_pdo->obtenerAsistentesPorIdCurso($idCurso, $asiste);
    
    if ($resAsistentes['estado'] && count($resAsistentes['datos']) > 0) {
        $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        
        //columna de la linea de las cuotas
        $x_cuota = 50;

        // Seteamos los datos dinámicos antes de renderizar las páginas
        $pdf->tituloCurso = $titulo;
        $pdf->tipoListado = $tipoListado;
        $pdf->x_cuota = $x_cuota;
        
        // Definimos márgenes para evitar que el contenido pise el encabezado (Header mide aprox 65mm)
        $pdf->SetMargins(10, 68, 10); 
        $pdf->SetHeaderMargin(5);
        $pdf->SetAutoPageBreak(TRUE, 20);

        $pdf->SetFont('dejavusans', '', 8);
        $pdf->AddPage(); // Aquí se ejecuta automáticamente el Header() con los datos cargados

        $cantidadAsistentes = 0;
        foreach ($resAsistentes['datos'] as $dato) {
            $idCursosAsistente = $dato['idCursosAsistente'];
            $matricula = $dato['matricula'];
            $apellidoNombre = $dato['apellidoNombre'];
            
            $resCuotas = $cursos_pdo->obtenerCuotasPorAsistente($idCursosAsistente);
            if ($resCuotas['estado']) {
                $asistenteImpreso = FALSE;
                
                foreach ($resCuotas['datos'] as $cuotas) {
                    $fechaVencimiento = $cuotas['fechaVencimiento'];
                    $fechaPago = $cuotas['fechaPago'];
                    
                    if ($conDeuda) {
                        if ($fechaVencimiento < date('Y-m-d') && (!isset($fechaPago) || $fechaPago == "0000-00-00" || $fechaPago == "")) {
                            $imprime = TRUE;
                        } else {
                            $imprime = FALSE;
                        }
                    } else {
                        $imprime = TRUE;
                    }
                    
                    if ($imprime) {
                        if (!$asistenteImpreso) {
                            $pdf->Ln(2);
                            $pdf->MultiCell(0, 5, $apellidoNombre, 0, 'L', false, 0, '10', '');
                            $pdf->MultiCell(0, 5, $matricula, 0, 'L', false, 1, '80', '');
                            $asistenteImpreso = TRUE;
                            $cantidadAsistentes += 1;
                        }
                        $cuota = $cuotas['cuota'];
                        $importe = $cuotas['importe'];
                        $fechaVencimientoMostrar = cambiarFechaFormatoParaMostrar($fechaVencimiento);
                        
                        $fechaPagoMostrar = "";
                        if (isset($fechaPago) && $fechaPago <> "0000-00-00" && $fechaPago <> "") {
                            $fechaPagoMostrar = cambiarFechaFormatoParaMostrar($fechaPago);
                        }
                        $recibo = $cuotas['recibo'];
                        $detalleCuota = $cuotas['detalleCuota'];
                        
                        $pdf->MultiCell(0, 5, $cuota, 0, 'L', false, 0, $x_cuota, '');
                        $pdf->MultiCell(0, 5, $importe, 0, 'L', false, 0, $x_cuota+15, '');
                        $pdf->MultiCell(0, 5, $fechaVencimientoMostrar, 0, 'L', false, 0, $x_cuota+35, '');
                        $pdf->MultiCell(0, 5, $fechaPagoMostrar, 0, 'L', false, 0, $x_cuota+60, '');
                        $pdf->MultiCell(0, 5, $recibo, 0, 'L', false, 0, $x_cuota+80, '');
                        $pdf->MultiCell(0, 5, $detalleCuota, 0, 'L', false, 1, $x_cuota+100, '');
                    }
                }
                
                if (!$conDeuda && !$asistenteImpreso) {
                    $pdf->Ln(2);
                    $pdf->MultiCell(0, 5, $apellidoNombre, 0, 'L', false, 0, '10', '');
                    $pdf->MultiCell(0, 5, $matricula, 0, 'L', false, 1, '80', '');
                    $asistenteImpreso = TRUE;
                    $cantidadAsistentes += 1;
                }

                if ($asistenteImpreso) {
                    $p1y = $pdf->getY();
                    $pdf->Line(10, $p1y, 200, $p1y, array('width' => 0));
                }
            } else {
                $continua = FALSE;
                $mensaje .= "ERROR Cuotas->" . $resCuotas['mensaje']; 
            }
        }
        
        if ($cantidadAsistentes == 0) {
            $pdf->SetFont('dejavusans', 'B', 14);
            $pdf->MultiCell(0, 5, 'No hay asistentes para imprimir.', 0, 'L', false, 0, '10', '');
        } else {
            $pdf->Ln(4);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->MultiCell(0, 5, 'Cantidad de asistentes: ' . $cantidadAsistentes, 0, 'L', false, 0, '10', '');
        }
        
        $pdf->lastPage();
        ob_clean();
        $pdf->Output('Curso_id_' . $idCurso . '.pdf', 'I');                   
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR Asistentes->" . $resAsistentes['mensaje'];
    }
}

if (!$continua) {
    echo "<div class='alert alert-danger'>" . htmlspecialchars($mensaje) . "</div>";
}
?>
