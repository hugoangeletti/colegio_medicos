<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/sumarianteLogic.php');
$sumarianteLogic = new sumarianteLogic();

$accion = $_POST['accion'];
if (isset($_POST['idSumariante'])){
    $idSumariante = $_POST['idSumariante'];
} else {
    $idSumariante = NULL;
}
$continua = TRUE;
$idColegiado = NULL;
$colegiadoBuscar = NULL;
$estado = "A";

if ($accion == 3){
    $resSumariante = $sumarianteLogic->obtenerSumariantePorId($idSumariante);
    if ($resSumariante['estado']){
        $sumariante = $resSumariante['datos'];
        $idSumariante = $sumariante['idSumariante'];
        $colegiadoBuscar = $sumariante['sumarianteBuscar'];
        $idColegiado = $sumariante['idColegiado'];
        $estado = $sumariante['estado'];
    } else {
        $continua = FALSE;
    }
    $titulo="Editar Sumariante";
    $nombreBoton="Guardar cambios";
} else {
    $titulo="Nuevo Sumariante";
    $nombreBoton="Guardar Sumariante";
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
        $idSumariante = $_POST['idSumariante'];
        $idColegiado = $_POST['idColegiado'];
        $colegiadoBuscar = $_POST['sumarianteBuscar'];
        $estado = $_POST['estado'];
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body">
            <form id="formExpediente" name="formExpediente" method="POST" onSubmit="" action="datosSumariante\abm_sumariante.php">
                <div class="row">
                    <div class="col-md-7">
                        <b>Colegiado *</b>  
                        <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" value="<?php echo $colegiadoBuscar; ?>" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado ?>" required="" />
                    </div>
                    <div class="col-md-5">
                        <b>Estado *</b>  
                        <select class="form-control" id="estado" name="estado" required="">
                            <option value="A" <?php if($estado == "A") { echo 'selected'; } ?>>Activo</option>
                            <option value="B" <?php if($estado == "B") { echo 'selected'; } ?>>Anulado</option>
                        </select>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                     <div style="text-align:center">
                         <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                     </div>
                </div>  

                <input type="hidden" name="idSumariante" id="idSumariante" value="<?php echo $idSumariante; ?>" />
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <form  method="POST" action="sumariante_lista.php">
                <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
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