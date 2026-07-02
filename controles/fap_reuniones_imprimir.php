<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoDomicilioLogic.php';
require_once '../dataAccess/colegiadoContactoLogic.php';
require_once '../dataAccess/fapLogic.php';

$continuar = true;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSapConsejo = $_GET['id'];
    $continuar = TRUE;
    $mensaje = '';
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <?php 
                $continua = TRUE;
                $fapLogic = new fapLogic();
                $resReunion = $fapLogic->obtenerSapReunionPorId($idSapConsejo);
                if ($resReunion['estado']) {
                    $reunion = $resReunion['datos'];
                    $fechaReunion = $reunion['fechaReunion'];
                    $resolucion = $reunion['resolucion'];
                    $estadoReunion = $reunion['estadoReunion'];
                    $observaciones = $reunion['observaciones'];
                    ?>
                    <div class="col-md-4">
                        <h4>Imprimir Reunión de fecha <?php echo cambiarFechaFormatoParaMostrar($fechaReunion); ?></h4>
                    </div>
                <?php
                } else {
                    $continua = FALSE;
                    $mensaje .= $resReunion['mensaje'];
                    $clase = $resReunion['clase'];
                }
                ?>
                <div class="col-md-1 text-right">
                    <a href="fap_reuniones.php?anio=<?php echo substr($fechaReunion, 0, 4); ?>" class="btn btn-info">Salir</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            if ($continua) {
                $pathOrigen = '';
                $planillaPDF = NULL;
                require_once('datosFap/generar_planilla.php');        
                if (isset($planillaPDF)) {
                ?>
                    <div class="row">
                       <embed src='data:application/pdf;base64,<?php echo $planillaPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                    </div> 
                <?php 
                } else {
                    echo 'ERROR AL OBTENER EL PLANILLA';
                }
            } else {
                echo $mensaje;
            }
            ?>
        </div>
    </div>
<?php            
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
    </div>
    <a href="fap_listado.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

