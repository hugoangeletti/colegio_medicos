<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/verificacionColegiadoLogic.php');
$verificacionColegiadoLogic = new verificacionColegiadoLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaObservaciones').DataTable({
                    "iDisplayLength":7,
                     "order": [[ 0, "desc" ]],
                     "bLengthChange": false,
                    "bFilter": false,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    }
                });
                
                $('#tablaAutoprescripcion').DataTable({
                    "iDisplayLength":7,
                     "order": [[ 0, "desc" ]],
                     "bLengthChange": false,
                    "bFilter": false,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    }
                });
    }
);
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Novedades</h4>
                </div>
                <div class="col-md-3 text-left">
                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
                </div>
                <div class="col-md-4">
                    <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                </div>
            </div>
            <?php
            if (isset($_POST['mensaje'])) {
            ?>
               <div class="ocultarMensaje"> 
                   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
               </div>
            <?php
            }
            //busco observaciones
            $resObservaciones = $colegiadoObservacionLogic->obtenerColegiadoObservaciones($idColegiado);
            if ($resObservaciones['estado']){
                if (count($resObservaciones['datos']) > 0){
            ?>
                    <div class="col-md-12 alert alert-success">
                        <div class="col-md-10"><h4>Observaciones</h4></div>
                        <div class="col-md-2 text-right">
                            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_novedades_form.php?idColegiado=<?php echo $idColegiado;?>">
                                <button type="submit"  class="btn btn-success" >Nueva observación</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <table  id="" class="display">
                            <thead>
                                <tr>
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th style="text-align: center;">Observaciones</th>
                                    <th style="text-align: center;">Realizó</th>
                                    <th style="text-align: center;">Fecha Actualizaci&oacute;n</th>
                                    <th style="text-align: center;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resObservaciones['datos'] as $observacion) {
                                    ?>
                                    <tr>
                                        <td style="display: none"><?php echo $observacion['id'];?></td>
                                        <td><?php echo $observacion['observaciones']; ?></td>
                                        <td><?php echo $observacion['nombreUsuario']; ?></td>
                                        <td><?php echo cambiarFechaFormatoParaMostrar($observacion['fechaCarga']); ?></td>
                                        <td>Modificar</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                }
            } else {
            ?>
                <div class="<?php echo $resObservaciones['clase']; ?>" role="alert">
                    <span class="<?php echo $resObservaciones['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resObservaciones['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            //busco autoprescripcion
            $resAutoprescripcion = $verificacionColegiadoLogic->obtenerColegiadoAutoprescripcion($idColegiado);
            if ($resAutoprescripcion['estado']){
                if (count($resAutoprescripcion['datos']) > 0){
            ?>
                    <div class="col-md-12 alert-warning"><h4>Autoprescripción</h4></div>
                    <div class="col-md-12">
                        <table width="100%" id="" class="display">
                            <thead>
                                <tr>
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th style="text-align: center;">Fecha</th>
                                    <th style="text-align: center;">Autorizado</th>
                                    <th style="text-align: center;">Documento</th>
                                    <th style="text-align: center;">Parentezco</th>
                                    <th style="text-align: center;">Autorizado</th>
                                    <th style="text-align: center;">Documento</th>
                                    <th style="text-align: center;">Parentezco</th>
                                    <th style="text-align: center;">Realizó</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resAutoprescripcion['datos'] as $autoprescripcion) {
                                    ?>
                                    <tr>
                                        <td style="display: none"><?php echo $autoprescripcion['id'];?></td>
                                        <td><?php echo cambiarFechaFormatoParaMostrar($autoprescripcion['fechaIngreso']); ?></td>
                                        <td><?php echo $autoprescripcion['autorizado1']; ?></td>
                                        <td><?php echo $autoprescripcion['documento1']; ?></td>
                                        <td><?php echo $autoprescripcion['parentezco1']; ?></td>
                                        <td><?php echo $autoprescripcion['autorizado2']; ?></td>
                                        <td><?php echo $autoprescripcion['documento2']; ?></td>
                                        <td><?php echo $autoprescripcion['parentezco2']; ?></td>
                                        <td><?php echo $autoprescripcion['nombreUsuario']; ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                }
            } else {
            ?>
                <div class="<?php echo $resAutoprescripcion['clase']; ?>" role="alert">
                    <span class="<?php echo $resAutoprescripcion['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resAutoprescripcion['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
            ?>
        </div>
    </div>
<?php
}
?>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';

