<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
require_once ('../dataAccess/funcionesPhp.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "asc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": false,
            dom: 'T<"clear">lfrtip'
        });
    });
</script>

<?php
if (isset($_POST['mensaje']))
{
    $conPermiso = TRUE;
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}
if (isset($_POST['fechaDesde']) && $_POST['fechaDesde'] <> "") {
    $fechaDesde = $_POST['fechaDesde'];
} else {
    $fechaDesde = date('Y-m').'-01';
}
if (isset($_POST['fechaHasta']) && $_POST['fechaHasta'] <> "") {
    $fechaHasta = $_POST['fechaHasta'];
} else {
    $fechaHasta = date('Y-m-d');
}
?> 
<div class="panel panel-default">
    <div class="panel-heading text-center"><h4><b>Estadísticas de Mesa de Entrada entre fechas</b></h4></div>
    <div class="panel-body">
        <?php
        $mesaEntradaLogic = new mesaEntradaLogic();
        ?>
        <div class="row">
            <form method="POST" action="mesa_entrada_estadisticas.php">
                <div class="col-md-3">&nbsp;</div>
                <div class="col-md-2">
                    <label for="fechaDesde">Fecha desde *</label>
                    <input class="form-control" type="date" name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde; ?>"/>
                </div>
                <div class="col-md-2">
                    <label for="fechaHasta">Fecha hasta *</label>
                    <input class="form-control" type="date" name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta; ?>"/>
                </div>
                <div class="col-md-1">
                    <br>
                    <button type="submit"  class="btn btn-info " >Buscar</button>
                </div>                
            </form>
        </div>
        <!--<div class="row">&nbsp;</div>-->
        <?php
        $resEstadisticas = $mesaEntradaLogic->obtenerEstadisticasEntreFechas($fechaDesde, $fechaHasta);
        if ($resEstadisticas['estado']) {
        ?>    
            <div class="row">
                <div class="col-md-3">&nbsp;</div>
                <div class="col-md-6">
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th style="display: none;">Id</th>
                                <th>Trámite</th>
                                <th style="text-align: center;">Cantidad</th>
                                <th style="text-align: center;">Ver detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resEstadisticas['datos'] as $dato) {
                                $idTipoMesaEntrada = $dato['idTipoMesaEntrada'];
                                $nombreTipoMesaEntrada = $dato['nombreTipoMesaEntrada'];
                                $cantidad = $dato['cantidad'];
                                $detalle = $dato['detalle'];
                                ?>
                                <tr>
                                    <td style="display: none;"><?php echo $idTipoMesaEntrada;?></td>
                                    <td><?php echo $nombreTipoMesaEntrada;?></td>
                                    <td style="text-align: center;"><?php echo $cantidad;?></td>
                                    <td style="text-align: center;">
                                        <?php 
                                        if (sizeof($detalle) > 0) {
                                        ?>
                                            <button type="submit" class="btn btn-toolbar btn-info" data-toggle="modal" data-target="#detalleModal_<?php echo $idTipoMesaEntrada; ?>">Ver</button>
                                            <div id="detalleModal_<?php echo $idTipoMesaEntrada; ?>" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header alert alert-info">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                            <h4 class="modal-title">Detalle por <?php echo $nombreTipoMesaEntrada; ?></h4>
                                                        </div>              
                                                        <!-- dialog body -->
                                                        <div class="modal-body">
                                                            <table>
                                                                <thead>
                                                                    <th>Nombre</th>
                                                                    <th style="text-align: center;">Cantidad</th>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $total = 0;
                                                                    foreach ($detalle as $datoDetalle) {
                                                                        $nombre = $datoDetalle['nombre'];
                                                                        $cantidad_detalle = $datoDetalle['cantidad_detalle'];
                                                                        $total += $cantidad_detalle;
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo $nombre; ?></td>
                                                                            <td style="text-align: center;"><?php echo $cantidad_detalle; ?></td>
                                                                        </tr>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                            <h5>Total: <b><?php echo $total; ?></b></h5>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
            <div class="<?php echo $resEstadisticas['clase']; ?>" role="alert">
                <span class="<?php echo $resEstadisticas['icono']; ?>" ></span>
                <span><strong><?php echo $resEstadisticas['mensaje']; ?></strong></span>
            </div>
        <?php    
        }    
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
<script language="JavaScript">
    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de ANULAR este registro?'))
            return true;
        else
            return false;
    }    
</script>

