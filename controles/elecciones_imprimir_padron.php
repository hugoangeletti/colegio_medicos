<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');
require_once ('../dataAccess/eleccionesLogic.php');
error_reporting(E_ALL);
ini_set("display_errors", 1);
set_time_limit(0);
ini_set("memory_limit",-1);

require_once('../tcpdf/config/lang/spa.php');
require_once('../tcpdf/tcpdf.php');
class MYPDF extends TCPDF {
        //Page header
        public function Header() 
        {
            $this->SetFont('dejavusans', 'B', 10);
            // Title
            $this->Ln(4);
            $this->MultiCell(0, 0, 'Colegio de Médicos - Distrito I', 0, 'L', false, 0, '10', '');
            $this->MultiCell(0, 0, cambiarFechaFormatoParaMostrar(date('Y-m-d')), 0, 'L', false, 1, '160', '');
            $this->MultiCell(0, 0, 'Padrón de Colegiados', 0, 'C', false, 0, '10', '');
            $this->Ln(5);
            $this->SetFont('dejavusans', 'B', 12);
            $this->Ln(2);
            $this->MultiCell(0, 5, 'Localidad '.TITULO_LOCALIDAD, 0, 'C', false, 1, '10', '');
            $this->Ln(2);
            $this->SetFont('dejavusans', 'B', 10);
            $this->MultiCell(20, 0, 'Matrícula', 0, 'R', false, 0, '8', '');
            $this->MultiCell(0, 0, 'Apellido y Nombre', 0, 'L', false, 0, '32', '');
            $this->MultiCell(0, 0, 'Localidad', 0, 'L', false, 0, '100', '');
            $this->MultiCell(0, 0, 'Tesoreria', 0, 'L', false, 0, '160', '');
            $this->MultiCell(0, 0, 'Cargo', 0, 'L', false, 1, '183', '');

            $y = $this->GetY() + 2;
            $this->Line(10, $y, 200, $y, array('width' => 1));
        }

        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('dejavusans', 'I', 8);

            // Page number
            $this->Cell(0, 5, 'Pag. '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
}

$continua = TRUE;
$mensaje = "";
$eleccionesLocalidadesLogic = new eleccionesLocalidades();
$eleccionesLogic = new elecciones();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idEleccionesLocalidad = $_GET['id'];
    $resEleccionesLocalidad = $eleccionesLocalidadesLogic->obtenerEleccionesLocalidadPorId($idEleccionesLocalidad);
    if ($resEleccionesLocalidad['estado']) {
        $eleccionesLocalidades = $resEleccionesLocalidad['datos'];
        $idElecciones = $eleccionesLocalidades['idElecciones'];
        $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
        if ($resElecciones['estado']) {
            $elecciones = $resElecciones['datos'];
            $resColegiados = $eleccionesLocalidadesLogic->obtenerColegiadosParaImprimirPadron($idEleccionesLocalidad);
            if ($resColegiados['estado']) {
                $colegiados = $resColegiados['datos'];
            } else {
                $continua = FALSE;
                $mensaje .= "ERROR->".$resColegiados['mensaje'];
            }
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resElecciones['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resEleccionesLocalidad['mensaje'];
    }

}

if ($continua) {
    //fin obtenemos todos los datos del asistente
    $pathOrigen = '../'; //$pathOrigen = '../../';
    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $resPadron = $eleccionesLocalidadesLogic->imprimirPadron($idEleccionesLocalidad, $eleccionesLocalidades, $colegiados, $pdf, $pathOrigen);
    if ($resPadron['estado']) {
        $padronPDF = $resPadron['padronPDF'];
        $pathArchivo = $resPadron['pathArchivo'];
        $nombreArchivo = $resPadron['nombreArchivo'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resPadron['mensaje'];
        ?>
            <div class="col-md-12">
                <h2 class="alert alert-danger"><?php echo $mensaje; ?></h2>
            </div>
            <a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-primary">Volver</a>
        <?php
    }
    require_once ('../html/head.php');
    require_once '../html/header.php';
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-11">
                    <h4>Imprimir padron <?php echo $elecciones['detalle'].' - '.$eleccionesLocalidades['localidadDetalle']; ?></h4>
                </div>
                <div class="col-md-1 text-right">
                    <a href="elecciones_localidades_lista.php?id=<?php echo $idElecciones; ?>" class="btn btn-info">Volver</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            if (!isset($padronPDF)) {
                if (file_exists($pathArchivo.'/'.$nombreArchivo)) {
                    $pdf_content = file_get_contents($pathArchivo.'/'.$nombreArchivo);        
                    $padronPDF = base64_encode($pdf_content);   
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = 'PADRON NO EXISTE.';
                }
            }
            ?>
            <div class="row">
               <embed src='data:application/pdf;base64,<?php echo $padronPDF; ?>' height="800px" width='100%' type='application/pdf'> 
            </div> 
        </div>
    </div>
<?php 
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR <?php echo $mensaje; ?></h2>
    </div>
    <a href="elecciones_lista.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");
