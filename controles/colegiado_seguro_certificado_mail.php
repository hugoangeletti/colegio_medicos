<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiado_seguro_Logic.php');
require_once ('../dataAccess/envioMailDiarioLogic.php');

$continuar = true;
if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
    $idColegiado = $_GET['idColegiado'];
    $continuar = TRUE;
    $mensaje = '';

    include_once ('certificado_seguro_praxis_medica.php');
    $certificado = new certificadoSeguroPraxisMedica();
    $resCertificado = $certificado->obtenerPdfPorIdColegiado($idColegiado);
    if ($resCertificado['estado']) {
        $nombreArchivoEnviar = $resCertificado['archivo'];
        if (isset($nombreArchivoEnviar)) {
            $colegiado_seguro_Logic = new colegiado_seguro_Logic();
            $resSeguro = $colegiado_seguro_Logic->obtenerSeguroPorColegiado($idColegiado);
            if ($resSeguro['estado']) {
                $seguro = $resSeguro['datos'];
                if (isset($seguro) && sizeof($seguro) > 0) {
                    $subCarpeta = $seguro['pathArchivo'];
                    if ((isset($subCarpeta) && $subCarpeta <> "") || (date('Y-m-d') > $subCarpeta.'-07-01')) {
                        $subCarpeta = 'seguro/'.PERIODO_ACTUAL;
                    } 
                    $matricula = $seguro['matricula'];

                    //enviamos el pdf por mail si tiene contacto
                    require_once '../PHPMailer/class.phpmailer.php';
                    require_once '../PHPMailer/class.smtp.php';
                    $idEnvio = 21;
                    $resEnvio = obtenerEnvioDiarioPorId($idEnvio);
                    if ($resEnvio['estado']) {
                        $envio = $resEnvio['datos'];

                        $detalle = $envio['detalle'];
                        $notaOriginal = $envio['texto'];
                        $from = $envio['from'];
                        $subject = $envio['subject'];
                    } else {
                        $detalle = "Seguro praxis medica";
                        $notaOriginal = "{1}<br>
                                        Se le envia el Certificado del seguro de praxis medica.<br>
                                        Saludamos atentamente.";
                        $from = "noreply@colmed1.org.ar";
                        $subject = "Certificado de Seguro praxis medica";
                    }
                    $laFecha = 'La Plata, '.date('d').' de '.obtenerMes(date('m')).' de '.date('Y').'.-';
                    $nota = $notaOriginal;
                    $nota = str_replace('{0}', $laFecha, $nota);

                    $apellidoNombre = utf8_decode($_POST['apellidoNombre']);
                    $destinatario = $apellidoNombre;
                    $sexo = $_POST['sexo'];
                    $matricula = $_POST['matricula'];
                    $destinatarioMail = $apellidoNombre;

                    if ($sexo == 'M'){
                        $destinatario = 'Estimado Dr. '.$destinatario;
                    } else {
                        if ($sexo == 'F') {
                            $destinatario = 'Estimada Dra. '.$destinatario;
                        } else {
                            $destinatario = 'Estimada/o '.$destinatario;
                        }
                    }
                    $destinatario .= ' - M.P. '.$matricula;
                    $nota = str_replace('{1}', $destinatario, $nota);

                        $mailDestino = $_POST['mail'];

                        $mail = new PHPMailer();
                        $mail->IsSMTP();
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = "ssl";
                        $mail->Host = "mail.colmed1.org.ar";
                        $mail->Port = 465;                           
                        $mail->Username = MAIL_MASIVO;
                        $mail->Password = MAIL_MASIVO_PASS;
                        //$mail->From = 'noreply@colmed1.org.ar';
                        $mail->From = $from;
                        $mail->FromName = "Colegio de Medicos. Distrito I";
                        $mail->Subject = $subject;
                        $mail->AltBody = "";
                        $mail->CharSet = 'UTF-8';
                        $mail->MsgHTML($nota);
                        $mail->AddAttachment('../archivos/'.$subCarpeta.'/'.$nombreArchivoEnviar);
                        $mail->AddAddress($mailDestino, $destinatarioMail);
                        $mail->IsHTML(true);
                        if($mail->Send()) {
                            $mailEnviado = TRUE;
                        }else{
                            $mailEnviado = FALSE;
                        }
                        if ($mailEnviado) {
                            require_once ('../html/head.php');
                            require_once ('../html/encabezado.php');
                            ?>
                            <div class="col-md-12">
                                <div class="row" style="background-color: #428bca;">
                                    <div class="col-md-12"></div>
                                </div>
                            </div>
                            <div class="row">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Certificado seguro praxis medica de <?php echo utf8_encode($apellidoNombre); ?></h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-success" role="alert">
                                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                                        <span><strong>&nbsp;El mail se envió con éxito al correo: </strong><?php echo $mailDestino; ?></span>
                                    </div>        
                                </div>
                            </div>
                        <?php
                    } else {
                        $continuar = FALSE;
                        $mensaje = "<b>ERROR al enviar el mail al correo: </b>".$mailDestino." ".$mail->ErrorInfo.". <b> Vuelva a intentar más tarde.</b>";
                    }
                } else {
                    $continuar = FALSE;
                    $mensaje = 'ARCHIVO NO EXISTE '.$nombreArchivoEnviar;
                }
            } else {
                $continuar = FALSE;
                $mensaje = 'COBERTURA ERRONEA -> '.$seguro['origen'];
            }
        } else {
            $continuar = FALSE;
            $mensaje = 'NO SE ENCONTRARON DATOS';
        }
    } else {
        $continuar = FALSE;
        $mensaje = $resSeguro['mensaje'];
    }
    ?>    
    <div class="row">
        <div class="col-md-12 text-center">Cierre esta pestaña del navegador.</div>
    </div>
    <?php
    if (!$continuar) {
    ?>
        <div class="col-md-12">
            <h4 class="alert alert-danger"><?php echo $mensaje; ?></h4>
        </div>
    <?php
    }
} else {
?>
    <div class="col-md-12">
        <h4 class="alert alert-danger">ERROR AL INGRESAR</h4>
    </div>
<?php
}
?>
<a href="colegiado_seguro_certificado_imprimir.php?id=<?php echo $idColegiado; ?>" class="btn btn-primary">Volver</a>
<?php
include("../html/footer.php");
