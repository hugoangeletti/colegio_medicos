<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/secretarioadhocLogic.php');
$secretarioadhocLogic = new secretarioadhocLogic();

$accion = $_POST['accion'];
if (isset($_POST['idSecretarioadhoc'])){
    $idSecretarioadhoc = $_POST['idSecretarioadhoc'];
} else {
    $idSecretarioadhoc = NULL;
}
$continua = TRUE;
$nombre = NULL;
$estado = "A";

if ($accion == 3){
    $resSecretario = $secretarioadhocLogic->obtenerSecretarioadhoPorId($idSecretarioadhoc);
    if ($resSecretario['estado']){
        $secretarioadhoc = $resSecretario['datos'];
        $idSecretarioadhoc = $secretarioadhoc['idSecretarioadhoc'];
        $nombre = $secretarioadhoc['nombre'];
        $estado = $secretarioadhoc['estado'];
    } else {
        $continua = FALSE;
    }
    $titulo="Editar Secretario ad-hoc";
    $nombreBoton="Guardar cambios";
} else {
    $titulo="Nuevo Secretario ad-hoc";
    $nombreBoton="Guardar Secretario ad-hoc";
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
        $idSecretarioadhoc = $_POST['idSecretarioadhoc'];
        $nombre = $_POST['nombre'];
        $estado = $_POST['estado'];
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <form id="formExpediente" name="formExpediente" method="POST" onSubmit="" action="datosSecretarioadhoc\abm_secretarioadhoc.php">
                <div class="row">
                    <div class="col-md-7">
                        <b>Apellido y nombres *</b>  
                        <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Ingrese Apellido y nombres del Secretario ad-hoc" value="<?php echo $nombre; ?>" required=""/>
                    </div>
                    <div class="col-md-5">
                        <b>Estado *</b>  
                        <select class="form-control" id="estado" name="estado" required="">
                            <option value="A" <?php if($estado == "A") { echo 'selected'; } ?>>Activo</option>
                            <option value="B" <?php if($estado == "B") { echo 'selected'; } ?>>Anulado</option>
                        </select>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  

                <input type="hidden" name="idSecretarioadhoc" id="idSecretarioadhoc" value="<?php echo $idSecretarioadhoc; ?>" />
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="secretarioadhoc_lista.php">
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
