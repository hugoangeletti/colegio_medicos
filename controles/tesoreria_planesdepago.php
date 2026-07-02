<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/planPagosLogic.php');
$planPagosLogic = new planPagosLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            //dom: 'T<"clear">lfrtip',
        });
    });              
</script>

<?php
if (isset($_POST['mensaje']))
{
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Planes de Pago</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
    if (isset($_POST['estadoPlanes']) && $_POST['estadoPlanes'] != ""){
        $estadoPlanes = $_POST['estadoPlanes'];
    } else {
        $estadoPlanes = 'A';
    }
    ?>
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="tesoreria_planesdepago.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoPlanes" name="estadoPlanes" required onChange="this.form.submit()">
                        <option value="A" <?php if($estadoPlanes == "A") { echo 'selected'; } ?>>Activos</option>
                        <option value="C" <?php if($estadoPlanes == "C") { echo 'selected'; } ?>>Cerrados</option>
                        <option value="N" <?php if($estadoPlanes == "N") { echo 'selected'; } ?>>Anulados</option>
                    </select>
                </div>
            </form>    
        </div>
        <div class="col-md-3"></div>
        <div class="col-md-3">
            <form method="POST" action="tesoreria_planesdepago_nuevo.php">
                <div align="right">
                    <button type="submit" class="btn btn-success btn-lg">Nuevo Plan de Pagos</button>
                    <input type="hidden" id="estadoPlanes" name="estadoPlanes" value="<?php echo $estadoPlanes; ?>">
                    <input type="hidden" id="accion" name="accion" value="1">
                </div>
            </form>
        </div>
    </div>
    <?php
    $resPlanes = $planPagosLogic->obtenerPlanPagosPorEstado($estadoPlanes);
    if ($resPlanes['estado']){
    ?>
        <div class="row">&nbsp;</div>
        <div class="col-md-12 table-responsive">
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha Creación</th>
                        <th>Matrícula</th>
                        <th>Apellido y Nombre</th>
                        <th>Importe total</th>
                        <th>Cantidad de Cuotas</th>
                        <?php if($estadoPlanes == "A") { ?>
                            <th style="width: 30px">Anular</th>
                        <?php } ?>
                        <?php if($estadoPlanes != "N") { ?>
                            <th style="width: 30px">Ver cuotas</th>
                        <?php } ?>
                        <th style="width: 30px">Imprime Nota</th>
                    </tr>
                </thead>
          <tbody>
              <?php
                  foreach ($resPlanes['datos'] as $dato) 
                  {
                      $idPlanPago = $dato['idPlanPago'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $importe = $dato['importe'];
                      $cuotas = $dato['cuotas'];
                      $fechaCreacion = cambiarFechaFormatoParaMostrar($dato['fechaCreacion']);
                      $idColegiado = $dato['idColegiado'];
                  ?>
                    <tr>
                	<td><?php echo $idPlanPago;?></td>
                	<td><?php echo $fechaCreacion;?></td>
			<td><?php echo $matricula;?></td>
			<td><?php echo $apellidoNombre;?></td>
			<td><?php echo $importe;?></td>
			<td><?php echo $cuotas;?></td>
                        <?php if($estadoPlanes == "A") { ?>
                        <td>
                            <div align="center">
                                <form method="POST" action="tesoreria_planesdepago_anular.php?idColegiado=<?php echo $idColegiado; ?>&idPP=<?php echo $idPlanPago; ?>">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="2">
                                    <input type="hidden" id="estadoPlanes" name="estadoPlanes" value="<?php echo $estadoPlanes; ?>">
                                </form>
                            </div>    
                        </td>
                        <?php } ?>
                        <?php if($estadoPlanes != "N") { ?>
                        <td>
                            <div align="center">
                                <form method="POST" action="tesoreria_planesdepago_anular.php?idColegiado=<?php echo $idColegiado; ?>&idPP=<?php echo $idPlanPago; ?>">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="4">
                                    <input type="hidden" id="estadoPlanes" name="estadoPlanes" value="<?php echo $estadoPlanes; ?>">
                                </form>
                            </div>    
                        </td>
                        <?php } ?>
                        <td>
                            <div align="center">
                                <form name="myForm"  method="POST" target="_BLANK" action="datosColegiadoPlanPagos/imprimir_plan_pagos.php?idPP=<?php echo $idPlanPago; ?>">
                                    <button type="submit" name='confirma' id='confirma' class="btn btn-warning glyphicon glyphicon-print center-block btn-sm"></button>
                                </form>
                            </div>    
                        </td>
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
    <div class="<?php echo $resPlanes['clase']; ?>" role="alert">
        <span class="<?php echo $resPlanes['icono']; ?>" ></span>
        <span><strong><?php echo $resPlanes['mensaje']; ?></strong></span>
    </div>
<?php
}    
?>
</div>
</div>
</div>
<?php
require_once '../html/footer.php';
