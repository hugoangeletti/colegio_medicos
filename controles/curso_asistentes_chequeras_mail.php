<?php
require_once ('../dataAccess/config.php');
permisoLogueado();
require_once ('../html/head.php');
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once ('../dataAccess/colegiadoLogic.php');
require_once ('../dataAccess/envioMailDiarioLogic.php');

$continua = TRUE;
$mensaje = '';
if (isset($_POST['idCurso']) && $_POST['idCurso'] <> "") {
    $idCurso = $_POST['idCurso'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta idCurso - ";
}
if (isset($_POST['apellidoNombre']) && $_POST['apellidoNombre'] <> "") {
    $apellidoNombre = $_POST['apellidoNombre'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta apellidoNombre - ";
}
if (isset($_POST['titulo']) && $_POST['titulo'] <> "") {
    $titulo = $_POST['titulo'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta titulo - ";
}
if (isset($_POST['mail']) && $_POST['mail'] <> "") {
    $mailDestino = $_POST['mail'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta mail - ";
}
if (isset($_POST['pathArchivo']) && $_POST['pathArchivo'] <> "") {
    $pathArchivo = $_POST['pathArchivo'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta pathArchivo - ";
}
if (isset($_POST['nombreArchivo']) && $_POST['nombreArchivo'] <> "") {
    $nombreArchivo = $_POST['nombreArchivo'];
} else {
    $continua = FALSE;
    $mensaje .= "Falta nombreArchivo - ";
}
if ($continua) {
    $subCarpeta = $pathArchivo;

    //enviamos el pdf por mail si tiene contacto
    require_once '../PHPMailer/class.phpmailer.php';
    require_once '../PHPMailer/class.smtp.php';
    $detalle = "Seguro praxis medica";
    $nota = "Estimado/a <b>".$apellidoNombre."</b><br>
                    Se le envia la chequera del ".$titulo.".<br>
                    Saludamos atentamente.";
    $from = "esem@colmed1.org.ar";
    $subject = "ESEM - Envio de Chequera del Curso.";
    $destinatarioMail = $apellidoNombre;

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
    $mail->AddAttachment($subCarpeta.'/'.$nombreArchivo);
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
                <h3>Chequera de cursos de <?php echo utf8_encode($apellidoNombre); ?></h3>
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
?>
    <div class="col-md-12">
        <h4 class="alert alert-danger">ERROR AL INGRESAR -> <?php echo $mensaje; ?></h4>
    </div>
<?php
}
?>
<a href="curso_asistentes.php?id=<?php echo $idCurso; ?>" class="btn btn-primary">Volver</a>
<?php
include("../html/footer.php");
