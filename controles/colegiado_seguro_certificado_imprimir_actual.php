<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoContactoLogic.php';
require_once '../dataAccess/colegiado_seguro_Logic.php';

$continuar = true;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idColegiado = $_GET['id'];
    $continuar = TRUE;
    $mensaje = '';
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-2">
                    <h4>Imprimir certificado</h4>
                </div>
                <?php 
                //obtenemos los datos del colegiado
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                if ($resColegiado['estado'] && $resColegiado['datos']) {
                    $colegiado = $resColegiado['datos'];
                    $matricula = $colegiado['matricula'];
                    $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
                    $sexo = $colegiado['sexo'];
                    $numeroDocumento = $colegiado['numeroDocumento'];
                    $mail = NULL;
                    ?>
                    <div class="col-md-2">
                        <h4>Matrícula: <?php echo $matricula; ?></h4>
                    </div>
                    <div class="col-md-4">
                        <h4>Apellido y Nombre: <?php echo $apellidoNombre; ?></h4>
                    </div>
                    <div class="col-md-3">
                        <?php
                        $correoRechazado = $colegiadoLogic->tieneCorreoRechazado($idColegiado);
                        if ($correoRechazado){
                        ?>
                            <h5 class="alert alert-danger">Debe actualizaar el correo electrónico porque el actual fue rechazado en el último envío.</h5>
                        <?php
                        } else {
                            $resContacto =  $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                            if ($resContacto['estado']) {
                                $contacto = $resContacto['datos'];
                                $noEnviaMail = $contacto['noEnviaMail'];
                                if (!$noEnviaMail) {
                                    $mail = $contacto['email'];
                                }
                            }
                            if (isset($mail)) {
                            ?>
                                <form id="formCertificado" name="formCertificado" method="POST" onSubmit="" action="colegiado_seguro_certificado_mail.php?idColegiado=<?php echo $idColegiado; ?>">
                                    <div class="col-md-10">
                                        <label>Mail registrado *</label><br>
                                        <input class="form-control" type="text" name="mail" id="mail" value="<?php echo $mail; ?>" />
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit"  class="btn btn-default" >Enviar mail </button>
                                        <input type="hidden" name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre; ?>" />
                                        <input type="hidden" name="sexo" id="sexo" value="<?php echo $sexo; ?>" />
                                        <input type="hidden" name="matricula" id="matricula" value="<?php echo $matricula; ?>" />
                                    </div>
                                </form>
                            <?php
                            }
                        }
                        ?>
                    </div>
                <?php
                } else {
                    echo 'Error al acceder a los datos del colegiado';
                }
                ?>
                <div class="col-md-1 text-right">
                    <a href="colegiado_consulta.php?idColegiado=<?php echo $idColegiado; ?>" class="btn btn-info">Volver</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            $colegiado_seguro_Logic = new colegiado_seguro_Logic();
            $resSeguro = $colegiado_seguro_Logic->obtenerSeguroPorColegiado($idColegiado);
            if ($resSeguro['estado']) {
                $seguro = $resSeguro['datos'];
                if (isset($seguro) && sizeof($seguro) > 0) {
                    if ($seguro['origen'] == 'COLEGIO') {
                        $matricula = $seguro['matricula'];
                        /* armamaos el path donde se va a guardar el pdf */
                        $subCarpeta = $seguro['pathArchivo'];

                        $camino = $_SERVER['DOCUMENT_ROOT'];
                        $camino .= '/'.PATH_PDF.'/archivos/'.$subCarpeta.'/';
                        if (isset($seguro['nombreArchivo']) && $seguro['nombreArchivo'] <> "") {
                            $nombreArchivo = $camino.$seguro['nombreArchivo'];
                        } else {
                            $nombreArchivo = NULL;
                        }
                        $nombreArchivoCompleto = $camino.$seguro['nombreArchivoCompleto'];
                        //var_dump($seguro);
 
                        //exit;
                        //si el pdf ya existe, no lo vuelvo a generar


                        //hago una prueba por si existe mas de una vez la matricula
                        $caminoArchivos = $_SERVER['DOCUMENT_ROOT'].'/'.PATH_PDF.'/archivos/seguro/2024/';
                        $buscarArchivos = $caminoArchivos.$matricula.'*.pdf';
                        //$buscarArchivos = $caminoArchivos.'*.pdf';
                        //echo 'archivos->'.$buscarArchivos;
                        foreach(glob($buscarArchivos) as $archivos_carpeta3){
                            //$archivo_array = explode($archivos_carpeta3, '-');
                            //print_r($archivos_carpeta3);
                            //echo '<br>';
                            //if ($archivo_array[0] == $matricula) {
                                $nombreArchivoCompleto = $archivos_carpeta3;
                            //}
                        }
                        //fin

                        $certificadoPDF = NULL;
                        //echo utf8_decode('<br>nombreArchivo->'.$nombreArchivo.' - nombreArchivoCompleto->'.$nombreArchivoCompleto);
                        $nombreArchivoCompleto=utf8_decode($nombreArchivoCompleto);
                        if (file_exists($nombreArchivo) || file_exists($nombreArchivoCompleto)) {
                            $existe = FALSE;
                            if (file_exists($nombreArchivo)) {
                                //echo '<br>nombreArchivo->'.$nombreArchivo;
                                $pdf_content = file_get_contents($nombreArchivo);        
                                $existe = TRUE;
                            } else {
                                if (file_exists($nombreArchivoCompleto)) {
                                //echo '<br>nombreArchivoCompleto->'.$nombreArchivoCompleto;
                                    $pdf_content = file_get_contents($nombreArchivoCompleto);        
                                    $existe = TRUE;
                                }   
                            }
                            if ($existe) {
                                $certificadoPDF = base64_encode($pdf_content);   
                                ?>
                                <div class="col-md-9">
                                    <?php
                                    if (isset($certificadoPDF)) {
                                    ?>
                                        <div class="row">
                                           <embed src='data:application/pdf;base64,<?php echo $certificadoPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                                        </div> 
                                    <?php 
                                    } else {
                                        $continuar = FALSE;
                                        $mensaje = 'ERROR AL OBTENER EL CERTIFICADO';
                                    }
                                    ?>
                                </div>
                            <?php
                            } else {
                                $continuar = FALSE;
                                $mensaje = 'CERTIFICADO NO EXISTE, SOLICITAR A LA COMPAÑIA DE SEGURO.';
                            }
                        } else {
                            $continuar = FALSE;
                            $mensaje = 'CERTIFICADO NO EXISTE, SOLICITAR A LA COMPAÑIA DE SEGURO.';
                        }
                    } else {
                        $continuar = FALSE;
                        $mensaje = 'COBERTURA ERRONEA -> '.$seguro['origen'];
                    }
                    if (!$continuar) {
                        //si no se encuentra el pdf en el servidor lo vamos a buscar por ws de la compañia
                        $resWS = $colegiado_seguro_Logic->obtenerCertificadoPorWS($matricula, $numeroDocumento, $caminoArchivos);
                        if ($resWS['estado']) {
                            $certificadoPDF = $resWS['archivo'];
                            if (isset($certificadoPDF)) {
                                $nombreArchivoCompleto = '../archivos/seguro/2024/'.$certificadoPDF;
                                $pdf_content = file_get_contents($nombreArchivoCompleto);       
                                $certificadoPDF = base64_encode($pdf_content); 
                            ?>
                                <div class="row">
                                   <embed src='data:application/pdf;base64,<?php echo $certificadoPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                                </div> 
                            <?php 
                            } else {
                                $continuar = FALSE;
                                $mensaje = 'ERROR AL OBTENER EL CERTIFICADO';
                            }
                        }
                    }
                } else {
                    $continuar = FALSE;
                    $mensaje = 'NO SE ENCONTRARON DATOS DE COBERTURA';
                }
            } else {
                $continuar = FALSE;
                $mensaje = 'ERROR AL ACCEDER A LOS DATOS: '.$resSeguro['mensaje'];
            }

            if (!$continuar) {
            ?>
                <div class="col-md-12"><h4 class="alert alert-danger"><?php echo $mensaje; ?></h4></div>
            <?php
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

