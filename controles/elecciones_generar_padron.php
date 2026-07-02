<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/zonaLogic.php');
require_once ('../dataAccess/eleccionesLogic.php');
require_once ('../dataAccess/eleccionesLocalidadesLogic.php');

$continua = TRUE;
$mensaje = "";
if (isset($_GET['id'])) {
    $idEleccionesLocalidad = $_GET['id'];
    $eleccionesLocalidadesLogic = new eleccionesLocalidades();
    $resElecciones = $eleccionesLocalidadesLogic->obtenerEleccionesLocalidadPorId($idEleccionesLocalidad);
    if ($resElecciones['estado']){
        $elecciones = $resElecciones['datos'];
        $idElecciones = $elecciones['idElecciones'];
        $codigoLocalidad = $elecciones['codigoLocalidad'];
        $localidadDetalle = $elecciones['localidadDetalle'];
    } else {
        $continua = FALSE;
        $mensaje .= $resElecciones['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idEleccionesLocalidad - ";
}

if ($continua) {
    if (isset($_POST['mensaje'])) {
    ?>
        <div id="divMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
    <?php    
    }
    ?>  
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-9">
                        <h4><b>Generar Padrón de <?php echo $localidadDetalle; ?></b></h4>
                    </div>
                    <div class="col-xs-3 text-right">
                        <a href="elecciones_localidades_lista.php?id=<?php echo $idElecciones; ?>" class="btn btn-info">Volver</a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <form id="formElecciones" name="formElecciones" method="POST" onSubmit="" action="datosElecciones/generar_padron.php">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="fechaCorte">Fecha de corte: *</label>
                            <input class="form-control" type="date" name="fechaCorte" id="fechaCorte" required>
                        </div>
                        <div class="col-md-2">
                            <br>
                            <button type="submit"  class="btn btn-success "  onclick="waitingDialog.show('Generando padrón...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar</button>
                            <input type="hidden" name="idEleccionesLocalidad" id="idEleccionesLocalidad" value="<?php echo $idEleccionesLocalidad; ?>">
                            <input type="hidden" name="codigoLocalidad" id="codigoLocalidad" value="<?php echo $codigoLocalidad; ?>">
                        </div>
                    </div>  
                </form>   
            </div>
        </div>
    </div>
<?php
}
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
