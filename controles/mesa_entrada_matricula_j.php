<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/remitenteLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/mesaEntradaLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

$continua = TRUE;
$mensaje = "";
$readOnly = "";
$requerido = "";
$mesaEntradaLogic = new mesaEntradaLogic();
if (isset($_GET['id']) && $_GET['id'] <> "") {
    if (isset($_GET['editar'])) {
        $accion = "EDITAR";
        $requerido = "required";
    } else {
        $accion = "CONSULTAR";
        $readOnly = "readonly";
    }
    $idMesaEntrada = $_GET['id'];
    $resMesa = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
    if ($resMesa['estado']) {
        $mesaEntrada = $resMesa['datos'];
        if (isset($mesaEntrada['idColegiado']) && $mesaEntrada['idColegiado'] <> "") {
            $idColegiado = $mesaEntrada['idColegiado'];
        } else {
            $idRemitente = NULL;
            $continua = FALSE;
            $mensaje .= 'Mal ingresado, falta Colegiado o Remitente';
        }
        $observaciones = $mesaEntrada['observaciones'];
    } else {
        $continua = FALSE;
        $mensaje .= $resMesa['mensaje'];
        $clase = $resMesa['clase'];    
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
}
$titulo = "MATRÍCULA J";

if (isset($idColegiado) & $idColegiado <> "") {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $colegiado_buscar = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        $estadoMatricular = $colegiado['estado'];
        $movimientoCompleto = $colegiado['movimientoCompleto'];
        if ($colegiado['tipoEstado'] == 'A' || $colegiado['tipoEstado'] == 'I'){
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
    } else {
        $mostrarTesoreria = FALSE;
        $codigoDeudor = 0;
    }
    //fin tesoreria
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-9">
                <h4><b><?php echo $titulo; ?></b></h4>
            </div>
            <div class="col-xs-3 text-right">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formJota" name="formJota" method="POST" onSubmit="" action="datosMesaEntrada\abm_matricula_j.php?<?php if ($accion == "AGREGAR") { echo 'agregar'; } else { echo 'editar'; } ?>">
                <div class="row">
                    <div class="col-md-2">
                        <label for="colegiado_buscar">Matrícula: </label>
                        <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly/>
                    </div>
                    <div class="col-md-6">
                        <label for="colegiado_buscar">Colegiado: </label>
                        <input class="form-control" autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" readonly/>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-6">
                        <b>Estado Matricular: </b>
                        <input class="form-control" type="text" <?php echo $estiloColegiado; ?> 
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
                        <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor; ?>" />
                    </div>
                    <?php 
                    if ($mostrarTesoreria) {
                    ?>
                        <br>
                        <div class="col-md-4">
                            <b>Estado con Tesorería: </b>
                            <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> value="<?php echo $estadoTesoreria  ?>" readonly=""/>
                        </div>
                    <?php 
                    }
                    ?>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8">
                        <label for="observaciones">Observaciones </label>
                        <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="5" <?php echo $readOnly; ?>><?php echo $observaciones; ?></textarea>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-8 text-center">
                        <button type="submit" class="btn btn-success" >Guardar</button>
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
<div class="row">
    <div class="col-md-1 text-right">
        <a href="mesa_entrada_listado.php" class="btn btn-info">Salir</a>
    </div>
</div>
<?php    
require_once '../html/footer.php';
?>
