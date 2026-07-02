<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/tipoMovimientoLogic.php');

$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
$mesaEntradaLogic = new mesaEntradaLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    if (isset($_GET['anular'])) {
        $accion = "ANULAR";
        $requerido = "required";
    } else {
        $accion = "CONSULTAR";
        $readOnly = "readonly";
    }
    $idMesaEntrada = $_GET['id'];
    $resMovimientoMatricular = $mesaEntradaLogic->obtenerMesaEntradaMovimientoPorId($idMesaEntrada);
    if ($resMovimientoMatricular['estado']) {
        $movimientoMatricular = $resMovimientoMatricular['datos'];
        $idMesaEntradaMovimiento = $movimientoMatricular['idMesaEntradaMovimiento'];
        $idColegiado = $movimientoMatricular['idColegiado'];
        if ($accion == 'ANULAR') {
            $observaciones = NULL;
        } else {
            $observaciones = $movimientoMatricular['observaciones'];    
        }
        $fechaIngreso = $movimientoMatricular['fechaIngreso'];
        $fechaMovimiento = $movimientoMatricular['fechaMovimiento'];
        $idTipoMovimientoOriginal = $movimientoMatricular['idTipoMovimiento'];
        $nombreTipoMovimiento = $movimientoMatricular['nombreTipoMovimiento'];
        $nombreTipoMovimientoCompleto = $movimientoMatricular['nombreTipoMovimientoCompleto'];
        $idMotivoCancelacion = $movimientoMatricular['idMotivoCancelacion'];
        $nombreMotivoCancelacion = $movimientoMatricular['nombreMotivoCancelacion'];
        $distrito = $movimientoMatricular['distrito'];
        $idEstadoMatricularOriginal = $movimientoMatricular['estadoMatricular'];
        $estadoMatricularOriginal = $movimientoMatricular['estadoMatricularOriginal'];
        $codigoDeudorOriginal = $movimientoMatricular['estadoTesoreria'];
        $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudorOriginal);
        if ($resEstadoTesoreria['estado']) {
            $estadoTesoreriaOriginal = $resEstadoTesoreria['estadoTesoreria'];
        } else {
            $estadoTesoreriaOriginal = $resEstadoTesoreria['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resMovimientoMatricular['mensaje'];
        $clase = $resMovimientoMatricular['clase'];    
    }
} else {
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idColegiado - ';
    }
    $accion = "AGREGAR";
    $idMesaEntrada = NULL;
    $observaciones = "";
    $fechaMovimiento = date('Y-m-d');
    $idTipoMovimiento = NULL;
    $idMotivoCancelacion = NULL;
    $distrito = NULL;
}
if (isset($_POST['mensaje'])) {
?>
    <div id="divMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php    
    if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
        $idColegiado = $_POST['idColegiado'];
    } else {
        $idColegiado = NULL;
    }
    if (isset($_POST['colegiado_buscar']) && $_POST['colegiado_buscar'] <> "") {
        $colegiado_buscar = $_POST['colegiado_buscar'];
    } else {
        $colegiado_buscar = NULL;
    }
    if (isset($_POST['observaciones']) && $_POST['observaciones'] <> "") {
        $observaciones = $_POST['observaciones'];
    } else {
        $observaciones = NULL;
    }
    if (isset($_POST['idTipoMovimiento']) && $_POST['idTipoMovimiento'] <> "") {
        $idTipoMovimiento = $_POST['idTipoMovimiento'];
    }
    if (isset($_POST['fechaMovimiento']) && $_POST['fechaMovimiento'] <> "") {
        $fechaMovimiento = $_POST['fechaMovimiento'];
    }
    if (isset($_POST['idMotivoCancelacion']) && $_POST['idMotivoCancelacion'] <> "") {
        $idMotivoCancelacion = $_POST['idMotivoCancelacion'];
    }
    if (isset($_POST['distrito']) && $_POST['distrito'] <> "") {
        $distrito = $_POST['distrito'];
    }
}   

