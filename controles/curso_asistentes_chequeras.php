<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');

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
                $this->Cell(0, 15, '', 0, false, 'C', 0, 'Nota', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer() {
        }
}

$continua = TRUE;
$mensaje = "";
$cursos_pdo = new cursos_pdo();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursosAsistente = $_GET['id'];
    $resAsistente = $cursos_pdo->obtenerAsistentePorId($idCursosAsistente);
    if ($resAsistente['estado']) {
        $asistente = $resAsistente['datos'];
        $apellidoNombre = $asistente['apellidoNombre'];
        $idColegiado = $asistente['idColegiado'];
        $matricula = $asistente['matricula'];
        $idCurso = $asistente['idCurso'];
        if (isset($idColegiado) && $idColegiado <> "") {
            $esColegiado = "S";
        } else {
            $esColegiado = "N";
        }
        $completo = FALSE;
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resAsistente['mensaje'];
    }
} else {
    if (isset($_GET['idCurso']) && $_GET['idCurso'] <> "") {
        $idCurso = $_GET['idCurso'];
        $completo = TRUE;
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idCurso - ';
    }
}
if ($continua) {
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
    }
}
if ($continua) {
    if ($completo) {
        $asiste = "S";
        $resAsistentes = $cursos_pdo->obtenerAsistentesPorIdCurso($idCurso, $asiste);
        if ($resAsistentes['estado'] && sizeof($resAsistentes['datos']) > 0) {
            $chequerasGeneradas = array();
            $pathOrigen = '../'; //$pathOrigen = '../../';
            $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
            foreach ($resAsistentes['datos'] as $asistente) {
                $idCursosAsistente = $asistente['idCursosAsistente'];
                $apellidoNombre = $asistente['apellidoNombre'];
                $idColegiado = $asistente['idColegiado'];
                $matricula = $asistente['matricula'];
                $asistente['idCurso'] = $idCurso;
                $asistente['tituloCurso'] = $titulo;
                $resChequera = $cursos_pdo->imprimirChequeraAsistenteCurso($idCursosAsistente, $cursos_pdo, $asistente, $pdf, $pathOrigen);
                if ($resChequera['estado']) {
                    $chequeraPDF = $resChequera['chequeraPDF'];
                    /*anterior
                    $resChequera = imprimirChequera($idCursosAsistente, $cursos_pdo, $asistente);
                    if ($resChequera['estado']) {
                    */
                    $row = array(
                                'idCursosAsistente' => $idCursosAsistente,
                                'apellidoNombre' => $apellidoNombre,
                                'pathArchivo' => $resChequera['pathArchivo'],
                                'nombreArchivo' => $resChequera['nombreArchivo']
                                );
                    array_push($chequerasGeneradas, $row);
                }
            }
            require_once ('../html/head.php');
            require_once '../html/header.php';
            ?>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-11">
                            <h4>Imprimir chequera <?php echo $titulo; ?></h4>
                        </div>
                        <div class="col-md-1 text-right">
                            <a href="curso_listado.php" class="btn btn-info">Volver</a>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <?php 
                    $cantidadChequeras = sizeof($chequerasGeneradas);
                    if ($cantidadChequeras > 0) {
                    ?>
                        <h5>Se generaron <?php echo $cantidadChequeras; ?> chequeras.</h5>
                    <?php 
                        foreach ($chequerasGeneradas as $chequera) {
                        ?>
                            <a href="<?php echo $chequera['pathArchivo'].'/'.$chequera['nombreArchivo']; ?>" target="_BLANK" ><img src="../public/images/pdf_Imagen.png" alt="<?php echo 'Asistente: '.$chequera['idCursosAsistente'].' - '.$chequera['apellidoNombre']; ?>" style="width:24px;height:24px;"> <?php echo 'Asistente: '.$chequera['idCursosAsistente'].' - '.$chequera['apellidoNombre']; ?> </a>
                            <br>
                        <?php
                        }
                    } else {
                    ?>
                        <h5 class="alert alert-warning">NO Se generaron chequeras.</h5>
                    <?php 
                    }
                    ?>
                </div>
            </div>
        <?php
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resAsistentes['mensaje'];
            ?>
                <div class="col-md-12">
                    <h2 class="alert alert-danger"><?php echo $mensaje; ?></h2>
                </div>
                <a href="curso_asistentes.php" class="btn btn-primary">Volver</a>
            <?php
        }
    } else {
        $pathOrigen = '../'; //$pathOrigen = '../../';
        $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $resChequera = $cursos_pdo->imprimirChequeraAsistenteCurso($idCursosAsistente, $cursos_pdo, $asistente, $pdf, $pathOrigen);
        if ($resChequera['estado']) {
            $chequeraPDF = $resChequera['chequeraPDF'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resChequera['mensaje'];
            ?>
                <div class="col-md-12">
                    <h2 class="alert alert-danger"><?php echo $mensaje; ?></h2>
                </div>
                <a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-primary">Volver</a>
            <?php
        }
        /*
        $resChequera = imprimirChequera($idCursosAsistente, $cursos_pdo, $asistente);
        if ($resChequera['estado']) {
            $pathArchivo = $resChequera['pathArchivo'];
            $nombreArchivo = $resChequera['nombreArchivo'];
        } else {
            $continua = FALSE;
            $mensaje .= "ERROR->".$resChequera['mensaje'];
            ?>
                <div class="col-md-12">
                    <h2 class="alert alert-danger"><?php echo $mensaje; ?></h2>
                </div>
                <a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-primary">Volver</a>
            <?php
        }
        */
        require_once ('../html/head.php');
        require_once '../html/header.php';
        ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Imprimir chequera <?php echo $titulo; ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <h4>Apellido y Nombre: <?php echo $apellidoNombre; ?></h4>
                    </div>
                    <?php 
                    if (isset($idColegiado) && $idColegiado <> "") {
                    ?>
                    <div class="col-md-2">
                        <h4>Matrícula: <?php echo $matricula; ?></h4>
                    </div>
                    <?php 
                    }
                    ?>
                    <div class="col-md-3">
                        <?php
                        if (isset($idColegiado) && $idColegiado <> "") {
                            $colegiadoLogic = new colegiadoLogic();
                            $correoRechazado = $colegiadoLogic->tieneCorreoRechazado($idColegiado);
                            if ($correoRechazado){
                            ?>
                                <h5 class="alert alert-danger">Debe actualizaar el correo electrónico porque el actual fue rechazado en el último envío.</h5>
                            <?php
                            } else {
                                $resContacto =  $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                                if ($resContacto['estado']) {
                                    $contacto = $resContacto['datos'];
                                    $noEnviaMail = $contacto['noEnviaMail'];
                                    if (!$noEnviaMail) {
                                        $mail = $contacto['email'];
                                    }
                                }
                            }
                        } else {
                            $mail = "";
                        }
                        ?>
                        <form id="formChequera" name="formChequera" method="POST" action="curso_asistentes_chequeras_mail.php">
                            <div class="col-md-10">
                                <label for="mail">Mail registrado *</label><br>
                                <input class="form-control" type="text" name="mail" id="mail" value="<?php echo $mail; ?>" required />
                            </div>
                            <div class="col-md-2">
                                <button type="submit"  class="btn btn-default" >Enviar mail </button>
                                <input type="hidden" name="idCurso" id="idCurso" value="<?php echo $idCurso; ?>" />
                                <input type="hidden" name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre; ?>" />
                                <input type="hidden" name="titulo" id="titulo" value="<?php echo $titulo; ?>" />
                                <input type="hidden" name="pathArchivo" id="pathArchivo" value="<?php echo $pathArchivo; ?>" />
                                <input type="hidden" name="nombreArchivo" id="nombreArchivo" value="<?php echo $nombreArchivo; ?>" />
                            </div>
                        </form>
                    </div>
                    <div class="col-md-1 text-right">
                        <a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-info">Volver</a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php
                if (!isset($chequeraPDF)) {
                    if (file_exists($pathArchivo.'/'.$nombreArchivo)) {
                        $pdf_content = file_get_contents($pathArchivo.'/'.$nombreArchivo);        
                        $chequeraPDF = base64_encode($pdf_content);   
                    } else {
                        $resultado['estado'] = FALSE;
                        $resultado['mensaje'] = 'CHEQUERA NO EXISTE.';
                    }
                }
                ?>
                <div class="row">
                   <embed src='data:application/pdf;base64,<?php echo $chequeraPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                </div> 
            </div>
        </div>
    <?php 
    }
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
    </div>
    <a href="curso_listado.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

function imprimirChequera($idCursosAsistente, $cursos_pdo, $asistente) {
    //style para el codigo de barras
    $styleCB = array(
        'position' => '',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => true,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 'auto',
        'vpadding' => 'auto',
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255),
        'text' => true,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );

    $styleCB = array(
        'position' => '',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => true,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 'auto',
        'vpadding' => 'auto',
        'fgcolor' => array(0,0,0),
        'bgcolor' => false, //array(255,255,255),
        'text' => true,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );
    $tipoPdf = 'F';
    $apellidoNombre = $asistente['apellidoNombre'];
    $idColegiado = $asistente['idColegiado'];
    $matricula = $asistente['matricula'];
    $idCurso = $asistente['idCurso'];
    if (isset($idColegiado) && $idColegiado <> "") {
        $esColegiado = "S";
    } else {
        $esColegiado = "N";
    }
    $titulo = $asistente['tituloCurso'];
    $generoChequera = FALSE;

    $resCuotas = $cursos_pdo->obtenerCuotasPorAsistente($idCursosAsistente);
    if ($resCuotas['estado'] && $resCuotas['cuotasAdeudadas'] > 0) {
        $totalDeuda = 0;
        foreach ($resCuotas['datos'] as $dato) {
            $idCursosAsistenteCuota = $dato['idCursosAsistenteCuota'];
            $cuota = $dato['cuota'];
            $importe = $dato['importe'];
            $fechaVencimiento = $dato['fechaVencimiento'];
            $cuotaAbonada = $dato['abonada'];
            $detalleCuota = $cuota.'-'.substr($fechaVencimiento, 0, 4).' ('.trim($dato['detalleCuota']).')';

            if ($cuotaAbonada) { continue; }
            if (!$generoChequera) {
                $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
                $pdf->SetPrintHeader(false);
                $pdf->SetPrintFooter(true);
                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetMargins(0, 0, 0);
                //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
                //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                $pdf->SetAutoPageBreak(TRUE, 0);
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                $pdf->SetFont('dejavusans', '', 8);
                $pdf->AddPage();
                //$posLinea = 40;
                $posLinea = 0;
                $generoChequera = TRUE;
            }
            $posLinea += 40;
            if ($fechaVencimiento < date('Y-m-d')) {
                $fechaVencimiento = ultmioDiaDelMes(date('Y-m-d'));
            }
            $pdf->Image('../public/images/logoChequera.png' , 5, $posLinea-35, 80 , 10,'PNG');                                
            $pdf->SetXY(3, $posLinea-25);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, $apellidoNombre, 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(3, $posLinea-20);
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, 5, 'Asistente: ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(25, $posLinea-20);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, $idCursosAsistente, 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(3, $posLinea-15);
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, 5, 'Recibo ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(25, $posLinea-15);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, $idCursosAsistenteCuota, 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(3, $posLinea-10);
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, 5, 'Vencimiento: ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(25, $posLinea-10);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, cambiarFechaFormatoParaMostrar($fechaVencimiento), 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(3, $posLinea-5);
            $pdf->SetFont('dejavusans', '', 9);
            $pdf->MultiCell(0, 5, 'Importe: ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(25, $posLinea-5);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, '$'.number_format($importe, 2, ',', ''), 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(80, $posLinea-30);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 0, $titulo, 0, 'L', false, 1, '', '', true);
            /*
            $pdf->MultiCell(0, 5, 'Importe: ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(117, $posLinea-30);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, '$'.number_format($importe, 2, ',', ''), 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(150, $posLinea-30);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, 'Vencimiento: '.cambiarFechaFormatoParaMostrar($fechaVencimiento), 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(100, $posLinea-25);
            $pdf->MultiCell(0, 5, 'Recibo ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(115, $posLinea-25);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, $idCursosAsistenteCuota, 0, 'L', false, 1, '', '', true);
            */
            $pdf->SetXY(100, $posLinea-25);
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->MultiCell(0, 5, 'CUOTA '.$detalleCuota, 0, 'L', false, 1, '', '', true);
            $comprobante = '7'.rellenarCeros($idCursosAsistenteCuota, 6);
            $codigoBarra = $colegiadoDeudaAnualLogic->obtenerCodigoBarra44($comprobante, $importe, $importe, $fechaVencimiento, $fechaVencimiento, NULL);
            $pdf->SetXY(80, $posLinea-20);
            $pdf->write1DBarcode($codigoBarra, 'I25', '', '', '', 18, 0.4, $styleCB, 'N');
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->SetXY(110, $posLinea-35);
            $pdf->MultiCell(0, 5, 'Código Pago Electrónico Red Link / PagoMisCuentas: ', 0, 'L', false, 1, '', '', true);
            $pdf->SetXY(185, $posLinea-35);
            $pdf->SetFont('dejavusans', 'B', 9);
            $pdf->MultiCell(0, 5, rellenarCeros($idCursosAsistente, 8), 0, 'L', false, 1, '', '', true);
            
            $pdf->Line(0, $posLinea, 220, $posLinea, array('width' => 0));
            
            $cuotasImpresas++;
            if ($cuotasImpresas >= 7) { //$cuotasImpresas >= 8
                $cuotasImpresas = 0;
                $pdf->AddPage();
                $posLinea = 0;
            }
        }
    }
    if ($generoChequera) {
        $pdf->lastPage();
        $destination = $tipoPdf; 
        ob_clean();
        $camino = $_SERVER['DOCUMENT_ROOT'];
        $camino .= PATH_PDF;
        $pathArchivo = "../archivos/chequera_cursos/".PERIODO_ACTUAL;
        $nombreArchivo = 'Chequera_Curso_Asistente_'.$idCursosAsistente.'.pdf';
        if (!file_exists($pathArchivo)) {
            mkdir($pathArchivo, 0777, true);
        }
        if (file_exists($pathArchivo."/".$nombreArchivo)) {
            unlink($pathArchivo."/".$nombreArchivo);
        } 

        $pdf->Output($camino.'/archivos/chequera_cursos/'.PERIODO_ACTUAL.'/'.$nombreArchivo, $destination);      

        $resultado['estado'] = TRUE;
        $resultado['mensaje'] = "Chequera generada.";
        $resultado['pathArchivo'] = $pathArchivo;
        $resultado['nombreArchivo'] = $nombreArchivo;
    } else {
        $resultado['estado'] = FALSE;
        $resultado['mensaje'] = "No se generó la chequera.";
    }

    return $resultado;
}
