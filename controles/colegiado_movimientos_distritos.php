<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaMovimientos').DataTable({
                    "iDisplayLength":8,
                     "order": [[ 0, "desc" ]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": false,
                });
    }
);
function confirmar()
{
	if(confirm('¿Estas seguro de elimiar el movimiento?'))
		return true;
	else
		return false;
}
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $idColegiado = NULL;
}
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Movimientos en Otros Distritos</h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_consulta.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-info" >Volver a Datos del colegiado</button>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-body">

<?php
if (isset($idColegiado)) {
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = trim($colegiado['apellido']).', '.trim($colegiado['nombre']);
    ?>
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $matricula; ?>
            </div>
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $apellidoNombre; ?>
            </div>
            <?php
            if ($colegiado['tipoEstado'] == 'A') {
                $estiloEstado = 'style="color: green;"';
            } else {
                $estiloEstado = 'style="color: red;"';
            }
            ?>
            <div class="col-md-4 text-right" <?php echo $estiloEstado; ?>><b>Estado actual: <?php echo $colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']).$colegiado['movimientoCompleto']; ?></b></div>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#nuevoModal">Agregar Movimiento Otro Distrito </button>
            </div>
        </div>
        <?php
        //busco los movimientos matriculares
        $resMovimientos = $colegiadoMovimientoLogic->obtenerMovimientosOtrosDistritosPorIdColegiado($idColegiado);
        if ($resMovimientos['estado']){
        ?>
        <div class="row">
            <div class="col-md-12">
            <table  id="tablaMovimientos" class="display">
                <thead>
                    <tr>
                        <th style="text-align: center; display: none;">Id</th>
                        <th style="text-align: center;">Observaciones</th>
                        <th style="text-align: center;">Fecha Desde</th>
                        <th style="text-align: center;">Fecha Hasta</th>
                        <th style="text-align: center;">Distrito de Cambio</th>
                        <th style="text-align: center;">Distrito de Origen</th>
                        <th style="text-align: center;">Fecha Carga</th>
                        <th style="text-align: center;" colspan="2">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resMovimientos['datos'] as $dato){
                        $idColegiadoMovimiento = $dato['idColegiadoMovimiento'];
                        $fechaDesde = cambiarFechaFormatoParaMostrar($dato['fechaDesde']);
                        $fechaHasta = cambiarFechaFormatoParaMostrar($dato['fechaHasta']);
                        $distritoCambio = $dato['distritoCambio'];
                        $distritoOrigen = $dato['distritoOrigen'];
                        $detalleMovimiento = $dato['observaciones'];
                        $fechaCarga = cambiarFechaFormatoParaMostrar($dato['fechaCarga']);
                        ?>
                        <tr>
                            <td style="display: none"><?php echo $idColegiadoMovimiento;?></td>
                            <td style="text-align: left;"><?php echo $detalleMovimiento;?></td>
                            <td style="text-align: center;"><?php echo $fechaDesde;?></td>
                            <td style="text-align: center;"><?php echo $fechaHasta;?></td>
                            <td style="text-align: center;"><?php echo $distritoCambio;?></td>
                            <td style="text-align: center;"><?php echo $distritoOrigen;?></td>
                            <td style="text-align: center;"><?php echo $fechaCarga;?></td>
                            <td style="text-align: center;">
                                <a href="datosColegiadoMovimiento/anulaMovimientoOtroDistrito.php?id=<?php echo $idColegiadoMovimiento; ?>&idColegiado=<?php echo $idColegiado; ?>" class="btn btn-danger" role="button" onclick="return confirmar()">Eliminar</a>                    
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
            <div class="<?php echo $resMovimientos['clase']; ?>" role="alert">
                <span class="<?php echo $resMovimientos['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resMovimientos['mensaje']; ?></strong></span>
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
?>
    </div>
</div>

<!-- Agregar Movimiento de Otro Distrito -->
<div id="nuevoModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar Movimiento de Otro Distrito</h4>
      </div>
      <div class="modal-body">
          <p><label>Matrícula: </label>&nbsp;<?php echo $matricula; ?> &nbsp;&nbsp;<label>Apellido y Nombre: </label>&nbsp;<?php echo $apellidoNombre; ?></p>
          <div class="row">
        <form id="nuevo" autocomplete="off" name="nuevo" method="POST" action="datosColegiadoMovimiento/movimientoOtroDistrito.php?idColegiado=<?php echo $idColegiado; ?>">
            <div class="col-md-3">
                <label>Fecha Desde:  *</label>
                <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" required >
            </div>
            <div class="col-md-3">
                <label>Fecha Hasta:  *</label>
                <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" >
            </div>
            <div class="col-md-3">
                <label>Distrito de Cambio: </label>
                <input class="form-control" type="number" id="distritoCambio" name="distritoCambio" required="" min="2" max="10" />
            </div>
            <div class="col-md-3">
                <label>Distrito Origen: </label>
                <input class="form-control" type="number" id="distritoOrigen" name="distritoOrigen" min="2" max="10" />
            </div>
            <div class="col-md-12">
                <label>Observaciones: </label>
                <textarea class="form-control" name="nota" id="nota" rows="10" ></textarea>
            </div>
            <div class="col-md-12 text-center">
                <button type="submit"  class="btn btn-lg" >Guardar</button>
                <input type="hidden" name="idColegiadoMovimiento" id="idColegiadoMovimiento" value="<?php echo $idColegiadoMovimiento; ?>" />
            </div>
        </form>                
              </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        

<!-- Modificar Movimiento de Otro Distrito -->
<div id="editarModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modificar Movimiento de Otro Distrito</h4>
      </div>
      <div class="modal-body">
          <p><label>Matrícula: </label>&nbsp;<?php echo $matricula; ?> &nbsp;&nbsp;<label>Apellido y Nombre: </label>&nbsp;<?php echo $apellidoNombre; ?></p>
          <div class="row">
            <form id="nuevo" autocomplete="off" name="nuevo" method="POST" action="datosColegiadoMovimiento/movimientoOtroDistrito.php?idColegiado=<?php echo $idColegiado; ?>">
                <div class="col-md-3">
                    <label>Fecha Desde:  *</label>
                    <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" value="<?php echo $fechaDesde;?>" required <?php echo $readOnly; ?>>
                </div>
                <div class="col-md-3">
                    <label>Fecha Hasta:  *</label>
                    <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" value="<?php echo $fechaHasta;?>" <?php echo $readOnly; ?>>
                </div>
                <div class="col-md-3">
                    <label>Distrito de Cambio: </label>
                    <input class="form-control" type="number" id="distritoOrigen" name="distritoOrigen" value="<?php echo $distritoCambio; ?>" required="" min="2" max="10" <?php echo $readOnlyColegiado; ?> />
                </div>
                <div class="col-md-3">
                    <label>Distrito Origen: </label>
                    <input class="form-control" type="number" id="distritoOrigen" name="distritoOrigen" value="<?php echo $distritoOrigen; ?>" min="2" max="10" <?php echo $readOnlyColegiado; ?> />
                </div>
                <div class="col-md-12">
                    <textarea class="form-control" name="nota" id="nota" rows="10" ><?php echo $nota; ?></textarea>
                </div>
                <div class="col-md-12 text-center">
                    <button type="submit"  class="btn btn-lg" >Guardar</button>
                    <input type="hidden" name="idColegiadoMovimiento" id="idColegiadoMovimiento" value="<?php echo $idColegiadoMovimiento; ?>" />
                </div>
            </form>                
          </div>>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        

<?php
require_once '../html/footer.php';
