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
            include_once ('certificado_seguro_praxis_medica.php');
            $certificado = new certificadoSeguroPraxisMedica();
            $resCertificado = $certificado->obtenerPdfPorIdColegiado($idColegiado);
            if ($resCertificado['estado']) {
                $certificadoPDF = $resCertificado['certificadoPDF'];
                if (isset($certificadoPDF)) {
                ?>
                    <div class="row">
                       <embed src='data:application/pdf;base64,<?php echo $certificadoPDF; ?>' height="800px" width='100%' type='application/pdf'> 
                    </div> 
                <?php 
                } else {
                    $continuar = FALSE;
                    $mensaje = $resCertificado['mensaje'];
                }
                ?>
            </div>
            <?php
            } else {
                $continuar = FALSE;
                $mensaje = $resCertificado['mensaje'];
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

