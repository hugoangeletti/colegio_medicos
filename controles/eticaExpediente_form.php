<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/eticaExpedienteLogic.php');
$eticaExpedienteLogic = new eticaExpedienteLogic();
require_once ('../dataAccess/sumarianteLogic.php');
$sumarianteLogic = new sumarianteLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/secretarioadhocLogic.php');
$secretarioadhocLogic = new secretarioadhocLogic();

$accion = $_POST['accion'];
if ($accion == 4) {
    $idSumariante = $_POST['idSumariante'];
    $tipoSumariante = $_POST['tipoSumariante'];
    $estadoExpediente = NULL;
} else {
    $estadoExpediente = $_POST['estadoExpediente'];
    $idSumariante = NULL;
    $tipoSumariante = NULL;
}
$idEticaExpediente = NULL;
$continua = TRUE;

$idColegiado = NULL;
$nroExpediente = "";
$caratula = "";
$observaciones = "";
$colegiadoBuscar = NULL;
$idSumarianteTitular = NULL;
$sumarianteTitularBuscar = NULL;
$idSumarianteSuplente = NULL;
$sumarianteSuplenteBuscar = NULL;
$idSecretarioadhoc = NULL;
$secretarioadhoc = NULL;
$denunciante = NULL;
$fechaReunionConsejo = NULL;

