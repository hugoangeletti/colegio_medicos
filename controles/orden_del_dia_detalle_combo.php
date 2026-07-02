<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/ordenDelDiaLogic.php');
$ordenDelDiaLogic = new ordenDelDiaLogic();
?>
<script>
$(document).ready(function () {
    $('#tablaOrdenada').DataTable({
        "iDisplayLength":10,
        "language": {
            "url": "../public/lang/esp.lang"
        },
        "order": [[ 0, "desc" ]],
    });
});
            
function confirmar()
{
    if(confirm('¿Estas seguro de elimiar orden de día?'))
        return true;
    else
        return false;
}
</script>
<?php
$continua = TRUE;

if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}   
if (isset($_POST['tipoPlanilla']) && $_POST['tipoPlanilla'] <> "") {
    $tipoPlanillaOrigen = $_POST['tipoPlanilla'];
} else {
    $tipoPlanillaOrigen = NULL;
}

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idOrdenDia = $_GET['id'];
    $resOrden = $ordenDelDiaLogic->obtenerOrdenDelDiaPorId($idOrdenDia);
    if ($resOrden['estado']) {
        $ordenDelDia = $resOrden['datos'];
        $fechaOrden = $ordenDelDia['fecha'];
        $numero = $ordenDelDia['numero'];
        $periodoOrden = $ordenDelDia['periodo'];
        $fechaDesde = $ordenDelDia['fechaDesde'];
        $fechaHasta = $ordenDelDia['fechaHasta'];
        $observaciones = $ordenDelDia['observaciones'];
        $estadoOrdenDia = $ordenDelDia['estado'];
        switch ($estadoOrdenDia) {
            case 'A':
                $estadoOrdenDiaDetalle = 'Abierto';
                break;
            
            case 'B':
                $estadoOrdenDiaDetalle = 'Borrado';
                break;
            
            case 'C':
                $estadoOrdenDiaDetalle = 'Cerrado';
                break;
            
            default:
                // code...
                break;
        }

        //verificamos si tiene detalle generado, sino lo genero
        if (!$ordenDelDiaLogic->ordenDelDiaConDetalle($idOrdenDia)) {
            $resOrdenDetalle = $ordenDelDiaLogic->obtenerMovimientosParaOrdenDia($fechaDesde, $fechaHasta);
            if ($resOrdenDetalle['estado']) {
                $ordenDiaDetalle = $resOrdenDetalle['datos'];
                foreach ($ordenDiaDetalle as $detalle) {
                    $idMesaEntrada = $detalle['idMesaEntrada'];
                    if ($detalle['idTipoMesaEntrada'] == 1 || $detalle['idTipoMesaEntrada'] == 7) {
                        //es movimiento matricular o autoprescripcion
                        $tipoPlanilla = 4;
                    } else {
                        $tipoPlanilla = 2;
                    }
                    $resultado = $ordenDelDiaLogic->agregarOrdenDelDiaDetalle($idOrdenDia, $tipoPlanilla, $idMesaEntrada);
                    if (!$resultado['estado']) {
                        //si da error, borro los registros que pudo cargar en el detalle
                        $ordenDelDiaLogic->borrarDetallePorIdOrdenDia($idOrdenDia);
                        $continua = FALSE;
                        $mensaje = $resultado['mensaje'];
                        break;
                    }
                }
            } else {
                $continua = FALSE;
                $mensaje = $resOrdenDetalle['mensaje'];
            }
        }
        
        $resOrdenDetalle = $ordenDelDiaLogic->ordenDelDiaDetallePorIdOrdenDia($idOrdenDia, 1);
        if ($resOrdenDetalle['estado']) {
            $ordenDelDiaTipo_1 = $resOrdenDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resOrdenDetalle['mensaje'];
        }
        $resOrdenDetalle = $ordenDelDiaLogic->ordenDelDiaDetallePorIdOrdenDia($idOrdenDia, 2);
        if ($resOrdenDetalle['estado']) {
            $ordenDelDiaTipo_2 = $resOrdenDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resOrdenDetalle['mensaje'];
        }
        $resOrdenDetalle = $ordenDelDiaLogic->ordenDelDiaDetallePorIdOrdenDia($idOrdenDia, 3);
        if ($resOrdenDetalle['estado']) {
            $ordenDelDiaTipo_3 = $resOrdenDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resOrdenDetalle['mensaje'];
        }
        $resOrdenDetalle = $ordenDelDiaLogic->ordenDelDiaDetallePorIdOrdenDia($idOrdenDia, 4);
        if ($resOrdenDetalle['estado']) {
            $ordenDelDiaTipo_4 = $resOrdenDetalle['datos'];
        } else {
            $continua = FALSE;
            $mensaje = $resOrdenDetalle['mensaje'];
        }
        //busco si hay entre las fechas de la orden del dia que no esten asignadas a ningun tipoPlanilla
        $resOrdenDetalle = $ordenDelDiaLogic->obtenerMovimientosParaOrdenDia($fechaDesde, $fechaHasta);
        if ($resOrdenDetalle['estado']) {
            $ordenDiaDetalleSinTipo = $resOrdenDetalle['datos'];
        } else {
            $ordenDiaDetalleSinTipo = array();
        }
    } else {
        $continua = FALSE;
        $mensaje = $resOrden['mensaje'];
    }
} else {
    $idOrdenDia = NULL;
    $continua = FALSE;
    $mensaje = "ACCESO ERRONEO";
}

