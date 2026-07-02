<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/especialidades_pdo.php');

$objEspecialidades = new especialidades_pdo();

$isEdit = false;
$idEditar = isset($_GET['id']) ? intval($_GET['id']) : 0;
$datosForm = array('Especialidad' => '', 'Codigo' => '', 'CodigoRes62707' => '', 'IdTipoEspecialidad' => '');

if ($idEditar > 0) {
    $isEdit = true;
    $resEspecialidades = $objEspecialidades->obtenerEspecialidades();
    if ($resEspecialidades['estado'] && !empty($resEspecialidades['datos'])) {
        foreach ($resEspecialidades['datos'] as $c) {
            if ($c['idEspecialidad'] == $idEditar) {
                $datosForm['Especialidad'] = $c['nombreEspecialidad'];
                $datosForm['Codigo'] = isset($c['codigo']) ? $c['codigo'] : '';
                $datosForm['CodigoRes62707'] = $c['codigoResolucion'];
                $datosForm['IdTipoEspecialidad'] = $c['idTipoEspecialidad'];
                break;
            }
        }
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
                        <?php echo $isEdit ? 'Modificar Especialidad (ID: '.$idEditar.')' : 'Registrar Nueva Especialidad'; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    
                    <form action="datosEspecialidad/abm_especialidad.php" method="POST" id="formEspecialidad">
                        <input type="hidden" name="action" value="<?php echo $isEdit ? 'edit' : 'create'; ?>">
                        <input type="hidden" name="id_especialidad" value="<?php echo $idEditar; ?>">

                        <div class="form-group">
                            <label for="especialidad">Especialidad *</label>
                            <input type="text" class="form-control" id="especialidad" name="especialidad" 
                                   value="<?php echo htmlspecialchars($datosForm['Especialidad']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" 
                                           value="<?php echo htmlspecialchars($datosForm['Codigo']); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo_res">Código Res. 62707 *</label>
                                    <input type="text" class="form-control" id="codigo_res" name="codigo_res" 
                                           value="<?php echo htmlspecialchars($datosForm['CodigoRes62707']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="codigo_res">Tipo especialidad *</label>
                                    <select class="form-control" id="id_tipo_especialidad" name="id_tipo_especialidad" required>
                                        <option value="">-- Seleccione un Tipo --</option>
                                        <option value="1" <?php echo ($datosForm['IdTipoEspecialidad'] == 1) ? 'selected' : '' ?>>BASICAS</option>
                                        <option value="2" <?php echo ($datosForm['IdTipoEspecialidad'] == 2) ? 'selected' : '' ?>>DEPENDIENTES</option>
                                        <option value="4" <?php echo ($datosForm['IdTipoEspecialidad'] == 4) ? 'selected' : '' ?>>EXPEDIDO POR MINISTERIO DE SALUD DE LA NACIÓN</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group text-right">
                            <a href="especialidades_listado.php" class="btn btn-default">Cancelar</a>
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

<?php 
require_once '../html/footer.php';
?>