<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":10,
            "order": [[ 1, "desc"]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });
</script>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-10">
                <h4><b>Resoluciones de Especialistas por Colegiado</b></h4>
                </div>
            <div class="col-md-2 text-right">
                <a href="resoluciones_por_colegiado.php" class="btn btn-primary">Buscar otro colegiado</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $colegiado_buscar = NULL;
        if (isset($_POST['idColegiado'])) {
            $idColegiado = $_POST['idColegiado'];
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado'] && $resColegiado['datos']) {
                $colegiado = $resColegiado['datos'];
                ?>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-10">
                        <p>Apellido y Nombres:&nbsp; <b><?php echo $colegiado['apellido'].', '.$colegiado['nombre']; ?></b> - Matr&iacute;cula:&nbsp; <b><?php echo $colegiado['matricula']; ?></b></p>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <?php
                //busco las especialidades y la cantidad que tenga por nombre de especialidad
                //en caso de tener mas de una por diferente origen, debo mostrar las fechas de especialista por cada tipo
                $resResoluciones = $colegiadoEspecialistaLogic->obtenerResolucionesEspecialidadesPorIdColegiado($idColegiado);
                if ($resResoluciones['estado']) {
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <table id="tablaOrdenada" class="display">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Especialidad</th>
                                        <th>Tipo</th>
                                        <th>Distrito</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                foreach ($resResoluciones['datos'] as $dato) {
                                ?>
                                    <tr>
                                        <td><?php echo $dato['numeroResolucion'];?></td>
                                        <td><?php echo $dato['fechaResolucion'];?></td>
                                        <td><?php echo $dato['nombreEspecialidad'];?></td>
                                        <td><?php echo $dato['tipoespecialista'];?></td>
                                        <td><?php echo $dato['distritoOrigen'];?></td>
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
                    <div class="<?php echo $resResoluciones['clase']; ?>" role="alert">
                        <span class="<?php echo $resResoluciones['icono']; ?>" aria-hidden="true"></span>
                        <span><strong><?php echo $resResoluciones['mensaje']; ?></strong></span>
                    </div>     
                <?php        
                }
            } else {
            ?>
                <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                    <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
                </div>        
            <?php        
            }
        } else {
            //buscar colegiado
            ?>
            <div class="col-xs-6">
                <form method="POST" action="resoluciones_por_colegiado.php">
                    <div class="col-md-9" id="esUnColegiado">
                        <label>Buscar colegiado:</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar; ?>" placeholder="Ingrese Matrícula o Apellido del colegiado" />
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <div class="col-md-3">
                        <br>
                        <button type="submit"  class="btn btn-info " >Buscar colegiado</button>
                    </div>
                </form>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<div class="row">&nbsp;</div>
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
                    url: 'colegiado.php',
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

</script>
