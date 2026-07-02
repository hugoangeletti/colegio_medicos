<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
?>
<script>
$(document).ready(
    function () {
                $('#tablaDetalleCaja').DataTable({
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

function confirmaAnular()
{
    if(confirm('¿Estas seguro de ANULAR este RECIBO?'))
        return true;
    else
        return false;
}
</script>
<?php
$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCajaDiariaMovimiento = $_GET['id'];
    $resRecibo = $cajaDiariaLogic->obtenerCajaDiariaMovimientoPorId($idCajaDiariaMovimiento);
    if ($resRecibo['estado']) {
        $recibo = $resRecibo['datos'];
        $idCajaDiaria = $recibo['idCajaDiaria'];
        $fecha = $recibo['fechaPago'];
        $monto = $recibo['monto'];
        $estadoRecibo = $recibo['estadoRecibo'];
        $idColegiado = $recibo['idColegiado'];
        $tipoRecibo = $recibo['tipoRecibo'];
        $numeroRecibo = $recibo['numeroRecibo'];
        $nombreUsuario = $recibo['usuario'];
    } else {
        $continua = FALSE;
        $mensaje .= $resRecibo['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCajaDiariaMovimiento";
}

if ($continua) {
    if(isset($_GET['mov']) && $_GET['mov'] == '1') {
        $link_volver = "cajadiaria_movimientos.php?id=".$idCajaDiaria;
    } else {
        $link_volver = "cajadiaria.php";
    }
?>
    <div class="panel panel-info">
    <div class="panel-heading">
        <h4>
            <b>Recibo <?php echo $tipoRecibo.'-'.$numeroRecibo; ?></b>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="<?php echo $link_volver; ?>" class="btn btn-info">Volver </a>
        </h4>
    </div>
    <?php
    if ($continua) {
        $resDetalle = $cajaDiariaLogic->obtenerCajaDiariaMovimientoDetallePorId($idCajaDiariaMovimiento);
        if ($resDetalle['estado']) {
        ?>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <table  id="tablaDetalleCaja" class="display">
                            <thead>
                                <tr>
                                    <th style="display: none;">Id</th>
                                    <th>Código de pago</th>
                                    <th>Detalle</th>
                                    <th style="text-align: center;">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resDetalle['datos'] as $dato){
                                    $idCajaDiariaMovimientoDetalle = $dato['idCajaDiariaMovimientoDetalle'];
                                    $indice = $dato['indice'];
                                    $monto = $dato['monto'];
                                    $codigoPago = $dato['codigoPago'];
                                    $tipoPago = $dato['tipoPago'];
                                    $detalle = $dato['detalle'];
                                    if (isset($dato['periodo']) && $dato['periodo'] <> "" && $dato['periodo'] <> "0") {
                                        $periodo = $dato['periodo'];
                                        $detalle .= $periodo;
                                    }
                                    if (isset($dato['cuota']) && $dato['cuota'] <> "" && $dato['cuota'] <> "0") {
                                        $cuota = $dato['cuota'];
                                        if ($detalle == "") {
                                            $detalle = 'Cuota: '.$cuota;
                                        } else {
                                            $detalle .= ' - '.$cuota;                                            
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td style="display: none;"><?php echo $idCajaDiariaMovimientoDetalle; ?></td>
                                        <td><?php echo $codigoPago.'-'.$tipoPago;?></td>
                                        <td><?php echo $detalle;?></td>
                                        <td style="text-align: center;"><?php echo $monto;?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="alert alert-danger" role="alert">
                <span><strong><?php echo $resDetalle['mensaje']; ?></strong></span>
            </div>        
        <?php
        }
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
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>        
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="cajadiaria.php">
                <button type="submit"  class="btn btn-info" >Volver</button>
            </form>
        </div>
    </div>
<?php
}
?>
<?php 
require_once '../html/footer.php';
