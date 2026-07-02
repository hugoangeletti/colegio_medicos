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
            "iDisplayLength":50,
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
$continua = TRUE;
$mensaje = "";
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCursoAula = $_GET['id'];
    $tituloCursoEntidad = "";
    $calendarioLogic = new calendario_eventosLogic();
    $resCursoAula = $calendarioLogic->obtenerCursoAulaPorId($idCursoAula);
    if ($resCursoAula['estado']) {
        $cursoAula = $resCursoAula['datos'];
        $idCursoEntidad = $cursoAula['idCursoEntidad'];
        $nombreAula = $cursoAula['nombreAula'];
        $nombreDia = $cursoAula['nombreDia'];
        $titulo = $cursoAula['titulo'];
        $estadoCurso = $cursoAula['estado'];
        $tituloCursoEntidad = '<b>'.$titulo.'</b> en el Aula <b>'.$nombreAula.'</b> del día <b>'.$nombreDia.'</b>';
    } else {
        $continua = FALSE;
        $mensaje .= $resCursoAula['mensaje'];
    }
}
if (isset($_GET['periodo']) && $_GET['periodo'] <> "") {
    $periodoSeleccionado = $_GET['periodo'];
} else {
    $periodoSeleccionado = date('Y');
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
    <div class="panel-heading"><h4>Listado de Turnos de <?php echo $tituloCursoEntidad; ?></h4></div>
    <div class="panel-body">
        <div class="row">
            <?php
            if (isset($_POST['estado']) && $_POST['estado'] != "") {
                $estado = $_POST['estado'];
            } else {
                $estado = 'A';
            }

            $resCursoAulaTurnos = $calendarioLogic->obtenerCursoAulaTurnoPorIdCursoAula($idCursoAula, $estado);  
            ?>
            <div class="row">
                <div class="col-xs-6">
                    <form method="POST" action="calendario_eventos_ver_turnos.php?id=<?php echo $idCursoAula; ?>">
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
                </div>
                <div class="col-xs-1 text-center">
                </div>
                <div class="col-xs-1 text-center">
                    <form method="POST" action="calendario_eventos_administrar_turnos.php?id=<?php echo $idCursoEntidad; ?>">
                        <button type="submit" name='confirma' id='confirma' class="btn btn-info">Volver</button>
                        <input type="hidden" name="estadoCurso" id="estadoCurso" value="<?php echo $estadoCurso; ?>">
                        <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>">
                    </form>
                </div>
            </div>
            <?php
            if ($resCursoAulaTurnos['estado']){
            ?>
                <br>
                <form id="formVerTurnos" name="formVerTurnos" method="POST" onSubmit="" action="datosCalendarioEventos/borrar_seleccionados.php?id=<?php echo $idCursoAula; ?>">
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Aula</th>
                                <th>Día</th>
                                <th>Hora Desde / Hasta</th>
                                <?php 
                                if ($estado == 'A') {
                                ?>
                                    <th style="width: 100px; text-align: center;">Seleccionar a borrar
                                        <button type="submit" name='confirmaBorrar' id='confirmaBorrar' class="btn btn-info" onclick="return confirmaAnular('Borrar')">Borrar seleccionados</button>
                                        <input type="hidden" name="estadoCurso" id="estadoCurso" value="<?php echo $estadoCurso; ?>">
                                        <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo $periodoSeleccionado; ?>">
                                    </th>
                                <?php 
                                } 
                                ?>
                                <th style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                    <tbody>
                      <?php
                      foreach ($resCursoAulaTurnos['datos'] as $dato) {
                          $idCursoAulaTurno = $dato['idCursoAulaTurno'];
                          $fecha = $dato['fecha'];
                          $horaDesde = number_format($dato['horaDesde'], 2);
                          $horaHasta = number_format($dato['horaHasta'], 2);
                        ?>
                        <tr>
                    	   <td><?php echo $idCursoAulaTurno;?></td>
                           <td><?php echo $nombreAula;?></td>
                           <td><?php echo $nombreDia.' '.cambiarFechaFormatoParaMostrar($fecha);?></td>
                           <td><?php echo 'De '.$horaDesde.' a '.$horaHasta;?></td>
                            <?php 
                            if ($estado == 'A') {
                            ?>
                                <td style="width: 100px; text-align: center;">
                                    <input type="checkbox" name="borrar[]" id="borrar[]" value="<?php echo $idCursoAulaTurno ?>">
                                </td>
                            <?php 
                            } 
                            ?>
                           <td style="width: 400px;">
                            <?php 
                            if ($estado == 'A') {
                            ?>
                                <a href="calendario_eventos_curso_aula_turno_form.php?id=<?php echo $idCursoAulaTurno; ?>&idCursoAula=<?php echo $idCursoAula; ?>&editar" class="btn btn-info">Editar </a>
                                <a href="datosCalendarioEventos/abm_curso_aula_turno.php?id=<?php echo $idCursoAulaTurno; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular('Borrar')">Borrar </a>
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
                <div class="<?php echo $resCursoAulaTurnos['clase']; ?>" role="alert">
                    <span class="<?php echo $resCursoAulaTurnos['icono']; ?>" ></span>
                    <span><strong><?php echo $resCursoAulaTurnos['mensaje']; ?></strong></span>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';