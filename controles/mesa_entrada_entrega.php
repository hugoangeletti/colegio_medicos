<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/remitenteLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
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
    $idMesaEntradaEntrega = $_GET['id'];
    $idMesaEntrada = NULL;
    $resEntrega = $mesaEntradaLogic->obtenerMesaEntradaEntregaPorId($idMesaEntradaEntrega, $idMesaEntrada);
    if ($resEntrega['estado']) {
        $entrega = $resEntrega['datos'];
        $idMesaEntrada = $entrega['idMesaEntrada'];
        $idColegiado = $entrega['idColegiado'];
        $observaciones = $entrega['observaciones'];
        $fechaIngreso = $entrega['fechaIngreso'];
        $fechaEntrega = $entrega['fechaEntrega'];
        $idTipoEntrega = $entrega['idTipoEntrega'];
        $nombreTipoEntrega = $entrega['nombreTipoEntrega'];
        $leyendaTipoEntrega = $entrega['leyendaTipoEntrega'];
    } else {
        $continua = FALSE;
        $mensaje .= $resEntrega['mensaje'];
        $clase = $resEntrega['clase'];    
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
    $fechaEntrega = date('Y-m-d');
    $fechaExtravio = NULL;
    $idMesaEntrada = NULL;
    $mostrarEspecialidades = "display: none;";
}
if (isset($_POST['mensaje'])) {
?>
    <div id="divMensaje"> 
        <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
    </div>
    <?php    
    /*
    if (isset($_POST['idMesaEntrada']) && $_POST['idMesaEntrada'] <> "") {
        $idMesaEntrada = $_POST['idMesaEntrada'];
    } else {
        $idMesaEntrada = NULL;
    }
    */
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
    if (isset($_POST['idTipoDenuncia']) && $_POST['idTipoDenuncia'] <> "") {
        $idTipoDenuncia = $_POST['idTipoDenuncia'];
    }
    if (isset($_POST['fechaDenuncia']) && $_POST['fechaDenuncia'] <> "") {
        $fechaDenuncia = $_POST['fechaDenuncia'];
    }
    if (isset($_POST['fechaExtravio']) && $_POST['fechaExtravio'] <> "") {
        $fechaExtravio = $_POST['fechaExtravio'];
    }
}   

$titulo = "ENTREGA DE DOCUMENTACIÓN";

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
                <a href="mesa_entrada_listado.php" class="btn btn-info">Salir</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php 
        if ($continua) {
            ?>  
            <form id="formJota" name="formJota" method="POST" onSubmit="" action="datosMesaEntrada\abm_entrega.php?<?php if ($accion == "AGREGAR") { echo 'agregar'; } else { echo 'editar'; } ?>">
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
                    </div>
                    <?php 
                    if ($mostrarTesoreria) {
                    ?>
                        <br>
                        <div class="col-md-4">
                            <b>Estado con Tesorería: </b>
                            <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> value="<?php echo $estadoTesoreria  ?>" readonly=""/>
                            <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor; ?>" />
                        </div>
                    <?php 
                    }
                    ?>                    
                </div>
                <div class="row">&nbsp;</div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="idTipoDenuncia">Tipo de Entrega</label>
                        <select class="form-control" id="idTipoEntrega" name="idTipoEntrega" required="" <?php echo $readOnly; ?> onChange="habilitar(this)">
                            <option value="">Seleccione Tipo</option>
                            <?php 
                            $idTipoEntrega = NULL;
                            $resTiposEntrega = $mesaEntradaLogic->obtenerTiposEntrega();
                            if ($resTiposEntrega['estado']) {
                                foreach ($resTiposEntrega['datos'] as $dato) {
                                ?>
                                    <option value="<?php echo $dato['id']; ?>" <?php if ($idTipoEntrega == $dato['id']) { echo 'selected'; } ?>><?php echo $dato['nombre']; ?></option>
                                <?php
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4" id="especialidades" style="<?php echo $mostrarEspecialidades; ?>">
                            <label for="idTituloEspecialista">Título especialista en: </label>
                            <select class="form-control" id="idTituloEspecialista" name="idTituloEspecialista" required >
                                <option value="">Seleccione Especialidad</option>
                                <?php 
                                $idTituloEspecialista = NULL;
                                $resTitulos = $colegiadoEspecialistaLogic->obtenerTitulosParaEntregaPorIdColegiado($idColegiado);
                                if ($resTitulos['estado']) {
                                    foreach ($resTitulos['datos'] as $dato) {
                                    ?>
                                        <option value="<?php echo $dato['idTituloEspecialista']; ?>" <?php if ($idTituloEspecialista == $dato['idTituloEspecialista']) { echo 'selected'; } ?>><?php echo $dato['especialidadEntregar']; ?></option>
                                    <?php
                                    }
                                }
                                ?>
                            </select>
                    </div>
                    <div class="col-md-3">
                        <label for="fechaEntrega">Fecha de entrega: </label>
                        <input class="form-control" type="date" name="fechaEntrega" id="fechaEntrega" value="<?php echo $fechaEntrega; ?>"> <?php echo $readOnly; ?>
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
function habilitar(sel) {
    if (sel.value=="3"){
        //Titulo de especialista
        divT = document.getElementById("especialidades");
        divT.style.display = "";
        document.getElementById("idTituloEspecialista").required = true;
    } else {
        divT = document.getElementById("especialidades");
        divT.style.display = "none";
        document.getElementById("idTituloEspecialista").required = false;
    }
}

</script>