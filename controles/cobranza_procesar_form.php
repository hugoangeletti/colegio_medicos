<?php 
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cobranzaLogic.php');
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php
}

if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
} else {
    $accion = 1;
}
$idLugarPago = NULL;

$continua = TRUE;
if ($accion <> 1) {
    if (isset($_POST['idCobranza']) && $_POST['idCobranza']) {
        $idCobranza = $_POST['idCobranza'];
        $idLugarPago = NULL;
    } else {
        $resCobranza['clase'] = "alert alert-warning";
        $resCobranza['icono'] = "glyphicon glyphicon-exclamation-sign";
        $resCobranza['mensaje'] = "Datos mal ingresados";
        $continua = FALSE;
    }
} else {
    $idCobranza = NULL;
}

if ($continua) {
?>
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Procesar Lotes de Cobranza</b></h4></div>
<div class="panel-body">
    <div class="row">
        <form method="POST" action="datosCobranza/procesar_lote.php" enctype="multipart/form-data" >
            <div class="col-md-3">
                <label>Lugar de cobranza:  *</label>
                <select class="form-control" id="idLugarPago" name="idLugarPago" required>
                    <option value="" selected>Saleccione Lugar de cobranza</option>
                    <?php
                    $lugaresProcesar = array(22, 23, 24, 25, 26, 28, 29, 30);
                    $resLugares = $lugarPagoLogic->obtenerLugaresDePago();
                    if ($resLugares['estado']) {
                        foreach ($resLugares['datos'] as $lugarPago) {
                            if (!in_array($lugarPago['id'], $lugaresProcesar)) { continue; }
                    ?>
                        <option value="<?php echo $lugarPago['id']; ?>" <?php if($lugarPago['id'] == $idLugarPago) { echo 'selected'; } ?>><?php echo $lugarPago['nombre']; ?></option>
                    <?php
                        }
                    } 
                    ?>
                </select>
            </div>
            <div class="col-md-5">
                <label>Archivo a procesar:  *</label>
            	<input type="file" id="archivoLote" name="archivoLote" required="">
        	</div>
            <!--
            <div class="col-md-2">
                <label>Fecha del lote:  *</label>
                <input type="date" class="form-control" id="fechaApertura" name="fechaApertura" value="<?php echo $fechaApertura;?>" required="" >
            </div>
            -->
            <div class="row">&nbsp;</div>
            <div class="col-md-8 text-center">
                <button type="submit"  class="btn btn-success btn-lg" onclick="waitingDialog.show('Generando Informe...');setTimeout(function () {waitingDialog.hide();}, 50000">Procesar </button>
                <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                <input type="hidden" name="idCobranza" id="idCobranza" value="<?php echo $idCobranza; ?>" />
            </div>
        </form>    
    </div>
</div>
</div>
</div>
<?php
} else {
    ?>  
    <div class="row">&nbsp;</div>
    <div class="<?php echo $resCobranza['clase']; ?>" role="alert">
        <span class="<?php echo $resCobranza['icono']; ?>" ></span>
        <span><strong><?php echo $resCobranza['mensaje']; ?></strong></span>
    </div>
<?php
}
?>
<div class="col-md-3">
    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="cobranza_lotes.php">
        <button type="submit"  class="btn btn-info" >Volver a Lotes</button>
    </form>
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