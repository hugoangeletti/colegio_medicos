<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();

$continuar = true;
$mensaje = '';
if (isset($_GET['id']) && $_GET['id'] <> "") {
    $idCertificado = $_GET['id'];
} else {
    $continuar = FALSE;
    $mensaje .= 'Falta el idCertificado - ';
}
if (isset($_POST['mail']) && $_POST['mail'] <> "") {
    $mailDestino = $_POST['mail'];
    //$mailDestino = 'sistemas@colmed1.org.ar';
} else {
    $continuar = FALSE;
    $mensaje .= 'Falta el mail - ';
}
if ($continuar) {
    $resCertificado = $colegiadoCertificadosLogic->obtenerCertificadoPorId($idCertificado);
    if ($resCertificado['estado']) {
        $certificado = $resCertificado['datos'];
        $idColegiado = $certificado['idColegiado'];
        $path = $certificado['path'];
        $nombreArchivo = $certificado['nombreArchivo'];
        if (isset($nombreArchivo)) {
            $nombreArchivoEnviar = '..'.$path.'/'.$nombreArchivo;
            if (file_exists($nombreArchivoEnviar)) {
                $colegiadoLogic = new colegiadoLogic();
                $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
                if ($resColegiado['estado']) {
                    $colegiado = $resColegiado['datos'];
                    $matricula = $colegiado['matricula'];
                    $apellidoNombre = utf8_decode($colegiado['apellido'].' '.$colegiado['nombre']);
                    $sexo = $colegiado['sexo'];

                    $destinatario = '<b>'.$apellidoNombre.'</b> - M.P. <b>'.$matricula.'</b>';
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
                    $nota = $destinatario.', se le envía el certificado solicitado.
                            <br><br>
                            Saludos cordiales.
                            <br><br>
                            Colegio de Médicos 
                            <br>
                            de la Provincia de Buenos Aires.
                            <br>
                            Distrito I';

                    
                    //enviamos el pdf por mail si tiene contacto
                    require_once '../PHPMailer/class.phpmailer.php';
                    require_once '../PHPMailer/class.smtp.php';
                        
                    $mail = new PHPMailer();
                    $mail->IsSMTP();
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = "ssl";
                    $mail->Host = "mail.colmed1.org.ar";
                    $mail->Port = 465;                           
                    $mail->Username = MAIL_MASIVO;
                    $mail->Password = MAIL_MASIVO_PASS;
                    //$mail->From = 'noreply@colmed1.org.ar';
                    $mail->From = 'mesadeentrada@colmed1.org.ar';
                    $mail->FromName = "Colegio de Medicos. Distrito I";
                    $mail->Subject = 'Envío de Certificado solicitado';
                    $mail->AltBody = "";
                    $mail->CharSet = 'UTF-8';
                    $mail->MsgHTML($nota);
                    $mail->AddAttachment($nombreArchivoEnviar);
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
                                <h3>Certificado de <?php echo utf8_encode($apellidoNombre); ?></h3>
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
                    $mensaje = 'ERROR -> '.$resColegiado['mensaje'];
                }
            } else {
                $continuar = FALSE;
                $mensaje = 'ARCHIVO NO EXISTE '.$nombreArchivoEnviar;
            }
        } else {
            $continuar = FALSE;
            $mensaje = 'NO SE ENCONTRO CERTIFICADO';
        }
    } else {
        $continuar = FALSE;
        $mensaje = $resCertificado['mensaje'];
    }
    ?>    
    <div class="row">&nbsp;</div>
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
<a href="colegiado_certificados_imprimir.php?id=<?php echo $idCertificado; ?>" class="btn btn-primary">Volver</a>
<?php
include("../html/footer.php");
