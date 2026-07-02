<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');

require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/habilitacionConsultorioLogic.php');
$habilitacionConsultorioLogic = new habilitacionConsultorioLogic();

?>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h4>Asignar Inspector a Solicitud de Habilitación de Consultorio</h4>
    </div>
    <div class="panel-body">
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <?php
                if (isset($_POST['idMesaEntrada']) && isset($_POST['idInspector'])){
                    $idsMesaEntrada = $_POST['idMesaEntrada'];
                    $idInspector = $_POST['idInspector'];

                    $resultado = $habilitacionConsultorioLogic->asignarInspectorAHabilitacion($idInspector, $idsMesaEntrada);
                    ?>
                        <div class="<?php echo $resultado['clase']; ?>" role="alert">
                            <span class="<?php echo $resultado['icono']; ?>" ></span>
                            <span><strong><?php echo $resultado['mensaje']; ?></strong></span>
                        </div>
                    <?php    
                    if($resultado['estado']) {
                        $inspecciones = $resultado['datos'];
                        //se asignaron los inspectores a las habilitaciones, muestro boton para imprimir actas
                    ?>
                        <form name="myForm"  method="POST" target="_BLANK" action="datosHabilitaciones/imprimir_actas.php">
                            <button type="submit" name='confirma' id='confirma' class="btn btn-warning btn-lg">Imprimir Acta/s de Inspección</button>
                            <input type="hidden"  name="inspecciones" id="inspecciones" value='<?php echo serialize($inspecciones);?>'>
                        </form>
                    <?php
                    }
                } else {
                    if (isset($_POST['idInspector'])){
                ?>
                    <div class="alert alert-error" role="alert">
                        <span class="glyphicon glyphicon-remove" ></span>
                        <span><strong>DEBE SELECCIONAR ALGUNA SOLICITUD DE HABILITACION</strong></span>
                    </div>
                <?php    
                    } else {
                ?>
                    <div class="alert alert-error" role="alert">
                        <span class="glyphicon glyphicon-remove" ></span>
                        <span><strong>DEBE SELECCIONAR ALGUN INSPECTOR</strong></span>
                    </div>
                <?php    
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
<div class="row">&nbsp;</div>
<!-- BOTON VOLVER -->    
<div class="col-md-12" style="text-align:right;">
    <form  method="POST" action="habilitaciones_solicitadas_lista.php">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
   </form>
</div>  
<div class="row">&nbsp;</div>
<?php 
require_once '../html/footer.php';
