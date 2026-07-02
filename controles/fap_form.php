<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../html/header.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/fap_pdo.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoDomicilioLogic.php');
$colegiadoDomicilioLogic = new colegiadoDomicilioLogic();
require_once ('../dataAccess/colegiadoContactoLogic.php');
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once ('../dataAccess/colegiadoMovimientoLogic.php');
$colegiadoMovimientoLogic = new colegiadoMovimientoLogic();
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();
require_once ('../dataAccess/tipoMovimientoLogic.php');
require_once ('../dataAccess/distritoLogic.php');
$distritoLogic = new distritoLogic();

$continua = TRUE;
$mensaje = "";
$titulo = "";
$botonConfirma = "Confirma ";
$fapLogic = new fap_pdo();
if (isset($_GET['agregar']) && $_GET['agregar'] <> "") {
    $accion = 'agregar_'.$_GET['agregar'];
    $idTipoCausa = NULL;
    $titulo = 'ALTA DE ';
    switch ($_GET['agregar']) {
        case 'consulta':
            $idSapTipoTramite = fap_pdo::TIPO_TRAMITE_CONSULTA;
            $botonConfirma .= 'consulta';
            break;
        
        case 'mediacion':
            $idSapTipoTramite = fap_pdo::TIPO_TRAMITE_MEDIACION;
            $idTipoCausa = fap_pdo::TIPO_CAUSA_MEDIACION;
            $botonConfirma .= 'mediación';
            break;

        case 'litigar_sin_gasto':
            $idSapTipoTramite = fap_pdo::TIPO_TRAMITE_LITIGAR_SIN_GASTO;
            $botonConfirma .= 'litigar sin gasto';
            break;
        
        case 'causa':
            $idSapTipoTramite = fap_pdo::TIPO_TRAMITE_CAUSA;
            $botonConfirma .= 'carátula';
            break;
        
        default:
            $continua = FALSE;
            $mensaje .= "Error de acceso, falta accion - ";
            $idSapTipoTramite = NULL;
            break;
    }
    $idSapCaratula = NULL;
} else {
    if (isset($_GET['id']) && $_GET['id'] <> "") {
        $idSapCaratula = $_GET['id'];
        $resFap = $fapLogic->obtenerSapCaratulaPorId($idSapCaratula);
        if ($resFap['estado']) {
            $fapCaratula = $resFap['datos'];
            $idColegiado = $fapCaratula['IdColegiado'];
            $idSapTipoTramite = $fapCaratula['IdSapTipoTramite'];
            $fechaIngreso = $fapCaratula['FechaIngreso'];
            $nombreCausa = $fapCaratula['NombreCausa'];
            $caratulaDefinitiva = $fapCaratula['CaratulaDefinitiva'];
            $idDepartamentoJudicial = $fapCaratula['DepartamentoJudicial'];
            $idJuzgado = $fapCaratula['IdJuzgado'];
            $idTipoCausa = $fapCaratula['IdTipoCausa'];
            $fechaHecho = $fapCaratula['FechaHecho'];
            $lugarHecho = $fapCaratula['LugarHecho'];
            $ambito = $fapCaratula['Ambito'];
            $abogados = $fapCaratula['Abogados'];
            $domicilioHecho = $fapCaratula['DomicilioHecho'];
            $telefonoHecho = $fapCaratula['TelefonoHecho'];
            $fechaNotificacion = $fapCaratula['FechaNotificacion'];
            $lugarNotificacion = $fapCaratula['LugarNotificacion'];
            $recepcion = $fapCaratula['Recepcion'];
            $especialidad_buscar = $fapCaratula['Especialidad'];
            $inscriptoDistrito = $fapCaratula['InscriptoDistrito'];
            $idSapEstado = $fapCaratula['IdSapEstado'];
            $tieneCobertura = $fapCaratula['TieneCobertura'];
            $coberturaDesde = $fapCaratula['CoberturaDesde'];
            $nombreCobertura = $fapCaratula['NombreCobertura'];
            $observaciones = $fapCaratula['Observaciones'];
            $domicilioReal = $fapCaratula['DomicilioReal'];
            $domicilioProfesional = $fapCaratula['DomicilioProfesional'];
            $mail = $fapCaratula['Mail'];
            $telefonoParticular = $fapCaratula['TelefonoParticular'];
            $celular = $fapCaratula['Celular'];
            $recepciono = $fapCaratula['Recepciono'];
            $conCedula = $fapCaratula['ConCedula'];
            $conFotoDemanda = $fapCaratula['ConFotoDemanda'];
            $idSapCondicion = $fapCaratula['IdSapCondicion'];

            //obtengo el dato si existe en alguna reunion de consejo
            $resReunion = $fapLogic->obtenerReunionConejoPorIdFap($idSapCaratula);
            if ($resReunion['estado']) {
                $reunion = $resReunion['datos'];
                $idSapConsejo = $reunion['idSapConsejo'];
                $idSapConsejoDetalle = $reunion['idSapConsejoDetalle'];
                $estadoSapConsejoDetalle = $reunion['estadoSapConsejoDetalle'];
                switch ($estadoSapConsejoDetalle) {
                    case 'P':
                        $estadoSapConsejoDetalleLeyenda = 'Pendiente';
                        break;
                    
                    case 'A':
                        $estadoSapConsejoDetalleLeyenda = 'Aprobado';
                        break;
                    
                    case 'D':
                        $estadoSapConsejoDetalleLeyenda = 'Desaprobado';
                        break;
                    
                    default:
                        $estadoSapConsejoDetalleLeyenda = 'Sin datos';
                        break;
                }
                $fechaAprobacion = $reunion['fechaAprobacion'];
                $observacionSapConsejoDetalle = $reunion['observacionSapConsejoDetalle'];
                $fechaReunion = $reunion['fechaReunion'];
                $numeroResolucion = $reunion['numeroResolucion'];
                $observacionesReunion = $reunion['observacionesReunion'];
                $estadoReunion = $reunion['estadoReunion'];
            }
            if (isset($_GET['editar'])) {
                if ($idSapTipoTramite == $fapLogic::TIPO_TRAMITE_CONSULTA) {
                    $accion = 'editar_consulta';
                } else {
                    $accion = 'editar';
                }
                $titulo = 'EDITAR CARÁTULA DE ';
                $botonConfirma .= 'cambios';
            } else {
                if (isset($_GET['anular'])) {
                    $accion = 'anular';
                    $titulo = 'ANULAR CARÁTULA DE ';
                    $botonConfirma .= 'anulación';
                } else {
                    $accion = 'consulta';
                    $titulo = 'CONSULTA CARÁTULA DE ';
                    $botonConfirma = 'Volver';
                }
            }
        } else {
            $continua = FALSE;
            $mensaje .= $resFap['mensaje'];
        }
    } else {
        $continua = FALSE;
        $mensaje .= 'Falta idSapCaratula - ';
    }
}
if ($continua) {
    switch ($idSapTipoTramite) {
        case fap_pdo::TIPO_TRAMITE_CONSULTA:
            $titulo .= 'CONSULTA';
            break;
        
        case fap_pdo::TIPO_TRAMITE_MEDIACION:
            $titulo .= 'MEDIACIÓN';
            break;

        case fap_pdo::TIPO_TRAMITE_LITIGAR_SIN_GASTO:
            $titulo .= 'LITIGAR SIN GASTO';
            break;
        
        case fap_pdo::TIPO_TRAMITE_CAUSA:
            $titulo .= 'CAUSA';
            break;
        
        default:
            $continua = FALSE;
            $mensaje .= "Error de acceso, falta accion - ";
            $idSapTipoTramite = NULL;
            break;
    }

    if (isset($_POST['mensaje'])) {
        //vino por error en la carga
        ?>
        <div class="ocultarMensaje"> 
            <p class="<?php echo $_POST['clase'];?>"><?php echo $_POST['mensaje'];?></p>  
        </div>
        <?php
        if (isset($_POST['tipoMovimiento'])) {
            $tipoMovimiento = $_POST['tipoMovimiento'];
        } else {
            $tipoMovimiento = '';
        }
        if (isset($_POST['distritoOrigen'])) {
            $distritoOrigen = $_POST['distritoOrigen'];
        } else {
            $distritoOrigen = '';
        }
        if (isset($_POST['fechaOtroDistrito'])) {
            $fechaOtroDistrito = $_POST['fechaOtroDistrito'];
        } else {
            $fechaOtroDistrito = '';
        }
        $tomo = $_POST['tomo'];
        $folio = $_POST['folio'];
        $matricula = $_POST['matricula'];
        $apellido = $_POST['apellido'];
        $nombre = $_POST['nombre'];
        $fechaMatriculacion = $_POST['fechaMatriculacion'];
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $tipoDocumento = $_POST['tipoDocumento'];
        $idPaises = $_POST['idPaises'];
        $nacionalidad_buscar = $_POST['nacionalidad_buscar'];
        $idTipoTitulo = $_POST['idTipoTitulo'];
        $fechaTitulo = $_POST['fechaTitulo'];
        $tituloDigital = $_POST['tituloDigital'];
        $idUniversidad = $_POST['idUniversidad'];
        $universidad_buscar = $_POST['universidad_buscar'];
        $numeroDocumento = $_POST['numeroDocumento'];
        $matriculaNacional = $_POST['matriculaNacional'];
        $sexo = $_POST['sexo'];
        $calle = $_POST['calle'];
        $numero = $_POST['numero'];
        $lateral = $_POST['lateral'];
        $idLocalidad = $_POST['idLocalidad'];
        $localidad_buscar = $_POST['localidad_buscar'];
        $mail = $_POST['mail'];
        $telefonoFijo = $_POST['telefonoFijo'];
        /*
        $telefonoFijoPrefijo = $_POST['telefonoFijoPrefijo'];
        $telefonoFijo1 = $_POST['telefonoFijo1'];
        $telefonoFijo2 = $_POST['telefonoFijo2'];
         * 
         */
        $telefonoMovil = $_POST['telefonoMovil'];
        $estado = $_POST['estadoMatricular'];
        $codigoPostal = $_POST['codigoPostal'];
        if (isset($_POST['piso'])) {
            $piso = $_POST['piso'];
        } else {
            $piso = NULL;
        }
        if (isset($_POST['depto'])) {
            $depto = $_POST['depto'];
        } else {
            $depto = NULL;
        }    
    } else {
        if (!isset($idSapCaratula)) {
            //si entra por alta inicializa todos los campos en null
            $fechaIngreso = date('Y-m-d');
            $nombreCausa = NULL;
            $caratulaDefinitiva = NULL;
            $idDepartamentoJudicial= NULL;
            $idJuzgado = NULL;
            if ($idSapTipoTramite == fap_pdo::TIPO_TRAMITE_MEDIACION) {
                $idTipoCausa = fap_pdo::TIPO_CAUSA_MEDIACION;
            } else {
                $idTipoCausa = NULL;
            }
            $fechaHecho = NULL;
            $lugarHecho = NULL;
            $ambito = NULL;
            $abogados = NULL;
            $domicilioHecho = NULL;
            $telefonoHecho = NULL;
            $fechaNotificacion = NULL;
            $lugarNotificacion = NULL;
            $recepcion = NULL;
            $especialidad_buscar = NULL;
            $inscriptoDistrito = NULL;
            $idSapEstado = NULL;
            $tieneCobertura = NULL;
            $coberturaDesde = NULL;
            $nombreCobertura = NULL;
            $observaciones = NULL;
            $domicilioReal = NULL;
            $domicilioProfesional = NULL;
            $mail = NULL;
            $telefonoParticular = NULL;
            $recepciono = NULL;
            $conCedula = 'S';
            $conFotoDemanda = 'N';
            $idSapCondicion = NULL;
        }
    }
    //$fechaLimite = sumarRestarSobreFecha(date('Y-m-d'), 23, 'year', '-');
    if ((!isset($_POST['idColegiado']) && !isset($_GET['idColegiado']) && !isset($idColegiado)) || isset($_GET['colegiado'])) {
        if (isset($_GET['colegiado'])) {
            $link_form = 'fap_form.php?id='.$idSapCaratula.'&editar';
        } else {
            $link_form = 'fap_form.php?agregar='.$_GET['agregar'];
        }
    ?>
        <div class="row">&nbsp;</div>
        <h4 class="alert alert-info"><b>Buscar colegiado </b></h4>
        <div class="row">
            <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="<?php echo $link_form; ?>">
                <div class="col-md-3" style="text-align: right;">
                    <label>Matr&iacute;cula o Apellido y Nombre *</label>
                </div>
                <div class="col-md-7">
                    <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" required=""/>
                    <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                </div>
                <div class="col-md-2">
                    <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                </div>
            </form>
        </div>
        <div class="row"></div>
        <div class="row">
            <div class="col-md-12">
                <?php 
                if ($accion == 'editar' || $accion == 'editar_consulta') {
                ?>
                    <a href="<?php echo $link_form; ?>" class="btn btn-info">Volver</a>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">&nbsp;</div>
        <div class="row text-center">
            <img src="../public/images/logo-transp.png" alt="Colegio de M{edicos Distrito I">
        </div>
        <div class="row">&nbsp;</div>
    <?php
    } else {
        if (isset($_POST['idColegiado']) && $_POST['idColegiado'] <> "") {
            $idColegiado = $_POST['idColegiado'];
        } else {
            if (!isset($idColegiado)) {
                $idColegiado = NULL;
                $continua = FALSE;
                $mensaje .= 'Falta idColegiado - ';
            }
        }
    ?>
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-4">
                        <h4><?php echo $titulo; ?></h4>
                    </div>
                    <?php
                    if (isset($idSapConsejoDetalle)) {
                        //si existe en alguna reunion, entonces mostramos los datos de la reunion
                        ?>
                        <div class="col-md-6">
                            <h4>En Reunión de Consejo de fecha <b><?php echo cambiarFechaFormatoParaMostrar($fechaReunion); ?></b> - Estado: <b><?php echo $estadoSapConsejoDetalleLeyenda; ?></b>
                            </h4>
                        </div>
                    <?php 
                    } else {
                        //si no esta en ninguna reunion de consejo, entonces dejo cambiar al colegiado
                        ?>
                        <div class="col-md-6 text-right">
                            <a href="fap_form.php?id=<?php echo $idSapCaratula; ?>&editar&colegiado" class="btn btn-info">Cambiar colegiado</a>
                        </div>
                        <?php 
                    }
                    ?>
                    <div class="col-md-1">
                        <a href="fap_listado.php" class="btn btn-info">Volver al listado</a>
                    </div>
                    <div class="col-md-1">
                        <?php 
                        if ($continua && ($accion == 'agregar_consulta' || isset($_GET['agregar']))) {
                        ?>
                            <a href="datosColegiadoCertificado/imprimir_legajo.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-info" target="_BLANK">Imprimir legajo</a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <?php
                if ($continua) {
                    $colegiadoLogic = new colegiadoLogic();
                    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                    if ($resColegiado['estado']) {
                        $colegiado = $resColegiado['datos'];
                        $matricula = $colegiado['matricula'];
                        $fechaMatriculacion = $colegiado['fechaMatriculacion'];
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
                        $sexo = $colegiado['sexo'];
                        switch ($sexo) {
                            case 'M':
                                $sexo = 'Masculino';
                                break;
                            
                            case 'F':
                                $sexo = 'Femenino';
                                break;
                            
                            default:
                                $sexo = '';
                                break;
                        }
                        $edad = calcular_edad($colegiado['fechaNacimiento']);
                        $edad_array = explode(' ', $edad);
                        $edad = $edad_array[0];
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
                    } else {
                        $mensaje .= "Verificar Estado Matricular (JUBILADO / FALLECIDO) - ";
                        $codigoDeudor = 0;
                        $estadoTesoreria = '';
                        $estiloTesoreria = '';
                    }
                    //fin tesoreria

                    //si es agregar, busco los datos de contacto y domicilio
                    if (substr($accion, 0, 7) == 'agregar') {
                        //datos de contacto
                        $resContactos = $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                        if ($resContactos['estado'] && isset($resContactos['datos'])) {
                            $contactos = $resContactos['datos'];
                            $mail = $contactos['email'];
                            if (isset($contactos['telefonoFijo']) && $contactos['telefonoFijo'] <> "") {
                                $telefonoParticular = trim($contactos['telefonoFijo']);
                            } else {
                                $telefonoParticular = NULL;
                            }
                            if (isset($contactos['telefonoMovil']) && $contactos['telefonoMovil'] <> "") {
                                $celular = trim($contactos['telefonoMovil']);
                            } else {
                                $celular = NULL;
                            }
                        } 
                        //fin datos de contacto
                        //domicilio real
                        $resDomicilio = $colegiadoDomicilioLogic->obtenerColegiadoDomicilioPorIdColegiado($idColegiado);
                        if ($resDomicilio['estado'] && isset($resDomicilio['datos'])) {
                            $domicilio = $resDomicilio['datos'];
                            $domicilioReal = "";
                            if ($domicilio['calle']) {
                                $domicilioReal = $domicilio['calle'];
                                if ($domicilio['numero']) {
                                    $domicilioReal .= " Nº ".$domicilio['numero'];
                                }
                                if ($domicilio['lateral']) {
                                    $domicilioReal .= " e/ ".$domicilio['lateral'];
                                }
                                if ($domicilio['piso'] && strtoupper($domicilio['piso']) != "NR") {
                                    $domicilioReal .= " Piso ".$domicilio['piso'];
                                }
                                if ($domicilio['depto'] && strtoupper($domicilio['depto']) != "NR") {
                                    $domicilioReal .= " Dto. ".$domicilio['depto'];
                                }
                            }
                            if (isset($domicilio['nombreLocalidad']) && $domicilio['nombreLocalidad'] <> "") {
                                $domicilioReal .= ' ( '.$domicilio['nombreLocalidad'].' )';
                            }
                        }
                        //fin domicilio real
                    }
                    //verifica cantidad de causas en sistema en el periodo actual
                    $cantidad_fap = $fapLogic->cantidadCausasPorPeriodoActual($idColegiado);
                    if (isset($cantidad_fap)) {
                        $continua = TRUE;
                        if ($cantidad_fap >= 3) {
                            $clase = "alert alert-warning";
                            $mensaje .= 'Ya tiene '.$cantidad_fap.' carátulas en el sistema FAP';
                            ?>
                            <div class="row">
                                <div class="col-md-12 alert alert-warning" role="alert">
                                    <span><strong><?php echo $mensaje; ?></strong></span>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        $continua = FALSE;
                        $mensaje .= 'Error al verificar cantidad de causas en FAP - ';
                    }

                    $cantidad_fap = 0;
                    $fechaDesde = PERIODO_ACTUAL.'-05-01';
                    $fechaHasta = (PERIODO_ACTUAL + 1).'04-30';
                    //$resFap = $fapLogic->obtenerCausasPorIdColegiado($idColegiado);
                    $resFap = $fapLogic->obtenerFapCaratulasPorPeriodoEstado('9999', NULL, $idColegiado, NULL);
                    if ($resFap['estado']){
                    ?>
                        <div id="fapColegiadoModal" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header alert alert-info">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Causas registradas en FAP</h4>
                                    </div>
                                    <div class="modal-body">
                                        <table style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: left;">Causa</th>
                                                    <th style="text-align: center;">Fecha Ingreso</th>
                                                    <th style="text-align: center;">Juzgado</th>
                                                    <th style="text-align: center;">Tipo trámite</th>
                                                    <th style="text-align: center;">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($resFap['datos'] as $fapCaratula) {
                                                    if ($fapCaratula['Estado'] == 'C' || $fapCaratula['Estado'] == 'D') { continue; }

                                                    if ($fapCaratula['FechaIngreso'] >= $fechaDesde && $fapCaratula['FechaIngreso'] <= $fechaHasta) {
                                                        $cantidad_fap += 1;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td style="text-align: left;"><?php echo $fapCaratula['NombreCausa'];?></td>
                                                        <td style="text-align: center;"><?php echo cambiarFechaFormatoParaMostrar($fapCaratula['FechaIngreso']);?></td>
                                                        <td style="text-align: center;"><?php echo $fapCaratula['NombreTipoCausa'].' - '.$fapCaratula['NombreJuzgado'];?></td>
                                                        <td style="text-align: center;"><?php echo $fapCaratula['NombreTipoTramite'];?></td>
                                                        <td style="text-align: center;"><?php echo $fapCaratula['NombreSapEstado'];?></td>
                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>        
                        <?php
                    }
                    $continua = TRUE;
                    if ($cantidad_fap >= 3) {
                        if ($accion <> "agregar_consulta") {
                            //$continua = FALSE;
                            $mensaje .= 'Ya tiene '.$cantidad_fap.' en el sistema FAP, debe autorizar la nueva carga';
                            ?>
                            <div class="row">
                                <div class="col-md-12 alert alert-warning" role="alert">
                                    <span><strong><?php echo $mensaje; ?></strong></span>
                                </div>
                            </div>
                        <?php
                        }
                    }
                    //fin verifica cantidad fap en el periodo
                }
                if ($continua) {
                ?>
                    
                    <div class="row">
                        <div class="col-md-1">
                            <label for="colegiado_buscar">Matrícula: </label>
                            <input class="form-control" type="text" name="matricula" id="matricula" value="<?php echo $matricula; ?>" readonly />
                        </div>
                        <div class="col-md-3">
                            <label for="colegiado_buscar">Colegiado: 
                                <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#fapColegiadoModal">Ver causas anteriores</button>
                            </label>
                            <input class="form-control" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" value="<?php echo $colegiado_buscar; ?>" readonly/>
                            <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>" />
                        </div>
                        <div class="col-md-4">
                            <label for="elEstado">Estado Matricular: 
                            <input type="hidden" name="estadoMatricular" id="estadoMatricular" value="<?php echo $estadoMatricular; ?>" />
                            <?php
                            $resMovimientos = $colegiadoMovimientoLogic->obtenerMovimientosPorIdColegiado($idColegiado);
                            if ($resMovimientos['estado']){
                            ?>
                            <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#novedadesModal">Ver movimientos matriculares</button>
                            <div id="novedadesModal" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header alert alert-info">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Movimientos matriculares</h4>
                                        </div>
                                        <div class="modal-body">
                                            <table style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: left;">Movimiento matricular</th>
                                                        <th style="text-align: center;">Fecha Desde</th>
                                                        <th style="text-align: center;">Fecha Hasta</th>
                                                        <th style="text-align: center;">Fecha Actualizaci&oacute;n</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    foreach ($resMovimientos['datos'] as $dato){
                                                        $idColegiadoMovimiento = $dato['idColegiadoMovimiento'];
                                                        $idTipoMovimietno = $dato['idTipoMovimietno'];
                                                        $fechaDesde = cambiarFechaFormatoParaMostrar($dato['fechaDesde']);
                                                        $fechaHasta = cambiarFechaFormatoParaMostrar($dato['fechaHasta']);
                                                        $distritoCambio = $dato['distritoCambio'];
                                                        $distritoOrigen = $dato['distritoOrigen'];
                                                        $idPatologia = $dato['idPatologia'];
                                                        $detalleMovimiento = $dato['detalleMovimiento'];
                                                        $nombrePatologia = $dato['nombrePatologia'];
                                                        $fechaCarga = cambiarFechaFormatoParaMostrar($dato['fechaCarga']);
                                                        ?>
                                                        <tr>
                                                            <td style="text-align: left;"><?php echo $detalleMovimiento;?></td>
                                                            <td style="text-align: center;"><?php echo $fechaDesde;?></td>
                                                            <td style="text-align: center;"><?php echo $fechaHasta;?></td>
                                                            <td style="text-align: center;"><?php echo $fechaCarga;?></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>        
                            <?php
                            }
                            ?>
                            </label>
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
                        </div>
                        <div class="col-md-2">
                            <label for="estadoTesoreria">Estado con Tesorería: </label>
                            <input class="form-control" type="text" <?php echo $estiloTesoreria; ?> name="estadoTesoreria" id="estadoTesoreria" value="<?php echo $estadoTesoreria  ?>" readonly=""/>
                            <input type="hidden" name="codigoDeudor" id="codigoDeudor" value="<?php echo $codigoDeudor; ?>" />
                        </div>
                        <div class="col-md-2">
                            <label for="fechaMatriculacion">Fecha matriculación: </label>
                            <input class="form-control" type="text" name="fechaMatriculacion" id="fechaMatriculacion" value="<?php echo cambiarFechaFormatoParaMostrar($fechaMatriculacion); ?>" readonly=""/>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <?php 
                    if ($accion == 'agregar_consulta' || $accion == 'editar_consulta') {
                    ?>
                        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosFap/abm_fap.php?accion=<?php echo $accion; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="nombreCausa">Tema de consulta: *</label>
                                            <input class="form-control" type="text" name="nombreCausa" id="nombreCausa" value="<?php echo $nombreCausa; ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">&nbsp;</div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="observaciones">Observaciones: *</label>
                                            <textarea class="form-control" name="observaciones" id="observaciones" rows="10" required><?php echo $observaciones; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="domicilioReal">Domicilio particular: *</label>
                                            <input class="form-control" type="text" name="domicilioReal" id="domicilioReal" value="<?php echo $domicilioReal; ?>" required >
                                        </div>
                                        <div class="col-md-3">
                                            <label for="telefonoParticular">Teléfono particular: </label>
                                            <input class="form-control" type="text" name="telefonoParticular" id="telefonoParticular" value="<?php echo $telefonoParticular; ?>" >
                                        </div>
                                        <div class="col-md-3">
                                            <label for="celular">Celular: </label>
                                            <input class="form-control" type="text" name="celular" id="celular" value="<?php echo $celular; ?>" >
                                        </div>
                                    </div>
                                    <div class="row">&nbsp;</div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="domicilioProfesional">Domicilio profesional: </label>
                                            <input class="form-control" type="text" name="domicilioProfesional" id="domicilioProfesional" value="<?php echo $domicilioProfesional; ?>" >
                                        </div>
                                        <div class="col-md-6">
                                            <label for="mail">Email *</label>
                                            <input class="form-control" type="email" name="mail" id="mail" value="<?php echo $mail; ?>" required/>
                                        </div>
                                    </div>
                                    <div class="row">&nbsp;</div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="recepciono">Recepcionó miembro de comisión: *</label>
                                            <input class="form-control" type="text" name="recepciono" id="recepciono" value="<?php echo $recepciono; ?>" required >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="submit"  class="btn btn-success"><?php echo $botonConfirma; ?></button>
                                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>">
                                    <input type="hidden" name="matricula" id="matricula" value="<?php echo $matricula; ?>">
                                    <input type="hidden" name="edad" id="edad" value="<?php echo $edad; ?>">
                                    <input type="hidden" name="sexo" id="sexo" value="<?php echo $sexo; ?>">
                                    <input type="hidden" name="idSapTipoTramite" id="idSapTipoTramite" value="<?php echo $idSapTipoTramite; ?>">
                                    <?php
                                    if ($accion == 'editar_consulta') {
                                    ?>
                                        <input type="hidden" name="idSapCaratula" id="idSapCaratula" value="<?php echo $idSapCaratula; ?>">
                                    <?php 
                                    } 
                                    ?>
                                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                                </div>
                            </div>
                        </form>
                    <?php
                    } else {
                        if ($accion == 'agregar_litigar_sin_gasto') {
                            $estado = 'G';
                        } else {
                            $estado = NULL;
                        }
                        ?>
                        <form id="formColegiado" name="formColegiado" method="POST" onSubmit="" action="datosFap/abm_fap.php?accion=<?php echo $accion; ?>">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="nombreCausa">Carátula de inicio: *</label>
                                    <input class="form-control" type="text" name="nombreCausa" id="nombreCausa" value="<?php echo $nombreCausa; ?>" required>
                                </div>
                                <!--<div class="col-md-6">
                                    <label for="caratulaDefinitiva">Carátula definitiva: </label>
                                    <input class="form-control" type="text" name="caratulaDefinitiva" id="caratulaDefinitiva" value="<?php echo $caratulaDefinitiva; ?>" >
                                </div>-->
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="idDepartamentoJudicial">Tramitada Dto.Judicial: *</label>
                                    <select class="form-control" id="idDepartamentoJudicial" name="idDepartamentoJudicial" required="">
                                        <option value="">Seleccione Departamento Judicial</option>
                                        <?php
                                        $resDepartamentoJudicial = $fapLogic->obtenerDepartamentosJudiciales();
                                        if ($resDepartamentoJudicial['estado']) {
                                            foreach ($resDepartamentoJudicial['datos'] as $row) {
                                            ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($idDepartamentoJudicial == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                            <?php
                                            }
                                        } else {
                                            echo $resDepartamentoJudicial['mensaje'];
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="idJuzgado">Juzgado: *</label>
                                    <select class="form-control" id="idJuzgado" name="idJuzgado" required="">
                                        <option value="">Seleccione Juzgado</option>
                                        <?php
                                        $resJuzgados = $fapLogic->obtenerJuzgados();
                                        if ($resJuzgados['estado']) {
                                            foreach ($resJuzgados['datos'] as $row) {
                                            ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($idJuzgado == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                            <?php
                                            }
                                        } else {
                                            echo $resJuzgados['mensaje'];
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php 
                                if ($idSapTipoTramite == fap_pdo::TIPO_TRAMITE_MEDIACION) {
                                ?> 
                                    <input class="form-control" type="hidden" name="idTipoCausa" id="idTipoCausa" value="<?php echo $idTipoCausa; ?>">
                                <?php
                                } else { ?> 
                                    <div class="col-md-2">
                                        <label for="idTipoCausa">Tipo Juzgado/Causa: *</label>
                                            <select class="form-control" id="idTipoCausa" name="idTipoCausa" required="" >
                                                <option value="">Seleccione Tipo</option>
                                                <?php
                                                $resTiposCausa = $fapLogic->obtenerTiposCausa();
                                                if ($resTiposCausa['estado']) {
                                                    foreach ($resTiposCausa['datos'] as $row) {
                                                    ?>
                                                        <option value="<?php echo $row['id'] ?>" <?php if($idTipoCausa == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                                    <?php
                                                    }
                                                } else {
                                                    echo $resTiposCausa['mensaje'];
                                                }
                                                ?>
                                            </select>
                                    </div>
                                <?php 
                                }
                                ?>
                                <div class="col-md-2">
                                    <label for="estado">Estado: *</label>
                                    <select class="form-control" id="idSapEstado" name="idSapEstado" required="">
                                        <option value="">Seleccione Estado</option>
                                        <?php
                                        $resEstados = $fapLogic->obtenerEstadosFap();
                                        if ($resEstados['estado']) {
                                            foreach ($resEstados['datos'] as $row) {
                                            ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($idSapEstado == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                            <?php
                                            }
                                        } else {
                                            echo $resEstados['mensaje'];
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="fechaHecho">Fecha del hecho *: </label>
                                    <input class="form-control" type="date" name="fechaHecho" id="fechaHecho" value="<?php echo $fechaHecho; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="lugarHecho">Lugar del hecho: *</label>
                                    <input class="form-control" type="text" name="lugarHecho" id="lugarHecho" value="<?php echo $lugarHecho; ?>" >
                                </div>
                                <div class="col-md-2">
                                    <label class="control-label">Ámbito: *</label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="ambito" value="1" <?php if ($ambito == '1') { ?> checked="" <?php } ?>>Público</label>
                                    <label class="radio-inline"><input type="radio" name="ambito" value="2" <?php if ($ambito == '2') { ?> checked="" <?php } ?>>Privado</label>
                                </div>
                                <div class="col-md-3">
                                    <label for="domicilioHecho">Domicilio del hecho: </label>
                                    <input class="form-control" type="text" name="domicilioHecho" id="domicilioHecho" value="<?php echo $domicilioHecho; ?>" >
                                </div>
                                <div class="col-md-2">
                                    <label for="telefonoHecho">Teléfono del hecho: </label>
                                    <input class="form-control" type="text" name="telefonoHecho" id="telefonoHecho" value="<?php echo $telefonoHecho; ?>" >
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="fechaNotificacion">Fecha notificación *: </label>
                                    <input class="form-control" type="date" name="fechaNotificacion" id="fechaNotificacion" value="<?php echo $fechaNotificacion; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="lugarNotificacion">Lugar notificación: *</label>
                                    <input class="form-control" type="text" name="lugarNotificacion" id="lugarNotificacion" value="<?php echo $lugarNotificacion; ?>" >
                                </div>
                                <div class="col-md-2">
                                    <label class="control-label">Recepción: *</label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="recepcion" id="recepcion_personal" value="P" <?php if ($recepcion == 'P') { ?> checked="" <?php } ?>>Personal</label>
                                    <label class="radio-inline"><input type="radio" name="recepcion" id="recepcion_familiar" value="F" <?php if ($recepcion == 'F') { ?> checked="" <?php } ?>>Familiar</label>
                                </div>
                                <div class="col-md-3">
                                    <label for="especialidad_buscar">Especialidad que origina la demanda: </label>
                                    <input class="form-control" type="text" name="especialidad_buscar" id="especialidad_buscar" value="<?php echo $especialidad_buscar; ?>" />
                                </div>
                                <div class="col-md-2">
                                    <label for="idSapCondicion">Condición: *</label>
                                    <select class="form-control" id="idSapCondicion" name="idSapCondicion" required="">
                                        <option value="">Seleccione condición</option>
                                        <?php
                                        $resCondiciones = $fapLogic->obtenerCondicionsFap();
                                        if ($resCondiciones['estado']) {
                                            foreach ($resCondiciones['datos'] as $row) {
                                                ?>
                                                <option value="<?php echo $row['id'] ?>" <?php if($idSapCondicion == $row['id']) { ?> selected <?php } ?>><?php echo $row['nombre'] ?></option>
                                            <?php
                                            }
                                        } else {
                                            echo $resCondiciones['mensaje'];
                                        }
                                        ?>
                                    </select>
                                <!--
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="condicion" id="condicion_regular" value="Regular" <?php if ($condicion == 'Regular') { ?> checked="" <?php } ?>>Regular</label>
                                    <label class="radio-inline"><input type="radio" name="condicion" id="condicion_irregular" value="Irregular" <?php if ($condicion == 'Irregular') { ?> checked="" <?php } ?>>Irregular</label>
                                -->
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <!--
                                <div class="col-md-2">
                                    <label for="litigioSinGasto">Beneficio litigar sin gasto *: </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="litigioSinGasto" id="litigioSinGasto" value="S" <?php if ($litigioSinGasto == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="litigioSinGasto" id="litigioSinGasto" value="N" <?php if ($litigioSinGasto == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                -->
                                <div class="col-md-1">
                                    <label class="control-label">Con cobertura </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="tieneCobertura" value="S" <?php if ($tieneCobertura == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="tieneCobertura" value="N" <?php if ($tieneCobertura == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                <div class="col-md-3">
                                    <label for="nombreCobertura">Nombre cobertura: </label>
                                    <input class="form-control" type="text" name="nombreCobertura" id="nombreCobertura" value="<?php echo $nombreCobertura; ?>" placeholder="Ingrese compañia de seguro" >
                                </div>
                                <div class="col-md-2">
                                    <label for="coberturaDesde">Cobertura desde: </label>
                                    <input class="form-control" type="text" name="coberturaDesde" id="coberturaDesde" value="<?php echo $coberturaDesde; ?>" placeholder="Ingrese fecha desde cobertura">
                                </div>
                                <div class="col-md-2">
                                    <label for="inscriptoDistrito">Distrito de defensa: *</label>
                                    <select class="form-control" id="inscriptoDistrito" name="inscriptoDistrito" required="">
                                        <option value="">Seleccione Distrito</option>
                                            <?php
                                            $resDistritos = $distritoLogic->obtenerDistritos();
                                            if ($resDistritos['estado']) {
                                                foreach ($resDistritos['datos'] as $row) {
                                                ?>
                                                    <option value="<?php echo $row['distrito'] ?>" <?php if($inscriptoDistrito == $row['distrito']) { ?> selected <?php } ?>><?php echo 'Distrito '.$row['romano'] ?></option>
                                                <?php
                                                }
                                            } else {
                                                echo $resEstados['mensaje'];
                                            }
                                            ?>
                                        </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="abogados">Abogados: </label>
                                    <input class="form-control" type="text" name="abogados" id="abogados" value="<?php echo $abogados; ?>" >
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="domicilioReal">Domicilio particular: *</label>
                                    <input class="form-control" type="text" name="domicilioReal" id="domicilioReal" value="<?php echo $domicilioReal; ?>" required >
                                </div>
                                <div class="col-md-1">
                                    <label for="telefonoParticular">Teléfono: </label>
                                    <input class="form-control" type="text" name="telefonoParticular" id="telefonoParticular" value="<?php echo $telefonoParticular; ?>" >
                                </div>
                                <div class="col-md-1">
                                    <label for="celular">Celular: </label>
                                    <input class="form-control" type="text" name="celular" id="celular" value="<?php echo $celular; ?>" >
                                </div>
                                <div class="col-md-3">
                                    <label for="domicilioProfesional">Domicilio profesional: </label>
                                    <input class="form-control" type="text" name="domicilioProfesional" id="domicilioProfesional" value="<?php echo $domicilioProfesional; ?>" >
                                </div>
                                <div class="col-md-3">
                                    <label for="mail">Email *</label>
                                    <input class="form-control" type="email" name="mail" id="mail" value="<?php echo $mail; ?>" required/>
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-1">
                                    <label class="control-label">Cédula: </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="conCedula" value="S" <?php if ($conCedula == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="conCedula" value="N" <?php if ($conCedula == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                <div class="col-md-2">
                                    <label class="control-label">Fotocopia de la demanda: </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="conFotoDemanda" value="S" <?php if ($conFotoDemanda == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="conFotoDemanda" value="N" <?php if ($conFotoDemanda == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="recepciono">Recepcionó miembro de comisión: *</label>
                                    <input class="form-control" type="text" name="recepciono" id="recepciono" value="<?php echo $recepciono; ?>" required >
                                </div>
                                <?php
                                /*
                                <div class="col-md-3">
                                    <label class="control-label">Fotocopia HC (legible y sin abreviaturas): </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="conFotoHC" value="S" <?php if ($conFotoHC == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="conFotoHC" value="N" <?php if ($conFotoHC == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label">Fotocopia ficha de consultorio legible: </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="conFotoFicha" value="S" <?php if ($conFotoFicha == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="conFotoFicha" value="N" <?php if ($conFotoFicha == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                <div class="col-md-1">
                                    <label class="control-label">Nota detallada: </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="notaDetalle" value="S" <?php if ($notaDetalle == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="notaDetalle" value="N" <?php if ($notaDetalle == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                <div class="col-md-2">
                                    <label class="control-label">Otros elementos útiles: </label>
                                    <br>
                                    <label class="radio-inline"><input type="radio" name="conOtros" value="S" <?php if ($conOtros == 'S') { ?> checked="" <?php } ?>>Si</label>
                                    <label class="radio-inline"><input type="radio" name="conOtros" value="N" <?php if ($conOtros == 'N') { ?> checked="" <?php } ?>>No</label>
                                </div>
                                */
                                ?>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="observaciones">Descripción del caso: *</label>
                                    <textarea class="form-control" name="observaciones" id="observaciones" rows="3" required><?php echo $observaciones; ?></textarea>
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="submit"  class="btn btn-success"><?php echo $botonConfirma; ?></button>
                                    <?php 
                                    if (isset($idSapCaratula)) {
                                    ?>
                                        <input type="hidden" name="idSapCaratula" id="idSapCaratula" value="<?php echo $idSapCaratula; ?>">
                                    <?php
                                    }
                                    ?>
                                    <input type="hidden" name="idColegiado" id="idColegiado" value="<?php echo $idColegiado; ?>">
                                    <input type="hidden" name="matricula" id="matricula" value="<?php echo $matricula; ?>">
                                    <input type="hidden" name="edad" id="edad" value="<?php echo $edad; ?>">
                                    <input type="hidden" name="sexo" id="sexo" value="<?php echo $sexo; ?>">
                                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                                    <input type="hidden" name="idSapTipoTramite" id="idSapTipoTramite" value="<?php echo $idSapTipoTramite; ?>">
                                </div>
                            </div>
                        </form>
                    <?php
                    }
                } else {
                ?>
                    <div class="row">&nbsp;</div>
                    <div class="col-md-12 alert alert-danger" role="alert">
                        <span><strong><?php echo $mensaje; ?></strong></span>
                    </div>    
                <?php    
                }
            }
            ?>
        </div>
    </div>
<?php
} else {
?>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="col-md-12 alert alert-danger" role="alert">
            <span><strong><?php echo $mensaje; ?></strong></span>
        </div>
    </div>
<?php    
}
require_once '../html/footer.php';
?>
<div id="colegiadoModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header alert alert-info">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modificar colegiado</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <form id="modificaColegiado" autocomplete="off" name="modificaColegiado" method="POST" action="fap_form.php?id=<?php echo $idSapCaratula; ?>&editar">
                <div class="row">
                    <div class="col-md-3" style="text-align: right;">
                        <label>Matr&iacute;cula o Apellido y Nombre *</label>
                    </div>
                    <div class="col-md-7">
                        <input class="form-control" autofocus autocomplete="OFF" type="text" name="colegiado_buscar" id="colegiado_buscar" placeholder="Ingrese Matrícula o Apellido del colegiado" required=""/>
                        <input type="hidden" name="idColegiado" id="idColegiado" required="" />
                    </div>
                    <div class="col-md-2">
                        <button type="submit"  class="btn btn-success">Confirma colegiado</button>
                    </div>
                </div>
            </form>      
        </div>          
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>        
        

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
        $('#especialidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'especialidad.php',
                    data: {query: query},
                    type: 'POST',
                    success: function (json) {
                        process(getOptionsFromJson(json.data));
                    }
                });
            },
           
            minLength: 3,
            //maxItem:15,
            /*
            updater: function (item) {
                $('#idLocalidad').val(nameIdMap[item]);
                return item;
            }
            */
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
        $('#nacionalidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'nacionalidad.php',
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
                $('#idPaises').val(nameIdMap[item]);
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
        $('#universidad_buscar').typeahead({ 
                source: function (query, process) {
                return $.ajax({
                    dataType: "json",
                    url: 'universidad.php',
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
                $('#idUniversidad').val(nameIdMap[item]);
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