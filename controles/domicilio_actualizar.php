<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../dataAccess/localidadLogic.php');

if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $origenForm = $_GET['ori'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        //include 'menuColegiado.php';
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
       <div class="ocultarMensaje"> 
           <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
       </div>
     <?php
        $calle = $_POST['calle'];
        $numero = $_POST['numero'];
        $lateral = $_POST['lateral'];
        $piso = $_POST['piso'];
        $depto = $_POST['depto'];
        $idLocalidad = $_POST['idLocalidad'];
        $localidad = $_POST['localidad_buscar'];
        $codigoPostal = $_POST['codigoPostal'];
    } else {
        $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
        if ($resDomicilio['estado']) {
            $domicilio = $resDomicilio['datos'];
            $calle = $domicilio['calle'];
            $numero = $domicilio['numero'];
            $lateral = $domicilio['lateral'];
            $piso = $domicilio['piso'];
            $depto = $domicilio['depto'];
            $idLocalidad = $domicilio['idLocalidad'];
            $localidad = $domicilio['nombreLocalidad'];
            $codigoPostal = $domicilio['codigoPostal'];
        } else {
            $calle = '';
            $numero = '';
            $lateral = '';
            $piso = '';
            $depto = '';
            $idLocalidad = '';
            $localidad = '';
            $codigoPostal = '';
        }
    }
    ?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Domicilio actual del colegiado</h4>
            </div>
            <?php 
            if ($origenForm == 'consulta') {
            ?>
                <div class="col-md-3 text-left">
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                    </form>
                </div>
            <?php
            } else {
            ?>
                <div class="col-md-3 text-left">
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_domicilio.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                    </form>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="panel-body">
    <div class="row">
        <div class="col-md-2">
            <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
        </div>
        <div class="col-md-4">
            <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
        </div>
        <div class="col-md-6">&nbsp;</div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center"><h4><b>Actualizar domicilio del colegiado</b></h4></div>
    </div>

    <form id="datosDomicilio" autocomplete="off" name="datosDomicilio" method="POST" onSubmit="" action="datosColegiadoDomicilio\actualiza_domicilio.php">
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Calle *</label>
                <input class="form-control" type="text" id="calle" name="calle" value="<?php echo $calle; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>N&uacute;mero *</label>
                <input class="form-control" type="text" id="numero" name="numero" value="<?php echo $numero; ?>" required=""/>
            </div>
            <div class="col-md-2">
                <label>Piso</label>
                <input class="form-control" type="text" id="piso" name="piso" value="<?php echo $piso; ?>" />
            </div>
            <div class="col-md-2">
                <label>Departamento</label>
                <input class="form-control" type="text" id="depto" name="depto" value="<?php echo $depto; ?>" />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Lateral </label>
                <input class="form-control" type="text" id="lateral" name="lateral" value="<?php echo $lateral; ?>" />
            </div>
            <div class="col-md-4">
                <label>Localidad *</label>
                <input class="form-control" autocomplete="OFF" type="text" name="localidad_buscar" id="localidad_buscar" placeholder="Ingrese Localidad" value="<?php echo $localidad; ?>" required=""/>
                <input type="hidden" name="idLocalidad" id="idLocalidad" value="<?php echo $idLocalidad; ?>" required="" />
            </div>
            <div class="col-md-2">
                <label>C&oacute;digo Postal *</label>
                <input class="form-control" type="text" name="codigoPostal" id="codigoPostal" value="<?php echo $codigoPostal; ?>" required="" />
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="text-center">
                <button type="submit"  class="btn btn-success " >Confirma actualizaci&oacute;n</button>
                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                <input type="hidden" name="origenForm" id="origenForm" value="<?php echo $origenForm; ?>" />
            </div>
        </div>    
    </form>
    </div>    
</div>
<?php
}
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#localidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'localidad.php',
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
                $('#idLocalidad').val(nameIdMap[item]);
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