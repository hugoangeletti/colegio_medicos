<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/reconocimientoAntiguedadLogic.php';

$continua = true;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idReconocimientoAntiguedadDetalle = $_GET['id'];
    $estado = $_GET['filtro'];
} else {
    if (isset($_GET['idActo']) && $_GET['idActo'] <> "") {
        $idReconocimientoAntiguedad = $_GET['idActo'];
        $estado = $_GET['filtro'];
    } else {
        $continua = false;
        $mensaje .= "Ingreso incorrecto.";
    }
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-4">
                <h4>Imprimir Diplomas</h4>
            </div>
            <?php
            if ($continua) {
                $actosLogic = new reconocimientoAntiguedadLogic();
                if (isset($idReconocimientoAntiguedadDetalle)) {
                    $resActoColegiado = $actosLogic->obtenerActoDetallePorId($idReconocimientoAntiguedadDetalle);            
                    if ($resActoColegiado['estado']){
                        $actoColegiado = $resActoColegiado['datos'];
                        $idReconocimientoAntiguedad = $actoColegiado['idReconocimientoAntiguedad'];
                        $fechaActo = $actoColegiado['fechaActo'];
                        $lugarActo = $actoColegiado['lugarActo'];
                        $antiguedad = $actoColegiado['antiguedad'];
                        $idColegiado = $actoColegiado['idColegiado'];
                        $matricula = $actoColegiado['matricula'];
                        $apellidoNombre = $actoColegiado['apellidoNombre'];
                        $sexo = $actoColegiado['sexo'];

                        $cantidadDiplomas = 1;
                        $diplomaUnico = TRUE;
                        $actoColegiados[0]['idReconocimientoAntiguedadDetalle'] = $idReconocimientoAntiguedadDetalle;
                        $actoColegiados[0]['idColegiado'] = $idColegiado;
                        $actoColegiados[0]['estadoInvitacion'] = "CITADO";
                        $actoColegiados[0]['codigoDeudor'] = NULL;
                        $actoColegiados[0]['estadoMatricular'] = NULL;
                        $actoColegiados[0]['matricula'] = $matricula;
                        $actoColegiados[0]['apellidoNombre'] = $apellidoNombre;
                        $actoColegiados[0]['sexo'] = $sexo;
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resActoColegiado['mensaje'];
                    }
                } else {
                    $resActos = $actosLogic->obtenerActoPorId($idReconocimientoAntiguedad);            
                    if ($resActos['estado']){
                        $acto = $resActos['datos'];
                        $fechaActo = $acto['fechaActo'];
                        $lugarActo = $acto['lugarActo'];
                        $antiguedad = $acto['antiguedad'];

                        $resActoColegiados = $actosLogic->obtenerColegiadosPorActo($idReconocimientoAntiguedad, $estado);
                        if ($resActoColegiados['estado']){
                            $actoColegiados = $resActoColegiados['datos'];
                            $cantidadDiplomas = sizeof($actoColegiados);
                            $diplomaUnico = FALSE;
                        } else {
                            $continua = FALSE;
                            $mensaje .= $resActoColegiados['mensaje'];    
                        }
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resActos['mensaje'];
                    }
                }
            }
            if ($continua) {
            ?>
                <div class="col-md-6">
                    <h4><b>Diploma por los <?php echo $antiguedad; ?> años de recibido. Fecha del acto: <?php echo cambiarFechaFormatoParaMostrar($fechaActo); ?></b></h4>
                </div>
                <div class="col-md-1 text-right">
                    <a href="reconocimiento_antiguedad_detalle.php?id=<?php echo $idReconocimientoAntiguedad; ?>" class="btn btn-info">Salir</a>
                </div>
            <?php 
            } else {
            ?>
                <div class="col-md-7 text-right">
                    <a href="reconocimiento_antiguedad.php" class="btn btn-info">Salir</a>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if ($continua) {
            $diplomaPDF = NULL;
            //var_dump($php_generar);
            //echo '<br>tipo->'.$tipo; exit;
            require_once('datosActo/generar_diploma.php');        
            if (isset($diplomaPDF)) {
                ?>
                <div class="row">
                   <embed src='data:application/pdf;base64,<?php echo $diplomaPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                </div> 
            <?php 
            } else {
                echo 'ERROR AL OBTENER EL DIPLOMA';
            }
        }
        ?>
    </div>
</div>
<?php
include("../html/footer.php");

