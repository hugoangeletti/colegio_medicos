<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/agremiacionesDebitoLogic.php');
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

$continua = TRUE;
$mensaje = "";
if (isset($_GET['idLugarPago']) && $_GET['idLugarPago'] <> "") {
    $idLugarPago = $_GET['idLugarPago'];
    $resLugarPago = $lugarPagoLogic->obtenerLugarPagoPorId($idLugarPago);
    if ($resLugarPago['estado']) {
        $lugarPago = $resLugarPago['datos']['nombre'];
    } else {
        $continua = FALSE;
        $mensaje .= "Error al obtener LugarPago - ";        
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idLugarPago - ";
}
if (isset($_GET['periodo']) && $_GET['periodo'] <> "") {
    $periodoSeleccionado = $_GET['periodo'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta periodo - ";
}

if ($continua) {
    if (isset($_GET['idColegiado'])) {
        $idColegiado = $_GET['idColegiado'];
    } else {
        if (isset($_POST['idColegiado'])) {
            $idColegiado = $_POST['idColegiado'];
        } else {
            $idColegiado = NULL;
        }
    }

    if (isset($idColegiado)) {
        $periodoActual = $_SESSION['periodoActual'];
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];

            $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, PERIODO_ACTUAL);
            if ($resEstadoTeso['estado']){
                $codigo = $resEstadoTeso['codigoDeudor'];
                $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
                if ($resEstadoTesoreria['estado']){
                    $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                } else {
                    $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                }
            } else {
                $estadoTesoreria = $resEstadoTeso['mensaje'];
            }

        }
        
        if (isset($_POST['mensaje'])) {
        ?>
            <div class="ocultarMensaje"> 
                <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
            </div>
         <?php
        }
        ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-9">
                        <h4>Adherir Matricula al Débito por Agremiaciones. (<?php echo $lugarPago; ?>)</h4>
                    </div>
                    <div class="col-md-2 text-left">
                        <a href="debito_agremiaciones_form.php?idLugarPago=<?php echo $idLugarPago;?>&periodo=<?php echo $periodoSeleccionado; ?>" class="btn btn-primary" >Cambiar matricula</a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
            <div class="row">
                <div class="col-md-1">
                    <label>Matrícula: </label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['matricula']; ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <label>Apellido y Nombres: </label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <label>Estado matricular: </label>
                    <input class="form-control" type="text" value="<?php echo $colegiado['detalleMovimiento']; ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <label>Estado con Tesorería: </label>
                    <input class="form-control" type="text" value="<?php echo $estadoTesoreria; ?>" readonly=""/>
                </div>
                <div class="col-md-2">
                    <br>
                    <form id="datosDebitoAgremiacion" name="datosDebitoAgremiacion" method="POST" action="datosDebitoAgremiacion\abm_debito_agremiaciones.php">
                        <button type="submit" name='confirma' id='confirma' class="btn btn-success" onclick="show('confirma', 'informe')">Confirma Matrícula</button>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>" />
                        <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>" />
                    </form>
                </div>
            </div>
        </div>
    <?php
    } else {
        //debe seleccionar al colegiado    
    ?>
        <div class="row">
            <div class="col-md-12 text-center">
                <h4>Adherir Matriculado al Débito por Agremiación. (<?php echo $lugarPago; ?>)</h4>
                <h5>Seleccione al colegiado/a</h5>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="debito_agremiaciones_form.php?idLugarPago=<?php echo $idLugarPago;?>&periodo=<?php echo $periodoSeleccionado;?>">
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
                        <input type="hidden" name="origen" id="origen" value="nuevo" />
                    </div>
                </div>
            </form>
        </div>
    <?php
    }
    ?>
    <div class="row">&nbsp;</div>
    <div class="row text-center">
        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="debito_agremiaciones.php">
            <button type="submit"  class="btn btn-info" >Volver al listado</button>
            <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>" />
            <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>" />
        </form>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <a href="debito_agremiaciones.php" class="btn btn-primary" >Volver</a>
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