<?php
require_once('../dataAccess/config.php');
permisoLogueado();
require_once('../dataAccess/conection_pdo.php');
require_once('../dataAccess/funcionesPhp.php');
require_once('../dataAccess/cursos_pdo.php');
require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    public $periodoInfo;
    public $cursoNombre;
    public $fechaCobranza;

    public function Header() {
        $image_file = '../public/images/logo_colmed1_lg.png';
        $this->Image($image_file, 10, 5, 190, 20, 'PNG');
        
        $this->SetY(25);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'DETALLE DE LIQUIDACIÓN DE CURSOS', 0, 1, 'C');
        
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Curso: ' . $this->cursoNombre, 0, 1, 'L');
        $this->Cell(0, 5, 'Período: ' . $this->periodoInfo, 0, 1, 'L');
        $this->Cell(0, 5, 'Fecha de cobranza hasta: ' . $this->fechaCobranza, 0, 1, 'L');
        $this->SetY(45); // Posición fija para la fecha
        $this->Cell(0, 5, 'Fecha de Emisión: ' . date('d/m/Y H:i'), 0, 1, 'R');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
    }
}

function imprimirEncabezadoTabla($pdf) {
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(80, 7, 'Asistente', 1, 0, 'C', 1);
    $pdf->Cell(30, 7, 'Cuota', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Fecha Pago', 1, 0, 'C', 1);
    $pdf->Cell(40, 7, 'Importe', 1, 1, 'C', 1);
    $pdf->SetFont('helvetica', '', 9);
}

$cursos_pdo = new cursos_pdo();
$id_liquidacion = $_GET['id'];

// Obtener datos de la cabecera y el detalle
$resLiq = $cursos_pdo->obtenerLiquidacionPorId($id_liquidacion);
$resDetalle = $cursos_pdo->obtenerDetalleLiquidacion($id_liquidacion);

if ($resLiq['estado'] && $resDetalle['estado']) {
    $liq = $resLiq['datos'];
    
    $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->cursoNombre = $liq['NombreCurso'];
    $pdf->periodoInfo = substr($liq['PeriodoLiquidacion'], 0, 4) . '-' . substr($liq['PeriodoLiquidacion'], 4, 2);
    $pdf->fechaCobranza = cambiarFechaFormatoParaMostrar($liq['FechaCobranza']);

    $pdf->SetMargins(15, 60, 15); 
    $pdf->SetAutoPageBreak(TRUE, 20); // Muy importante para que no se pise con el footer
    $pdf->AddPage();
    imprimirEncabezadoTabla($pdf);

    $pdf->SetFont('helvetica', '', 9);

    $totalGeneral = 0;
    $cuotaAnterior = null;
    $subtotalCuota = 0;

    foreach ($resDetalle['datos'] as $item) {
        // Si queda poco espacio (menos de 20mm), saltar página y repetir encabezado
        if ($pdf->GetY() > 250) { 
            $pdf->AddPage();
            imprimirEncabezadoTabla($pdf);
        }
        // Detectar cambio de cuota para imprimir subtítulo
        if ($item['cuota'] !== $cuotaAnterior) {
            
            // Si no es la primera cuota, podrías imprimir un subtotal aquí (opcional)
            if ($cuotaAnterior !== null) {
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(150, 6, 'Subtotal ' . $cuotaAnterior, 1, 0, 'R');
                $pdf->Cell(40, 6, '$ ' . number_format($subtotalCuota, 2, ',', '.'), 1, 1, 'R');
                $pdf->Ln(2);
                $subtotalCuota = 0;
            }

            // Imprimir Subtítulo de nueva Cuota
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(190, 7, 'DETALLE CUOTA: ' . $item['cuota'], 1, 1, 'L', 1);
            $pdf->SetFont('helvetica', '', 9);
            
            $cuotaAnterior = $item['cuota'];
        }

        // Imprimir fila del asistente
        $pdf->Cell(80, 6, $item['apellidoNombre'], 1, 0, 'L');
        $pdf->Cell(30, 6, $item['cuota'], 1, 0, 'C');
        $pdf->Cell(40, 6, cambiarFechaFormatoParaMostrar($item['fechaPago']), 1, 0, 'C');
        $pdf->Cell(40, 6, '$ ' . number_format($item['importe'], 2, ',', '.'), 1, 1, 'R');
        
        $totalGeneral += $item['importe'];
        $subtotalCuota += $item['importe'];
    }

    // Imprimir el último subtotal de la última cuota
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(150, 6, 'Subtotal ' . $cuotaAnterior, 1, 0, 'R');
    $pdf->Cell(40, 6, '$ ' . number_format($subtotalCuota, 2, ',', '.'), 1, 1, 'R');

    // Total final
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(200, 220, 255);
    $pdf->Cell(150, 10, 'TOTAL GENERAL LIQUIDADO', 1, 0, 'R', 1);
    $pdf->Cell(40, 10, '$ ' . number_format($totalGeneral, 2, ',', '.'), 1, 1, 'R', 1);


    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(150, 8, 'TOTAL LIQUIDADO', 1, 0, 'R');
    $pdf->Cell(40, 8, '$ ' . number_format($totalGeneral, 2, ',', '.'), 1, 1, 'R');

    $pdf->Output('Liquidacion_'.$id_liquidacion.'.pdf', 'I');
} else {
    echo "Error al generar el PDF: " . $resLiq['mensaje'];
}
