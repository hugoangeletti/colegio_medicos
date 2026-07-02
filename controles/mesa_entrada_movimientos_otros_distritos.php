<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
require_once ('../dataAccess/funcionesPhp.php');
?>
<script>
    $(document).ready(function () {
        $('#tablaOrdenada').DataTable({
            "iDisplayLength":25,
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "../public/lang/esp.lang"
            },
            "bPaginate": true,
            "bLengthChange": true,
            "bFilter": true,
            dom: 'T<"clear">lfrtip'
        });
    });
</script>

<?php
$accedePor = "FECHA";
if (isset($_POST['mensaje']))
{
    $conPermiso = TRUE;
 ?>
   <div class="ocultarMensaje"> 
   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
   </div>
 <?php    
}

?> 
<div class="panel panel-default">
    <div class="panel-heading"><h4><b>Mesa de Entrada por fecha</b></h4></div>
    <div class="panel-body">
        <?php
        $mesaEntradaLogic = new mesaEntradaLogic();
        if (isset($_POST['idTipoMesaEntradaSeleccionada']) && $_POST['idTipoMesaEntradaSeleccionada'] != "") {
            $idTipoMesaEntradaSeleccionada = $_POST['idTipoMesaEntradaSeleccionada'];            
            $accedePor = "FECHA_TIPO";
        } else {
            $idTipoMesaEntradaSeleccionada = NULL;
        }
        if (isset($_POST['fechaIngreso']) && $_POST['fechaIngreso'] != ""){
            $fechaIngreso = $_POST['fechaIngreso'];
        } else {
            $fechaIngreso = date('Y-m-d');
        }
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
            $accedePor = "COLEGIADO";
        } else {
            $idColegiado = NULL;
        }
        if (isset($_POST['idRemitente']) && $_POST['idRemitente'] <> "") {
            $idRemitente = $_POST['idRemitente'];
            $accedePor = "OTRO";
        } else {
            $idRemitente = NULL;
        }
        ?>
        <div class="row">
            <form method="POST" action="mesa_entrada_listado.php">
                <div class="col-md-2">
                    <label for="fechaIngreso">Fecha de Ingreso</label>
                    <input class="form-control" type="date" name="fechaIngreso" value="<?php echo $fechaIngreso; ?>"/>
                </div>
                <div class="col-md-2">
                    <label for="idTipoMesaEntradaSeleccionada">Tipo Ingreso</label>
                    <select class="form-control" id="idTipoMesaEntradaSeleccionada" name="idTipoMesaEntradaSeleccionada">
                        <option value="">Todos</option>
                        <?php
                        $resTipo = $mesaEntradaLogic->obtenerTiposMesaEntrada();
                        if ($resTipo['estado']) {
                            foreach ($resTipo['datos'] as $dato) {
                                $idTipoMesaEntrada = $dato['id'];
                                if ($idTipoMesaEntrada == 8) { continue; }
                                $nombreTipoMesaEntrada = $dato['nombre'];
                                ?>
                                <option value="<?php echo $idTipoMesaEntrada; ?>" <?php if($idTipoMesaEntrada == $idTipoMesaEntradaSeleccionada) { echo 'selected'; } ?>><?php echo$nombreTipoMesaEntrada; ?></option>
                            <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4" id="esUnColegiado">
                    <label for="colegiado_buscar">Buscar colegiado</label>
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" />
                    <input type="hidden" name="idColegiado" id="idColegiado" />
                </div>
                <div class="col-md-3" id="noEsUnColegiado">
                    <label for="remitente_buscar">Buscar remitente</label>
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="remitente_buscar" id="remitente_buscar" placeholder="Buscar Remitente"/>
                    <input type="hidden" name="idRemitente" id="idRemitente" />
                </div>
                <div class="col-md-1">
                    <br>
                    <button type="submit"  class="btn btn-info " >Buscar</button>
                </div>                
            </form>
            <div class="col-md-6 text-right">
                <!--<a href="remitente_form.php?agregar" class="btn btn-info">Agregar </a> -->
            </div>
        </div>
        <div class="row">&nbsp;</div>
            <div class="row">
                <?php
                $resTipoMesaEntrada = $mesaEntradaLogic->obtenerTiposMesaEntrada();
                if ($resTipoMesaEntrada['estado']) {
                    foreach ($resTipoMesaEntrada['datos'] as $dato) {
                        $idTipoMesaEntrada = $dato['id'];
                        if ($idTipoMesaEntrada == 8) { continue; }
                        $nombreTipoMesaEntrada = $dato['nombre'];
                        switch ($idTipoMesaEntrada) {
                            case '2':
                                ?>
                                <a href="especialidades_expedientes_form.php?accion=1" class="btn btn-primary btn-sm">Expediente Especialistas</a>
                                <?php
                                break;
                            
                            case '3':
                                ?>
                                <a href="mesa_entrada_notas_oficios.php" class="btn btn-primary btn-sm">Notas / Oficios</a>
                                <?php
                                break;
                            
                            default:
                                ?>
                                <a href="mesa_entrada_form.php?tipo=<?php echo $idTipoMesaEntrada; ?>&agregar" class="btn btn-primary btn-sm"><?php echo $nombreTipoMesaEntrada; ?></a>
                                <?php
                                break;
                        }
                    }
                }
                /*
                 
                <a href="mesa_entrada_habilitacion_consultorio.php" class="btn btn-primary">Habilitación Consultorio</a>
                */ 
                ?>
            </div>
            <div class="row">&nbsp;</div>            
        <?php
        $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaPorFechaTipo($fechaIngreso, $idTipoMesaEntradaSeleccionada, $idColegiado, $idRemitente);
        if ($resMesaEntrada['estado']) {
        ?>    
            <table id="tablaOrdenada" class="display">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Fecha Ingreso</th>
                        <th style="text-align: right;">Matrícula</th>
                        <th>Apellido y Nombre / Remitente</th>
                        <th>Tipo</th>
                        <th style="width: 200px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($resMesaEntrada['datos'] as $dato) {
                        $idMesaEntrada = $dato['idMesaEntrada'];
                        $fechaIngreso = $dato['fechaIngreso'];
                        $matricula = $dato['matricula'];
                        $idRemitente = $dato['idRemitente'];
                        $nombreRemitente = $dato['nombreRemitente'];
                        $nombreTipoMesaEntrada = $dato['nombreTipoMesaEntrada'];
                        if ($dato['detalleTipoMesaEntrada'] <> "") {
                            $nombreTipoMesaEntrada = $nombreTipoMesaEntrada.' ('.$dato['detalleTipoMesaEntrada'].')';
                        }
                        $idTipoMesaEntrada = $dato['idTipoMesaEntrada'];
                        ?>
                        <tr>
                            <td><?php echo $idMesaEntrada;?></td>
                            <td><?php echo cambiarFechaFormatoParaMostrar($fechaIngreso);?></td>
                            <td style="text-align: right;"><?php echo $matricula;?></td>
                            <td><?php echo $nombreRemitente;?></td>
                            <td><?php echo $nombreTipoMesaEntrada;?></td>
                            <td>
                                <?php 
                                //ver
                                switch ($idTipoMesaEntrada) {
                                    case '4':
                                        // Habilitacion de consultorios
                                        $idMesaEntradaConsultorio = $dato['idMesaEntradaConsultorio'];
                                        ?>
                                        <a href="mesa_entrada_habilitacion_consultorio.php?id=<?php echo $idMesaEntradaConsultorio; ?>&ingreso=<?php echo $accedePor; ?>&ver" class="btn btn-info">Ver</a>
                                        <?php
                                        break;
                                    
                                    default:
                                        // otros
                                        ?>
                                        <a href="mesa_entrada_ver.php?id=<?php echo $idMesaEntrada; ?>&ingreso=<?php echo $accedePor; ?>" class="btn btn-info">Ver</a>
                                        <?php                                        
                                        break;
                                }
                                //fin ver

                                //imprimir
                                if ($idTipoMesaEntrada == 2) {
                                    //si es para especialista
                                    $resMesaEntradaEspecialista = $mesaEntradaEspecialistaLogic->obtenerEspecialidadPorIdMesaEntrada($idMesaEntrada);
                                    if ($resMesaEntradaEspecialista['estado']) {
                                        $mesaEntradaEspecialista = $resMesaEntradaEspecialista['datos'];
                                        $numeroExpediente = $mesaEntradaEspecialista['numeroExpediente'];
                                        $anioExpediente = $mesaEntradaEspecialista['anioExpediente'];
                                        ?>
                                        <a href="datosMesaEntrada/especialidades_expedientes_imprimir.php?n_exp=<?php echo $numeroExpediente; ?>&a_exp=<?php echo $anioExpediente; ?>" target="_BLANK" class="btn btn-info">Imprimir</a>
                                <?php
                                    }
                                } else {
                                    //if ($idTipoMesaEntrada <> 10) {
                                        //si no es entrega se muestra el boton imprimir
                                        ?>
                                        <a href="mesa_entrada_imprimir.php?id=<?php echo $idMesaEntrada; ?>&ingreso=<?php echo $accedePor; ?>" class="btn btn-info">Imprimir</a>
                                    <?php
                                    //}
                                }
                                //fin imprimir

                                //borrar o anular
                                //si no es ni movimientos ni anulacion de movimiento, lo dejo borrar
                                if ($idTipoMesaEntrada != 1 && $idTipoMesaEntrada != 8) {
                                    if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 104)) {
                                    ?>
                                        <a href="datosMesaEntrada\mesa_entrada.php?borrar&id=<?php echo $idMesaEntrada; ?>&ingreso=<?php echo $accedePor; ?>" class="btn btn-info" onclick="return confirmaAnular()">Borrar</a>
                                    <?php
                                    }

                                    //si es habilitacion de consultorio, puede editar medicos autorizados
                                    if ($fechaIngreso == date('Y-m-d') && $idTipoMesaEntrada == 4 && isset($dato['idMesaEntradaConsultorio'])) {
                                        //obtenemos el idmesaentradaconsultorio para hacer la edicion
                                        $idMesaEntradaConsultorio = $dato['idMesaEntradaConsultorio'];
                                        ?>
                                        <a href="mesa_entrada_habilitacion_consultorio.php?id=<?php echo $idMesaEntradaConsultorio; ?>&editar" class="btn btn-info">Autorizados</a>
                                    <?php 
                                    }
                                } else {
                                    if ($fechaIngreso == date('Y-m-d')) {
                                        if ($idTipoMesaEntrada == 1) {
                                            if ($mesaEntradaLogic->elMovimientoSePuedeAnular($idMesaEntrada)) {
                                            ?>
                                                <a href="datosMesaEntrada\mesa_entrada.php?borrar&id=<?php echo $idMesaEntrada; ?>&ingreso=<?php echo $accedePor; ?>" class="btn btn-info" onclick="return confirmaAnular()">Borrar</a>
                                            <?php
                                            }
                                        }
                                    } else {
                                        if ($idTipoMesaEntrada == 1 && $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 104)) {
                                            if ($mesaEntradaLogic->elMovimientoSePuedeAnular($idMesaEntrada)) {
                                            ?>
                                                <a href="mesa_entrada_movimientos_matriculares.php?anular&id=<?php echo $idMesaEntrada; ?>&ingreso=<?php echo $accedePor; ?>" class="btn btn-info">Anular</a>
                                            <?php                                    
                                            }
                                        }
                                    }
                                    //fin borrar o anular
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
            <div class="<?php echo $resMesaEntrada['clase']; ?>" role="alert">
                <span class="<?php echo $resMesaEntrada['icono']; ?>" ></span>
                <span><strong><?php echo $resMesaEntrada['mensaje']; ?></strong></span>
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

    $(function(){
        var nameIdMap = {};
        $('#remitente_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'remitente.php',
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
                $('#idRemitente').val(nameIdMap[item]);
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

    
    var lastSelected;
    $(function () {
        //if you have any radio selected by default
        lastSelected = $('[name="esColegiado"]:checked').val();
    });
    $(document).on('click', '[name="esColegiado"]', function () {
        if (lastSelected != $(this).val() && typeof lastSelected != "undefined") {
            //if (x.style.display === "none") {
            var x = document.getElementById("esUnColegiado");
            var y = document.getElementById("noEsUnColegiado");
            var z = document.getElementById("incluyeMovimientos");
            if (lastSelected != 'S') {
                x.style.display = "block";
                y.style.display = "none";
                z.style.display = "none";
            } else {
                x.style.display = "none";
                y.style.display = "block";
                z.style.display = "block";
            }
        }
        lastSelected = $(this).val();
    });

    function confirmaAnular()
    {
        if(confirm('¿Estas seguro de ANULAR este registro?'))
            return true;
        else
            return false;
    }
    
</script>
