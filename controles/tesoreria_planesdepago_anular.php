<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/planPagosLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../dataAccess/colegiadoPlanPagoLogic.php');
$colegiadoPlanPagoLogic = new colegiadoPlanPagoLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "asc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            //dom: 'T<"clear">lfrtip',
        });
    });              
</script>

<?php

$continua = TRUE;
$accion = $_POST['accion'];
if (isset($_GET['idColegiado'])) {
    $idColegiado = $_GET['idColegiado'];
} else {
    $continua = FALSE;
}

if (isset($_GET['idPP'])) {
    $idPlanPago = $_GET['idPP'];
} else {
    $continua = FALSE;
}

if ($continua) {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $resContacto = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
        if ($resContacto['estado']) {
            $mail = $resContacto['datos']['email'];
        } else {
            $mail = '';
        }
    }
    
    if (isset($_POST['mensaje'])) {
    ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
     <?php
    }
    //obtengo el plan de pagos vigente
    $resPlanPago = $colegiadoPlanPagoLogic->obtenerPlanPagoPorIdColegiado($idColegiado);
    
    if ($resPlanPago['estado']) {
        $planPago = $resPlanPago['datos'];
        $totalDeuda = $planPago[0]['importe'];
        $cuotas = $planPago[0]['cuotas'];
        $fechaCreacion = $planPago[0]['fechaCreacion'];
        
        //busco las cuotas
        $resPPCuotas = $colegiadoPlanPagoLogic->obtenerPlanPagosCuotasPorIdPlanPago($idPlanPago);
        if ($resPPCuotas['estado']) {
            $planPagoCuotas = $resPPCuotas['datos'];
        } else {
            $planPagoCuotas = array();
        }
    } else {
        $totalDeuda = 0;
        $cuotas = 0;
        $fechaCreacion = "";
    }
    ?>

<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4><?php if ($accion == 2) { echo 'Anular Plan de Pagos'; } else { echo 'Ver Cuotas del Plan de Pagos'; } ?></h4>
            </div>
            <div class="col-md-3 text-left">
                <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="tesoreria_planesdepago.php">
                    <button type="submit"  class="btn btn-info" >Volver a Planes de Pago</button>
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
    <?php
    if ($totalDeuda > 0) {
    ?>
        <form id="datosPlanPagos" autocomplete="off" name="datosPlanPagos" method="POST" action="datosColegiadoPlanPagos\anular_plan_pagos.php">
            <div class="row">
                <div class="col-md-3">
                    <label>Plan de pagos Nº: &nbsp;</label>
                    <input type="text" class="form-control" name="idPlanPagos" id="idPlanPagos" value="<?php echo $idPlanPago; ?>" readonly="" />
                </div>
                <div class="col-md-4">
                    <label>Importe del Plan de pagos: &nbsp;</label>
                    <input type="text" class="form-control" name="importe" id="importe" value="$<?php echo $totalDeuda; ?>" readonly="" />
                </div>
                <div class="col-md-2">
                    <label>Cuotas: &nbsp;</label>
                    <input type="text" class="form-control" name="cuotas" id="cuotas" value="<?php echo $cuotas; ?>" readonly="" />
                </div>
                <div class="col-md-3">
                    <label>Fecha de creación: &nbsp;</label>
                    <input type="text" class="form-control" name="fecha" id="fecha" value="<?php echo cambiarFechaFormatoParaMostrar($fechaCreacion); ?>" readonly="" />
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 table-responsive">
                    <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Cuota</th>
                            <th>Importe</th>
                            <th>Vencimiento</th>
                            <th>Fecha de pago</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $botonConfirma = TRUE;
                        foreach ($planPagoCuotas as $dato) 
                        {
                          $cuota = $dato['cuota'];
                          $importe = $dato['importe'];
                          $vencimiento = $dato['vencimiento'];
                          $fechaPago = $dato['fechaPago'];
                          $estado = $dato['estado'];
                          ?>
                        <tr>
                            <td><?php echo $cuota;?></td>
                            <td><?php echo $importe;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($vencimiento);?></td>
                            <td><?php if (isset($fechaPago)) { echo cambiarFechaFormatoParaMostrar($fechaPago); } ?></td>
                            <td><?php                         
                                switch ($estado) {
                                 case 1:
                                     echo 'A pagar';
                                     break;

                                 case 2:
                                     echo 'Abonada';
                                     $botonConfirma = FALSE;
                                     break;

                                 default:
                                     break;
                             } ?></td>
                       </tr>
                      <?php
                      }
                  ?>
                    </tbody>
                    </table>
                </div>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <?php
                    if ($accion == 2) {
                        if ($botonConfirma) { ?>
                            <button type="submit" name='confirma' id='confirma' class="btn btn-success">Confirma anulación</button>
                            <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idPlanPago; ?>">
                    <?php } else { ?>
                            <h4 class="alert alert-danger">No se puede anular el Plan de Pagos porque ya tiene cuotas pagas, puede refinanciar.</h4>
                    <?php
                          }   
                    } ?>
                </div>
            </div>
        </form>
    <?php  } else { ?>
        <div class="col-md-12 text-center">
            <h4>No se encontró el Plan de pagos, vuelva a intentar</h4>
        </div>
    <?php } ?>
    <div class="row">
        <hr style="border-color: #08d; ">
        <form id="formChequeraActual" name="formChequeraActual" method="POST" onSubmit="" action="datosColegiadoPlanPagos/imprimir_chequera.php?idPP=<?php echo $idPlanPago; ?>" target="_BLANK">
            <h4>Imprimir chequera</h4>
            <div class="col-md-2">
                <div class="radio-inline">
                    <label><input type="radio" name="tipoPdf" checked="" value="I">Para imprimir </label>
                </div>
            </div>
            <div class="col-md-2">
                <?php
                $readOnly = '';
                $placeHolder = ' placeholder="Ingrese el correo, no lo tiene registrado"';
                if (isset($mail) && $mail != '') {
                    $readOnly = 'readonly=""';
                    $placeHolder = '';
                } 
                ?>
                <div class="radio-inline">
                    <label><input type="radio" name="tipoPdf" value="F">Env&iacute;a por mail </label>
                </div>
            </div>
            <div class="col-md-4">
                <input class="form-control" type="text" name="mail" id="mail" value="<?php echo $mail; ?>" <?php //echo $readOnly; ?> <?php echo $placeHolder; ?> />
            </div>
            <div class="col-md-1">
                <button type="submit" name='confirma' id='confirma' class="btn btn-success">Imprimir chequera</button>
            </div>
        </form>
    </div>
                
    </div>
</div>
<?php
}
require_once '../html/footer.php';
