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
$notificacionLogic = new notificacionLogic();
if (isset($_GET['matricula'])) {
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idNotificacion = $_GET['id'];
        $resNotificacion = $notificacionLogic->obtenerNotificacionColegiadoPorIdNotificacion($idNotificacion);
        if ($resNotificacion['estado']) {
            $notificacionColegiado = $resNotificacion['datos'];
            $idNotificacionColegiado = $notificacionColegiado['idNotificacionColegiado'];
        } else {
            $idNotificacion = NULL;
            $continua = FALSE;
            $mensaje .= $resNotificacion['mensaje'];
        }
    } else {
        $idNotificacion = NULL;
        $continua = FALSE;
        $mensaje .= 'Falta idNotificacion - ';
    }
} else {
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idNotificacionColegiado = $_GET['id'];
        $resNotificacion = $notificacionLogic->obtenerNotificacionColegiadoPorId($idNotificacionColegiado);
        if ($resNotificacion['estado']) {
            $notificacionColegiado = $resNotificacion['datos'];
            $idNotificacion = $notificacionColegiado['idNotificacion'];
        } else {
            $idNotificacion = NULL;
            $continua = FALSE;
            $mensaje .= $resNotificacion['mensaje'];
        }
    } else {
        $idNotificacionColegiado = NULL;
        $continua = FALSE;
        $mensaje .= 'Falta idNotificacion - ';
    }
}
?>
<div class="panel panel-info">
<?php
if ($continua) {
    $apellidoNombre = $notificacionColegiado['apellido'].' '.$notificacionColegiado['nombre'];
    $matricula = $notificacionColegiado['matricula'];
?>
    <div class="panel-heading">
        <h4>
            <b>Detalle de cuotas adeudadas </b>
        </h4>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">
                <h4><b>Notificación de deuda de <?php echo $apellidoNombre; ?> - Matrícula <?php echo $matricula; ?></b></h4>
            </div>
            <div class="col-md-3 text-right">
                <?php 
                if (isset($_GET['matricula'])) {
                ?>
                    <a href="notificacion_lista.php" class="btn btn-info">Volver</a>
                <?php
                } else {
                ?>
                    <a href="notificacion_colegiados.php?id=<?php echo $idNotificacion; ?>" class="btn btn-info">Volver</a>
                <?php 
                }
                ?>
            </div>
        </div>
        <br>
        <?php
        $notificacionLogic = new notificacionLogic();
        $resCuotas = $notificacionLogic->obtenerNotificacionColegiadoDetallePorId($idNotificacionColegiado);
        if ($resCuotas['estado']) {
            ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaNotificaciones" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th style="text-align: left;">Tipo de deuda</th>
                                <th style="text-align: center;">Cuota</th>
                                <th style="text-align: right;">Deuda original</th>
                                <th style="text-align: center;">Vencimiento original</th>
                                <th style="text-align: right;">Deuda actualizada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resCuotas['datos'] as $dato){
                                $idNotificacionColegiadoDeuda = $dato['idNotificacionColegiadoDeuda'];
                                $idColegiadoDeudaAnualCuota = $dato['idColegiadoDeudaAnualCuota'];
                                $valorActualizado = $dato['valorActualizado'];
                                if (isset($idColegiadoDeudaAnualCuota) && $idColegiadoDeudaAnualCuota <> "") {
                                    //es cuota de colegiacion
                                    $tipoCuota = "Cuota de colegiación";
                                    $cuota = $dato['periodo'].' - '.rellenarCeros($dato['cuota'], 2);
                                    $importe = $dato['importe'];
                                    $fechaVencimiento = cambiarFechaFormatoParaMostrar($dato['fechaVencimiento']);
                                } else {
                                    $idPlanPagosCuota = $dato['idPlanPagosCuota'];
                                    if (isset($idPlanPagosCuota) && $idPlanPagosCuota <> "") {
                                        //es cuota de colegiacion
                                        $tipoCuota = "Cuota de plan de pagos";
                                        $cuota = rellenarCeros($dato['cuotaPP'], 2);
                                        $importe = $dato['importePP'];
                                        $fechaVencimiento = cambiarFechaFormatoParaMostrar($dato['fechaVencimientoPP']);
                                    } else {
                                        $tipoCuota = "Sin discriminar";
                                        $cuota = rellenarCeros($dato['cuota'].$dato['cuotaPP'], 2);
                                        $importe = $dato['importe'].$dato['importePP'];
                                        $fechaVencimiento = cambiarFechaFormatoParaMostrar($dato['fechaVencimiento'].$dato['fechaVencimientoPP']);
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?php echo $idNotificacionColegiadoDeuda; ?></td>
                                    <td><?php echo $tipoCuota; ?></td>
                                    <td style="text-align: center;"><?php echo $cuota; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($importe, 2, ',', '.'); ?></td>
                                    <td style="text-align: center;"><?php echo $fechaVencimiento; ?></td>
                                    <td style="text-align: right;"><?php echo number_format($valorActualizado, 2, ',', '.'); ?></td>
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
            <div class="<?php echo $resCuotas['clase']; ?>" role="alert">
                <span class="<?php echo $resCuotas['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resCuotas['mensaje']; ?></strong></span>
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
