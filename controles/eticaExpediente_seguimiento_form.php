<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eticaEstadoLogic.php');
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
require_once ('../dataAccess/eticaExpedienteMovimientoLogic.php');

$accion = $_POST['accion'];
$estadoExpediente = $_POST['estadoExpediente'];
$idEticaExpediente = $_POST['idEticaExpediente'];

$continua = TRUE;
$idEticaEstado = NULL;
$derivado = "";
$observacion = "";
$usuario = "";
$fecha = NULL;

$resEticaExpediente = $eticaExpedienteLogic->obtenerEticaExpedientePorId($idEticaExpediente);
if ($resEticaExpediente['estado']){
    $eticaExpediente = $resEticaExpediente['datos'];
} else {
    $continua = FALSE;
}

$titulo="Nuevo Movimiento";
$nombreBoton="Guardar Movimiento";
        
if ($continua){
?>
    <?php
    if (isset($_POST['mensaje']))
    {
     ?>
        <div id="divMensaje"> 
            <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
     <?php    
        $idEticaEstado = $_POST['idEticaEstado'];
        $derivado = $_POST['derivado'];
        $observacion = $_POST['observacion'];
        $fecha = $_POST['fecha'];
    } else {
        $fecha = date('Y-m-d');
    }
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-6"><h4><b>Colegiado:</b> <?php echo $eticaExpediente['apellido'].' '.$eticaExpediente['nombres'].' ('.$eticaExpediente['matricula'].')'; ?></h4></div>
                <div class="col-xs-6"><h4><b>Car&aacute;tula:</b> <?php echo $eticaExpediente['caratula']; ?></h4></div>
            </div>
            <form id="formExpediente" name="formExpediente" method="POST" onSubmit="" action="datosEticaExpediente\abm_eticaExpedienteSeguimiento.php">
                <div class="row">
                    <div class="col-md-2">
                        <b>Fecha *</b>  
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>" placeholder="dd/mm/aaaa" required="">
                    </div>                
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <b>Observaciones</b>
                        <textarea class="form-control" rows="5" id="observacion" name="observacion" ><?php echo $observacion; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  
                <input type="hidden" name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente; ?>" />
                <input type="hidden" name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>" />
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="eticaExpediente_seguimiento.php">
                <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                <input type="hidden" name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente; ?>" />
                <input type="hidden" name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>" />
           </form>
        </div>  
        </div>
     </div>

    <?php    
    require_once '../html/footer.php';
    ?>
    </div>
<?php
}
