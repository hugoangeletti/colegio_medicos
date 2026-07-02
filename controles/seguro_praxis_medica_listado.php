<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiado_seguro_Logic.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaEnvios').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 1, "desc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR EL PROCESO?'))
        return true;
    else
        return false;
}
</script>
<?php
$idUsuario = $_SESSION['user_id'];

if (isset($_POST['mensaje'])) {
?>
   <div class="ocultarMensaje"> 
       <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
<?php
}
$fecha = date('Y-m-d');
$periodoProcesar = date('Ym');
$continua = TRUE;
$mensaje = '';
?>

<div class="panel panel-info">
<div class="panel-heading">
    <h4>Listado de envios para seguro praxis médica</h4>
</div>
<div class="panel-body">
    <div class="row">
        <div class="col-md-1">
            <?php
            $colegiado_seguro_Logic = new colegiado_seguro_Logic();
            if ($colegiado_seguro_Logic->noHayLiquidacionEnElMes(date('Y-m-d')) && $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 89)) {
            ?>
                <a href="seguro_praxis_medica_form.php" class="btn btn-primary">Generar envío</a>
            <?php 
            }
            ?>
        </div>
        <div class="col-md-2">
            <form id="buscarMatricula" name="buscarMatricula" method="POST" action="seguro_praxis_medica_matricula.php">
                <div class="col-md-10">
                    <label for="matricula">Buscar matrícula: </label>
                    <input class="form-control" type="number" name="matricula" id="matricula">
                </div>
                <div class="col-md-2">
                    <br>
                    <button type="submit" class="btn btn-success">Buscar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <?php
            $resSeguroProcesado = $colegiado_seguro_Logic->obtenerSegurosProcesados();
            if ($resSeguroProcesado['estado']) {
            ?>
                <table  id="tablaEnvios" class="display">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th style="text-align: center;">Período</th>
                            <th style="text-align: center;">Fecha</th>
                            <th style="text-align: center;">Vigentes</th>
                            <th style="text-align: center;">Altas</th>
                            <!--<th style="text-align: center;">Bajas</th>-->
                            <th style="text-align: center;">Archivos</th>
                            <th style="text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $anular = TRUE;
                        foreach ($resSeguroProcesado['datos'] as $dato){
                            $idSeguro = $dato['idSeguro'];
                            $procesoAnio = $dato['procesoAnio'];
                            $procesoMes = $dato['procesoMes'];
                            $periodo = $procesoAnio.'-'.rellenarCeros($procesoMes, 2);
                            $fechaLimiteProceso = $dato['fechaLimiteProceso'];
                            $cantidadVigentes = $dato['cantidadVigentes'];
                            $cantidadAltas = $dato['cantidadAltas'];
                            $cantidadBajas = $dato['cantidadBajas'];
                            $pathNombre = $dato['pathNombre'];
                            $archivoAltas = $dato['archivoAltas'];
                            $archivoBajas = $dato['archivoBajas'];
                            $archivoCompleto = $dato['archivoCompleto'];
                            ?>
                            <tr>
                                <td><?php echo $idSeguro; ?></td>
                                <td style="text-align: center;"><?php echo $periodo;?></td>
                                <td style="text-align: center;"><?php echo $fechaLimiteProceso?></td>
                                <td style="text-align: center;"><?php echo $cantidadVigentes;?></td>
                                <td style="text-align: center;"><?php echo $cantidadAltas;?></td>
                                <!--<td style="text-align: center; <?php echo $style; ?>"><?php echo $cantidadBajas;?></td>-->
                                <td>
                                    <a href="seguro_praxis_medica_vigentes.php?id=<?php echo $idSeguro; ?>" class="btn btn-primary">Vigentes</a>
                                    <a href="seguro_praxis_medica_cancelacion_deuda.php?id=<?php echo $idSeguro; ?>" class="btn btn-primary" onclick="waitingDialog.show('Generando Liquidación Anual...');setTimeout(function () {waitingDialog.hide();}, 500000);">Cancelación deuda</a>
                                    <!--
                                    <a href="seguro_praxis_medica_altas.php?id=<?php echo $idSeguro; ?>" class="btn btn-primary">Altas</a>
                                    <a href="seguro_praxis_medica_bajas.php?id=<?php echo $idSeguro; ?>" class="btn btn-primary">Bajas</a>
                                    -->
                                </td>
                                <td>
                                    <?php 
                                    if ($anular && $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 90)) {
                                    ?>
                                        <a href="datosSeguroPraxisMedica/anular_proceso.php?id=<?php echo $idSeguro; ?>" class="btn btn-danger" onclick="return confirmaAnular()">Anular</a>
                                    <?php 
                                        $anular = FALSE;
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
                <div class="<?php echo $resSeguroProcesado['clase']; ?>" role="alert">
                    <span class="<?php echo $resSeguroProcesado['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resSeguroProcesado['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            ?>
        </div>
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
