<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/deudoresLogic.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
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

function confirmaAnular(accion)
{
    if(confirm('¿Estas seguro de ' + accion + ' listado?'))
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
    <div class="panel-heading"><h4><b>Listado de Deuda</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] != "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            } else {
                $periodoSeleccionado = date('Y');
            }

            $deudoresLogic = new deudoresLogic();
            $resDeudores = $deudoresLogic->obtenerListadoDeudoresPorPeriodo($periodoSeleccionado);
            ?>
            <div class="row">
                <div class="col-xs-6">
                    <form method="POST" action="deudores_listado.php">
                        <div class="col-xs-5">
                            <label for="periodoSeleccionado">Seleccione Año:</label>
                            <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required onChange="this.form.submit()">
                                <option value="" selected>Seleccione Período</option>
                                <?php
                                $periodo = date('Y');
                                while ($periodo >= 2012) {
                                ?>
                                    <option value="<?php echo $periodo; ?>" <?php if($periodo == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodo; ?></option>
                                <?php
                                    $periodo--;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-3">&nbsp;</div>
                    </form>    
                </div>
                <div class="col-xs-3">
                </div>
                <div class="col-xs-3 text-center">
                    <?php
                    if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 120)) {
                    ?>
                        <a href="deudores_form.php?agregar" class="btn btn-info" >Generar Listado de Deudores</a>
                    <?php 
                    } 
                    ?>
                </div>
            </div>
            <?php
            if ($resDeudores['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Id</th>
                                <th style="text-align: center;">Fecha Proceso</th>
                                <th style="text-align: center;">Período límite</th>
                                <th style="text-align: center;">Tipo de listado</th>
                                <th style="text-align: center;">Mínimo Cuotas adeudadas</th>
                                <th style="text-align: center;">Envío notifiación</th>
                                <th style="width: 400px; text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resDeudores['datos'] as $dato) {
                          $idDeudores = $dato['id'];
                          $fecha_proceso = $dato['fecha_proceso'];
                          $periodo_limite = $dato['periodo_limite'];
                          $tipo_filtro = $dato['tipo_filtro'];
                          if ($tipo_filtro == '6') {
                            $idEnvioDiario = 25; //Notificación a deudores más de 2 períodos
                          } else {
                            $idEnvioDiario = 1; //Notificación de Deuda
                          }
                          $tipo_filtro_detalle = $dato['tipo_filtro_detalle'];
                          $cuotas_adeudadas = $dato['cuotas_adeudadas'];
                          $idNotificacion = $dato['idNotificacion'];
                        ?>
                        <tr style="text-align: center;">
                    	   <td><?php echo $idDeudores;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fecha_proceso);?></td>
                           <td><?php echo $periodo_limite;?></td>
                           <td><?php echo $tipo_filtro_detalle;?></td>
                           <td><?php echo $cuotas_adeudadas;?></td>
                           <td>
                                <?php 
                                if (!isset($idNotificacion) || $idNotificacion == "") {
                                ?>
                                    <a href="notificacion_generar_form.php?idDeudores=<?php echo $idDeudores; ?>&idEnvioDiario=<?php echo $idEnvioDiario; ?>" class="btn btn-info">Generar notificación</a>
                                <?php 
                                } else {
                                    echo 'Envío generado';
                                }
                                ?>
                            </td>
                           <td style="text-align: left;">
                                <a href="deudores_detalle.php?id=<?php echo $idDeudores; ?>" class="btn btn-info">Ver deudores</a>
                                <a href="deudores_descargar_archivo.php?id=<?php echo $idDeudores; ?>" class="btn btn-info">Descargar archivo</a>
                                <?php 
                                if (!isset($idNotificacion) || $idNotificacion == "") {
                                ?>
                                    <a href="datosDeudores/generar_deudores.php?idDeudores=<?php echo $idDeudores; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular('Borrar')">Borrar listado</a>
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