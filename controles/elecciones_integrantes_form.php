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
require_once ('../dataAccess/eleccionesLocalidadesIntegrantesLogic.php');
$eleccionesLocalidadesIntegrantesLogic = new eleccionesLocalidadesIntegrantesLogic();

$continua = TRUE;
if (isset($_POST['idElecciones']) && isset($_POST['idEleccionesLocalidad']) && isset($_POST['idEleccionesLocalidadLista'])){
    $idElecciones = $_POST['idElecciones'];
    $idEleccionesLocalidad = $_POST['idEleccionesLocalidad'];
    $idEleccionesLocalidadLista = $_POST['idEleccionesLocalidadLista'];
    
    if (isset($_POST['idEleccionesLocalidadListaIntegrante'])){
        $idEleccionesLocalidadListaIntegrante = $_POST['idEleccionesLocalidadListaIntegrante'];
    } else {
        $idEleccionesLocalidadListaIntegrante = NULL;
    }
    $tituloElecciones = "";
    $eleccionesLogic = new elecciones();
    $resElecciones = $eleccionesLogic->obtenerEleccionesPorId($idElecciones);
    if ($resElecciones['estado']) {
        $elecciones = $resElecciones['datos'];
        $tituloElecciones = $elecciones['detalle'];
        
        $eleccionesLocalidadesLogic = new eleccionesLocalidades();
        $resLocalidades = $eleccionesLocalidadesLogic->obtenerEleccionesLocalidadPorId($idEleccionesLocalidad);
        if ($resLocalidades['estado']) {
            $localidad = $resLocalidades['datos'];
            $tituloElecciones = $tituloElecciones.' - Localidad: '.$localidad['localidadDetalle'];
            
            $resLista = $eleccionesLocalidadesListasLogic->obtenerEleccionesLocalidadListaPorId($idEleccionesLocalidadLista);
            if ($resLista['estado']) {
                $lista = $resLista['datos'];
                $tituloElecciones = $tituloElecciones.' - Lista: '.$lista['nombre'];
            } else {
                $mensaje = $resLista['mensaje'];
                $clase = $resLista['clase'];
                $icono = $resLista['icono'];
                $continua = FALSE;
            }
        } else {
            $mensaje = $resLocalidades['mensaje'];
            $clase = $resLocalidades['clase'];
            $icono = $resLocalidades['icono'];
            $continua = FALSE;
        }
    } else {
        $mensaje = $resElecciones['mensaje'];
        $clase = $resElecciones['clase'];
        $icono = $resElecciones['icono'];
        $continua = FALSE;
    }

    $accion = $_POST['accion'];
    if ($accion == 3){
        $resIntegrante = $eleccionesLocalidadesIntegrantesLogic->obtenerEleccionesLocalidadListaIntegrantesPorId($idEleccionesLocalidadListaIntegrante);
        if ($resIntegrante['estado']){
            $integrante = $resIntegrante['datos'];
            $apellidoNombre = $integrante['apellidoNombre'];
            $orden = $integrante['orden'];
            $cargo = $integrante['cargo'];
            $idColegiado = $integrante['idColegiado'];
            $matricula = $integrante['matricula'];
            $colegiado_buscar = $matricula.' - '.$apellidoNombre;
        } else {
            $continua = FALSE;
        }
        $titulo="Editar Integrante";
        $nombreBoton="Guardar cambios";
    } else {
        $titulo="Nuevo Integrante";
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
        <div class="panel-heading"><h4><b><?php echo $titulo.' para '.$tituloElecciones; ?></b></h4></div>
        <div class="panel-body">
            <form id="formElecciones" name="formElecciones" method="POST" onSubmit="" action="datosElecciones\abm_elecciones_integrantes.php">
                <div class="row">
                    <div class="col-md-6">
                        <b>Matr&iacute;cula / Apellido y Nombre *</b>  
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar; ?>" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" required="" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <div class="col-md-4">
                        <b>Cargo *</b>  
                        <select class="form-control" id="cargo" name="cargo" required="">
                            <option value="T" <?php if($cargo == "T") { echo 'selected'; } ?>>Titular</option>
                            <option value="S" <?php if($cargo == "S") { echo 'selected'; } ?>>Suplente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <b>Orden </b>
                        <input class="form-control" type="number" name="orden" id="orden" value="<?php echo $orden; ?>" />
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
                <input type="hidden" name="idEleccionesLocalidadListaIntegrante" id="idEleccionesLocalidadListaIntegrante" value="<?php echo $idEleccionesLocalidadListaIntegrante; ?>" />
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
        <form  method="POST" action="elecciones_integrantes_lista.php">
            <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
            <input type="hidden" name="idElecciones" id="idElecciones" value="<?php echo $idElecciones; ?>" />
            <input type="hidden" name="idEleccionesLocalidad" id="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>" />
            <input type="hidden" name="idEleccionesLocalidadLista" id="idEleccionesLocalidadLista" value="<?php echo $idEleccionesLocalidadLista; ?>" />
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