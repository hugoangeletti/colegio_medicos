<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiado_seguro_Logic.php');
?>
<script>
$(document).ready(
    function () {
                $('#tablaEnvios').DataTable({
                    "iDisplayLength":50,
                    "order": [[ 1, "desc" ]],
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
    if(confirm('¿Estas seguro de ANULAR EL PROCESO?'))
        return true;
    else
        return false;
}
</script>
<?php
$continua = TRUE;
$mensaje = "";
if (isset($_POST['matricula']) && $_POST['matricula'] <> "") {
    $matricula = $_POST['matricula'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta matricula - ";
}

if ($continua) {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorMatricula($matricula);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $idColegiado = $colegiado['idColegiado'];
        $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);

        $colegiado_seguro_Logic = new colegiado_seguro_Logic();
        $resEnvios = $colegiado_seguro_Logic->obtenerSegurosProcesadosPorMatricula($matricula);
        if ($resEnvios['estado']) {
            $enviosColegiado = $resEnvios['datos'];
        } else {
            $continua = FALSE;
            $mensaje .= $resEnvios['mensaje'];    
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
    }
    if ($continua) {    
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-5">
                    <h4>Envíos para seguro praxis médica</h4>
                </div>
                <div class="col-md-1">
                    <a href="seguro_praxis_medica_listado.php" class="btn btn-info">Volver</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <h4>Matrícula: <b><?php echo $matricula; ?></b></h4>
                </div>
                <div class="col-md-4">
                    <h4>Apellido y Nombres: <b><?php echo $apellidoNombre; ?></b></h4>
                </div>
            </div>
            <div class="row">
                <?php 
                if (sizeof($enviosColegiado) > 0) {
                ?>
                    <div class="col-md-12">
                        <br>
                        <table id="tablaEnvios" class="display">
                            <thead>
                                <tr>
                                    <th>Id envío</th>
                                    <th style="text-align: center;">Período</th>
                                    <th style="text-align: center;">Fecha proceso</th>
                                    <th style="text-align: center;">Seguro activo</th>
                                    <th style="text-align: center;">Cuotas adeudadas</th>
                                    <th style="text-align: center;">Estado tesorería</th>
                                    <th style="text-align: center;">Cuotas abonadas</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($enviosColegiado as $envio) {
                                $idEnvio = $envio['idEnvio'];
                                $cuotasAdeudadas = $envio['cuotasAdeudadas'];
                                if ($cuotasAdeudadas == 0) {
                                    $cuotasAdeudadas = 'Al día.';
                                }
                                $activo = $envio['activo'];
                                if ($activo == 0) {
                                    $activo = 'NO (Motivo baja: '.$envio['motivoBaja'].')';
                                } else {
                                    $activo = 'SI';
                                }
                                $periodo = $envio['procesoAnio'].'-'.rellenarCeros($envio['procesoMes'], 2);
                                $fechaLimiteProceso = cambiarFechaFormatoParaMostrar($envio['fechaHasta']);
                                $estadoTesoreria = $envio['estadoTesoreria'];
                                $cuotasAbonadas = $envio['cuotasAbonadas'];
                                $fechaDesde = cambiarFechaFormatoParaMostrar($envio['fechaDesde']);
                                $fechaHasta = cambiarFechaFormatoParaMostrar($envio['fechaHasta']);
                                ?>
                                <tr>
                                    <td><?php echo $idEnvio; ?></td>
                                    <td style="text-align: center;"><?php echo $periodo;?></td>
                                    <td style="text-align: center;"><?php echo $fechaLimiteProceso?></td>
                                    <td style="text-align: center;"><?php echo $activo?></td>
                                    <td style="text-align: center;"><?php echo $cuotasAdeudadas;?></td>
                                    <td style="text-align: center;"><?php echo $estadoTesoreria;?></td>
                                    <td style="text-align: center;"><?php echo $cuotasAbonadas;?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                } else {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="<?php echo $resEnvios['clase']; ?>" role="alert">
                        <span class="<?php echo $resEnvios['icono']; ?>" aria-hidden="true"></span>
                        <span><strong><?php echo $resEnvios['mensaje']; ?></strong></span>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
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
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="alert alert-danger" role="alert">
        <span><strong><?php echo $mensaje; ?></strong></span>
    </div>
<?php
}
require_once '../html/footer.php';
