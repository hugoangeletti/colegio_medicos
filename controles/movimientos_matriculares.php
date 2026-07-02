<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 0, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": true,
            "bLengthChange": false,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });
            
function confirmar()
{
    if(confirm('¿Estas seguro de eliminar el movimiento?'))
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
?> 
<div class="panel panel-default">
<div class="panel-heading"><h4><b>Movimientos Matriculares</b></h4></div>
<div class="panel-body">
    <div class="row">
        <?php
        if (isset($_POST['fechaIngreso']) && $_POST['fechaIngreso'] != ""){
            $fechaIngreso = $_POST['fechaIngreso'];
        } else {
            $fechaIngreso = date('Y-m-d');
        }
        if (isset($_POST['matricula']) && $_POST['matricula'] != "") {
            $matricula = $_POST['matricula'];            
            $fechaIngreso = NULL;
        } else {
            $matricula = NULL;
        }
        ?>
        <div class="col-md-12">
            <div class="col-md-1"><b>Buscar por</b></div>
            <div class="col-md-2">
                <form method="POST" action="movimientos_matriculares.php">
                    <b>Fecha de Ingreso</b>
                    <input class="form-control" type="date" name="fechaIngreso" value="<?php echo $fechaIngreso; ?>" onChange="this.form.submit()"/>
                </form>    
            </div>
            <div class="col-md-1">&nbsp;</div>
            <div class="col-md-5">
                <form method="POST" action="movimientos_matriculares.php">
                    <div class="col-md-7">
                        <b>Matricula</b>
                        <input class="form-control" type="number" name="matricula" />
                    </div>
                    <div class="col-md-5">
                        <br>
                        <button type="submit"  class="btn btn-info " >Buscar</button>
                    </div>
                </form>    
            </div>
            <div class="col-md-3">
                <a href="movimientos_matriculares_form.php?accion=1" class="btn btn-success btn-lg">Nuevo Expediente</a>
            </div>
        </div>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12">
            <?php
            $resExpedientes = $mesaEntradaEspecialistaLogic->obtenerExpedientesPorFechaMatricula($fechaIngreso, $matricula);   
            if ($resExpedientes['estado']){
            ?>            
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th style="display: none;">Id</th>
                            <th>Ingreso</th>
                            <th>Expediente</th>
                            <th>Matrícula</th>
                            <th>Apellido y Nombre</th>
                            <th>Especialidad</th>
                            <th>Solicitud de </th>
                            <?php 
                            if ($fechaIngreso == date('Y-m-d')) {
                            ?>
                                <th style="text-align: center;">Editar</th>
                                <!--<th style="text-align: center;">Borrar</th>-->
                            <?php 
                            } else {
                            ?>
                                <th style="text-align: center;">Observaciones</th>
                            <?php
                            }
                            ?>
                            <th style="text-align: center;">Imprimir</th>
                        </tr>
                    </thead>
              <tbody>
                  <?php
                      foreach ($resExpedientes['datos'] as $dato) 
                      {
                          $idMesaEntradaEspecialidad = $dato['idMesaEntradaEspecialidad'];
                          $nombreEspecialidad = $dato['nombreEspecialidad'];
                          $numeroExpediente = $dato['numeroExpediente'];
                          $anioExpediente = $dato['anioExpediente'];
                          $fechaIngresoMesaEntrada = cambiarFechaFormatoParaMostrar($dato['fechaMesaEntrada']);
                          $idColegiado = $dato['idColegiado'];
                          $matricula = $dato['matricula'];
                          $apellidoNombre = $dato['apellidoNombre'];
                          $tipoTramite = $dato['nombreTipoEspecialista'];
                          $tipoEspecialidad = $dato['tipoTramiteEspecialista'];
                          $inciso = $dato['inciso'];
                          $distrito = $dato['distrito'];
                          $codigoEspecialista = $dato['codigoEspecialista'];
                          if (isset($dato['numeroResolucion'])) {
                            $numeroResolucion = $dato['numeroResolucion'];
                          } else {
                              $numeroResolucion = NULL;
                          }
                          if (isset($dato['fechaResolucion'])) {
                            $fechaResolucion = $dato['fechaResolucion'];
                            $clase = 'class="alert alert-warning"';
                          } else {
                            $fechaResolucion = NULL;
                            $clase = '';
                          }
                      ?>
                        <tr <?php echo $clase; ?>>
                            <td style="display: none;"><?php echo $idMesaEntradaEspecialidad;?></td>
                            <td><?php echo $fechaIngresoMesaEntrada;?></td>
                            <td><?php echo $numeroExpediente.'/'.$anioExpediente;?></td>
                            <td><?php echo $matricula;?></td>
                            <td><?php echo $apellidoNombre;?></td>
                            <td><?php echo $nombreEspecialidad;?></td>
                            <td><?php if ($codigoEspecialista <> "N") {
                                echo $tipoTramite; 
                            } else {
                                echo "Renovación";
                            }?></td>
                            <?php
                            if (isset($numeroResolucion)) {
                                if ($fechaIngreso == date('Y-m-d')) {
                                ?>
                                    <td style="text-align: center;">
                                        Res.Nº <b><?php echo $numeroResolucion; ?></b>
                                    </td>
                                    <td>Fecha: <?php echo $fechaResolucion; ?></td>
                                <?php 
                                } else {
                                ?>
                                    <td style="text-align: center;">
                                        Res.Nº <b><?php echo $numeroResolucion; ?></b> - Fecha: <b><?php echo cambiarFechaFormatoParaMostrar($fechaResolucion); ?></b>
                                    </td>
                                <?php 
                                }
                            } else {
                                if ($fechaIngreso == date('Y-m-d') || $_SESSION['user_id'] == 1 || $_SESSION['user_id'] == 29) {
                                    if ($tipoEspecialidad <> "R" && $tipoEspecialidad <> "J" && $tipoEspecialidad <> "C") {
                                    ?>
                                        <td style="text-align: center;">
                                            <form  method="POST" action="especialidades_expedientes_alta.php?accion=3">
                                                <button type="submit" class="btn btn-primary glyphicon glyphicon-pencil btn-sm" name='editar' id='name'></button>
                                                <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                                <input type="hidden" id="tipo" name="tipo" value="A">
                                                <input type="hidden" id="id" name="id" value="<?php echo $idMesaEntradaEspecialidad; ?>">
                                            </form>
                                        </td>
                                    <?php
                                    } else {
                                    ?>
                                        <td>&nbsp;</td>
                                    <?php
                                    }
                                    ?>
<!--                                    <td style="text-align: center;">
                                        <a href="especialidades_expedientes_borrar.php?id=<?php echo $idMesaEntradaEspecialidad; ?>" 
                                           class="btn btn-danger glyphicon glyphicon-erase btn-sm" onclick="return confirmar()"></a>
                                    </td>-->
                                <?php
                                } else {
                                ?>
                                    <td>&nbsp;</td>
                                <?php
                                }
                            }
                            ?>
                            <td>
                                <div align="center">
                                    <a href="datosMesaEntrada/especialidades_expedientes_imprimir.php?n_exp=<?php echo $numeroExpediente; ?>&a_exp=<?php echo $anioExpediente; ?>" target="_BLANK" 
                                       class="btn btn-info glyphicon glyphicon-print btn-sm"></a>
                                </div>    
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
                <div class="<?php echo $resExpedientes['clase']; ?>" role="alert">
                    <span class="<?php echo $resExpedientes['icono']; ?>" ></span>
                    <span><strong><?php echo $resExpedientes['mensaje']; ?></strong></span>
                </div>
        <?php
        }    
        ?>
        </div>
    </div>
</div>
</div>
<?php
require_once '../html/footer.php';