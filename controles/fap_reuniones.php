<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fapLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ]],
            //"order": [[ 2, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

function confirmaAnular()
{
    if(confirm('¿Estas seguro de Borrar registro?'))
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
$fapLogic = new fapLogic();
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Listado de FAP presentados para Reuniones de Consejo</b></h4></div>
    <div class="panel-body">
            <?php
            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] != "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            } else {
                if (isset($_GET['anio']) && $_GET['anio'] != "") {
                    $periodoSeleccionado = $_GET['anio'];
                } else {
                    $periodoSeleccionado = date('Y');
                }
            }
            if (isset($_POST['estadoSeleccionado']) && $_POST['estadoSeleccionado'] != "") {
                $estadoSeleccionado = $_POST['estadoSeleccionado'];
            } else {
                if (isset($_GET['estado']) && $_GET['estado'] != "") {
                    $estadoSeleccionado = $_GET['estado'];
                } else {
                    $estadoSeleccionado = 'T';
                }
            }
            ?>
            <div class="row">
                <form method="POST" action="fap_reuniones.php">
                    <div class="col-md-1">
                        <label for="periodoSeleccionado">Año: </label>
                        <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required onChange="this.form.submit()">
                            <option value="9999" <?php if($periodoSeleccionado == '9999') { echo 'selected'; } ?>>Todos</option>
                            <?php 
                            $periodo = date('Y');
                            while ($periodo >= 2004) {
                            ?>
                                <option value="<?php echo $periodo; ?>" <?php if($periodo == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodo; ?></option>
                            <?php 
                                $periodo -= 1;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label for="estadoSeleccionado">Estado: </label>
                        <select class="form-control" id="estadoSeleccionado" name="estadoSeleccionado" required onChange="this.form.submit()">
                            <option value="T" <?php if($estadoSeleccionado == 'T') { echo 'selected'; } ?>>Todos</option>
                            <option value="A" <?php if($estadoSeleccionado == 'A') { echo 'selected'; } ?>>Abiertas</option>
                            <option value="C" <?php if($estadoSeleccionado == 'C') { echo 'selected'; } ?>>Cerradas</option>
                        </select>
                    </div>
                </form>
                <div class="col-md-8">&nbsp;</div> 
                <div class="col-md-2 text-center">
                    <br>
                    <a href="fap_reuniones_form.php?agregar" class="btn btn-primary">Agregar Reunión </a>
                </div>
            </div>
            <?php
            $resReuniones = $fapLogic->obtenerReunionesPorPeriodo($periodoSeleccionado, $estadoSeleccionado);
            if ($resReuniones['estado']){
            ?>
                <div class="row">&nbsp;</div> 
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Fecha</th>
                            <th>Resolución</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php
                      foreach ($resReuniones['datos'] as $dato) {
                          $idSapConsejo = $dato['idSapConsejo'];
                          $fechaReunion = $dato['fechaReunion'];
                          $resolucion = $dato['resolucion'];
                          $estadoReunion = $dato['estadoReunion'];
                          $nombreEstadoReunion = $dato['nombreEstadoReunion'];
                          $observaciones = $dato['observaciones'];
                        ?>
                        <tr>
                    	   <td><?php echo $idSapConsejo;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fechaReunion);?></td>
                           <td><?php echo $resolucion;?></td>
                           <td><?php echo $nombreEstadoReunion;?></td>
                           <td><?php echo $observaciones;?></td>
                           <td style="text-align: center;">
                                <a href="fap_reuniones_form.php?id=<?php echo $idSapConsejo; ?>&editar" class="btn btn-primary <?php if ($estadoReunion == fapLogic::ESTADO_REUNION_CERRADA) { echo 'disabled'; } ?>">Editar</a>
                                <a href="fap_reuniones_imprimir.php?id=<?php echo $idSapConsejo; ?>" class="btn btn-primary">Imprimir</a>
                                <a href="datosFap/abm_reuniones.php?id=<?php echo $idSapConsejo; ?>&cerrar" onclick="return confirmarCierre()" class="btn btn-primary <?php if ($estadoReunion == fapLogic::ESTADO_REUNION_CERRADA) { echo 'disabled'; } ?>">Cerrar</a>
                                <a href="fap_reuniones_detalle.php?id=<?php echo $idSapConsejo; ?>" class="btn btn-primary">Ver Detalle</a>
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
                <div class="row">&nbsp;</div>
                <div class="<?php echo $resReuniones['clase']; ?>" role="alert">
                    <span class="<?php echo $resReuniones['icono']; ?>" ></span>
                    <span><strong><?php echo $resReuniones['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';
?>
<script language="JavaScript">

    function confirmarCierre()
    {
        if(confirm('¿Estas seguro de CERRAR esta reunión?'))
            return true;
        else
            return false;
    }
    
</script>
