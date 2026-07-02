<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
if (!isset($_GET['ingreso']) || (isset($_GET['ingreso']) && $_GET['ingreso'] != "NUEVA_MATRICULA")) {
    require_once '../html/header.php';
}
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
$colegiadoLogic = new colegiadoLogic();
require_once '../dataAccess/colegiadoDomicilioLogic.php';
require_once '../dataAccess/colegiadoContactoLogic.php';
require_once '../dataAccess/mesaEntradaLogic.php';
require_once '../dataAccess/remitenteLogic.php';
require_once '../dataAccess/tipoMovimientoLogic.php';
require_once ('../dataAccess/colegiadoDeudaAnualLogic.php');
$colegiadoDeudaAnualLogic = new colegiadoDeudaAnualLogic();

$continuar = true;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idMesaEntrada = $_GET['id'];
    if (isset($_GET['ingreso']) && ($_GET['ingreso'] == "FECHA" || $_GET['ingreso'] == "FECHA_TIPO" || $_GET['ingreso'] == "COLEGIADO" || $_GET['ingreso'] == "OTRO" || $_GET['ingreso'] == "notas") ||$_GET['ingreso'] == "NUEVA_MATRICULA") {
        $accedePor = $_GET['ingreso'];
    } else {
        $accedePor = NULL;
    }
    $continuar = TRUE;
    $mensaje = '';
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-4">
                    <h4>Imprimir hoja de ruta - Mesa Entrada N° <?php echo $idMesaEntrada; ?></h4>
                </div>
                <?php 
                $mesaEntradaLogic = new mesaEntradaLogic();
                $resMesaEntrada = $mesaEntradaLogic->obtenerMesaEntradaPorId($idMesaEntrada);
                if ($resMesaEntrada['estado']) {
                    $mesaEntrada = $resMesaEntrada['datos'];
                    $idColegiado = $mesaEntrada['idColegiado'];
                    $idRemitente = $mesaEntrada['idRemitente'];
                    $fechaIngreso = $mesaEntrada['fechaIngreso'];
                    $observaciones = $mesaEntrada['observaciones'];
                    $idTipoMesaEntrada = $mesaEntrada['idTipoMesaEntrada'];
                    $nombreUsuario = $mesaEntrada['nombreUsuario'];
                    $tipoEstado = $mesaEntrada['tipoEstado'];
                    $estadoTesoreria = $mesaEntrada['estadoTesoreria'];
                    $estadoMatricular = $mesaEntrada['estadoMatricular'];

                    //obtener el estado matricular al momento del tramite
                    if ($mesaEntrada['estadoMatricular'] <> 9) {
                        if (isset($tipoEstado) && $tipoEstado <> "") {
                            $elEstado = trim($colegiadoLogic->obtenerDetalleTipoEstado($tipoEstado));
                        } else {
                            $elEstado = "ACTIVO";
                        }
                        if (isset($elEstado) && $elEstado <> "") {
                            $elEstado .= ' - ';
                        }
                        $resTipoMovimiento = $tipoMovimientoLogic->obtenerTipoMovimientoPorId($estadoMatricular);
                        if ($resTipoMovimiento['estado']) {
                            $tipoMovimiento = $resTipoMovimiento['datos'];
                            $elEstado .= $tipoMovimiento['detalleCompleto'];
                        }
                    } else {
                        $elEstado = "";
                    }
                    //fin estado matricular

                    //obtener el estado con tesoreria al momento del tramite
                    $elEstadoTesoreria = "";
                    if (isset($estadoTesoreria)) {
                        $resEstadoTesoreria = $colegiadoDeudaAnualLogic->estadoTesoreria($estadoTesoreria);
                        if ($resEstadoTesoreria['estado']){
                            $elEstadoTesoreria = $resEstadoTesoreria['estadoTesoreria'];
                        } else {
                            $elEstadoTesoreria = $resEstadoTesoreria['mensaje'];
                        }
                    }
                    //fin estado tesoreria

                    switch ($idTipoMesaEntrada) {
                        case '1':
                            // Movimientos Matriculares
                            $titulo = "Movimientos Matriculares";
                            $php_generar = 'generar_movimientos_matriculares.php';
                            $resMesaEntradaMovimiento = $mesaEntradaLogic->obtenerMesaEntradaMovimientoPorId($idMesaEntrada);
                            if ($resMesaEntradaMovimiento['estado']) {
                                $mesaEntradaMovimiento = $resMesaEntradaMovimiento['datos'];
                                $idMesaEntradaMovimiento = $mesaEntradaMovimiento['idMesaEntradaMovimiento'];
                                $fechaMoviento = $mesaEntradaMovimiento['fechaMovimiento'];
                                $idTipoMovimiento = $mesaEntradaMovimiento['idTipoMovimiento'];
                                $nombreTipoMovimiento = $mesaEntradaMovimiento['nombreTipoMovimiento'];
                                $nombreTipoMovimientoCompleto = $mesaEntradaMovimiento['nombreTipoMovimientoCompleto'];
                                $idMotivoCancelacion = $mesaEntradaMovimiento['idMotivoCancelacion'];
                                $nombreMotivoCancelacion = $mesaEntradaMovimiento['nombreMotivoCancelacion'];
                                $distrito = $mesaEntradaMovimiento['distrito'];
                                $obraSocialJubilado = $mesaEntradaMovimiento['obraSocialJubilado'];
                                $idPatologia = $mesaEntradaMovimiento['idPatologia'];
                                $nombrePatologia = $mesaEntradaMovimiento['nombrePatologia'];
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resMesaEntradaMovimiento['mensaje'];
                                $clase = $resMesaEntradaMovimiento['clase'];
                            }
                            break;
                        
                        case '2':
                            // Especialidades
                            break;
                        
                        case '3':
                            // Notas
                            $titulo = "NOTAS Y OFICIOS";
                            $idMesaEntradaNota = NULL;
                            $resMesaEntradaNota = $mesaEntradaLogic->obtenerMesaEntradaNotaPorId($idMesaEntradaNota, $idMesaEntrada);
                            if ($resMesaEntradaNota['estado']) {
                                $mesaEntradaNota = $resMesaEntradaNota['datos'];
                                $tema = $mesaEntradaNota['tema'];
                                $incluyeMovimiento = $mesaEntradaNota['incluyeMovimiento'];
                                $estadoMesaEntradaNota = $mesaEntradaNota['estadoMesaEntradaNota'];
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resMesaEntradaNota['mensaje'];
                                $clase = $resMesaEntradaNota['clase'];
                            }
                            $php_generar = 'generar_nota_oficio.php';
                            break;
                        
                        case '4':
                            // Habilitación de Consultorio
                            $titulo = "Habilitación de Consultorio";
                            $php_generar = 'generar_habilitacion_consultorio.php';
                            break;
                        
                        case '5':
                            // Matricula J
                            $titulo = "Matrícula J";
                            $php_generar = 'generar_matricula_j.php';
                            break;
                        
                        case '7':
                            // Autoprescripción
                            $titulo = "Autoprescripción";
                            $php_generar = 'generar_autoprescripcion.php';
                            $idMesaEntradaAutoprescripcion = NULL;
                            $resAutoprescripcion = $mesaEntradaLogic->obtenerMesaEntradaAutoprescripcionPorId($idMesaEntrada);
                            if ($resAutoprescripcion['estado']) {
                                $autoprescripcion = $resAutoprescripcion['datos'];
                                $idMesaEntradaAutoprescripcion = $autoprescripcion['idMesaEntradaAutoprescripcion'];
                                $fecha = $autoprescripcion['fecha'];
                                $autorizado1 = $autoprescripcion['autorizado1'];
                                $documentoAutorizado1 = $autoprescripcion['documentoAutorizado1'];
                                $parentesco1 = $autoprescripcion['parentesco1'];
                                $autorizado2 = $autoprescripcion['autorizado2'];
                                $documentoAutorizado2 = $autoprescripcion['documentoAutorizado2'];
                                $parentesco2 = $autoprescripcion['parentesco2'];
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resAutoprescripcion['mensaje'];
                                $clase = $resAutoprescripcion['clase'];
                            }
                            break;
                        
                        case '8':
                            // Anulación de Movimiento Matricular
                            $titulo = "Anulación de Movimiento Matricular";
                            $php_generar = 'generar_anulacion_movimiento_matricular.php';
                            $resMesaEntradaMovimientoAnulado = $mesaEntradaLogic->obtenerMesaEntradaAnulacionMovimientoPorId($idMesaEntrada);
                            if ($resMesaEntradaMovimientoAnulado['estado']) {
                                $mesaEntradaMovimientoAnulado = $resMesaEntradaMovimientoAnulado['datos'];
                                $idMesaEntradaMovimientoAnulado = $mesaEntradaMovimientoAnulado['idMesaEntradaMovimientoAnulado'];
                                $idTipoMovimiento = $mesaEntradaMovimientoAnulado['idTipoMovimiento'];
                                $nombreTipoMovimiento = $mesaEntradaMovimientoAnulado['nombreTipoMovimiento'];
                                $nombreTipoMovimientoCompleto = $mesaEntradaMovimientoAnulado['nombreTipoMovimientoCompleto'];
                                $estadoTipoMovimiento = $mesaEntradaMovimientoAnulado['estadoTipoMovimiento'];
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resMesaEntradaMovimientoAnulado['mensaje'];
                                $clase = $resMesaEntradaMovimientoAnulado['clase'];
                            }
                            break;
                        
                        case '9':
                            // Denuncia de Extravío o Falsificación
                            $titulo = "Denuncia de Extravío, Falsificación o Robo";
                            $php_generar = 'generar_denuncia.php';
                            $idMesaEntradaDenuncia = NULL;
                            $resDenuncia = $mesaEntradaLogic->obtenerMesaEntradaDenunciaPorId($idMesaEntrada);
                            if ($resDenuncia['estado']) {
                                $denuncia = $resDenuncia['datos'];
                                $idMesaEntradaDenuncia = $denuncia['idMesaEntradaDenuncia'];
                                $fechaExtravio = $denuncia['fechaExtravio'];
                                $fechaDenuncia = $denuncia['fechaDenuncia'];
                                $idTipoDenuncia = $denuncia['idTipoDenuncia'];
                                $nombreTipoDenuncia = $denuncia['nombreTipoDenuncia'];
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resDenuncia['mensaje'];
                                $clase = $resDenuncia['clase'];
                            }
                            break;
                        
                        case '10':
                            // Entregas
                            $titulo = "Entregas";
                            $php_generar = 'generar_entrega.php';
                            $idMesaEntradaEntrega = NULL;
                            $resMesaEntradaEntrega = $mesaEntradaLogic->obtenerMesaEntradaEntregaPorId($idMesaEntradaEntrega, $idMesaEntrada);
                            if ($resMesaEntradaEntrega['estado']) {
                                $mesaEntradaEntrega = $resMesaEntradaEntrega['datos'];
                                $idMesaEntradaEntrega = $mesaEntradaEntrega['idMesaEntradaEntrega'];
                                $idColegiado = $mesaEntradaEntrega['idColegiado'];
                                $fechaIngreso = $mesaEntradaEntrega['fechaIngreso'];
                                $observaciones = $mesaEntradaEntrega['observaciones'];
                                $idTipoEntrega = $mesaEntradaEntrega['idTipoEntrega'];
                                $nombreTipoEntrega = $mesaEntradaEntrega['nombreTipoEntrega'];
                                $leyendaTipoEntrega = $mesaEntradaEntrega['leyendaTipoEntrega'];

                                
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resMesaEntradaEntrega['mensaje'];
                                $clase = $resMesaEntradaEntrega['clase'];
                            }
                            break;
                        
                        default:
                            // ERROR
                            $continua = FALSE;
                            $mensaje .= "ERROR: idTipoMesaEntrada inválido.";
                            break;
                    }

                    //obtengo los datos del colegiado o remitente segun corresponda
                    if (isset($idColegiado) && $idColegiado <> "") {
                        //obtenemos los datos del colegiado
                        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                        if ($resColegiado['estado'] && $resColegiado['datos']) {
                            $colegiado = $resColegiado['datos'];
                            $matricula = $colegiado['matricula'];
                            $apellido = trim($colegiado['apellido']);
                            $nombre = trim($colegiado['nombre']);
                            $nombreRemitente = $apellido.' '.$nombre;
                            $sexo = $colegiado['sexo'];

                            ?>
                            <div class="col-md-2">
                                <h4>Matrícula: <?php echo $matricula; ?></h4>
                            </div>
                            <div class="col-md-4">
                                <h4>Apellido y Nombre: <?php echo $nombreRemitente; ?></h4>
                                <!--Estado matricular al momento del ingreso: <b><?php echo $elEstado; ?></b><br>
                                Estado con Tesorería al momento del ingreso: <b><?php echo $elEstadoTesoreria; ?></b>-->
                            </div>
                        <?php
                        } else {
                            $continua = FALSE;
                            $mensaje .= $resColegiado['mensaje'];
                            $clase = $resColegiado['clase'];
                        }
                    } else {
                        if (isset($idRemitente) && $idRemitente <> "") {
                            //obtenemos los datos del colegiado
                            $remitenteLogic = new remitenteLogic();
                            $resRemitente = $remitenteLogic->obtenerRemitentePorId($idRemitente);
                            if ($resRemitente['estado']) {
                                $remitente = $resRemitente['datos'];
                                $nombreRemitente = $remitente['nombre'];
                                ?>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-4">
                                    <h4>Remitente: <?php echo $nombreRemitente; ?></h4>
                                </div>
                            <?php
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resRemitente['mensaje'];
                                $clase = $resRemitente['clase'];
                            }
                        } else {
                            $continua = FALSE;
                            $mensaje .= 'Error al acceder a los datos mesa de entradas';
                            $clase = 'alert alert-danger';
                        }
                    }
                } else {
                    $continua = FALSE;
                    $mensaje .= $resMesaEntrada['mensaje'];
                    $clase = $resMesaEntrada['clase'];
                }
                ?>
                <div class="col-md-1 text-right">
                    <?php 
                    include('mesa_entrada_volver_listado.php');
                    //<a href="mesa_entrada_listado.php" class="btn btn-info">Salir</a>
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            $pathOrigen = '';
            $hojaRutaPDF = NULL;
            if (isset($php_generar)) {
                require_once('datosMesaEntrada/'.$php_generar);        
            }
            if (isset($hojaRutaPDF)) {
            ?>
                <div class="row">
                   <embed src='data:application/pdf;base64,<?php echo $hojaRutaPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                </div> 
            <?php 
            } else {
                echo 'ERROR AL OBTENER EL RECIBO';
            }
            ?>
        </div>
    </div>
<?php            
} else {
?>
    <div class="col-md-12">
        <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
    </div>
    <a href="colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

