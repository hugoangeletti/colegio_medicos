<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPagoLogic.php');
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
?>
<script>
$(document).ready(function () {
                $('#tablaPlanPago').DataTable({
                    "iDisplayLength":7,
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                        "bLengthChange": false,
                        "bFilter": false,
//                    dom: 'T<"clear">lfrtip',
                    tableTools: {
                       "sSwfPath": "../public/swf/copy_csv_xls_pdf.swf", 
                       "aButtons": [
                            {
                                "sExtends": "pdf",
                                "mColumns" : [0, 1, 2, 3, 4, 5],
//                                "oSelectorOpts": {
//                                    page: 'current'
//                                }
                                "sTitle": "Cuotas adeudadas",
                                "sPdfOrientation": "portrait",
                                "sFileName": "ListadoDeCuotasAdeudadas.pdf"
//                              "sPdfOrientation": "landscape",
//                              "sPdfSize": "letter",  ('A[3-4]', 'letter', 'legal' or 'tabloid')
                            }
                            
                    ]
                    }
                });
    }
);
</script>
<div class="container-fluid">
    <?php
    if (isset($_POST['idColegiado'])) {
        $periodoActual = $_SESSION['periodoActual'];
        $idColegiado = $_POST['idColegiado'];
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            include 'menuColegiado.php';
        ?>
        <div class="row">
            <div class="col-md-5">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                <label>- Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-5">
                <?php 
                $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, $periodoActual);
                if ($resEstadoTeso['estado']){
                    $codigo = $resEstadoTeso['codigoDeudor'];
                    $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigo);
                    if ($resEstadoTesoreria['estado']){
                        $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                    } else {
                        $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                    }
                } else {
                    $estadoTesoreria = $resEstadoTeso['mensaje'];
                }

                if ($codigo == 0){
                    $estiloTesoreria = ' style="color: green;"';
                } else {
                    $estiloTesoreria = ' style="color: red;"';
                }
                ?>
                <label <?php echo $estiloTesoreria; ?>>Estado con Tesorer&iacute;a:&nbsp; <?php echo $estadoTesoreria; ?></label>
            </div>
            <div class="col-md-2">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_tesoreria.php?idColegiado=<?php echo $idColegiado; ?>">
                    <button type="submit"  class="btn btn-default " >Volver a Tesorer&iacute;a.</button>
                </form>
            </div>
        </div>
<!--        <div class="row">
            <div class="col-md-12"><hr></div>
        </div>-->
        <div class="row">
            <div class="row">
                <div class="col-md-6">
                    <?php
                    //si tiene plan de pagos abierto, muestro las cuotas. Sino muestro los pagos
                    $resPlanPago = $colegiadoPlanPagoLogic->obtenerColegiadoPlanPago($idColegiado);
                    if ($resPlanPago['estado']) {
                    ?>
                        <h4  style="color: red;">Plan de pagos</h4>
                        <table  id="tablaPlanPago" class="display">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Cuota</th>
                                    <th style="text-align: center;">Importe</th>
                                    <th style="text-align: center;">Actualizado</th>
                                    <th style="text-align: center;">Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalPPAlDia = 0;
                                $totalPPConDeuda = 0;
                                foreach ($resPlanPago['datos'] as $dato){
                                    $fechaVencimiento = $dato['vencimiento'];
                                    $importeActualizado = $dato['importeActualizado'];
                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo rellenarCeros($dato['cuota'], 2);?></td>
                                        <td style="text-align: right;"><?php echo number_format($dato['importe'], 2, ',', '.');?></td>
                                        <td style="text-align: right;"><?php echo number_format($importeActualizado, 2, ',', '.');?></td>
                                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaVencimiento);?></td>
                                    </tr>
                                <?php
                                    //totalizadores
                                    if ($fechaVencimiento >= date('Y-m-d')) {
                                        $totalPPAlDia += $importeActualizado;
                                    } else {
                                        $totalPPConDeuda += $importeActualizado;
                                    }
                                }
                                $totalPPDeuda = $totalPPAlDia + $totalPPConDeuda;
                                ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-6">
                                <?php 
                                if ($totalPPDeuda > 0) {
                                ?>
                                    <h5 style="color: green;">Total cuotas No vencidas: <b>$<?php echo number_format($totalPPDeuda, 2, ',', '.'); ?></b></h5>
                                <?php
                                }
                                if ($totalPPConDeuda > 0) {
                                ?>
                                    <h5 style="color: red;">Total cuotas Vencidas: <b>$<?php echo number_format($totalPPConDeuda, 2, ',', '.'); ?></b></h5>
                                <?php
                                }
                                if ($totalPPDeuda > 0) {
                                ?>
                                    <h5>Total: <b>$<?php echo number_format($totalPPDeuda, 2, ',', '.'); ?></b></h5>
                                <?php
                                } else {
                                ?>
                                    <h4 style="color: green;">No hay cuotas de Plan de Pagos pendiente de cobro</h4>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-4">Botoner</div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="<?php echo $resPlanPago['clase']; ?>" role="alert">
                            <span class="<?php echo $resPlanPago['icono']; ?>" aria-hidden="true"></span>
                            <span><strong><?php echo $resPlanPago['mensaje']; ?></strong></span>
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
            <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
                <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
            </div>        
        <?php
        }
    }
    ?>
    </div>
<?php    
//require_once '../html/footer.php';
