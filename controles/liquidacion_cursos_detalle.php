<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');

$cursos_pdo = new cursos_pdo();
$id_liquidacion = isset($_GET['id']) ? $_GET['id'] : NULL;

if (!$id_liquidacion) {
    echo "Falta el ID de liquidación.";
    exit;
}

// Obtener cabecera y detalle
$resLiq = $cursos_pdo->obtenerLiquidacionPorId($id_liquidacion);
$resDetalle = $cursos_pdo->obtenerDetalleLiquidacion($id_liquidacion);

if ($resLiq['estado'] && $resDetalle['estado']) {
    $liq = $resLiq['datos'];
?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4><b>Detalle de Liquidación N°: <?php echo $id_liquidacion; ?></b></h4>
                </div>
                <div class="col-md-3 text-right">
                    <a href="liquidacion_cursos_listado.php" class="btn btn-primary">Volver al listado</a>
                    <a href="liquidacion_cursos_imprimir.php?id=<?php echo $id_liquidacion; ?>" target="_blank" class="btn btn-info">
                        <i class="glyphicon glyphicon-print"></i> PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="well">
                <div class="row">
                    <div class="col-md-6">
                        <p><b>Curso:</b> <?php echo $liq['NombreCurso']; ?></p>
                        <p><b>Periodo:</b> <?php echo $liq['PeriodoLiquidacion']; ?></p>
                    </div>
                    <div class="col-md-6 text-right">
                        <p><b>Fecha Generación:</b> <?php echo cambiarFechaFormatoParaMostrar(substr($liq['FechaLiquidacion'], 0, 10)); ?></p>
                        <p>
                            <b>Total Cobrado:</b> <span class="label label-success" style="font-size: 1.2em;">$ <?php echo number_format($liq['TotalCobrado'], 2, ',', '.'); ?></span> 
                            <?php 
                            if (!empty($liq['ValorCuotaLiquidacion'])) {
                            ?>
                                <b>Valor cuota liquidación:</b><span class="label label-success" style="font-size: 1.2em;">$ <?php echo number_format($liq['ValorCuotaLiquidacion'], 2, ',', '.'); ?></span>
                            <?php 
                            }
                            ?>
                        </p>
                        <p><b>Total Liquidado:</b> <span class="label label-success" style="font-size: 1.2em;">$ <?php echo number_format($liq['TotalLiquidado'], 2, ',', '.'); ?></span></p>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="active">
                        <th>Asistente</th>
                        <th class="text-center">Cuota</th>
                        <th class="text-center">Fecha Pago</th>
                        <th class="text-right">Importe Cobrado</th>
                        <th class="text-right">Importe Liquidado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cuotaAnterior = null;
                    $total_cobrado = 0;
                    foreach ($resDetalle['datos'] as $item) {
                        // Subtítulo por grupo de cuota
                        if ($item['cuota'] !== $cuotaAnterior) {
                            echo '<tr class="info"><td colspan="5"><b>CUOTA: '.$item['cuota'].'</b></td></tr>';
                            $cuotaAnterior = $item['cuota'];
                        }
                        $total_cobrado += $item['importe'];
                    ?>
                        <tr>
                            <td><?php echo $item['apellidoNombre']; ?></td>
                            <td class="text-center"><?php echo $item['cuota']; ?></td>
                            <td class="text-center"><?php echo cambiarFechaFormatoParaMostrar($item['fechaPago']); ?></td>
                            <td class="text-right">$ <?php echo number_format($item['importe'], 2, ',', '.'); ?></td>
                            <td class="text-right">$ <?php if (!empty($liq['ValorCuotaLiquidacion'])) { 
                                                                $importe_liquidado = $liq['ValorCuotaLiquidacion'];
                                                            } else { 
                                                                $importe_liquidado = $item['importe'];
                                                                
                                                            }
                                                            echo number_format($importe_liquidado, 2, ',', '.');
                                                            ?> 
                                                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="active">
                        <th colspan="3" class="text-right">TOTAL GENERAL:</th>
                        <th class="text-right">$ <?php echo number_format($total_cobrado, 2, ',', '.'); ?></th>
                        <th class="text-right">$ <?php echo number_format($liq['TotalLiquidado'], 2, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php 
} else {
    echo '<div class="alert alert-danger">No se pudo cargar la información de la liquidación.</div>';
}
require_once '../html/footer.php';
?>
