<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/ordenDelDiaLogic.php');
$ordenDelDiaLogic = new ordenDelDiaLogic();
?>
<script>
$(document).ready(function () {
    $('#tablaOrdenada').DataTable({
        "iDisplayLength":10,
        "language": {
            "url": "../public/lang/esp.lang"
        },
        "order": [[ 0, "desc" ]],
    });
});
            
function confirmar()
{
    if(confirm('¿Estas seguro de elimiar orden de día?'))
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
   <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}   
if (isset($_POST['anio'])) {
    $anio = $_POST['anio'];
} else {
    $anio = date('Y');
}
if (isset($_POST['estadoOrdenDia'])) {
    $estadoOrdenDia = $_POST['estadoOrdenDia'];
} else {
    $estadoOrdenDia = 'A';
}
?> 
<div class="panel panel-info">
<div class="panel-heading"><h4>Listado de Orden del día</h4></div>
<div class="panel-body">
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="orden_del_dia_listado.php">
                <div class="col-xs-3">
                    <b>Año</b>
                    <select class="form-control" id="anio" name="anio" onChange='this.form.submit()'>
                        <?php 
                        $anioSelect = date('Y');
                        while ($anioSelect >= 2016) {
                        ?>
                            <option value="<?php echo $anioSelect; ?>" <?php if($anio == $anioSelect) { echo 'selected'; } ?>><?php echo $anioSelect; ?></option>
                        <?php
                            $anioSelect--;
                        }
                        ?>
                    </select>
                </div>
                <div class="col-xs-3">
                    <b>Estado Orden del Día</b>
                    <select class="form-control" id="estadoOrdenDia" name="estadoOrdenDia" onChange='this.form.submit()'>
                        <option value="A" <?php if($estadoOrdenDia == 'A') { echo 'selected'; } ?>>Abierto</option>
                        <option value="C" <?php if($estadoOrdenDia == 'C') { echo 'selected'; } ?>>Cerrado</option>
                        <option value="B" <?php if($estadoOrdenDia == 'B') { echo 'selected'; } ?>>Borrado</option>
                    </select>
                </div>
            </form>    
        </div>
        <div class="col-md-8 text-right">
            <form  method="POST" action="orden_del_dia_form.php">
                <button type="submit" class="btn btn-primary" name='agregar' id='name'>Agregar orden del día </button>
                <input type="hidden" id="accion" name="accion" value="1">
            </form>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <?php
    $resOrden = $ordenDelDiaLogic->obtenerOrdenDelDia($anio, $estadoOrdenDia);
    if ($resOrden['estado']){
    ?>
        <table id="tablaOrdenada" class="display">
            <thead>
                <tr>
                    <th style="display: none;">Id</th>
                    <th>Número</th>
                    <th>Fecha Reunión</th>
                    <th>Período</th>
                    <th>Fecha Desde</th>
                    <th>Fecha Hasta</th>
                    <?php 
                    //<th>Estado</th>
                    if ($estadoOrdenDia <> 'B') {
                    ?>
                        <th>Acción orden del día</th>
                        <th>Detalle</th>
                    <?php 
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
              <?php
                  foreach ($resOrden['datos'] as $dato) 
                  {
                      $idOrdenDia = $dato['id'];
                      $numero = $dato['numero'];
                      $fecha = cambiarFechaFormatoParaMostrar($dato['fecha']);
                      $fechaDesde = cambiarFechaFormatoParaMostrar($dato['fechaDesde']);
                      $fechaHasta = cambiarFechaFormatoParaMostrar($dato['fechaHasta']);
                      $periodo = $dato['periodo'];
                      $cantidadDetalle = $dato['cantidadDetalle'];
                      if ($cantidadDetalle > 0) { 
                        $botonDetalle = 'Ver'; 
                        $botonClase = 'btn btn-warning';
                      } else { 
                        $botonDetalle = 'Generar'; 
                        $botonClase = 'btn btn-success';
                      }
                      $estado = $dato['estado'];
                      /*
                      switch ($estado) {
                          case 'A':
                              $estadoOrdenDia = "Abierto";
                              break;
                          
                          case 'B':
                              $estadoOrdenDia = "Borrado";
                              break;
                          
                          case 'C':
                              $estadoOrdenDia = "Cerrado";
                              break;
                          
                          default:
                              // code...
                              break;
                      }
                      */
                  ?>
                    <tr>
                        <td style="display: none;"><?php echo $idOrdenDia;?></td>
                        <td><?php echo $numero;?></td>
                        <td><?php echo $fecha;?></td>
                        <td><?php echo $periodo;?></td>
                        <td><?php echo $fechaDesde;?></td>
                        <td><?php echo $fechaHasta;?></td>
                        <?php 
                        //<td><?php echo $estadoOrdenDia;</td>
                        if ($estadoOrdenDia <> 'B') {
                        ?>
                            <td>
                                <a href="orden_del_dia_form.php?id=<?php echo $idOrdenDia; ?>&accion=3" class="btn btn-primary" role="button" >Editar</a>
                                &nbsp;
                                <a href="datosOrdenDelDia/abm_orden_del_dia.php?id=<?php echo $idOrdenDia; ?>&accion=2" class="btn btn-danger" role="button" onclick="return confirmar()">Eliminar</a>
                            </td>
                            <td>
                                <a href="orden_del_dia_detalle.php?id=<?php echo $idOrdenDia; ?>" class="<?php echo $botonClase; ?>" role="button" >
                                    <?php echo $botonDetalle; ?></a>
                                <?php 
                                if ($cantidadDetalle > 0 && $estado == 'A') {
                                ?>
                                    &nbsp;
                                    <a href="datosOrdenDelDia/abm_orden_del_dia_detalle.php?id=<?php echo $idOrdenDia; ?>&accion=2" class="<?php echo $botonClase; ?>" role="button" onclick="return confirmar()">Borrar detalle</a>
                                <?php 
                                }
                                ?>
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
    <?php
    } else {
      ?>
        <div class="<?php echo $resOrden['clase']; ?>" role="alert">
            <span class="<?php echo $resOrden['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resOrden['mensaje']; ?></strong></span>
        </div>
    <?php    
    }    
?>
</div>
</div>
<?php
require_once '../html/footer.php';