<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoArchivoLogic.php';

$continua = true;
$mensaje = '';
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
} else {
    $continua = false;
    $mensaje .= "Ingreso incorrecto.";
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-4">
                <h4>Imprimir Credencial</h4>
            </div>
            <?php
            if ($continua) {
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);            
                if ($resColegiado['estado']){
                    $colegiado = $resColegiado['datos'];
                    $matricula = $colegiado['matricula'];
                    $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
                    $sexo = $colegiado['sexo'];
                    $numeroDocumento = $colegiado['numeroDocumento'];
                    $hashColegiado = $colegiado['hashColegiado'];
                    $fechaMatriculacion = $colegiado['fechaMatriculacion'];
                } else {
                    $continua = FALSE;
                    $mensaje .= $resColegiado['mensaje'];
                }
            }
            if ($continua) {
            ?>
                <div class="col-md-6">
                    <h4><b>Credencial de <?php echo $apellidoNombre; ?> Matrícula: <?php echo $matricula; ?></b></h4>
                </div>
                <div class="col-md-1 text-right">
                    <a href="colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-info">Salir</a>
                </div>
            <?php 
            } else {
            ?>
                <div class="col-md-7 text-right">
                    <a href="colegiado_consulta.php" class="btn btn-info">Salir</a>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if ($continua) {
            $credencialPDF = NULL;
            //var_dump($php_generar);
            //echo '<br>tipo->'.$tipo; exit;
            require_once('datosColegiadoCredencial/generar_credencial.php');        
            if (isset($credencialPDF)) {
                ?>
                <div class="row">
                   <embed src='data:application/pdf;base64,<?php echo $credencialPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                </div> 
            <?php 
            } else {
                echo 'ERROR AL OBTENER CREDENCIAL';
            }
        }
        ?>
    </div>
</div>
<?php
include("../html/footer.php");

