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
    $idMesaEntradaAutoprescripcion = $_GET['id'];
    $resAutoprescripcion = $mesaEntradaLogic->obtenerMesaEntradaAutoprescripcionPorId($idMesaEntradaAutoprescripcion);
    if ($resAutoprescripcion['estado']) {
        $nota = $resAutoprescripcion['datos'];
        $idMesaEntrada = $nota['idMesaEntrada'];
        $tema = $nota['tema'];
        $incluyeListaMovimientos = $nota['incluyeListaMovimientos'];
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
        $continua = FALSE;
        $mensaje .= $resAutoprescripcion['mensaje'];
        $clase = $resAutoprescripcion['clase'];    
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
    $fecha = date('Y-m-d');
    $autorizado1 = NULL;
    $documentoAutorizado1 = NULL;
    $autorizado2 = NULL;
    $documentoAutorizado2 = NULL;
    $parentesco1 = NULL;
    $parentesco2 = NULL;
}
$titulo = "AUTOPRESCRIPCIÓN";

if (isset($idColegiado) & $idColegiado <> "") {
    $colegiadoLogic = new colegiadoLogic();
    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
    if ($resColegiado['estado']) {
        $colegiado = $resColegiado['datos'];
        $matricula = $colegiado['matricula'];
        $colegiado_buscar = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        $estadoMatricular = $colegiado['estado'];
        $tipoEstadoMatricular = $colegiado['tipoEstado'];
        $movimientoCompleto = $colegiado['movimientoCompleto'];
        if ($tipoEstadoMatricular == 'A' || $tipoEstadoMatricular == 'I'){
            $estiloColegiado = ' style="color: green; font-size: large;"';
        } else {
            $estiloColegiado = ' style="color: red;"';
        }        
        if ($colegiado['idEstadoMatricular'] == 44 || $colegiado['idEstadoMatricular'] == 45) {
            //si el estado matricular es por jubilacion de otro distrito, no se permite la autoprescrpcion (Estado = 44 o 45)
            $continua = FALSE;
            $clase = 'alert alert-danger';
            $mensaje .= '<p>Matrícula: <b>'.$matricula.'</b></p>
                        <p>Apellido y Nombre: <b>'.$colegiado_buscar.'</b></p>
                        <p>NO SE PUEDE GENERAR LA AUTOPRESCRIPCIÓN POR ESTAR CON JUBILACIÓN EN OTRO DISTRITO.</b></p>
                        <p>Estado actual de la matrícula: <b>'.$elEstado.$movimientoCompleto.'</b></p>';
        }
    } else {
        $continua = FALSE;
        $mensaje .= $resColegiado['mensaje'];
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
            <form id="formJota" name="formJota" method="POST" onSubmit="" action="datosMesaEntrada\abm_autoprescripcion.php?<?php if ($accion == "AGREGAR") { echo 'agregar'; } else { echo 'editar'; } ?>">
                <div class="row">
                    <div class="col-md-2">
                        <label for="colegiado_buscar">Matrícula: </label>
                        <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly />
                    </div>
                    <div class="col-md-5">
                        <label for="colegiado_buscar">Colegiado: </label>
                        <input class="form-control" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" readonly/>
                        <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                    </div>
                    <div class="col-md-5">
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
                        <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="0" />
                    </div>
                </div>
                <?php 
                if ($tipoEstadoMatricular == 'J') {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-2">
                            <label for="fecha">Fecha Autoprescripción </label>
                            <input class="form-control" type="date" name="fecha" id="fecha" <?php echo $readOnly; ?> required>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Autorizado 1</h5>
                            <div class="col-md-6">
                                <label for="autorizado1">Apellido y Nombres: </label>
                                <input class="form-control" type="text" name="autorizado1" id="autorizado1" <?php echo $readOnly; ?>>
                            </div>
                            <div class="col-md-3">
                                <label for="documentoAutorizado1">Documento: </label>
                                <input class="form-control" type="text" name="documentoAutorizado1" id="documentoAutorizado1" <?php echo $readOnly; ?>>
                            </div>
                            <div class="col-md-3">
                                <label for="parentescoAutorizado1">Parentesco: </label>
                                <input class="form-control" type="text" name="parentescoAutorizado1" id="parentescoAutorizado1" <?php echo $readOnly; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Autorizado 2</h5>
                            <div class="col-md-6">
                                <label for="autorizado2">Autorizado: </label>
                                <input class="form-control" type="text" name="autorizado2" id="autorizado2" <?php echo $readOnly; ?>>
                            </div>
                            <div class="col-md-3">
                                <label for="documentoAutorizado2">Documento: </label>
                                <input class="form-control" type="text" name="documentoAutorizado2" id="documentoAutorizado2" <?php echo $readOnly; ?>>
                            </div>
                            <div class="col-md-3">
                                <label for="parentescoAutorizado2">Parentesco: </label>
                                <input class="form-control" type="text" name="parentescoAutorizado2" id="parentescoAutorizado2" <?php echo $readOnly; ?>>
                            </div>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="observaciones">Observaciones </label>
                            <textarea class="form-control" type="text" name="observaciones" id="observaciones" rows="3" <?php echo $readOnly; ?>><?php echo $observaciones; ?></textarea>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 text-center">
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
                <?php
                } else {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <div class="col-md-12 alert alert-warning">
                            El colegiado no está JUBILADO, no se puede generar la Autoprescipción.
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
