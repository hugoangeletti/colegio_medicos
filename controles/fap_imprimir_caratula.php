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
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/fap_pdo.php');

$continuar = true;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idSapCaratula = $_GET['id'];
    $continuar = TRUE;
    $mensaje = '';
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-4">
                    <h4>Imprimir Carátula N° <?php echo $idSapCaratula; ?></h4>
                </div>
                <?php 
                $continua = TRUE;
                $fapLogic = new fap_pdo();
                $resFap = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
                if ($resFap['estado']) {
                    $fapCaratula = $resFap['datos'];
                    $idColegiado = $fapCaratula['IdColegiado'];
                    $matricula = $fapCaratula['Matricula'];
                    $apellidoNombre = $fapCaratula['Apellido'].' '.$fapCaratula['Nombres'];
                    ?>
                    <div class="col-md-2">
                        <h4>Matrícula: <?php echo $matricula; ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h4>Apellido y Nombre: <?php echo $apellidoNombre; ?></h4>
                    </div>
                <?php
                } else {
                    $continua = FALSE;
                    $mensaje .= $resFap['mensaje'];
                    $clase = $resFap['clase'];
                }
                ?>
                <div class="col-md-1 text-right">
                    <a href="fap_listado.php" class="btn btn-info">Salir</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            if ($continua) {
                $pathOrigen = '';
                $caratulaPDF = NULL;
                require_once('datosFap/generar_caratula.php');        
                if (isset($caratulaPDF)) {
                ?>
                    <div class="row">
                       <embed src='data:application/pdf;base64,<?php echo $caratulaPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                    </div> 
                <?php 
                } else {
                    echo 'ERROR AL OBTENER EL FAP';
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

