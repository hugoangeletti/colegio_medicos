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
            $idDebito = $_GET['id'];
            $linkVolver = '<a href="colegiado_consulta.php?idColegiado='.$idColegiado.'" class="btn btn-primary">Volver</a>';
            $colegiadoLogic = new colegiadoLogic();
            $resColegiado = $colegiadoLogic->obtenerColegiadoPorId($idColegiado);
            if ($resColegiado['estado']) {
                $colegiado = $resColegiado['datos'];
                $apellidoNombre = $colegiado['apellido'].' '.$colegiado['nombre'];
                //obtengo los datos del debito
                switch ($tipoDebito) {
                    case 'C':
                    case 'D':
                        $resDebito = $colegiadoDebitosLogic->obtenerDebitoPorId($idDebito);
                        $titulo = 'DEBITO AUTOMATICO DE TARJETA VISA';
                        $debitar = 'TARJETA VISA';
                        break;

                    case 'H':
                        $resDebito = $colegiadoDebitosLogic->obtenerDebitoCBUPorId($idDebito);
                        $titulo = 'DEBITO AUTOMATICO POR CBU';
                        break;

                    default:
                        $resDebito['estado'] = FALSE;
                        break;
                }
                
                if ($resDebito['estado']) {
                    $debito = $resDebito['datos'];
                    $tipoBaja = $debito['tipoBaja'];

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

                    switch ($tipoBaja) {
                        case 'SOLICITADA':
                            $motivoBaja = 'Comunicamos que procedimos a realizar la baja solicitada, de su '.$titulo.'.<br>';
                            break;
                        
                        case 'DEBITO_RECHAZADO':
                            $motivoBaja = 'Comunicamos que procedimos a realizar la baja de su '.$titulo.'. <br> 
                                    Por no poder realizar los débitos correspondientes. <br>
                                    Le sugerimos, envié al mail de tesoreria@colmed1.org.ar
                                    su nuevo CBU o TARJETA DE CREDITO.';
                            break;
                        
                        default:
                            $motivoBaja = 'Comunicamos que procedimos a realizar la baja de su '.$titulo.'.<br>';
                            break;
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
                            $asunto = 'Baja al debito automatico';

                            $cuerpo = '<p>Estimado/a <b>'.$apellidoNombre.'</b></p>
                                    <p>'.$motivoBaja.'</p>
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
                    } else {
                    ?>
                        <div class="col-md-12">
                            <h3 class="alert alert-danger">ERROR: <?php echo $mensaje; ?></h3>
                        </div>
                    <?php                    
                    }
                } else {
                ?>
                    <div class="col-md-12">
                        <h3 class="alert alert-danger">ERROR AL ACCEDER AL DEBITO</h3>
                    </div>
                <?php                    
                }
            } else {
            ?>
                <div class="col-md-12">
                    <h3 class="alert alert-danger">ERROR AL ACCEDER AL COLEGIADO</h3>
                </div>
            <?php
            }
        } else {
        ?>
            <div class="col-md-12">
                <h3 class="alert alert-danger">ERROR AL INGRESAR</h3>
            </div>
        <?php
        }
        echo $linkVolver;
        ?>
    </div>
</div>
<?php        
include("../html/footer.php");

