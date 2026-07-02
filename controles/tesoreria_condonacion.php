<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/condonacionLogic.php');
$condonacionLogic = new condonacionLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
require_once ('../dataAccess/planPagosLogic.php');
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
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Condonación de deuda</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
    if (isset($_POST['estadoCondonacion']) && $_POST['estadoCondonacion'] != ""){
        $estadoCondonacion = $_POST['estadoCondonacion'];
    } else {
        $estadoCondonacion = 'A';
    }
    ?>
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="tesoreria_condonacion.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoCondonacion" name="estadoCondonacion" required onChange="this.form.submit()">
                        <option value="A" <?php if($estadoCondonacion == "A") { echo 'selected'; } ?>>Abiertas</option>
                        <option value="C" <?php if($estadoCondonacion == "C") { echo 'selected'; } ?>>Condonadas</option>
                        <option value="B" <?php if($estadoCondonacion == "B") { echo 'selected'; } ?>>Anuladas</option>
                    </select>
                </div>
            </form>    
        </div>
        <div class="col-md-2"></div>
        <div class="col-md-4 text-center">
            <form method="POST" action="tesoreria_condonacion_nueva.php">
                <button type="submit" class="btn btn-success btn-lg">Nueva Condonación</button>
                <input type="hidden" id="estadoCondonacion" name="estadoCondonacion" value="<?php echo $estadoCondonacion; ?>">
                <input type="hidden" id="accion" name="accion" value="1">
            </form>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resCondonaciones = $condonacionLogic->obtenerCondonacionesPorEstado($estadoCondonacion);
    if ($resCondonaciones['estado']){
    ?>
        <div class="col-md-12 table-responsive">
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha Solicitud</th>
                        <th>Matrícula</th>
                        <th>Apellido y Nombre</th>
                        <th>Autorizó</th>
                        <th>Realizó</th>
                        <?php if($estadoCondonacion != "B") { ?>
                            <th style="width: 30px">Anular</th>
                            <th style="width: 30px">Ver cuotas</th>
                        <?php } ?>
                    </tr>
                </thead>
          <tbody>
              <?php
                  foreach ($resCondonaciones['datos'] as $dato) 
                  {
                      $idCondonacion = $dato['idCondonacion'];
                      $fechaSolicitud = $dato['fechaSolicitud'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $queCondona = $dato['queCondona'];
                      $idColegiado = $dato['idColegiado'];
                      $autorizo = $dato['responsable'];
                      $realizo = $dato['realizo'];
                  ?>
                    <tr>
                	<td><?php echo $idCondonacion;?></td>
                        <td><?php echo cambiarFechaFormatoParaMostrar($fechaSolicitud);?></td>
			<td><?php echo $matricula;?></td>
			<td><?php echo $apellidoNombre;?></td>
			<td><?php echo $autorizo;?></td>
			<td><?php echo $realizo;?></td>
                        <?php if($estadoCondonacion != "B") { ?>
                        <td>
                            <div align="center">
                                <form method="POST" action="tesoreria_condonacion_anular.php?idColegiado=<?php echo $idColegiado; ?>&idCondonacion=<?php echo $idCondonacion; ?>">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="2">
                                    <input type="hidden" id="estadoCondonacion" name="estadoCondonacion" value="<?php echo $estadoCondonacion; ?>">
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="tesoreria_condonacion_anular.php?idColegiado=<?php echo $idColegiado; ?>&idCondonacion=<?php echo $idCondonacion; ?>">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"></button>
                                    <input type="hidden" id="accion" name="accion" value="4">
                                    <input type="hidden" id="estadoCondonacion" name="estadoCondonacion" value="<?php echo $estadoCondonacion; ?>">
                                </form>
                            </div>    
                        </td>
                        <?php } ?>
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
    <div class="<?php echo $resCondonaciones['clase']; ?>" role="alert">
        <span class="<?php echo $resCondonaciones['icono']; ?>" ></span>
        <span><strong><?php echo $resCondonaciones['mensaje']; ?></strong></span>
    </div>
<?php
}    
?>
</div>
</div>
</div>
<?php
require_once '../html/footer.php';
