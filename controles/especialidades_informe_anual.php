<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/informesLogic.php');
if (isset($_POST['periodo']) && $_POST['periodo'] <> "") {
    $periodo = $_POST['periodo'];
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-12">
                <h4>Informe Anual de Especialistas</h4>
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
        ?>
        <form id="informeAnual" autocomplete="off" name="informeAnual" method="POST" action="especialidades_informe_anual.php">
            <div class="row">
                <div class="col-md-2">
                    <label>Período a procesar: </label>
                    <select class="form-control" id="periodo" name="periodo" required>
                        <?php 
                        $periodoAnterior = $_SESSION['periodoActual'] - 1;
                        $periodoActual = $_SESSION['periodoActual'];
                        if (!isset($_POST['periodo']) || $_POST['periodo'] == "") {
                            $periodo = $periodoActual;
                        }
                        ?>
                        <option value="<?php echo $periodoAnterior; ?>" <?php if($periodo == $periodoAnterior) { echo 'selected'; } ?>><?php echo $periodoAnterior.'/'.($periodoAnterior+1); ?></option>
                        <option value="<?php echo $periodoActual; ?>" 
                            <?php if($periodo == $periodoActual) { echo 'selected'; } ?>><?php echo $periodoActual.'/'.($periodoActual+1); ?></option>
                    </select>
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
        $totalPeriodo = 0;
    ?> 
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4"> 
                    <?php 
                    $informesLogic = new informesLogic();
                    $resInforme = $informesLogic->informeAnualPorTipoEspecialista($periodoProcesar);
                    if ($resInforme['estado']) {
                    ?>
                    <table id="tablaOrdenada" display>
                        <thead>
                            <tr>
                                <th>Tipo especialista</th>
                                <th style="text-align: right;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resInforme['datos'] as $row) {
                                $tipoEspecialista = $row['tipoEspecialista'];
                                $cantidad = $row['cantidad'];
                                $totalPeriodo += $cantidad;
                                ?>
                                <tr>
                                    <td><?php echo $tipoEspecialista; ?></td>
                                    <td style="text-align: right;"><?php echo $cantidad; ?></td>
                                </tr>
                            <?php 
                            }
                            ?>
                        </tbody>
                    </table>
                    <h5 class="text-right">Total: <b><?php echo $totalPeriodo; ?></b></h5>
                    <?php 
                    }
                    ?>
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
            "order": [[ 0, "asc" ]],
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