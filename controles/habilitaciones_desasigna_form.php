<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();

$continua = TRUE;
if (isset($_GET['id'])){
    $idInspectorHabilitacion = $_GET['id'];
    $resHabilitacion = $habilitacionConsultorioLogic->obtenerInspeccionPorId($idInspectorHabilitacion);
    if ($resHabilitacion['estado']) {
        $habilitacion = $resHabilitacion['datos'];
    } else {
        $continua = FALSE;
    }        
} else {
    $continua = FALSE;
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
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-danger">
        <div class="panel-heading"><h4><b>Desasignar Habilitación de Consultorio</b></h4></div>
        <div class="panel-body">
            <form id="formEliminar" name="formEliminar" method="POST" onSubmit="" action="datosHabilitaciones\desasignar_habilitacion.php">
                <div class="row">
                    <div class="col-md-2">
                        Matrícula Solicitante:
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['matriculaColegiado']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Apellido y Nombre Solicitante: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['apellidoNombreColegiado']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        Matrícula Inspector:
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['matriculaInspector']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Apellido y Nombre Inspector: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['apellidoNombreInspector']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-4">
                        Dirección: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['domicilio']; ?>" readonly=""/></b>
                    </div>
                </div>                
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-danger " >Desasigna habilitación</button>
                        <input type="hidden" name="idInspectorHabilitacion" id="idInspectorHabilitacion" value="<?php echo $idInspectorHabilitacion; ?>" />
                    </div>
                </div>
            </form>   
        </div>
     </div>

<?php
} else {
    echo 'Error de Acceso';
}
?>
    <!-- BOTON VOLVER -->    
    <div class="col-md-12" style="text-align:right;">
        <form  method="POST" action="habilitaciones_asignadas_lista.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
       </form>
    </div>  
    <div class="row">&nbsp;</div>
<?php    
require_once '../html/footer.php';
