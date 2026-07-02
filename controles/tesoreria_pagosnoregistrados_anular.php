<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/pagosNoRegistradosLogic.php');
$pagosNoRegistradosLogic = new pagosNoRegistradosLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":50,
            "order": [[ 0, "asc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": false,
            "bFilter": false,
            //dom: 'T<"clear">lfrtip',
        });
    });              
</script>

<?php

$continua = TRUE;
$accion = $_GET['accion'];
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $continua = FALSE;
}

if (isset($_GET['idPago'])) {
    $idPagoNoRegistrado = $_GET['idPago'];
} else {
    $continua = FALSE;
}

if ($continua) {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
     <?php
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <?php
            $resPagoNoRegistrado = $pagosNoRegistradosLogic->obtenerPagoNoregistradoPorId($idPagoNoRegistrado);    
            if ($resPagoNoRegistrado['estado']) {
                $recibo = $resPagoNoRegistrado['datos']['recibo'];
                $tipoPago = $resPagoNoRegistrado['datos']['tipoPago'];
                if ($tipoPago == 'P') {
                  $tipoPago = 'Cuota Plan Pagos';
                  $cuotaPP = $resPagoNoRegistrado['datos']['idPlanPago'].'-'.$resPagoNoRegistrado['datos']['cuotaPlanPago'];
                  $periodo = NULL;
                  $cuota = NULL;
                } else {
                  $tipoPago = 'Cuota colegiación';
                  $idPlanPago = NULL;
                  $cuotaPP = NULL;
                  $cuota = $resPagoNoRegistrado['datos']['periodo'].'-'.$resPagoNoRegistrado['datos']['cuota'];
                }
                $fechaPago = $resPagoNoRegistrado['datos']['fechaPago'];
                $fechaCarga = $resPagoNoRegistrado['datos']['fechaCarga'];
            ?>
                <div class="row">
                    <div class="col-md-9">
                        <h4><?php if ($accion == 2) { echo 'Anular Condonación'; } else { echo 'Ver Cuotas Condonadas'; } ?></h4>
                    </div>
                    <div class="col-md-3 text-left">
                        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_pagosnoregistrados.php?idColegiado=<?php echo $idColegiado; ?>">
                            <button type="submit"  class="btn btn-info" >Volver a Pagos No Registrados</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <label>Matr&iacute;cula:&nbsp; </label><?php echo $colegiado['matricula']; ?>
                </div>
                <div class="col-md-4">
                    <label>Apellido y Nombres:&nbsp; </label><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?>
                </div>
                <div class="col-md-6">&nbsp;</div>
            </div>
            <div class="row">&nbsp;</div>
            <form id="datosCondonacion" autocomplete="off" name="datosCondonacion" method="POST" action="datosPagosNoRegistrados/anular_pagonoregistrado.php">
                <div class="row">
                    <div class="col-md-1">
                        <label>Id: &nbsp;</label>
                        <input type="text" class="form-control" name="idPagoNoRegistrado" id="idPagoNoRegistrado" value="<?php echo $idPagoNoRegistrado; ?>" readonly="" />
                    </div>
                    <div class="col-md-2">
                        <label>Tipo de Pago: &nbsp;</label>
                        <input type="text" class="form-control" name="tipoPago" id="tipoPago" value="<?php echo $tipoPago; ?>" readonly="" />
                    </div>
                    <div class="col-md-1">
                        <label>Recibo Nº: &nbsp;</label>
                        <input type="text" class="form-control" name="recibo" id="recibo" value="<?php echo $recibo; ?>" readonly="" />
                    </div>
                    <div class="col-md-2">
                        <label>Cuota de Colegiación: &nbsp;</label>
                        <input type="text" class="form-control" name="periodoCuota" id="periodoCuota" value="<?php echo $cuota; ?>" readonly="" />
                    </div>
                    <div class="col-md-2">
                        <label>Cuota de Plan de Pagos: &nbsp;</label>
                        <input type="text" class="form-control" name="cuotaPP" id="cuotaPP" value="<?php echo $cuotaPP; ?>" readonly="" />
                    </div>
                    <div class="col-md-2">
                        <label>Fecha de Pago: &nbsp;</label>
                        <input type="text" class="form-control" name="fechaPago" id="fechaPago" value="<?php echo cambiarFechaFormatoParaMostrar($fechaPago); ?>" readonly="" />
                    </div>
                    <div class="col-md-2">
                        <label>Fecha de Carga: &nbsp;</label>
                        <input type="text" class="form-control" name="fechaCarga" id="fechaCarga" value="<?php echo cambiarFechaFormatoParaMostrar($fechaCarga); ?>" readonly="" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" name='confirma' id='confirma' class="btn btn-success">Confirma anulación</button>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                </div>
            </form>
            <?php  
        } else { ?>
            <div class="col-md-12 text-center">
                <h4>No se encontró el pago no registrado, vuelva a intentar</h4>
            </div>
        <?php 
        } 
        ?>
    </div>
</div>
<?php
} else {
    echo 'datos mal ingresados<br>';
}

require_once '../html/footer.php';
