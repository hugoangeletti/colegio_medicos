<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once '../dataAccess/funcionesPhp.php';
require_once '../dataAccess/colegiadoLogic.php';
require_once '../dataAccess/colegiadoDebitosLogic.php';
require_once '../dataAccess/colegiadoContactoLogic.php';

?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Envía adhesión al débito automático por mail</h4>
            </div>
            <div class="col-md-3 text-left">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $continuar = true;
        $mensaje = '';
        $linkVolver = '<a href="colegiado_consulta.php" class="btn btn-danger">Volver</a>';
        if (isset($_GET['idColegiado']) && $_GET['idColegiado'] <> "") {
            $idColegiado = $_GET['idColegiado'];
            $tipoDebito = $_GET['tipo'];
            $linkVolver = '<a href="colegiado_debito.php?idColegiado='.$idColegiado.'&tipo='.$tipoDebito.'" class="btn btn-primary">Volver</a>';
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado']) {
                $colegiado = $resColegiado['datos'];
                $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
                //obtengo los datos del debito
                switch ($tipoDebito) {
                    case 'C':
                    case 'D':
                        $resDebito = $colegiadoDebitosLogic->obtenerDebitoPorIdColegiado($idColegiado);
                        $titulo = 'DEBITO AUTOMATICO DE TARJETA VISA';
                        $debitar = 'TARJETA VISA';
                        break;

                    case 'H':
                        $resDebito = $colegiadoDebitosLogic->obtenerDebitoCBUPorIdColegiado($idColegiado);
                        $titulo = 'DEBITO AUTOMATICO POR CBU';
                        break;

                    default:
                        $resDebito['estado'] = FALSE;
                        break;
                }
                
                if ($resDebito['estado']) {
                    $debito = $resDebito['datos'];
                    $pathArchivo = $debito['pathArchivo'];
                    $nombreArchivo = $debito['nombreArchivo'];
                    $tipoArchivo = $debito['tipoArchivo'];

                    $resContacto =  $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                    if ($resContacto['estado']) {
                        $contacto = $resContacto['datos'];
                        $mail = $contacto['email'];
                        $noEnviaMail = $contacto['noEnviaMail'];
                        if ($noEnviaMail) {
                            $continuar = FALSE;
                            $mensaje .= 'No tiene registrado mail o fue rechazado, debe agregar en los datos de contacto.';
                        }
                    } else {
                        $continuar = FALSE;
                        $mensaje .= 'ERROR al obtener mail, vuelva a intentar';
                    }
                    if ($continuar) {
                    ?>
                        <div class="col-md-9">
                            <?php
                            //enviamos el pdf por mail si tiene contacto
                            $destinatario = $apellidoNombre;
                            $mailDestino = $mail;
                            //$mailDestino = 'sistemas@colmed1.org.ar';
                            $subCarpeta = substr($fechaPago, 0, 4).'/'.substr($fechaPago, 5, 2);
                            $archivoAdjuntar = '..'.$pathArchivo.$nombreArchivo;
                            $asunto = 'Planilla de adhesion al debito automatico';
                            $cuerpo = '<p>Estimado/a <b>'.$apellidoNombre.'</b></p>
                                    <p>Le enviamos la planilla de adhesion al debito automatico solicitado.</p>
                                    <p>Saludos cordiales,</p>
                                    <p>Tesorería<br> 
                                    Colegio de Médicos Pcia. de Bs.As.<br>
                                    Distrito I</p>';
                            require_once '../PHPMailer/class.phpmailer.php';
                            require_once '../PHPMailer/class.smtp.php';

                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->SMTPAuth = true;
                            $mail->SMTPSecure = "ssl";
                            $mail->Host = "mail.colmed1.org.ar";
                            $mail->Port = 465;
                            //$mail->Username = "sistemas@colmed1.org.ar";
                            //$mail->Password = "@sistem@s_1965";
                            //$mail->Username = 'mesadeentrada@colmed1.org.ar';
                            //$mail->Password = 'certificado';
                            $mail->Username = MAIL_MASIVO;
                            $mail->Password = MAIL_MASIVO_PASS;

                            $mail->From = "tesoreria@colmed1.org.ar";
                            $mail->FromName = "Colegio de Medicos - Distrito I";
                            $mail->Subject = $asunto;
                            $mail->AltBody = "";
                            $mail->CharSet = 'UTF-8';
                            $mail->MsgHTML($cuerpo);
                            $mail->AddAttachment($archivoAdjuntar);
                            $mail->AddAddress($mailDestino, $destinatario);
                            $mail->IsHTML(true);
                            
                            if($mail->Send()) {
                                $mailEnviado = TRUE;
                                ?>
                                <h3>Mail enviado correctamente a la casilla <?php echo $mailDestino; ?></h3>
                            <?PHP
                            }else{
                                $mailEnviado = FALSE;
                                ?>
                                <h3>ERROR al enviar mail a la casilla <?php echo $mailDestino.' - '.$mail->ErrorInfo; ?></h3>
                            <?PHP
                            }
                            echo $linkVolver;
                            ?>
                        </div>
                    <?php
                    }
                } else {
                ?>
                    <div class="col-md-12">
                        <h2 class="alert alert-danger">ERROR AL ACCEDER AL PDF</h2>
                    </div>
                <?php                    
                    echo $linkVolver;
                }
            } else {
            ?>
                <div class="col-md-12">
                    <h2 class="alert alert-danger">ERROR AL ACCEDER AL COLEGIADO</h2>
                </div>
            <?php
                echo $linkVolver;
            }
        } else {
        ?>
            <div class="col-md-12">
                <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
            </div>
        <?php
            echo $linkVolver;
        }
        ?>
    </div>
</div>
<?php        
include("../html/footer.php");

