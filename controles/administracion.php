<?php
require_once '../dataAccess/config.php';
require_once '../html/head.php';
require_once '../html/header.php';
require_once ('../dataAccess/funcionesConector.php');
require_once ('../dataAccess/colegiadoCertificadosLogic.php');
$colegiadoCertificadosLogic = new colegiadoCertificadosLogic();
require_once ('../dataAccess/envios_caja_medicosLogic.php');
require_once ('../dataAccess/usuarioLogic.php');
$usuarioLogic = new usuarioLogic();
require_once ('../dataAccess/funcionesPhp.php');
?>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-2">&nbsp;</div>
    <div class="col-md-8 text-center"><img src="../public/images/logo-transp.png" /></div>
    <div class="col-md-2">&nbsp;</div>
</div>
<div class="row">
    <div class="col-md-2">&nbsp;</div>
    <div class="col-md-8 text-center"><h1>Colegio de M&eacute;dicos Distrito I</h1></div>
    <div class="col-md-2">&nbsp;</div>
</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-3">&nbsp;</div>
    <div class="col-md-6 text-center">
        <?php 
        //se verifica ssi hay solicitudes de certificados online para mostrar el aviso
        if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 116)) {
            $cantidadSolicitudCertificadosOnLine = $colegiadoCertificadosLogic->existenSolicitudCertificadoWebPendientes();
            if (isset($cantidadSolicitudCertificadosOnLine)) {
                if ($cantidadSolicitudCertificadosOnLine > 0) {
                ?>
                    <h4>
                        <a href="certificados_online.php" class="btn btn-info btn-lg">
                            <?php
                            if ($cantidadSolicitudCertificadosOnLine == 1) {
                                echo 'Existe una solicitud de certificado Online pendiente de autorización'; 
                            } else {
                                if ($cantidadSolicitudCertificadosOnLine > 1) {
                                    echo 'Existen '.$cantidadSolicitudCertificadosOnLine.' solicitudes de certificados Online pendientes de autorización'; 
                                }
                            }
                            ?> 
                        </a>
                    </h4>
                <?php
                }
            }
        }

        //se verifica el envio de novedades a la caja de medicos, una vez al mes
        if ($usuarioLogic->verificarRolUsuario($_SESSION['user_id'], 130)) {
            $envioLogic = new enviosCajaMedicosLogic();
            $periodo = date('Y-m', strtotime("-1 month"));
            if (!$envioLogic->envioRealizadoEnElPeriodo($periodo)) {
                echo 'generar envio -> '.$periodo.'<br>';
                $fechaDesde = $periodo."-01";
                $fechaHasta = date('Y-m-t', strtotime("-1 month"));
                $resultado = $envioLogic->agregarEnvio($fechaDesde, $fechaHasta, $_SESSION['user_id']);
                if($resultado['estado']) {
                    $idEnviosCajaMedicos = $resultado['idEnviosCajaMedicos'];
                    //include_once ('datosTramites/descargar_archivos.php');
                    $generar = TRUE;
                    include_once ('datosTramites/generar_pdf_altas.php');
                    //if (isset($nombreCsv)) {
                    if (isset($nombrePdf)) {
                        //enviar el mail
                        $nombreArchivo = $nombrePdf;
                        $path_archivo = $path;
                        $destinatarioMail = 'Caja de Médicos';
                        if (ENV == 'prod') {
                            $mailDestino = MAIL_ENVIO_CAJA; 
                        } else {
                            $mailDestino = 'sistemas@colmed1.org.ar';
                        }
                        $subject = 'Novedades del período '.$periodo;
                        $path_mailer = '..';
                        $enviaPdf = TRUE;
                        $nota = '<html>
                                    <head>
                                        <title>Novedades de colegiados</title>
                                        <meta charset="UTF-8">
                                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    </head>
                                    <body>
                                        <div style="width: 800px;">
                                        <h1 style="font-family: Calibri, sans-serif; font-size: 22px; color: #00BFFF;"">
                                            Colegio de Médicos de la Pcia. de Bs.As.
                                            <br>Distrito I
                                        </h1>
                                        <p align="justify" style="margin-bottom: 0cm">Tenemos el agrado de enviarles las novedades de los colegiados en el Distrito I, correspondientes al período '.$periodo.'.</p>
                                        <p align="justify" style="margin-bottom: 0cm">Lo saludamos atentamente.</p>
                                        <p ALIGN=JUSTIFY STYLE="margin-bottom: 0cm"><SPAN STYLE="font-weight: normal"><b>Este e-mail es enviado desde una casilla autom&aacute;tica. Por favor no lo responda.</b></SPAN></P>
                                        </div>
                                    </body>
                                </html>';

                        require_once $path_mailer . '/PHPMailer/class.phpmailer.php';
                        require_once $path_mailer . '/PHPMailer/class.smtp.php';

                        $mail = new PHPMailer();
                        $mail->IsSMTP();
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = "ssl";
                        $mail->Host = "mail.colmed1.org.ar";
                        $mail->Port = 465;                           
                        $mail->Username = MAIL_MASIVO;
                        $mail->Password = MAIL_MASIVO_PASS;
                        $mail->From = MAIL_MASIVO;
                        $mail->FromName = "Colegio de Medicos. Distrito I";
                        $mail->Subject = $subject;
                        $mail->AltBody = "";
                        $mail->CharSet = 'UTF-8';
                        $mail->MsgHTML($nota);
                        if ($enviaPdf) {
                            $archivoAdjuntar = $path_mailer.$path_archivo.$nombreArchivo;
                            echo 'archivoAdjuntar->'.$archivoAdjuntar.'<br>';
                            $mail->AddAttachment($archivoAdjuntar);
                        }
                        $mail->AddAddress($mailDestino, $destinatarioMail);
                        $mail->IsHTML(true);
                        if($mail->Send()) {
                            $error = 'OK';
                        }else{
                            $error = $mail->ErrorInfo;
                            var_dump($error);
                            echo '<br>';
                        }
                    }
                    echo 'envio realizado';
                } else {
                    echo 'envio NO realizado -> '.$resultado['mensaje'];
                }
            }
        }
        ?>
    </div>
    <div class="col-md-3">&nbsp;</div>
</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<?php
require_once '../html/footer.php';