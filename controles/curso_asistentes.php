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

    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de Borrar registro?'))
            return true;
        else
            return false;
    }

    function confirmaAsistencia()
    {
        if(confirm('¿Estas seguro de Modificar asistencia?'))
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
$continua = TRUE;
$mensaje = "";
$cursos_pdo = new cursos_pdo();
$estadoCurso = 'A';

if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCurso = $_GET['id'];
    $ingreso_por = "CURSOS";
    $action = "curso_asistentes.php?id=".$idCurso;
    $readOnly = "readonly";
} else {
    $ingreso_por = "ASISTENTES";
    $action = "curso_asistentes.php";
    $readOnly = "";
    if (isset($_POST['idCurso']) && $_POST['idCurso'] <> "") {
        $idCurso = $_POST['idCurso'];
    } else {
        $idCurso = NULL;
    }
}

if (isset($_POST['asiste'])) {
    $asiste = $_POST['asiste'];
} else {
    $asiste = "";
}

if (isset($idCurso)) {
    $resCurso = $cursos_pdo->obtenerCursoPorId($idCurso);
    if ($resCurso['estado']) {
        $curso = $resCurso['datos'];
        $titulo = $curso['titulo'];
        $director = $curso['director'];
        $fechaInicio = $curso['fechaInicio'];
        $estadoCurso = $curso['estado'];
        $tema = $curso['tema'];
        $dias = $curso['dias'];
        $fechas = $curso['fechas'];
        $salon = $curso['salon'];
        $lugar = $curso['lugar'];
        $coordinador = $curso['coordinador'];
        $vigenciaHasta = $curso['vigenciaHasta'];
    } else {
        $continua = FALSE;
        $mensaje .= "ERROR->".$resCurso['mensaje'];
    }
}
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
        <div class="col-xs-9 text-center">
            <h5><b>
            <?php 
            if ($ingreso_por == "CURSOS") {
                echo "Asistentes del Curso (#".$idCurso.") - ".$titulo;
            } else {
                $resCursos = $cursos_pdo->obtenerCursos($estadoCurso);
                if ($resCursos['estado']) {
                ?>
                    <form method="POST" action="<?php echo $action; ?>">
                        <select class="form-control" id="idCurso" name="idCurso" required <?php echo $readOnly; ?> onChange="this.form.submit()">
                            <option value="">Seleccione un curso</option>                            
                            <?php
                            foreach ($resCursos['datos'] as $dato) {
                                $idCursoSelect = $dato['idCurso'];
                                $titulo = $dato['titulo'];
                                ?>
                                <option value="<?php echo $idCursoSelect; ?>" <?php if ($idCurso == $idCursoSelect) { echo "selected"; } ?>><?php echo $titulo; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </form>
                <?php
                }
            }
            ?>
            </b></h5>
        </div>
        <div class="col-xs-3 text-right">
            <a href="curso_listado.php" class="btn btn-primary" >Volver</a>
        </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="row">
                <form method="POST" action="<?php echo $action; ?>">
                    <div class="col-xs-1 text-right">
                        <label for="asiste">Estado: </label>
                    </div>
                    <div class="col-xs-3">
                        <select class="form-control" id="asiste" name="asiste" onChange="this.form.submit()">
                            <option value="" <?php if($asiste == "") { echo 'selected'; } ?>>Todos</option>
                            <option value="S" <?php if($asiste == "S") { echo 'selected'; } ?>>Asistentes</option>
                            <option value="N" <?php if($asiste == "N") { echo 'selected'; } ?>>No asistentes</option>
                        </select>
                    </div>
                    <input type="hidden" name="idCurso" id="idCurso" value="<?php echo $idCurso; ?>">
                </form>
                    <div class="col-xs-2">
                    </div>
                <div class="col-xs-2 text-center">
                    <a href="curso_asistentes_form.php?idCurso=<?php echo $idCurso; ?>&agregar" class="btn btn-info">Agregar asistente</a>
                </div>
                <div class="col-xs-2 text-center">
                    <a href="curso_asistentes_listado.php?idCurso=<?php echo $idCurso; ?>" class="btn btn-info" target="_BLANK" >Listado asistentes</a>
                </div>
                <div class="col-xs-2 text-center">
                    <a href="curso_asistentes_listado.php?idCurso=<?php echo $idCurso; ?>&deuda" class="btn btn-info" target="_BLANK" >Listado asistentes con deuda</a>
                </div>
            </div>
            <?php
            $resAsistentes = $cursos_pdo->obtenerAsistentesPorIdCurso($idCurso, $asiste);
            if ($resAsistentes['estado']){
            ?>
                <div class="row">
                    <div class="col-md-12">
                    <br>
                    <table id="tablaOrdenada" class="display">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Apellido y Nombre</th>
                                <th>Matrícula</th>
                                <th>Correr electrónico</th>
                                <th>Usuario</th>
                                <th>Asiste</th>
                                <th>Fecha carga</th>
                                <th>Acciones</th>
                                <th>Cuotas</th>
                                <th>Chequera</th>
                                <th>Planilla</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach ($resAsistentes['datos'] as $dato) {
                              $idCursosAsistente = $dato['idCursosAsistente'];
                              $apellidoNombre = $dato['apellidoNombre'];
                              $estado = $dato['estado'];
                              if ($estado == "S") {
                                $asiste = "SI";
                                $fechaCarga = cambiarFechaFormatoParaMostrar($dato['fechaCarga']);
                              } else {
                                $asiste = "NO";
                                $fechaCarga = cambiarFechaFormatoParaMostrar($dato['fechaBaja']).' Usuario: '.$dato['usuarioBaja'];
                                /*
                                $noAsiste = $cursos_pdo->obtenerFechaCargaNoAsiste($idCursosAsistente);
                                if (sizeof($noAsiste) > 0) {
                                    $fechaCarga = cambiarFechaFormatoParaMostrar(substr($noAsiste['fechaCarga'], 0, 10)).' Usuario: '.$noAsiste['usuarioNombre'];
                                } else {
                                    $fechaCarga = NULL;
                                }
                                */
                              }
                              $matricula = $dato['matricula'];
                              $idUsuario = $dato['idUsuario'];
                              if (isset($idUsuario) && $idUsuario > 0) {
                                $nombreUsuario = $dato['nombreUsuario'];
                              } else {
                                $nombreUsuario = 'tramites-web';
                              }
                              $cuotasPagas = $dato['cuotasPagas'];
                              $correoElectronico = $dato['correoElectronico'];
                              $observaciones = $dato['observaciones'];
                            ?>
                            <tr>
                        	   <td><?php echo $idCursosAsistente;?></td>
                               <td><?php echo $apellidoNombre;?></td>
                               <td><?php echo $matricula;?></td>
                               <td><?php echo $correoElectronico;?></td>
                               <td><?php echo $nombreUsuario;?></td>
                               <td>
                                    <?php 
                                    if ($asiste == 'SI') {
                                        //se pasa a No asiste entonces pedimos la fecha y un detalle del porque no asiste
                                        ?>
                                        <!--<a href="datosCurso/abm_curso_asistente.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCursosAsistente; ?>&asiste" class="btn btn-info" onclick="return confirmaAsistencia()"><?php echo $asiste; ?></a>-->
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#no_asisteModal_<?php echo $idCursosAsistente; ?>">SI</button>
                                        <div id="no_asisteModal_<?php echo $idCursosAsistente; ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header alert alert-success">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                        <h4 class="modal-title">No asiste al curso</h4>
                                                    </div>              
                                                    <div class="modal-body">
                                                        <div class="row">
                                                        <form id="no_asiste" autocomplete="off" name="no_asiste" method="POST" action="datosCurso/abm_curso_asistente.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCursosAsistente; ?>&no_asiste">
                                                            <div class="col-md-6">
                                                                <label>Fecha baja: *</label>
                                                                <input type="date" class="form-control" name="fecha_baja" id="fecha_baja" value="<?php echo date('Y-m-d'); ?>" required=""/>
                                                            </div>
                                                            <div class="col-md-12">&nbsp;</div>
                                                            <div class="col-md-12">
                                                                <label>Motivo: *</label>
                                                                <input type="text" class="form-control" name="motivo_baja" id="motivo_baja" required=""/>
                                                            </div>
                                                            <div class="col-md-12">&nbsp;</div>
                                                            <div class="col-md-12 text-center">
                                                                <button type="submit"  class="btn btn-success btn-lg" >Confirma</button>
                                                            </div>
                                                        </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                    <?php
                                    } else {
                                    ?>
                                        <a href="datosCurso/abm_curso_asistente.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCursosAsistente; ?>&asiste" class="btn btn-info" onclick="return confirmaAsistencia()"><?php echo $asiste; ?></a> 
                                    <?php
                                    }
                                    ?>
                               </td>
                               <td><?php echo $fechaCarga; ?></td>
                               <td>
                                    <?php 
                                    if ($cuotasPagas == 0) { 
                                    ?>
                                        <a href="datosCurso/abm_curso_asistente.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCursosAsistente; ?>&borrar" class="btn btn-info" onclick="return confirmaAnular()">Borrar </a>
                                    <?php 
                                    } 
                                    ?>
                               </td>
                               <td>
                                    <?php
                                    $resCuotas = $cursos_pdo->obtenerCuotasPorAsistente($idCursosAsistente);
                                    if ($resCuotas['estado']) {
                                        $cantidad = $resCuotas['cantidad'];
                                        $cuotasAdeudadas = $resCuotas['cuotasAdeudadas'];
                                        if ($cantidad > 0) {
                                        ?>
                                            <a href="curso_asistentes_cuotas.php?id=<?php echo $idCursosAsistente; ?>" class="btn btn-info">Ver cuotas </a>
                                        <?php
                                        } else {
                                        ?>
                                            <a href="datosCurso/abm_curso_asistente_cuota.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCursosAsistente; ?>&generar" class="btn btn-info">Generar cuotas </a>
                                        <?php
                                        }
                                    }
                                    ?>
                               </td>
                               <td>
                                    <a href="curso_asistentes_chequeras.php?&id=<?php echo $idCursosAsistente; ?>" class="btn btn-info" >Chequera </a>
                               </td>
                               <td>
                                    <a href="curso_asistentes_planilla.php?&id=<?php echo $idCursosAsistente; ?>" class="btn btn-info" >Planilla </a>
                               </td>
                               <td>
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#observaciones_<?php echo $idCursosAsistente ?>_Modal">Observaciones</button>
                                    <div id="observaciones_<?php echo $idCursosAsistente ?>_Modal" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header alert alert-success">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                    <h4 class="modal-title">Observaciones</h4>
                                                </div>              
                                                <div class="modal-body">
                                                    <div class="row">
                                                    <form id="no_asiste" autocomplete="off" name="no_asiste" method="POST" action="datosCurso/abm_curso_asistente.php?idCurso=<?php echo $idCurso; ?>&id=<?php echo $idCursosAsistente; ?>&observacion">
                                                        <div class="col-md-12">
                                                            <label>Observaciones: *</label>
                                                            <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="5" required><?php echo $observaciones; ?></textarea>
                                                        </div>
                                                        <?php 
                                                        if ($asiste == 'SI') {                   
                                                        ?>
                                                            <div class="col-md-12">&nbsp;</div>
                                                            <div class="col-md-12 text-center">
                                                                <button type="submit"  class="btn btn-success btn-lg" >Confirma</button>
                                                            </div>
                                                        <?php 
                                                        }
                                                        ?>
                                                    </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                               </td>
                            </tr>
                          <?php
                          }
                          ?>              
            	       </tbody>
        	        </table>
                    </div>
                </div>
            <?php
            } else {
            ?>  
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="<?php echo $resAsistentes['clase']; ?>" role="alert">
                            <span class="<?php echo $resAsistentes['icono']; ?>" ></span>
                            <span><strong><?php echo $resAsistentes['mensaje']; ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php
            }    
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';