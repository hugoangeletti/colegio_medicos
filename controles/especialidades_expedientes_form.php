<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/resolucionesLogic.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/colegiadoEspecialistaLogic.php');
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
require_once ('../dataAccess/mesaEntradaEspecialistaLogic.php');
$mesaEntradaEspecialistaLogic = new mesaEntradaEspecialistaLogic();

$continua = TRUE;
$periodoActual = $_SESSION['periodoActual'];
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    if ($accion == 1) {
        $titulo = "Alta de ";
        $nombreBoton="Guardar";
        $especialidad = "";
        $especialidadDetalle = "";
        $estado = "A"; 
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado'] && $resColegiado['datos']) {
                $colegiado = $resColegiado['datos'];
                $matricula = $colegiado['matricula'];
                $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
                $idEstadoMatricular = $colegiado['idEstadoMatricular'];
            } else {
                $continua = FALSE;
            ?>
                <div class="<?php echo $resColegiado['clase']; ?>" role="alert">
                    <span class="<?php echo $resColegiado['icono']; ?>" aria-hidden="true"></span>
                    <span><strong><?php echo $resColegiado['mensaje']; ?></strong></span>
                </div>        
            <?php
            }
        } else {
            $idColegiado = NULL;
            $matricula = NULL;
            $apellidoNombre = NULL;
        }
    } else { 
        $continua = FALSE;
    }
} else {
    $continua = FALSE;
}
?>

