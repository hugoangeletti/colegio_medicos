<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eleccionesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesListasLogic.php');
$eleccionesLocalidadesListasLogic = new eleccionesLocalidadesListasLogic();

$continua = TRUE;
if (isset($_POST['idElecciones']) && isset($_POST['idEleccionesLocalidad'])){
    $idElecciones = $_POST['idElecciones'];
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
    if (isset($_POST['idEleccionesLocalidadLista'])){
        $idEleccionesLocalidadLista = $_POST['idEleccionesLocalidadLista'];
    } else {
        $idEleccionesLocalidadLista = NULL;
    }
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
    if ($resElecciones['estado']){
        $elecciones = $resElecciones['datos'];
        $detalleElecciones = $elecciones['detalle'];
        $estado = $elecciones['estado'];
        $anio = $elecciones['anio'];

        $eleccionesLocalidadesLogic = new eleccionesLocalidades();
        $resLocalidades = $eleccionesLocalidadesLogic->obtenerEleccionesLocalidadPorId($idEleccionesLocalidad);
        if ($resLocalidades['estado']) {
            $localidad = $resLocalidades['datos'];
            $detalleLocalidad = $localidad['localidadDetalle'];
        } else {
            $continua = FALSE;
        }
    } else {
        $continua = FALSE;
    }

    $accion = $_POST['accion'];
    if ($accion == 3){
        $resEleccionesListas = $eleccionesLocalidadesListasLogic->obtenerEleccionesLocalidadListaPorId($idEleccionesLocalidadLista);
        if ($resEleccionesListas['estado']){
            $lista = $resEleccionesListas['datos'];
            $nombre = $lista['nombre'];
            $tipoLista = $lista['tipoLista'];
        } else {
            $continua = FALSE;
        }
        $titulo="Editar Lista";
        $nombreBoton="Guardar cambios";
    } else {
        $titulo="Nueva Lista";
        $nombreBoton="Guardar";
        $nombre = "";
        $tipoLista = "";
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
        $idElecciones = $_POST['idElecciones'];
        $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
        if (isset($_POST['idEleccionesLocalidadLista']) && $_POST['idEleccionesLocalidadLista'] <> "") {
            $idEleccionesLocalidadLista = $_POST['idEleccionesLocalidadLista'];
        } else {
            $idEleccionesLocalidadLista = NULL;
        }
        $nombre = $_POST['nombre'];
        $tipoLista = $_POST['tipoLista'];
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo.' para '.$detalleElecciones.' de '.$detalleLocalidad; ?></b></h4></div>
        <div class="panel-body">
            <form id="formElecciones" name="formElecciones" method="POST" onSubmit="" action="datosElecciones\abm_elecciones_listas.php">
                <div class="row">
                    <div class="col-md-8">
                        <b>Nombre de la Lista *</b>  
                        <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Nombre de la lista" value="<?php echo $nombre; ?>" required=""/>
                    </div>
                    <div class="col-md-4">
                        <b>Tipo de lista *</b>  
                        <select class="form-control" id="tipoLista" name="tipoLista" required="">
                            <option value="C" <?php if($tipoLista == "C") { echo 'selected'; } ?>>Consejeros</option>
                            <option value="T" <?php if($tipoLista == "T") { echo 'selected'; } ?>>Tribunal Disciplinario</option>
                            <option value="B" <?php if($tipoLista == "B") { echo 'selected'; } ?>>Voto en Blanco</option>
                            <option value="A" <?php if($tipoLista == "A") { echo 'selected'; } ?>>Voto Anulado</option>
                        </select>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  

                <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
                <input type="hidden" name="idEleccionesLocalidad" id="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>" />
                <input type="hidden" name="idEleccionesLocalidadLista" id="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>" />
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
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
        <form  method="POST" action="elecciones_listas_lista.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
            <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
            <input type="hidden" name="idEleccionesLocalidad" id="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>" />
       </form>
    </div>  
    <div class="row">&nbsp;</div>
<?php    
require_once '../html/footer.php';
?>
</div>