$titulo = "MOVIMIENTOS MATRICULARES";


if (isset($idColegiado) && $idColegiado <> "") {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $colegiado_buscar = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        $idEstadoMatricular = $colegiado['idEstadoMatricular'];
        $estadoMatricular = $colegiado['estado'];
        $tipoEstadoMatricular = $colegiado['tipoEstado'];
        $movimientoCompleto = $colegiado['movimientoCompleto'];
        if ($tipoEstadoMatricular == 'A' || $tipoEstadoMatricular == 'I'){
            $estiloColegiado = ' style="color: green; font-size: large;"';
        } else {
            $estiloColegiado = ' style="color: red;"';
        }        
    } else {
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
    }

    //obtengo el estado actual con tesoreria, solo si no es ni fallecido ni jubilado
    $aJubFal = array('J', 'F');
    if (!in_array($colegiado['tipoEstado'], $aJubFal)){
        $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, PERIODO_ACTUAL);
        if ($resEstadoTeso['estado']) {
            $codigoDeudor = $resEstadoTeso['codigoDeudor'];
            $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoDeudor);
            if ($resEstadoTesoreria['estado']) {
                $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
            } else {
                $estadoTesoreria = $resEstadoTesoreria['mensaje'];
            }
        } else {
            $estadoTesoreria = $resEstadoTeso['mensaje'];
        }

        if ($codigoDeudor == 0) {
            $estiloTesoreria = ' style="color: green; font-size: large;"';
        } else {
            $estiloTesoreria = ' style="color: red;"';
        }
        $mostrarTesoreria = TRUE;

        //verificamos si esta al dia para poder realizar el movimiento, solo si tiene permiso le genera el movimiento en caso de ser deudor
        if ($codigoDeudor == 0 || $usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 103)) {
            $generaMovimiento = TRUE;

            //verificamos si ya no tiene un movimiento generado en el mismo dia
            if ($accion == 'AGREGAR') {
                if ($mesaEntradaLogic->existeMovimientoParaColegiadoFecha($idColegiado, $idTipoMovimiento)) {
                    $generaMovimiento = FALSE;
                    $mensaje .= "Ya existe un movimiento en la fecha actual.";
                }
            }
        } else {
            $generaMovimiento = FALSE;
            $mensaje .= "No está en condiciones de generar el movimiento!";
        }
    } else {
        $mostrarTesoreria = FALSE;
        $generaMovimiento = TRUE;
        $codigoDeudor = 0;
    }
    //fin tesoreria
    $mostrarDistritos = "display: none;";
    $nombreBoton = 'Guardar Movimiento';

    if ($accion == 'ANULAR') {
        $readOnly = "readonly";
        $mostrarTesoreria = TRUE;
        $generaMovimiento = TRUE;
        $nombreBoton = 'Anular Movimiento';
        //if (isset($distrito) && $distrito <> "") {
            $mostrarDistritos = NULL;
        //}
    }
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
                <a href="mesa_entrada_listado.php" class="btn btn-info">Salir</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formJota" name="formJota" method="POST" onSubmit="" action="datosMesaEntrada\abm_movimientos.php?<?php
                switch ($accion) {
                    case 'AGREGAR':
                        echo 'agregar';
                        break;

                    case 'ANULAR':
                        echo 'anular&id='.$idMesaEntrada;
                        break;

                    case 'EDITAR':
                        echo 'editar&id='.$idMesaEntrada;
                        break;

                    case 'CONSULTAR':
                        echo 'consultar&id='.$idMesaEntrada;
                        break;
                    
                    default:
                        echo '';
                        break;
                }
                ?>">
                <div class="row">
                    <div class="col-md-1">
                        <label for="colegiado_buscar">Matrícula: </label>
                        <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly />
                    </div>
                    <div class="col-md-4">
                        <label for="colegiado_buscar">Colegiado: </label>
                        <input class="form-control" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" readonly/>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <div class="col-md-4">
                        <label for="elEstado">Estado Matricular: </label>
                        <input class="form-control" name="elEstado" id="elEstado" type="text" <?php echo $estiloColegiado; ?> 
                            value="<?php 
                                //se agrega esta condicion el 7/3/2024 a pedido de secretaria
                                if ($colegiado['idEstadoMatricular'] <> 9) {
                                    $elEstado = trim($colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']));
                                    if (isset($elEstado) && $elEstado <> "") {
                                        $elEstado .= ' - ';
                                    }
                                } else {
                                    $elEstado = "";
                                }
                                echo $elEstado.$movimientoCompleto; ?>" 
                            readonly=""/>
                        <input type="hidden" name="estadoMatricular" id="estadoMatricular" value="<?php echo $estadoMatricular; ?>" />
                    </div>
                    <?php 
                    if ($mostrarTesoreria) {
                    ?>
                        <div class="col-md-3">
                            <label for="estadoTesoreria">Estado con Tesorería: </label>
                            <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> name="estadoTesoreria" id="estadoTesoreria" value="<?php echo $estadoTesoreria  ?>" readonly=""/>
                            <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor; ?>" />
                        </div>
                    <?php 
                    } else {
                    ?>
                        <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="0" />
                        <input type="hidden" name="estadoTesoreria" id="estadoTesoreria" value="Al día" />
                    <?php
                    }
                    ?>                    
                </div>
                <?php 
                if ($accion == 'ANULAR') {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="elEstadoOriginal">Estado en el momento del trámite: </label>
                            <input class="form-control" name="elEstadoOriginal" id="elEstadoOriginal" type="text" 
                                value="<?php echo $estadoMatricularOriginal; ?>" 
                                readonly=""/>
                            <input type="hidden" name="idTipoMovimientoOriginal" id="idTipoMovimientoOriginal" value="<?php echo $idTipoMovimientoOriginal; ?>" />
                        </div>
                        <div class="col-md-5">
                            <label for="estadoTesoreriaOriginal">Estado con Tesorería al momento del trámite: </label>
                            <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> name="estadoTesoreriaOriginal" id="estadoTesoreriaOriginal" value="<?php echo $estadoTesoreriaOriginal  ?>" readonly=""/>
                            <input type="hidden" name="codigoDeudorOriginal" id="codigoDeudorOriginal" value="<?php echo $codigoDeudorOriginal; ?>" />
                        </div>
                    </div>
                <?php 
                }
                if ($generaMovimiento) {
                ?>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-5">
                        <label for="idTipoMovimiento">Tipo de Movimiento</label>
                        <select class="form-control" id="idTipoMovimiento" name="idTipoMovimiento" required="" <?php echo $readOnly; ?>>
                            <?php 
                            if ($accion == 'ANULAR') {
                            ?>
                                <option value="<?php echo $idTipoMovimientoOriginal; ?>" selected><?php echo $nombreTipoMovimiento; ?></option>
                            <?php
                            } else {
                            ?>
                                <option value="">Seleccione Tipo de Movimiento</option>
                                <?php 
                                $resTiposMovimiento = $mesaEntradaLogic->obtenerTiposMovimientoMesaEntrada($idEstadoMatricular);
                                if ($resTiposMovimiento['estado']) {
                                    foreach ($resTiposMovimiento['datos'] as $dato) {
                                    ?>
                                        <option value="<?php echo $dato['id']; ?>" <?php if ($idTipoMovimiento == $dato['id']) { echo 'selected'; } ?>><?php echo $dato['nombre']; ?></option>
                                    <?php
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="fechaMovimiento">Fecha desde: </label>
                        <input class="form-control" type="date" name="fechaMovimiento" id="fechaMovimiento" value="<?php echo $fechaMovimiento; ?>" <?php echo $readOnly; ?>>
                    </div>
                    <div class="col-md-3" id="motivoMovimiento">
                        <label for="idMotivoCancelacion">Motivo del movimiento</label>
                        <select class="form-control" id="idMotivoCancelacion" name="idMotivoCancelacion" required="" <?php echo $readOnly; ?> onChange="habilitar_segun_motivo_cancelacion(this)">
                            <option value="">Seleccione Motivo</option>
                            <?php 
                            $resMotivosCancelacion = $mesaEntradaLogic->obtenerMotivosCancelacion();
                            if ($resMotivosCancelacion['estado']) {
                                foreach ($resMotivosCancelacion['datos'] as $dato) {
                                ?>
                                    <option value="<?php echo $dato['id']; ?>" <?php if ($idMotivoCancelacion == $dato['id']) { echo 'selected'; } ?>><?php echo $dato['nombre']; ?></option>
                                <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php 
                    if (isset($mostrarDistritos)) {
                    ?>
                    <div class="col-md-2" id="distritos" style="<?php echo $mostrarDistritos; ?>">
                        <label for="distrito">Distrito</label>
                        <select class="form-control" id="distrito" name="distrito" required="" <?php echo $readOnly; ?>>
                            <option value="">Seleccione</option>
                            <option value="2" <?php if ($distrito == "2") { echo 'selected'; } ?>>Distrito II</option>
                            <option value="3" <?php if ($distrito == "3") { echo 'selected'; } ?>>Distrito III</option>
                            <option value="4" <?php if ($distrito == "4") { echo 'selected'; } ?>>Distrito IV</option>
                            <option value="5" <?php if ($distrito == "5") { echo 'selected'; } ?>>Distrito V</option>
                            <option value="6" <?php if ($distrito == "6") { echo 'selected'; } ?>>Distrito VI</option>
                            <option value="7" <?php if ($distrito == "7") { echo 'selected'; } ?>>Distrito VII</option>
                            <option value="8" <?php if ($distrito == "8") { echo 'selected'; } ?>>Distrito VIII</option>
                            <option value="9" <?php if ($distrito == "9") { echo 'selected'; } ?>>Distrito IX</option>
                            <option value="10" <?php if ($distrito == "10") { echo 'selected'; } ?>>Distrito X</option>
                        </select>
                    </div>
                    <?php 
                    } 
                    ?>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="observaciones">Observaciones </label>
                        <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="3" <?php if ($accion == 'CONSULTAR') { echo 'readonly'; } ?>><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success" ><?php echo $nombreBoton; ?></button>
                        <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                        <?php 
                        if (isset($idMesaEntrada)) {
                        ?>
                            <input type="hidden" name="idMesaEntrada" id="idMesaEntrada" value="<?php echo $idMesaEntrada; ?>">
                        <?php 
                        } 
                        ?>
                    </div>
                </div>  
                <?php 
                } else {
                ?>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4 class="alert alert-warning"><?php echo $mensaje; ?></h4>
                    </div>
                </div>  
                <?php
                }
                ?>
            </form>   
        <?php
        } else {
        ?>
            <div class="row">&nbsp;</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="<?php echo $clase; ?>" role="alert">
                        <span><strong><?php echo $mensaje; ?></strong></span>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
<script language="JavaScript">

function habilitar_segun_motivo_cancelacion(sel) {
    /*var data = [
                  [1, "4"],
                  [2, "5"],
                  [3, "7"],
                  [4, "8"],
                  [4, "11"],
                  [4, "12"],
                ];*/
    var data = [
                  [1, "7"],
                  [2, "8"],
                  [3, "11"],
                  [4, "16"],
                ];
    var id = sel.value;
    var result = ""; // Declaro auxiliar para depositar los datos en un string común
    for (const item of data) { // recorro cada array dentro del array padre
      if (item[1] === id) { // en cada array ve si en indice 0 o sea el id es igual id
        //Si fue igual concateno en un string su id, su nombre y el último dato
        result = "MUESTRA_DISTRITOS"
      }
    }

    if (result == "MUESTRA_DISTRITOS"){
        divT = document.getElementById("distritos");
        divT.style.display = "";
        document.getElementById("distrito").required = true;
    }else{
        divT = document.getElementById("distritos");
        divT.style.display = "none";
        document.getElementById("distrito").required = false;
    }
}
</script>