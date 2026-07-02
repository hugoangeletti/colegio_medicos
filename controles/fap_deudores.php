<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-12">
                <h4>Listado de Deudores de Médicos Asistidos</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['cuotasAdeudadas']) && isset($_POST['antiguedad'])) {
            $cuotasAdeudadas = $_POST['cuotasAdeudadas'];
            $antiguedad = $_POST['antiguedad'];
            $fapLogic = new fapLogic();
            $resDeudores = $fapLogic->obtenerDeudoresAsistidos($cuotasAdeudadas, $antiguedad);
            if ($resDeudores['estado']) {
            ?>
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Matricula</th>
                            <th>Apellido y Nombre</th>
                            <th>Domicilio</th>
                            <th>Teléfonos</th>
                            <th>Correo electrónico</th>
                            <th>Estado matricular</th>
                            <th>Cuotas adeudadas</th>
                            <th>N° FAP</th>
                        </tr>
                    </thead>
                    <tbody>                
                    <?php
                    foreach ($resDeudores['datos'] as $dato) {
                        $matricula = $dato['matricula'];
                        $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                        $estadoDetalle = $dato['estadoDetalle'];
                        $domicilioCompleto = $dato['domicilioCompleto'];
                        $telefonos = trim($dato['telefonoFijo']).' / '.trim($dato['telefonoMovil']);
                        $correoElectronico = $dato['correoElectronico'];
                        $cantidadCuotas = $dato['cantidadCuotas'];
                        $fap = $dato['fap'];
                        ?>
                        <tr>
                            <td><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <td><?php echo $domicilioCompleto;?></td>
                            <td><?php echo $telefonos;?></td>
                            <td><?php echo $correoElectronico;?></td>
                            <td><?php echo $estadoDetalle;?></td>
                            <td><?php echo $cantidadCuotas;?></td>
                            <td><?php echo $fap;?></td>
                        </tr>
                    <?php    
                    }
                    ?>
                    </tbody>
                </table>
            <?php
            } else {
            ?>
                <div class="<?php echo $resDeudores['clase']; ?>" role="alert">
                    <span class="<?php echo $resDeudores['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resDeudores['mensaje']; ?></strong></span>
                </div>
            <?php
            }
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <form id="datosBusqueda" autocomplete="off" name="datosBusqueda" method="POST" action="fap_deudores.php?procesar">
                <div class="row">
                    <div class="col-md-2">
                        <label for="cuotasAdeudadas">Mínimo de cuotas adeudadas: *</label>
                        <input class="form-control" type="number" name="cuotasAdeudadas" id="cuotasAdeudadas" required>
                    </div>
                    <div class="col-md-3">
                        <label for="antiguedad">Antigüedad en años de ingreso al sistema: *</label>
                        <input class="form-control" type="number" name="antiguedad" id="antiguedad" required>
                    </div>
                    <div class="col-md-1 text-left">
                        <br>
                        <button type="submit"  class="btn btn-success btn-lg" onclick="waitingDialog.show('Generando Listado...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar listado </button>
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
    
$(document).ready(function () {
    $('#tablaOrdenada').DataTable({
        "iDisplayLength":25,
        "language": {
            "url": "../public/lang/esp.lang"
        },
        "order": [[ 0, "asc" ], [ 1, "asc"]],
        dom: 'T<"clear">lfrtip',
    });
});            

</script>