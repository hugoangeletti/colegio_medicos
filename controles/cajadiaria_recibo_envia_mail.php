<?php
require_once '../dataAccess/config.php';
permisoLogueado();
require_once '../html/head.php';
require_once '../html/header.php';
require_once '../dataAccess/funcionesConector.php';
require_once ('../dataAccess/conection_pdo.php');
require_once ('../dataAccess/funcionesPhp.php');
require_once ('../dataAccess/cursos_pdo.php');
require_once '../dataAccess/cajaDiariaLogic.php';
$cajaDiariaLogic = new cajaDiariaLogic();
require_once '../dataAccess/colegiadoContactoLogic.php';
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-9">
                <h4>Envía recibo por mail</h4>
            </div>
            <div class="col-md-3 text-left">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <?php
        $continuar = true;
        $mensaje = '';
        if (isset($_GET['id']) && $_GET['id'] <> "") {
            $idCajaDiariaMovimiento = $_GET['id'];
            $resRecibo = $cajaDiariaLogic->obtenerCajaDiariaMovimientoPorId($idCajaDiariaMovimiento);
            if ($resRecibo['estado']) {
                $recibo = $resRecibo['datos']; 
                $idCajaDiaria = $recibo['idCajaDiaria'];
                $fechaPago = $recibo['fechaPago'];
                $totalRecibo = $recibo['monto'];
                $tipoRecibo = $recibo['tipoRecibo'];
                $numeroRecibo = $recibo['numeroRecibo'];
                $idAsistente = $recibo['idAsistente'];
                $idColegiado = $recibo['idColegiado'];
                $usuario = $recibo['usuario'];
                $apellidoNombre = $recibo['apellidoNombre'];
                $matricula = $recibo['matricula'];

                if (isset($idAsistente) && $idAsistente <> "") {
                    $cursos_pdo = new cursos_pdo();
                    $resAsistente = $cursos_pdo->obtenerAsistentePorId($idAsistente);
                    if ($resAsistente['estado']) {
                        $asistente = $resAsistente['datos'];
                        $idColegiado = $asistente['idColegiado'];
                    } else {
                        $idColegiado = NULL;
                        $continuar = FALSE;
                        $mensaje = $resAsistente['mensaje'];
                    }
                }

                if (isset($idColegiado)) {
                    $resContacto =  $colegiadoContactoLogic->obtenerColegiadoContactoPorIdColegiado($idColegiado);
                    if ($resContacto['estado']) {
                        $contacto = $resContacto['datos'];
                        $mail = $contacto['email'];
                        if (!isset($mail) || strtoupper($mail) == 'NR') {
                            $continuar = FALSE;
                            $mensaje .= 'No tiene registrado mail, debe agregar en los datos de contacto.';
                        }
                    } else {
                        $continuar = FALSE;
                        $mensaje .= 'ERROR al obtener mail, vuelva a intentar';
                    }
                } else {
                    $continuar = FALSE;
                    $mensaje = 'No es un colegiado';
                }
                if ($continuar) {
                ?>
                    <div class="col-md-9">
                        <?php
                        //enviamos el pdf por mail si tiene contacto
                        $destinatario = $apellidoNombre;
                        $mailDestino = $mail;
                        $subCarpeta = substr($fechaPago, 0, 4).'/'.substr($fechaPago, 5, 2);
                        $nombreArchivo = $tipoRecibo.'_'.$numeroRecibo.'.pdf';
                        $archivoAdjuntar = "../archivos/recibos/".$subCarpeta."/".$nombreArchivo;
                        $asunto = 'Comprobante '.$tipo.' '.$numeroRecibo;
                        $cuerpo = '<p>Estimado/a <b>'.$apellidoNombre.'</b></p>
                                <p>Le enviamos el comprobante '.$tipo.'-'.$numeroRecibo.' con fecha '.cambiarFechaFormatoParaMostrar($fechaPago).'.</p>
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

                        $mail->From = MAIL_MASIVO;
                        $mail->FromName = "Colegio de Medicos - Distrito I";
                        $mail->Subject = $asunto;
                        $mail->AltBody = "";
                        $mail->CharSet = 'UTF-8';
                        $mail->MsgHTML($cuerpo);
                        $mail->AddAttachment($archivoAdjuntar);
                        //$mailDestino = 'sistemas@colmed1.org.ar';
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
                        ?>
                        <a href="cajadiaria_movimientos.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Volver</a>
                    </div>
                <?php
                } else {
                ?>
                    <div class="col-md-9">
                        <h2 class="alert alert-danger">ERROR: <?php echo $mensaje; ?></h2>
                    </div>
                    <div class="col-md-3">
                        <a href="cajadiaria_movimientos.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Volver</a>
                    </div>
                <?php
                }
            } else {
            ?>
                <div class="col-md-12">
                    <h2 class="alert alert-danger">ERROR AL ACCEDER AL RECIBO</h2>
                </div>
                <a href="cajadiaria_movimientos.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Volver</a>
            <?php
            }
        } else {
        ?>
            <div class="col-md-12">
                <h2 class="alert alert-danger">ERROR AL INGRESAR</h2>
            </div>
            <a href="cajadiaria_movimientos.php?id=<?php echo $idCajaDiaria; ?>" class="btn btn-primary">Volver</a>
        <?php
        }
        ?>
    </div>
</div>
<?php        
include("../html/footer.php");

