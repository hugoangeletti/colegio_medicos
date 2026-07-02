<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/cobranzaLogic.php');
$cobranzaLogic = new cobranzaLogic();
require_once ('../dataAccess/lugarPagoLogic.php');
require_once ('../dataAccess/tipoPagoLogic.php');
require_once ('../dataAccess/colegiacionAnualLogic.php');

$continua = TRUE;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCobranza = $_GET['id'];
    /*
    if (isset($_POST['mensaje'])) {
        $periodo = $_POST['periodo'];
        $cuota = $_POST['cuota'];
        $fechaPago = $_POST['fechaPago'];
        $importe = $_POST['importe'];
        $recibo = $_POST['recibo'];
        $recargo = $_POST['recargo'];
        $idColegiado = $_POST['idColegiado'];
        $idAsistente = $_POST['idAsistente'];
        $codigoPago = $_POST['codigoPago'];
    } else {
        */
        $periodo = NULL;
        $cuota = NULL;
        $fechaPago = NULL;
        $importe = NULL;
        $recibo = NULL;
        $recargo = NULL;
        $matricula = NULL;
        $apellidoNombre = NULL;
        $idColegiado = NULL;
        $idAsistente = NULL;
        $codigoPago = NULL;
        $tipoPago = NULL;
    //}

    $resLote = $cobranzaLogic->obtenerLotePorId($idCobranza);
    if ($resLote['estado']) {
        $lote = $resLote['datos'];
        $fechaApertura = $lote['fechaApertura'];
        $idLugarPago = $lote['idLugarPago'];
        $lugarPago = $lote['lugarPago'];
        $tipoLote = $lote['tipoLote'];
        $numeroLoteManual = $lote['numeroLoteManual'];
    } else {
        $continua = FALSE;
        $mensaje .= $resLote['mensaje'];
    }
} else {
    $continua = FALSE;
    $mensaje .= 'Falta id - ';
}

