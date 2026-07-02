<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/sisaLogic.php');
$sisaLogic = new sisaLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "order": [[ 0, "desc" ]],
            dom: 'T<"clear">lfrtip',
        });
    });
            
function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR este PROCESO?'))
        return true;
    else
        return false;
}
   
</script>

<?php
if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}   
?> 
<div class="panel panel-info">
<div class="panel-heading"><h4>SISA exportaciones - Listado de Envios generados</h4></div>
<div class="panel-body">
    <div class="row">
        <?php
        if (isset($_POST['anio']) && $_POST['anio'] != ""){
            $anio = $_POST['anio'];
        } else {
            $anio = date('Y');
        }
        ?>
        <div class="row">
            <div class="col-xs-6">
                <form method="POST" action="sisa_exportaciones.php">
                    <div class="col-xs-3">
                        <label for="anio">Año</label>
                        <select class="form-control" id="anio" name="anio" required onChange="this.form.submit()">
                            <option value="0" selected>Todos</option>
                            <?php
                            $anioDebito = date('Y');
                            while ($anioDebito >= 2024) {
                            ?>
                                <option value="<?php echo $anioDebito; ?>" <?php if($anioDebito == $anio) { echo 'selected'; } ?>><?php echo $anioDebito; ?></option>
                            <?php
                                $anioDebito--;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-3">
                    </div>
                    <div class="col-xs-6">
                    </div>
                </form>    
            </div>
            <div class="col-xs-3"></div>
            <div class="col-xs-3">
                <a href="datosSisa/genera_archivos.php" class="btn btn-primary" onclick="waitingDialog.show('Generando Exportación...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar lote </a>
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $mes = rellenarCeros($mes, 2);
    $resEnvios = $sisaLogic->obtenerSisaExportaciones($anio);
    if ($resEnvios['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Fecha proceso</th>
                    <th>Período</th>
                    <th>Archivo Colegiados</th>
                    <th>Archivo Especialistas</th>
                    <th>Path</th>
                    <th style="text-align: center; width: 200px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resEnvios['datos'] as $dato) 
                  {
                      $idSisaExportacion = $dato['idSisaExportacion'];
                      $fechaProceso = $dato['fechaProceso'];
                      $periodoProceso = $dato['periodoProceso'];
                      $nombreArchivoColegiados = $dato['nombreArchivoColegiados'];
                      $nombreArchivoEspecialistas = $dato['nombreArchivoEspecialistas'];
                      $pathArchivo = $dato['pathArchivo'];
                      $borrado = $dato['borrado'];
                      if ($borrado == 1) {
                        $estado = 'BORRADO';
                      } else {
                        $estado = "ACTIVO";
                      }

                  ?>
                    <tr>
                        <td><?php echo $idSisaExportacion;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($fechaProceso, 0, 10));?></td>
                        <td><?php echo $periodoProceso;?></td>
                        <td><?php echo $nombreArchivoColegiados;?></td>
                        <td><?php echo $nombreArchivoEspecialistas;?></td>
                        <td><?php echo $pathArchivo;?></td>
                        <td>
                            <div class="btn-group">
                              <button type="button" class="btn btn-info dropdown-toggle"
                                      data-toggle="dropdown">
                                Archivos <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="datosSisa/genera_archivos.php?id=<?php echo $idSisaExportacion; ?>" class="btn btn-info"  onclick="waitingDialog.show('Generando Exportación...');setTimeout(function () {waitingDialog.hide();}, 500000);">Generar archivos</a>
                                </li>
                                <li>
                                    <a href="datosSisa/descargar_archivos.php?id=<?php echo $idSisaExportacion ?>&archivo=COLEGIADOS" class="btn btn-default">Descargar Archivo Colegiados</a>
                                </li>
                                <li>
                                    <a href="datosSisa/descargar_archivos.php?id=<?php echo $idSisaExportacion ?>&archivo=ESPECIALISTAS" class="btn btn-default">Descargar Archivo Especialistas</a>
                                </li>
                              </ul>
                            </div>
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
        <div class="<?php echo $resEnvios['clase']; ?>" role="alert">
            <span class="<?php echo $resEnvios['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resEnvios['mensaje']; ?></strong></span>
        </div>
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