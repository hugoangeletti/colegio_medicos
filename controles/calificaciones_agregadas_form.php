<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/especialidades_pdo.php');

$objEspecialidades = new especialidades_pdo();

// 1. Obtener especialidades aptas para ser padres (Filtrando IdTipoEspecialidad != 3)
$resPadresTodo = $objEspecialidades->obtenerEspecialidades();
$especialidadesCombo = array();
if ($resPadresTodo['estado'] && !empty($resPadresTodo['datos'])) {
    foreach ($resPadresTodo['datos'] as $e) {
        if ($e['idTipoEspecialidad'] != 3) {
            $especialidadesCombo[$e['idEspecialidad']] = $e['nombreEspecialidad'];
        }
    }
}

// 2. Determinar si es Edición o Alta
$isEdit = false;
$idEditar = isset($_GET['id']) ? intval($_GET['id']) : 0;
$datosForm = array('Especialidad' => '', 'Codigo' => '', 'CodigoRes62707' => '');
$padresAsociadosExistentes = array(); // Arreglo para almacenar padres si es edición

if ($idEditar > 0) {
    $isEdit = true;
    $resCalificaciones = $objEspecialidades->obtenerCalificacionesAgergadas();
    if ($resCalificaciones['estado'] && !empty($resCalificaciones['datos'])) {
        foreach ($resCalificaciones['datos'] as $c) {
            if ($c['idEspecialidad'] == $idEditar) {
                $datosForm['Especialidad'] = $c['nombreEspecialidad'];
                $datosForm['Codigo'] = isset($c['codigo']) ? $c['codigo'] : '';
                $datosForm['CodigoRes62707'] = $c['codigoResolucion'];
                break;
            }
        }
    }
    
    // CARGA DE ESPECIALIDADES ASOCIADAS EN EDICIÓN
    $resPadresAsociados = $objEspecialidades->obtenerEspecialidadesAsociadas($idEditar);
    if ($resPadresAsociados['estado'] && !empty($resPadresAsociados['datos'])) {
        $padresAsociadosExistentes = $resPadresAsociados['datos'];
    }
}
?>
<div class="container" style="margin-top: 20px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span class="glyphicon glyphicon-edit"></span> 
                        <?php echo $isEdit ? 'Modificar Calificación Agregada (ID: '.$idEditar.')' : 'Registrar Nueva Calificación Agregada'; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    
                    <form action="datosEspecialidad/abm_calificacion.php" method="POST" id="formCalificaciones">
                        <input type="hidden" name="action" value="<?php echo $isEdit ? 'edit' : 'create'; ?>">
                        <input type="hidden" name="id_calificacion" value="<?php echo $idEditar; ?>">

                        <div class="form-group">
                            <label for="especialidad">Especialidad / Calificación *</label>
                            <input type="text" class="form-control" id="especialidad" name="especialidad" 
                                   value="<?php echo htmlspecialchars($datosForm['Especialidad']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" 
                                           value="<?php echo htmlspecialchars($datosForm['Codigo']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo_res">Código Res. 62707 *</label>
                                    <input type="text" class="form-control" id="codigo_res" name="codigo_res" 
                                           value="<?php echo htmlspecialchars($datosForm['CodigoRes62707']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4>Asociar Especialidades Base (Padres)</h4>
                        
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <select class="form-control" id="cmbPadres">
                                        <option value="">-- Seleccione una Especialidad Padre --</option>
                                        <?php foreach ($especialidadesCombo as $idPadre => $nombrePadre): ?>
                                            <option value="<?php echo $idPadre; ?>"><?php echo htmlspecialchars($nombrePadre); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="btnAsociarPadre" class="btn btn-success btn-block">
                                    <span class="glyphicon glyphicon-link"></span> Asociar
                                </button>
                            </div>
                        </div>

                        <label style="margin-top: 10px;">Especialidades Asociadas:</label>
                        <ul class="list-group" id="listaPadresAsociados">
                            <?php if (empty($padresAsociadosExistentes)): ?>
                                <li class="list-group-item item-vacio text-muted">Ninguna especialidad padre asociada todavía.</li>
                            <?php else: ?>
                                <?php foreach ($padresAsociadosExistentes as $padre): ?>
                                    <li class="list-group-item" id="padre_row_<?php echo $padre['idEspecialidad']; ?>">
                                        <input type="hidden" name="padres[]" value="<?php echo $padre['idEspecialidad']; ?>">
                                        <span class="glyphicon glyphicon-tag text-primary"></span> &nbsp; <?php echo htmlspecialchars($padre['nombreEspecialidad']); ?>
                                        <button type="button" class="btn btn-danger btn-xs pull-right btn-eliminar-relacion" data-id="<?php echo $padre['idEspecialidad']; ?>">
                                            <span class="glyphicon glyphicon-trash"></span> Quitar
                                        </button>
                                        <div class="clearfix"></div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>

                        <hr>
                        <div class="form-group text-right">
                            <a href="calificaciones_agregadas_listado.php" class="btn btn-default">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Cambios
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar el arreglo JS dinámicamente con los IDs ya asociados si es una edición
    var padresAgregadosIds = [
        <?php 
        if (!empty($padresAsociadosExistentes)) {
            $ids = array();
            foreach ($padresAsociadosExistentes as $p) {
                $ids[] = '"' . $p['idEspecialidad'] . '"';
            }
            echo implode(',', $ids);
        }
        ?>
    ];

    $('#btnAsociarPadre').on('click', function() {
        var idSelected = $('#cmbPadres').val();
        var nombreSelected = $('#cmbPadres option:selected').text();

        if (idSelected === "") {
            alert("Por favor seleccione una especialidad válida.");
            return;
        }

        if (padresAgregadosIds.indexOf(idSelected) !== -1) {
            alert("Esta especialidad padre ya se encuentra asociada.");
            return;
        }

        $('.item-vacio').remove();
        padresAgregadosIds.push(idSelected);

        var nuevoItem = '<li class="list-group-item" id="padre_row_' + idSelected + '">' +
            '<input type="hidden" name="padres[]" value="' + idSelected + '">' +
            '<span class="glyphicon glyphicon-tag text-primary"></span> &nbsp; ' + nombreSelected +
            '<button type="button" class="btn btn-danger btn-xs pull-right btn-eliminar-relacion" data-id="' + idSelected + '">' +
                '<span class="glyphicon glyphicon-trash"></span> Quitar' +
            '</button>' +
            '<div class="clearfix"></div>' +
        '</li>';

        $('#listaPadresAsociados').append(nuevoItem);
        $('#cmbPadres').val("");
    });

    $(document).on('click', '.btn-eliminar-relacion', function() {
        var idEliminar = $(this).data('id').toString();
        
        padresAgregadosIds = $.grep(padresAgregadosIds, function(value) {
            return value !== idEliminar;
        });

        $('#padre_row_' + idEliminar).remove();

        if (padresAgregadosIds.length === 0) {
            $('#listaPadresAsociados').html('<li class="list-group-item item-vacio text-muted">Ninguna especialidad padre asociada todavía.</li>');
        }
    });
});
</script>
<?php 
require_once '../html/footer.php';
?>