if ($continua) {
?> 
    <div class="row alert alert-info">
        <div class="col-md-6">
            <h4>Listado de Orden del día <b><?php echo cambiarFechaFormatoParaMostrar($fechaOrden); ?></b> Nº <b><?php echo $numero.'/'.$periodoOrden; ?></b> (<?php echo $estadoOrdenDiaDetalle; ?>)</h4>
        </div>
        <div class="col-md-6 text-right">
            <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="orden_del_dia_listado.php">
                <button type="submit"  class="btn btn-primary">Volver a ordenes de día</button>
                <input type="hidden" name="estadoOrdenDia" value="<?php echo $estadoOrdenDia; ?>">
                <input type="hidden" name="anio" value="<?php echo substr($fechaOrden, 0, 4); ?>">
            </form>
        </div>
    </div>
    <div class="col-md-12">
        <?php 
        if ($estadoOrdenDia == 'A') {
        ?>
            <div class="row">
                <div class="col-xs-2">&nbsp;</div>
                <div class="col-md-10 text-right">
                    <form  method="POST" action="datosOrdenDelDia/abm_orden_del_dia.php">
                        <button type="submit" class="btn btn-success" name='cerrar' id='name'>Cerrar orden del día </button>
                        <input type="hidden" id="accion" name="accion" value="5">
                        <input type="hidden" id="idOrdenDia" name="idOrdenDia" value="<?php echo $idOrdenDia; ?>">
                    </form>
                </div>
            </div>
        <?php 
        }
        ?>
        <div class="row">&nbsp;</div>

        <div class = "responsive">
            <div class="panel-group" id="accordion">
            <?php 
            $i = 1;
            while ($i < 6) {
                switch ($i) {
                    case 1:
                        $titulo = "<b>1. - Planilla de Asuntos Internos. (items: ".sizeof($ordenDelDiaTipo_1).")</b>";
                        $ordenDelDiaTipo = $ordenDelDiaTipo_1;
                        break;
                    
                    case 2:
                        $titulo = "<b>2. - Planilla de Notas Recibidas. (items: ".sizeof($ordenDelDiaTipo_2).")</b>";
                        $ordenDelDiaTipo = $ordenDelDiaTipo_2;
                        break;
                    
                    case 3:
                        $titulo = "<b>3. - Archivado - Descarta el Trámite Definitivamente.  (items: ".sizeof($ordenDelDiaTipo_3).")</b>";
                        $ordenDelDiaTipo = $ordenDelDiaTipo_3;
                        break;
                    
                    case 4:
                        $titulo = "<b>4. - Planilla de movimientos matriculares.  (items: ".sizeof($ordenDelDiaTipo_4).")</b>";
                        $ordenDelDiaTipo = $ordenDelDiaTipo_4;
                        break;
                    
                    case 5:
                        $titulo = "<b>Sin asignar.  (items: ".sizeof($ordenDiaDetalleSinTipo).")</b>";
                        $ordenDelDiaTipo = $ordenDiaDetalleSinTipo;
                        break;
                    
                    default:
                        // code...
                        break;
                }
            ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <!--<h4 class="panel-title">-->
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo $i; ?>"><h4><?php echo $titulo; ?></h4></a>
                        <!--</h4>-->
                            <?php 
                            if ($i <> 5 && $i <> 3) {
                                if (sizeof($ordenDelDiaTipo) > 0) {
                            ?>
                                    <a class=""href="orden_del_dia_imprimir.php?id=<?php echo $idOrdenDia; ?>&tipo=<?php echo $i; ?>" target="_BLANK">Imprimir planilla</a>
                            <?php 
                                }
                            }
                            ?>
                    </div>
                    <div id="collapse_<?php echo $i; ?>" class="panel-collapse collapse <?php if ($i == $tipoPlanillaOrigen) { ?> in <?php } ?>">
                        <div class="panel-body">
                            <?php
                            if (sizeof($ordenDelDiaTipo) > 0) {
                            ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="display: none;">Id</th>
                                        <th>Fecha de Trámite</th>
                                        <th>Tipo de Trámite</th>
                                        <th>Colegiado / Remitente</th>
                                        <?php 
                                        if ($i == '4') {
                                        ?>
                                            <th>Movimiento</th>
                                        <?php
                                        } else {
                                        ?>
                                            <th>Tema / Observaciones</th>
                                        <?php 
                                        }
                                        if ($estadoOrdenDia == 'A') {
                                        ?>
                                            <th style="width: 200px;">Derivar</th>
                                        <?php 
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($ordenDelDiaTipo as $dato) {
                                        $idMesaEntrada = $dato['idMesaEntrada'];
                                        if (isset($dato['idOrdenDiaDetalle'])) {
                                            $idOrdenDiaDetalle = $dato['idOrdenDiaDetalle'];
                                        } else {
                                            $idOrdenDiaDetalle = NULL;
                                        }
                                        $fechaIngreso = cambiarFechaFormatoParaMostrar($dato['fechaIngreso']);
                                        $tipoTramite = $dato['nombreMovimiento'];
                                        if (isset($dato['tipoPlanilla'])) {
                                            $tipoPlanilla = $dato['tipoPlanilla'];
                                        } else {
                                            $tipoPlanilla = NULL;
                                        }
                                        $apellido = $dato['apellido'];
                                        $nombre = $dato['nombre'];
                                        $nombreRemitente = $dato['nombreRemitente'];
                                        $detalleCompleto = $dato['detalleCompleto'];
                                        $tema = $dato['tema'];
                                        $observaciones = $dato['observaciones'];
                                        $colegiadoRemitente = NULL;
                                        $temaObservaciones = NULL;
                                        if (isset($apellido) && $apellido <> "") {
                                            $colegiadoRemitente = trim($apellido).' '.trim($nombre);
                                        }
                                        if (isset($detalleCompleto) && $detalleCompleto <> "") {
                                            $detalleCompleto = trim($detalleCompleto);
                                        }
                                        if (isset($nombreRemitente) && $nombreRemitente <> "") {
                                            $colegiadoRemitente = trim($nombreRemitente);
                                        }
                                        if (isset($tema) && $tema <> "") {
                                            $temaObservaciones = trim($tema);
                                        }
                                        if (isset($observaciones) && $observaciones <> "") {
                                            $temaObservaciones = trim($observaciones);
                                        }
                                        $idGet = $idOrdenDia.'_'.$idOrdenDiaDetalle;
                                        ?>
                                        <tr>
                                            <td style="display: none;"><?php echo $idOrdenDiaDetalle;?></td>
                                            <td><?php echo $fechaIngreso;?></td>
                                            <td><?php echo $tipoTramite;?></td>
                                            <td><?php echo $colegiadoRemitente;?></td>
                                            <?php 
                                            if ($tipoPlanilla == '4') {
                                            ?>
                                                <td><?php echo $detalleCompleto;?></td>
                                            <?php 
                                            } else { 
                                            ?>
                                                <td><?php echo $temaObservaciones;?></td>
                                            <?php 
                                            } 
                                            ?>
                                            <td>
                                                <?php 
                                                if ($estadoOrdenDia == 'A') {
                                                ?>
                                                <div class="btn-group">
                                                  <button type="button" class="btn btn-info dropdown-toggle"
                                                          data-toggle="dropdown">
                                                    Derivar <span class="caret"></span>
                                                  </button>

                                                  <ul class="dropdown-menu" role="menu">
                                                    <?php 
                                                    if ($tipoPlanilla <> '1') { 
                                                    ?>
                                                        <li><a href="datosOrdenDelDia/abm_orden_del_dia_detalle.php?id=<?php echo $idGet; ?>&tipo=<?php echo $i; ?>_1&accion=4">1.- Asuntos Internos</a></li>
                                                    <?php 
                                                    }

                                                    if ($tipoPlanilla <> '2') { 
                                                    ?>
                                                        <li><a href="datosOrdenDelDia/abm_orden_del_dia_detalle.php?id=<?php echo $idGet; ?>&tipo=<?php echo $i; ?>_2&accion=4">2.- Notas Recibidas</a></li>
                                                    <?php 
                                                    }

                                                    if ($tipoPlanilla <> '3') { 
                                                    ?>
                                                        <li><a href="datosOrdenDelDia/abm_orden_del_dia_detalle.php?id=<?php echo $idGet; ?>&tipo=<?php echo $i; ?>_3&accion=4">3.- Archivar</a></li>
                                                    <?php 
                                                    }

                                                    if ($tipoPlanilla <> '4') { 
                                                    ?>
                                                        <li><a href="datosOrdenDelDia/abm_orden_del_dia_detalle.php?id=<?php echo $idGet; ?>&tipo=<?php echo $i; ?>_4&accion=4">4.- Movimientos Matriculares</a></li>
                                                    <?php 
                                                    }
                                                    ?>
                                                    <li><a href="datosOrdenDelDia/abm_orden_del_dia_detalle.php?id=<?php echo $idGet; ?>&tipo=<?php echo $i; ?>_5&accion=4">Dejar Pendiente</a></li>
                                                    <!--<li class="divider"></li>-->
                                                  </ul>
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
                            <?php 
                            } else {
                                echo "No hay detalle para este tipo de trámite";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
            <?php 
                $i++;
            }
            ?>
            </div>
        </div>
    </div>
<?php
} else {
?>
    <div class="alert alert-danger" role="alert">
        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>
<?php        
}
?>
<div class="row">&nbsp;</div>
<div class="row">

</div>
<?php
require_once '../html/footer.php';