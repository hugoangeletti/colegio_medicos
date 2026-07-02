<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/debitoAutomaticoLogic.php');
$debitoAutomaticoLogic = new debitoAutomaticoLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "order": [[ 0, "desc" ]],
            dom: 'T<"clear">lfrtip',
        });
    });            
</script>
<?php
$continua = TRUE;
$mensaje = "";
if (isset($_POST['mensaje'])) {
?>
    <div class="ocultarMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
<?php    
}   
if (isset($_GET['id']) && $_GET['id'] != ""){
    $idDebitoAutomatico = $_GET['id'];
    $resEnvioDebito = $debitoAutomaticoLogic->obtenerEnvioDebitoPorId($idDebitoAutomatico);
    if ($resEnvioDebito['estado']) {
        $envioDebito = $resEnvioDebito['datos'];
        $tipoEnvio = $envioDebito['tipo'];
    } else {
        $continua = FALSE;
        $mensaje .= $resEnvioDebito['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idDebitoAutomatico - ';
}
if (isset($_GET['ori']) && $_GET['ori'] != ""){
    $origen = $_GET['ori'];
    $datosOrigen = explode("_", $origen);
    $anioOrigen = $datosOrigen[0];
    $mesOrigen = $datosOrigen[1];
    $mesOrigen = rellenarCeros($mesOrigen, 2);
    $tipoDebitoOrigen = $datosOrigen[2];
} else {
    $continua = FALSE;
    $mensaje .= 'Falta ori - ';
}
?> 
<div class="panel panel-info">
<div class="panel-heading"><h4>Débito Automático - Detalle de Envio</h4></div>
<div class="panel-body">
    <div class="row">
        <div class="row">
            <div class="col-md-12 text-right">
                <form method="POST" action="debito_automatico.php">
                    <button type="submit" class="btn btn-info">Volver</button>
                    <input type="hidden" name="anio" id="anio" value="<?php echo $anioOrigen; ?>">
                    <input type="hidden" name="mes" id="mes" value="<?php echo $mesOrigen; ?>">
                    <input type="hidden" name="tipoDebito" id="tipoDebito" value="<?php echo $tipoDebitoOrigen; ?>">
                </form>    
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $mes = rellenarCeros($mes, 2);
    $resEnvioDetalle = $debitoAutomaticoLogic->obtenerEnvioDebitoDetallePorIdEnvio($idDebitoAutomatico, $tipoEnvio);
    if ($resEnvioDetalle['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Matricula</th>
                    <th>Apellido y Nombre</th>
                    <th style="text-align: center;">Importe debitar</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resEnvioDetalle['datos'] as $dato) 
                  {
                      $idEnvioDebitoDetalle = $dato['idEnvioDebitoDetalle'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = trim($dato['apellido']).' '.trim($dato['nombre']);
                      $totalDebitar = $dato['importeDebitar'];
                  ?>
                    <tr>
                        <td><?php echo $idEnvioDebitoDetalle;?></td>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo $apellidoNombre;?></td>
                        <td style="text-align: right;"><?php echo $totalDebitar;?></td>
                        <td>
                            <?php 
                            $resEnvioCuotas = $debitoAutomaticoLogic->obtenerEnvioDebitoDetalleCuotas($idEnvioDebitoDetalle);
                            if ($resEnvioCuotas['estado']) {
                                /*<a href="debito_automatico_detalle_cuotas.php?id=<?php echo $idEnvioDebitoDetalle; ?>&ori=<?php echo $_GET['ori']; ?>" class="btn btn-default">Cuotas</a>*/
                                ?>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#notaDeuda_<?php echo $idEnvioDebitoDetalle; ?>Modal">Ver cuotas</button>
                                
                                <div id="notaDeuda_<?php echo $idEnvioDebitoDetalle; ?>Modal" class="modal fade" role="dialog">
                                    <div class="modal-dialog modal-lg">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header alert alert-info">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Cuotas a debitar</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-xs-2 text-center">Período-Cuota</div>
                                                    <div class="col-xs-2">Monto debitar</div>
                                                    <div class="col-xs-2">N° de recibo</div>
                                                </div>
                                                <?php
                                                $totalDeuda = 0;
                                                foreach ($resEnvioCuotas['datos'] as $datoCuota) {
                                                    $totalDeuda += $datoCuota['importe'];
                                                ?>
                                                    <div class="row">
                                                        <div class="col-xs-2 text-center"><?php echo $datoCuota['periodo'].'-'.rellenarCeros($datoCuota['cuota'], 2); ?></div>
                                                        <div class="col-xs-2"><?php echo $datoCuota['importe']; ?></div>
                                                        <div class="col-xs-2"><?php echo $datoCuota['recibo']; ?></div>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="modal-footer">
                                                <h5>Total debitar: <b><?php echo number_format($totalDeuda, 2, ',', '.') ; ?></b></h5>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
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
      ?>
        <div class="<?php echo $resEnvioDetalle['clase']; ?>" role="alert">
            <span class="<?php echo $resEnvioDetalle['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resEnvioDetalle['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';