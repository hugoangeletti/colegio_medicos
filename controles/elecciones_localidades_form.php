<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/zonaLogic.php');
$zonaLogic = new zonaLogic();
require_once ('../dataAccess/eleccionesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');

$accion = $_POST['accion'];
$idElecciones = $_POST['idElecciones'];

//armo el arreglo para no generar duplicidad
$arrayLocalidades = array();
$resZonas = $zonaLogic->obtenerZonasPorIdElecciones($idElecciones);
if ($resZonas['estado']) {
    $i = 0;
    foreach ($resZonas['datos'] as $zona) {
        $arrayLocalidades[$i] = $zona['codigo'];
        $i += 1;
    }
}

if (isset($_POST['idEleccionesLocalidad'])){
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
} else {
    $idEleccionesLocalidad = NULL;
}
$continua = TRUE;

if ($accion == 3){
    $eleccionesLocalidadesLogic = new eleccionesLocalidades();
    $resElecciones = $eleccionesLocalidadesLogic->obtenerEleccionesLocalidadPorId($idEleccionesLocalidad);
    if ($resElecciones['estado']){
        $elecciones = $resElecciones['datos'];
        $codLocalidad = $elecciones['codigoLocalidad'];
        $cantDelegados = $elecciones['cantDelegados'];
    } else {
        $continua = FALSE;
    }
    $titulo="Editar Localidad";
    $nombreBoton="Guardar cambios";
} else {
    $titulo="Nueva Localidad";
    $nombreBoton="Guardar";
    $codLocalidad = "";
    $cantDelegados = "";
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
        $codLocalidad = $_POST['codigoLocalidad'];
        $cantDelegados = $_POST['cantDelegados'];    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <form id="formElecciones" name="formElecciones" method="POST" onSubmit="" action="datosElecciones\abm_elecciones_localidades.php">
                <div class="row">
                    <div class="col-md-6">
                        <b>Localidad *</b>  
                        <select class="form-control" id="codigoLocalidad" name="codigoLocalidad" required="">
                            <?php
                            $resZonas = $zonaLogic->obtenerZonas();
                            if ($resZonas['estado']) {
                            ?>
                                <option value="">Seleccione Localidad</option>
                                <?php
                                foreach ($resZonas['datos'] as $row) {
                                    if (in_array($row['codigo'], $arrayLocalidades)) {
                                        echo 'existe. '.$row['nombre'];
                                    } else {
                                ?>
                                    <option value="<?php echo $row['codigo'] ?>" <?php if($codLocalidad == $row['codigo']) { echo 'selected'; } ?>><?php echo $row['nombre'] ?></option>
                                <?php
                                    }
                                }
                            } else {
                            ?>
                                <div class="col-md-12">
                                    <div class="<?php echo $resZonas['clase']; ?>" role="alert">
                                        <span class="<?php echo $resZonas['icono']; ?>" aria-hidden="true"></span>
                                        <span><strong><?php echo $resZonas['mensaje']; ?></strong></span>
                                    </div>        
                                </div>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <b>Cantidad de Delegados </b>  
                        <input class="form-control" type="number" name="cantDelegados" id="cantDelegados" value="<?php echo $cantDelegados; ?>" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  

                <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="elecciones_localidades_lista.php">
                <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
           </form>
        </div>  
        </div>
     </div>
    </div>
<?php
}
require_once '../html/footer.php';
