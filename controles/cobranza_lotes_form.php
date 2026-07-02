<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php
}
$continua = TRUE;
$mensaje = "";
if (isset($_GET['accion']) && $_GET['accion']) {
    $accion = $_GET['accion'];
    if ($accion == 3) {
        if (isset($_GET['id']) && $_GET['id']) {
            $idCobranza = $_GET['id'];
        } else {
            $continua = FALSE;
            $mensaje .= "Falta idCobranza - ";
        }
    } else {
        $idCobranza = NULL;
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta accion - ";
}
if (isset($_GET['idLugarPago']) && $_GET['idLugarPago']) {
    $idLugarPago = $_GET['idLugarPago'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idLugarPago - ";
}
if (isset($_GET['anio']) && $_GET['anio']) {
    $anio = $_GET['anio'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta anio - ";
}
if (isset($_GET['debito_agremiaciones'])) {
    $debito_agremiaciones = TRUE;
} else {
    $debito_agremiaciones = FALSE;
}
?>
<div class="panel panel-info">
    <?php
    if ($continua) {
        if (isset($idCobranza)) {
            $resLote = $cobranzaLogic->obtenerLotePorId($idCobranza);
            if ($resLote['estado']) {
                $lote = $resLote['datos'];
                $idLugarPago = $lote['idLugarPago'];
                $lugarPago = $lote['lugarPago'];
                $cantidadComprobantes = $lote['cantidadComprobantes'];
                $totalRecaudacion = $lote['totalRecaudacion'];
                $fechaApertura = $lote['fechaApertura'];
                $estado = $lote['estado'];
                $tipoLote = $lote['tipoLote'];
                $numeroLoteManual = $lote['numeroLoteManual'];
                $diferenciaImporte = $lote['diferenciaImporte'];
                $anio = substr($fechaApertura, 0, 4);
            } else {
                $mensaje .= $resLote['mensaje'];
                $continua = FALSE;
            }
        } else {
            $resLugarPago = $lugarPagoLogic->obtenerLugarPagoPorId($idLugarPago);
            if ($resLugarPago['estado']) {
                $lugarPago = $resLugarPago['datos']['nombre'];
                $cantidadComprobantes = 0;
                $totalRecaudacion = NULL;
                $diferenciaImporte = NULL;
                $fechaApertura = date('Y-m-d');
                $periodo = PERIODO_ACTUAL;
                $cuota = $cobranzaLogic->obtenerProximaCuota($periodo, $idLugarPago);
                $estado = 'A';
                $tipoLote = 'M';
                $resNumero = $cobranzaLogic->obtenerNumeroLoteManual();
                if ($resNumero['estado']) {
                    $numeroLoteManual = $resNumero['numeroLoteManual'];
                } else {
                    $mensaje .= $resNumero['mensaje'];
                    $continua = FALSE;    
                }
            } else {
                $mensaje .= $resLugarPago['mensaje'];
                $continua = FALSE;
            }
        }
    }

    if ($continua) {
    ?>
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4><?php if ($accion == 1) { echo 'Alta del lote: '; } else { echo 'Editar lote: '; } echo $numeroLoteManual.' de '.$lugarPago; if ($accion == 3) { echo ' con fecha '.cambiarFechaFormatoParaMostrar($fechaApertura); } ?></h4>
                </div>
                <div class="col-md-3 text-left">
                    <?php 
                    if ($debito_agremiaciones) {
                    ?>
                        <form id="formLotes" name="formLotes" method="POST" onSubmit="" action="debito_agremiaciones.php">
                            <button type="submit"  class="btn btn-info" >Volver </button>
                            <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>">
                            <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $anio; ?>">
                        </form>
                    <?php
                    } else {
                    ?>
                        <form id="formLotes" name="formLotes" method="POST" onSubmit="" action="cobranza_lotes.php">
                            <button type="submit"  class="btn btn-info" >Volver a Lotes</button>
                            <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>">
                            <input type="hidden" name="anioCobranza" id="anioCobranza" value="<?php echo $anio; ?>">
                        </form>
                    <?php 
                    } 
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <form id="formLote" name="formLote" method="POST" onSubmit="" action="datosCobranza/abm_lote_cobranza.php">
                <div class="row">
                    <div class="col-md-2">
                        <label>Fecha Apertura *</label>
                        <input type="date" class="form-control" name="fechaApertura" id="fechaApertura" value="<?php echo $fechaApertura; ?>" required="" />
                    </div>
                    <div class="col-md-2">
                        <label>Total recaudación *</label>
                        <input class="form-control" type="text" name="totalRecaudacion" id="totalRecaudacion" value="<?php echo $totalRecaudacion; ?>" required="" />
                    </div>
                    <?php 
                    if ($accion == 1) {
                    ?>
                        <div class="col-md-2">
                            <label>Período * </label>
                            <input class="form-control" type="number" name="periodo" id="periodo" value="<?php echo $periodo; ?>" />
                        </div>
                        <div class="col-md-2">
                            <label>Cuota * </label>
                            <input class="form-control" type="number" name="cuota" id="cuota" value="<?php echo $cuota; ?>" max="12" />
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="col-md-2">
                            <label>Cantidad de comprobantes * </label>
                            <input class="form-control" type="number" name="cantidadComprobantes" id="cantidadComprobantes" value="<?php echo $cantidadComprobantes; ?>" />
                        </div>
                        <div class="col-md-2">
                            <label>Diferencia </label>
                            <input class="form-control" type="text" name="diferenciaImporte" id="diferenciaImporte" value="<?php echo $diferenciaImporte; ?>" />
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8 text-center">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-info" onclick="waitingDialog.show('Generando Lote de pagos...');setTimeout(function () {waitingDialog.hide();}, 500000);" >Confirmar</button>
                        <input class="form-control" type="hidden" name="idCobranza" id="idCobranza" value="<?php echo $idCobranza; ?>" />
                        <input class="form-control" type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>" />
                        <input class="form-control" type="hidden" name="numeroLoteManual" id="numeroLoteManual" value="<?php echo $numeroLoteManual; ?>" />
                        <input class="form-control" type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                    </div>
                </div>

                <div class="row">&nbsp;</div>
            </form>        
        </div>
    <?php
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <span class="glyphicon glyphicon-exclamation-sign" ></span>
                    <span><strong><?php echo $mensaje; ?></strong></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="cobranza_lotes.php">
                    <button type="submit"  class="btn btn-info" >Volver</button>
                </form>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="row">&nbsp;</div>
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
