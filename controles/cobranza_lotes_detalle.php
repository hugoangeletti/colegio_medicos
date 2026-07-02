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
?>
<script>
$(document).ready(function () {
    $('#tablaOrdenada').DataTable({
        "iDisplayLength":10,
        "order": [[ 0, "asc"]],
        "language": {
            "url": "../public/lang/esp.lang"
        },
        "bLengthChange": true,
        "bFilter": true,
        dom: 'T<"clear">lfrtip'
    });
});

function confirmar()
{
    if(confirm('¿Estas seguro de elimiar este pago?'))
        return true;
    else
        return false;
}

function confirmarAplicacionPagos()
{
    if(confirm('¿Estas seguro de aplicar los pagos del lote?'))
        return true;
    else
        return false;
}
</script>
<?php
$continua = TRUE;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCobranza = $_GET['id'];
    $resLote = $cobranzaLogic->obtenerLotePorId($idCobranza);
    if ($resLote['estado']) {
        $lote = $resLote['datos'];
        $fechaApertura = $lote['fechaApertura'];
        $anio = substr($fechaApertura, 0, 4);
        //$idLugarPago = $lote['idLugarPago'];
        $lugarPago = $lote['lugarPago'];
        $cantidadComprobantes = $lote['cantidadComprobantes'];
        $totalRecaudacion = $lote['totalRecaudacion'];
        $tipoLote = $lote['tipoLote'];
        $numeroLoteManual = $lote['numeroLoteManual'];
        if ($lote['estado'] == 'A') {
            $estadoLote = "ABIERTO";
        } else {
            $estadoLote = "CERRADO";
        }
    } else {
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}

if (isset($_GET['idLugarPago']) && $_GET['idLugarPago'] <> "") {
    $idLugarPago = $_GET['idLugarPago'];
} else {
    $idLugarPago = NULL;
}

