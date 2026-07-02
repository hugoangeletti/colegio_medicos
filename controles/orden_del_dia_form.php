<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/ordenDelDiaLogic.php');
$ordenDelDiaLogic = new ordenDelDiaLogic();

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
$continua = TRUE;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idOrdenDia = $_GET['id'];
    $accion = 3;
} else {
    if (isset($_POST['accion']) && $_POST['accion'] == 1) {
        $accion = 1;
        $idOrdenDia = NULL;
    } else {
        $continua = FALSE;
        $mensaje = "PARAMETROS INVALIDOS";
    }
}

if ($continua) {
    $fecha = date('Y-m-d');
    $periodo = date('Y');
    $numero = $ordenDelDiaLogic->obtenerNumeroOrdenDelDia($periodo);
    $fechaDesde = NULL;
    $fechaHasta = NULL;
    $observaciones = NULL;
    if (isset($idOrdenDia)) {
        $resOrden = $ordenDelDiaLogic->obtenerOrdenDelDiaPorId($idOrdenDia);
        if ($resOrden['estado']) {
            $ordenDelDia = $resOrden['datos'];
            $fecha = $ordenDelDia['fecha'];
            $periodo = $ordenDelDia['periodo'];
            $numero = $ordenDelDia['numero'];
            $fechaDesde = $ordenDelDia['fechaDesde'];
            $fechaHasta = $ordenDelDia['fechaHasta'];
            $observaciones = $ordenDelDia['observaciones'];
        }
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3>Orden del día &nbsp;<?php if (isset($idOrdenDia)) { echo 'N° '.$numero; } ?></h3>
        </div>
        <div class="row">&nbsp;</div>

        <div class="panel-body"> 
            <form id="formOrdeDia" name="formDistrito" method="POST" onSubmit="" action="datosOrdenDelDia/abm_orden_del_dia.php">
                <div class="row">
                    <div class="col-md-2">
                        <label>Fecha * </label>
                        <input type="date" class="form-control" name="fecha" id="fecha" value="<?php echo $fecha; ?>" required="" />
                    </div>
                    <div class="col-md-2">
                        <label>Período *</label>
                        <input type="number" class="form-control" name="periodo" id="periodo" value="<?php echo $periodo; ?>" readonly />
                    </div>
                    <div class="col-md-2">
                        <label>Número *</label>
                        <input type="number" class="form-control" name="numero" id="numero" value="<?php echo $numero; ?>" readonly />
                    </div>
                    <div class="col-md-2">
                        <label>Fecha Desde * </label>
                        <input type="date" class="form-control" autofocus="" name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde; ?>" required="" />
                    </div>
                    <div class="col-md-2">
                        <label>Fecha Hasta * </label>
                        <input type="date" class="form-control" name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta; ?>" required="" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>

                <!--<div class="row">
                    <div class="col-md-10"> 
                        <label>Observaciones </label>
                        <textarea class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" name="observaciones" id="observaciones" rows="5" ><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>-->

                <div class="row">
                    <div class="col-md-12 text-center">
                        <label>&nbsp;</label>
                        <button type="submit"  class="btn btn-info" >Confirmar</button>
                        <input class="form-control" type="hidden" name="idOrdenDia" id="idOrdenDia" value="<?php echo $idOrdenDia; ?>" />
                        <input class="form-control" type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    </div>
                </div>
            </form>        

            <div class="row">&nbsp;</div>
        </div>
    </div>
    <?php 
} else {
    echo 'datos mal ingresados<br>';
}
?>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12">
        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="orden_del_dia_listado.php">
            <button type="submit"  class="btn btn-info" >Volver</button>
        </form>
    </div>
</div>
<?php 
require_once '../html/footer.php';
