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

function confirmaAnular(estado)
{
    if(confirm('¿Estas seguro de '+ estado +' registro?'))
        return true;
    else
        return false;
}

</script>

<?php
$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursoEntidad = $_GET['id'];
    $calendarioLogic = new calendario_eventosLogic();
    $resCursoEntidad = $calendarioLogic->obtenerCursoEntidadPorId($idCursoEntidad);
    if ($resCursoEntidad['estado']) {
        $cursoEntidad = $resCursoEntidad['datos'];
        $tituloCursoEntidad = $cursoEntidad['titulo'];
        $estadoCurso = $cursoEntidad['estado'];
    } else {
        $continua = FALSE;
        $mensaje .= $resCursoEntidad['mensaje'];
    }
}

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
    <div class="panel-heading"><h4><b>Listado de Turnos de <?php echo $tituloCursoEntidad; ?></b></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['estado']) && $_POST['estado'] <> "") {
                $estado = $_POST['estado'];
            } else {
                $estado = 'A';
            }
            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] <> "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            } else {
                $periodoSeleccionado = date('Y');
            }

            $resCursoAulas = $calendarioLogic->obtenerCursoAulaPorIdCursoEntidad($idCursoEntidad, $estado);  
            ?>
            <div class="row">
                <div class="col-xs-6">
                    <form method="POST" action="calendario_eventos_administrar_turnos.php?id=<?php echo $idCursoEntidad; ?>">
                        <div class="col-xs-6">
                            <label for="estado">Estado: </label>
                            <select class="form-control" id="estado" name="estado" required onChange="this.form.submit()">
                                <option value="A" <?php if($estado == "A") { echo 'selected'; } ?>>Activo</option>
                                <option value="B" <?php if($estado == "B") { echo 'selected'; } ?>>Borrado</option>
                            </select>
                        </div>
                        <div class="col-xs-3">&nbsp;</div>
                    </form>    
                </div>
                <div class="col-xs-2"></div>
                <div class="col-xs-2 text-center">
                    <a href="calendario_eventos_curso_aula_form.php?idCursoEntidad=<?php echo $idCursoEntidad; ?>&agregar&periodo=<?php echo $periodoSeleccionado; ?>" class="btn btn-info" >Agregar Aula al Evento/Curso</a>
                </div>
                <div class="col-xs-1 text-center">
                    <!--<a href="calendario_eventos_calendario.php?idCursoEntidad=<?php echo $idCursoEntidad; ?>" class="btn btn-info" >Ver calendario</a>-->
                    <a href="../cursos/controller/controladorCalendario.php?seccion=calendario" class="btn btn-info" target="_BLANK">Ver calendario</a>
                </div>
                <div class="col-xs-1 text-center">
                    <form method="POST" action="calendario_eventos.php">
                        <button type="submit" name='confirma' id='confirma' class="btn btn-info">Volver</button>
                        <input type="hidden" name="estadoCurso" id="estadoCurso" value="<?php echo $estadoCurso; ?>">
                        <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>">
                    </form>
                </div>
            </div>
            <?php
            if ($resCursoAulas['estado']){
            ?>
                <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Aula</th>
                                <th>Día</th>
                                <th>Hora Desde / Hasta</th>
                                <th>Fecha Desde / Hasta</th>
                                <th>Autorizado por</th>
                                <th>Fecha de carga</th>
                                <th>Usuario de carga</th>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resCursoAulas['datos'] as $dato) {
                          $idCursoAula = $dato['idCursoAula'];
                          $nombreAula = $dato['nombreAula'];
                          $nombreDia = $dato['nombreDia'];
                          $horaDesde = $dato['horaDesde'];
                          $horaHasta = $dato['horaHasta'];
                          $fechaDesde = $dato['fechaDesde'];
                          $fechaHasta = $dato['fechaHasta'];
                          $autorizado = $dato['autorizado'];
                          $fechaCarga = $dato['fechaCarga'];
                          $nombreUsuario = $dato['nombreUsuario'];
                        ?>
                        <tr>
                        	<td><?php echo $idCursoAula;?></td>
                            <td><?php echo $nombreAula;?></td>
                            <td><?php echo $nombreDia;?></td>
                            <td style="text-align: center;"><?php echo $horaDesde.' a '.$horaHasta;?></td>
                            <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fechaDesde).' al '.cambiarFechaFormatoParaMostrar($fechaHasta);?></td>
                            <td><?php echo $autorizado;?></td>
                            <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar(substr($fechaCarga, 0, 10));?></td>
                            <td><?php echo $nombreUsuario;?></td>
                            <td style="width: 400px;">
                                <?php 
                                if ($estado == 'A') {
                                    //el editar lo ocultamos
                                    /*<a href="calendario_eventos_curso_aula_form.php?id=<?php echo $idCursoAula; ?>&idCursoEntidad=<?php echo $idCursoEntidad; ?>&editar" class="btn btn-info">Editar </a>*/
                                ?>
                                    <a href="datosCalendarioEventos/abm_curso_aula.php?id=<?php echo $idCursoAula; ?>&borrar&periodo=<?php echo $periodoSeleccionado; ?>" class="btn btn-info" onclick="return confirmaAnular('Borrar')">Borrar </a>
                                <?php 
                                }
                                ?>
                                <a href="calendario_eventos_ver_turnos.php?id=<?php echo $idCursoAula; ?>&periodo=<?php echo $periodoSeleccionado; ?>" class="btn btn-info">Ver Turnos </a>
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
                <div class="<?php echo $resCursoAulas['clase']; ?>" role="alert">
                    <span class="<?php echo $resCursoAulas['icono']; ?>" ></span>
                    <span><strong><?php echo $resCursoAulas['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';