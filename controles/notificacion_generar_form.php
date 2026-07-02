<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/notificacionLogic.php');
require_once ('../dataAccess/deudoresLogic.php');

$continuar = TRUE;
$mensaje = "";

//inicializamos los datos del envio
$idNotificacionNota = 1; //notificacion de deuda de colegiacion
if (isset($_GET['idEnvioDiario']) && $_GET['idEnvioDiario'] <> "") {
    $idNotificacionNota = $_GET['idEnvioDiario'];
} 
$idColegiado = NULL;
$fechaVencimiento = ultmioDiaDelMes(date('Y-m-d'));
$filtroDeudores = 'T'; //todos los deudores
$filtroDeudoresNombre = 'Todos los deudores';
$tipoEnvio = 'A'; //tipo de envio por mail y por correo
$tipoEnvioNombre = 'Tipo de envío por mail y por correo';
$periodoDesde = 0; //periodo desde el inicio

if (isset($_GET['idDeudores']) && $_GET['idDeudores'] <> "") {
    $idDeudores = $_GET['idDeudores'];
    $deudoresLogic = new deudoresLogic();
    $resDeudores = $deudoresLogic->obtenerListadoDeudoresPorId($idDeudores);
    if ($resDeudores['estado']) {
        $deudores = $resDeudores['datos'];
        $periodoHasta = $deudores['periodo_limite'];
        $cuotasAdeudadas = $deudores['cuotas_adeudadas'];
    } else {
        $continuar = FALSE;
        $mensaje .= $resDeudores['mensaje'];
    }
} else {
    $idDeudores = NULL;
    $periodoHasta = PERIODO_ACTUAL; //periodo hasta el actual
}
?>
<div class="col-md-12 alert alert-info">
    <div class="row">
        <div class="col-md-9">
            <h4>Generar Notificaciones de deuda de colegiaciòn</h4>
        </div>
        <div class="col-md-3 text-left">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="notificacion_lista.php">
                <button type="submit"  class="btn btn-info" >Volver</button>
            </form>
        </div>
    </div>
</div>
<?php 
if ($continuar) {
?>
    <div class="panel panel-info">
        <div class="panel-body">
            <form class="form-control" id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosNotificacion/notificacion_generar.php" >
                <div class="row">
                    <!--
                    <div class="col-md-2">
                        <label>Tipo de notificación: &nbsp;</label>
                        <input type="text" class="form-control" name="idNotificacionNota" id="idNotificacionNota" value="<?php echo $idNotificacionNota; ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label>Filtro deudores: &nbsp;</label>
                        <input type="text" class="form-control" name="filtroDeudores" id="filtroDeudores" value="<?php echo $filtroDeudores; ?>" readonly>
                    </div>
                    <div class="col-md-2">
                        <label>Período desde: &nbsp;</label>
                        <input type="number" class="form-control" name="periodoDesde" id="periodoDesde" value="<?php echo $periodoDesde; ?>">
                    </div>
                    -->
                    <div class="col-md-2">
                        <label for="periodoHasta">Período hasta: *</label>
                        <input type="number" class="form-control" name="periodoHasta" id="periodoHasta" value="<?php echo $periodoHasta; ?>" <?php if (isset($idDeudores)) { echo 'readonly';}?>>
                    </div>
                    <div class="col-md-3">
                        <label for="cuotasAdeudadas">Cantidad mínima de cuotas adeudadas: *</label>
                        <input type="number" class="form-control" name="cuotasAdeudadas" id="cuotasAdeudadas" value="<?php echo $cuotasAdeudadas; ?>" <?php if (isset($idDeudores)) { echo 'readonly';}?>>
                    </div>
                    <div class="col-md-2">
                        <label for="fechaVencimiento">Fecha vencimiento: &nbsp;</label>
                        <input type="date" class="form-control" name="fechaVencimiento" id="fechaVencimiento" value="<?php echo $fechaVencimiento; ?>">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-default">Confirma notificación</button>
                        <input type="hidden" name="idNotificacionNota" id="idNotificacionNota" value="<?php echo $idNotificacionNota; ?>" />
                        <?php 
                        if (isset($idColegiado)) {
                        ?>
                            <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        <?php 
                        }
                        if (isset($idDeudores)) {
                        ?>
                            <input type="hidden" name="idDeudores" id="idDeudores" value="<?php echo $idDeudores; ?>" />
                        <?php 
                        }
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="alert alert-warning">ACCESO INCORRECTO</div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <?php 
        $link_volver = "notificaciones.php";
        if (isset($idDeudores)) {
            $link_volver = "deudores_listado.php";
        }
        ?>
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="<?php echo $link_volver; ?>">
            <button type="submit"  class="btn btn-info" >Volver</button>
        </form>
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
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php?activos=SI',
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