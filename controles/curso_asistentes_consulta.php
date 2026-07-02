<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/colegiadoLogic.php');
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

$esColegiado = FALSE;
$esNoColegiado = FALSE;
$colegiado_buscar = NULL;
$no_colegiado_buscar = NULL;
if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
    $idColegiado = $_POST['idColegiado'];
    $esColegiado = TRUE;
    $colegiado_buscar = $_POST['colegiado_buscar'];
} else {
    if (isset($_POST['idAsistente']) && $_POST['idAsistente'] <> "") {
        //es un asistente NO colegiado
        $idAsistente = $_POST['idAsistente'];
        $esNoColegiado = TRUE;
        $no_colegiado_buscar = $_POST['no_colegiado_buscar'];
    }
}
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <?php
        if (!$esColegiado && !$esNoColegiado) {
        ?>
            <h3>Buscar asistente de cursos</h3>
            <div class="row">
                <form method="POST" action="curso_asistentes_consulta.php">
                    <div class="row">
                        <div class="col-md-3" style="text-align: right;">
                            <h4><b>Buscar colegiado del distrito</b></h4>
                        </div>
                        <div class="col-md-7">
                            <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" required="" />
                            <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                        </div>
                        <div class="col-md-2">
                            <button type="submit"  class="btn btn-success">Confirmar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">&nbsp;</div>
            <div class="row">
                <form method="POST" action="curso_asistentes_consulta.php">
                    <div class="row">
                        <div class="col-md-3" style="text-align: right;">
                            <h4><b>No colegiado del distrito</b></h4>
                        </div>
                        <div class="col-md-7">
                            <input class="form-control" autocomplete="OFF" type="text" name="no_colegiado_buscar" id="no_colegiado_buscar" placeholder="Ingrese Apellido y Nombre del asistente" value="<?php echo $no_colegiado_buscar; ?>" required=""/>
                            <input type="hidden" name="idAsistente" id="idAsistente" required="" />
                        </div>
                        <div class="col-md-2">
                            <button type="submit"  class="btn btn-success">Confirmar</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php 
        } else {
        ?>
            <div class="row">
                <?php
                if ($esColegiado) {
                ?>
                    <div class="col-md-10">
                        Colegiado: <b><?php echo $colegiado_buscar; ?></b>
                    </div>
                <?php
                } else {
                ?>
                    <div class="col-md-10">
                        <h4><b>Datos del asistente seleccionado (NO es colegiado)</b></h4>
                    </div>
                <?php
                }
                ?>
                <div class="col-md-2 text-right">
                    <a href="curso_asistentes_consulta.php" class="btn btn-primary" >Otra consulta</a>
                </div>
            </div>
        <?php
        }
        ?>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php
            if ($esColegiado) {
                $resAsistentes = $cursos_pdo->obtenerCursosPorIdColegiado($idColegiado);
                if ($resAsistentes['estado']){
                ?>
                    <div class="row">
                        <div class="col-md-12">
                        <br>
                        <table id="tablaOrdenada" class="display">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Curso</th>
                                    <th>Asiste</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resAsistentes['datos'] as $dato) {
                                    $idCurso = $dato['idCurso'];
                                    $idCursosAsistente = $dato['idCursosAsistente'];
                                    $tituloCurso = $dato['titulo'];
                                    $estado = $dato['estado'];
                                    if ($estado == "S") {
                                        $asiste = "SI";
                                    } else {
                                        $asiste = "NO";
                                    }
                                ?>
                                <tr>
                            	   <td><?php echo $idCurso;?></td>
                                   <td><?php echo $tituloCurso;?></td>
                                   <td><?php echo $asiste;?></td>
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
            } else {
                if ($esNoColegiado) {
                    $resAsistente = $cursos_pdo->obtenerAsistentePorId($idAsistente);
                    if ($resAsistente['estado']) {
                        $asistente = $resAsistente['datos'];
                        $apellidoNombre = $asistente['apellidoNombre'];
                        $asiste = $asistente['asiste'];
                        if ($asiste == "S") {
                            $asiste = "SI";
                        } else {
                            $asiste = "NO";
                        }
                        $tituloCurso = $asistente['tituloCurso'];
                        ?>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="apellidoNombre">Apellido y Nombre</label>
                                <input type="text" class="form-control" name="apellidoNombre" value="<?php echo $apellidoNombre; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="tituloCurso">Curso</label>
                                <input type="text" class="form-control" name="tituloCurso" value="<?php echo $tituloCurso; ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="asiste">Asiste</label>
                                <input type="text" class="form-control" name="asiste" value="<?php echo $asiste; ?>" readonly>
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
                }
            }
            ?>
        </div>
    </div>
</div>
<?php
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado_asistente.php?colegiado',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idColegiado').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
    
    $(function(){
        var nameIdMap = {};
        $('#no_colegiado_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado_asistente.php?no_colegiado',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            
            updater: function (item) {
                $('#idAsistente').val(nameIdMap[item]);
                return item;
            }
        });
        function getOptionsFromJson(json) {
             
            $.each(json, function (i, v) {
                //console.log(v);
                nameIdMap[v.nombre] = v.id;
            });
            return $.map(json, function (n, i) {
                return n.nombre;
            });
        }
    });  
</script>