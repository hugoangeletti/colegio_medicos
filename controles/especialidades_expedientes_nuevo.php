<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();

$continua = TRUE;
$mensaje = 'OK';

$titulo = "";
//si es una nueva especialidad, armo el form completo
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idMesaEntradaEspecialidad = $_GET['id'];
            
            $resMesa = $mesaEntradaEspecialistaLogic->obtenerMesaEntradaEspecialistaPorId($idMesaEntradaEspecialidad);
            if ($resMesa['estado']) {
                $mesaEntrada = $resMesa['datos'];
                $tipoEspecialista = $mesaEntrada['tipoTramiteEspecialista'];
                $especialidad = $mesaEntrada['idEspecialidad'];
                $inciso = $mesaEntrada['inciso'];
                $distrito = $mesaEntrada['distrito'];
                $numeroExpediente = $mesaEntrada['numeroExpediente'];
                $anioExpediente = $mesaEntrada['anioExpediente'];
                $titulo = "Expediente de Especialidades Nº ".$numeroExpediente.'/'.$anioExpediente;
            } else {
                $continua = FALSE;
                ?>
                <div class="<?php echo $resMesa['clase']; ?>" role="alert">
                    <span class="<?php echo $resMesa['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resMesa['mensaje']; ?></strong></span>
                </div>        
            <?php
            }
        } else {
            $continua = FALSE;
            ?>
            <div class="alert alert-error" role="alert">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                <span><strong>NO SELECCIONO EL EXPEDIENTE CORRECTAMENTE</strong></span>
            </div>        
        <?php
        }
    } else {
        $continua = FALSE;
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php
    }
    
    if ($continua) {
    ?>
        <div class="panel panel-default">
            <div class="panel-heading"><h4><b><?php echo $titulo; ?>  </b></h4></div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-1">
                        <b>Matrícula</b>
                        <input class="form-control" type="text" name="matricula" id="matricula" readonly="" value="<?php echo $matricula; ?>" />
                    </div>                    
                    <div class="col-md-4">
                        <b>Apellido y Nombre</b>
                        <input class="form-control" type="text" name="apellidoNombre" id="apellidoNombre" readonly="" value="<?php echo $apellidoNombre ?>"/>
                    </div>                    
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-12 text-center alert-info"><h4><b>EXPEDIENTE GENERADO CORRECTAMENTE, YA PUEDE IMPRIMIR</b></h4></div>
                    </div>                    
                </div>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="col-md-12 text-center">
            <a href="datosMesaEntrada/especialidades_expedientes_imprimir.php?n_exp=<?php echo $numeroExpediente; ?>&a_exp=<?php echo $anioExpediente; ?>" target="_BLANK" 
               class="btn btn-info glyphicon glyphicon-print">&nbsp;Imprimir Expediente</a>
        </div>  
        <br>
    <?PHP
    }
} else {
    $mensaje = "TIPO ESPECIALISTA NO INGRESADO";
    $continua = FALSE;
}
?>
<!-- BOTON VOLVER -->    
<div class="col-md-12" style="text-align:right;">
    <form  method="POST" action="especialidades_expedientes.php">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Cerrar </button>
   </form>
</div>  
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';
