<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiacionAnualLogic.php');
$colegiacionAnualLogic = new colegiacionAnualLogic();
?>
<script>
$(document).ready(
    function () {
                $('#tablaColegiacion').DataTable({
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
</script>
<div class="panel panel-info">
<div class="panel-heading"><h4><b>Colegiación anual - Valores y Vencimientos</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-9">&nbsp;</div>
            <div class="col-md-3">
                <form method="POST" action="colegiacion_anual_form.php">
                    <div align="right">
                        <button type="submit" class="btn btn-primary">Nuevo Valor y Vencimiento</button>
                        <input type="hidden" id="accion" name="accion" value="1">
                    </div>
                </form>
            </div>
        </div>
        <?php
        $periodoActual = $_SESSION['periodoActual'];
        $resColegiacion = $colegiacionAnualLogic->obtenerColegiacionAnual();
        if ($resColegiacion['estado']) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <table  id="tablaColegiacion" class="display">
                        <thead>
                            <tr>
                                <th style="display: none;">Id</th>
                                <th style="text-align: center;">Período</th>
                                <th style="text-align: center;">Antigüedad</th>
                                <th style="text-align: center;">Cantidad Cuotas</th>
                                <th style="text-align: center;">Importe</th>
                                <th style="text-align: center;">Vencimiento Cuota 1</th>
                                <th style="text-align: center;">Pago Total</th>
                                <th style="text-align: center;">Vencimiento Pago Total</th>
                                <th style="text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resColegiacion['datos'] as $dato){
                                $idColegiacionAnual = $dato['idColegiacionAnual'];
                                $periodo = $dato['periodo'];
                                $vencimientoCuotaUno = cambiarFechaFormatoParaMostrar($dato['vencimientoCuotaUno']);
                                $vencimientoPagoTotal = cambiarFechaFormatoParaMostrar($dato['vencimientoPagoTotal']);
                                $antiguedad = $dato['antiguedad'];
                                switch ($antiguedad) {
                                    case '1':
                                        $antiguedadTexto = "menos de 5 años";
                                        break;
                                    
                                    case '2':
                                        $antiguedadTexto = "5 o más años";
                                        break;
                                    
                                    default:
                                        $antiguedadTexto = "";
                                        break;
                                }
                                $importe = $dato['importe'];
                                $pagoTotal = $dato['pagoTotal'];
                                $cuotas = $dato['cuotas'];
                                ?>
                                <tr>
                                    <td style="display: none"><?php echo $idColegiacionAnual;?></td>
                                    <td style="text-align: center;"><?php echo $periodo;?></td>
                                    <td style="text-align: center;"><?php echo $antiguedadTexto;?></td>
                                    <td style="text-align: center;"><?php echo $cuotas;?></td>
                                    <td style="text-align: center;"><?php echo $importe;?></td>
                                    <td style="text-align: center;"><?php echo $vencimientoCuotaUno;?></td>
                                    <td style="text-align: center;"><?php echo $pagoTotal;?></td>
                                    <td style="text-align: center;"><?php echo $vencimientoPagoTotal;?></td>
                                    <td>
                                        <?php
                                        if ($periodo == $periodoActual) {
                                        ?>
                                        <form method="POST" action="colegiacion_anual_form.php">
                                            <button type="submit" class="btn btn-info">Editar</button>
                                            <input type="hidden" id="accion" name="accion" value="3">
                                            <input type="hidden" id="idColegiacionAnual" name="idColegiacionAnual" value="<?php echo $idColegiacionAnual; ?>">
                                        </form>
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
            <div class="row">&nbsp;</div>
            <div class="<?php echo $resColegiacion['clase']; ?>" role="alert">
                <span class="<?php echo $resColegiacion['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resColegiacion['mensaje']; ?></strong></span>
            </div>        
        <?php        
        }
        ?>
    </div>
</div>
<?php
require_once '../html/footer.php';
