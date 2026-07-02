<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/homeBankingLogic.php');
$homeBankingLogic = new homeBankingLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "order": [[ 0, "asc" ], [ 1, "asc"]],
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
    $idHomeBankingArchivo = $_GET['id'];
    $resProcesaHomeBanking = $homeBankingLogic->obtenerHomaBankingPorId($idHomeBankingArchivo);
    if ($resProcesaHomeBanking['estado']) {
        $homeBankingArchivo = $resProcesaHomeBanking['datos'];
        $periodoProceso = $homeBankingArchivo['periodoProceso'];
        $fechaPrimerVencimiento = $homeBankingArchivo['fechaPrimerVencimiento'];
        $codigo = $homeBankingArchivo['codigo'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta idDebitoAutomatico - ';
}
if (isset($_GET['anio']) && $_GET['anio'] != ""){
    $anio = $_GET['anio'];
} else {
    $continua = FALSE;
    $mensaje .= 'Falta anio - ';
}
?> 
<div class="panel panel-info">
<div class="panel-heading"><h4>Liquidación HomeBanking Período <?php echo $periodoProceso; ?></h4></div>
<div class="panel-body">
    <div class="row">
        <div class="row">
            <div class="col-md-12 text-right">
                <form method="POST" action="home_banking.php">
                    <button type="submit" class="btn btn-info">Volver</button>
                    <input type="hidden" name="anio" id="anio" value="<?php echo $anio; ?>">
                </form>    
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resEnvioDetalle = $homeBankingLogic->obtenerLinkPagosPorIdHomeBankin($idHomeBankingArchivo);
    if ($resEnvioDetalle['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th>Origen</th>
                    <th>Matricula</th>
                    <th>Apellido y Nombre</th>
                    <th>Detalle</th>
                    <th style="text-align: center;">Importe</th>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resEnvioDetalle['datos'] as $dato) 
                  {
                    $origen = $dato['origen'];
                    $mensajeTicket = $dato['mensajeTicket'];
                    $matricula = $dato['matricula'];
                    $apellidoNombre = $dato['apellidoNombre'];
                    $importe = $dato['importe'];
                  ?>
                    <tr>
                        <td><?php echo $origen;?></td>
                        <td><?php echo $matricula;?></td>
                        <td><?php echo utf8_encode($apellidoNombre);?></td>
                        <td><?php echo $mensajeTicket;?></td>
                        <td style="text-align: right;"><?php echo $importe;?></td>
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