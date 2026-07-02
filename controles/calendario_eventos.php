<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/calendario_eventos_Logic.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 1, "asc" ]],
            //"order": [[ 2, "desc" ], [ 1, "asc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });   

function confirmaAnular(estado)
{
    if(confirm('¿Estas seguro de '+ estado +' registro?'))
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
    <div class="panel-heading"><h4><b>Listado de Eventos/Cursos</b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['estadoCurso']) && $_POST['estadoCurso'] != "") {
                $estadoCurso = $_POST['estadoCurso'];
            } else {
                $estadoCurso = 'A';
            }
            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] != "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            } else {
                $periodoSeleccionado = date('Y');
            }

            $calendarioLogic = new calendario_eventosLogic();
            $resEventos = $calendarioLogic->obtenerCursoEntidadPorEstado($estadoCurso, $periodoSeleccionado);  

            ?>
            <div class="row">
                <div class="col-xs-6">
                    <form method="POST" action="calendario_eventos.php">
                        <div class="col-xs-4">
                            <label for="periodoSeleccionado">Seleccione período con turnos: </label>
                            <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required onChange="this.form.submit()">
                                <option value="TODOS" <?php if($periodoSeleccionado == "TODOS") { echo 'selected'; } ?>>Ver Todos</option>
                                <?php
                                $periodo = date('Y') + 1;
                                while ($periodo >= 2012) {
                                ?>
                                    <option value="<?php echo $periodo; ?>" <?php if($periodo == $periodoSeleccionado) { echo 'selected'; } ?>><?php echo $periodo; ?></option>
                                <?php 
                                    $periodo -= 1; 
                                } 
                                ?>
                            </select>
                        </div>
                        <div class="col-xs-6">
                            <label for="estadoCurso">Estado: </label>
                            <select class="form-control" id="estadoCurso" name="estadoCurso" required onChange="this.form.submit()">
                                <option value="A" <?php if($estadoCurso == "A") { echo 'selected'; } ?>>Activo</option>
                                <option value="F" <?php if($estadoCurso == "F") { echo 'selected'; } ?>>Finalizado</option>
                                <option value="B" <?php if($estadoCurso == "B") { echo 'selected'; } ?>>Borrado</option>
                            </select>
                        </div>
                    </form>    
                </div>
                <div class="col-xs-3"></div>
                <div class="col-xs-1 text-center">
                    <a href="calendario_eventos_form.php?agregar" class="btn btn-info" >Agregar Evento/Curso</a>
                </div>
                <div class="col-xs-2 text-center">
                    <!--<a href="calendario_eventos_calendario.php?idCursoEntidad=<?php echo $idCursoEntidad; ?>" class="btn btn-info" >Ver calendario</a>-->
                    <a href="../cursos/controller/controladorCalendario.php?seccion=calendario" class="btn btn-info" target="_BLANK">Ver calendario</a>
                </div>
            </div>
            <?php
            if ($resEventos['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Título</th>
                                <th>Director</th>
                                <th>Fecha Inicio</th>
                                <th>Vigencia Hasta</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resEventos['datos'] as $dato) {
                          $idCursoEntidad = $dato['idCursoEntidad'];
                          $titulo = $dato['titulo'];
                          $director = $dato['director'];
                          $fechaInicio = $dato['fechaInicio'];
                          $vigenciaHasta = $dato['vigenciaHasta'];
                        ?>
                        <tr>
                    	   <td><?php echo $idCursoEntidad;?></td>
                           <td><?php echo $titulo;?></td>
                           <td><?php echo $director;?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($fechaInicio);?></td>
                           <td><?php echo cambiarFechaFormatoParaMostrar($vigenciaHasta);?></td>
                           <td style="width: 400px;">
                            <a href="calendario_eventos_form.php?id=<?php echo $idCursoEntidad; ?>&editar" class="btn btn-info">Editar </a>
                            <?php 
                            if ($estadoCurso == 'A') {
                            ?>
                                <a href="datosCalendarioEventos/abm_curso_entidad.php?id=<?php echo $idCursoEntidad; ?>&finalizar" class="btn btn-info" onclick="return confirmaAnular('Finalizar')">Finalizar </a>
                            <?php 
                            } else {
                                if ($estadoCurso == 'F') {
                                ?>
                                    <a href="datosCalendarioEventos/abm_curso_entidad.php?id=<?php echo $idCursoEntidad; ?>&abrir" class="btn btn-info" onclick="return confirmaAnular('Abrir')">Abrir </a>
                                <?php 
                                }
                            }
                            ?>
                            <a href="datosCalendarioEventos/abm_curso_entidad.php?id=<?php echo $idCursoEntidad; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular('Borrar')">Borrar </a>
                            <a href="calendario_eventos_administrar_turnos.php?id=<?php echo $idCursoEntidad; ?>" class="btn btn-info">Administrar Turnos </a>
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
                <div class="<?php echo $resEventos['clase']; ?>" role="alert">
                    <span class="<?php echo $resEventos['icono']; ?>" ></span>
                    <span><strong><?php echo $resEventos['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';