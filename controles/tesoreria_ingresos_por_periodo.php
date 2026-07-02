<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/tesoreriaLogic.php');
$tesoreriaLogic = new tesoreriaLogic();
if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
    $periodo = $_POST['periodo'];
}
if (isset($_POST['mes']) && $_POST['mes'] <> "") {
    $mes = $_POST['mes'];
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-12">
                <h4>Ingresos por Agremiaciones, Medios de Pago y Caja</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['mensaje']) && $_POST['mensaje'] == "OK") {
        ?>
           <div class="row">
                <div class="col-md-6 text-left"><h4>El proceso ha finalizado <b><?php echo $_POST['mensaje'] ?></b></h4></div>
            </div>
         <?php
        } else {
        /*
        <div class="row">
            <div class="col-md-12 text-center"><h4><b>Ingresos por Agremiaciones, Medios de Pago y Caja</b></h4></div>
        </div>
        */
        ?>
        <form id="reporteIngresos" autocomplete="off" name="reporteIngresos" method="POST" action="tesoreria_ingresos_por_periodo.php">
            <div class="row">
                <div class="col-md-2">
                    <label>Período a procesar: </label>
                    <select class="form-control" id="periodo" name="periodo" required>
                        <?php 
                        $periodoAnterior = PERIODO_ACTUAL_CONTABLE - 1;
                        $periodoActual = PERIODO_ACTUAL_CONTABLE;
                        if (!isset($_POST['periodo']) || $_POST['periodo'] == "") {
                            $periodo = $periodoAnterior;
                        }
                        ?>
                        <option value="<?php echo $periodoAnterior; ?>" <?php if($periodo == $periodoAnterior) { echo 'selected'; } ?>><?php echo $periodoAnterior.'/'.($periodoAnterior+1); ?></option>
                        <option value="<?php echo $periodoActual; ?>" 
                            <?php if($periodo == $periodoActual) { echo 'selected'; } ?>><?php echo $periodoActual.'/'.($periodoActual+1); ?></option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Mes de cobranza: </label>
                    <div class="form-check">
                        <input class="form-check-input" name="mes[]" type="checkbox" value="0" id="0" <?php if (isset($mes[0]) && $mes[0] == '0' && count($mes) == 1) { echo 'checked'; } ?>>
                            <label class="form-check-label" for="0">Todos los meses</label>
                        <br>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="05" id="05" <?php if (in_array('05', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="05">Mayo</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="06" id="06" <?php if (in_array('06', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="06">Junio</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="07" id="07" <?php if (in_array('07', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="07">Julio</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="08" id="08" <?php if (in_array('08', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="08">Agosto</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="09" id="09" <?php if (in_array('09', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="09">Septiembre</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="10" id="10" <?php if (in_array('10', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="10">Octubre</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="11" id="11" <?php if (in_array('11', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="11">Noviembre</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="12" id="12" <?php if (in_array('12', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="12">Diciembre</label>
                        <br>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="01" id="01" <?php if (in_array('01', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="01">Enero</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="02" id="02" <?php if (in_array('02', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="02">Febrero</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="03" id="03" <?php if (in_array('03', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="03">Marzo</label>
                        <input class="form-check-input" name="mes[]" type="checkbox" value="04" id="04" <?php if (in_array('04', $mes)) { echo 'checked'; } ?>>
                        <label class="form-check-label" for="04">Abril</label>
                        <?php
                        /* 
                        $i = 1;
                        while ($i <= 12) {
                            $mesSeleccionar = rellenarCeros($i, 2);
                            ?>
                            <input class="form-check-input" name="mes[]" type="checkbox" value="<?php echo $mesSeleccionar; ?>" id="<?php echo $mesSeleccionar ?>" <?php if (in_array($mesSeleccionar, $mes)) { echo 'checked'; } ?>>
                            <label class="form-check-label" for="<?php echo $mesSeleccionar ?>">
                              <?php echo obtenerMes($i); ?>
                            </label>
                            <?php
                            $i++;
                        }
                        */
                        ?>
                    </div>
                </div>
                <div class="col-md-1 text-center">
                    <br>
                    <button type="submit"  class="btn btn-success" onclick="waitingDialog.show('Generando Reporte...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar reporte </button>
                </div>
            </div>    
        </form>
        <?php
        }
        ?>
    </div>    
    <?php 
    if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
        $periodoProcesar = $_POST['periodo'];
        $mesProcesar = $_POST['mes'];
        $tipoDetalle = 'TOTALIZADO';
        $totalPeriodo = 0;
        $totalPeriodoActual = 0;
        $totalPeriodosAnteriores = 0;
        $totalOtrosConceptos = 0;
        $totalDevoluciones = 0;
    ?> 
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2"> 
                    <h5><b>Por Agremiaciones</b></h5>
                    <?php 
                    $totalAgremiaciones = 0;
                    $resAgremiacion = $tesoreriaLogic->totalPorAgremiaciones($periodoProcesar, $mesProcesar, $tipoDetalle);
                    if ($resAgremiacion['estado']) {
                    ?>
                    <table id="tablaOrdenada" display>
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th style="text-align: right;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resAgremiacion['datos'] as $row) {
                                $periodo = $row['periodo'];
                                $importe = $row['importe'];
                                if ($importe == 0) { continue; }
                                $totalAgremiaciones += $importe;
                                if ($periodo == PERIODO_ACTUAL) {
                                    $totalPeriodoActual += $importe;
                                } else {
                                    $totalPeriodosAnteriores += $importe;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $periodo; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($importe, 2, ',', '.'); ?></td>
                                </tr>
                            <?php 
                            }
                            ?>
                        </tbody>
                    </table>
                    <h5 class="text-right">Total: <b><?php echo number_format($totalAgremiaciones, 2, ',', '.'); ?></b></h5>
                    <?php 
                        $totalPeriodo += $totalAgremiaciones;
                    }
                    ?>
                </div>
                <div class="col-md-3"> 
                    <h5><b>Por Medio de Pago</b></h5>
                    <?php 
                    $totalMediosPago = 0;
                    $resMedios = $tesoreriaLogic->totalPorMediosPago($periodoProcesar, $mesProcesar, $tipoDetalle);
                    if ($resMedios['estado']) {
                    ?>
                    <table id="tablaMedios" display>
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th style="text-align: right;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resMedios['datos'] as $row) {
                                $periodo = $row['periodo'];
                                $periodoDetalle = $row['periodoDetalle'];
                                $importe = $row['importe'];
                                if ($importe == 0) { continue; }
                                $totalMediosPago += $importe;
                                if ($periodo == PERIODO_ACTUAL) {
                                    $totalPeriodoActual += $importe;
                                } else {
                                    $totalPeriodosAnteriores += $importe;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $periodoDetalle; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($importe, 2, ',', '.'); ?></td>
                                </tr>
                            <?php 
                            }
                            ?>
                        </tbody>
                    </table>
                    <h5 class="text-right">Total: <b><?php echo number_format($totalMediosPago, 2, ',', '.'); ?></b></h5>
                    <?php 
                        $totalPeriodo += $totalMediosPago;
                    }
                    ?>
                </div>
                <div class="col-md-3"> 
                    <h5><b>Por Caja del Colegio</b></h5>
                    <?php 
                    $totalCaja = 0;
                    $resCajaDiaria = $tesoreriaLogic->totalPorCaja($periodoProcesar, $mesProcesar, $tipoDetalle);
                    if ($resCajaDiaria['estado']) {
                    ?>
                    <table id="tablaCajas" display>
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th style="text-align: right;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resCajaDiaria['datos'] as $row) {
                                $concepto = $row['concepto'];
                                $idConcepto = $row['idConcepto'];
                                $importe = $row['importe'];
                                if ($importe == 0) { continue; }
                                $totalCaja += $importe;
                                switch ($idConcepto) {
                                    case CONCEPTO_PERIODO_ACTUAL:
                                        $totalPeriodoActual += $importe;
                                        break;
                                    
                                    case CONCEPTO_PERIODOS_ANTERIORES:
                                    case CONCEPTO_PLAN_PAGO:
                                        $totalPeriodosAnteriores += $importe;
                                        break;
                                    
                                    case CONCEPTO_DEVOLUCION:
                                        $totalDevoluciones += $importe;
                                        break;
                                    
                                    default:
                                        $totalOtrosConceptos += $importe;
                                        break;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $concepto; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($importe, 2, ',', '.'); ?></td>
                                </tr>
                            <?php 
                            }
                            ?>
                        </tbody>
                    </table>
                    <h5 class="text-right">Total: <b><?php echo number_format($totalCaja, 2, ',', '.'); ?></b></h5>
                    <?php 
                        $totalPeriodo += $totalCaja;
                    }
                    ?>
                </div>            
                <div class="col-md-3"> 
                    <h5><b>Detalle colegiación períodos anteriores por cajas</b></h5>
                    <?php 
                    $totalDetalle = 0;
                    $resDetalle = $tesoreriaLogic->totalDetalleColegiacion($periodoProcesar, $mesProcesar, $tipoDetalle);
                    if ($resDetalle['estado']) {
                    ?>
                    <table id="tablaDetalle" display>
                        <thead>
                            <tr>
                                <th>Período</th>
                                <th style="text-align: right;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resDetalle['datos'] as $row) {
                                $periodo = $row['periodo'];
                                $importe = $row['importe'];
                                if ($importe == 0) { continue; }
                                $totalDetalle += $importe;
                                ?>
                                <tr>
                                    <td><?php echo $periodo; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($importe, 2, ',', '.'); ?></td>
                                </tr>
                            <?php 
                            }
                            ?>
                        </tbody>
                    </table>
                    <h5 class="text-right">Total: <b><?php echo number_format($totalDetalle, 2, ',', '.'); ?></b></h5>
                    <?php 
                    }
                    ?>
                </div>  
            </div>
            <div class="row">
                <div class="col-md-3">
                    <h4 class="alert alert-info">Período actual: <b><?php echo number_format($totalPeriodoActual, 2, ',', '.'); ?></b></h4>
                </div>
                <div class="col-md-3">
                    <h4 class="alert alert-info">Períodos anteriores: <b><?php echo number_format($totalPeriodosAnteriores, 2, ',', '.'); ?></b></h4>
                </div>
                <div class="col-md-3">
                    <h4 class="alert alert-info">Otros conceptos: <b><?php echo number_format($totalOtrosConceptos, 2, ',', '.'); ?></b></h4>
                </div>
                <div class="col-md-3">
                    <h4 class="alert alert-info">Devoluciones: <b><?php echo number_format($totalDevoluciones, 2, ',', '.'); ?></b></h4>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <h4 class="alert alert-success text-center">Total del período: <b><?php echo number_format($totalPeriodo, 2, ',', '.'); ?></b></h4>
                </div>
                <div class="col-md-4">
                    <h4 class="alert alert-success"><br></h4>
                </div>
            </div>          
        </div>
    <?php
    }
    ?>
</div>
<?php
require_once '../html/footer.php';
?>
<script type="text/javascript"> 
    $(document).ready(function () { $('.dropdown-toggle').dropdown(); }); 
    
    var waitingDialog = waitingDialog || (function ($) {
    'use strict';

    // Creating modal dialog's DOM
    var $dialog = $(
        '<div class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="padding-top:15%; overflow-y:visible;">' +
        '<div class="modal-dialog modal-m">' +
        '<div class="modal-content">' +
            '<div class="modal-header"><h3 style="margin:0;"></h3></div>' +
            '<div class="modal-body">' +
                '<div class="progress progress-striped active" style="margin-bottom:0;"><div class="progress-bar" style="width: 100%"></div></div>' +
            '</div>' +
        '</div></div></div>');

    return {
        /**
         * Opens our dialog
         * @param message Custom message
         * @param options Custom options:
         *                options.dialogSize - bootstrap postfix for dialog size, e.g. "sm", "m";
         *                options.progressType - bootstrap postfix for progress bar type, e.g. "success", "warning".
         */
        show: function (message, options) {
            // Assigning defaults
            if (typeof options === 'undefined') {
                options = {};
            }
            if (typeof message === 'undefined') {
                message = 'Loading';
            }
            var settings = $.extend({
                dialogSize: 'm',
                progressType: '',
                onHide: null // This callback runs after the dialog was hidden
            }, options);

            // Configuring dialog
            $dialog.find('.modal-dialog').attr('class', 'modal-dialog').addClass('modal-' + settings.dialogSize);
            $dialog.find('.progress-bar').attr('class', 'progress-bar');
            if (settings.progressType) {
                $dialog.find('.progress-bar').addClass('progress-bar-' + settings.progressType);
            }
            $dialog.find('h3').text(message);
            // Adding callbacks
            if (typeof settings.onHide === 'function') {
                $dialog.off('hidden.bs.modal').on('hidden.bs.modal', function (e) {
                    settings.onHide.call($dialog);
                });
            }
            // Opening dialog
            $dialog.modal();
        },
        /**
         * Closes dialog
         */
        hide: function () {
            $dialog.modal('hide');
        }
    };

})(jQuery);
    
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang",
            },
            "bLengthChange": false,
            "bFilter": false,
            "bPaginate": false,
            "bInfo": false,
            //dom: 'T<"clear">lfrtip',
        });
    });              

    $(document).ready(function () {
        $('#tablaMedios').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang",
            },
            "bLengthChange": false,
            "bFilter": false,
            "bPaginate": false,
            "bInfo": false,
            //dom: 'T<"clear">lfrtip',
        });
    });              

    $(document).ready(function () {
        $('#tablaCajas').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang",
            },
            "bLengthChange": false,
            "bFilter": false,
            "bPaginate": false,
            "bInfo": false,
            //dom: 'T<"clear">lfrtip',
        });
    });              

    $(document).ready(function () {
        $('#tablaDetalle').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang",
            },
            "bLengthChange": false,
            "bFilter": false,
            "bPaginate": false,
            "bInfo": false,
            //dom: 'T<"clear">lfrtip',
        });
    });              
 </script>