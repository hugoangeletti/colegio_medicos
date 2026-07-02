<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiado_seguro_Logic.php');

if (isset($_POST['mensaje']) && $_POST['mensaje'] <> "OK") {
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
            <div class="col-md-10">
                <h4>Generar envio de seguro praxis médica</h4>
            </div>
            <div class="col-md-2">
                <a href="seguro_praxis_medica_listado.php" class="btn btn-primary" >Volver</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $periodo = '';
        $colegiado_seguro_Logic = new colegiado_seguro_Logic();
        $resUltimoProceso = $colegiado_seguro_Logic->obtenerUltimoProcesoEnvioSeguro();
        if ($resUltimoProceso['estado']) {
            $ultimoProceso = $resUltimoProceso['datos'];
            $idSeguroPraxisMedicaEnvios = $ultimoProceso['idSeguroPraxisMedicaEnvios'];
            if (isset($ultimoProceso)) {
                $procesoMes = $ultimoProceso['procesoMes'];           
                $procesoAnio = $ultimoProceso['procesoAnio'];
            } else {
                $procesoMes = date('m');
                $procesoAnio = date('Y');
            }
        } else {
            $procesoMes = date('m');
            $procesoAnio = date('Y');
        }
        if ($procesoMes == 12) {
            $procesoMes = 1;
            $procesoAnio += 1;
        } else {
            $procesoMes += 1;
        }
        $periodo = $procesoAnio.'-'.rellenarCeros($procesoMes, 2);
        $fechaLimiteProceso = date('Y-m-d');
        ?>
        <div class="row">
            <div class="col-md-12 text-center"><h4><b>Generar archivos para envio</b></h4></div>
        </div>
        <form id="datosProceso" autocomplete="off" name="datosProceso" method="POST" action="datosSeguroPraxisMedica/seguro_praxis_medica_proceso.php">
            <div class="row">
                <div class="col-md-2">
                    <label>Período: * </label>
                    <input class="form-control" type="text" id="periodo" name="periodo" value="<?php echo $periodo; ?>" readonly/>
                    <br>
                </div>
                <div class="col-md-2">
                    <label>Fecha límite: </label>
                    <input class="form-control" type="date" name="fechaLimiteProceso" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fechaLimiteProceso ?>" required="" />
                </div>
                <div class="col-md-2">
                    <br>
                    <button type="submit"  class="btn btn-success" onclick="waitingDialog.show('Generando Listado Seguro...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar archivos </button>
                    <input type="hidden"  name="procesoAnio" id="procesoAnio" value="<?php echo $procesoAnio; ?>">
                    <input type="hidden"  name="procesoMes" id="procesoMes" value="<?php echo $procesoMes; ?>">
                    <input type="hidden"  name="idSeguroPraxisMedicaEnvios" id="idSeguroPraxisMedicaEnvios" value="<?php echo $idSeguroPraxisMedicaEnvios; ?>">
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
