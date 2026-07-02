<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoEspecialistaLogic.php';
$colegiadoEspecialistaLogic = new colegiadoEspecialistaLogic();
require_once '../dataAccess/resolucionesLogic.php';

$continuar = true;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    //verifica por donde ingresa a imprimir, 
    //si es por especialidades del colegiado, entonces tomo el idColegiado
    //si es por el detalle de la resolucion, entonces tomo el idResolucionDetalle
    //si viene con el get jerarquizado o consultor, , entonces tomo el idResolucionDetalle
    $inciso_e_colegio = FALSE;
    $unlp = FALSE;
    if (isset($_GET['jerarquizado']) || isset($_GET['consultor'])) {
        if (isset($_GET['jerarquizado']) && $_GET['id'] <> "NULL") {
            $idResolucionDetalle = $_GET['id'];
        } else {
            if (isset($_GET['consultor']) && $_GET['id'] <> "NULL") {
                $idResolucionDetalle = $_GET['id'];
            } else {
                $idResolucionDetalle = NULL;
            }
        }
        $idColegiadoEspecialista = NULL;
    } else {
        $pos = strpos($_SESSION['link_volver'], 'idResolucion');
        //echo 'link_volver->'.$_SESSION['link_volver'].' - pos->'.$pos.'<br>';
        if ($pos) {
            $idResolucionDetalle = $_GET['id'];
            $idColegiadoEspecialista = NULL;
            if (isset($_GET['colegio'])) {
                $inciso_e_colegio = TRUE;
            }
            if (isset($_GET['unlp'])) {
                $unlp = TRUE;
            }
        } else {
            $idColegiadoEspecialista = $_GET['id'];
            $idResolucionDetalle = NULL;
        }
    }
    //echo 'idResolucionDetalle->'.$idResolucionDetalle.' - idColegiadoEspecialista->'.$idColegiadoEspecialista.'<br>';
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-4">
                    <h4>Imprimir Título de especialista</h4>
                </div>
                <?php
                $continua = TRUE;
                if (isset($idColegiadoEspecialista)) {
                    $resEspecialista = $colegiadoEspecialistaLogic->obtenerColegiadoEspecialistaPorId($idColegiadoEspecialista);
                    if ($resEspecialista['estado']) {
                        $especialista = $resEspecialista['datos'];
                        $idColegiado = $especialista['idColegiado'];
                        $idResolucionDetalle = $especialista['idResolucionDetalle'];
                        $fechaVencimiento = $especialista['fechaVencimiento'];
                        $fechaEspecialista = $especialista['fechaEspecialista'];
                        $especialidadDetalle = $especialista['nombreEspecialidad'];
                        $fechaAprobada = $especialista['fechaEspecialista'];
                        $fechaRecertificacion = $especialista['fechaRecertificacion'];
                        $inciso = $especialista['incisoArticulo8'];
                        $colegiadoLogic = new colegiadoLogic();
                        $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                        if ($resColegiado['estado']) {
                            $colegiado = $resColegiado['datos'];
                            $matricula = $colegiado['matricula'];
                            $apellido = $colegiado['apellido'];
                            $nombre = $colegiado['nombre'];
                            $apellidoNombre = trim($apellido).' '.trim($nombre);
                            $sexo = $colegiado['sexo'];
                        } else {
                            $continua = FALSE;
                            $mensaje .= $resResDetalle['mensaje'];
                            $clase = $resColegiado['clase'];
                        }
                        $tipoEspecialista = NULL;
                        $idTipoResolucion = NULL;
                        $tipoResolucion = NULL;
                        $numeroResolucion = NULL;
                        $idResolucion = NULL;
                        $tipo = 'E';
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resEspecialista['mensaje'];
                        $clase = $resEspecialista['clase'];
                    }
                }
                if (isset($idResolucionDetalle)) {
                    $resResDetalle = $resolucionesLogic->obtenerResolucionDetallePorId($idResolucionDetalle);
                    if ($resResDetalle['estado']) {
                        $resolucionDetalle  = $resResDetalle['datos'];
                        $idResolucion = $resolucionDetalle['idResolucion'];
                        $tipo = $resolucionDetalle['tipo'];
                        if ($inciso_e_colegio && (!isset($tipo) || $tipo == "")) {
                            $tipo = 'E';
                        }
                        $especialidad = $resolucionDetalle['especialidad'];
                        $especialidadDetalle = $resolucionDetalle['especialidadDetalle'];
                        $estado = $resolucionDetalle['estado'];
                        $fechaAprobada = $resolucionDetalle['fechaAprobada'];
                        $fechaRecertificacion = $resolucionDetalle['fechaRecertificacion'];
                        $idColegiado = $resolucionDetalle['idColegiado'];
                        $matricula = $resolucionDetalle['matricula'];
                        $apellido = $resolucionDetalle['apellido'];
                        $nombre = $resolucionDetalle['nombre'];
                        $apellidoNombre = trim($apellido).' '.trim($nombre);
                        $sexo = $resolucionDetalle['sexo'];
                        $inciso = $resolucionDetalle['inciso'];
                        $tipoEspecialista = $resolucionDetalle['tipoEspecialista'];
                        $idTipoResolucion = $resolucionDetalle['idTipoResolucion'];
                        $tipoResolucion = $resolucionDetalle['tipoResolucion'];
                        $numeroResolucion = $resolucionDetalle['numeroResolucion'];

                        //datos del vencimiento de la especialidad si no es consultor
                        //if ($tipo <> 'C' && $tipo <> 'J') {
                            $resEspecialista = $colegiadoEspecialistaLogic->obtenerEspecialistaPorIdResolucionDetalle($idResolucionDetalle, $tipo);
                            //var_dump($resEspecialista);
                            if ($resEspecialista['estado']) {
                                $especialista  = $resEspecialista['datos'];
                                $fechaVencimiento = $especialista['fechaVencimiento'];
                                //if ($tipo <> 'C' && $tipo <> 'J') {
                                    $fechaEspecialista = $especialista['fechaEspecialista'];
                                //}
                                $idColegiadoEspecialista = $especialista['idColegiadoEspecialista'];
                            } else {
                                $continua = FALSE;
                                $mensaje .= $resEspecialista['mensaje'];
                                $clase = $resEspecialista['clase'];
                            }
                        /*
                        } else {
                            $fechaVencimiento = NULL;
                            $fechaEspecialista = NULL;
                            $idColegiadoEspecialista = NULL;
                        }
                        */
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resResDetalle['mensaje'];
                        $clase = $resResDetalle['clase'];
                    }
                } else {
                    //quiere decir que entro por jerarquizado o consultor y el id = NULL,
                    //buscamos por 
                }
                $php_generar = "";
                if ($continua) {
                    $imprimir = 0;
                    switch ($tipo) {
                        case 'E':
                        case 'N':
                            $php_generar = 'generar_titulo_especialista_colegio.php';
                            break;
                        
                        case 'X':
                            if ($inciso == 'e' || $inciso == 'b') {
                                if ($unlp) {
                                    $titulo = "Convenio UNLP";
                                     $php_generar = 'generar_titulo_especialista_unlp.php';
                                } else {
                                    if ($inciso_e_colegio) {
                                        $titulo = "Especialista Colegio";
                                        $php_generar = 'generar_titulo_especialista_colegio.php';
                                    } else {
                                        $titulo = "Convenio UBA";
                                        $php_generar = 'generar_titulo_especialista_uba.php';
                                    }
                                }
                            } else {
                                $titulo = "Especialista";
                                $php_generar = 'generar_titulo_especialista_colegio.php';
                            }
                            break;

                        case 'R':
                            if ($idTipoResolucion == 9) {
                                $titulo = "Recertificación Calificación Agregada";
                                $php_generar = 'generar_titulo_recertificacion_calificacion.php';
                            } else {
                                $titulo = "Recertificación Especialista";
                                if ($unlp) {
                                    $php_generar = 'generar_titulo_recertificacion_unlp.php';
                                } else {
                                    $php_generar = 'generar_titulo_recertificacion_colegio.php';
                                }
                            }
                            break;

                        case 'J':
                            $titulo = "Jerarquizado";
                            $php_generar = 'generar_titulo_especialista_jerarquizado_consultor.php';
                            break;                                    
                            
                        case 'C':
                            $titulo = "Consultor";
                            $php_generar = 'generar_titulo_especialista_jerarquizado_consultor.php';
                            break;

                        case 'A':
                            $titulo = "Calificación Agregada";
                            $php_generar = 'generar_titulo_especialista_calificacion.php';
                            break;

                        case 'U':
                            $titulo = "Convenio UNLP";
                            $php_generar = 'generar_titulo_especialista_unlp.php';
                            break;

                        default:
                            $imprimir = 1;
                            break;
                    }
                }
                    //echo 'tipo->'.$tipo.' php->'.$php_generar.' tipoResolucion->'.$tipoResolucion.' - ';
                ?>
                <div class="col-md-2">
                    <h4>Matrícula: <?php echo $matricula; ?></h4>
                </div>
                <div class="col-md-4">
                    <h4>Apellido y Nombre: <?php echo $apellidoNombre; ?></h4>
                </div>
                <div class="col-md-1 text-right">
                    <a href="<?php echo $_SESSION['link_volver']; ?>" class="btn btn-info">Salir</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            $pathOrigen = '';
            $tituloPDF = NULL;
            if (isset($php_generar)) {
                require_once('datosColegiadoEspecialista/'.$php_generar);        
                if (isset($tituloPDF)) {
                    //guardamos la fecha de emision en tituloespecialista
                    $colegiadoEspecialistaLogic->guradarFechaEmicionTitulo($idResolucionDetalle);
                    ?>
                    <div class="row">
                       <embed src='data:application/pdf;base64,<?php echo $tituloPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                    </div> 
                <?php 
                } else {
                    echo 'ERROR AL OBTENER EL TITULO';
                }
            } else {
                echo 'NO GENERO EL TIPO DE TITULO';
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
    <a href="especialidades_resoluciones.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

