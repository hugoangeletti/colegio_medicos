<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eticaEstadoLogic.php');
$eticaEstadoLogic = new eticaEstadoLogic();
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
require_once ('../dataAccess/eticaExpedienteMovimientoLogic.php');
$eticaExpedienteMovimientoLogic = new eticaExpedienteMovimientoLogic();

$continua = TRUE;
$mensaje = "";
$estadoExpediente = 'S';

if (isset($_GET['accion']) && $_GET['accion'] <> "") {
    $accion = $_GET['accion'];
    if (isset($_GET['id']) && $_GET['id'] <> ""){
        $idEticaExpedienteMovimiento = $_GET['id'];
        //traemos el registro del movimiento para editar
        $resExpedienteMovimiento = $eticaExpedienteMovimientoLogic->obtenerEticaExpedienteMovimientoPorId($idEticaExpedienteMovimiento);
        if ($resExpedienteMovimiento['estado']) {
            $eticaExpedienteMovimiento = $resExpedienteMovimiento['datos'];
            $idEticaExpediente = $eticaExpedienteMovimiento['idEticaExpediente'];
            $idEticaEstado = $eticaExpedienteMovimiento['idEticaEstado'];
            $derivado = $eticaExpedienteMovimiento['derivado'];
            $fecha = $eticaExpedienteMovimiento['fecha'];
            $observacion = $eticaExpedienteMovimiento['observacion'];
        } else {
            $mensaje .= $resExpedienteMovimiento['mensaje']; 
            $continua = FALSE;
        }
    } else {
        $idEticaExpedienteMovimiento = NULL;
        $idEticaExpediente = NULL;
        $mensaje .= "Falta idEticaExpedienteMovimiento"; 
        $continua = FALSE;
    }
} else {
    $accion = 1;
    $idEticaExpedienteMovimiento = NULL;
    if (isset($_GET['idEticaExpediente']) && $_GET['idEticaExpediente'] <> ""){
        $idEticaExpediente = $_GET['idEticaExpediente'];
        $idEticaEstado = NULL;
        $derivado = NULL;
        $fecha = date('Y-m-d');
        $observacion = NULL;
    } else {
        $idEticaExpediente = NULL;
        $mensaje .= "Falta idEticaExpediente"; 
        $continua = FALSE;
    }
}

switch ($accion) {
    case '1':
        $titulo="Nuevo Movimiento";
        break;
    
    case '3':
        $titulo="Editar Movimiento";
        break;
    
    default:
        $mensaje .= "Acceso incorrecto. sw "; 
        $continua = FALSE;
        break;
}
if (isset($idEticaExpediente)) {
    $resEticaExpediente = $eticaExpedienteLogic->obtenerEticaExpedientePorId($idEticaExpediente);
    if ($resEticaExpediente['estado']){
        $eticaExpediente = $resEticaExpediente['datos'];
        $idEticaExpediente = $eticaExpediente['idEticaExpediente'];
        $estadoExpediente = $eticaExpediente['estadoExpediente'];
        $matriculado = $eticaExpediente['apellido'].' '.$eticaExpediente['nombres'].' ('.$eticaExpediente['matricula'].')';
        $expedienteCaratula = $eticaExpediente['caratula'];
        $estadoExpediente = $eticaExpediente['estadoExpediente'];
    } else {
        $mensaje .= $resEticaExpediente['mensaje']; 
        $continua = FALSE;
    }
} else {
    $mensaje .= 'Falta idEticaExpediente.-'; 
    $continua = FALSE;
}
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <?php        
        if ($continua){
            if (isset($_POST['mensaje']))
            {
             ?>
                <div id="divMensaje"> 
                    <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
                </div>
             <?php    
            }   
            ?>  
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6"><h4><b>Colegiado:</b> <?php echo $matriculado; ?></h4></div>
                        <div class="col-xs-6"><h4><b>Car&aacute;tula:</b> <?php echo $expedienteCaratula; ?></h4></div>
                    </div>
                    <form id="formExpediente" name="formExpediente" method="POST" onSubmit="" action="datosEticaExpediente\abm_eticaExpedienteMovimiento.php?id=<?php echo $idEticaExpedienteMovimiento; ?>&accion=<?php echo $accion; ?>">
                        <div class="row">
                            <div class="col-md-4">
                                <b>Estado *</b>  
                                <select class="form-control" id="idEticaEstado" name="idEticaEstado" required="">
                                    <option value="">Seleccione estado</option>
                                    <?php
                                    $resEticaEstados = $eticaEstadoLogic->obtenerEticaEstados();
                                    if ($resEticaEstados['estado']){
                                        foreach ($resEticaEstados['datos'] as $eticaEstado) {
                                        ?>
                                            <option value="<?php echo $eticaEstado['id']; ?>" <?php if($eticaEstado['id'] == $idEticaEstado) { echo 'selected'; } ?>><?php echo $eticaEstado['nombre']; ?></option>
                                        <?php    
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <b>Derivado </b>  
                                <input type="text" class="form-control" id="derivado" name="derivado" value="<?php echo $derivado; ?>" placeholder="Detalla a donde se deriva el expediente" >
                            </div>
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
                                 <button type="submit"  class="btn btn-success " >Guardar Movimiento</button>
                             </div>
                        </div>  
                        <input type="hidden" name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente; ?>" />
                        <input type="hidden" name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>" />
                 </form>   
            <?php
            } else {
            ?>
                <div class="alert alert-danger" role="alert">
                    <span><strong><?php echo $mensaje; ?></strong></span>
                </div>
            <?php
            }
            ?>
            <!-- BOTON VOLVER -->    
            <div class="col-md-12" style="text-align:right;">
                <form  method="POST" action="eticaExpediente_movimientos.php">
                    <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                    <input type="hidden" name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente; ?>" />
                    <input type="hidden" name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>" />
               </form>
            </div>  
        </div>
     </div>
</div>
<?php
require_once '../html/footer.php';
?>