if ($continua) {
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
    <?php    
    }   
    ?> 
    <div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
        <div class="col-md-10">
            <h5>Lote de Cobranza <b><?php if ($tipoLote == 'MANUAL') { echo 'MANUAL N° '.$numeroLoteManual; } else { echo 'N° '.$idCobranza; } ?></b> de fecha <b><?php echo cambiarFechaFormatoParaMostrar($fechaApertura); ?></b>. Lugar de Pago: <b><?php echo $lugarPago; ?></b> - Recaudación: <b><?php echo number_format($totalRecaudacion, 2, ',', '.') ?></b> - Cantidad Comprobante: <b><?php echo $cantidadComprobantes; ?></b></h5>
        </div>
        <div class="col-md-2">
            <?php 
            if ($tipoLote == 'MANUAL' && $estadoLote == "ABIERTO") {
            ?>
                <a href="cobranza_lotes_pago_form.php?id=<?php echo $idCobranza; ?>" class="btn btn-primary">Agregar pago</a>
            <?php 
            }
            ?>
        </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
        <?php
        $resPagos = $cobranzaLogic->obtenerDetalleLote($idCobranza);
        if ($resPagos['estado']){
        ?>
            <br>
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Matrícula / Asistente</th>
                        <th>Apellido y Nombres</th>
                        <th>Cuota</th>
                        <th>Fecha de Pago</th>
                        <th>Importe cobrado</th>
                        <th>Recargo cobrado</th>
                        <th>Recibo</th>
                        <th>Tipo de Pago</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resPagos['datos'] as $dato) {
                        $idCobranzaDetalle = $dato['idCobranzaDetalle'];
                        $referencia = NULL;
                        $apellidoNombre = NULL;
                        if (isset($dato['idColegiado'])) {
                            $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                            $referencia = $dato['matricula'];
                        } 
                        if (isset($dato['idAsistente'])) {
                            $apellidoNombre = trim($dato['asistente']);
                            $referencia = $dato['idAsistente'];
                        } 
                        $fechaPago = cambiarFechaFormatoParaMostrar($dato['fechaPago']);
                        $importe = $dato['importe'];
                        $recargo = $dato['recargo'];
                        $recibo = $dato['recibo'];
                        $tipoPago = $dato['tipoPago'].' ('.obtenrTipoPago($dato['detalleTipoPago']).')';
                        $cuotaAbonada = "";
                        if (isset($dato['periodo']) && $dato['periodo'] > 0) {
                            $cuotaAbonada = trim($dato['periodo']);
                        }
                        if (isset($dato['cuota']) && $dato['cuota'] >= 0) {
                            if ($cuotaAbonada <> "") {
                                $cuotaAbonada .= '/';
                            }
                            $cuotaAbonada .= rellenarCeros($dato['cuota'], 2);
                        }
                        //$detalleTipoPago = $dato['detalleTipoPago'];

                      ?>
                    <tr>
                	   <td><?php echo $idCobranzaDetalle;?></td>
                       <td><?php echo $referencia;?></td>
                       <td><?php echo $apellidoNombre;?></td>
                       <td><?php echo $cuotaAbonada;?></td>
                       <td><?php echo $fechaPago;?></td>
                       <td><?php echo $importe;?></td>
                       <td><?php echo $recargo;?></td>
                       <td><?php echo $recibo;?></td>
                       <td><?php echo $tipoPago;?></td>
                       <td style="width:200px;">
                            <?php 
                            if ($tipoLote == 'MANUAL' && $estadoLote == "ABIERTO") {
                                /*<a href="cobranza_lotes_pago_form.php?id=<?php echo $id; ?>&accion=3" class="btn btn-primary">Editar </a>*/
                            ?>
                                <a href="datosCobranza/abm_cobranza_lotes_pago.php?idPago=<?php echo $idCobranzaDetalle; ?>&accion=2&idCobranza=<?php echo $idCobranza; ?>" class="btn btn-danger" onclick="return confirmar()">Borrar </a>
                            <?php 
                            } else {
                                echo '';
                            }
                            ?>
                       </td>
                    </tr>
                  <?php
                  }
                  ?>              
    	       </tbody>
    	  </table>
        <?php
        } else {
            ?>  
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resPagos['clase']; ?>" role="alert">
                <span class="<?php echo $resPagos['icono']; ?>" ></span>
                <span><strong><?php echo $resPagos['mensaje']; ?></strong></span>
            </div>
        <?php
        }    
        ?>
    </div>
    </div>
    </div>
    <?php
} else {
    ?>  
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-exclamation-sign" ></span>
        <span><strong>Ingreso incorrecto</strong></span>
    </div>
<?php
}
?>
<div class="row">
    <div class="col-md-2">
        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="cobranza_lotes.php">
            <button type="submit"  class="btn btn-info" >Volver a Lotes</button>
            <?php 
            if (isset($idLugarPago)) {
            ?>
                <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>">
            <?php
            }
            ?>
            <input type="hidden" name="anioCobranza" id="anioCobranza" value="<?php echo $anio; ?>">
        </form>
    </div>
    <?php 
    if ($tipoLote == 'MANUAL' && $estadoLote == 'ABIERTO') {
    ?>
    <div class="col-md-2">
        <form id="formAplicarPagos" name="formAplicarPagos" method="POST" onSubmit="" action="datosCobranza/aplicar_pagos.php">
            <button type="submit" class="btn btn-info" onclick="return confirmarAplicacionPagos()" onclick="waitingDialog.show('Aplicando los pagos...');setTimeout(function () {waitingDialog.hide();}, 500000);">Aplicar pagos </button>
            <input type="hidden" name="idCobranza" id="idCobranza" value="<?php echo $idCobranza; ?>">
            <input type="hidden" name="idLugarPago" id="idLugarPago" value="<?php echo $idLugarPago; ?>">
            <input type="hidden" name="anioCobranza" id="anioCobranza" value="<?php echo $anio; ?>">
        </form>
    </div>
    <?php 
    }
    ?>
</div>
<div class="row">&nbsp;</div>
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
