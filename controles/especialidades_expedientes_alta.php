<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
$resolucionesLogic = new resolucionesLogic();
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();
require_once ('../dataAccess/tipoPagoLogic.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/especialidades_pdo.php');

$objEspecialidades = new especialidades_pdo();

$continua = TRUE;
$mensaje = 'OK';

$accion = $_GET['accion'];

//si es una nueva especialidad, armo el form completo
if (isset($_POST['tipo']) && $_POST['tipo'] && isset($_POST['idColegiado'])) {
    $tipo = $_POST['tipo'];

    $idColegiado = $_POST['idColegiado'];
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado'] && $resColegiado['datos']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        $idEstadoMatricular = $colegiado['idEstadoMatricular'];
        if (isset($_POST['estadoTesoreria'])) {
            $estadoTesoreria = $_POST['estadoTesoreria'];
        } else {
            $estadoTesoreria = NULL;
        }
        
        if (isset($_POST['id']) && $_POST['id'] <> "") {
            $idMesaEntradaEspecialidad = $_POST['id'];
            
            $mostrarIncisios = "display: none;";
            $mostrarDistritos = "display: none;";
            $resMesa = $mesaEntradaEspecialistaLogic->obtenerMesaEntradaEspecialistaPorId($idMesaEntradaEspecialidad);
            if ($resMesa['estado']) {
                $mesaEntrada = $resMesa['datos'];
                $tipoEspecialista = $mesaEntrada['tipoTramiteEspecialista'];
                $idTipoEspecialista = $mesaEntrada['idTipoEspecialista'];
                if ($tipoEspecialista == "A") {
                    $requeridoAgregada = "display: block;";
                    $requeridoEspecialidad = "display: none;";
                    $especialidad = $mesaEntrada['idEspecialidad'];
                    //$especialidadDetalle = "";
                } else {
                    $requeridoAgregada = "display: none;";
                    $requeridoEspecialidad = "display: block;";
                    $especialidad = $mesaEntrada['idEspecialidad'];
                    //$especialidadDetalle = $mesaEntrada['nombreEspecialidad'];
                    $inciso = "";
                    $mostrarIncisios = "display: none;";
                    if ($tipoEspecialista == "X") {
                        $inciso = $mesaEntrada['inciso'];
                        $mostrarIncisios = "display: block;";
                    }
                    $distrito = "";
                    $mostrarDistritos = "display: none;";
                    if ($tipoEspecialista == "O") {
                        $distrito = $mesaEntrada['distrito'];
                        $mostrarDistritos = "display: block;";
                    }
                }
                $numeroExpediente = $mesaEntrada['numeroExpediente'];
                $anioExpediente = $mesaEntrada['anioExpediente'];
                $titulo = "Modificación del Expediente de Especialidades Nº ".$numeroExpediente.'/'.$anioExpediente;
            } else {
                $continua = FALSE;
                ?>
                <div class="<?php echo $resMesa['clase']; ?>" role="alert">
                    <span class="<?php echo $resMesa['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resMesa['mensaje']; ?></strong></span>
                </div>        
            <?php
            }
        } else {
            $idMesaEntradaEspecialidad = NULL;
            $titulo = "Alta Expediente para Especialidades";
            $especialidad = NULL;
            $especialidadDetalle = NULL;
            $tipoEspecialista = "";
            $idEspecialidad = NULL;
            $inciso = NULL;
            $distrito = NULL;
            $requeridoAgregada = "display: none;";
            $requeridoEspecialidad = "display: block;";
            $mostrarIncisios = "display: none;";
            $mostrarDistritos = "display: none;";
        }
    } else {
        $continua = FALSE;
    ?>
        <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
            <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
            <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
        </div>        
    <?php
    }
    
    if ($continua) {
    ?>
        <div class="panel panel-default">
            <div class="panel-heading"><h4><b><?php echo $titulo; ?>  </b></h4></div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-1">
                        <b>Matrícula</b>
                        <input class="form-control" type="text" name="matricula" id="matricula" readonly="" value="<?php echo $matricula; ?>" />
                    </div>                    
                    <div class="col-md-4">
                        <b>Apellido y Nombre</b>
                        <input class="form-control" type="text" name="apellidoNombre" id="apellidoNombre" readonly="" value="<?php echo $apellidoNombre ?>"/>
                    </div>                    
                </div>
                <?php
                switch ($tipo) {
                    case 'A':
                        $continua = TRUE;
                        $idEspecialidad = NULL;
                            ?>
                            <div class="row">&nbsp;</div>
                            <form id="formAlta" name="formAlta" method="POST" onSubmit="" action="datosMesaEntrada/abm_especialidades.php?accion=<?php echo $accion; ?>">
                                <div class="row">
                                    <div class="col-md-5">
                                        <b>Tipo de Especialista *</b>  
                                        <select class="form-control" id="tipoEspecialista" name="tipoEspecialista" required="" onChange="habilitar(this)">
                                            <option value="">Seleccione el Tipo de Especialista</option>
                                            <?php
                                            $resTipoEspecialista = $resolucionesLogic->obtenerTiposEspecialista();
                                            if ($resTipoEspecialista['estado']) {
                                                $noMostrar[0] = "J";
                                                $noMostrar[1] = "C";
                                                $noMostrar[2] = "R";
                                                foreach ($resTipoEspecialista['datos'] as $row) {
                                                    if (!in_array($row['codigo'], $noMostrar)) {
                                                        //if ($row['codigo'] <> 'E' && ($colegiado['tipoEstado'] == 'A' || ($colegiado['tipoEstado'] == 'I' && $row['codigo'] == 'O'))) {
                                                        if ($colegiado['tipoEstado'] == 'A' || ($colegiado['tipoEstado'] == 'I' && ($row['codigo'] == 'O' || $row['codigo'] == 'A' || $row['codigo'] == 'E'))) {
                                                        ?>
                                                            <option value="<?php echo $row['codigo'] ?>" <?php if ($tipoEspecialista == $row['codigo']) { ?> selected="" <?php } ?>><?php echo $row['nombre'] ?></option>
                                                        <?php                                                
                                                        }
                                                    }
                                                }
                                            } else {
                                                echo $resTipoEspecialista['mensaje'];
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <div id="incisoArt8" style="<?php echo $mostrarIncisios; ?>">
                                            <b>Inciso - Articulo 8 por Excepción</b>&nbsp;&nbsp;&nbsp;
                                            <select class="form-control" id="inciso" name="inciso" >
                                                <option value="">Seleccione el Inciso correspondiente</option>
                                                <option value="a" <?php if ($inciso == "a") { ?> selected="" <?php } ?>>Inciso a - </option>
                                                <option value="b" <?php if ($inciso == "b") { ?> selected="" <?php } ?>>Inciso b - Universitario</option>
                                                <option value="c" <?php if ($inciso == "c") { ?> selected="" <?php } ?>>Inciso c - CONFEMECO</option>
                                                <option value="d" <?php if ($inciso == "d") { ?> selected="" <?php } ?>>Inciso d - Por puntaje</option>
                                                <option value="e" <?php if ($inciso == "e") { ?> selected="" <?php } ?>>Inciso e - Curso Superior</option>
                                                <option value="f" <?php if ($inciso == "f") { ?> selected="" <?php } ?>>Inciso f - Residencia</option>
                                            </select>                                            
                                        </div>
                                        <div id="distrito" style="<?php echo $mostrarDistritos; ?>">
                                            <b>Distrito de Origen</b>&nbsp;&nbsp;&nbsp;
                                            <select class="form-control" id="distrito" name="distrito" >
                                                <option value="">Seleccione el Distrito de Origen</option>
                                                <option value="2" <?php if ($distrito == "2") { ?> selected="" <?php } ?>>II</option>
                                                <option value="3" <?php if ($distrito == "3") { ?> selected="" <?php } ?>>III</option>
                                                <option value="4" <?php if ($distrito == "4") { ?> selected="" <?php } ?>>IV</option>
                                                <option value="5" <?php if ($distrito == "5") { ?> selected="" <?php } ?>>V</option>
                                                <option value="6" <?php if ($distrito == "6") { ?> selected="" <?php } ?>>VI</option>
                                                <option value="7" <?php if ($distrito == "7") { ?> selected="" <?php } ?>>VII</option>
                                                <option value="8" <?php if ($distrito == "8") { ?> selected="" <?php } ?>>VIII</option>
                                                <option value="9" <?php if ($distrito == "9") { ?> selected="" <?php } ?>>IX</option>
                                                <option value="10" <?php if ($distrito == "10") { ?> selected="" <?php } ?>>X</option>
                                            </select>                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">&nbsp;</div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div id="especialidad" style="<?php echo $requeridoEspecialidad; ?>">
                                            <b>Especialidad *</b>
                                            <?php 
                                                //$resEspecialidades = obtenerEspecialidades();
                                                $resEspecialidades = $objEspecialidades->obtenerEspecialidadesParaExpedientes($idColegiado);
                                                if ($resEspecialidades['estado']) {
                                                ?>
                                                    <select class="form-control" id="especialidad" name="especialidad" >
                                                        <option value="">Seleccione Especialidad</option>
                                                    <?php
                                                    foreach ($resEspecialidades['datos'] as $row) {
                                                        if ($row['idTipoEspecialidad'] <> 3) {
                                                        ?>
                                                        <option value="<?php echo $row['idEspecialidad'] ?>" <?php if ($especialidad == $row['idEspecialidad']) { ?> selected="" <?php } ?>><?php echo $row['nombreEspecialidad'] ?></option>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                    </select>
                                                <?php
                                                } else {
                                                    echo "NO HAY ESPECIALIDADES";
                                                }
                                            ?>
                                        </div>
                                        <div id="calificacion" style="<?php echo $requeridoAgregada; ?>">
                                            <b>Calificación Agregada *</b>&nbsp;&nbsp;&nbsp;
                                            <?php 
                                                $resCalificacion = $objEspecialidades->obtenerCalificacionesAgregadasSegunEspecialidadOtorgada($idColegiado);
                                                if ($resCalificacion['estado']) {
                                                ?>
                                                    <select class="form-control" id="especialidadCalificacion" name="especialidadCalificacion" >
                                                        <option value="">Seleccione Calificación Agregada</option>
                                                    <?php
                                                    foreach ($resCalificacion['datos'] as $row) {
                                                    ?>
                                                        <option value="<?php echo $row['idEspecialidad'] ?>" <?php if ($especialidad == $row['idEspecialidad']) { ?> selected="" <?php } ?>><?php echo $row['nombreEspecialidad'].' ('.$row['nombreEspecialidadPadre'].')' ?></option>
                                                    <?php                                                
                                                    }
                                                    ?>
                                                    </select>
                                                <?php
                                                } else {
                                                    echo "NO TIENE ESPECIALIDAD CON CALIFICACION AGREGADA";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">&nbsp;</div>
                                <div class="row">
                                    <div style="text-align:center">
                                        <button type="submit"  class="btn btn-success">Confirma</button>
                                        <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                        <input type="hidden" id="tipo" name="tipo" value="A">
                                        <input type="hidden" id="idEstadoMatricular" name="idEstadoMatricular" value="<?php echo $idEstadoMatricular; ?>">
                                        <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $estadoTesoreria; ?>">
                                        <?php if (isset($idMesaEntradaEspecialidad)) { ?>
                                            <input type="hidden" id="id" name="id" value="<?php echo $idMesaEntradaEspecialidad; ?>">
                                            <input type="hidden" id="idTipoEspecialista" name="idTipoEspecialista" value="<?php echo $idTipoEspecialista; ?>">
                                        <?php } ?>
                                    </div>
                                </div>  
                            </form>               
                        <?php
                        break;

                    case 'R':
                        if (isset($_POST['idColegiado']) && isset($_POST['idColegiadoEspecialista']) && isset($_POST['idEspecialidad'])
                                && isset($_POST['idTipoMovimiento']) && isset($_POST['estadoTesoreria'])) {
                            $idColegiado = $_POST['idColegiado'];
                            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
                            $idEspecialidad = $_POST['idEspecialidad'];
                            $idTipoMovimiento = $_POST['idTipoMovimiento'];
                            $estadoTesoreria = $_POST['estadoTesoreria'];
                            $idTipoEspecialista = 6;
                        } else {
                            $mensaje = "ERROR EN LOS DATOS INGRESADOS";
                            $continua = FALSE;
                        }

                        if ($continua) {
                            //agrega el movimiento en mesa de entradas
                            $resultado = $mesaEntradaEspecialistaLogic->realizarAltaMesaEntrada($idColegiado, $tipo, $idEspecialidad, $idTipoMovimiento, $estadoTesoreria, NULL, NULL, $idTipoEspecialista);
                            if ($resultado['estado']) {
                                $expediente = $resultado['datos'];
                                $numeroExpediente = $expediente['numeroExpediente'];
                                $anioExpediente = $expediente['anioExpediente'];
                            } else {
                                $continua = FALSE;
                            }
                        } else {
                            $resultado['estado'] = FALSE;
                            $resultado['icono'] = "glyphicon glyphicon-remove";
                            $resultado['clase'] = "alert alert-error";
                            $resultado['mensaje'] = $mensaje;
                        }

                        ?>
                        <div class="<?php echo $resultado['clase']; ?>" role="alert">
                            <span class="<?php echo $resultado['icono']; ?>" aria-hidden="true"></span>
                            <span><strong><?php echo $resultado['mensaje']; ?></strong></span>
                        </div>        
                        <?php
                        break;

                    case 'J':
                        if (isset($_POST['idColegiado']) && isset($_POST['idColegiadoEspecialista']) && isset($_POST['idEspecialidad'])
                                && isset($_POST['idTipoMovimiento']) && isset($_POST['estadoTesoreria'])) {
                            $idColegiado = $_POST['idColegiado'];
                            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
                            $idEspecialidad = $_POST['idEspecialidad'];
                            $idTipoMovimiento = $_POST['idTipoMovimiento'];
                            $estadoTesoreria = $_POST['estadoTesoreria'];
                            $idTipoEspecialista = 3;
                        } else {
                            $mensaje = "ERROR EN LOS DATOS INGRESADOS";
                            $continua = FALSE;
                        }

                        if ($continua) {
                            //agrega el movimiento en mesa de entradas
                            $resultado = $mesaEntradaEspecialistaLogic->realizarAltaMesaEntrada($idColegiado, $tipo, $idEspecialidad, $idTipoMovimiento, $estadoTesoreria, NULL, NULL, $idTipoEspecialista);
                        } else {
                            $resultado['estado'] = FALSE;
                            $resultado['icono'] = "glyphicon glyphicon-remove";
                            $resultado['clase'] = "alert alert-error";
                            $resultado['mensaje'] = $mensaje;
                        }

                        ?>
                        <div class="row">&nbsp;</div>
                        <div class="<?php echo $resultado['clase']; ?>" role="alert">
                            <span class="<?php echo $resultado['icono']; ?>" aria-hidden="true"></span>
                            <span><strong><?php echo $resultado['mensaje']; ?></strong></span>
                        </div>        
                        <?php
                        break;

                    case 'C':
                        if (isset($_POST['idColegiado']) && isset($_POST['idColegiadoEspecialista']) && isset($_POST['idEspecialidad'])
                                && isset($_POST['idTipoMovimiento']) && isset($_POST['estadoTesoreria'])) {
                            $idColegiado = $_POST['idColegiado'];
                            $idColegiadoEspecialista = $_POST['idColegiadoEspecialista'];
                            $idEspecialidad = $_POST['idEspecialidad'];
                            $idTipoMovimiento = $_POST['idTipoMovimiento'];
                            $estadoTesoreria = $_POST['estadoTesoreria'];
                            $idTipoEspecialista = 4;
                        } else {
                            $mensaje = "ERROR EN LOS DATOS INGRESADOS";
                            $continua = FALSE;
                        }

                        if ($continua) {
                            //agrega el movimiento en mesa de entradas
                            $resultado = $mesaEntradaEspecialistaLogic->realizarAltaMesaEntrada($idColegiado, $tipo, $idEspecialidad, $idTipoMovimiento, $estadoTesoreria, NULL, NULL, $idTipoEspecialista);
                        } else {
                            $resultado['estado'] = FALSE;
                            $resultado['icono'] = "glyphicon glyphicon-remove";
                            $resultado['clase'] = "alert alert-error";
                            $resultado['mensaje'] = $mensaje;
                        }

                        ?>
                        <div class="<?php echo $resultado['clase']; ?>" role="alert">
                            <span class="<?php echo $resultado['icono']; ?>" aria-hidden="true"></span>
                            <span><strong><?php echo $resultado['mensaje']; ?></strong></span>
                        </div>        
                        <?php
                        break;

                    default:
                        break;
                }
            ?>
            </div>
        </div>
        <?php
        if (isset($resultado) && $resultado['estado']) {
            if ($accion == 1) {
                $mesaEntrada = $resultado['datos'];
                $idMesaEntrada = $mesaEntrada['idMesaEntrada'];
                $numeroExpediente = $mesaEntrada['numeroExpediente'];
                $anioExpediente = $mesaEntrada['anioExpediente'];
            }
        ?>
            <div class="col-md-12 text-center">
                <a href="datosMesaEntrada/especialidades_expedientes_imprimir.php?n_exp=<?php echo $numeroExpediente; ?>&a_exp=<?php echo $anioExpediente; ?>" target="_BLANK" 
                   class="btn btn-info glyphicon glyphicon-print">&nbsp;Imprimir Expediente</a>
            </div>  
            <br>
        <?php
        } else {
            if (isset($resultado)) {
            ?>
                <div class="<?php echo $resultado['clase']; ?>" role="alert">
                    <span class="<?php echo $resultado['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resultado['mensaje']; ?></strong></span>
                </div>        
            <?php
            }
        }
    }
} else {
    $mensaje = "TIPO ESPECIALISTA NO INGRESADO";
    $continua = FALSE;
}
?>
<!-- BOTON VOLVER -->    
<div class="col-md-12" style="text-align:right;">
    <form  method="POST" action="especialidades_expedientes.php">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Cerrar </button>
   </form>
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
        $('#especialidadDetalle').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'especialidades.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 2,
            //maxItem:15,
            
            updater: function (item) {
                $('#especialidad').val(nameIdMap[item]);
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
    
    $(document).ready(
    function () {
                $('#tablaEspecialista').DataTable({
                    "iDisplayLength":8,
                     "order": [[ 0, "desc" ], [ 1, "asc"]],
                    "language": {
                        "url": "../public/lang/esp.lang"
                    },
                    "bPaginate": false,
                    "bInfo" : false,
                    "bLengthChange": false,
                    "bFilter": false,
                });
    }
);

function habilitar(sel) {
    if (sel.value=="X"){
        divT = document.getElementById("incisoArt8");
        divT.style.display = "";
        divT = document.getElementById("distrito");
        divT.style.display = "none";
        divT = document.getElementById("calificacion");
        divT.style.display = "none";
        divT = document.getElementById("especialidad");
        divT.style.display = "";
        document.getElementById("especialidad").required = true;
        document.getElementById("especialidadDetalle").required = true;
    }else{
        if (sel.value=="O"){
            divT = document.getElementById("distrito");
            divT.style.display = "";
            divT = document.getElementById("incisoArt8");
            divT.style.display = "none";
            divT = document.getElementById("calificacion");
            divT.style.display = "none";
            divT = document.getElementById("especialidad");
            divT.style.display = "";
            document.getElementById("especialidad").required = true;
            document.getElementById("especialidadDetalle").required = true;
        }else{
            if (sel.value=="A"){
                divT = document.getElementById("distrito");
                divT.style.display = "none";
                divT = document.getElementById("incisoArt8");
                divT.style.display = "none";
                divT = document.getElementById("calificacion");
                divT.style.display = "";
                divT = document.getElementById("especialidad");
                divT.style.display = "none";
                document.getElementById("especialidad").required = false;
                document.getElementById("especialidadDetalle").required = false;
            }else{
                divT = document.getElementById("incisoArt8");
                divT.style.display = "none";
                divT = document.getElementById("distrito");
                divT.style.display = "none";
                divT = document.getElementById("calificacion");
                divT.style.display = "none";
                divT = document.getElementById("especialidad");
                divT.style.display = "";
                document.getElementById("especialidad").required = true;
                document.getElementById("especialidadDetalle").required = true;
            }
        }
    }
}
  
</script>
