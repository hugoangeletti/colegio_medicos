<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/retiroDocumentacionLogic.php');
$retiroDocumentacionLogic = new retiroDocumentacionLogic();

$accion = $_POST['accion'];
if (isset($_POST['idTipoDocumentacionRetiro'])){
    $idTipoDocumentacionRetiro = $_POST['idTipoDocumentacionRetiro'];
} else {
    $idTipoDocumentacionRetiro = NULL;
}
$continua = TRUE;

if ($accion == 3){
    $resTipo = $retiroDocumentacionLogic->obtenerTipoDocumentacionRetiroPorId($idTipoDocumentacionRetiro);
    if ($resTipo['estado']){
        $tipoDocumentacionRetiro = $resTipo['datos'];
        $idTipoDocumentacionRetiro = $tipoDocumentacionRetiro['idTipoDocumentacionRetiro'];
        $nombre = $tipoDocumentacionRetiro['nombre'];
    } else {
        var_dump($resTipo);
        $continua = FALSE;
    }
    $titulo="Editar Tipo Documentación a retirar";
    $nombreBoton="Guardar cambios";
} else {
    $titulo="Nuevo Tipo Documentación a retirar";
    $nombreBoton="Guardar ";
    $nombre = NULL;
}        
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
        $idTipoDocumentacionRetiro = $_POST['idTipoDocumentacionRetiro'];
        $nombre = $_POST['nombre'];
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <form id="formExpediente" name="formExpediente" method="POST" onSubmit="" action="datosRetiro\abm_tipo_documentacion.php">
                <div class="row">
                    <div class="col-md-7">
                        <b>Nombre *</b>  
                        <input class="form-control" type="text" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="nombre" id="nombre" placeholder="Ingrese el detalle del tipo de Documentación" value="<?php echo $nombre; ?>" required=""/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  

                <input type="hidden" name="idTipoDocumentacionRetiro" id="idTipoDocumentacionRetiro" value="<?php echo $idTipoDocumentacionRetiro; ?>" />
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="tipo_documentacion_retiro.php">
                <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
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
