<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/cursos_pdo.php');
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
?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Listado de Cursos</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['estadoCurso']) && $_POST['estadoCurso'] != "") {
                $estadoCurso = $_POST['estadoCurso'];
            } else {
                $estadoCurso = 'A';
            }

            $cursos_pdo = new cursos_pdo();
            $resCursos = $cursos_pdo->obtenerCursos($estadoCurso);            
            ?>
            <div class="row">
                <div class="col-xs-6">
                    <form method="POST" action="curso_listado.php">
                        <div class="col-xs-6">
                            <select class="form-control" id="estadoCurso" name="estadoCurso" required onChange="this.form.submit()">
                                <option value="A" <?php if($estadoCurso == "A") { echo 'selected'; } ?>>Activo</option>
                                <option value="F" <?php if($estadoCurso == "F") { echo 'selected'; } ?>>Finalizado</option>
                            </select>
                        </div>
                        <div class="col-xs-3">&nbsp;</div>
                    </form>    
                </div>
                <div class="col-xs-3"></div>
                <div class="col-xs-1 text-center">
                    <a href="curso_form.php?agregar" class="btn btn-info" >Agregar Curso</a>
                </div>
                <div class="col-xs-2 text-center">
                    <!--<a href="curso_listado_pago_form.php" class="btn btn-info" >Listado de pagos</a>-->
                    <button type="submit" class="btn btn-toolbar btn-info" data-toggle="modal" data-target="#pagosModal">Listado de pagos</button>
                    <div id="pagosModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header alert alert-info">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Listado de pagos</h4>
                                </div>              
                                <!-- dialog body -->
                                <div class="modal-body">
                                    <form method="POST" action="curso_listado_pagos.php" target="_BLANK">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <select class="form-control" id="idCurso" name="idCurso">
                                                    <option value="">Todos los cursos</option>                     
                                                    <?php
                                                    foreach ($resCursos['datos'] as $dato) {
                                                        $idCursoSelect = $dato['idCurso'];
                                                        $titulo = $dato['titulo'];
                                                        ?>
                                                        <option value="<?php echo $idCursoSelect; ?>"><?php echo $titulo; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="">&nbsp;</div>
                                            <div class="col-md-6">
                                                <label for="fechaDesde">Fecha Desde *</label>
                                                <input type="date" name="fechaDesde" id="fechaDesde" value="<?php echo $fechaDesde; ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fechaHasta">Fecha Hasta *</label>
                                                <input type="date" name="fechaHasta" id="fechaHasta" value="<?php echo $fechaHasta; ?>" required>
                                            </div>
                                            <div class="">&nbsp;</div>
                                            <div class="col-md-12">
                                                <label for="tipoListado">Tipo de listado</label>
                                                <label class="radio-inline" selected ><input type="radio" name="tipoListado" id="tipoListado" value="DETALLE" >Totales y detalle</label>
                                                <label class="radio-inline"><input type="radio" name="tipoListado" id="tipoListado" value="TOTALES" >Solo totales</label>
                                            </div>
                                            <div class="">&nbsp;</div>
                                            <div class="col-md-12">
                                                <label for="totalizado">Totatalizado por</label>
                                                <label class="radio-inline" selected ><input type="radio" name="totalizado" id="totalizado" value="FECHA" >Cursos y fecha de pago</label>
                                                <label class="radio-inline"><input type="radio" name="totalizado" id="totalizado" value="CUOTA" >Cursos y cuotas</label>
                                            </div>
                                            <div class="">&nbsp;</div>
                                            <div class="col-md-12 text-center">
                                                <button type="submit"  class="btn btn-info btn-lg" >Confirma</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ($resCursos['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Título</th>
                                <th>Director</th>
                                <th>Fecha Inicio</th>
                                <th>Estado</th>
                                <th>Asistentes</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resCursos['datos'] as $dato) {
                          $idCurso = $dato['idCurso'];
                          $titulo = $dato['titulo'];
                          $director = $dato['director'];
                          $fechaInicio = $dato['fechaInicio'];
                          $estado = $dato['estado'];
                          switch ($estado) {
                              case 'A':
                                  $estadoCurso = "Activo";
                                  break;
                              
                              case 'F':
                                  $estadoCurso = "Finalizado";
                                  break;
                              
                              default:
                                  $estadoCurso = "Sin detalle (".$estado.")";
                                  break;
                          }
                          $cantidadCuotas = $dato['cantidadCuotas'];
                          $cantidadAsistentes = $dato['cantidadAsistentes'];
                        ?>
                        <tr>
                    	   <td><?php echo $idCurso;?></td>
                           <td><?php echo $titulo;?></td>
                           <td><?php echo $director;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fechaInicio);?></td>
                           <td><?php echo $estadoCurso;?></td>
                           <td>
                                <?php 
                                if ($cantidadCuotas > 0) {
                                ?>
                                    <a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-info">Asistentes del curso (<?php echo $cantidadAsistentes; ?>)</a>
                                <?php 
                                } else {
                                    echo '<b>Falta cuotas del curso</b>';
                                } 
                                ?>
                           </td>
                           <td style="width: 300px;">
                               <div class="btn-group">
                                  <button type="button" class="btn btn-primary dropdown-toggle"
                                          data-toggle="dropdown">
                                    Acciones <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="curso_form.php?id=<?php echo $idCurso; ?>" class="btn btn-info">Consultar curso</a>
                                    </li>
                                    <li>
                                        <a href="curso_form.php?id=<?php echo $idCurso; ?>&editar" class="btn btn-info">Editar curso</a>
                                    </li>
                                    <li>
                                        <a href="curso_cuotas.php?id=<?php echo $idCurso; ?>" class="btn btn-info">Cuotas del curso</a>
                                    </li>
                                    <li>
                                        <a href="datosCurso/abm_curso.php?id=<?php echo $idCurso; ?>&finalizar" class="btn btn-info" onclick="return confirmaAnular()">Finalizar curso</a>
                                    </li>
                                  </ul>
                                </div>
                               <div class="btn-group">
                                  <button type="button" class="btn btn-primary dropdown-toggle"
                                          data-toggle="dropdown">
                                    Listados <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="curso_listado_alfabetico.php?id=<?php echo $idCurso; ?>" class="btn btn-info" target="_BLANK">Alfabético</a>
                                    </li>
                                    <li>
                                        <a href="curso_listado_tesoreria.php?id=<?php echo $idCurso; ?>" class="btn btn-info" target="_BLANK">Situación con Tesorería</a>
                                    </li>
                                    <li>
                                        <a href="curso_asistentes_chequeras.php?idCurso=<?php echo $idCurso; ?>&completo" class="btn btn-info" >Chequeras</a>
                                    </li>
                                  </ul>
                                </div>
                                <a href="curso_tesoreria_form.php?id=<?php echo $idCurso; ?>&tesoreria" class="btn btn-primary">Tesorería</a>
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
                <div class="<?php echo $resCursos['clase']; ?>" role="alert">
                    <span class="<?php echo $resCursos['icono']; ?>" ></span>
                    <span><strong><?php echo $resCursos['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';