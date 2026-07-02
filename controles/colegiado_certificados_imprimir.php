<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoDomicilioLogic.php';
require_once '../dataAccess/colegiadoContactoLogic.php';
$colegiadoContactoLogic = new colegiadoContactoLogic();
require_once '../dataAccess/colegiadoCertificadosLogic.php';

$continuar = true;
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCertificado = $_GET['id'];
    $continuar = TRUE;
    $mensaje = '';
    if (isset($_GET['tramites_web'])) {
        $onLine = TRUE;
    } else {
        $onLine = FALSE;
    }
    ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-2">
                    <h4>Imprimir Certificado</h4>
                </div>
                <?php 
                $continua = TRUE;
                $resCertificado = $colegiadoCertificadosLogic->obtenerCertificadoPorId($idCertificado);
                if ($resCertificado['estado']) {
                    $certificado = $resCertificado['datos'];
                    $idColegiado = $certificado['idColegiado'];
                    $enviaMail = $certificado['envioMail'];
                    $conFirma = $certificado['conFirma'];
                    $colegiadoLogic = new colegiadoLogic();
                    $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                    if ($resColegiado['estado']) {
                        $colegiado = $resColegiado['datos'];
                        $matricula = $colegiado['matricula'];
                        $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
                        ?>
                        <div class="col-md-1">
                            <h4>Matrícula: <br><b><?php echo $matricula; ?></b></h4>
                        </div>
                        <div class="col-md-3">
                            <h4>Apellido y Nombre: <br><b><?php echo $apellidoNombre; ?></b></h4>
                        </div>
                    <?php
                    } else {
                        $continua = FALSE;
                        $mensaje .= $resColegiado['mensaje'];
                    }
                } else {
                    $continua = FALSE;
                    $mensaje .= $resFap['mensaje'];
                    $clase = $resFap['clase'];
                }
                ?>
                <div class="col-md-4">
                    <?php
                    if ($conFirma == 'S') {
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
                                <form id="formCertificado" name="formCertificado" method="POST" onSubmit="" action="colegiado_certificados_mail.php?id=<?php echo $idCertificado; ?>">
                                    <div class="col-md-10">
                                        <label>Mail registrado *</label><br>
                                        <input class="form-control" type="text" name="mail" id="mail" value="<?php echo $mail; ?>" />
                                    </div>
                                    <div class="col-md-2">
                                        <br>
                                        <button type="submit"  class="btn btn-default" >Enviar mail </button>
                                        <input type="hidden" name="apellidoNombre" id="apellidoNombre" value="<?php echo $apellidoNombre; ?>" />
                                        <input type="hidden" name="sexo" id="sexo" value="<?php echo $sexo; ?>" />
                                        <input type="hidden" name="matricula" id="matricula" value="<?php echo $matricula; ?>" />
                                    </div>
                                </form>
                            <?php
                            }
                        }
                    }
                    ?>
                </div>
                <div class="col-md-1 text-right">
                    <?php 
                    if ($onLine) {
                    ?>
                        <a href="certificados_online.php" class="btn btn-info">Volver a Certificados OnLine</a>
                    <?php
                    } else {
                    ?>
                        <a href="colegiado_certificados.php?idColegiado=<?php echo $idColegiado;?>" class="btn btn-info">Volver a Certificados del colegiado</a>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php
            if ($continua) {
                $path = $certificado['path'];   
                $nombrePdf = $certificado['nombreArchivo'];

                $certificadoPDF = NULL;
                //$camino = $_SERVER['DOCUMENT_ROOT'];
                //$camino .= PATH_PDF.'/archivos/certificados/'.PERIODO_ACTUAL.'/';
                $camino = $_SERVER['DOCUMENT_ROOT'].'/'.PATH_PDF.$path;
                $nombreArchivo = $camino.$nombrePdf;
                //echo 'camino->'.$camino.'<br>';
                //echo 'nombrePdf->'.$nombrePdf.'<br>';
                if (file_exists($nombreArchivo)) {
                    //obtiene el certificado y lo guarda como base64 para mostrar
                    $pdf_content = file_get_contents($nombreArchivo);        
                    $certificadoPDF = base64_encode($pdf_content);                
                    if (isset($certificadoPDF)) {
                    ?>
                        <div class="row">
                           <embed src='data:application/pdf;base64,<?php echo $certificadoPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                        </div> 
                    <?php
                    } else {
                        echo 'ERROR AL OBTENER EL CERTIFICADO PDF';    
                    }
                } else {
                    echo 'ERROR AL OBTENER EL CERTIFICADO';
                }
            } else {
                echo $mensaje;
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
    <a href="colegiado_consulta.php" class="btn btn-primary">Volver</a>
<?php
}
include("../html/footer.php");

