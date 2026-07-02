<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
set_time_limit(0);
$continua = TRUE;
if (isset($_FILES['archivoAdjuntar'])) {
	$nombreAdjunto = $_FILES['archivoAdjuntar']['name'];
} else {
	$nombreAdjunto = NULL;
}
?>
<div class="panel panel-primary">
	<div class="panel-heading"><h4><b>Listado de médicos por entidad (Archivo)</b></h4></div>
	<div class="panel-body">
		<?php
		if (!isset($nombreAdjunto)) {
        ?>
	        <form id="adjuntar" name="adjuntar" enctype="multipart/form-data" method="POST" action="listado_por_entidad.php">
	            <input type="file" id="archivoAdjuntar" name="archivoAdjuntar">
	            <button type="submit" class="btn btn-primary">Procesar </button>
	        </form>
        <?php
        } else {
	        if($_FILES['archivoAdjuntar']['name'] != ""){
	            $fileName = explode(".", $_FILES['archivoAdjuntar']['name']);

	            $nombreAdjunto = $fileName[0];
	            $extensionAdjunto = $fileName[1];
	            $tipoArchivo = $_FILES['archivoAdjuntar']['type'];
	            $path = '../archivos/entidad';
	            $continuaArc = TRUE;
	            $mensaje = "";
	            $a_Types = array("txt", "csv", "TXT", "CSV");
	            $archivo = new SplFileInfo('foo.txt');
				$extensionAdjunto = $archivo->getExtension();

	            if (in_array($extensionAdjunto, $a_Types)) { 
	                $nombreAdjunto = $_FILES['archivoAdjuntar']['name'];
                    //guardo el archivo en el path
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    if (file_exists($path.'/'.$nombreAdjunto)) {
						unlink($path.'/'.$nombreAdjunto);
					}
                    move_uploaded_file($_FILES['archivoAdjuntar']['tmp_name'], $path.'/'.$nombreAdjunto);
	            } else {
	                $mensaje .= 'ERROR AL ADJUNTAR EL TIPO DE ARCHIVO, DEBE SER DEL TIPO "CSV", "TXT" - ';
	                $continuaArc = FALSE;
	            }
			}
			if (!$continuaArc) {
			?>
				<div class="ocultarMensaje">
                    <div class="alert alert-danger" role="alert">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                        <span><?php echo $mensaje; ?></span>
                    </div>
                </div>
			<?php
			} else {
				//procesa el archivo
				?>
				<table class="table">
					<thead>
						<th>Matrícula</th>
						<th>Apellido Nombre Solicitado</th>
						<th>Apellido Nombre</th>
						<th>Estado Matricular</th>
						<th>Estado Tesorería</th>
						<th>Fecha Alta</th>
					</thead>
					<tbody>
					<?php
					$fp = fopen ($path.'/'.$nombreAdjunto,"r");
					while ($data = fgetcsv ($fp, 1000, ";")) {
						$num = count ($data);
						$apellidoNombreSolicitado = $data[0];
						$matricula = trim($data[1]);
						?>
						<tr>
							<td><?php echo $matricula; ?></td>
							<td><?php echo $apellidoNombreSolicitado; ?></td>
							<?php
							$colegiadoLogic = new colegiadoLogic();
							$resColegiado = $colegiadoLogic->obtenerIdColegiado($matricula);
							if ($resColegiado['estado']) {
								$idColegiado = $resColegiado['idColegiado'];
								$resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
								if ($resColegiado['estado']) {
									$colegiado = $resColegiado['datos'];
									$estadoMatricular = trim($colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado'])).' - '.$colegiado['movimientoCompleto'];
	                                $estadoTesoreria = "";
									$resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, $_SESSION['periodoActual']);
		                            if ($resEstadoTeso['estado']){
		                                $codigo = $resEstadoTeso['codigoDeudor'];
		                                $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
		                                if ($resEstadoTesoreria['estado']){
		                                    $estadoTesoreria .= $resEstadoTesoreria['estadoTesoreria'];
		                                } else {
		                                    $estadoTesoreria.= $resEstadoTesoreria['mensaje'];
		                                }
		                            } else {
		                                $estadoTesoreria = $resEstadoTeso['mensaje'];
		                            }
								
									$fechaAlta = cambiarFechaFormatoParaMostrar($colegiado['fechaMatriculacion']);
									?>
									<td><?php echo $idColegiado.' - '.trim($colegiado['apellido']).' '.trim($colegiado['nombre']); ?></td>
									<td><?php echo $estadoMatricular; ?></td>
									<td><?php echo $estadoTesoreria; ?></td>
									<td><?php echo $fechaAlta; ?></td>
								<?php
								} else {
								?>
									<td>Colegiado no encontrado</td>
									<td></td>
									<td></td>
									<td></td>
								<?php
								}
							} else {
							?>
								<td>Matrícula no encontrada</td>
								<td></td>
								<td></td>
								<td></td>
							<?php
							}
						}
						?>
					</tbody>
				</table>
				<div class="row">
					<form  method="POST" action="datosColegiadoCertificado/genera_certificado_archivo.php" target="_BLANK">
						<div class="col-md-4">
		                    <label>Tipo de Certificado *</label>
		                    <select class="form-control" id="idTipoCertificado" name="idTipoCertificado" required="" >
		                        <option value="">Seleccione Tipo de Certificado</option>
		                        <option value="6">A TODO EFECTO</option>
		                        <option value="9">FAP Completo</option>
		                        <option value="10">CAJA DE PREVISION Y SEGURO MEDICO</option>
		                        <option value="10">AGREMIACION MEDICA PLATENSE</option>
		                    </select>
		                </div>
		                <div class="col-md-4">
                                <label>Para ser presentado *</label>
                                <input class="form-control" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" type="text" id="presentado" name="presentado" placeholder="Ingrese texto. Ejemplo: QUIEN CORRESPONDA" required=""/>
                            </div>
		                <div class="col-md-1">
		                	<label>&nbsp;</label>
	                        <button type="submit" class="btn btn-success" name='caja' id='caja' onclick="waitingDialog.show('GENERANDO CERTIFICADOS...');setTimeout(function () {waitingDialog.hide();}, 80000);">Genera Certificados </button>
	                        <input type="hidden" id="archivo" name="archivo" value="<?php echo $path.'/'.$nombreAdjunto; ?>">
                        </div>
                    </form>
				</div>
			<?php				
			}
		}
		?>
	    <div class="row">
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