<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaEspecialista').DataTable({
                    "iDisplayLength":8,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bLengthChange": false,
                    "bFilter": false,
                });
    }
);
</script>
<?php
if (isset($_GET['idColegiado'])) {
    $_SESSION['menuColegiado'] = "Especialista";
    $periodoActual = $_SESSION['periodoActual'];
    $idColegiado = $_GET['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        include 'menuColegiado.php';
        ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-6">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-3"><h4><b>Especialidades otorgadas</b></h4></div>
            <div class="col-md-3">
                <?php
                $fechaHasta = date('Y-m-d');
                $fechaDesde = sumarRestarSobreFecha($fechaHasta, 4, 'month', '-');

                $resPagos = $verificacionColegiadoLogic->tienePagosPotTituloEspecialista($idColegiado, $fechaDesde, $fechaHasta);
                if ($resPagos['estado']) {
                    ?>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#pagosModal">Tiene pagos para título especialista</button>                    
                <?php
                } else {
                ?>
                    &nbsp;
                <?php
                }
                ?>
            </div>
        </div>
        <?php
        //busco las especialidades y la cantidad que tenga por nombre de especialidad
        //en caso de tener mas de una por diferente origen, debo mostrar las fechas de especialista por cada tipo
        $resEspecialista = $colegiadoEspecialistaLogic->obtenerCantidadEspecialidadesPorIdColegiado($idColegiado);
        if ($resEspecialista['estado']) {
            ?>
            <div class="row">
                <div class="col-md-12">
                <table  id="tablaEspecialista" class="display">
                    <thead>
                        <tr>
                            <th style="text-align: center; display: none;">Id</th>
                            <th style="text-align: center;">Especialidad</th>
                            <th style="text-align: center;">Fecha Especialista</th>
                            <th style="text-align: center;">Fecha Recertificaci&oacute;n</th>
                            <th style="text-align: center;">Otorgado por</th>
                            <th style="text-align: center;">Fecha Jerarquizado</th>
                            <th style="text-align: center;">Fecha Consultor</th>
                            <th style="text-align: center;">Fecha Caducidad</th>
                            <th style="text-align: center;">Origen</th>
                            <th style="text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($resEspecialista['datos'] as $dato) {
                            $nombreEspecialidad = $dato['nombreEspecialidad'];
                            $codigoEspecialidad = $dato['codigoEspecialidad'];
                            $idEspecialidad = $dato['idEspecialidad'];
                            if ($dato['cantidad'] > 1) {
                                $origenesVarios = TRUE;
                            } else {
                                $origenesVarios = FALSE;
                            }
                            $resEspecialidad = $colegiadoEspecialistaLogic->obtenerEspecialidadPorIdColegiadoIdEspecialidad($idColegiado, $idEspecialidad);
                            if ($resEspecialidad['estado']){
                                $especialista = $resEspecialidad['datos'];
                                $idColegiadoEspecialista = $especialista['idColegiadoEspecialista'];
                                $fechaCarga= cambiarFechaFormatoParaMostrar($especialista['fechaCarga']);
                                $fechaEspecialista = cambiarFechaFormatoParaMostrar($especialista['fechaEspecialista']);
                                $fechaRecertificacion = cambiarFechaFormatoParaMostrar($especialista['fechaRecertificacion']);
                                $distritoOrigen = $especialista['distritoOrigen'];
                                $fechaVencimiento = cambiarFechaFormatoParaMostrar($especialista['fechaVencimiento']);
                                $tipoespecialista = $especialista['tipoespecialista'];
                                //obtengo la fecha de jerarquizado
                                if ($distritoOrigen <> "NACIÓN") {
                                    $resJerarquizado = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'J');
                                    if ($resJerarquizado['estado']){
                                        $fechaJerarquizado = cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']);
                                    } else {
                                        $fechaJerarquizado = NULL;
                                    }
                                    //obtengo la fecha de consultor
                                    $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
                                    if ($resConsultor['estado']){
                                        $fechaConsultor = cambiarFechaFormatoParaMostrar($resConsultor['fecha']);
                                        $fechaVencimiento = NULL;
                                    } else {
                                        $fechaConsultor = NULL;
                                    }
                                } else {
                                    $fechaJerarquizado = NULL;
                                    $fechaConsultor = NULL;
                                }
                                $origen = $especialista['origen'];
                                $especialistaInciso = $especialista['especialistaInciso'];
                                ?>
                                <tr>
                                    <td style="display: none"><?php echo $idColegiadoEspecialista;?></td>
                                    <td style="text-align: left;"><?php echo $nombreEspecialidad;?></td>
                                    <td style="text-align: center;"><?php echo $fechaEspecialista;?></td>
                                    <td style="text-align: center;"><?php echo $fechaRecertificacion;?></td>
                                    <td style="text-align: center;"><?php echo $distritoOrigen;?></td>
                                    <td style="text-align: center;"><?php echo $fechaJerarquizado;?></td>
                                    <td style="text-align: center;"><?php echo $fechaConsultor;?></td>
                                    <td style="text-align: center;"><?php echo $fechaVencimiento;?></td>
                                    <td style="text-align: right;"><?php echo $origen.$especialistaInciso;?></td>
                                    <td style="text-align: right;"><?php if ($origenesVarios) { echo "Varios"; } ?></td>
                                </tr>
                            <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                    </div>
                </div>
        <?php
        } else {
        ?>
            <div class="<?php echo $resEspecialista['clase']; ?>" role="alert">
                <span class="<?php echo $resEspecialista['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resEspecialista['mensaje']; ?></strong></span>
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
?>
        <!-- Modal -->
<div id="pagosModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Pagos registrados entre el <?php echo cambiarFechaFormatoParaMostrar($fechaDesde) ?> y el <?php echo cambiarFechaFormatoParaMostrar($fechaHasta); ?> </h4>
      </div>
      <div class="modal-body">
          <p>
              <?php 
            if ($resPagos['estado'] && count($resPagos['datos']) > 0){
              ?>
                <table width="100%" id="" class="display">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Detalle</th>
                            <th style="text-align: center;">Fecha de Pago</th>
                            <th style="text-align: center;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resPagos['datos'] as $pagos) {
                            ?>
                            <tr>
                                <td style="text-align: center;"><?php echo $pagos['detalle']; ?></td>
                                <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($pagos['fechaPago']); ?></td>
                                <td style="text-align: center;"><?php echo $pagos['monto']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
              }
              ?>
          </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        

        
        