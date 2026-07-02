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
if (isset($_POST['accion']) && isset($_POST['estadoInspectores'])){
    $accion = $_POST['accion'];
    $estadoInspectores = $_POST['estadoInspectores'];
    if (isset($_POST['idInspector'])){
        $idInspector = $_POST['idInspector'];
        $resInspector = $habilitacionConsultorioLogic->obtenerInspectorPorId($idInspector);
        if ($resInspector['estado']){
            $inspector = $resInspector['datos'];
            $idColegiado = $inspector['idColegiado'];
            $matricula = $inspector['matricula'];
            $apellidoNombre = $inspector['apellidoNombre'];
        } else {
            $continua = FALSE;
        }
    } else {
        $idInspector = NULL;
    }

    if ($accion == 2){
        if ($estadoInspectores == 'A') {
            $titulo="Eliminar Inspector";
            $nombreBoton="Eliminar inspector";
        } else {
            $titulo="Activar Inspector";
            $nombreBoton="Activar inspector";
        }
    } else {
        $titulo="Nuevo Inspector";
        $nombreBoton="Guardar";
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
        $idInspector = $_POST['idInspector'];
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <form id="formInspector" name="formInspector" method="POST" onSubmit="" action="datosHabilitaciones\abm_inspector.php">
                <div class="row">
                    <div class="col-md-3" style="text-align: right;">
                        <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    </div>
                    <div class="col-md-7">
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" <?php if (isset($idInspector)) { ?> value="<?php echo $matricula.' - '.$apellidoNombre; ?>" readonly="" <?php } else { ?> placeholder="Ingrese Matrícula o Apellido del colegiado" <?php } ?> required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                    </div>
                    <div class="col-md-2">
                        <div style="text-align:center">
                            <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                            <input type="hidden" name="idInspector" id="idInspector" value="<?php echo $idInspector; ?>" />
                            <input type="hidden" name="estadoInspectores" id="estadoInspectores" value="<?php echo $estadoInspectores; ?>" />
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        </div>
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
        <form  method="POST" action="habilitaciones_inspectores_lista.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
            <input type="hidden" name="estadoInspectores" id="estadoInspectores" value="<?php echo $estadoInspectores; ?>" />
       </form>
    </div>  
    <div class="row">&nbsp;</div>
<?php    
require_once '../html/footer.php';
?>
</div>
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