<div class="panel panel-default">
    <div class="panel-heading"><h4><b><?php echo $titulo; ?>Expediente para Especialidades </b></h4></div>
    <div class="panel-body">
        <?php
        if ($continua){
        ?>
            <?php
            if (isset($_POST['mensaje']))
            {
             ?>
                <div id="divMensaje"> 
                    <p class="<?php echo $_POST['tipomensaje'];?>"><?php echo $_POST['mensaje'];?></p>  
                </div>
             <?php    
                $idResolucion = $_POST['idResolucion'];
                $tipo = $_POST['tipo'];
                $especialidad = $_POST['especialidad'];
                $especialidadDetalle = $_POST['especialidadDetalle'];
                $estado = $_POST['estado']; 
                $fechaAprobada = $_POST['fechaAprobada'];
                $fechaRecertificacion = $_POST['fechaRecertificacion'];
                $idColegiado = $_POST['idColegiado'];
                $matricula = $_POST['matricula'];
                $apellidoNombre = $_POST['apellidoNombre'];
            } 
            
            if (!isset($idColegiado)) {
            ?>
                <form id="formResolucionMatricula" name="formResolucionMatricula" method="POST" onSubmit="" action="especialidades_expedientes_form.php?accion=<?php echo $accion; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Matr&iacute;cula o Apellido y Nombre *</label>
                            <input class="form-control" autofocus autocomplete="OFF" type="text" name="apellidoNombre" id="apellidoNombre" placeholder="Ingrese Matrícula o Apellido del colegiao" required="" value="<?php echo $apellidoNombre ?>"/>
                            <input type="hidden" name="idColegiado" id="idColegiado" required="" value="<?php echo $idColegiado; ?>" />
                        </div>                    
                        <div class="col-md-4">
                            <br>
                            <button type="submit"  class="btn btn-success " >Confirma colegiado </button>
                        </div>
                    </div>  
                </form>
            <?php
            } else {
            ?>
                <form id="formOtro" name="formOtro" method="POST" onSubmit="" action="especialidades_expedientes_form.php?accion=<?php echo $accion; ?>">
                    <div class="row">
                        <div class="col-md-1">
                            <b>Matrícula</b>
                            <input class="form-control" type="text" name="matricula" id="matricula" readonly="" value="<?php echo $matricula; ?>" />
                        </div>                    
                        <div class="col-md-4">
                            <b>Apellido y Nombre</b>
                            <input class="form-control" type="text" name="apellidoNombre" id="apellidoNombre" readonly="" value="<?php echo $apellidoNombre ?>"/>
                        </div>                    
                        <div class="col-md-2">
                            <?php
                            if ($colegiado['tipoEstado'] == 'A' || $colegiado['tipoEstado'] == 'I'){
                                $estiloColegiado = ' style="color: green; font-size: large;"';
                            } else {
                                $estiloColegiado = ' style="color: red;"';
                            }
                            ?>
                            <b>Estado Matricular</b>
                            <input class="form-control" type="text" <?php echo $estiloColegiado; ?> value="<?php if ($colegiado['tipoEstado'] == 'F') { echo 'Fallecido'; } else { if ($colegiado['tipoEstado'] == 'J') { echo 'Jubilado'; } else { echo $colegiadoLogic->obtenerDetalleTipoEstado($colegiado['tipoEstado']); }} ?>" readonly=""/>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $aJubFal = array('J', 'F');
                            if (!in_array($colegiado['tipoEstado'], $aJubFal)){
                            ?>
                                <b>Estado con Tesorer&iacute;a</b>
                                <?php
                                $permiteTramite = TRUE;
                                //obtengo el estado actual con tesoreria
                                $resEstadoTeso = $colegiadoDeudaAnualLogic->estadoTesoreriaPorColegiado($idColegiado, $periodoActual);
                                if ($resEstadoTeso['estado']){
                                    $codigoEstadoTesoreria = $resEstadoTeso['codigoDeudor'];
                                    $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($codigoEstadoTesoreria);
                                    if ($resEstadoTesoreria['estado']){
                                        $estadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                                    } else {
                                        $estadoTesoreria = $resEstadoTesoreria['mensaje'];
                                    }
                                } else {
                                    $estadoTesoreria = $resEstadoTeso['mensaje'];
                                }

                                if ($codigoEstadoTesoreria == 0){
                                    $estiloTesoreria = ' style="color: green; font-size: large;"';
                                } else {
                                    $estiloTesoreria = ' style="color: red;"';
                                    $permiteTramite = FALSE;
                                }
                                ?>
                                <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> value="<?php echo $estadoTesoreria  ?>" readonly=""/>
                        <?php
                        } else {
                            //obtengo la fecha de la cancelacion
                            $resMovimiento = $colegiadoMovimientoLogic->obtenerMovimientoMatricular($idColegiado, $colegiado['estado']);
                            if ($resMovimiento['estado']) {
                                $movimiento = $resMovimiento['datos'];
                                $fechaCancelacion = cambiarFechaFormatoParaMostrar($movimiento['fechaDesde']);
                            } else {
                                $fechaCancelacion = $resMovimiento['mensaje'];
                            }
                        ?>
                            Fecha de la cancelaci&oacute;n
                            <input class="form-control" type="text" <?php echo $estiloColegiado; ?> value="<?php echo $fechaCancelacion  ?>" readonly=""/>
                        <?php
                        }
                        ?>
                        </div>
                        <div class="col-md-1">
                            <br>
                            <button type="submit"  class="btn btn-default" >Cambiar </button>
                        </div>
                    </div>
                </form>
                <?php
                if (!in_array($colegiado['tipoEstado'], $aJubFal) && $permiteTramite){
                    //busco las especialidades
                    $resEspecialidad = $colegiadoEspecialistaLogic->obtenerEspecialidadesPorIdColegiado($idColegiado);
                    if ($resEspecialidad['estado']) {
                    ?>
                        <div class="row">
                        <br>
                        <div class="col-md-12 text-center alert-warning"><h4><b>Especialidades otorgadas</b></h4></div>
                        <div class="col-md-12">
                        <table  id="tablaEspecialista" class="display">
                            <thead>
                                <tr>
                                    <th style="text-align: center; display: none;">Id</th>
                                    <th style="text-align: center;">Especialidad</th>
                                    <th style="text-align: center;">Fecha Especialista</th>
                                    <th style="text-align: center;">Ult. Recertificaci&oacute;n</th>
                                    <th style="text-align: center;">Otorgado por</th>
                                    <th style="text-align: center;">Recertificación</th>
                                    <th style="text-align: center;">Jerarquizado</th>
                                    <th style="text-align: center;">Consultor</th>
                                    <th style="text-align: center;">Actualizaciòn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($resEspecialidad['datos'] as $dato){
                                    $idColegiadoEspecialista = $dato['idColegiadoEspecialista'];
                                    $idEspecialidad = $dato['idEspecialidad'];
                                    $fechaCarga= cambiarFechaFormatoParaMostrar($dato['fechaCarga']);
                                    $fechaEspecialista = cambiarFechaFormatoParaMostrar($dato['fechaEspecialista']);
                                    $fechaRecertificacion = cambiarFechaFormatoParaMostrar($dato['fechaRecertificacion']);
                                    $distritoOrigen = $dato['distritoOrigen'];
                                    $fechaVencimiento = $dato['fechaVencimiento'];
                                    $tipoespecialista = $dato['tipoespecialista'];
                                    $nombreEspecialidad = $dato['nombreEspecialidad'];
                                    $codigoEspecialidad = $dato['codigoEspecialidad'];
                                    $codigoEspecialista = $dato['codigoEspecialista'];
                                    $idColegiadoEspecialistaActualizacion = $dato['idColegiadoEspecialistaActualizacion'];
                                    $idColegiadoEspecialistaOrigen = $dato['idColegiadoEspecialistaOrigen'];
                                    //obtengo la fecha de jerarquizado
                                    if ($distritoOrigen <> "NACIÓN") {
                                        $resJerarquizado = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'J');
                                        if ($resJerarquizado['estado']){
                                            $fechaJerarquizado = cambiarFechaFormatoParaMostrar($resJerarquizado['fecha']);
                                        } else {
                                            $fechaJerarquizado = NULL;
                                        }
                                        //obtengo la fecha de consultor
                                        $resConsultor = $colegiadoEspecialistaLogic->obtenerFechaJerarquizadoConsultor($idColegiadoEspecialista, 'C');
                                        if ($resConsultor['estado']){
                                            $fechaConsultor = cambiarFechaFormatoParaMostrar($resConsultor['fecha']);
                                        } else {
                                            $fechaConsultor = NULL;
                                        }
                                    } else {
                                        $fechaJerarquizado = NULL;
                                        $fechaConsultor = NULL;
                                    }
                                    ?>
                                    <tr>
                                        <td style="display: none"><?php echo $idColegiadoEspecialista;?></td>
                                        <td style="text-align: left;"><?php echo $nombreEspecialidad;?></td>
                                        <td style="text-align: center;"><?php echo $fechaEspecialista;?></td>
                                        <td style="text-align: center;"><?php echo $fechaRecertificacion;?></td>
                                        <td style="text-align: center;"><?php echo $distritoOrigen;?></td>
                                        <td style="text-align: center;">
                                            <?php
                                            //si la especialidad tiene actualizacion por UNLP, se debe tomar la fecha de recertificacion sobre la fecha de vencimiento de la nueva especialidad. En caso contrario se usa la fecha de vencimiento de la especialidad
                                            if (!isset($idColegiadoEspecialistaActualizacion)) {
                                                $permiteJerarquizado = FALSE;
                                                $permiteConsultor = FALSE;
                                                if (isset($fechaVencimiento) && $fechaVencimiento <> "0000-00-00") {
                                                    $fechaVencimiento = cambiarFechaFormatoParaMostrar($fechaVencimiento);
                                                    $botonRecertificar = "Debe "; 
                                                    $botonRecertificarClass = "btn btn-success";
                                                } else {
                                                    $permiteJerarquizado = TRUE;
                                                    $botonRecertificar = "Opta ";
                                                    $botonRecertificarClass = "btn btn-warning";
                                                    //echo 'SIN CADUCIDAD';
                                                }
                                                if ($codigoEspecialista <> "N") {
                                                    $botonRecertificar .= "Recertificar";
                                                } else {
                                                    $botonRecertificar .= "Renovar";
                                                }
                                                $resExpedienteIngresado = $mesaEntradaEspecialistaLogic->expedienteIngresadoPendiente($idColegiado, $idEspecialidad, 'R');
                                                if ($resExpedienteIngresado['estado']) {
                                                    $permiteJerarquizado = TRUE;
                                                ?>
                                                    Exp.Nº <b><?php echo $resExpedienteIngresado['numeroExpediente'].'/'.$resExpedienteIngresado['anioExpediente']; ?></b>
                                                <?php
                                                } else {
                                                ?>
                                                    <form  method="POST" action="especialidades_expedientes_alta.php?accion=<?php echo $accion; ?>" onclick="return confirmar()">
                                                        <button type="submit" class="<?php echo $botonRecertificarClass; ?>" name='alta' id='name'><?php echo $botonRecertificar; ?></button>
                                                        <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                                        <input type="hidden" id="idEspecialidad" name="idEspecialidad" value="<?php echo $idEspecialidad; ?>">
                                                        <input type="hidden" id="idTipoMovimiento" name="idTipoMovimiento" value="<?php echo $idEstadoMatricular; ?>">
                                                        <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $codigoEstadoTesoreria; ?>">
                                                        <input type="hidden" id="idColegiadoEspecialista" name="idColegiadoEspecialista" value="<?php echo $idColegiadoEspecialista; ?>">
                                                        <input type="hidden" id="tipo" name="tipo" value="R">
                                                    </form>
                                                <?php
                                                }
                                                $permiteJerarquizado = TRUE;
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php 
                                            if (!isset($idColegiadoEspecialistaActualizacion)) {
                                                if (!isset($fechaJerarquizado) && $distritoOrigen <> "NACIÓN") {
                                                    if (!$permiteJerarquizado) {
                                                        //if ($fechaVencimiento < date('Y-m-d')) {
                                                        echo 'DEBE RECERTIFICAR';
                                                    } else {
                                                        $fechaPermitida = sumarRestarSobreFecha(cambiarFechaaformatoBD($fechaEspecialista), 5, 'year', '+');
                                                        if ($fechaPermitida <= date('Y-m-d')) {
                                                            //verifica que ya no haya ingresado un expediente por la recertificacion
                                                            $resExpedienteIngresado = $mesaEntradaEspecialistaLogic->expedienteIngresadoPendiente($idColegiado, $idEspecialidad, 'J');
                                                            if ($resExpedienteIngresado['estado']) {
                                                                $permiteConsultor = TRUE;
                                                            ?>
                                                                Exp.Nº <b><?php echo $resExpedienteIngresado['numeroExpediente'].'/'.$resExpedienteIngresado['anioExpediente']; ?></b>
                                                            <?php
                                                            } else {
                                                            ?>
                                                                <form  method="POST" action="especialidades_expedientes_alta.php?accion=<?php echo $accion; ?>" onclick="return confirmar()">
                                                                    <button type="submit" class="btn btn-success" name='alta' id='name'>Solicitar </button>
                                                                    <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                                                    <input type="hidden" id="idEspecialidad" name="idEspecialidad" value="<?php echo $idEspecialidad; ?>">
                                                                    <input type="hidden" id="idTipoMovimiento" name="idTipoMovimiento" value="<?php echo $idEstadoMatricular; ?>">
                                                                    <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $codigoEstadoTesoreria; ?>">
                                                                    <input type="hidden" id="idColegiadoEspecialista" name="idColegiadoEspecialista" value="<?php echo $idColegiadoEspecialista; ?>">
                                                                    <input type="hidden" id="tipo" name="tipo" value="J">
                                                                </form>
                                                            <?php
                                                            }
                                                        } else {
                                                            echo 'SIN CONDICIONES';
                                                        }
                                                    }
                                                } else {
                                                    $permiteConsultor = TRUE;
                                                    echo $fechaJerarquizado;
                                                }
                                            } else {
                                                $fechaPermitida = sumarRestarSobreFecha(cambiarFechaaformatoBD($fechaEspecialista), 5, 'year', '+');
                                                if ($fechaPermitida <= date('Y-m-d')) {
                                                    //verifica que ya no haya ingresado un expediente por la recertificacion
                                                    $resExpedienteIngresado = $mesaEntradaEspecialistaLogic->expedienteIngresadoPendiente($idColegiado, $idEspecialidad, 'J');
                                                    if ($resExpedienteIngresado['estado']) {
                                                        $permiteConsultor = TRUE;
                                                    ?>
                                                        Exp.Nº <b><?php echo $resExpedienteIngresado['numeroExpediente'].'/'.$resExpedienteIngresado['anioExpediente']; ?></b>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <form  method="POST" action="especialidades_expedientes_alta.php?accion=<?php echo $accion; ?>" onclick="return confirmar()">
                                                            <button type="submit" class="btn btn-success" name='alta' id='name'>Solicitar </button>
                                                            <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                                            <input type="hidden" id="idEspecialidad" name="idEspecialidad" value="<?php echo $idEspecialidad; ?>">
                                                            <input type="hidden" id="idTipoMovimiento" name="idTipoMovimiento" value="<?php echo $idEstadoMatricular; ?>">
                                                            <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $codigoEstadoTesoreria; ?>">
                                                            <input type="hidden" id="idColegiadoEspecialista" name="idColegiadoEspecialista" value="<?php echo $idColegiadoEspecialista; ?>">
                                                            <input type="hidden" id="tipo" name="tipo" value="J">
                                                        </form>
                                                    <?php
                                                    }
                                                } else {
                                                    echo 'SIN CONDICIONES';
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php 
                                            if (isset($fechaConsultor)) {
                                                echo $fechaConsultor;
                                            } else {
                                                if (!isset($idColegiadoEspecialistaActualizacion)) {
                                                    if ($permiteConsultor) {
                                                        $resExpedienteIngresado = $mesaEntradaEspecialistaLogic->expedienteIngresadoPendiente($idColegiado, $idEspecialidad, 'C');
                                                        if ($resExpedienteIngresado['estado']) {
                                                            $permiteConsultor = TRUE;
                                                        ?>
                                                            Exp.Nº <b><?php echo $resExpedienteIngresado['numeroExpediente'].'/'.$resExpedienteIngresado['anioExpediente']; ?></b>
                                                    <?php
                                                    } else {
                                                        $fechaPermitida = sumarRestarSobreFecha(cambiarFechaaformatoBD($fechaEspecialista), 15, 'year', '+');
                                                        if ($fechaPermitida <= date('Y-m-d')) {
                                                        ?>
                                                            <form  method="POST" action="especialidades_expedientes_alta.php?accion=<?php echo $accion; ?>" onclick="return confirmar()">
                                                                <button type="submit" class="btn btn-success" name='alta' id='name'>Solicitar </button>
                                                                <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                                                <input type="hidden" id="idEspecialidad" name="idEspecialidad" value="<?php echo $idEspecialidad; ?>">
                                                                <input type="hidden" id="idTipoMovimiento" name="idTipoMovimiento" value="<?php echo $idEstadoMatricular; ?>">
                                                                <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $codigoEstadoTesoreria; ?>">
                                                                <input type="hidden" id="idColegiadoEspecialista" name="idColegiadoEspecialista" value="<?php echo $idColegiadoEspecialista; ?>">
                                                                <input type="hidden" id="tipo" name="tipo" value="C">
                                                            </form>
                                                        <?php
                                                        } else {
                                                            if ($distritoOrigen <> "NACIÓN") {
                                                                echo 'FALTA TIEMPO';
                                                            }
                                                        }
                                                    }
                                                    } else {
                                                        echo 'SIN CONDICIONES';
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php
                                            if (!isset($idColegiadoEspecialistaActualizacion)) {
                                            ?>
                                                <form  method="POST" action="especialidades_expedientes_alta.php?accion=<?php echo $accion; ?>" onclick="return confirmar()">
                                                    <button type="submit" class="btn btn-success" name='alta' id='name'>Actualiza </button>
                                                    <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                                    <input type="hidden" id="idEspecialidad" name="idEspecialidad" value="<?php echo $idEspecialidad; ?>">
                                                    <input type="hidden" id="idTipoMovimiento" name="idTipoMovimiento" value="<?php echo $idEstadoMatricular; ?>">
                                                    <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $codigoEstadoTesoreria; ?>">
                                                    <input type="hidden" id="tipo" name="tipo" value="A">
                                                </form>
                                            <?php 
                                            } else {
                                                echo 'ACTUALIZADO';
                                            }
                                            ?>
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
                    }
                    
                    if ($colegiado['tipoEstado'] == 'A' || $colegiado['tipoEstado'] == 'I') {
                    ?>
                        <br>
                        <div class="col-md-12">
                            <form  method="POST" action="especialidades_expedientes_alta.php?accion=1">
                                <button type="submit" class="btn btn-info" name='volver' id='name'>Nueva Especialidad </button>
                                <input type="hidden" id="idColegiado" name="idColegiado" value="<?php echo $idColegiado; ?>">
                                <input type="hidden" id="estadoTesoreria" name="estadoTesoreria" value="<?php echo $codigoEstadoTesoreria; ?>">
                                <input type="hidden" id="tipo" name="tipo" value="A">
                           </form>
                        </div>  
                    <?php
                    } else {
                    ?>                    
                        <div class="row">
                            <br>
                            <div class="col-md-12 text-center alert-danger"><h4><b>NO SE PUEDE GENERAR EXPEDIENTE SEGÚN EL ESTADO MATRICULAR ACTUAL</b></h4></div>
                        </div>
                    <?php
                    }
                } else {
                    $sinEspecialidades = false;
                    if ($permiteTramite) {
                    ?>                    
                        <div class="row">
                            <br>
                            <div class="col-md-12 text-center alert-danger"><h4><b>MATRÍCULA NO ACTIVA, NO SE PUEDE GENERAR EXPEDIENTE</b></h4></div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="row">
                            <br>
                            <div class="col-md-12 text-center alert-danger"><h4><b>NO SE PUEDE GENERAR EXPEDIENTE, DEBE ABONAR LAS CUOTAS ADEUDADAS.</b></h4></div>
                        </div>
                    <?php
                    }
                }
            }
        } 
        ?>
    </div>
</div>
<!-- BOTON VOLVER -->    
<div class="col-md-12" style="text-align:right;">
    <!--<form  method="POST" action="especialidades_expedientes.php">-->
    <form  method="POST" action="mesa_entrada_listado.php">
        <button type="submit" class="btn btn-info" name='volver' id='name'>Volver </button>
   </form>
</div>  
<br>
<?php
require_once '../html/footer.php';
?>
<!--AUTOCOMLETE-->
<script src="../public/js/bootstrap3-typeahead.js"></script>    
<script language="JavaScript">
    $(function(){
        var nameIdMap = {};
        $('#apellidoNombre').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'colegiado.php?activos=SI',
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

function confirmar()
{
    if(confirm('¿Estas seguro de realizar la operación?'))
        return true;
    else
        return false;
}
</script>