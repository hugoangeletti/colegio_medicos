<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiacionAnualLogic.php');
require_once ('../dataAccess/homeBankingLogic.php');

$periodo = $_SESSION['periodoActual'];
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-12">
                <h4>Genera envío a HomeBanking (LINK / PagoMisCuentas)</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['mensaje']) && $_POST['mensaje'] == "OK") {
        ?>
           <div class="row">
                <div class="col-md-6 text-left"><h4>El proceso de generación ha finalizado <b><?php echo $_POST['mensaje'] ?></b></h4></div>
            </div>
         <?php
        } else {
        ?>
        <div class="row">&nbsp;</div>
        <form id="datosCobranza" autocomplete="off" name="datosCobranza" method="POST" action="datosHomeBanking/genera_archivos.php">
            <div class="row">
                <div class="col-md-2">
                    <label>Código de liquidación: </label>
                    <input class="form-control" type="text" name="codigoLiquidacion" id="codigoLiquidacion" value="<?php $codigoLiquidacion ?>" required>
                </div>
                <div class="col-md-2">
                    <label>Fecha primer vencimiento: </label>
                    <input class="form-control" type="date" name="fechaPrimerVencimiento" id="fechaPrimerVencimiento" value="<?php $fechaPrimerVencimiento ?>" required>
                </div>
                <div class="col-md-2">
                    <label>Fecha segundo vencimiento: </label>
                    <input class="form-control" type="date" name="fechaSegundoVencimiento" id="fechaSegundoVencimiento" value="<?php $fechaSegundoVencimiento ?>" required>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit"  class="btn btn-success btn-lg" onclick="waitingDialog.show('Generando Home Banking...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar liquidación </button>
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