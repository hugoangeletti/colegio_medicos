<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/informesLogic.php');

$continua = TRUE;
$mensaje = "";
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Listado de matriculados por Universidad</h4>
            </div>
            <div class="col-md-3 text-left">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $tipoFiltro = 'COMPLETO';
        $fechaDesde = NULL;
        $fechaHasta = date('Y-m-d');
        if ($continua) {
        ?>
            <form id="datosListado" autocomplete="off" name="datosListado" method="POST" target="_BLANK" action="matriculados_por_universidad_listado.php">
                <div class="row">
                    <div class="col-md-4">
                        <label for="tipoListado">Tipo listado: * </label>
                        <br>
                        <label class="radio-inline">
                            <input type="radio" name="tipoListado" id="tipoListado" value="POR_UNIVERSIDAD" >Por Universidad
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="tipoListado" id="tipoListado" value="COMPLETO" >Completo
                        </label>
                    </div>
                    <div class="col-md-3">
                        <label for="fechaDesde">Fecha matriculación desde:  *</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" value="<?php echo $fechaDesde;?>" required="" >
                    </div>
                    <div class="col-md-2">
                        <label for="fechaDesde">Fecha matriculación hasta:  *</label>
                        <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" value="<?php echo $fechaHasta;?>" required="" >
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit"  class="btn btn-info" >Confirma listado</button>
                    </div>
                </div>    
            </form>
        <?php
        }
        ?>
    </div>    
</div>
<?php
require_once '../html/footer.php';