if ($continua) {

    if (isset($idColegiado) && $idColegiado > 0) {
        //obtenero los datos del colegiado
        $colegiadoLogic = new colegiadoLogic();
        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
        if ($resColegiado['estado']) {
            $colegiado = $resColegiado['datos'];
            $matricula = $colegiado['matricula'];
            $apellidoNombre = trim($colegiado['apellido']).' '.trim($colegiado['nombre']);
        } else {
            $apellidoNombre = "ERROR: colegiado no encontrado";
        }
    } else {
        if (isset($idAsistente) && $idAsistente > 0) {
            //obtenero los datos del colegiado
            $cursos_pdo = new cursos_pdo();
            $resAsistente = $cursos_pdo->obtenerAsistentePorId($idAsistente);
            if ($resAsistente['estado']) {
                $asistente = $resAsistente['datos'];
                $apellidoNombre = $asistente['apellidoNombre'];
            } else {
                $apellidoNombre = "ERROR: asistente no encontrado";
            }
        } else {
            $apellidoNombre = "ERROR: sin datos del asistente o colegiado";
        }
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-9">
                    <h4>Carga de pagos del lote: <?php echo $numeroLoteManual.' de '.$lugarPago.' con fecha '.cambiarFechaFormatoParaMostrar($fechaApertura); ?></h4>
                </div>
                <div class="col-md-3 text-left">
                    <a href="cobranza_lotes_detalle.php?id=<?php echo $idCobranza; ?>" class="btn btn-primary">Volver al listado</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            if (isset($_POST['mensaje'])) {
            ?>
               <div class="ocultarMensaje"> 
                   <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
               </div>
            <?php
            }

            if (isset($_POST['tipoPago']) && $_POST['tipoPago'] <> ""
                && isset($_POST['recibo']) && $_POST['recibo'] <> "") {
                //obtener los datos para mostrar y cargar el pago
                $tipoPago = $_POST['tipoPago'];
                $recibo = $_POST['recibo'];

                $resDatos = $cobranzaLogic->obtenerDatosRecibo($tipoPago, $recibo);
                if ($resDatos['estado']) {
                    $datosRecibo = $resDatos['datos'];
                    $periodo = $datosRecibo['periodo'];
                    $cuota = $datosRecibo['cuota'];
                    if (isset($datosRecibo['fechaPago'])) {
                        $fechaPago = $datosRecibo['fechaPago'];
                    } else {
                        $fechaPago = date('Y-m-d');
                    }
                    $importe = $datosRecibo['importe'];
                    if (isset($datosRecibo['recargo'])) {
                        $recargo = $datosRecibo['recargo'];
                    } else {
                        $recargo = 0;
                    }
                    $matricula = $datosRecibo['matricula'];
                    $apellidoNombre = $datosRecibo['apellidoNombre'];
                    $idColegiado = $datosRecibo['idColegiado'];
                    $idAsistente = $datosRecibo['idAsistente'];
                    $codigoPago = $datosRecibo['codigoPago'];
                    $tipoPagoDetalle = $datosRecibo['tipoPagoDetalle'];
                    $codigoPagoDetalle = $datosRecibo['codigoPagoDetalle'];

                    $labelPeriodo = 'Período:';
                    if ($tipoPago == TIPO_PAGO_CUOTA_PLAN_PAGO) {
                        $labelPeriodo = 'idPlanPago:';
                    }
                    if ($tipoPago == TIPO_PAGO_CURSO) {
                        $labelPeriodo = 'IdCurso';
                    }
                    ?>
                    <form id="tomaDatos" autocomplete="off" name="tomaDatos" method="POST" action="datosCobranza/abm_cobranza_lotes_pago.php?idCobranza=<?php echo $idCobranza; ?>">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Recibo N°: </label>
                                <input class="form-control" type="text" id="recibo" name="recibo" value="<?php echo $recibo; ?>" readonly />
                            </div>
                            <div class="col-md-4">
                                <label>Tipo pago: </label>
                                <input class="form-control" type="text" id="tipoPagoDetalle" name="tipoPagoDetalle" value="<?php echo $codigoPagoDetalle.' ('.$tipoPagoDetalle.')'; ?>" readonly />
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-2">
                                <label>Matrícula: </label>
                                <input class="form-control" type="text" id="matricula" name="matricula" value="<?php echo $matricula; ?>" readonly />
                            </div>
                            <div class="col-md-4">
                                <label>Apellido y Nombres: </label>
                                <input class="form-control" type="text" id="apellidoNombre" name="apellidoNombre" value="<?php echo $apellidoNombre; ?>" readonly />
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-2">
                                <?php
                                if ($tipoPago <> TIPO_PAGO_CURSO) {
                                ?>
                                    <label><?php echo $labelPeriodo; ?></label>
                                    <input class="form-control" type="text" id="periodo" name="periodo" value="<?php echo $periodo; ?>" readonly />
                                <?php 
                                }
                                ?>
                            </div>
                            <div class="col-md-2">
                                <label>Cuota: </label>
                                <input class="form-control" type="text" id="cuota" name="cuota" value="<?php echo $cuota; ?>" readonly />
                            </div>
                            <div class="col-md-2">
                                <label>Importe: </label>
                                <input class="form-control" type="text" id="importe" name="importe" value="<?php echo $importe; ?>" readonly />
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-2">&nbsp;</div>
                            <div class="col-md-2">
                                <label>Fecha de Pago:  *</label>
                                <input type="date" aut class="form-control" id="fechaPago" name="fechaPago" value="<?php echo $fechaPago;?>" required="" autofocus>
                            </div>
                        </div>
                        <div class="row">&nbsp;</div>
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <button type="submit"  class="btn btn-primary" >Guardar</button>
                                <input type="hidden" name="accion" id="accion" value="1" />
                                <input type="hidden" name="tipoPago" id="tipoPago" value="<?php echo $tipoPago; ?>" />
                                <input type="hidden" name="codigoPago" id="codigoPago" value="<?php echo $codigoPago; ?>" />
                                <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />                                
                                <input type="hidden" name="idAsistente" id="idAsistente" value="<?php echo $idAsistente; ?>" />
                            </div>
                        </div>
                    </form>
                <?php
                } else {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="<?php echo $resDatos['clase']; ?>" role="alert">
                        <span class="<?php echo $resDatos['icono']; ?>" ></span>
                        <span><strong><?php echo $resDatos['mensaje']; ?></strong></span>
                    </div>
                <?php
                }
                ?>
                <form id="datosColegiacion" autocomplete="off" name="datosColegiacion" method="POST" action="cobranza_lotes_pago_form.php?id=<?php echo $idCobranza; ?>">
                    <div class="row">
                        <div class="col-md-1">
                            <br>
                            <button type="submit" class="btn btn-danger" >ANULAR</button>
                        </div>
                    </div>
                </form>
            <?php
            } else {
            ?>
                <form id="datosColegiacion" autocomplete="off" name="datosColegiacion" method="POST" action="cobranza_lotes_pago_form.php?id=<?php echo $idCobranza; ?>">
                    <div class="row">
                        <div class="col-md-7">
                            <label>Código pago: </label>
                            <br>
                            <label class="radio-inline">
                                <input type="radio" name="tipoPago" id="tipoPago_1" value="<?php echo TIPO_PAGO_CUOTA_COLEGIACION ?>" <?php if ($tipoPago == TIPO_PAGO_CUOTA_COLEGIACION) { echo 'checked=""'; } ?>>Cuota de colegiación
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="tipoPago" id="tipoPago_2" value="<?php echo TIPO_PAGO_CUOTA_PERIODO_ANTERIOR ?>" <?php if ($tipoPago == TIPO_PAGO_CUOTA_PERIODO_ANTERIOR) { echo 'checked=""'; } ?>>Cuota de período anterior
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="tipoPago" id="tipoPago_3" value="<?php echo TIPO_PAGO_TOTAL ?>" <?php if ($tipoPago == TIPO_PAGO_TOTAL) { echo 'checked=""'; } ?>>Pago Total
                            </label>
                            <?php 
                            if ($tipoLote <> 'MANUAL') {
                            ?>
                                <label class="radio-inline">
                                    <input type="radio" name="tipoPago" id="tipoPago_1" value="<?php echo TIPO_PAGO_CUOTA_PLAN_PAGO ?>" <?php if ($tipoPago == TIPO_PAGO_CUOTA_PLAN_PAGO) { echo 'checked=""'; } ?>>Cuota de Plan de Pagos
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="tipoPago" id="tipoPago_2" value="<?php echo TIPO_PAGO_CURSO ?>" <?php if ($tipoPago == TIPO_PAGO_CURSO) { echo 'checked=""'; } ?>>Cuota de Curso
                                </label>
                            <?php 
                            }
                            ?>
                        </div>
                        <div class="col-md-2">
                            <label>Recibo N°: </label>
                            <input class="form-control" type="number" id="recibo" name="recibo" value="<?php echo $recibo; ?>" required="" />
                        </div>
                        <div class="col-md-1">
                            <br>
                            <button type="submit" class="btn btn-primary" >Buscar</button>
                            <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>" />
                        </div>
                    </div>
                </form>
            <?php 
            }
            ?>
        </div>    
    </div>
<?php
} else {

}

require_once '../html/footer.php';
