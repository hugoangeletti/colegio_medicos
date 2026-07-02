<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();

$periodo = PERIODO_ACTUAL;
if (date('Y') > $periodo) {
    if (date('m') >= '6') {
        $periodo += 1;
    }
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-12">
                <h4>Liquidación de Colegiación Anual</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['mensaje']) && $_POST['mensaje'] == "OK") {
        ?>
           <div class="row">
                <div class="col-md-6 text-left"><h4>El proceso de liquidación ha finalizado <b><?php echo $_POST['mensaje'] ?></b></h4></div>
            </div>
         <?php
        } else {
        ?>
        <div class="row">
            <div class="col-md-12 text-center"><h4><b>Liqudar cuotas de colegiación anual</b></h4></div>
        </div>
        <form id="datosColegiacion" autocomplete="off" name="datosColegiacion" method="POST" action="datosColegiacion/liquidacion_colegiacion_anual.php">
            <div class="row">
                <div class="col-md-3">
                    <label>Período: * </label>
                    <input class="form-control" autofocus autocomplete="OFF" type="number" id="periodo" name="periodo" value="<?php echo $periodo; ?>" />
                    <br>
                    <!-- Solo para el periodo 2020
                    <label>Descuenta cuotas: * </label>
                        <input type="radio" name="descuentaPagos" id="descuentaPagos" value="S" checked=""> SI
                        <input type="radio" name="descuentaPagos" id="descuentaPagos" value="N" > NO
                    <br>
                    <label>Cuotas: </label>
                        <input type="text" name="cuotasVerificar" id="cuotasVerificar" value="" />-->
                </div>
                <div class="col-md-9">
                    <label>Cuotas: </label>
                    <?php
                    $resColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnualPorPeriodo($periodo, null);
                    if ($resColegiacion['estado']) {
                        ?>
                        <table class="table">
                            <thead>
                                <th>Antigüedad</th>
                                <th>Cuotas</th>
                                <th>Importe</th>
                                <th>Vencimiento Cuota 1</th>
                                <th>Pago Total</th>
                                <th>Vencimiento Pago Total</th>
                            </thead>
                            <tbody>
                        <?php
                        foreach ($resColegiacion['datos'] as $datos) {
                            $idColegiacionAnual = $datos['idColegiacionAnual'];
                            $periodo = $datos['periodo'];
                            $cuotas = $datos['cuotas'];
                            $antiguedad = $datos['antiguedad'];
                            $importe = $datos['importe'];
                            $vencimientoCuotaUno = $datos['vencimientoCuotaUno'];
                            $pagoTotal = $datos['pagoTotal'];
                            $vencimientoPagoTotal = $datos['vencimientoPagoTotal'];
                            switch ($antiguedad) {
                                case '1':
                                    $antiguedadTexto = "menos de 5 años";
                                    break;
                                
                                case '2':
                                    $antiguedadTexto = "5 o más años";
                                    break;
                                
                                default:
                                    $antiguedadTexto = "";
                                    break;
                            }
                            ?>
                            <tr>
                                <td><?php echo $antiguedad.' - '.$antiguedadTexto; ?></td>
                                <td><?php echo $cuotas; ?></td>
                                <td><?php echo $importe; ?></td>
                                <td><?php echo cambiarFechaFormatoParaMostrar($vencimientoCuotaUno); ?></td>
                                <td><?php echo $pagoTotal; ?></td>
                                <td><?php echo cambiarFechaFormatoParaMostrar($vencimientoCuotaUno); ?></td>
                            </tr>
                        <?php    
                        } 
                        ?>
                        </tbody>
                    </table>
                    <?php
                    } else {
                        ?>
                        <div class="col-md-12">
                            <div class="<?php echo $resColegiacion['clase']; ?>" role="alert">
                                <span class="<?php echo $resColegiacion['icono']; ?>" aria-hidden="true"></span>
                                <span><strong><?php echo $resColegiacion['mensaje']; ?></strong></span>
                            </div>        
                        </div>
                    <?php 
                    }
                    ?>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <?php
                    if ($resColegiacion['estado']) {
                    ?>
                    <button type="submit"  class="btn btn-success btn-lg" onclick="waitingDialog.show('Generando Liquidación Anual...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar liquidación </button>
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    <?php
                    }
                    ?>
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