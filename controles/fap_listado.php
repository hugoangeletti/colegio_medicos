<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/fap_pdo.php');
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
$fapLogic = new fap_pdo();
?> 
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-10">
                <h4><b>Listado de FAP</b></h4>
            </div>
            <div class="col-md-2 text-center">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle"
                              data-toggle="dropdown">
                        Agregar carátula <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="fap_form.php?agregar=consulta">Agregar Consulta</a></li>
                        <li><a href="fap_form.php?agregar=mediacion">Agregar Mediación</a></a></li>
                        <li><a href="fap_form.php?agregar=litigar_sin_gasto">Agregar Litigar sin gasto</a></a></li>
                        <li><a href="fap_form.php?agregar=causa">Agregar Causa</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
            <?php
//        <div class="row">
            $estadoSapCaratula = '0'; //inicializa todos;
            $periodoSeleccionado = date('Y');
            $idColegiado = NULL;
            $colegiado_buscar = NULL;
            $caratula_buscar = NULL;
            if (isset($_POST['estadoSapCaratula']) && $_POST['estadoSapCaratula'] != "") {
                $estadoSapCaratula = $_POST['estadoSapCaratula'];
            }
            if (isset($_POST['periodoSeleccionado']) && $_POST['periodoSeleccionado'] != "") {
                $periodoSeleccionado = $_POST['periodoSeleccionado'];
            }
            if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
                $idColegiado = $_POST['idColegiado'];
                $colegiado_buscar = $_POST['colegiado_buscar'];
            }
            if (isset($_POST['caratula_buscar']) && $_POST['caratula_buscar'] <> "") {
                $caratula_buscar = $_POST['caratula_buscar'];
            }
            ?>
            <div class="row">
                <div class="col-md-10">
                <form method="POST" action="fap_listado.php">
                    <div class="col-md-1">
                        <label for="periodoSeleccionado">Año: </label>
                        <select class="form-control" id="periodoSeleccionado" name="periodoSeleccionado" required >
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
                    <div class="col-md-2">
                        <label for="estadoSapCaratula">Tipo/Estado: </label>
                        <select class="form-control" id="estadoSapCaratula" name="estadoSapCaratula" required >
                            <option value="0" selected>Todos</option>
                            <option value="1" <?php if($estadoSapCaratula == 1) { echo 'selected'; } ?>>Aprobados / En Sistema</option>
                            <option value="2" <?php if($estadoSapCaratula == 2) { echo 'selected'; } ?>>Litigar Sin Gasto</option>
                            <option value="3" <?php if($estadoSapCaratula == 3) { echo 'selected'; } ?>>Mediación</option>
                            <option value="4" <?php if($estadoSapCaratula == 4) { echo 'selected'; } ?>>Consulta</option>
                            <?php
                            /*
                            $resTipos = $fapLogic->obtenerTiposTramites();
                            if ($resTipos['estado']) {
                                foreach ($resTipos['datos'] as $tipoTramite) {
                                ?>
                                    <option value="<?php echo $tipoTramite['estadoSapCaratula']; ?>" <?php if($tipoTramite['estadoSapCaratula'] == $estadoSapCaratula) { echo 'selected'; } ?>><?php echo $tipoTramite['nombre']; ?></option>
                                <?php
                                }
                            } 
                            */
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4" id="esUnColegiado">
                        <label for="colegiado_buscar">Buscar colegiado</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" value="<?php echo $colegiado_buscar; ?>" placeholder="Ingrese Matrícula o Apellido del colegiado" />
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <div class="col-md-4">
                        <label for="caratula_buscar">Buscar carátula</label>
                        <input class="form-control" autocomplete="OFF" type="text" name="caratula_buscar" id="caratula_buscar" value="<?php echo $caratula_buscar; ?>" placeholder="Ingrese alguna palabra de la carátula" />
                    </div>
                    <div class="col-md-1">
                        <br>
                        <button type="submit"  class="btn btn-info " >Buscar</button>
                    </div>
                </form>
                </div>
                <div class="col-md-1">
                <form method="POST" action="fap_listado.php">
                    <div class="col-md-1">
                        <br>
                        <button type="submit"  class="btn btn-default " >Inicializar campos</button>
                        <!--<input type="hidden" name="idColegiado" id="idColegiado" value="" />-->
                        <!--<input type="hidden" name="estadoSapCaratula" id="estadoSapCaratula" value="1" />-->
                        <input type="hidden" name="periodoSeleccionado" id="periodoSeleccionado" value="<?php echo date('Y'); ?>" />
                    </div>
                </form>    
                </div>
            </div>
            <?php
            $resCaratulas = $fapLogic->obtenerFapCaratulasPorPeriodoEstado($periodoSeleccionado, $estadoSapCaratula, $idColegiado, $caratula_buscar);

            if ($resCaratulas['estado'] && !empty($resCaratulas['datos'])) {
            ?>
                <br>
                <table id="tablaOrdenada" class="display">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>N° FAP</th>
                            <th>Matrícula</th>
                            <th>Apellido y Nombre</th>
                            <th>Nombre de la causa</th>
                            <th>Fecha de ingreso</th>
                            <th>Reunión Consejo</th>
                            <th>Tipo trámite</th>
                            <th>Estado</th>
                            <th>Condición</th>
                            <th style="text-align: center;">Acciones</th>
                            <th style="text-align: center;">Adjuntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($resCaratulas['datos'] as $dato) {
                            // Ajuste de índices según los alias de la consulta PDO
                            $idSapCaratula = $dato['IdSapCaratula'];
                            $idColegiado   = $dato['IdColegiado'];
                            $matricula     = $dato['Matricula'];
                            $idSapTipoTramite = $dato['IdSapTipoTramite'];
                            
                            $nombreSapTipoTramite = $dato['NombreTipoTramite'];
                            $nombreSapEstado      = $dato['NombreSapEstado'];
                            $nombreSapCondicion   = $dato['NombreCondicion'];
                            
                            $apellidoNombre = trim($dato['Apellido']) . ' ' . trim($dato['Nombres']);
                            $fechaIngreso   = $dato['FechaIngreso'];
                            $nombreCausa    = $dato['NombreCausa'];
                            
                            // Lógica para Número SAP (solo si no es consulta)
                            $esConsulta = ($idSapTipoTramite == fap_pdo::TIPO_TRAMITE_CONSULTA);
                            $numeroSAP  = $esConsulta ? '' : ($dato['NumeroSAP']);
                            
                            $fechaReunionConsejo = isset($dato['FechaReunion']) ? $dato['FechaReunion'] : null;
                        ?>
                            <tr>
                                <td><?php echo $idSapCaratula; ?></td>
                                <td><?php echo $numeroSAP; ?></td>
                                <td><?php echo $matricula; ?></td>
                                <td><?php echo $apellidoNombre; ?></td>
                                <td><?php echo $nombreCausa; ?></td>
                                <td><?php echo cambiarFechaFormatoParaMostrar($fechaIngreso); ?></td>
                                <td><?php echo $fechaReunionConsejo ? cambiarFechaFormatoParaMostrar($fechaReunionConsejo) : 'Pendiente'; ?></td>
                                <td><?php echo $nombreSapTipoTramite; ?></td>
                                <td><?php echo $nombreSapEstado; ?></td>
                                <td><?php echo $nombreSapCondicion; ?></td>
                                <td style="text-align: center;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                            Acciones <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <?php if (!$esConsulta): ?>
                                                <li><a href="fap_imprimir_caratula.php?id=<?php echo $idSapCaratula; ?>">Imprimir carátula</a></li>
                                            <?php endif; ?>

                                            <li><a href="fap_form.php?id=<?php echo $idSapCaratula; ?>&editar">Editar</a></li>

                                            <?php if (!$esConsulta && $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 128)): ?>
                                                <li><a href="datosFap/abm_fap.php?id=<?php echo $idSapCaratula; ?>&cambiar_causa" onclick="return confirmaCambio()">Pasar a consulta</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <a href="fap_adjunto_form.php?id=<?php echo $idSapCaratula; ?>" class="btn btn-primary">Ver Adjuntos</a>
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
                <div class="<?php echo $resCaratulas['clase']; ?>" role="alert">
                    <span class="<?php echo $resCaratulas['icono']; ?>"></span>
                    <span><strong><?php echo $resCaratulas['mensaje']; ?></strong></span>
                </div>
            <?php
            }
            ?>
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

    function confirmaCambio()
    {
        if(confirm('¿Estas seguro de pasar a CONSULTA este trámite?'))
            return true;
        else
            return false;
    }
    
</script>
