<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eleccionesLogic.php');

$accion = $_POST['accion'];
$estadoElecciones = $_POST['estadoElecciones'];
if (isset($_POST['idElecciones'])){
    $idElecciones = $_POST['idElecciones'];
} else {
    $idElecciones = NULL;
}
$continua = TRUE;

if ($accion == 3){
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
    if ($resElecciones['estado']){
        $elecciones = $resElecciones['datos'];
        $idElecciones = $elecciones['idElecciones'];
        $detalle = $elecciones['detalle'];
        $estado = $elecciones['estado'];
        $anio = $elecciones['anio'];
    } else {
        $continua = FALSE;
    }
    $titulo="Editar Elecciones";
    $nombreBoton="Guardar cambios";
} else {
    $titulo="Nueva Elecciones";
    $nombreBoton="Guardar";
    $detalle = "";
    $estado = "";
    $anio = "";
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
        $detalle = $_POST['detalle'];
        $anio = $_POST['anio'];
        $estado = $_POST['estado'];
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <form id="formElecciones" name="formElecciones" method="POST" onSubmit="" action="datosElecciones\abm_elecciones.php">
                <div class="row">
                    <div class="col-md-7">
                        <b>Detalle *</b>  
                        <input class="form-control" type="text" name="detalle" id="detalle" placeholder="Elecciones año" value="<?php echo $detalle; ?>" required=""/>
                    </div>
                    <div class="col-md-4">
                        <b>Estado *</b>  
                        <select class="form-control" id="estado" name="estado" required="">
                            <option value="A" <?php if($estado == "A") { echo 'selected'; } ?>>Activa</option>
                            <option value="C" <?php if($estado == "C") { echo 'selected'; } ?>>Hist&oacute;rica</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <b>Año *</b>  
                        <input class="form-control" type="number" name="anio" id="anio" value="<?php echo $anio; ?>" required=""/>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  

                <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
                <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="elecciones_lista.php">
                <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                <input type="hidden" id="estadoElecciones" name="estadoElecciones" value="<?php echo $estadoElecciones; ?>">
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
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idColegiado').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
   
</script>