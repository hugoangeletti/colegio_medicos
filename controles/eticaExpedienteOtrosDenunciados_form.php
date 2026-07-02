<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    if (isset($_POST['idColegiado'])) {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
}
if (isset($_POST['idEticaExpediente']) && $_POST['idEticaExpediente']) {
    $idEticaExpediente = $_POST['idEticaExpediente'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idEticaExpediente";
}

if ($continua) {
    $resExpediente = $eticaExpedienteLogic->obtenerEticaExpedientePorId($idEticaExpediente);
    if ($resExpediente['estado']) {
        $expediente = $resExpediente['datos'];
    ?>
        <div class="row">
            <div class="col-md-12">Expediente: <b><?php echo $expediente['caratula']; ?></b></div>
        </div>     
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 text-center">
                <h5>Seleccione al colegiado/a</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosEticaExpediente/abm_eticaExpedienteOtrosDenunciados.php">
                <div class="row">
                    <div class="col-md-3" style="text-align: right;">
                        <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    </div>
                    <div class="col-md-7">
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                    </div>
                    <div class="col-md-2">
                        <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                        <input type="hidden" name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>" />
                    </div>
                </div>
            </form>
        </div>
        <div class="row">&nbsp;</div>
    <?php 
    } else {
        $mensaje .= $resExpediente['mensaje'];
    ?>
        <div class="row">
            <div class="col-md-12">Error: <?php echo $mensaje; ?></div>
        </div>
    <?php 
    }
}
?>
<div class="row text-center">
    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="eticaExpedienteOtrosDenunciados.php?id=<?php echo $idEticaExpediente; ?>">
        <button type="submit"  class="btn btn-info" >Volver </button>
    </form>
</div>
<?php
require_once '../html/footer.php';
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