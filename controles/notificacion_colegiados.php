<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/notificacionLogic.php');
?>
<script>
$(document).ready(
    function () {
                $('#tablaNotificaciones').DataTable({
                    "iDisplayLength":25,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": true,
                    "bFilter": true,
                    dom: 'T<"clear">lfrtip'
                });
    }
);

function confirmaAccion(accion)
{
    if(confirm('¿Estas seguro de ' + accion + ' esta Notificación?'))
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
$continua = TRUE;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idNotificacion = $_GET['id'];
} else {
    $idNotificacion = NULL;
    $continua = FALSE;
    $mensaje .= 'Falta idNotificacion - ';
}
?>
<div class="panel panel-info">
<div class="panel-heading">
    <h4>
        <b>Colegiados de la Notificación <?php echo $idNotificacion; ?></b>
    </h4>
</div>
<?php
if ($continua) {
?>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">
            </div>
            <div class="col-md-3 text-right">
                <a href="notificacion_lista.php" class="btn btn-info">Volver</a>
            </div>
        </div>
        <br>
        <?php
        $notificacionLogic = new notificacionLogic();
        $resNotificaciones = $notificacionLogic->obtenerNotificacionColegiados($idNotificacion);
        if ($resNotificaciones['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaNotificaciones" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th style="text-align: center;">Matrícula</th>
                                <th style="text-align: left;">Apellido y Nombre</th>
                                <th style="text-align: right;">Total deuda original</th>
                                <th style="text-align: right;">Total deuda actualizada</th>
                                <th style="text-align: center;">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resNotificaciones['datos'] as $dato){
                                $idNotificacionColegiado = $dato['idNotificacionColegiado'];
                                $matricula = $dato['matricula'];
                                $apellidoNombre = $dato['apellido'].' '.$dato['nombre'];
                                $totalCuotaPura = $dato['totalCuotaPura'];
                                $totalActualizado = $dato['totalRecargo'];
                                ?>
                                <tr>
                                    <td><?php echo $idNotificacionColegiado; ?></td>
                                    <td style="text-align: right;"><?php echo $matricula; ?></td>
                                    <td style="text-align: left;"><?php echo $apellidoNombre; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($totalCuotaPura, 2, ',', '.'); ?></td>
                                    <td style="text-align: right;"><?php echo number_format($totalActualizado, 2, ',', '.'); ?></td>
                                    <td style="text-align: center;">
                                        <a href="notificacion_colegiado_detalle.php?id=<?php echo $idNotificacionColegiado; ?>" class="btn btn-primary">Ver Detalle</a>
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
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resNotificaciones['clase']; ?>" role="alert">
                <span class="<?php echo $resNotificaciones['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resNotificaciones['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
<?php
}
?>
</div>
<?php
require_once '../html/footer.php';
