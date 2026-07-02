<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cajaDiariaLogic.php');
$cajaDiariaLogic = new cajaDiariaLogic();
require_once ('../dataAccess/constanciaFirmaLogic.php');
$constanciaFirmaLogic = new constanciaFirmaLogic();
?>
<script>
function confirmarCierre()
{
    if(confirm('¿Estas seguro de cerrar la Caja Diaria?'))
        return true;
    else
        return false;
}
</script>
<?php 
$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCajaDiaria = $_GET['id'];
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idCajaDiaria';
}
if ($continua) {
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
    <?php
    }

    $resCajaDiaria = $cajaDiariaLogic->obtenerCajaDiariaPorId($idCajaDiaria);
    if ($resCajaDiaria['estado']) {
        $cajaDiaria = $resCajaDiaria['datos'];
        $fechaApertura = $cajaDiaria['fechaApertura'];
        $saldoInicial = $cajaDiaria['saldoInicial'];
        $totalesCaja = $cajaDiariaLogic->obtenerTotalRecaudacion($idCajaDiaria);
        if (isset($totalesCaja)) {
            $totalRecaudacion = $totalesCaja['totalRecaudacion'];
            $cantidadComprobantes = $totalesCaja['cantidadComprobantes'];
        } else {
            $totalRecaudacion = 0;
            $cantidadComprobantes = 0;
        }

        //verificamos si hay certificaciones de firma sin emitir recibo, entonces lo derivamos para que genere el recibo antes de cerrar la caja
        $importeTotal = 0;
        /*
        //Se debe activar cuando este la emision de recibos por constancia de firma
        $resCertificaciones = $constanciaFirmaLogic->obtenerCertificacionFirmaPorFecha($fechaApertura);
        if ($resCertificaciones['estado']) {
            foreach ($resCertificaciones['datos'] as $dato){
                if (isset($dato['numeroComprobante']) && $dato['numeroComprobante'] <> "") { continue; }

                $importeTotal += $dato['importe'];
            }
        }
        */
        if ($importeTotal > 0) {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row"> 
                <div class="col-md-12 alert alert-warning">Debe generar un recibo por constancia de firma </div>
            </div>
            <div class="col-md-3">
                <form method="POST" action="datosCajaDiaria\generar_recibo.php">
                    <button type="submit" class="btn btn-default">Generar recibo de firmas</button>
                    <input type="hidden" name="importe" id="importe" value="<?php echo $importeTotal; ?>">
                    <input type="hidden" name="tipoRecibo" id="tipoRecibo" value="FIRMA">
                </form>
            </div>
            <div class="row">&nbsp;</div>
        <?php
        } else {
            if (isset($_GET['lis']) && $_GET['lis'] == '1') {
                $link_volver = "cajadiaria_lista.php?id=".$idCajaDiaria;
            } else {
                $link_volver = "cajadiaria.php";
            }
        ?>
            <form id="formCajaDiaria" name="formCajaDiaria" method="POST" onSubmit="" action="datosCajaDiaria/abm_cajaDiaria.php">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3>Caja Diaria del &nbsp;<?php echo cambiarFechaFormatoParaMostrar($fechaApertura); ?></h3>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <label>Saldo inicial</label>
                                <input type="float" class="form-control" name="saldoInicial" id="saldoInicial" value="<?php echo $saldoInicial; ?>" readonly="" />
                            </div>
                            <div class="col-md-2">
                                <label>Recibos </label>
                                <input type="number" class="form-control" name="cantidadComprobantes" id="cantidadComprobantes" value="<?php echo $cantidadComprobantes; ?>" readonly="" />
                            </div>
                            <div class="col-md-2">
                                <label>Total cobrado * </label>
                                <input type="float" class="form-control" name="totalRecaudacion" id="totalRecaudacion" value="<?php echo $totalRecaudacion; ?>" required="" />
                            </div>
                            <div class="col-md-2">
                                <br>
                                <button type="submit"  class="btn btn-info" onclick="return confirmarCierre()">Confirmar</button>
                                <input type="hidden" name="idCajaDiaria" id="idCajaDiaria" value="<?php echo $idCajaDiaria ?>" required="" />
                                <input type="hidden" name="accion" id="accion" value="Cerrar" required="" />
                            </div>
                        </div>
                    </div>

                    <div class="row">&nbsp;</div>
                </div>
            </form>        
    <?php 
        }
    } else {
    ?>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12 alert alert-danger">
                ERROR: <?php echo $resCajaDiaria['mensaje']; ?>
            </div>
        </div>
    <?php
    }
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12 alert alert-danger">
            ERROR: <?php echo $mensaje; ?>
        </div>
    </div>
<?php 
}
?>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-12">
        <form id="formVolver" name="formVolver" method="POST" onSubmit="" action="<?php echo $link_volver; ?>">
            <button type="submit"  class="btn btn-info" >Volver</button>
        </form>
    </div>
</div>
<?php 
require_once '../html/footer.php';
