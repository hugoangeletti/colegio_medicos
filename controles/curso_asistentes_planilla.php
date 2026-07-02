<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
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
    $resAsistente = $cursos_pdo->obtenerAsistenteParaPlanillaPorId($idCursosAsistente);
    var_dump($resAsistente);
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
    $pathOrigen = '../'; //$pathOrigen = '../../';
    $pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $resPlanilla = $cursos_pdo->imprimirPlanillaInscripcion($idCursosAsistente, $cursos_pdo, $asistente, $pdf, $pathOrigen);
    if ($resPlanilla['estado']) {
        $planillaPDF = $resPlanilla['planillaPDF'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resPlanilla['mensaje'];
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
            if (!isset($planillaPDF)) {
                if (file_exists($pathArchivo.'/'.$nombreArchivo)) {
                    $pdf_content = file_get_contents($pathArchivo.'/'.$nombreArchivo);        
                    $planillaPDF = base64_encode($pdf_content);   
                } else {
                    $resultado['estado'] = FALSE;
                    $resultado['mensaje'] = 'CHEQUERA NO EXISTE.';
                }
            }
            ?>
            <div class="row">
               <embed src='data:application/pdf;base64,<?php echo $planillaPDF; ?>' height="800px" width='100%' type='application/pdf'> 
            </div> 
        </div>
    </div>
<?php 
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger"><?php echo $mensaje; ?></h2>
    </div>
    <a href="curso_listado.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");
