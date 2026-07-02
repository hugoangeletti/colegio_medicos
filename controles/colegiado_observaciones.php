<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoObservacionLogic.php');
$colegiadoObservacionLogic = new colegiadoObservacionLogic();
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
                    <h4>Observaciones</h4>
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
                <div class="col-md-6 text-right">
                    <a href="colegiado_observaciones_form.php?idColegiado=<?php echo $idColegiado;?>&accion=1" class="btn btn-success glyphicon glyphicon-new-window" title="Editar observaciones">&nbsp;Agregar observaciones</a>
<!--                    <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_observaciones_form.php?idColegiado=<?php echo $idColegiado;?>">
                        <button type="submit"  class="btn btn-lg btn-success" >Nueva observación</button>
                    </form>-->
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
                //if (count($resObservaciones['datos']) > 0){
            ?>
                    <div class="row">&nbsp;</div>
                    <div class="col-md-12">
                        <table  id="tablaObservaciones" class="display">
                            <thead>
                                <tr>
                                    <th style="display: none;">Id</th>
                                    <th>Observaciones</th>
                                    <th>Realizó</th>
                                    <th style="text-align: center; width: 50px;">Fecha Actualizaci&oacute;n</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th style="text-align: center;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resObservaciones['datos'] as $observacion) {
                                    if ($observacion['estado'] == "B") {
                                        $estado = "ANULADO";
                                        $colorTR = 'style="color: red;"';
                                    } else {
                                        $estado = "";
                                        $colorTR = "";
                                    }
                                    ?>
                                    <tr <?php echo $colorTR; ?>>
                                        <td style="display: none"><?php echo $observacion['id'];?></td>
                                        <td><?php echo $observacion['observaciones']; ?></td>
                                        <td><?php echo $observacion['nombreUsuario']; ?></td>
                                        <td><?php echo cambiarFechaFormatoParaMostrar(substr($observacion['fechaCarga'], 0, 10)); ?></td>
                                        <td><?php echo $observacion['tipoObservacion']; ?></td>
                                        <td><?php echo $estado; ?></td>
                                        <td>
                                            <a href="colegiado_observaciones_form.php?idColegiado=<?php echo $idColegiado;?>&accion=3&id=<?php echo $observacion['id']; ?>" class="btn btn-info glyphicon glyphicon-pencil" title="Editar observaciones">&nbsp;Editar</a>
                                            <a href="colegiado_observaciones_adjunto.php?idColegiado=<?php echo $idColegiado;?>&id=<?php echo $observacion['id']; ?>" class="btn btn-warning glyphicon glyphicon-book" title="Adjunto">&nbsp;Adjunto</a>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                //}
            } else {
            ?>
                <div class="<?php echo $resObservaciones['clase']; ?>" role="alert">
                    <span class="<?php echo $resObservaciones['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resObservaciones['mensaje']; ?></strong></span>
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

