<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiacionAnualLogic.php');
require_once ('../dataAccess/zonaLogic.php');
$zonaLogic = new zonaLogic();
require_once ('../dataAccess/lugarPagoLogic.php');
$lugarPagoLogic = new lugarPagoLogic();

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
                <h4>Emisión de chequeras de Colegiación Anual</h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        if (isset($_POST['mensaje']) && $_POST['mensaje'] == "OK") {
        ?>
           <div class="row">
                <div class="col-md-6 text-left"><h4><b><?php echo $_POST['mensaje'] ?></b></h4></div>
            </div>
         <?php
        } else {
        ?>
        <form id="datosColegiacion" autocomplete="off" name="datosColegiacion" method="POST" action="emision_colegiacion_anual.php">
            <div class="row">
                <div class="col-md-4">
                    <label>Período: * </label>
                    <input class="form-control" autofocus autocomplete="OFF" type="number" id="periodo" name="periodo" value="<?php echo $periodo; ?>" />
                    <br>
                    <label>Emitir chequeras por: </label>
                        <input type="radio" name="emitirPor" id="emitirPor" value="P" checked=""> Partido
                        <input type="radio" name="emitirPor" id="emitirPor" value="A" > Agremiación
                    <br>
                </div>
                <div class="col-md-8" id="grupoZona">
                    <div class="6">
                        <div class = "form-group">                        
                            <label>Partido: </label>
                            <?php
                            $resEmitirPor = $zonaLogic->obtenerZonas();
                            if ($resEmitirPor['estado']) {
                                ?>
                                <select class="form-control" id="idZona" name="idZona" >
                                    <option value="" selected>Seleccione Partido</option>
                                    <?php
                                    foreach ($resEmitirPor['datos'] as $datos) {
                                        ?>
                                        <option value="<?php echo $datos['id']; ?>" ><?php echo $datos['nombre']; ?></option>
                                    <?php    
                                    } 
                                    ?>
                                </select>            
                            <?php
                            } else {
                                ?>
                                <div class="<?php echo $resEmitirPor['clase']; ?>" role="alert">
                                    <span class="<?php echo $resEmitirPor['icono']; ?>" aria-hidden="true"></span>
                                    <span><strong><?php echo $resEmitirPor['mensaje']; ?></strong></span>
                                </div>        
                            <?php 
                            }
                            ?>
                        </div>
                    </div>
                    <div class="6">
                        <div class = "form-group">
                            <b>Código Postal</b>
                            <select  id = "codigoPostal" name = "codigoPostal"  class = "form-control" disabled = "disabled" required = "required">
                                <option value = "">Seleccione Código Postal</option>
                            </select>
                        </div>
                    </div>
                    <div class="6">
                        <div class = "form-group">
                            <b>Calle desde</b>
                            <input class="form-control" type="text" name="calleDesde" value="" placeholder="Calle desde" autofocus="" />
                        </div>
                    </div>
                    <div class="6">
                        <div class = "form-group">
                            <b>Calle hasta</b>
                            <input class="form-control" type="text" name="calleHasta" value="" placeholder="Calle hasta" />
                        </div>
                    </div>
                </div>
                <div class="col-md-8" id="grupoAgremiaciones" style="display: none;">
                    <div class="6">
                        <div class = "form-group">                        
                            <label>Agremiación: </label>
                            <?php
                            $resEmitirPor = $lugarPagoLogic->obtenerAgremiaciones();
                            if ($resEmitirPor['estado']) {
                                ?>
                                <select class="form-control" id="idAgremiacion" name="idAgremiacion" >
                                    <option value="" selected>Seleccione Agremiación</option>
                                    <?php
                                    foreach ($resEmitirPor['datos'] as $datos) {
                                        ?>
                                        <option value="<?php echo $datos['id']; ?>" ><?php echo $datos['nombre']; ?></option>
                                    <?php    
                                    } 
                                    ?>
                                </select>            
                            <?php
                            } else {
                                ?>
                                <div class="<?php echo $resEmitirPor['clase']; ?>" role="alert">
                                    <span class="<?php echo $resEmitirPor['icono']; ?>" aria-hidden="true"></span>
                                    <span><strong><?php echo $resEmitirPor['mensaje']; ?></strong></span>
                                </div>        
                            <?php 
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit"  class="btn btn-success btn-lg" 
                    <?php //onclick="waitingDialog.show('Emitiendo chequeras Colegiación Anual...');setTimeout(function () {waitingDialog.hide();}, 50);" ?>>Emitir chequeras</button>
                    <input type="hidden" name="accion" id="accion" value="Emitir" />
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
<script type = "text/javascript">
    $(document).ready(function(){
        $('#idZona').on('change', function(){
            if($('#idZona').val() != "4"){
                $('#codigoPostal').empty();
                $('<option value = "0">Todos</option>').appendTo('#codigoPostal');
                $('#codigoPostal').attr('disabled', 'disabled');
            }else{
                $('#codigoPostal').removeAttr('disabled', 'disabled');
                $('#codigoPostal').load('localidadPorZona.php?idZona=' + $('#idZona').val());
            }
            
        });
    });

</script>
<script>
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="emitirPor"]:checked').val();
    });
    $(document).on('click', '[name="emitirPor"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            var x = document.getElementById("grupoZona");
            var y = document.getElementById("grupoAgremiaciones");
            //if (x.style.display === "none") {
            if (lastSelected != 'P') {
                x.style.display = "block";
                y.style.display = "none";
            } else {
                x.style.display = "none";
                y.style.display = "block";
            }
            //alert("radio box with value " + $('[name="conFirma"][value="' + lastSelected + '"]').val() + " was deselected");
        }
        lastSelected = $(this).val();
    });
</script>