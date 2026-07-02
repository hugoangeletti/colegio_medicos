<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoRecetariosLogic.php');
$colegiadoRecetariosLogic = new colegiadoRecetariosLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaRecetarios').DataTable({
                    "iDisplayLength":10,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmar()
{
	if(confirm('¿Estas seguro de elimiar esta entrega de recetarios?'))
		return true;
	else
		return false;
}
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Recetarios";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $muestraMenuCompleto = TRUE;
        include 'menuColegiado.php';
        ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-3"><h4><b>Recetarios entregados</b></h4></div>
            <div class="col-md-3">            
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_recetarios_alta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-success" >Nueva entrega</button>
                </form>
            </div>
        </div>
        <?php
        //busco las especialidades
        $resRecetarios = $colegiadoRecetariosLogic->obtenerRecetariosPorIdColegiado($idColegiado);
        if ($resRecetarios['estado']){
        ?>
            <div class="row">
                <div class="col-md-12">
                <table id="tablaRecetarios" class="display">
                    <thead>
                        <tr>
                            <th style="display: none;">Id</th>
                            <th>Entrega</th>
                            <th>Fecha Eentrega</th>
                            <th>Especialidad</th>
                            <th>Serie</th>
                            <th>Desde / Hasta</th>
                            <th>Cantidad</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resRecetarios['datos'] as $dato){
                            $idReceta = $dato['idReceta'];
                            $entrega = $dato['entrega'];
                            $fecha = cambiarFechaFormatoParaMostrar($dato['fecha']);
                            $nombreEspecialidad = $dato['nombreEspecialidad'];
                            $serie = $dato['serie'];
                            $desde = $dato['desde'];
                            $hasta = $dato['hasta'];
                            $cantidad = $dato['cantidad'];
                            ?>
                            <tr>
                                <td style="display: none"><?php echo $idReceta;?></td>
                                <td><?php echo $entrega;?></td>
                                <td><?php echo $fecha;?></td>
                                <td><?php echo $nombreEspecialidad;?></td>
                                <td><?php echo $serie;?></td>
                                <td><?php echo $desde.' / '.$hasta;?></td>
                                <td><?php echo $cantidad;?></td>
                                <td style="text-align: center;">
                                    <?php 
                                    $fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 1, 'month', '-');
                                    if ($dato['fecha'] > $fechaLimite) {
                                    ?>
                                        <a href="datosColegiadoRecetas/imprimir_receta.php?idReceta=<?php echo $idReceta; ?>&idColegiado=<?php echo $idColegiado; ?>" 
                                           class="btn btn-info btn-sm" role="button" target="_BLANK">Imprimir</a>                    
                                        <a href="datosColegiadoRecetas/abm_receta.php?idReceta=<?php echo $idReceta; ?>&idColegiado=<?php echo $idColegiado; ?>&accion=2" 
                                           onclick="return confirmar()" class="btn btn-danger btn-sm" role="button">Eliminar</a>                    
                                    <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="<?php echo $resRecetarios['clase']; ?>" role="alert">
                <span class="<?php echo $resRecetarios['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resRecetarios['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
    } else {
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php        
    }
}
require_once '../html/footer.php';
