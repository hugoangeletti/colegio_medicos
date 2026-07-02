<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
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
        $this->Cell(0, 15, '', 0, false, 'C', 0, 'Situación con Tesoreria de Asistentes al Curso', 0, false, 'M', 'M');

        //imprimr encabezado de la grilla
        $p1y = 47;
        $alturaLinea = 7;
        $this->SetXY(0, $p1y);
        $this->SetFont('dejavusans', 'B', 10);        
        $this->Ln(2);
        $this->MultiCell(0, 0, 'Apellido y Nombre', 0, 'L', false, 0, '5', '');
        $this->MultiCell(0, 0, 'Matrícula', 0, 'L', false, 0, '80', '');
        $this->MultiCell(0, 0, 'Situación con Tesoreria', 0, 'L', false, 0, '100', '');
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
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCurso = $_GET['id'];
    $cursos_pdo = new cursos_pdo();
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
    $continua = FALSE;
    $mensaje .= 'Falta idCurso - ';
}

$tipoListado = "Situación con Tesoreria de Asistentes al Curso.";

if ($continua) {
    //$mailDestino = 'sistemas@colmed1.org.ar'; //para las pruebas, sacar en produccion
    $asiste = 'S';
    $resAsistentes = $cursos_pdo->obtenerAsistentesPorIdCurso($idCurso, $asiste);
    if ($resAsistentes['estado'] && sizeof($resAsistentes['datos']) > 0) {
        $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(true);
        $pdf->SetPrintFooter(true);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //$pdf->SetMargins(0, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        //$pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->AddPage();

        $pdf->MultiCell(0, 5, 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y'), 0, 'R', false, 1, '50', '');
        $pdf->SetFont('dejavusans', 'B', 12);        
        $pdf->MultiCell(0, 5, $titulo, 0, 'L', false, 1, '', '');
        $pdf->Ln(2);
        $pdf->SetFont('dejavusans', 'B', 10);        
        $pdf->MultiCell(0, 5, $tipoListado, 0, 'L', false, 1, '', '');

        $pdf->SetFont('dejavusans', '', 8);

        $pdf->Ln(10);
        $cantidadAsistentes = 0;
        $alturaLinea = 7;
        $p1y = $pdf->getY();
        foreach ($resAsistentes['datos'] as $dato){
            $idCursosAsistente = $dato['idCursosAsistente'];
            $matricula = $dato['matricula'];
            $apellidoNombre = $dato['apellidoNombre'];
            $idColegiado = $dato['idColegiado'];

            if (isset($idColegiado) && $idColegiado > 0) { 
                //obtengo el estado actual con tesoreria
                $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, PERIODO_ACTUAL);
                if ($resEstadoTeso['estado']){
                    $codigo = $resEstadoTeso['codigoDeudor'];
                    $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
                    if ($resEstadoTesoreria['estado']){
                        $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                    } else {
                        $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                    }
                } else {
                    $estadoTesoreria = $resEstadoTeso['mensaje'];
                }
            } else {
                $estadoTesoreria = "NO ES COLEGIADO/A DEL DISTRITO.";
            }
            $pdf->MultiCell(0, 0, $apellidoNombre, 0, 'L', false, 0, '5', '');
            $pdf->MultiCell(0, 0, $matricula, 0, 'L', false, 0, '80', '');
            $pdf->MultiCell(0, 0, $estadoTesoreria, 0, 'L', false, 1, '100', '');
            $pdf->Ln(2);
            $cantidadAsistentes += 1;
        }
        if ($cantidadAsistentes == 0) {
            $pdf->SetXY(0, 70);
            $pdf->SetFont('dejavusans', 'B', 14);
            $pdf->MultiCell(0, 5, 'No hay asistentes para imprimir.', 0, 'L', false, 0, '10', '');
        } else {
            //$pdf->Line(0, $p1y+7, 220, $p1y+7, array('width' => 0));
            $pdf->Ln(5);
            $pdf->SetFont('dejavusans', 'B', 10);
            $pdf->MultiCell(0, 5, 'Cantidad de asistentes: '.$cantidadAsistentes, 0, 'L', false, 0, '10', '');
        }
        $pdf->lastPage();
        ob_clean();
        $pdf->Output('Curso_id_'.$idCurso.'.pdf', 'I');                   
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR Asistentes->".$resAsistentes['mensaje'];
    }
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
