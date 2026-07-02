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
if (isset($_GET['idMesaEntrada'])){
    $idMesaEntrada = $_GET['idMesaEntrada'];
    $resHabilitacion = $habilitacionConsultorioLogic->obtenerHabilitacionSolicitadaPorId($idMesaEntrada);
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
        <div class="panel-heading"><h4><b>Eliminar Solicitud de Habilitación de Consultorio</b></h4></div>
        <div class="panel-body">
            <form id="formEliminar" name="formEliminar" method="POST" onSubmit="" action="datosHabilitaciones\eliminar_solicitud.php">
                <div class="row">
                    <div class="col-md-2">
                        Fecha Ingreso: 
                        <b><input class="form-control" type="text" value="<?php echo cambiarFechaFormatoParaMostrar($habilitacion['fechaIngreso']); ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-2">
                        Matrícula:
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['matricula']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Apellido y Nombre: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['apellidoNombre']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Especialidad: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['especialidad']; ?>" readonly=""/></b>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-4">
                        Dirección: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['domicilio']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-4">
                        Localidad: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['localidad']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-2">
                        Teléfono: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['telefono']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-2">
                        Mail: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['mail']; ?>" readonly=""/></b>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        Horarios: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['horarios']; ?>" readonly=""/></b>
                    </div>
                    <div class="col-md-6">
                        Observaciones: 
                        <b><input class="form-control" type="text" value="<?php echo $habilitacion['observaciones']; ?>" readonly=""/></b>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-danger " >Elimina Solicitud</button>
                        <input type="hidden" name="idMesaEntrada" id="idMesaEntrada" value="<?php echo $idMesaEntrada; ?>" />
                        <input type="hidden" name="idMesaEntradaConsultorio" id="idMesaEntradaConsultorio" value="<?php echo $habilitacion['idMesaEntradaConsultorio']; ?>" />
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
        <form  method="POST" action="habilitaciones_solicitadas_lista.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
       </form>
    </div>  
    <div class="row">&nbsp;</div>
<?php    
require_once '../html/footer.php';