if (isset($_POST['idEticaExpediente'])){
    $idEticaExpediente = $_POST['idEticaExpediente'];
    $resEticaExpediente = $eticaExpedienteLogic->obtenerEticaExpedientePorId($idEticaExpediente);
    if ($resEticaExpediente['estado']){
        $datos = $resEticaExpediente['datos'];
        $idEticaExpediente = $datos['idEticaExpediente'];
        $idColegiado = $datos['idColegiado'];
        $denunciante = $datos['denunciante'];
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado']) {
            $colegiado = $resColegiado['datos'];
            $colegiadoBuscar = $colegiado['matricula'].' - '.trim($colegiado['apellido'])." ".trim($colegiado['nombre'])." (DNI ".$colegiado['numeroDocumento'].")";
        } else {
            $colegiadoBuscar = NULL;
        }
        $nroExpediente = $datos['nroExpediente'];
        $fechaReunionConsejo = $datos['fechaReunionConsejo'];
        $caratula = $datos['caratula'];
        $observaciones = $datos['observaciones'];
        $idSumarianteTitular = $datos['idSumarianteTitular'];
        if ($idSumarianteTitular){
            $resSumariante = $sumarianteLogic->obtenerSumarianteBuscar($idSumarianteTitular);
            $sumarianteTitularBuscar = $resSumariante['sumarianteBuscar'];
        }
        $idSumarianteSuplente = $datos['idSumarianteSuplente'];
        if ($idSumarianteSuplente){
            $resSumariante = $sumarianteLogic->obtenerSumarianteBuscar($idSumarianteSuplente);
            $sumarianteSuplenteBuscar = $resSumariante['sumarianteBuscar'];
        }
        $idSecretarioadhoc = $datos['idSecretarioadhoc'];
        if ($idSecretarioadhoc){
            $resSecretario = $secretarioadhocLogic->obtenerSecretarioadhocBuscar($idSecretarioadhoc);
            $secretarioadhoc = $resSecretario['nombre'];
        }

        $titulo="Editar Expediente";
        $nombreBoton="Editar Expediente";
    } else {
        //error al buscar expediente
        $continua = FALSE;
    }
} else {
    $titulo="Nuevo Expediente";
    $nombreBoton="Guardar Expediente";
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
        $idColegiado = $_POST['idColegiado'];
        $denunciante = $_POST['denunciante'];
        $nroExpediente = $_POST['nroExpediente'];
        $fechaReunionConsejo = $_POST['fechaReunionConsejo'];
        $caratula = $_POST['caratula'];
        $observaciones = $_POST['observaciones'];
        $colegiadoBuscar = $_POST['colegiado_buscar'];
        if (isset($_POST['idSumarianteTitular'])){
            $idSumarianteTitular = $_POST['idSumarianteTitular'];
            $sumarianteTitularBuscar = $_POST['sumarianteTitular'];
        }
        if (isset($_POST['idSumarianteSuplente'])){
            $idSumarianteSuplente = $_POST['idSumarianteSuplente'];
            $sumarianteSuplenteBuscar = $_POST['sumarianteSuplente'];
        }
        if (isset($_POST['idSecretarioadhoc'])){
            $idSecretarioadhoc = $_POST['idSecretarioadhoc'];
            $secretarioadhoc = $_POST['secretarioadhoc'];
        }
    }   
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
        <div class="panel-heading"><h4><b><?php echo $titulo; ?></b></h4></div>
        <div class="panel-body"> 
            <form id="formBeneficio" name="formExpediente" method="POST" onSubmit="" action="datosEticaExpediente\abm_eticaExpediente.php">
                <div class="row">
                    <div class="col-md-4">
                        <b>Denunciado *</b>  
                        <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiao" value="<?php echo $colegiadoBuscar; ?>" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado ?>" required="" />
                    </div>
                    <div class="col-md-4">
                        <b>Denunciante *</b>  
                        <input class="form-control" autocomplete="OFF" type="text" name="denunciante" id="denunciante" placeholder="Ingrese nombre del denunciante" value="<?php echo $denunciante; ?>" />
                    </div>
                    <div class="col-md-2">
                        <b>N&ordm; de Expediente *</b>  
                        <input type="text" class="form-control" id="nroExpediente" name="nroExpediente" value="<?php echo $nroExpediente; ?>" placeholder="Número de Expediente" required="">
                    </div>
                    <div class="col-md-2">
                        <b>Fecha Reuni&oacute;n *</b>  
                        <input type="date" class="form-control" id="fechaReunionConsejo" name="fechaReunionConsejo" value="<?php echo $fechaReunionConsejo; ?>" placeholder="dd/mm/aaaa" required="">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <b>Car&aacute;tula *</b>  
                        <input type="text" class="form-control" id="caratula" name="caratula" value="<?php echo $caratula; ?>" placeholder="Carátula del Expediente" required="">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-2">
                        <b>Estado del expediente *</b>
                        <br>
                        <div>
                            <label class="radio-inline">
                            <input type="radio" name="estadoExpediente" id="S" value="S" checked required>Sumario</label>
                        </div>
                        <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
                        <div>
                            <label class="radio-inline">
                            <input type="radio" name="estadoExpediente" id="A" value="A" >Archivado</label>
                        </div>
                    </div>
                    <div class="col-md-10" id="sumariantes">
                        <div class="col-md-6">
                            <b>Sumariante Titular</b>  
                            <input type="text" autocomplete="OFF" class="form-control" id="sumarianteTitular" name="sumarianteTitular" value="<?php echo $sumarianteTitularBuscar; ?>" placeholder="Sumariante titular">
                            <input type="hidden" name="idSumarianteTitular" id="idSumarianteTitular" value="<?php echo $idSumarianteTitular ?>" />
                        </div>
                        <div class="col-md-6">
                            <b>Sumariante Suplente</b>  
                            <input type="text" autocomplete="OFF" class="form-control" id="sumarianteSuplente" name="sumarianteSuplente" value="<?php echo $sumarianteSuplenteBuscar; ?>" placeholder="Sumariante suplente">
                            <input type="hidden" name="idSumarianteSuplente" id="idSumarianteSuplente" value="<?php echo $idSumarianteSuplente ?>" />
                        </div>
                        <div class="col-md-6">
                            <b>Secretario ad-hoc</b>  
                            <input type="text" autocomplete="OFF" class="form-control" id="secretarioadhoc" name="secretarioadhoc" value="<?php echo $secretarioadhoc; ?>" placeholder="Secretario ad-hoc">
                            <input type="hidden" name="idSecretarioadhoc" id="idSecretarioadhoc" value="<?php echo $idSecretarioadhoc ?>" />
                        </div>
                    </div>            
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <b>Observaciones</b>
                        <textarea class="form-control" rows="5" id="observaciones" name="observaciones" ><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <?php
                if ($accion != 4){
                ?>
                    <div class="row">
                         <div style="text-align:center">
                             <button type="submit"  class="btn btn-success " ><?php echo $nombreBoton; ?></button>
                         </div>
                    </div>  

                    <input type="hidden" name="idEticaExpediente" id="idEticaExpediente" value="<?php echo $idEticaExpediente; ?>" />
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                <?php
                }
                ?>
         </form>   
        <!-- BOTON VOLVER -->    
        <div class="col-md-12" style="text-align:right;">
            <?php if ($accion == 4) {
                ?>
                <form  method="POST" action="sumariante_expedientes.php">
                    <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                    <input type="hidden" name="idSumariante" id="idSumariante" value="<?php echo $idSumariante; ?>" />
                    <input type="hidden" name="tipoSumariante" id="tipoSumariante" value="<?php echo $tipoSumariante; ?>" />
                </form>
            <?php
            } else {?>
                <form  method="POST" action="eticaExpediente_lista.php">
                    <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
                    <input type="hidden" name="estadoExpediente" id="estadoExpediente" value="<?php echo $estadoExpediente; ?>" />
                </form>
            <?php
            }
            ?>
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
    
    $(function(){
        var nameIdMap = {};
        $('#sumarianteTitular').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'sumariante.php',
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
                $('#idSumarianteTitular').val(nameIdMap[item]);
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
    
    $(function(){
        var nameIdMap = {};
        $('#sumarianteSuplente').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'sumariante.php',
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
                $('#idSumarianteSuplente').val(nameIdMap[item]);
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
    
    $(function(){
        var nameIdMap = {};
        $('#secretarioadhoc').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'secretarioadhoc.php',
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
                $('#idSecretarioadhoc').val(nameIdMap[item]);
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
    
    $(document).ready(function() {
    $("input[type=radio]").click(function(event){
        var valor = $(event.target).val();
        if(valor =="S"){
            $("#sumariantes").show();
        } else if (valor == "A") {
            $("#sumariantes").hide();
        } else { 
            // Otra cosa
        }
    });
});
</script>