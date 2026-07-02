<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/informeContableLogic.php');

$periodo = PERIODO_ACTUAL;
$accion = 1;
$textoBoton = 'Confirmar';
$claseBoton = 'btn-info';
$readOnly = '';

$continua = TRUE;
$informesLogic = new informeContableLogic();
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Informe Contable</h4>
            </div>
            <div class="col-md-3 text-left">
                <a href="informe_contable_lista.php" class="btn btn-primary">Volver al listado</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['mensaje'])) {
        ?>
           <div class="ocultarMensaje"> 
               <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
           </div>
         <?php
            $periodo = $_POST['periodo'];
            $mesProcesado = $_POST['mesProcesado'];
        } else {
            $mesProcesado = NULL;
        }

        $mesesProcesados = $informesLogic->obtenerMesesYaProcesadosDelPeriodo($periodo);
        ?>
        <div class="row">
            <div class="col-md-12 text-center"><h4><b>Generar informe contable de cobranza, altas y bajas.</b></h4></div>
        </div>
        <form id="datosInforme" autocomplete="off" name="datosInforme" method="POST" action="datosContable/abm_informe_contable.php">
            <div class="row">
                <div class="col-md-4">&nbsp;</div>
                <div class="col-md-2">
                    <label for="periodo">Período Contable: * </label>
                    <input class="form-control" autofocus autocomplete="OFF" type="number" id="periodo" name="periodo" value="<?php echo $periodo; ?>" required="" readonly="" />
                </div>
                <div class="col-md-2">
                    <label>Mes: </label>
                    <select class="form-control" id="mesProcesado" name="mesProcesado" required >
                        <?php 
                        $i = 1;
                        while ($i <= 12) {
                            switch ($i) {
                                case '1':
                                    $mesProcesado = $periodo.'05';
                                    $labelMes = obtenerMes(5).' de '.$periodo;
                                    break;
                                
                                case '2':
                                    $mesProcesado = $periodo.'06';
                                    $labelMes = obtenerMes(6).' de '.$periodo;
                                    break;
                                
                                case '3':
                                    $mesProcesado = $periodo.'07';
                                    $labelMes = obtenerMes(7).' de '.$periodo;
                                    break;

                                case '4':
                                    $mesProcesado = $periodo.'08';
                                    $labelMes = obtenerMes(8).' de '.$periodo;
                                    break;

                                case '5':
                                    $mesProcesado = $periodo.'09';
                                    $labelMes = obtenerMes(9).' de '.$periodo;
                                    break;

                                case '6':
                                    $mesProcesado = $periodo.'10';
                                    $labelMes = obtenerMes(10).' de '.$periodo;
                                    break;

                                case '7':
                                    $mesProcesado = $periodo.'11';
                                    $labelMes = obtenerMes(11).' de '.$periodo;
                                    break;

                                case '8':
                                    $mesProcesado = $periodo.'12';
                                    $labelMes = obtenerMes(12).' de '.$periodo;
                                    break;

                                case '9':
                                    $mesProcesado = ($periodo+1).'01';
                                    $labelMes = obtenerMes(1).' de '.($periodo+1);
                                    break;

                                case '10':
                                    $mesProcesado = ($periodo+1).'02';
                                    $labelMes = obtenerMes(2).' de '.($periodo+1);
                                    break;

                                case '11':
                                    $mesProcesado = ($periodo+1).'03';
                                    $labelMes = obtenerMes(3).' de '.($periodo+1);
                                    break;

                                case '12':
                                    $mesProcesado = ($periodo+1).'04';
                                    $labelMes = obtenerMes(4).' de '.($periodo+1);
                                    break;

                                default:
                                    $mesProcesado = $periodo.'XX';
                                    $labelMes = "ERROR";
                                    break;
                            }
                            if (!in_array($mesProcesado, $mesesProcesados)) { 
                            //si todavia no se proceso entonces se muestra en el select
                            ?>
                                <option value="<?php echo $mesProcesado; ?>"><?php echo $labelMes; ?></option>
                            <?php
                            }
                            $i += 1;
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit"  class="btn <?php echo $claseBoton ?> btn-lg" onclick="waitingDialog.show('Generando Informe...');setTimeout(function () {waitingDialog.hide();}, 500000);">Confirma proceso</button>
                    <input type="hidden" name="accion" id="accion" value="agregar" />
                </div>
            </div>    
        </form>
    </div>    
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
    
</script>