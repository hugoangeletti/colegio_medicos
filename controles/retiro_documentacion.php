<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/retiroDocumentacionLogic.php');
$retiroDocumentacionLogic = new retiroDocumentacionLogic();
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

function anular(leyenda)
{
    if(confirm('¿Estas seguro de '+leyenda+' el retiro seleccionado?'))
      return true;
    else
      return false;
}          
function confirmaRetiro(leyenda)
{
    if(confirm('¿Estas seguro de '+leyenda+' de la entrega de la documentación seleccionada?'))
      return true;
    else
      return false;
}          
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
<div class="panel-heading"><h4><b>Retiro de documentación</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
    if (isset($_POST['estadoRetiro']) && $_POST['estadoRetiro'] != ""){
        $estadoRetiro = $_POST['estadoRetiro'];
    } else {
        $estadoRetiro = 'A';
    }
    ?>
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="retiro_documentacion.php">
                <div class="col-xs-6">
                    <select class="form-control" id="estadoRetiro" name="estadoRetiro" required onChange="this.form.submit()">
                        <option value="A" <?php if($estadoRetiro == "A") { echo 'selected'; } ?>>A entregar</option>
                        <option value="E" <?php if($estadoRetiro == "E") { echo 'selected'; } ?>>Entregados</option>
                        <option value="B" <?php if($estadoRetiro == "B") { echo 'selected'; } ?>>Anulados</option>
                    </select>
                </div>
            </form>    
        </div>
        <div class="col-md-3"></div>
        <div class="col-md-3">
            <form method="POST" action="retiro_documentacion_nuevo.php">
                <div align="right">
                    <button type="submit" class="btn btn-success btn-lg">Nuevo Retiro Documentación</button>
                    <input type="hidden" id="estadoRetiro" name="estadoRetiro" value="<?php echo $estadoRetiro; ?>">
                    <input type="hidden" id="accion" name="accion" value="1">
                </div>
            </form>
        </div>
    </div>
    <?php
    $resRetiros = $retiroDocumentacionLogic->obtenerRetiroDocumentacionPorEstado($estadoRetiro);
    if ($resRetiros['estado']){
    ?>
        <div class="row">&nbsp;</div>
        <div class="col-md-12 table-responsive">
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha Carga</th>
                        <th>Matrícula</th>
                        <th>Apellido y Nombre</th>
                        <th>Documentación</th>
                        <th>Observación</th>
                        <th>Fecha retiro</th>
                        <?php 
                        if($estadoRetiro == "A") { 
                            $leyendaAnularActivar = "Anular";
                            $leyendaEntrega = "Marcar retiro";
                        ?>
                            <th style="width: 30px">Editar</th>
                            <th style="width: 30px">Anular</th>
                            <th style="width: 30px">Marcar retiro</th>
                        <?php
                        }
                        if($estadoRetiro == "B") { 
                            $leyendaAnularActivar = "Activar";
                        ?>
                            <th style="width: 30px">Activar</th>
                        <?php
                        } 
                        if($estadoRetiro == "E") { 
                            $leyendaEntrega = "Desmarcar retiro";
                        ?>
                            <th style="width: 30px">Desmarcar retiro</th>
                        <?php
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                  foreach ($resRetiros['datos'] as $dato) 
                  {
                      $idRetiroDocumentacion = $dato['idRetiro'];
                      $matricula = $dato['matricula'];
                      $apellidoNombre = $dato['apellidoNombre'];
                      $tipoDocumentacionRetiro = $dato['tipoDocumentacionRetiro'];
                      $fechaCarga = cambiarFechaFormatoParaMostrar(substr($dato['fechaCarga'], 0, 10));
                      if (isset($dato['fechaRetiro'])) {
                        $fechaRetiro = cambiarFechaFormatoParaMostrar(substr($dato['fechaRetiro'], 0, 10));
                      } else {
                        $fechaRetiro = NULL;
                      }
                      $idColegiado = $dato['idColegiado'];
                      $observacion = $dato['observacion'];
                  ?>
                    <tr>
                	<td><?php echo $idRetiroDocumentacion;?></td>
                	<td><?php echo $fechaCarga;?></td>
        			<td><?php echo $matricula;?></td>
        			<td><?php echo $apellidoNombre;?></td>
        			<td><?php echo $tipoDocumentacionRetiro;?></td>
                    <td><?php echo $observacion;?></td>
        			<td><?php echo $fechaRetiro;?></td>
                    <?php 
                    if ($estadoRetiro == "A") { 
                    ?>
                        <td>
                            <div align="center">
                                <form method="POST" action="retiro_documentacion_nuevo.php">
                                    <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil center-block btn-sm" ></button>
                                    <input type="hidden" id="accion" name="accion" value="3">
                                    <input type="hidden" id="estadoRetiro" name="estadoRetiro" value="<?php echo $estadoRetiro; ?>">
                                    <input type="hidden" id="idRetiroDocumentacion" name="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion; ?>">
                                </form>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="datosRetiro/abm_retiro_documentacion.php">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"  onclick="return anular('<?php echo $leyendaAnularActivar; ?>')"></button>
                                    <input type="hidden" id="accion" name="accion" value="2">
                                    <input type="hidden" id="estadoRetiro" name="estadoRetiro" value="<?php echo $estadoRetiro; ?>">
                                    <input type="hidden" id="idRetiroDocumentacion" name="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion; ?>">
                                </form>
                            </div>    
                        </td>
                        <td>
                            <div align="center">
                                <form method="POST" action="datosRetiro/abm_retiro_documentacion.php">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"  onclick="return confirmaRetiro('<?php echo $leyendaEntrega; ?>')"></button>
                                    <input type="hidden" id="accion" name="accion" value="4">
                                    <input type="hidden" id="estadoRetiro" name="estadoRetiro" value="<?php echo $estadoRetiro; ?>">
                                    <input type="hidden" id="idRetiroDocumentacion" name="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion; ?>">
                                </form>
                            </div>
                        </td>
                    <?php
                    }
                    if($estadoRetiro == "B") { 
                    ?>
                        <td>
                            <div align="center">
                                <form method="POST" action="datosRetiro/abm_retiro_documentacion.php">
                                    <button type="submit" class="btn btn-danger glyphicon glyphicon-erase center-block btn-sm"  onclick="return anular('<?php echo $leyendaAnularActivar; ?>')"></button>
                                    <input type="hidden" id="accion" name="accion" value="2">
                                    <input type="hidden" id="estadoRetiro" name="estadoRetiro" value="<?php echo $estadoRetiro; ?>">
                                    <input type="hidden" id="idRetiroDocumentacion" name="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion; ?>">
                                </form>
                            </div>    
                        </td>
                    <?php 
                    } 
                    if($estadoRetiro == "E") { 
                    ?>
                        <td>
                            <div align="center">
                                <form method="POST" action="datosRetiro/abm_retiro_documentacion.php">
                                    <button type="submit" class="btn btn-info glyphicon glyphicon-book center-block btn-sm"  onclick="return confirmaRetiro('<?php echo $leyendaEntrega; ?>')"></button>
                                    <input type="hidden" id="accion" name="accion" value="4">
                                    <input type="hidden" id="estadoRetiro" name="estadoRetiro" value="<?php echo $estadoRetiro; ?>">
                                    <input type="hidden" id="idRetiroDocumentacion" name="idRetiroDocumentacion" value="<?php echo $idRetiroDocumentacion; ?>">
                                </form>
                            </div>  
                        </td>  
                    <?php 
                    } 
                    ?>
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
    <br>
    <div class="<?php echo $resRetiros['clase']; ?>" role="alert">
        <span class="<?php echo $resRetiros['icono']; ?>" ></span>
        <span><strong><?php echo $resRetiros['mensaje']; ?></strong></span>
    </div>
<?php
}    
?>
</div>
</div>
</div>
<?php
require_once '../html/footer.php';
