<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
?>
<script>
    $(document).ready(function()
    {
        $("#myModal").modal("show");
    });
</script>
<?php
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoPagoLogic.php');
$colegiadoPagoLogic = new colegiadoPagoLogic();
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
?>
<script>
$(document).ready(function () {
                $('#tablaOrdenada').DataTable({
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

                $('#tablaPagos').DataTable({
                    "iDisplayLength":7,
                    "order": [[ 0, "desc" ]],
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
                <h4>Estado con Tesorer&iacute;a</h4>
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
        $periodoActual = $_SESSION['periodoActual'];
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado'] && $resColegiado['datos']) {
            $colegiado = $resColegiado['datos'];
            //include 'menuColegiado.php';
            
            $tipoPdf = 'D';
            $mail = NULL;
            /*
            $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
            if ($resContacto['estado']) {
                $mail = $resContacto['datos']['email'];
                if (strtoupper($mail) <> 'NR' && $mail <> ''){
                    $tipoPdf = 'F';
                }
            }
            */
            $resContacto =  $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
            if ($resContacto['estado']) {
                $contacto = $resContacto['datos'];
                $noEnviaMail = $contacto['noEnviaMail'];
                if (!$noEnviaMail) {
                    $mail = $contacto['email'];
                    $tipoPdf = 'F';
                }
            }


        ?>
        <div class="row">
            <div class="col-md-2">
                <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
            </div>
            <div class="col-md-4">
                <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
            </div>
            <div class="col-md-4">
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
                Estado actual: <label <?php echo $estiloTesoreria; ?>><?php echo $estadoTesoreria; ?></label>
            </div>
            <div class="col-md-2">
<!--                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_imprimir_ctacte.php?idColegiado=<?php echo $idColegiado;?>">
                    <button type="submit"  class="btn btn-success " >Imprimir Cta.Cte.</button>
                </form>-->
            </div>
        </div>
<!--        <div class="row">
            <div class="col-md-12"><hr></div>
        </div>-->
        <!--<div class="row">-->
            <div class="row">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <h4  style="color: #08d;">Cuotas de Colegiaci&oacute;n a pagar</h4>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $deudaPlanPagosActualizado = 0;
                        $resDeudaPP = $colegiadoPlanPagoLogic->obtenerDeudaPlanPagosPorIdColegiado($idColegiado);
                        if ($resDeudaPP['estado']) {
                            foreach ($resDeudaPP['datos'] as $row) {
                                $deudaPlanPagosActualizado += $row['importeActualizado'];
                            }
                        }
                        if ($deudaPlanPagosActualizado > 0) {
                        ?>
                            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="colegiado_tesoreriaPP.php">
                                <button type="submit"  class="btn btn-danger">Tiene Plan de Pagos (Deuda: $<?php echo $deudaPlanPagosActualizado; ?>)</button>
                                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                            </form>
                        <?php
                        } else {
                            echo "";
                        }
                        ?>
                    </div>
                    <?php
                    $totalDeuda = 0;
                    $debePeriodoAnterior = 0;
                    $totalAlDia = 0;
                    $totalConDeuda = 0;
                    $totalDeudaAnteriores = 0;
                    $debePeriodoAnterior = FALSE;
                    $debePeriodoActual = FALSE;
                    $resDeuda = $colegiadoDeudaAnualLogic->obtenerColegiadoDeudaAnualAPagar($idColegiado);
                    if ($resDeuda['estado']) {
                        $deuda = $resDeuda['datos'];
                        ?>
                        <table  id="tablaOrdenada" class="display">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Per&iacute;odo-Cuota</th>
                                    <th style="text-align: center;">Importe</th>
                                    <th style="text-align: center;">Actualizado</th>
                                    <th style="text-align: center;">Vencimiento</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $totalDeudaAnteriores = $deudaPlanPagosActualizado;
                                foreach ($resDeuda['datos'] as $dato){
                                    /*
                                    $resPagoNoRegistrado = $colegiadoPagoLogic->obtenerPagoNoRegistrado($dato['idColegiadoDeudaAnualCuota'],'C');
                                    if ($resPagoNoRegistrado['estado']) {
                                        $pagoNoRegistrado = $resPagoNoRegistrado['datos']['pagoNoRegistrado'];
                                    } else {
                                        $pagoNoRegistrado = "";
                                    }
                                     * 
                                     */
                                    $vencimientoTrimestre = NULL;
                                    if (CHEQUERA_TRIMESTRAL) {
                                        $fechaVencimientoTrimestre = date('Y-m-d');
                                        if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_UNO) {
                                            $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_UNO;
                                        } else {
                                            if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_DOS) {
                                                $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_DOS;
                                            } else {
                                                if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_TRES) {
                                                    $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_TRES;
                                                } else {
                                                    if ($fechaVencimientoTrimestre <= VENCIMIENTO_TRIMESTRE_CUATRO) {
                                                        $vencimientoTrimestre = VENCIMIENTO_TRIMESTRE_CUATRO;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $fechaVencimiento = $dato['vencimiento'];
                                    $importeActualizado = $dato['importeActualizado'];
                                    if ($dato['idPagoNoRegistrado']) {
                                        $pagoNoRegistrado = 'Pago NR';
                                    } else {
                                        $pagoNoRegistrado = "";
                                    }
                                    if ($dato['periodo'] < $periodoActual) {
                                        $debePeriodoAnterior = TRUE;
                                    } else {
                                        if ($dato['periodo'] == $periodoActual) {
                                            $debePeriodoActual = TRUE;
                                        }
                                    }
                                    //totalizadores
                                    if ($fechaVencimiento >= date('Y-m-d')) {
                                        $totalAlDia += $importeActualizado;
                                    } else {
                                        $totalConDeuda += $importeActualizado;
                                        if ($dato['periodo'] < $periodoActual) {
                                            $totalDeudaAnteriores += $importeActualizado;
                                        }
                                    }
                                    if ($vencimientoTrimestre < $fechaVencimiento) { continue; }
                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $dato['periodo'].'-'.rellenarCeros($dato['cuota'], 2);?></td>
                                        <td style="text-align: right;"><?php echo number_format($dato['importeUno'], 2, ',', '.');?></td>
                                        <td style="text-align: right;"><?php echo number_format($importeActualizado, 2, ',', '.');?></td>
                                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaVencimiento);?></td>
                                        <td><?php echo $pagoNoRegistrado;?></td>
                                    </tr>
                                <?php
                                }
                                $totalDeuda = $totalAlDia + $totalConDeuda;
                                ?>
                            </tbody>
                        </table>
                        
                    <?php
                    } else {
                        ?>
                        <div class="col-md-12">
                            <div class="<?php echo $resDeuda['clase']; ?>" role="alert">
                                <span class="<?php echo $resDeuda['icono']; ?>" aria-hidden="true"></span>
                                <span><strong><?php echo $resDeuda['mensaje']; ?></strong></span>
                            </div>        
                        </div>
                        <?php
                    }
                    
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    //muestro los pagos
                    $fechaHasta = date('Y-m-d');
                    $fechaDesde = sumarRestarSobreFecha(date('Y-m-d'), 5, 'year', '-');
                    $resPagos = $colegiadoPagoLogic->obtenerPagosColegiacionPorIdColegiado($idColegiado, $fechaDesde, $fechaHasta);
                    ?>
                    <h4  style="color: #08d;">Pagos registrados entre el <?php echo cambiarFechaFormatoParaMostrar($fechaDesde).' y el '.cambiarFechaFormatoParaMostrar($fechaHasta); ?>  </h4>
                    <?php
                    if ($resPagos['estado']) {
                    ?>
                        <table  id="tablaPagos" class="display" style="font-size: small">
                            <thead>
                                <tr>
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th style="text-align: center;">Fecha Pago</th>
                                    <th style="text-align: center;">Per&iacute;odo-Cuota</th>
                                    <th style="text-align: center;">Importe</th>
                                    <th style="text-align: center;">Recibo</th>
                                    <th style="text-align: center;">Lugar Pago</th>
                                    <th style="text-align: center;">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resPagos['datos'] as $dato){
                                    $fechaPago = $dato['fechaPago'];
                                    $importe = $dato['importe'];
                                    $recibo = $dato['recibo'];
                                    $lugarPago = $dato['lugarPago'];
                                    $tipoPago = '';
                                    if ($dato['idTipoPago'] == '2') {
                                        $periodoCuota = 'P.P. '.$dato['periodo'].'-'.rellenarCeros($dato['cuota'], 2);
                                        $tipoPago = "Plan Pagos";
                                    } else {
                                        if ($dato['idTipoPago'] == '4') {
                                            $periodoCuota = "Pago por Nota de deuda";
                                            //$tipoPago = 'Nota de Deuda';
                                            $resPagosNotaDeuda = $colegiadoPagoLogic->obtenerCuotasPorNotaDeuda($recibo);
                                            if ($resPagosNotaDeuda['estado']){
                                                $tipoPago = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#notaDeuda_'.$recibo.'Modal">Ver recibos</button>';
                                                ?>
                                                <div id="notaDeuda_<?php echo $recibo; ?>Modal" class="modal fade" role="dialog">
                                                    <div class="modal-dialog modal-lg">
                                                        <!-- Modal content-->
                                                        <div class="modal-content">
                                                            <div class="modal-header alert alert-info">
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                <h4 class="modal-title">Cuotas abonadas con Nota de Deuda <?php echo $recibo; ?> - fecha de pago: <?php echo cambiarFechaFormatoParaMostrar($dato['fechaPago']); ?> </h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-xs-2 text-center">Período-Cuota</div>
                                                                    <div class="col-xs-3 text-center">Vencimiento Original</div>
                                                                    <div class="col-xs-2">Monto original</div>
                                                                    <div class="col-xs-2">Monto actualizado</div>
                                                                    <div class="col-xs-2">N° de recibo</div>
                                                                </div>
                                                                <?php
                                                                $totalDeudaPorNota = 0;
                                                                foreach ($resPagosNotaDeuda['datos'] as $datoNota) {
                                                                    $totalDeudaPorNota += $datoNota['importeActualizado'];
                                                                ?>
                                                                    <div class="row">
                                                                        <div class="col-xs-2 text-center"><?php echo $datoNota['periodo'].'-'.rellenarCeros($datoNota['cuota'], 2); ?></div>
                                                                        <div class="col-xs-3 text-center"><?php echo cambiarFechaFormatoParaMostrar($datoNota['fechaVencimiento']); ?></div>
                                                                        <div class="col-xs-2"><?php echo $datoNota['importe']; ?></div>
                                                                        <div class="col-xs-2"><?php echo $datoNota['importeActualizado']; ?></div>
                                                                        <div class="col-xs-2"><?php echo $datoNota['idColegiadoDeudaAnualCuota']; ?></div>
                                                                    </div>
                                                                <?php
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <h5>Total abonado: <b><?php echo number_format($totalDeudaPorNota, 2, ',', '.') ; ?></b></h5>
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>        
                                            <?php
                                            } else {
                                                $tipoPago = "";
                                            }
                                        } else {
                                            if ($dato['periodo'] <> 0) {
                                                $periodoCuota = $dato['periodo'].'-'.rellenarCeros($dato['cuota'], 2);
                                                $tipoPago = "Colegiación";
                                            } else {
                                                $periodoCuota = "";
                                                $tipoPago = $dato['tipoPago'];
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td style="display: none;"><?php echo $fechaPago;?></td>
                                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaPago);?></td>
                                        <td style="text-align: center;"><?php echo $periodoCuota;?></td>
                                        <td style="text-align: right;"><?php echo number_format($dato['importe'], 2, ',', '.');?></td>
                                        <td style="text-align: right;"><?php echo $recibo; ?></td>
                                        <td style="text-align: left;"><?php echo substr($lugarPago, 0, 16); ?></td>
                                        <td style="text-align: left;"><?php echo $tipoPago; ?></td>
                                    </tr>
                                <?php
                                }
                                $pagosRegistrados = TRUE;
                                ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                        $pagosRegistrados = FALSE;
                    ?>
                        <div class="<?php echo $resPagos['clase']; ?>" role="alert">
                            <span class="<?php echo $resPagos['icono']; ?>" aria-hidden="true"></span>
                            <span><strong><?php echo $resPagos['mensaje']; ?></strong></span>
                        </div>        
                    <?php
                    }
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?php 
                    if ($totalAlDia > 0) {
                    ?>
                        <h5 style="color: green;">Total cuotas No vencidas: <b>$<?php echo number_format($totalAlDia, 2, ',', '.'); ?></b></h5>
                    <?php
                    }
                    if ($totalConDeuda > 0) {
                    ?>
                        <h5 style="color: red;">Total cuotas Vencidas: <b>$<?php echo number_format($totalConDeuda, 2, ',', '.'); ?></b></h5>
                    <?php
                    }
                    if ($totalDeuda > 0) {
                    ?>
                        <h5>Total: <b>$<?php echo number_format($totalDeuda, 2, ',', '.'); ?></b></h5>
                    <?php
                    } else {
                    ?>
                        <h4 style="color: green;">No hay cuotas pendiente de cobro</h4>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-md-6 text-center">
                    <?php if ($totalDeudaAnteriores > 0 && $usuarioLogic->verificarAppUsuario($_SESSION['user_id'], 8)) { ?>
                        <h4>Deuda de períodos anteriores<?php if ($deudaPlanPagosActualizado > 0) { echo ' y Plan de Pagos'; } ?>: <b>$<?php echo number_format($totalDeudaAnteriores, 2, ',', '.'); ?></b>
                        <a href="tesoreria_planesdepago_nuevo.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-lg btn-warning" role="button">Generar Plan de Pagos</a>            
                        </h4>
                    <?php } ?>
                </div>
                <div class="col-md-3">
                    <!--<a href="colegiado_tesoreria_otrospagos.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-warning" role="button">Ver Pagos de otros conceptos</a>-->
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#otrosPagosModal">Ver Pagos de otros conceptos </button>
                </div>
            </div>
            <div class="row">
                <hr style="border-color: #08d; ">
                <form id="formChequeraActual" name="formChequeraActual" method="POST" onSubmit="" action="colegiado_tesoreria_imprimir.php?idColegiado=<?php echo $idColegiado; ?>" target="_BLANK">
                    <div class="col-md-4">
                        <label>Forma de reporte *</label><br>
                        <div class="radio-inline">
                            <label><input type="radio" name="tipoPdf" checked="" value="I">Para imprimir </label>
                        </div>
                        <?php
                        if (isset($mail)) {
                        ?>
                        <div class="radio-inline">
                            <label><input type="radio" name="tipoPdf" value="F">Env&iacute;a por mail </label>
                        </div>
                        <input class="form-control" type="text" name="mail" id="mail" value="<?php echo $mail; ?>" />
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-8">
                        <label>Reporte de: *</label><br>
                        <div class="radio-inline">
                            <label><input type="radio" name="imprimir" checked="" value="CC">Cuenta Corriente </label>
                        </div>
                        <?php 
                        if ($debePeriodoActual) {
                        ?>
                        <div class="radio-inline">
                            <label><input type="radio" name="imprimir" value="PA">Chequera Per&iacute;odo Actual </label>
                        </div>
                        <?php
                        }
                        if ($debePeriodoAnterior) {
                        ?>
                            <div class="radio-inline">
                                <label><input type="radio" name="imprimir" value="DE">Deuda Per&iacute;odos Anteriores </label>
                            </div>
                        <?php
                        }
                        if ($pagosRegistrados) {
                        ?>
                        <div class="radio-inline">
                            <label><input type="radio" name="imprimir" value="PR">Pagos Registrados </label>
                        </div>
                        <?php 
                        }
                        ?>
                        &nbsp;&nbsp;
                        <button type="submit"  class="btn btn-success btn-lg " >Generar </button>
                    </div>
                </form>
            </div>

        <!--</div>-->
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
</div>
        
<div id="otrosPagosModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-warning">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Pagos de otros conceptos</h4>
      </div>
      <div class="modal-body">
          <p>
            <?php 
            $resPagosOC = $colegiadoPagoLogic->obtenerPagosPorOtrosConceptos($idColegiado);
            if ($resPagosOC['estado']){
            ?>
                <table width="100%" id="" class="display">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Fecha Pago</th>
                            <th>Concepto</th>
                            <th>Lugar de Pago</th>
                            <th>Nro.Recibo</th>
                            <th style="text-align: center;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resPagosOC['datos'] as $value) {
                        ?>
                        <tr>
                            <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($value['fechaPago']); ?></td>
                            <td><?php echo $value['tipoPago']; ?></td>
                            <td><?php echo $value['lugarPago']; ?></td>
                            <td><?php echo $value['numeroRecibo']; ?></td>
                            <td style="text-align: right;"><?php echo $value['importe']; ?></td>
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

<?php
require_once '../html/footer.php';
