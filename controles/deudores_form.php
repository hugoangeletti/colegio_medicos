<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/deudoresLogic.php');

$continuar = TRUE;
$mensaje = "";

if (isset($_POST['mensaje']) && $_POST['mensaje'] <> "OK") {
    $tipo_filtro = $_POST['tipoFiltro'];
    $periodo_hasta = $_POST['periodoHasta'];
    $cuotas_adeudadas = $_POST['cuotasAdeudadas'];
} else {
    $tipo_filtro = '1';
    $periodo_hasta = PERIODO_ACTUAL;
    $cuotas_adeudadas = 6;
}
?>
<div class="col-md-12 alert alert-info">
    <div class="row">
        <div class="col-md-9">
            <h4>Generar Listado de deudores de colegiaciòn</h4>
        </div>
        <div class="col-md-3 text-left">
            <a href="deudores_listado.php" class="btn btn-info" >Volver</a>
        </div>
    </div>
</div>
<?php 
if ($continuar) {
?>
    <div class="panel panel-info">
        <div class="panel-body">
            <form class="form-control" id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosDeudores/generar_deudores.php" >
                <div class="row">
                    <div class="col-md-2">
                        <label>Filtro deudores: *</label>
                        <div class="radio">
                            <label><input type="radio" name="tipo_filtro" checked="<?php if ($tipo_filtro == '1') { echo 'checked'; } ?>" value="1">Todos los deudores </label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="tipo_filtro" checked="<?php if ($tipo_filtro == '2') { echo 'checked'; } ?>" value="2">Con 2 o más períodos adeudados; Incluye Período Actual (<?php echo PERIODO_ACTUAL; ?>)</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="tipo_filtro" checked="<?php if ($tipo_filtro == '3') { echo 'checked'; } ?>" value="3">Con 2 o más períodos adeudados; NO Incluye Período Actual (<?php echo PERIODO_ACTUAL; ?>)</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="tipo_filtro" checked="<?php if ($tipo_filtro == '4') { echo 'checked'; } ?>" value="4">Con 1 período adeudado y cantidad de cuotas seleccionadas</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="tipo_filtro" checked="<?php if ($tipo_filtro == '5') { echo 'checked'; } ?>" value="5">Con 2 o menos períodos adeudados y cantidad de cuotas seleccionadas</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" name="tipo_filtro" checked="<?php if ($tipo_filtro == '6') { echo 'checked'; } ?>" value="6">Más de 2 períodos adeudados; NO Incluye Período Actual (<?php echo PERIODO_ACTUAL; ?>)</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="periodoHasta">Período hasta: *</label>
                        <input type="number" class="form-control" name="periodoHasta" id="periodoHasta" value="<?php echo $periodo_hasta; ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="cuotasAdeudadas">Cantidad Mínima de cuotas adeudadas: *</label>
                        <input type="number" class="form-control" name="cuotasAdeudadas" id="cuotasAdeudadas" value="<?php echo $cuotas_adeudadas; ?>">
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-default">Confirma proceso</button>
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
<?php      
}
require_once '../html/footer.php';
